<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 11/2/16
 * Time: 4:50 PM
 */
namespace Application\AffiliateBundle\Services;

class FindAffiliateUfiService
{
    /**
     * @param $searchTerm
     * @return null
     */
    public function findUfiBySearchTerm($searchTerm)
    {
        $BaseUrl = 'https://www.booking.com/autocomplete_2';
        $url = sprintf('%s?stype=1&lang=en-gb&pid=fa2b3d1fcfda02d8&sid=ed3ed63bbf3e104fb4d39aebcf20338e&aid=304142&should_split=1&term=%s', $BaseUrl, $searchTerm);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: text/html', 'Accept-Language: en-US,en;q=0.8', 'Host: www.booking.com'));

        $response = curl_exec($ch);
        $data = json_decode($response);

        if (isset($data->city) && isset($data->city[0]) && isset($data->city[0]->dest_id)) {
            return $data->city[0]->dest_id;
        }

        return null;
    }
}
