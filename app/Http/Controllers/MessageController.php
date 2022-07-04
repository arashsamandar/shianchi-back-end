<?php

namespace App\Http\Controllers;
use App\Store;
use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Elasticsearch\ClientBuilder;
use App\Http\Requests;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tymon;
use Wego\ElasticHelper;
use Illuminate\Support\Facades\Validator;


class MessageController extends ApiController
{
    use Helpers;

    protected $requestMessageRules = [
        'subject' => 'required',
        'body' => 'required',
        'receiver_id' => 'required'
    ];
    protected $requestReplyRules = [
        'title' => 'required',
        'body' => 'required'
    ];

    const SIZE = 10;

    /**
     * return list of messages
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $params = [
            'index' => 'wego_1',
            'type' => 'message',
            'body' => [
                'query' => [
                    'match' => [
                        'is_read' => false
                    ]
                ]
            ]
        ];
        $client = ClientBuilder::create()->build();
        $results = $client->search($params);
        return $results;
    }

    /**
     * gets receiver's unread messages or replies
     *
     * @return array
     */
    public function getReceiverUnreadMessages(Request $request)
    {
        $user = $this->auth->user();
        $from = ElasticHelper::convertPageValueToFromInElasticSearch($request->input('page'));
        $results = $this->searchOnNotRead('receiver', $user->getUserableType(), $from);
        $response = ElasticHelper::paginate($results, $from);
        $response = $this->setIsReadStatusForReceiverUser($response);
        return $response;

    }

    /**
     * gets sender's unread messages or replies
     *
     * @return array
     */
    public function getSenderUnreadMessages(Request $request)
    {
        $user = $this->auth->user();
        $from = ElasticHelper::convertPageValueToFromInElasticSearch($request->input('page'));
        $results = $this->searchOnNotRead('sender', $user, $from);
        $response = ElasticHelper::paginate($results, $from);
        $response = $this->setIsReadStatusForSenderUser($response);
        return $response;

    }

    /**
     * gets all the messages which the user has been sent
     *
     * @return array
     */
    public function getSenderMessages(Request $request)
    {
        $user = $this->auth->user();
        $from = ElasticHelper::convertPageValueToFromInElasticSearch($request->input('page'));
        $results = $this->searchOnFiled('sender_id', $user->id, $from);
        $response = ElasticHelper::paginate($results, $from);
        $response = $this->setIsReadStatusForSenderUser($response);
        return $response;

    }

    /**
     * get all the message which user has been received
     *
     * @return mixed
     */
    public function getReceiverMessages(Request $request)
    {
        $user = $this->auth->user();
        $from = ElasticHelper::convertPageValueToFromInElasticSearch($request->input('page'));
        $results = $this->searchOnFiled('receiver_id', $user->userable_id, $from);
        $response = ElasticHelper::paginate($results, $from);
        $response = $this->setIsReadStatusForReceiverUser($response);
        return $response;
    }

    /**
     * shows the specific message with its id
     *
     * @param $id
     * @return array
     */
    public function show($id)
    {
        $results = $this->searchOnFiled('_id', $id);
        return $results;

    }

    /**
     * changes the unread messages status to read
     *
     * @param $id
     * @return mixed
     */
    public function readMessage($id) //ghablan request migerefte
    {
        $user = $this->auth->user();
        $results = $this->searchOnFiled('_id', $id);
        if ($this->isSender($results, $user->id)) {
            $updateBody = [
                'sender_isRead' => true
            ];
            $this->updateMessage($updateBody, $id);
            return $this->respondOk('success', 'message');
        } else if ($this->isReceiver($results, $user->userable_id)) {
            $updateBody = [
                'receiver_isRead' => true
            ];
            $this->updateMessage($updateBody, $id);
            return $this->respondOk('success', 'message');
        }
    }

    public function search()
    {
//        $params = [
//            'index' => 'wego_1',
//            'type' => 'message',
//            'body' => [
//                'query' => [
//                    'match_all' => []
//                ]
//            ],
//            'size'=>100,
//            'from'=>0
//        ];
        $params = [
            'index' => 'wego_1',
            'type' => 'message',
            'body' => [
                'query' => [
                    'match_all' => []
                ]
            ]
        ];
        $client = ClientBuilder::create()->build();
        $results = $client->deleteByQuery($params);
        return $results;
    }

    /**
     * delete the specified message and its replies with its id
     *
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        $user = $this->auth->user();
        $results = $this->searchOnFiled('_id', $id);
        $this->checkManipulatePermission($user, $results);
        $params = [
            'index' => 'wego_1',
            'type' => 'message',
            'id' => $id
        ];
        $client = ClientBuilder::create()->build();
        $response = $client->delete($params);
        return $this->respondOk('success', 'message');
    }

    /**
     * add a reply a message with the reply body and requested message id
     *
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function addReplyToMessage($id, Requests\ReplyMessageRequest $request)
    {
        $user = $this->auth->user();
        $validator = Validator::make($request->toArray(), $this->requestReplyRules);
        if ($validator->fails())
            return $this->setStatusCode(404)->respondWithError($validator->errors()->all());
        $results = $this->searchOnFiled('_id', $id);
        $this->checkManipulatePermission($user, $results);
        $updateBody = $this->createUpdateReplyBody($results, $user, $request);
        $this->updateMessage($updateBody, $id);
        return $this->respond('reply has been successfully added');
    }

    /**
     * stores a new message
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Requests\StoreMessageRequest $request)
    {
        $user = $this->auth->user();
        $validator = Validator::make($request->toArray(), $this->requestMessageRules);
        if ($validator->fails())
            return $this->setStatusCode(404)->respondWithError($validator->errors()->all());
        $params = $this->buildMessageSchema($request, $user);
        $client = ClientBuilder::create()->build();
        $response = $client->index($params);
        return $this->respondOk('success', 'message');
    }

    /**
     * get the user's unread messages
     *
     * @param $searchField
     * @param $user
     * @param $from
     * @return array
     */
    public function searchOnNotRead($searchField, $user, $from = 0)
    {
        $search_id = $searchField . '_id';
        $search_isRead = $searchField . '_isRead';
        $params = [
            'index' => 'wego_1',
            'type' => 'message',
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match' => [$search_isRead => false]],
                            ['match' => [$search_id => $user->id]],
                        ]
                    ]
                ]
            ],
            'from' => $from * self::SIZE,
            'size' => self::SIZE
        ];
        $client = ClientBuilder::create()->build();
        return $client->search($params);
    }

    /**
     * search on requested filed for the value
     *
     * @param $searchField
     * @param $value
     * @param int $from
     * @return array
     */
    public function searchOnFiled($searchField, $value, $from = 0)
    {
        $params = [
            'index' => 'wego_1',
            'type' => 'message',
            'body' => [
                'query' => [
                    'match' => [
                        $searchField => $value
                    ],
                ],
                'sort' =>[
                    'sending_time'=>[
                        'order' => 'asc'
                    ],
                ]
            ],
            'from' => $from * self::SIZE,
            'size' => self::SIZE
        ];
        $client = ClientBuilder::create()->build();
        return $client->search($params);
    }

    /**
     * @param Request $request
     * @param $id
     * @return array
     */
    public function buildMessageSchema(Request $request, $user)
    {
        $store = Store::find($request->input('receiver_id'));
        return $params = [
            'index' => 'wego_1',
            'type' => 'message',
            'body' => [
                'subject' => $request->input('subject'),
                'body' => $request->input('body'),
                'sender_id' => $user->id,
                'receiver_id' => $request->input('receiver_id'),
                'receiver_isRead' => false,
                'receiver_name' => $store->user->name,
                'sender_isRead' => true,
                'sender_name' => $user->name . ' ' . $user->getUserableType()->last_name,
                'sending_time' => Carbon::now()->toDateTimeString()
            ],
        ];
    }

    /**
     * check if the user is the sender or receiver of the message and can manipulate it
     *
     * @param $userId
     * @param $message
     * @return bool
     */
    public function checkManipulatePermission($user, $message)
    {
        if ($message['hits']['hits'][0]['_source']['sender_id'] == $user->id or
            $message['hits']['hits'][0]['_source']['receiver_id'] == $user->getUserableType()->id
        ) {
            return true;
        }
        throw new AccessDeniedHttpException();
    }

    /**
     * check if the user is the sender of the message
     *
     * @param $message
     * @param $id
     * @return bool
     */
    public function isSender($message, $id)
    {
        $senderId = $message['hits']['hits'][0]['_source']['sender_id'];
        return ($senderId == $id);
    }

    /**
     * check if the user is the receiver of the message
     *
     * @param $message
     * @param $id
     * @return bool
     */
    public function isReceiver($message, $id)
    {
        $receiverId = $message['hits']['hits'][0]['_source']['receiver_id'];
        return ($receiverId == $id);
    }

    /**
     * ake update on elasticSearch for requested update body
     *
     * @param $updatedBody
     * @param $messageId
     * @return array
     */
    public function updateMessage($updatedBody, $messageId)
    {
        $params = [
            'index' => 'wego_1',
            'type' => 'message',
            'id' => $messageId,
            'body' => [
                'doc' => $updatedBody
            ]
        ];
        $client = ClientBuilder::create()->build();
        return $client->update($params);
    }

    /**
     * creates the update body for adding the reply and changing the read/unread status
     *
     * @param $message
     * @param $user
     * @param $request
     * @return mixed
     */
    public function createUpdateReplyBody($message, $user, $request)
    {
        $newdata = [
            'body' => $request->input('body'),
            'title' => $request->input('title')
        ];
        if ($this->isSender($message, $user->id)) {
            $message['hits']['hits'][0]['_source']['receiver_isRead'] = false;
            $newdata['replier_id'] = $user->id;
            $newdata['replier_name'] = $user->name . ' ' . $user->getUserableType()->last_name;
        } else {
            $message['hits']['hits'][0]['_source']['sender_isRead'] = false;
            $newdata['replier_id'] = $user->getUserableType()->id;
            $newdata['replier_name'] = $user->name;
        }
        $message['hits']['hits'][0]['_source']['reply'][] = $newdata;
        return $message['hits']['hits'][0]['_source'];

    }

    private function setIsReadStatusForReceiverUser($response)
    {
        if (!empty($response[ElasticHelper::BODY]))
            foreach ($response[ElasticHelper::BODY] as $key => $message) {
                $response[ElasticHelper::BODY][$key]['isRead'] = $response[ElasticHelper::BODY][$key]['receiver_isRead'];
                unset($response[ElasticHelper::BODY][$key]['receiver_isRead']);
                unset($response[ElasticHelper::BODY][$key]['sender_isRead']);
            }
        return $response;
    }

    private function setIsReadStatusForSenderUser($response)
    {
        if (!empty($response[ElasticHelper::BODY]))
            foreach ($response[ElasticHelper::BODY] as $key => $message) {
                $response[ElasticHelper::BODY][$key]['isRead'] = $response[ElasticHelper::BODY][$key]['sender_isRead'];
                unset($response[ElasticHelper::BODY][$key]['receiver_isRead']);
                unset($response[ElasticHelper::BODY][$key]['sender_isRead']);
            }
        return $response;
    }

}