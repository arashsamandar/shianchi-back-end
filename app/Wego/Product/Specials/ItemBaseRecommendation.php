<?php
namespace Wego\Product\Specials;

use Illuminate\Support\Facades\Lang;
use Recombee\RecommApi\Client;
use Recombee\RecommApi\Requests\ItemBasedRecommendation;
use Wego\Services\Recommendation\RecombeeRecommender;

/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 07/09/17
 * Time: 15:34
 */
class ItemBaseRecommendation extends AbstractSpecial
{

    private $productId, $clientId;

    public function getProducts()
    {
        $recommender = new RecombeeRecommender();
        $recommendedProductId = $recommender->productBaseProductRecommendation($this->clientId,$this->productId);
        return $this->productElasticSearch->recommended($recommendedProductId);
    }

    public function setProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

    public function setTitle()
    {
        $this->title = Lang::get('ProductGroupMessage.item_base_recommendation');
        return $this;
    }

    /**
     * @param mixed $clientId
     * @return ItemBaseRecommendation
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
        return $this;
    }
}