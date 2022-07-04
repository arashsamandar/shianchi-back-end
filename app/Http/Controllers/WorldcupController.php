<?php

namespace App\Http\Controllers;

use App\Buyer;
use App\User;
use App\WorldcupGame;
use App\WorldcupPredictions;
use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Wego\Helpers\JsonUtil;
use Wego\Helpers\PersianUtil;

class WorldcupController extends ApiController
{
    use Helpers;
    public function storeGame(Request $request)
    {
        WorldcupGame::create($request->all());
    }
    public function updateGameTime(Request $request)
    {
        WorldcupGame::where('id',$request->id)->update(['game_time'=>$request->game_time]);
    }

    public function setResult($id)
    {
        $game = WorldcupGame::find($id);
        $game->result = request()->result;
        $game->save();
        $predictions = WorldcupPredictions::where('game_id',$id)->get();
        $this->calculateScoreForEachPredictor($predictions);
        return $this->respondOk();
    }

    public function getGames()
    {
        $games = WorldcupGame::whereNotNull('result')->get();
        return $games;
    }

    public function getUserPredictionInfo()
    {
        $user = $this->auth->user();
        $predictions = WorldcupPredictions::where('user_id',$user->id)->get();
        $predicts = $predictions->toArray();
        $predicts = JsonUtil::removeFields($predicts,['*.user_id','*.game','*.mobile_number','*.id']);
        $result =  ['score'=>$user->userable->comment_score,'prediction'=>$predicts,
            'mobile_number'=>$user->userable->mobile_number];
        return $result;
    }

    public function predict()
    {
        $user = $this->auth->user();
        $gameId = request()->game_id;
        $game = WorldcupGame::find($gameId);
        $now = Carbon::now();
        $data = request()->all();
        if($now->gte(Carbon::parse($game->game_time)->addMinutes(1))){
            return $this->respondWithError("تنها تا قبل از برگزاری بازی مجاز به پیش بینی هستید");
        }
        $data['user_id'] = $user->id;
        $data['mobile_number'] = $user->userable->mobile_number;
        try {
            WorldcupPredictions::create($data);
        } catch (\Exception $e){
            return $this->respondWithError("شما قبلا این بازی را پیش بینی کرده اید");
        }
        return $this->respondOk();
    }

    public function getBestScores()
    {
        $user = $this->auth->user();
        $top = Buyer::select(['comment_score as score','id','mobile_number'])->whereNotNull('comment_score')
            ->whereNotNull('mobile_number')
            ->orderBy('comment_score','desc')
            ->take(20)->get();
        $top = $this->changeStyle($top);
        $top = $this->sortResult($top);
        $ranking = 0;
        foreach ($top as $key=>$buyer) {
            if($buyer->user->id == $user->id){
                $ranking = $key+1;
            }
        }
        if ($ranking == 0){
            $commentScore = empty($user->userable->comment_score)? 0 : $user->userable->comment_score;
            $ranking = Buyer::where('comment_score', '>=', $commentScore)->count();
        }
        $top = JsonUtil::removeFields($top,['*.mobile_number','*.correct']);
        $result= ['best_scores'=>$top , 'rank'=>$ranking];
        return $result;

    }

    public function getBestScoresForPrizes()
    {
        $user = $this->auth->user();
        $top = Buyer::select(['comment_score as score','id','mobile_number'])->whereNotNull('comment_score')
            ->whereNotNull('mobile_number')
            ->orderBy('comment_score','desc')
            ->take(20)->get();
        $top = $this->changeStyle($top);
        $top = $this->sortResult($top);
        $ranking = 0;
        foreach ($top as $key=>$buyer) {
            if($buyer->user->id == $user->id){
                $ranking = $key+1;
            }
        }
        if ($ranking == 0){
            $commentScore = empty($user->userable->comment_score)? 0 : $user->userable->comment_score;
            $ranking = Buyer::where('comment_score', '>=', $commentScore)->count();
        }
        $result= ['best_scores'=>$top , 'rank'=>$ranking];
        return $result;

    }

    /**
     * @param $prediction
     * @return bool
     */
    protected function theGameHasResult($prediction)
    {
        return !empty($prediction->game->result);
    }

    /**
     * @param $prediction
     * @return mixed
     */
    protected function gamePredictedCorrectly($prediction)
    {
        return $prediction->game->result == $prediction->prediction;
    }

    /**
     * @param $top
     * @return mixed
     */
    protected function sortResult($top)
    {
        $top = $top->sort(function ($a, $b) {
            if ($a->score == $b->score) {
                return ($b->correct - $a->correct);
            } else {
                return ($b->score - $a->score);
            }
        });
        $top = $top->values()->All();
        return $top;
    }

    /**
     * @param $top
     * @return mixed
     */
    protected function changeStyle($top)
    {
        $top = $top->map(function ($item, $key) {
            $item->mobile = substr_replace($item->mobile_number, "***", 4, 3);
            $predictions = WorldcupPredictions::where('user_id', $item->user->id)->get();
            $correct = 0;
            foreach ($predictions as $prediction) {
                if ($this->theGameHasResult($prediction)) {
                    if ($this->gamePredictedCorrectly($prediction))
                        $correct += 1;
                }
            }
            $item->correct = $correct;
            return $item;
        });
        return $top;
    }

    /**
     * @param $predictions
     */
    protected function calculateScoreForEachPredictor($predictions)
    {
        foreach ($predictions as $prediction) {
            $user = User::find($prediction->user_id);
            $score = 20;
            if ($this->gamePredictedCorrectly($prediction)) {
                if ($prediction->game_id == 2018111) {
                    $score = 500;
                } elseif ($prediction->game_id == 2018222) {
                    $score = 450;
                } elseif ($prediction->game_id == 2018333) {
                    $score = 400;
                } else {
                    $score = 100;
                }
            }
            $buyer = $user->userable;
            $buyer->comment_score += $score;
            $buyer->save();
        }
    }
}
