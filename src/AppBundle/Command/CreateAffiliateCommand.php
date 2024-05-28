<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 9/27/16
 * Time: 5:54 PM
 */
namespace AppBundle\Command;

use Application\AffiliateBundle\Entity\Affiliate;
use Application\AffiliateBundle\Entity\AffiliateType;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\PlaceType;

class CreateAffiliateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bl:create:affiliates')
            ->setDescription('This command is used to create goals affiliates')
            ->setDefinition(array(new InputArgument('affiliateId', InputArgument::REQUIRED, 'Affiliate type id'),
                                  new InputArgument('domain', InputArgument::REQUIRED, 'Project domain')))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em          = $this->getContainer()->get('doctrine')->getManager();
        $googlePlace =  $this->getContainer()->get('app.google_place');
        $routing     = $this->getContainer()->get('router');

        $goals = $em->createQuery("SELECT g
                          FROM AppBundle:Goal g
                          INDEX BY g.id
                          WHERE g.lat IS NOT NULL AND g.lng IS NOT NULL")
            ->getResult();

        $output->writeln('Start finding goals cities');
        $progress = new ProgressBar($output, count($goals));
        $progress->start();

        $countryNames = [];
        $resultArray = [];
        foreach($goals as $goal){
            $result = $googlePlace->getPlace($goal->getLat(), $goal->getLng());
            if (isset($result[PlaceType::TYPE_CITY]) && isset($result[PlaceType::COUNTRY_SHORT_NAME])){
                if (!isset($resultArray[$result[PlaceType::COUNTRY_SHORT_NAME]])){
                    $resultArray[$result[PlaceType::COUNTRY_SHORT_NAME]] = [];
                }

                if (isset($result[PlaceType::TYPE_COUNTRY])) {
                    $countryNames[$result[PlaceType::COUNTRY_SHORT_NAME]] = $result[PlaceType::TYPE_COUNTRY];
                }
                $resultArray[$result[PlaceType::COUNTRY_SHORT_NAME]][$result[PlaceType::TYPE_CITY]] = $goal->getId();
            }

            $progress->advance();
        }

        $progress->finish();
        $output->writeln('Finish finding goals cities');

        $domain        = $input->getArgument('domain');
        $affiliateId   = $input->getArgument('affiliateId');
        $affiliateType = $em->getRepository('ApplicationAffiliateBundle:AffiliateType')->find($affiliateId);
        if (!$affiliateType){
            throw new \Exception('Affiliate Type not found');
        }

        $output->writeln('Read excel data');
        $path = __DIR__ . '/Booking/cities.tsv';
        $objPHPExcel = \PHPExcel_IOFactory::load($path);

        $output->writeln('Start ufi code finding');
        $progress = new ProgressBar($output);
        $progress->start();

        $affiliateCounts = 0;
        $dataCell        = 'A2';
        $sheetData       = $objPHPExcel->getActiveSheet();

        do {
            $data    = strtolower($sheetData->getCell($dataCell)->getValue());
            $data = explode("\t", $data);

            $city    = isset($data[0]) ? strtolower($data[0]) : null;
            $country = isset($data[2]) ? strtolower($data[2]) : null;

            if (!$city && !$country){
                break;
            }

            if (isset($resultArray[$country]) && isset($resultArray[$country][$city])){

                $ufi = isset($data[3]) ? $data[3] : null;

                if (!is_null($ufi)) {
                    $affiliate = new Affiliate();
                    $affiliate->setName($city);
                    $affiliate->setAffiliateType($affiliateType);
                    $affiliate->setUfi($ufi);

                    $link = $domain . $routing->generate('inner_goal', ['slug' => $goals[$resultArray[$country][$city]]->getSlug()]);
                    $affiliate->setLinks([$link]);

                    $em->persist($affiliate);
                    $em->flush();

                    $affiliateCounts++;

                    unset($resultArray[$country][$city]);
                }
            }

            $dataCellCrd    = \PHPExcel_Cell::coordinateFromString($dataCell);
            ++$dataCellCrd[1];

            $dataCell = implode($dataCellCrd);
            $progress->advance();

        } while(true);


        $progress->finish();
        $output->writeln($affiliateCounts . ' Affiliates was created from excel data');

        $output->writeln("\nStart ufi code finding by search");
        $progress = new ProgressBar($output, count($resultArray));
        $progress->start();
        $affiliateCounts = 0;

        foreach($resultArray as $countrySortName => $cities){
            foreach($cities as $city => $goalId) {

                $countryName = isset($countryNames[$countrySortName]) ? $countryNames[$countrySortName] : $countrySortName;
                $searchTerm = urlencode($countryName . ' ' . $city);

                $ufi = $this->getContainer()->get('application_affiliate.find_ufi')->findUfiBySearchTerm($searchTerm);

                if ($ufi) {
                    $affiliate = new Affiliate();
                    $affiliate->setName($city);
                    $affiliate->setAffiliateType($affiliateType);
                    $affiliate->setUfi($ufi);

                    $link = $domain . $routing->generate('inner_goal', ['slug' => $goals[$goalId]->getSlug()]);
                    $affiliate->setLinks([$link]);

                    $em->persist($affiliate);
                    $em->flush();

                    $affiliateCounts++;
                    continue;
                }

//                if(strlen($countryName) > 3) {
//                    $searchTerm = urlencode($countryName);
//                    $ufi = $this->getContainer()->get('application_affiliate.find_ufi')->findUfiBySearchTerm($searchTerm);
//
//                    if ($ufi) {
//                        $affiliate = new Affiliate();
//                        $affiliate->setName($city);
//                        $affiliate->setAffiliateType($affiliateType);
//                        $affiliate->setUfi($ufi);
//
//                        $link = $domain . $routing->generate('inner_goal', ['slug' => $goals[$goalId]->getSlug()]);
//                        $affiliate->setLinks([$link]);
//
//                        $em->persist($affiliate);
//                        $em->flush();
//
//                        $affiliateCounts++;
//                        continue;
//                    }
//                }
//
//                if(strlen($city) > 3) {
//                    $searchTerm = urlencode($city);
//                    $ufi = $this->getContainer()->get('application_affiliate.find_ufi')->findUfiBySearchTerm($searchTerm);
//
//                    if ($ufi) {
//                        $affiliate = new Affiliate();
//                        $affiliate->setName($city);
//                        $affiliate->setAffiliateType($affiliateType);
//                        $affiliate->setUfi($ufi);
//
//                        $link = $domain . $routing->generate('inner_goal', ['slug' => $goals[$goalId]->getSlug()]);
//                        $affiliate->setLinks([$link]);
//
//                        $em->persist($affiliate);
//                        $em->flush();
//
//                        $affiliateCounts++;
//                        continue;
//                    }
//                }
            }

            $progress->advance();
        }

        $progress->finish();
        $output->writeln($affiliateCounts . ' Affiliates was created by city search');
        $em->flush();
        $output->writeln('Success!!!');
    }


}



//        $resultArray =
//            ["us" =>   ["morrisville" => 62,
//                        "new york" => 880,
//                        "oakland" => 87,
//                        "san diego" => 88,
//                        "boston" => 94,
//                        "lexington" => 120,
//                        "united states" => 3281,
//                        "anaheim" => 191,
//                        "new haven" => 198,
//                        "baltimore" => 203,
//                        "seattle" => 224,
//                        "los angeles" => 459,
//                        "erie" => 275,
//                        "healy" => 356,
//                        "sandusky" => 389,
//                        "pittsburgh" => 419,
//                        "hackensack" => 431,
//                        "orlando" => 484,
//                        "washington" => 1518,
//                        "key west" => 548,
//                        "chicago" => 587,
//                        "jackson" => 1485,
//                        "sanibel" => 651,
//                        "new orleans" => 715,
//                        "detroit" => 881,
//                        "crystal river" => 1227,
//                        "morristown" => 1480,
//                        "concord" => 1481,
//                        "monson" => 1482,
//                        "north kingstown" => 1483,
//                        "watertown" => 1484,
//                        "kansas city" => 1702,
//                        "millersville" => 1496,
//                        "bay lake" => 1630,
//                        "san francisco" => 1824,
//                        "hollywood" => 2449,
//                        "sinclair" => 2459,
//                        "senoia" => 2461,
//                        "atlanta" => 3658, ],
//            "fr" =>   ["paris" => 365,
//                        "versailles" => 387,
//                        "chessy" => 1631,
//                        "la celle-sous-gouzon" => 3237 ],
//            "at" =>   ["vienna" => 3592 ],
//                    "las vegas" =>   ["las vegas" => 511 ],
//            "jp" =>   ["fujinomiya" => 82, "urayasu" => 1634 ],
//            "pe" =>   ["peru" => 1792, "lima district" => 1182 ],
//            "it" =>   ["alba" => 85,
//                    "venice" => 223,
//                    "palermo" => 246,
//                    "rome" => 1571,
//                    "pompeii" => 1802,
//                    "milan" => 2007,
//                    "florence" => 3653 ],
//            "id" =>   ["indonesia" => 551,
//                    "bogor" => 304 ],
//            "be" =>   ["boom" => 89 ],
//            "nz" =>   ["new zealand" => 98,
//                    "matamata" => 520 ],
//            "br" =>   ["brazil" => 105,
//                    "brasília" => 362,
//                    "rio de janeiro" => 2380,
//                    "foz do iguaçu" => 3632 ],
//            "eg" =>   ["egypt" => 3590,
//                    "dahab" => 1671 ],
//            "ro" =>   ["cluj-napoca" => 107,
//                    "romania" => 3336 ],
//            "pa" =>   ["panama" => 114 ],
//            "hk" =>   ["hong kong" => 1632 ],
//            "mx" =>   ["mexico" => 1154,
//                    "cancún" => 2945,
//                    "mexico city" => 3602 ],
//            "gb" =>   ["united kingdom" => 3127,
//                    "london" => 3595,
//                    "londonderry" => 696,
//                    "brighton" => 709,
//                    "manchester" => 1170,
//                    "liverpool" => 1174 ],
//            "sydney" =>   ["sydney nsw" => 3641 ],
//            "pt" =>   ["são miguel do rio torto" => 165,
//                    "ponta delgada" => 2283 ],
//            "cn" =>   ["golog" => 183,
//                    "altay" => 1145,
//                    "xigaze" => 1151,
//                    "xining" => 1514,
//                    "lanzhou" => 1566,
//                    "shanghai" => 1633,
//                    "beijing" => 3654 ],
//            "cl" =>   ["chile" => 3214 ],
//            "au" =>   ["australia" => 1564,
//                    "tanami east nt" => 423,
//                    "ghan nt" => 819,
//                    "hillier sa" => 2603,
//                    "middlesex tas" => 3403,
//                    "nitmiluk nt" => 3404 ],
//            "tokyo" =>   ["bunkyo" => 202 ],
//            "np" =>   ["namarjung" => 2310 ],
//            "my" =>   ["padang tengku" => 229,
//                    "kota belud" => 2538 ],
//            "ca" =>   ["niagara falls" => 230,
//                    "ottawa" => 263,
//                    "canada" => 3212,
//                    "montreal" => 2028,
//                    "vancouver" => 3600 ],
//            "es" =>   ["spain" => 1175,
//                    "madrid" => 3593,
//                    "barcelona" => 2306,
//                    "palma" => 3276 ],
//            "co" =>   ["colombia" => 2604 ],
//            "canberra" =>   ["canberra act" => 262 ],
//            "in" =>   ["agra" => 278,
//                    "wadgaon" => 499,
//                    "new delhi" => 570 ],
//            "no" =>   ["norway" => 1386 ],
//            "chiisagata district" =>   ["nagawa" => 317 ],
//            "vn" =>   ["vietnam" => 368 ],
//            "ar" =>   ["argentina" => 1513,
//                    "buenos aires" => 1181 ],
//            "rs" =>   ["gornje komarice" => 388 ],
//            "va" =>   ["vatican city" => 396 ],
//            "il" =>   ["באר שבע" => 420, "jerusalem" => 3606 ],
//            "am" =>   ["armenia" => 3431,
//                    "yerevan" => 1163 ],
//            "tw" =>   ["taiwan" => 502,
//                    "kaohsiung" => 1177 ],
//            "ke" =>   ["kenya" => 2210 ],
//            "kh" =>   ["cambodia" => 507 ],
//            "ma" =>   ["ouled khellouf" => 516 ],
//            "ie" =>   ["ireland" => 528 ],
//            "gr" =>   ["greece" => 1799,
//                    "athens" => 3604 ],
//            "nl" =>   ["soest" => 571,
//                    "amsterdam" => 3589 ],
//            "is" =>   ["iceland" => 2602 ],
//            "dublin" =>   ["dublin" => 588 ],
//            "hr" =>   ["jezerane" => 589 ],
//            "th" =>   ["nong chaeng" => 590,
//                    "bangkok" => 850 ],
//            "ru" =>   ["russia" => 3337,
//                    "moscow" => 1178,
//                    "st petersburg" => 3580 ],
//            "pl" =>   ["poland" => 610 ],
//            "de" =>   ["berlin" => 646,
//                    "oberdorla" => 778,
//                    "munich" => 3644 ],
//            "dk" =>   ["copenhagen" => 688,
//                    "frederikssund" => 3616 ],
//            "aq" =>   ["antarctica" => 699 ],
//            "warsaw" =>   ["warsaw" => 720 ],
//            "melbourne" =>   ["melbourne vic" => 744 ],
//            "ch" =>   ["sachseln" => 748,
//                    "zermatt" => 1152 ],
//            "cr" =>   ["costa rica" => 749 ],
//            "kr" =>   ["muju-gun" => 757,
//                    "seoul" => 3603 ],
//            "ec" =>   ["ecuador" => 3228 ],
//            "sk" =>   ["Čierny balog" => 764 ],
//            "sg" =>   ["singapore" => 777 ],
//            "pf" =>   ["french polynesia" => 2481 ],
//            "bs" =>   ["nassau" => 843 ],
//            "toronto" =>   ["ward  -- york south-weston (frances nunziata)" => 3648 ],
//            "me" =>   ["montenegro" => 1164 ],
//            "kp" =>   ["north korea" => 1165 ],
//            "sm" =>   ["san marino" => 1168 ],
//            "santiago" =>   ["santiago" => 1169 ],
//            "kyoto" =>   ["nakagyo ward" => 1180 ],
//            "lu" =>   ["nommern" => 1380 ],
//            "mk" =>   ["macedonia (fyrom)" => 1383 ],
//            "mc" =>   ["monaco-ville" => 1385 ],
//            "si" =>   ["kotredež" => 1389 ],
//            "ua" =>   ["dobrovelychkivka" => 1390 ],
//            "kw" =>   ["kuwait" => 1392 ],
//            "lb" =>   ["zahlé" => 1393 ],
//            "om" =>   ["oman" => 1394 ],
//            "qa" =>   ["qatar" => 1395 ],
//            "sa" =>   ["saudi arabia" => 1396 ],
//            "ae" =>   ["united arab emirates" => 1397 ],
//            "pw" =>   ["palau" => 1398 ],
//            "sb" =>   ["solomon islands" => 1399 ],
//            "ck" =>   ["avarua district" => 1400 ],
//            "vu" =>   ["vanuatu" => 1401 ],
//            "bo" =>   ["bolivia" => 1402,
//                    "uyuni" => 2247 ],
//            "az" =>   ["azerbaijan" => 3170 ],
//            "tr" =>   ["turkey" => 1511 ],
//            "za" =>   ["cape town" => 1686 ],
//            "jo" =>   ["jordan" => 1857 ],
//            "zw" =>   ["zimbabwe" => 1858 ],
//            "ml" =>   ["mali" => 1859 ],
//            "sc" =>   ["seychelles" => 2826 ],
//            "la" =>   ["laos" => 2982 ],
//            "cu" =>   ["cuba" => 3135 ],
//            "ag" =>   ["antigua and barbuda" => 3169 ],
//            "bh" =>   ["bahrain" => 3171 ],
//            "bd" =>   ["keraniganj" => 3180 ],
//            "bz" =>   ["belize" => 3184 ],
//            "bn" =>   ["brunei" => 3185 ],
//            "bf" =>   ["burkina faso" => 3187 ],
//            "bi" =>   ["burundi" => 3193 ],
//            "cd" =>   ["democratic republic of the congo" => 3215 ],
//            "cz" =>   ["golčův jeníkov" => 3219, "prague" => 3591 ],
//            "dm" =>   ["dominica" => 3225 ],
//            "do" =>   ["dominican republic" => 3227 ],
//            "sv" =>   ["el salvador" => 3235 ],
//            "gd" =>   ["grenada" => 3243 ],
//            "gh" =>   ["ghana" => 3244 ],
//            "gt" =>   ["guatemala" => 3245 ],
//            "ht" =>   ["durocher" => 3248 ],
//            "ir" =>   ["iran" => 3250 ],
//            "ci" =>   ["côte d'ivoire" => 3251 ],
//            "kz" =>   ["kazakhstan" => 3252 ],
//            "ki" =>   ["kiribati" => 3259 ],
//            "kg" =>   ["kyrgyzstan" => 3260 ],
//            "ls" =>   ["lesotho" => 3261 ],
//            "li" =>   ["schaan" => 3291 ],
//            "mg" =>   ["madagascar" => 3301 ],
//            "mt" =>   ["mgarr" => 3306 ],
//            "mh" =>   ["marshall islands" => 3307 ],
//            "mu" =>   ["mauritius" => 3308 ],
//            "fm" =>   ["federated states of micronesia" => 3319 ],
//            "mn" =>   ["mongolia" => 3325 ],
//            "mz" =>   ["mozambique" => 3326 ],
//            "mm" =>   ["sagaing" => 3327 ],
//            "ni" =>   ["nicaragua" => 3328 ],
//            "py" =>   ["paraguay" => 3329 ],
//            "ph" =>   ["philippines" => 3330 ],
//            "lc" =>   ["saint lucia" => 3338 ],
//            "vc" =>   ["st vincent and the grenadines" => 3339 ],
//            "ws" =>   ["samoa" => 3371 ],
//            "tm" =>   ["turkmenistan" => 3378 ],
//            "uy" =>   ["uruguay" => 3425 ],
//            "uz" =>   ["uzbekistan" => 3426 ],
//            "perth" =>   ["perth wa" => 3574 ],
//            "mumbai" =>   ["mumbai" => 3643 ],
//         ];


//        $countryNames = [
//          "us" => "usa",
//          "fr" => "france",
//          "at" => "austria",
//          "las vegas" => "usa",
//          "jp" => "japan",
//          "it" => "italy",
//          "be" => "belgium",
//          "ro" => "romania",
//          "sydney" => "australia",
//          "pt" => "portugal",
//          "cn" => "china",
//          "tokyo" => "japan",
//          "np" => "nepal",
//          "my" => "malaysia",
//          "ca" => "canada",
//          "canberra" => "australia",
//          "in" => "india",
//          "gb" => "uk",
//          "id" => "indonesia",
//          "chiisagata district" => "japan",
//          "br" => "brazil",
//          "rs" => "serbia",
//          "il" => "israel",
//          "au" => "australia",
//          "es" => "spain",
//          "ma" => "morocco",
//          "nz" => "new zealand",
//          "nl" => "netherlands",
//          "dublin" => "ireland",
//          "hr" => "croatia",
//          "th" => "thailand",
//          "de" => "germany",
//          "dk" => "denmark",
//          "warsaw" => "poland",
//          "melbourne" => "australia",
//          "ch" => "switzerland",
//          "kr" => "south korea",
//          "sk" => "slovakia",
//          "bs" => "the bahamas",
//          "toronto" => "canada",
//          "am" => "armenia",
//          "santiago" => "chile",
//          "tw" => "taiwan",
//          "ru" => "russia",
//          "kyoto" => "japan",
//          "ar" => "argentina",
//          "pe" => "peru",
//          "lu" => "luxembourg",
//          "mc" => "monaco",
//          "si" => "slovenia",
//          "ua" => "ukraine",
//          "lb" => "lebanon",
//          "ck" => "cook islands",
//          "eg" => "egypt",
//          "za" => "south africa",
//          "bo" => "bolivia",
//          "mx" => "mexico",
//          "bd" => "bangladesh",
//          "cz" => "czech republic",
//          "ht" => "haiti",
//          "li" => "liechtenstein",
//          "mt" => "malta",
//          "mm" => "myanmar (burma)",
//          "perth" => "australia",
//          "gr" => "greece",
//          "mumbai" => "india",
//        ];