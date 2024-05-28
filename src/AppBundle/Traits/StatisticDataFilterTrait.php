<?php

namespace AppBundle\Traits;
use AppBundle\Controller\Rest\StatisticController;

/**
 * Class StatisticDataFilterTrait
 * @package AppBundle\Traits
 */
trait StatisticDataFilterTrait
{
    /**
     * This function is used to filter statistic data
     *
     * @param $query
     * @param $date
     * @param $groupBy
     * @param $start
     * @param $end
     * @return mixed
     */
    public function filterStatisticData($query, $date, $groupBy, $start, $end)
    {
        //if start date is exists
        if ($start) {
            $query
                ->andWhere(':start <= date('.$date.')')
                ->setParameter('start', $start);
        }

        //if end date is exists
        if ($end) {
            $query
                ->andWhere(':end >= date('.$date.')')
                ->setParameter('end', $end);
        }

        // switch for group by
        switch ($groupBy) {

            case StatisticController::DAY:
                $query
                    ->groupBy('created')
                    ->orderBy('created');
                break;
            case StatisticController::MONTH:
                $query
                    ->addSelect('month('.$date.') as hidden mn')
                    ->groupBy('mn')
                    ->orderBy('mn');
                break;
            default:
                break;
        }

        //get result
        $data = $query->getQuery()->getResult();

        return $data;
    }

    /**
     * This function is used to convert statistic string type to integer
     *
     * @param $type
     * @return int
     */
    public function convertStatisticType($type)
    {
        //switch for type name
        switch (strtolower($type)) {
            case 'email':
                $type = StatisticController::TYPE_EMAIL;
                break;
            case 'device':
                $type = StatisticController::TYPE_REG_USER_BY_DEVICE;
                break;
            case 'social':
                $type = StatisticController::TYPE_REG_USER_BY_SOCIAL;
                break;
            case 'published-goal':
                $type = StatisticController::TYPE_PUBLISHED_GOAL;
                break;
            case 'created-goal':
                $type = StatisticController::TYPE_CREATED_GOAL;
                break;
            case 'added-goal':
                $type = StatisticController::TYPE_ADDED_GOAL;
                break;
            case 'completed-goal':
                $type = StatisticController::TYPE_COMPLETED_GOAL;
                break;
            case 'story-create':
                $type = StatisticController::TYPE_STORY_CREATED;
                break;
            case 'story-like':
                $type = StatisticController::TYPE_STORY_LIKED;
                break;
            default:
                break;
        }

        return $type;
    }
}