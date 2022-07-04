<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 9/5/17
 * Time: 9:31 AM
 */

namespace Wego\Services\Recommendation;


use App\Product;
use Dingo\Api\Exception\ValidationHttpException;
use Recombee\RecommApi\Client;
use Recombee\RecommApi\Requests\AddBookmark;
use Recombee\RecommApi\Requests\AddCartAddition;
use Recombee\RecommApi\Requests\AddDetailView;
use Recombee\RecommApi\Requests\AddPurchase;
use Recombee\RecommApi\Requests\AddRating;
use Recombee\RecommApi\Requests\ItemBasedRecommendation;
use Recombee\RecommApi\Requests\UserBasedRecommendation;

class RecombeeRecommender
{
    protected $client;

    function __construct()
    {
        $this->client = new Client('wegobazaar', 'UbsqmSduaoy23L8z4dnSWW09piBEfu6W927JJ0O6N6SK2wAqv173wKaWIRecaIrA');
    }

    public function UserBaseProductRecommendation($clientId, $take = 10)
    {
        $recommended = $this->client->send(new UserBasedRecommendation($clientId, $take, ['cascadeCreate' => true, 'filter' => "'item_type'==\"product\""]));
        if (empty($recommended)){
            throw new ValidationHttpException();
        }
        return $recommended;
    }

    public function productBaseProductRecommendation($clientId, $productId, $take = 10)
    {
        $recommended = $this->client->send(new ItemBasedRecommendation($productId, $take, ['targetUserId' => $clientId, 'filter' => "'item_type'==\"product\"", 'cascadeCreate' => true]));
        if (empty($recommended)){
            throw new ValidationHttpException();
        }
        return $recommended;
    }

    public function addProductDetailView($clientId, $productId)
    {
        if (!empty($clientId) && strcmp($clientId, "\"\"") && strcmp($clientId, "null")) {
            $this->client->send(new AddDetailView($clientId, $productId, ['cascadeCreate' => true]));
            $this->client->send(new AddDetailView($clientId, 'category-' . Product::findOrFail($productId)->category->id, ['cascadeCreate' => true]));
        }
    }

    public function addProductFavorite($clientId, $productId)
    {
        if (!empty($clientId) && strcmp($clientId, "\"\"") && strcmp($clientId, "null")) {
            $this->client->send(new AddBookmark($clientId, $productId, ['cascadeCreate' => true]));
        }
    }

    public function addProductRating($clientId, $productId, $rating)
    {
        $rating = ($rating - 3) / 2;
        if (!empty($clientId) && strcmp($clientId, "\"\"") && strcmp($clientId, "null")) {
            $this->client->send(new AddRating($clientId, $productId, $rating, ['cascadeCreate' => true]));
        }
    }

    public function addPurchase($clientId,$productId)
    {
        if (!empty($clientId) && strcmp($clientId, "\"\"") && strcmp($clientId, "null")) {
            $this->client->send(new AddPurchase($clientId, $productId, ['cascadeCreate' => true]));
        }
    }

    public function addCardAddition($clientId,$productId)
    {
        if (!empty($clientId) && strcmp($clientId, "\"\"") && strcmp($clientId, "null")) {
            $this->client->send(new AddCartAddition($clientId, $productId, ['cascadeCreate' => true]));
        }
    }

}