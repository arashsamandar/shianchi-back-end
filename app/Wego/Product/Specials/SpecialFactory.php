<?php

namespace Wego\Product\Specials;
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 07/09/17
 * Time: 15:40
 */
class SpecialFactory
{
    /**
     * @param $type
     * @param $clientId
     * @return MostSell|MostViewed|RecentlyAdded
     */
    public static function create($type, $clientId = 0)
    {
        switch ($type) {
            case 0:
                return (new RecentlyAdded());
            case 1:
                return (new MostViewed());
            case 2:
                return (new UserBasedRecommendation())->setClientId($clientId);
            case 3:
                return (new MostSell());
            case 4:
                return (new DailyOffer());
            default:
                return (new RandomFromTopCategories());
        }
    }
}