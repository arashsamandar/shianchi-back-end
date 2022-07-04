<?php

namespace App\Http\Controllers;

use App\Buyer;
use App\Message;
use App\Product;
use App\ReadMessage;
use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Elasticsearch\ClientBuilder;
use App\Http\Requests;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon;
use Wego\ElasticHelper;
use Wego\UserHandle\UserPermission;
use Wego\ShamsiCalender\Shamsi;
use Illuminate\Support\Facades\Validator;

class WegoMessageController extends ApiController
{
    use Helpers;
    protected $requestMessageRules = [
        'subject' => 'required',
        'body' => 'required'
    ];

    /**
     * store a new wego message
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Requests\StoreWegoMessageRequest $request)
    {
        $validator = Validator::make($request->toArray(), $this->requestMessageRules);
        if ($validator->fails())
            return $this->setStatusCode(404)->respondWithError($validator->errors()->all());
        $params = $this->buildWegoMessageSchema($request);
        $client = ClientBuilder::create()->build();
        $response = $client->index($params);
        return $this->respond('general message added successfully');
    }

    /**
     * check if the user did not read the wego message before
     *
     * @param $id
     * @param $user
     * @return bool
     */
    public function checkIfNotRead($id, $user)
    {
        $params = [
            'index' => 'wego_1',
            'type' => 'wego_message',
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match' => ['_id' => $id]],
                            ['match' => ['seen_ids' => $user->id]],
                        ]
                    ]
                ]
            ]
        ];
        $client = ClientBuilder::create()->build();
        $results = $client->search($params);
        if ($results['hits']['total'] < 1) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * get the public wego message which is specified by id
     *
     * @param $id
     * @return array
     */
    public function getWegoMessage($id)
    {
        $params = [
            'index' => 'wego_1',
            'type' => 'wego_message',
            'body' => [
                'query' => [
                    'match' => [
                        '_id' => $id
                    ]
                ]
            ]
        ];
        $client = ClientBuilder::create()->build();
        return $client->search($params);

    }

    /**
     * adds the user id to the list of users who have seen the wego message
     *
     * @param $id
     */
    public function readWegoMessage($id)
    {
        $user = $this->auth->user();
        if ($this->checkIfNotRead($id, $user)) {
            $results = $this->getWegoMessage($id);

            if (is_array($results['hits']['hits'][0]['_source']['seen_ids'])) {
                array_push($results['hits']['hits'][0]['_source']['seen_ids'], $user->id);
            } else {
                $results['hits']['hits'][0]['_source']['seen_ids'] = array($user->id);
            }
            $params = [
                'index' => 'wego_1',
                'type' => 'wego_message',
                'id' => $id,
                'body' => [
                    'doc' => $results['hits']['hits'][0]['_source']
                ]

            ];
            $client = ClientBuilder::create()->build();
            $response = $client->update($params);

        }

    }

    /**
     * return all wego messages
     *
     * @return array
     */
    public function search()
    {
        $params = [
            'index' => 'wego_1',
            'type' => 'wego_message',
            'body' => [
                'query' => [
                    'match_all' => []
                ]
            ]
        ];
        $client = ClientBuilder::create()->build();
        $results = $client->search($params);
        return $results;
    }

    /**
     * delete the specified public wego message by id
     *
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        $params = [
            'index' => 'wego_1',
            'type' => 'wego_message',
            'id' => $id
        ];
        $client = ClientBuilder::create()->build();
        $response = $client->delete($params);
        return $this->respondOk('success', 'message');
    }

    /**
     * gets the unread wego messages of the user
     *
     * @return array
     */
    public function getUnreadWegoMessages(Request $request)
    {
        $user = $this->auth->user();
        $params = [
            'index' => 'wego_1',
            'type' => 'wego_message',
            'body' => [
                'query' => [
                    'bool' => [
                        'must_not' => [
                            ['match' => ['seen_ids' => $user->id]]
                        ]
                    ]
                ]
            ]
        ];
        $client = ClientBuilder::create()->build();
        $results = $client->search($params);
        $from = $request->input('from');
        $response = ElasticHelper::paginate($results, $from);
        return $response;

    }

    public function getWegoMessages(Request $request)
    {
        $params = [
            'index' => 'wego_1',
            'type' => 'wego_message',
            'body' => [
                'query' => [
                    'match_all' => []
                ]
            ]
        ];
        $client = ClientBuilder::create()->build();
        $results = $client->search($params);
        $from = $request->input('from');
        $response = ElasticHelper::paginate($results, $from);
        return $response;
    }

    /**
     * @param $request
     * @return array
     */
    public function buildWegoMessageSchema($request)
    {
        return $params = [
            'index' => 'wego_1',
            'type' => 'wego_message',
            'body' => [
                'subject' => $request->input('subject'),
                'body' => $request->input('body'),
                'sending_time' => Carbon::now()->toDateTimeString(),
                'seen_ids' => ''
            ],
        ];
    }
}
