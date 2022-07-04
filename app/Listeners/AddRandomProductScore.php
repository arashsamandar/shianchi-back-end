<?php

namespace App\Listeners;

use App\Events\ProductAdded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class AddRandomProductScore implements ShouldQueue
{

    public function handle(ProductAdded $event)
    {
        $request= [];
        for($i=1;$i<=4;$i++){
            $request[] = ['user_id' => 23, 'product_id' => $event->product->id, 'score' => rand(3, 5),
                'score_title_id' => $i , 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()];
        }
        DB::table('product_score')->insert($request);

    }
}
