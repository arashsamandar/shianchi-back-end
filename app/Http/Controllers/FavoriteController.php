<?php

namespace App\Http\Controllers;

use App\Repositories\FavoriteRepository;
use App\Repositories\UserRepository;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use App\Http\Requests;
use Tymon;

use Elasticsearch;

class FavoriteController extends ApiController
{
    use Helpers;
    protected $favoriteRepository, $userRepository;

    function __construct(FavoriteRepository $favoriteRepository, UserRepository $userRepository)
    {
        $this->favoriteRepository = $favoriteRepository;
        $this->userRepository = $userRepository;
    }


    public function favoriteStoreFormat($item)
    {
        return [

            "store" => [
                "id" => $item["stores"][0]["id"],
                "english_name" => $item["stores"][0]["english_name"],
                "persian_name" => $item["stores"][0]["user"]["name"],
                "path" => $item["stores"][0]["pictures"][0]["path"],
                "phones" => $item["stores"][0]["store_phones"][0]["prefix_phone_number"] . $item["stores"][0]["store_phones"][0]["phone_number"],
                "information" => $item["stores"][0]["information"]
            ]
        ];
    }

    public function favoriteProductFormat($item)
    {
        return [
            "id" => $item["id"],
            "product" => [
                "english_name" => $item["products"][0]["english_name"],
                "persian_name" => $item["products"][0]["persian_name"],
                "id" => $item["products"][0]["id"],
                "path" => $item["products"][0]["pictures"][0]["path"]
            ],
            "store" => [
                "id" => $item["products"][0]["store"]["id"],
                "english_name" => $item["products"][0]["store"]["english_name"],
                "persian_name" => $item["products"][0]["store"]["user"]["name"],
                "path" => $item["products"][0]["store"]["pictures"][0]["path"],
                "phones" => $item["products"][0]["store"]["store_phones"][0]["prefix_phone_number"] . $item["products"][0]["store"]["store_phones"][0]["phone_number"],
                "information" => $item["products"][0]["store"]["information"]
            ]
        ];
    }

    public function getStoreFavorite()
    {
        $user = $this->auth->user();
        $storeFavorite = $this->favoriteRepository->getStoreFavoriteByUser($user->id);
        return array_map([$this, 'favoriteStoreFormat'], $storeFavorite);

    }

    public function getProductFavorite()
    {
        $user = $this->auth->user();
        $favoriteProducts = $this->favoriteRepository->getProductFavoriteByUser($user->id);
        if (empty($favoriteProducts))
            return $this->respond('no favorite product found');
        $favoriteProducts = array_filter($favoriteProducts,function($item){
           return (!empty($item['products']));
        });
        $favoriteProducts = array_values($favoriteProducts);
        return array_map([$this, 'favoriteProductFormat'], $favoriteProducts);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     * need type in request
     * type = 1 for product favorite just for buyer
     * type = 2 for store store and buyer can add this
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function addProductToFavorites(Request $request)
    {
        $user = $this->auth->user();
        if ($this->favoriteRepository->userAddedThisProductBefore($user->id, $request->input('id'))) {
            return $this->respondNotFound('added product');
        }
        $this->userRepository->attachProductToUser($user->id, $request->input('id'));
        return $this->respond("favorite created");
    }

    public function addStoreToFavorites(Request $request)
    {
        $user = $this->auth->user();
        if ($this->favoriteRepository->userAddedThisStoreBefore($user->id, $request->input('id'))) {
            return $this->respondNotFound('added store');
        }
        $this->userRepository->attachStoreToUser($user->id, $request->input('id'));
        return $this->respond("favorite created");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = $this->auth->user();

        $competitor = $this->favoriteRepository->deleteFromFavorite($user->id, $id);

        if ($competitor === 0)
            return $this->respondNotFound('not deleted');
        return $this->respond('successfully deleted favorite');
    }
}
