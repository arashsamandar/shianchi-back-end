<?php
namespace Wego\Product\Specials;

use Illuminate\Support\Facades\Lang;
use Recombee\RecommApi\Client;
use Recombee\RecommApi\Requests\UserBasedRecommendation as URecomm;
use Wego\Services\Recommendation\RecombeeRecommender;

/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 07/09/17
 * Time: 16:16
 */
class UserBasedRecommendation extends AbstractSpecial
{

    private $clientId;

    public function getProducts()
    {
        $recommender = new RecombeeRecommender();
        $recommendedProductId = $recommender->UserBaseProductRecommendation($this->clientId);
        return $this->productElasticSearch->recommended($recommendedProductId);
    }

    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function setTitle()
    {
        $this->title = Lang::get('ProductGroupMessage.user_base_recommendation');
        return $this;
    }
}