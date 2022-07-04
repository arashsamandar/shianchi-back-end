<?php

namespace App\Http\Controllers;

use App\Product;
use App\ScoreTitle;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon;
use App\Http\Requests;
use Wego\UserHandle\UserPermission;

class ProductScoreController extends ApiController
{
    const SCORE_TITLE_SIZE = 4;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ScoreTitle::all();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = UserPermission::checkBuyerPermission();
        $mainRequest = $request->input('scores');

        if (count($mainRequest) > self::SCORE_TITLE_SIZE)
            return $this->setStatusCode(404)->respondWithError('لطفا دوباره تلاش کنید');

        try {
            $this->insertScore($mainRequest, $user->id);

        } catch (QueryException $e) {
            return $this->respondWithError('شما قبلا به این محصول رای داده اید');
        }
        return $this->respondOk('ok', 'message');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $result = $this->getAverageAndCountScoreForEachProduct($id);
        if (empty($result))
            return $this->prepareEmptyAverageAndCountScoreForEachProduct($id);
        return $result;
    }

    private static function getAverageAndCountScoreForEachProduct($productId)
    {
        return DB::table('product_score')->join('score_title', 'product_score.score_title_id', '=', 'score_title.id')
            ->where('product_id', '=', $productId)->select(DB::raw('CONVERT(ROUND(avg(score)),UNSIGNED INTEGER) as score ,count(score) as total, score_title.id , score_title.name'))
            ->groupBy('product_score.score_title_id')->get();
    }

    /**
     *
     */
    public static function addAverageScoreToAllProducts()
    {
        $products = Product::where('confirmation_status', Product::CONFIRMED)->get();
        foreach ($products as $product) {
            $avgScore = self::getAverageAndCountScoreForEachProduct($product->id);
            $avg = self::calculateTotalAverageScore($avgScore);
            self::addScoreToProductElastic($product->id, $avg);
        }
    }

    private static function calculateTotalAverageScore($scores)
    {
        $avg = 0;
        foreach ($scores as $score) {
            $avg += $score->score;
        }
        $avg = $avg / 4;
        return $avg;
    }

    /**
     * @param $productId
     * @param $avgScore
     */
    public static function addScoreToProductElastic($productId, $avgScore)
    {
        $result = Product::find($productId);

        if (empty($avgScore)) {
            $avg = 0;
        } else {
            $avg = $avgScore;
        }
        $result->average_score = $avg;
        $result->save();
        Product::where('id',$productId)->elastic()->addToIndex();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    private function insertScore(&$mainRequest, $userId)
    {
        foreach ($mainRequest as &$item) {
            $item["user_id"] = $userId;
            $item["score_title_id"] = $item['id'];
            $item['created_at'] = Carbon::now();
            $item['updated_at'] = Carbon::now();
            unset($item['id']);
        }
        //dd($mainRequest);
        unset($item);
        DB::table('product_score')->insert($mainRequest);
    }

    private function prepareEmptyAverageAndCountScoreForEachProduct($id = '')
    {
        $scoreTitles = DB::table('score_title')->get();
        $result = [];
        foreach ($scoreTitles as $scoreTitle) {
            $result[] = ["score" => 0, "total" => 0, "id" => $scoreTitle->id, "name" => $scoreTitle->name];
        }
        return $result;
    }
}
