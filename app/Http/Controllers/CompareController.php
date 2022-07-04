<?php

namespace App\Http\Controllers;

use App\Store;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;

use App\Http\Requests;
use Wego\Search\ElasticCompare;

class CompareController extends Controller
{


    /**
     * @param Request $request
     * @return array
     * @internal param $id
     */
    public function show(Request $request)
    {

        $d = new ElasticCompare($request->input('id'));


        return $d->run();

    }

    public function find(Request $request)
    {
        $d = new ElasticCompare($request->input('id'));
        $client = ClientBuilder::create()->build();
        return $client->search($d->setSource("pictures")->createQuery($request->input('id')));
    }

}
