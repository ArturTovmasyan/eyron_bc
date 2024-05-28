<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/15/15
 * Time: 3:27 PM
 */

namespace AppBundle\Entity\Repository;

use AppBundle\Controller\Rest\StatisticController;
use AppBundle\Entity\Goal;
use AppBundle\Entity\UserGoal;
use AppBundle\Model\PublishAware;
use AppBundle\Traits\StatisticDataFilterTrait;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class GoalRepository
 * @package AppBundle\Entity\Repository
 */
class GoalRepository extends EntityRepository
{
    use StatisticDataFilterTrait;

    const TopIdeasCount = 100;

    /**
     * @param $text
     * @param $count
     * @return array
     */
    public function findGoalsByAutocomplete($text, $count)
    {
        $query =
            $this->getEntityManager()
                ->createQueryBuilder()
                ->select('g.id as id, g.title as value, g.title as label')
                ->from('AppBundle:Goal', 'g', 'g.id')
                ->where('g.publish = :publish')
                ->andWhere('g.title LIKE :text')
                ->setParameter('publish', PublishAware::PUBLISH)
                ->setParameter('text', '%' . $text . '%')
                ->setMaxResults($count);
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * @param $latitude
     * @param $longitude
     * @param $first
     * @param $count
     * @param $userId
     * @param $isCompleted
     * @return array
     */
    public function findNearbyGoals($latitude, $longitude, $first, $count, $isCompleted, $userId)
    {
        $result = [];
        //3959 search in miles
        //6371 search in km
        $query =  $this->getEntityManager()
            ->createQueryBuilder()
            ->select('g.id', '(6371 * acos(cos(radians(:lat)) * cos(radians(g.lat)) * cos(radians(g.lng)
                            - radians(:lng)) + sin(radians(:lat)) * sin(radians(g.lat)))) as HIDDEN dist')
            ->from('AppBundle:Goal', 'g')
            ->leftJoin('g.userGoal', 'ug', 'WITH', 'ug.user = :user')
            ->where('g.lat is not null and g.lng is not null')
            ->andWhere('ug.id IS NULL or ug.notInterested != 1')
            ->orderBy('dist')
            ->setParameter('lat', $latitude)
            ->setParameter('lng', $longitude)
            ->setParameter('user', $userId);
        ;

        if(($isCompleted == 'false' || !$isCompleted ) && !is_null($userId)){
            $query
                ->andWhere('ug.id IS NULL or ug.status = :status')
                ->setParameter('status', UserGoal::ACTIVE);
        }

        $ids = $query
            ->setFirstResult($first)
            ->setMaxResults($count)
            ->getQuery()
            ->getArrayResult()
        ;

        if($ids){

            $ids = array_map(function($item){return $item['id'];}, $ids);

            //3959 search in miles
            //6371 search in km
            $result =  $this->getEntityManager()
                ->createQuery("SELECT g, i,
                           (6371 * acos(cos(radians(:lat)) * cos(radians(g.lat)) * cos(radians(g.lng)
                            - radians(:lng)) + sin(radians(:lat)) * sin(radians(g.lat)))) as dist
                           FROM AppBundle:Goal g
                           LEFT JOIN g.images i
                           WHERE g.id in (:ids)
                           ORDER BY dist
                         ")
                ->setParameter('lat', $latitude)
                ->setParameter('lng', $longitude)
                ->setParameter('ids', $ids)
                ->getResult()
            ;

        }


        return $result;
    }

    /**
     * @param $count
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findAllWithCount($count = null)
    {
        $query =
            $this->getEntityManager()
                ->createQueryBuilder()
                ->select('g', 'i', 'count(ug) as HIDDEN  cnt')
                ->from('AppBundle:Goal', 'g', 'g.id')
                ->join('g.images', 'i', 'with', 'i.list = true')
                ->leftJoin('g.userGoal', 'ug')
                ->where('g.publish = :publish')
                ->addGroupBy('g.id')
                ->orderBy('cnt', 'desc')
                ->setParameter('publish', PublishAware::PUBLISH)
        ;


        // check count
        if($count){
            $query
                ->setMaxResults($count);
        }
        return $query->getQuery()->getResult();
    }


    /**
     * This function is used to get listedBy, doneBy counts for goal
     *
     * @param $goals
     * @param $getStats
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findGoalStateCount(&$goals, $getStats = false)
    {
        $isSingleObject = 0;
        if ($goals instanceof Goal){
            $isSingleObject = $goals->getId();
            $goals = [$goals->getId() => $goals];
        }

        if (!count($goals)){
            return null;
        }

        $stats = $this->getEntityManager()
            ->createQuery("SELECT g.id as goalId,
                              SUM(CASE WHEN ug.status = :activeStatus THEN 1 ELSE  0 END) AS listedBy,
                              SUM(CASE WHEN ug.status = :completeStatus THEN 1 ELSE 0 END) AS doneBy
                           FROM AppBundle:Goal g
                           LEFT JOIN g.userGoal ug
                           INDEX BY g.id
                           WHERE g.id IN (:goalIds)
                           GROUP BY g.id
                          ")
            ->setParameter('goalIds', array_keys($goals))
            ->setParameter('activeStatus', UserGoal::ACTIVE)
            ->setParameter('completeStatus', UserGoal::COMPLETED)
            ->getResult();


//        $stats = $this->getEntityManager()
//            ->createQuery("SELECT g.id as goalId, COUNT(ug) as listedBy,
//                          (SELECT COUNT (ug1) from AppBundle:UserGoal ug1
//                           WHERE ug1.status != :status and ug1.goal = g) as doneBy
//                           FROM AppBundle:Goal g
//                           INDEX BY g.id
//                           LEFT JOIN g.userGoal ug
//                           WHERE g.id IN (:goalIds)
//                           GROUP BY g.id
//                          ")
//            ->setParameter('goalIds', array_keys($goals))
//            ->setParameter('status', UserGoal::ACTIVE)
//            ->getResult();

        if ($getStats){
            return $stats;
        }

        foreach($goals as &$goal){
            $goal->setStats([
                'listedBy' => $stats[$goal->getId()]['listedBy'],
                'doneBy'   => $stats[$goal->getId()]['doneBy'],
            ]);
        }

        if ($isSingleObject){
            $goals = $goals[$isSingleObject];
            return $goals;
        }

        return $goals;
    }

    /**
     * @param $user
     * @param $count
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findPopular($count, $user = null)
    {
        $ids = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('DISTINCT g.id', '(SELECT COUNT(ug2) FROM AppBundle:UserGoal ug2 WHERE ug2.goal = g) as HIDDEN cnt')
            ->from('AppBundle:Goal', 'g')
            ->leftJoin('g.userGoal', 'ug', 'WITH', 'ug.user = :user')
            ->where('g.publish = true AND ug.id IS NULL')
            ->orderBy('cnt', 'desc')
            ->setParameter('user', is_object($user) ? $user->getId() : -1)
            ->setMaxResults(self::TopIdeasCount)
            ->getQuery()
            ->getScalarResult();


        $ids = array_map(function($v){ return $v['id']; }, $ids);
        shuffle($ids);
        $ids = array_slice($ids, 0, $count);

        if (count($ids) == 0){
            return [];
        }

        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('g', 'i')
            ->from('AppBundle:Goal', 'g', 'g.id')
            ->leftJoin('g.images', 'i')
            ->where('g.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $user
     * @return array
     */
    public function findFeatured($user)
    {
        $featuredIds = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('DISTINCT g.id')
            ->from('AppBundle:Goal', 'g')
            ->leftJoin('g.userGoal', 'ug', 'WITH', 'ug.user = :user')
            ->where('g.featuredDate >= CURRENT_DATE() AND ug.id IS NULL')
            ->setParameter('user', is_object($user) ? $user->getId() : -1)
            ->getQuery()
            ->getResult();

        if (count($featuredIds) == 0){
            return [];
        }

        shuffle($featuredIds);
        $featuredId = $featuredIds[0]['id'];

        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('g', 'i')
            ->from('AppBundle:Goal', 'g', 'g.id')
            ->leftJoin('g.images', 'i')
            ->where('g.id = :id')
            ->setParameter('id', $featuredId)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $user
     * @param null $first
     * @param null $count
     * @return Query
     */
    public function findMyDrafts($user, $first = null, $count = null)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('g, i')
            ->from('AppBundle:Goal', 'g')
            ->leftJoin('g.images', 'i')
            ->leftJoin('g.author', 'a')
            ->where('a.id = :user')
            ->andWhere('g.readinessStatus = :readinessStatus')
            ->orderBy('g.id', 'desc')
            ->setParameter('user', $user)
            ->setParameter('readinessStatus', Goal::DRAFT)
        ;

        if (!is_null($first) && !is_null($count)){
            $query
                ->setFirstResult($first)
                ->setMaxResults($count);

            $paginator = new Paginator($query, $fetchJoinCollection = true);
            return $paginator->getIterator()->getArrayCopy();
        }


        return $query->getQuery();
    }

    /**
     * @param $user
     * @return array
     */
    public function findMyDraftsAndFriendsCount(&$user)
    {
        $query =
            $this->getEntityManager()
                ->createQueryBuilder()
                ->addSelect('COUNT(g)')
                ->from('AppBundle:Goal', 'g')
                ->leftJoin('g.author', 'a')
                ->where('a.id = :user')
                ->andWhere('g.readinessStatus = :readinessStatus')
                ->setParameter('user', $user)
                ->setParameter('readinessStatus', Goal::DRAFT)
        ;


        $draftCount = $query->getQuery()->getSingleScalarResult();

        $this->findRandomGoalFriends($user->getId(), null, $goalFriendsCount, true);

        $user->setDraftCount($draftCount);
        $user->setGoalFriendsCount($goalFriendsCount);

        return $draftCount;
    }

    /**
     * @param $user
     * @return array
     */
    public function findMyIdeasCount(&$user)
    {
        $query =
            $this->getEntityManager()
                ->createQueryBuilder()
                ->addSelect('COUNT(g)')
                ->from('AppBundle:Goal', 'g')
                ->leftJoin('g.author', 'a')
                ->where('a.id = :user')
                ->andWhere('g.readinessStatus = :readinessStatus OR g.status = :status')
                ->setParameter('user', $user)
                ->setParameter('readinessStatus', Goal::DRAFT)
                ->setParameter('status', Goal::PRIVATE_PRIVACY)
        ;

        $myIdeasCount = $query->getQuery()->getSingleScalarResult();
        $user->setDraftCount($myIdeasCount);

        return $myIdeasCount;
    }

    /**
     * @param $category
     * @param $search
     * @param $first
     * @param $count
     * @param $allIds
     * @return mixed
     * @param null $locale
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findAllByCategory($category = null, $search = null, $first = null, $count = null, &$allIds = null, $locale = null)
    {
        $query =
            $this->getEntityManager()
                ->createQueryBuilder()
                ->from('AppBundle:Goal', 'g', 'g.id')
                ->leftJoin('g.images', 'i', 'with', 'i.list = true')
                ->where('g.publish = :publish')
                ->setParameter('publish', PublishAware::PUBLISH)
        ;

        $isRandom = (!$search && ($category != 'most-popular'));

        if($search){
            $sortSelect = "MATCH_AGAINST(g.title, :search) * 10 + MATCH_AGAINST(g.description, :search) as HIDDEN cnt";

            $query
                ->andWhere('MATCH_AGAINST(g.title, g.description, :search) > 0.5')
                ->setParameter('search', $search);
        }
        else {
            if ($category && $category == 'featured'){
                $query->andWhere('g.featuredDate >= CURRENT_DATE()');
            }
            elseif($category && $category != 'most-popular'){
                $query
                    ->leftJoin('g.tags', 'gt')
                    ->andWhere('gt.id in (SELECT ct.id
                                          FROM AppBundle:Category c
                                          LEFT JOIN c.tags ct
                                          WHERE c.slug = :catId)')
                    ->setParameter('catId', $category);
            }

            if($locale){
                $query
                    ->andWhere('g.language  = :lng OR g.language is null')
                    ->setParameter('lng', $locale);
            }

            $sortSelect = "(SELECT count(cug) FROM AppBundle:UserGoal cug WHERE cug.goal = g) as HIDDEN cnt";
        }

        if (!$isRandom){
            $query
                ->addSelect($sortSelect)
                ->orderBy('cnt', 'desc');
        }

        if (is_numeric($first) && is_numeric($count)){

            $idsQuery = clone $query;

            $ids = $idsQuery
                ->addSelect('g.id')
                ->getQuery()
                ->getResult();

            if($isRandom){
                $ids = $this->shuffle_goal($ids);
            }

            $allIds = $ids;
            $ids = array_slice($ids, $first, $count);

            if (count($ids) == 0){
                return [];
            }

            $query
                ->andWhere('g.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        $query->addSelect('g, i');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $userId
     * @param $count
     * @param $allCount
     * @param $getAllCount
     * @return array
     */
    public function findRandomGoalFriends($userId, $count, &$allCount, $getAllCount = false)
    {
        $goalFriendIds = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('DISTINCT u.id')
            ->from('ApplicationUserBundle:User', 'u', 'u.id')
            ->join('u.userGoal', 'ug')
            ->join('AppBundle:UserGoal', 'ug1', 'WITH', 'ug1.goal = ug.goal AND ug1.user = :userId')
            ->where("u.id != :userId")
            ->andWhere('u.roles = :roles')
            ->setParameter('userId', $userId)
            ->setParameter('roles', 'a:0:{}')
            ->getQuery()
            ->getResult();


        $allCount = count($goalFriendIds);

        if($getAllCount){
            return $allCount;
        }

        if ($allCount == 0){
            return [];
        }

        shuffle($goalFriendIds);
        $goalFriendIds = array_slice($goalFriendIds, 0, $count);

        return $this->getEntityManager()
            ->createQuery("SELECT u
                           FROM ApplicationUserBundle:User u
                           WHERE u.id IN (:ids)")
            ->setParameter('ids', $goalFriendIds)
            ->getResult();
    }


    /**
     * @param $userId
     * @param $type
     * @param $search
     * @param $first
     * @param $count
     * @return array|Query
     */
    public function findGoalFriends($userId, $type, $search, $first, $count)
    {
        $search = str_replace(' ', '', $search);

        if (!is_numeric($first) || !is_numeric($count)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST);
        }

        //TODO roles in query must be changed
        $query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('DISTINCT u')
            ->from('ApplicationUserBundle:User', 'u', 'u.id')
            ->where("u.id != :userId AND u.isAdmin = false");

        ;


        if($type != "follow"){
            $query
                ->join('u.userGoal', 'ug')
                ->join('AppBundle:UserGoal', 'ug1', 'WITH', 'ug1.goal = ug.goal AND ug1.user = :userId');
        }

        if ($search){
            $query->andWhere("u.firstname LIKE :search
                           or u.lastname LIKE :search
                           or u.email LIKE :search
                           or CONCAT(u.firstname, u.lastname) LIKE :search")
                ->setParameter('search', '%' . $search . '%');
        }


        switch ($type) {
            case 'recently':
                $query->orderBy('u.createdAt', 'DESC');
                break;
            case 'match':
                $query = $this
                    ->getEntityManager()
                    ->createQueryBuilder()
                    ->select('u')
                    ->from('ApplicationUserBundle:User', 'u', 'u.id')
                    ->join('ApplicationUserBundle:MatchUser', 'm_user', 'WITH', 'm_user.user = :userId AND m_user.matchUser = u')
                    ->where('u.isAdmin = false')
                    ->orderBy('m_user.commonFactor', 'DESC')
                    ->addOrderBy('m_user.commonCount', 'DESC')
                ;
                break;
            case 'follow':
                $query
                    ->join('ApplicationUserBundle:User', 'fu', 'WITH', 'fu = :userId')
                    ->join('fu.followings', 'fus')
                    ->andWhere('fus = u')
                ;
                break;
            case 'active':
                $query->orderBy('u.activeFactor', 'DESC');
                break;
        }

        $query
            ->setParameter('userId', $userId)
            ->setFirstResult($first)
            ->setMaxResults($count);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findWithRelations($id)
    {
        return $this->getEntityManager()
            ->createQuery("SELECT g, i, t, au, gs, f, gsu, v
                           FROM AppBundle:Goal g
                           LEFT JOIN g.tags t
                           LEFT JOIN g.images i
                           LEFT JOIN g.author au
                           LEFT JOIN g.successStories gs
                           LEFT JOIN gs.user gsu
                           LEFT JOIN gs.files f
                           LEFT JOIN gs.successStoryVoters v
                           WHERE g.id = :id")
            ->setParameter('id', $id)
            ->getOneOrNullResult();
    }

    /**
     * @param $slug
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findWithRelationsBySlug($slug)
    {
        if(is_array($slug)) {
            $slug = $slug['slug'];
        }
        
        return $this->getEntityManager()
            ->createQuery("SELECT g, i, t, au, gs, f, gsu, v
                           FROM AppBundle:Goal g
                           LEFT JOIN g.tags t
                           LEFT JOIN g.images i
                           LEFT JOIN g.author au
                           LEFT JOIN g.successStories gs
                           LEFT JOIN gs.user gsu
                           LEFT JOIN gs.files f
                           LEFT JOIN gs.successStoryVoters v
                           WHERE g.slug = :slug")
            ->setParameter('slug', $slug)
            ->getOneOrNullResult();
    }

    /**
     * This is actual only for param converter repository method
     *
     * @param $slug
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findBySlugWithRelations($slug)
    {
        return $this->getEntityManager()
            ->createQuery("SELECT g, i, t, au, ug, gs, f, gsu
                           FROM AppBundle:Goal g
                           LEFT JOIN g.tags t
                           LEFT JOIN g.images i
                           LEFT JOIN g.author au
                           LEFT JOIN au.userGoal ug
                           LEFT JOIN g.successStories gs
                           LEFT JOIN gs.user gsu
                           LEFT JOIN gs.files f
                           WHERE g.slug = :slug")
            ->setParameter('slug', $slug['slug'])
            ->getOneOrNullResult();
    }

    /**
     * @param $slug
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findBySlugWithTinyRelations($slug)
    {
        //check if slug is array
        if(is_array($slug)) {
            $slug = $slug['slug'];
        }

        return $this->getEntityManager()
            ->createQuery("SELECT g, i
                           FROM AppBundle:Goal g
                           LEFT JOIN g.images i
                           WHERE g.slug = :slug")
            ->setParameter('slug', $slug)
            ->getOneOrNullResult();
    }

    /**
     * @param $goalId
     * @param $status
     * @param null $first
     * @param null $count
     * @param null $search
     * @return array|Query
     */
    public function findGoalUsers($goalId, $status, $first = null, $count = null, $search = null)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('u, ug')
            ->from('ApplicationUserBundle:User', 'u', 'u.id')
            ->join('u.userGoal', 'ug', 'WITH', '(ug.status = :status) OR (:status IS NULL AND ug.status IN (1, 2))')
            ->join('ug.goal', 'g', 'WITH', 'g.id = :goalId')
            ->setParameter('status', $status)
            ->setParameter('goalId', $goalId);

        if ($search){
            $query->andWhere("u.firstname LIKE :search
                           or u.lastname LIKE :search
                           or u.email LIKE :search
                           or CONCAT(u.firstname, u.lastname) LIKE :search")
                ->setParameter('search', '%' . $search . '%');
        }

        if (is_numeric($first) && is_numeric($count)){
            $query
                ->setFirstResult($first)
                ->setMaxResults($count);

            $paginator = new Paginator($query, $fetchJoinCollection = true);
            return $paginator->getIterator()->getArrayCopy();
        }

        return $query->getQuery()->getResult();
    }

    /**
     * This function is used to get goal group by created date
     *
     * @param $limit
     * @return array
     */
    public function findGoalGroupByCreationDateByAdmin($limit, $ids)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT DATE(g.created) as dates, COUNT(g.created) as counts
						   FROM AppBundle:Goal g
						   WHERE g.created > :limit AND (g.author is null OR g.author in (:ids))
						   GROUP BY dates
						   ORDER BY dates')
            ->setParameter('limit', $limit)
            ->setParameter('ids', $ids)
            ->getResult();
    }

    /**
     * This function is used to get goal group by created date
     *
     * @param $limit
     * @return array
     */
    public function findGoalGroupByCreationDateByUser($limit, $ids)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT DATE(g.created) as dates, COUNT(g.created) as counts
						   FROM AppBundle:Goal g
						   WHERE g.created > :limit AND (g.author is not null AND g.author not in (:ids))
						   GROUP BY dates
						   ORDER BY dates')
            ->setParameter('limit', $limit)
            ->setParameter('ids', $ids)
            ->getResult();
    }

    /**
     * This function is used to get published goal group by publishedDate
     *
     * @param $limit
     * @return array
     */
    public function findPublishedGoalGroupByDate($limit)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT DATE(g.publishedDate) as dates,COUNT(g.publishedDate) as counts, g.publishedBy
								   FROM AppBundle:Goal g
								   WHERE g.publish = :publish
                                   AND g.publishedDate is NOT NULL
								   AND g.publishedDate > :limit
								   GROUP BY g.publishedBy, dates
								   ORDER BY dates')
            ->setParameter('publish', Goal::PUBLISH)
            ->setParameter('limit', $limit)
            ->getResult();
    }

    /**
     * This function is used to get goal group by updated dates
     *
     * @param $limit
     * @return array
     */
    public function findGoalGroupByUpdateDate($limit)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT DATE(g.updated ) as dates, COUNT(g.updated) as counts
								   FROM AppBundle:Goal g
								   WHERE g.updated > :limit
								   GROUP BY dates
								   ORDER BY dates')
            ->setParameter('limit', $limit)
            ->getResult();
    }

    /**
     * This function is used to do random data in array
     *
     * @param $ids
     * @return array
     */
    public function shuffle_goal($ids) {

        //check if ids not exist
        if(!is_array($ids)) {

            return $ids;
        }

        //get key in array
        $keys = array_keys($ids);

        //random array by key
        shuffle($keys);

        //set random default array
        $random = array();

        foreach ($keys as $key) {
            $random[$key] = $ids[$key];
        }

        return $random;
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findGoalWithAuthor($id)
    {
        return $this->getEntityManager()
            ->createQuery("SELECT g, a
                           FROM AppBundle:Goal g
                           LEFT JOIN g.author a
                           WHERE g.id = :id")
            ->setParameter('id', $id)
            ->getOneOrNullResult();
    }

    /**
     * @param $user
     * @param null $first
     * @param null $count
     * @return mixed
     */
    public function findMyPrivateGoals($user, $first = null, $count = null)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('g, i')
            ->from('AppBundle:Goal', 'g')
            ->leftJoin('g.images', 'i')
            ->leftJoin('g.author', 'a')
            ->where('a.id = :user')
            ->andWhere('g.status = :status')
            ->andWhere('g.readinessStatus = :readinessStatus')
            ->orderBy('g.id', 'desc')
            ->setParameter('user', $user)
            ->setParameter('status', Goal::PRIVATE_PRIVACY)
            ->setParameter('readinessStatus', Goal::TO_PUBLISH)
        ;

        if (!is_null($first) && !is_null($count)){
            $query
                ->setFirstResult($first)
                ->setMaxResults($count);

            $paginator = new Paginator($query, $fetchJoinCollection = true);
            return $paginator->getIterator()->getArrayCopy();
        }
        
        return $query->getQuery();
    }

    /**
     * @param $user1Id
     * @param $user2Id
     * @param $first
     * @param $count
     * @return array
     */
    public function findCommonGoals($user1Id, $user2Id, $first, $count)
    {
        $queryIds = $this->getEntityManager()
            ->createQuery("SELECT DISTINCT g.id
                           FROM AppBundle:Goal g
                           JOIN g.userGoal ug WITH ug.user = :user1Id
                           JOIN g.userGoal ug1 WITH ug1.user = :user2Id
                           LEFT JOIN g.images img")
            ->setParameter('user1Id', $user1Id)
            ->setParameter('user2Id', $user2Id)
            ->getArrayResult();

        $currentIds = array_map(function ($a) { return $a['id']; }, array_slice($queryIds, $first, $count));

        if (count($currentIds) == 0){
            return [];
        }

        return $this->getEntityManager()
                    ->createQuery("SELECT g, img
                                   FROM AppBundle:Goal g
                                   INDEX BY g.id
                                   LEFT JOIN g.images img
                                   WHERE g.id in (:ids)")
                    ->setParameter('ids', $currentIds)
                    ->getResult();
    }



//    public function findOwnedGoalsCount($owner, $onlyPublish = false)
//    {
//        $query = $this->getEntityManager()
//            ->createQueryBuilder()
//            ->select('count(g)')
//            ->from('AppBundle:Goal', 'g')
//            ->andWhere('g.author = :owner')
//            ->setParameter('owner', $owner);
//        ;
//
//        if($onlyPublish){
//            $query
//                ->andWhere('g.publish = 1')
//            ;
//        }
//
//        return $query->getQuery()->getSingleScalarResult();
//
//    }


    /**
     * @param $owner
     * @param $publish
     * @return array|null
     */
    public function findOwnedGoalsCount($owner, $publish)
    {

        $filter = $this->getEntityManager()->getFilters();
        $filter->isEnabled('visibility_filter') ? $filter->disable('visibility_filter') : null;

        $query = $this->getEntityManager()
            ->createQueryBuilder();

        if(!$publish){
            $query
                ->select('count(ug)')
                ->from('AppBundle:UserGoal', 'ug')
                ->join('ug.goal', 'g')
                ->addOrderBy('ug.updated', 'DESC')
                ->andWhere('ug.user = :owner')
            ;

        }else{
            $query
                ->select('count(g)')
                ->from('AppBundle:Goal', 'g')
                ->andWhere('g.publish = :publish')
                ->setParameter('publish', PublishAware::PUBLISH)
            ;
        }

        $query
            ->andWhere('g.author = :owner')
            ->orderBy('g.created', 'DESC')
            ->setParameter('owner', $owner)
            ->getQuery()->getSingleScalarResult();
        ;

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $owner
     * @param $first
     * @param $count
     * @param $publish
     * @param bool $getLastUpdated
     * @return array
     */
    public function findOwnedGoals($owner, $first, $count, $publish, $getLastUpdated = false)
    {

        $filter = $this->getEntityManager()->getFilters();
        $filter->isEnabled('visibility_filter') ? $filter->disable('visibility_filter') : null;

        $query = $this->getEntityManager()
            ->createQueryBuilder();

        $query
            ->addSelect('g, i');

        if(!$publish){
            $query
                ->select('ug')
                ->from('AppBundle:UserGoal', 'ug')
                ->join('ug.goal', 'g')
                ->addOrderBy('ug.updated', 'DESC')
                ->andWhere('ug.user = :owner')
            ;

        }else{
            $query
                ->from('AppBundle:Goal', 'g')
                ->andWhere('g.publish = :publish')
                ->setParameter('publish', PublishAware::PUBLISH)
            ;
        }

        $query
            ->leftJoin('g.images', 'i')
            ->andWhere('g.author = :owner')
            ->setFirstResult($first)
            ->setMaxResults($count)
            ->orderBy('g.created', 'DESC')
            ->setParameter('owner', $owner)
        ;
        ;




        if($getLastUpdated){

            if(!$publish){
                $query->select('ug.updated');
            }else{
                $query->select('g.updated');
            }
            $result = $query
                ->getQuery()
                ->getResult();


            $lastUpdated = null;

            array_map(function ($item) use (&$lastUpdated){
                $lastUpdated = $item['updated'] > $lastUpdated ? $item['updated'] : $lastUpdated;
            }, $result);

            return $lastUpdated;
        }

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        return $paginator->getIterator()->getArrayCopy();
    }


    /**
     * @param $userId
     * @param $userIds
     * @return array
     */
    public function findCommonCounts($userId, $userIds)
    {
        if (count($userIds) == 0){
            return [];
        }

        return $this->getEntityManager()
            ->createQuery("SELECT u.id, COUNT(mug.id) as commonGoals
                           FROM ApplicationUserBundle:User u
                           INDEX BY u.id
                           LEFT JOIN u.userGoal ug
                           LEFT JOIN AppBundle:UserGoal mug WITH mug.goal = ug.goal AND mug.user = :userId
                           WHERE u.id IN (:userIds)
                           GROUP BY u.id")
            ->setParameter('userId', $userId)
            ->setParameter('userIds', $userIds)
            ->getResult();
    }

    /**
     * @param $goalId
     * @param $authorId
     * @return array
     */
    public function findImportantAddedUsers($goalId, $authorId = null)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('u')
            ->from('ApplicationUserBundle:User', 'u')
            ->join('u.userGoal', 'ug', 'with', 'ug.goal = :goalId AND ug.important = true')
            ->setParameter('goalId', $goalId)

        ;

        if($authorId){
            $query
                ->andWhere('u.id != :authorId')
                ->setParameter('authorId', $authorId)
            ;
        }

        return $query->getQuery()->getResult();
    }

    /**
     * This function is used to get all goal by place
     *
     * @param $placeIds
     * @param $userId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findAllByPlaceIds($placeIds, $userId)
    {
        return $this->getEntityManager()
            ->createQuery("SELECT g, p
                           FROM AppBundle:Goal g
                           JOIN g.place p
                           LEFT JOIN AppBundle:UserGoal ug WITH ug.goal = g and ug.user = :userId
                           LEFT JOIN ug.user u
                           WHERE p.id in (:placeIds) and (ug.id is null or ug.confirmed = :status)")
            ->setParameter('userId', $userId)
            ->setParameter('placeIds', $placeIds)
            ->setParameter('status', false)
            ->getResult();
    }

    /**
     * @param $goalIds
     * @return array
     */
    public function findAllByIds($goalIds)
    {
        return $this->getEntityManager()
            ->createQuery("SELECT g, ug, u
                           FROM AppBundle:Goal g
                           LEFT JOIN g.userGoal ug
                           LEFT JOIN ug.user u
                           WHERE g.id in (:goalIds)")
            ->setParameter('goalIds', $goalIds)
            ->getResult();
    }


    /**
     * @param $userId
     * @return array
     */
    public function findUserUnConfirmInPlace($userId)
    {
        return $this->getEntityManager()
            ->createQuery("SELECT g, p, img, ug
                           FROM AppBundle:Goal g
                           JOIN g.place p
                           LEFT JOIN g.userGoal ug WITH ug.goal = g and ug.user = :userId 
                           LEFT JOIN ug.user u
                           LEFT JOIN g.images img
                           WHERE ug.id is null or (ug.confirmed != :status AND ug.status != :completed) ")
            ->setParameter('userId', $userId)
            ->setParameter('status', true)
            ->setParameter('completed', UserGoal::COMPLETED)
            ->getResult();
    }

    /**
     * @param $goalIds
     * @return array
     */
    public function findGoalByIds($goalIds)
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT g, im
                 FROM AppBundle:Goal g
                 INDEX BY g.id
                 LEFT JOIN g.images im
                 WHERE g.id in (:ids)")
            ->setParameter('ids', $goalIds)
            ->getResult();
    }

    /**
     * This function is used to get random goal
     *
     * @param $count
     * @return array
     */
    public function findRandomGoal($count)
    {
        //get random goal ids
        $goalIds = $this->getEntityManager()
            ->createQuery("SELECT g.id, RAND() as HIDDEN rand
                           FROM AppBundle:Goal g
                           ORDER BY rand 
                           ")
            ->setMaxResults($count)
            ->getResult();

        return $this->getEntityManager()
            ->createQuery("SELECT g, img 
                           FROM AppBundle:Goal g
                           LEFT JOIN g.images img
                           WHERE g.id in (:ids)
                           ")
            ->setParameter('ids', $goalIds)
            ->getResult();
    }

    /**
     * This function is used to get completed, created or added goal statistic data
     *
     * @param $groupBy
     * @param $start
     * @param $end
     * @param $type
     * @return array
     */
    public function getGoalByTypeForStatisticData($groupBy, $start, $end, $type)
    {
        //set default selected date value
        $date = null;

        switch ($type) {
            case StatisticController::TYPE_PUBLISHED_GOAL:
                $date = 'g.publishedDate';
                break;
            case StatisticController::TYPE_ADDED_GOAL:
                $date = 'ug.listedDate';
                break;
            case StatisticController::TYPE_COMPLETED_GOAL:
                $date = 'ug.completionDate';
                break;
            default:
                break;
        }

        //get completed, added or created goals statistic data
        $goals = $this->getEntityManager()
            ->createQueryBuilder()
            ->select("COUNT(g.id) as total, DATE(".$date.") as created")
            ->from('AppBundle:Goal', 'g')
            ->leftJoin('g.userGoal', 'ug')
            ->where(''.$date.' is NOT NULL');

        //check if type is published goal
        if($type == StatisticController::TYPE_PUBLISHED_GOAL) {
            $goals
                ->andWhere('g.publish = :publish')
                ->setParameter('publish', Goal::PUBLISH);
        }

        //get filtered statistic data
        $data = $this->filterStatisticData($goals, $date, $groupBy, $start, $end);

        return $data;
    }

    /**
     * This function is used to get created goal by admin or not admin statistic data
     *
     * @param $groupBy
     * @param $start
     * @param $end
     * @return mixed
     */
    public function getCreatedGoalStatisticData($groupBy, $start, $end)
    {
        //set selected date
        $date = 'g.created';

        //get completed, added or created goals statistic data
        $goals = $this->getEntityManager()
            ->createQueryBuilder()
            ->select("COUNT(g.id) as total, DATE(".$date.") as created,
             SUM(CASE WHEN at.id IS NULL OR at.isAdmin = (:isAdmin) THEN 1 ELSE 0 END) as byAdmin")
            ->from('AppBundle:Goal', 'g')
            ->leftJoin('g.author', 'at')
            ->setParameter('isAdmin', true);

        //get filtered statistic data
        $data = $this->filterStatisticData($goals, $date, $groupBy, $start, $end);

        //generate created by user goal count
        $data = array_map(function(&$item){
            $item['byUser'] = $item['total'] - $item['byAdmin'];
            return $item;
        }, $data);

            return $data;
    }
}