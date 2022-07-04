<?php

namespace Wego\Buy;

use App\Bazaar;
use App\Coupon;
use App\Events\OrderStatusSetToAvailable;
use App\Events\OrderStatusSetToPurchased;
use App\Exceptions\ExpiredCouponException;
use App\Exceptions\OrderDBException;
use App\Exceptions\UsedCouponException;
use App\Gift;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\OrderController;
use App\Http\Requests;
use App\Order;
use App\Payment;
use App\Product;
use App\Store;
use App\Transaction;
use App\WegoCoin;
use Carbon\Carbon;
use Elasticsearch\ClientBuilder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use App\User;
use Mockery\CountValidator\Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Wego\ElasticHelper;
use Wego\Province\Util\MemoryProvinceManager;
use Wego\ShamsiCalender\Shamsi;
use Wego\Shipping\Company\AbstractShipping;
use Wego\Shipping\Price\ShippingPrice;

class BuyStorageUtil
{
    protected $user, $order, $storeItem, $provinceManager, $orderContent;
    protected $orderProduct, $gift, $giftAmount, $wegoCoinAmount, $wegoCoinOccur, $paymentId, $couponId, $coupon,$couponType;
    protected $couponUsed = false;
    public $finalPrice;
    const MATCHED_ELEMENT_NUMBER_STRING = 'matched_element_number';
    const PAGINATION_SIZE_STRING = 'pagination_size';
    const PAGINATION_LAST_PAGE_STRING = 'last_page';
    const PAGINATION_SIZE = 10;
    const PAGINATION_CURRENT_PAGE_STRING = 'current_page';
    const BODY = 'body';

    function __construct(User $user, $orderContent)
    {
        $this->user = $user;
        $this->orderContent = $orderContent;
        $this->storeItem = [];
        $this->paymentId = array_values($orderContent)[0]['payment_id'];
        $this->setProvinceManager(new MemoryProvinceManager());
    }

    public function setCouponId($couponId)
    {
        $this->couponId = $couponId;
        return $this;
    }

    /**
     * @param $product
     * @return bool
     */
    private static function productIsDeleted($product)
    {
        if (is_null($product))
            return true;
        return false;
    }

    private static function isNotProgressableOrderExpired($orderCreatedAt)
    {
        $difference = self::differenceFromNow($orderCreatedAt);
        return abs($difference) > Order::EXPIRED_NOT_PROGRESSABLE_DURATION;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function setProvinceManager($provinceManager)
    {
        $this->provinceManager = $provinceManager;
    }

    public function getProvinceManager()
    {
        return $this->provinceManager;
    }

    public static function getBazaarOrdersNumberByStatus($bazaarId, $status)
    {
        $client = ClientBuilder::create()->build();
        $query = self::createOrdersElasticQuery(['status' => $status], ['stores.bazaar' => $bazaarId], 0, 1);
        $result = $client->search($query)['hits']['total'];
        return $result;
    }

    public static function getBazaarOrders($bazaarId, $status, $from, $size = self::PAGINATION_SIZE)
    {
        $client = ClientBuilder::create()->build();
        $query = self::createOrdersElasticQuery(['status' => $status], ['stores.bazaar' => $bazaarId], $from, $size);
        $elasticResult = $client->search($query);
        return self::paginateElasticSearchResult($elasticResult, $from, 'removeNotRelatedStores', $bazaarId);
    }

    public static function getBuyerOrders($userId, $from, $size = self::PAGINATION_SIZE)
    {
        $client = ClientBuilder::create()->build();
        $query = self::createOrdersElasticQuery(['user_id' => $userId], [], $from, $size, 'desc');
        $elasticResult = $client->search($query);
        return self::paginateElasticSearchResult($elasticResult, $from);
    }


    public static function createOrdersElasticQuery($orderQueryPairs, $nestedQueryPairs, $from = 0, $size = 10, $searchOrder = 'desc')
    {
        $must = [];

        foreach ($orderQueryPairs as $column => $value) {
            $must[] = [
                [
                    'match' => [
                        $column => $value,
                    ]
                ]
            ];
        }

        $nestedMusts = [];
        $nestedPath = null;
        foreach ($nestedQueryPairs as $column => $value) {
            $nestedPath = explode(".", $column)[0];
            $value = is_array($value) ? $value : (array)$value;
            $nestedMusts[] = [
                'terms' => [
                    $column => $value
                ]
            ];
        }
        if (count($nestedMusts) != 0) {
            $must[] = [
                'nested' => [
                    'path' => $nestedPath,
                    'query' => [
                        'bool' => [
                            'must' => $nestedMusts
                        ]
                    ]
                ]
            ];
        }

        $query = [
            'index' => 'wego_1',
            'type' => 'orders',
            'body' => [
                'size' => $size,
                'from' => $from * $size,
                'sort' => [
                    'created_at' => $searchOrder,
                ],
                'query' => [
                    'bool' => [
                        'must' => $must
                    ]
                ]
            ]
        ];
        return $query;
    }

    public static function getBazaarOrdersByStore($bazaarId, $status)
    {
        $newOrdersNumber = self::getBazaarOrdersNumberByStatus($bazaarId, $status);
        $orders = self::getBazaarOrders($bazaarId, $status, 0, $newOrdersNumber)['body'];
        //dd($orders);
        $ordersByStore = self::sortOrdersByStore($orders, $bazaarId);
        return ($ordersByStore);
    }

    private static function getOnlyProductsWithStatusFromOrders($orders, $productStatus)
    {
        foreach ($orders as &$order) {
            $products = [];
            foreach ($order['products'] as &$product) {
                if (!strcmp($product['status'], $productStatus))
                    $products [] = $product;
            }
            $order['products'] = $products;
        }
        return $orders;
    }

    public static function getBazaarProductsByStoreFromAvailableOrders($bazaarId, $productStatus)
    {
        $orders = self::getBazaarOrdersByStore($bazaarId, Order::AVAILABLE);
        return self::getOnlyProductsWithStatusFromOrders($orders, $productStatus);
    }

    public static function getBazaarProductsByStoreFromInProgressOrders($bazaarId, $productStatus)
    {
        $orders = self::getBazaarOrdersByStore($bazaarId, Order::IN_PROGRESS);
        return self::getOnlyProductsWithStatusFromOrders($orders, $productStatus);
    }

    public static function getAllBazaarOrdersByStatus($status, $from, $size = self::PAGINATION_SIZE)
    {
        $client = ClientBuilder::create()->build();
        $query = self::createOrdersElasticQuery(['status' => $status], [], $from, $size);
        $elasticResult = $client->search($query);
        return self::paginateElasticSearchResult($elasticResult, $from);
    }

    public static function getAllBazaarProgressableOrdersByStatus($status, $from, $size = self::PAGINATION_SIZE)
    {
        $client = ClientBuilder::create()->build();
        $query = self::createOrdersElasticQuery(['status' => $status, 'progressable' => true], [], $from, $size);
        $elasticResult = $client->search($query);
        return self::paginateElasticSearchResult($elasticResult, $from);
    }

    public static function getAllBazaarNotProgressableOrdersByStatus($status, $from, $size = self::PAGINATION_SIZE)
    {
        $client = ClientBuilder::create()->build();
        $query = self::createOrdersElasticQuery(['status' => $status, 'progressable' => false], [], $from, $size);
        $elasticResult = $client->search($query);
        return self::paginateElasticSearchResult($elasticResult, $from);
    }

    private static function sortOrdersByStore($orders, $bazaarIds)
    {
        $result = [];
        foreach ($orders as $order) {
            foreach ($order['stores'] as $store) {
                foreach ($bazaarIds as $bazaarId) {
                    if (!strcmp($store['bazaar'], $bazaarId)) {
                        if (isset($result[$store['id']])) {
                            $products = self::addOrderIdToProducts($store['products'], $order['id']);
                            $result[$store['id']]['products'] = array_merge($result[$store['id']]['products'], $products);
                        } else {
                            $store['products'] = self::addOrderIdToProducts($store['products'], $order['id']);
                            $result[$store['id']] = $store;
                            $result[$store['id']]['created_at'] = Carbon::now()->toDateString();

                        }
                    }
                }
            }
        }
        return $result;
    }

    private static function addOrderIdToProducts($products, $orderId)
    {
        foreach ($products as &$product) {
            $product['order_id'] = $orderId;
        }
        return $products;
    }

    private static function removeNotRelatedStores($orders, $bazaarIds)
    {
        for ($i = 0; $i < count($orders); $i++) {
            $order = &$orders[$i];
            $stores = $order['stores'];
            $relatedStores = [];
            foreach ($stores as $store) {
                foreach ($bazaarIds as $bazaarId) {
                    if (!strcmp($store['bazaar'], $bazaarId))
                        $relatedStores[] = $store;
                }
            }
            $order['stores'] = $relatedStores;
        }
        return $orders;
    }

    public static function setOrderProductStatus($orderId, $productId, $status)
    {
        $order = self::getOrderById($orderId);
        $order = self::setProductStatus($order, $productId, $status);
        $order['status'] = self::findOrderStatus($order);
        return $order;
    }


    public static function setOrderStatus($status, $orderId)
    {
        Order::where('id', $orderId)->update(['status' => $status]);
        $order = self::getOrderById($orderId);
        $order['status'] = $status;
        return $order;
    }

    public static function setOrderStatusToDeliveredAndAddWegoCoin($orderId)
    {
        $order = self::setOrderStatus(Order::DELIVERED, $orderId);
//        $user = User::findOrFail($order['user_id']);
//        $order = BuyStorageUtil::addUserWegoCoins($order, $user);
        self::updateOrder($order);
    }

    public static function setOrderStatusToInProgress($orderId)
    {
        $order = self::setOrderStatus(Order::IN_PROGRESS, $orderId);
        self::updateOrder($order);
    }

    public static function setOrderStatusToCancel($orderId)
    {
        $order = self::setOrderStatus(Order::CANCELLED, $orderId);
        self::updateOrder($order);
    }

    public static function cancelOrder($order)
    {
        self::incrementProductsCount($order);
        self::reverseUpdateWegoCoins($order);
        if ($order['status'] == Order::DELIVERED)
            self::reverseUserWegoCoinsGet($order);
        self::setOrderStatusToCancel($order['id']);
    }


    private static function findOrderStatus($order)
    {
        $allProductIsSet = true;
        $isUnavailable = false;
        $isAvailable = false;
        foreach ($order['stores'] as $store) {
            foreach ($store['products'] as $product) {
                if (!strcmp($product['status'], Product::PRE_PURCHASE))
                    $allProductIsSet = false;
                if (!strcmp($product['status'], Product::UNAVAILABLE))
                    $isUnavailable = true;
                if (!strcmp($product['status'], Product::AVAILABLE))
                    $isAvailable = true;
            }
        }

        if ($allProductIsSet == false)
            return Order::IN_PROGRESS;
        if ($isUnavailable)
            return Order::UNAVAILABLE;
        if ($isAvailable) {
            return Order::AVAILABLE;
        }
        event(new OrderStatusSetToPurchased($order));
        return Order::PURCHASED;
    }

    private static function setProductStatus($order, $productId, $status)
    {
        foreach ($order['stores'] as &$store) {
            foreach ($store['products'] as &$product) {
                if (!strcmp($product['product_id'], $productId)) {
                    $product['status'] = $status;
                }
            }
        }
        return $order;
    }

    public static function getOrderPrice($order)
    {
        return $order['price'] + $order['shipping_price'];
    }

    public function generateOrder()
    {
        $this->order = $this->user->order()->create([
            'id' => time(),
            'time' => Carbon::now(),
            'status' => $this->getOrderStatus(),
        ]);
    }

    private function getOrderStatus()
    {
        $result = self::differenceFromNow(Order::PURCHASE_START_TIME);
        if (!$this->purchaseTimeStarted($result) && $this->isProgressable($this->paymentId))
            return Order::IN_PROGRESS;
//        return Order::CREATED;
        return Order::IN_PROGRESS;
    }

    private function purchaseTimeStarted($result)
    {
        if ($result < 0)
            return true;
        return false;
    }

    public static function setAllProgressableNewOrdersToInProgress()
    {
        $allBazaarsNewOrders = BuyStorageUtil::getAllBazaarProgressableOrdersByStatus(Order::CREATED, 0)['body'];
        foreach ($allBazaarsNewOrders as $newOrder) {
            self::setOrderStatusToInProgress($newOrder['id']);
        }
    }

    public static function cancelNotProgressableOrder()
    {
        $allBazaarsNewOrders = BuyStorageUtil::getAllBazaarNotProgressableOrdersByStatus(Order::CREATED, 0)['body'];
        foreach ($allBazaarsNewOrders as $newOrder) {
            if (self::isNotProgressableOrderExpired($newOrder['created_at'])) {
                self::cancelOrder($newOrder);
            }
        }
    }

    public static function removeProductsFromOrder($order)
    {
        $order->products()->detach();
    }

    public function getOrderId()
    {
        return $this->order->id;
    }

    //DB
    public function addProductToOrder()
    {
        $products = array_map([$this, "filterContent"], $this->orderContent);
        $this->order->products()->attach($products);
        return $this;
    }

    public function reduceProductsCount()
    {
        $requestedProducts = array_map([$this, "filterContent"], $this->orderContent);
        foreach ($requestedProducts as $requestedProduct) {
            Product::reduceQuantity($requestedProduct['product_id'], $requestedProduct['quantity']);
        }
    }

    public static function updateProductElastic($productId, $attributes)
    {
        $product = self::getProductById($productId);

        foreach ($attributes as $attributeKey => $attributeValue) {
            $product[$attributeKey] = $attributeValue;
        }

        $client = ClientBuilder::create()->build();
        $query = [
            'index' => 'wego_1',
            'type' => 'products',
            'id' => $productId,
            'body' => $product,
        ];
        $client->index($query);
    }

    public static function getProductById($productId)
    {
        $client = ClientBuilder::create()->build();
        $query = [
            'index' => 'wego_1',
            'type' => 'products',
            'body' => [
                'query' => ['constant_score' => ['filter' => ['term' => ['id' => $productId]]]],
            ],
        ];
        return $client->search($query)['hits']['hits'][0]['_source'];
    }

    //TODO: PERFORMANCE ISSUE DANGEROUSSSSSSSSSSSSSSSSSS MANY DATABASE QUERY
    public static function incrementProductsCount($oldOrder)
    {
        foreach ($oldOrder['stores'] as $store) {
            foreach ($store['products'] as $elasticProduct) {
                $product = Product::where('id', $elasticProduct['product_id'])->first();
                if (!self::productIsDeleted($product)) {
                    $product->quantity += $elasticProduct['quantity'];
                    $product->save();
                    self::updateProductElastic($product->id, ["quantity" => $product->quantity]);
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * be orderContent harchi ezafe beshe inja un chizhaie ezafe shode ro filter mikone
     * ta baraie addProductToOrder amade bashe
     * @param $element
     * @return array
     */
    private function filterContent($element)
    {
        $validKeys = [
            "discount",
            "wego_coin_use",
            "wego_coin_get",
            "quantity",
            "gift_count",
            "gift",
            "price",
            "buyer_address_id",
            "product_id",
            "shipping_id",
            "payment_id",
            "delivery_time",
            "delivery_date",
        ];
        $forbiddenKeys = array_keys(array_except($element, $validKeys));
        return array_except($element, $forbiddenKeys);
    }

    ///elastic
    public static function getRequestedProductsDetail($productsId)
    {
        $client = ClientBuilder::create()->build();
        $query = [
            'index' => 'wego_1',
            'type' => 'products',
            'body' =>
                [
                    "size" => 25,
                    "query" => ["constant_score" => ["filter" => ["terms" => ["id" => $productsId]]]],
                ],
            "_source" =>
                [
                    'store_id', 'store.english_name', 'store.user.name', 'store.free_shipping_condition',
                    'store.store_phones.phone_number', 'store.store_phones.prefix_phone_number',
                    'store.manager_mobiles', 'store.manager_first_name', 'store.manager_last_name',
                    'store.address', 'store.province_id', 'store.city_id',
                    'weight', 'store.wego_expiration', 'id', 'special_conditions', 'current_price',
                    'wego_coin_need', 'colors', 'values', 'store.bazaar',
                    'persian_name', 'english_name', 'values', 'category'
                ]
        ];
        return $client->search($query);
    }

    //elastic
    public static function getOrderById($orderId)
    {
        $client = ClientBuilder::create()->build();
        $query = [
            'index' => 'wego_1',
            'type' => 'orders',
            'body' => [
                'query' => ['constant_score' => ['filter' => ['term' => ['id' => $orderId]]]],
            ],
        ];
        return $client->search($query)['hits']['hits'][0]['_source'];
    }
    public static function searchOrder($params)
    {
        $query = [];
        $size = 10;
        $from = 0;
        if(isset($params['page']))
            $from = $params['page'];
        if(isset($params['size']))
            $from = $params['from'];
        if(isset($params['order_id']))
            $query = ['bool'=>['must' => ['term' => ['id' => $params['order_id']]]]];

        $client = ClientBuilder::create()->build();
        $quer = [
            'index' => 'wego_1',
            'type' => 'orders',
            'body' => [
                'from' => $from * $size,
                'size' => $size,
                'query' => ['filtered' => ['filter'=>$query]],
                'sort'=> ['id' => ['order'=> 'desc']]
            ]
        ];
        $elasticResult = $client->search($quer);
        return self::paginateElasticSearchResult($elasticResult, $from);
    }

    public static function addBazaarStaffMessageToOrder($order, $productId, $message)
    {
        self::addBazaarStaffMessageToOrderInDatabase($order['id'], $productId, $message);
        return self::addBazaarStaffMessageToOrderInElasticSearch($order, $productId, $message);
    }

    private static function addBazaarStaffMessageToOrderInDatabase($orderId, $productId, $message)
    {
        $order = Order::where('id', $orderId)->first();
        $order->BazaarStaffMessages()->create([
            'product_id' => $productId,
            'message' => $message
        ]);
    }

    private static function addBazaarStaffMessageToOrderInElasticSearch($order, $productId, $message)
    {
        $storeId = Product::find($productId)->store->id;
        foreach ($order['stores'] as $key => $store) {
            if ($store['id'] = $storeId) {
                foreach ($store['products'] as $productKey => $product) {
                    if ($product['product_id'] = $productId) {
                        $order['stores'][$key]['products'][$productKey]['staff_message'] = $message;
                    }
                }
            }
        }
        return $order;
    }

    public static function getStoreOrders($storeId, $from, $size = self::PAGINATION_SIZE)
    {
        $client = ClientBuilder::create()->build();
        $query = self::createOrdersElasticQuery([], ['stores.id' => $storeId], $from, $size);
        $elasticResult = $client->search($query);

        return self::paginateElasticSearchResult($elasticResult, $from, 'findStoreOrders', $storeId);
    }

    public static function getStoreNewOrders($storeId, $from, $size = self::PAGINATION_SIZE)
    {
        $client = ClientBuilder::create()->build();
        $query = self::createOrdersElasticQuery(['status' => Order::CREATED], ['stores.id' => $storeId], $from, $size);
        $elasticResult = $client->search($query);
        return self::paginateElasticSearchResult($elasticResult, $from, 'findStoreOrders', $storeId);
    }

    //TODO ino ashgal neveshtam badan dorostesh konid -> alan 2ta fore toodartooe
    //TODO etelaate in too baiad kam o ziad she
    public static function findStoreOrders($orders, $storeId)
    {
        $result = [];
        foreach ($orders as $order) {
            foreach ($order['stores'] as $store) {
                if (!strcmp($store['id'], $storeId)) {
                    $result[] = [
                        'order_id' => $order['id'],
                        'receiver_first_name' => $order['address']['receiver_first_name'],
                        'receiver_last_name' => $order['address']['receiver_last_name'],
                        'total_discount' => $store['total_discount'],
                        'total_price' => $store['total_price'],
                        'total_wego_coin_use' => $store['total_wego_coin_use'],
                        'final_price' => $store['final_price'],
                        'created_at' => $order['created_at'],
                        'products' => $store['products'],
                        'status' => $order['status']
                    ];
                }
            }
        }
        return $result;
    }

    public function updateWegoCoins()
    {
        $wegoCoinsToUpdate = [];
        foreach ($this->orderContent as $orderedProduct) {
            $wegoCoinsToUpdate = array_merge($wegoCoinsToUpdate, $orderedProduct['wego_coins_to_update']);
        }
        $wegoCoinsSubtractedAmount = [];
        foreach ($wegoCoinsToUpdate as $wegoCoin) {
            $wegoId = $wegoCoin['wego_coin_id'];
            if (!isset($wegoCoinsSubtractedAmount[$wegoId]))
                $wegoCoinsSubtractedAmount[$wegoId] = $wegoCoin['remained_amount'];
            $wegoCoinsSubtractedAmount[$wegoId] = min($wegoCoin['remained_amount'], $wegoCoinsSubtractedAmount[$wegoId]);
        }
        foreach ($wegoCoinsSubtractedAmount as $wegoId => $subtractedAmount) {
            WegoCoin::where('id', '=', $wegoId)
                ->where('user_id', '=', $this->user->id)
                ->update(['amount' => $subtractedAmount]);
        }
    }

    public static function reverseUpdateWegoCoins($oldOrder)
    {
        foreach ($oldOrder['wego_coin_used'] as $elasticWegoCoin) {
            $wegoCoin = WegoCoin::where('id', $elasticWegoCoin['wego_coin_id'])->first();
            $wegoCoin->amount += $elasticWegoCoin['subtracted_amount'];

            $expirationTime = Carbon::parse($wegoCoin->expiration);
            $result = Carbon::now()->diffInSeconds($expirationTime, false);
            if ($result > 0)
                $wegoCoin->status = WegoCoin::AVAILABLE;
            $wegoCoin->save();
        }
    }

    public static function addUserWegoCoins($order, User $user)
    {
        $wegoCoins = [];
        foreach ($order['stores'] as $store) {
            $dayToExpiration = self::getStoreExpirationDay($store['id']);
            foreach ($store['products'] as $product) {
                if ($product['wego_coin_get'] != 0) {
                    $wegoCoins[] = new WegoCoin([
                        'store_id' => $product['store_id'],
                        'status' => 'a',
                        'amount' => $product['wego_coin_get'],
                        'expiration' => Carbon::createFromTime(23, 59, 59)->addDay($dayToExpiration),
                    ]);
                }
            }
        }
        $wegoCoins = $user->wegoCoin()->saveMany($wegoCoins);
        $wegoCoinsIds = [];
        foreach ($wegoCoins as $wegoCoin) {
            $wegoCoinsIds[] = $wegoCoin->id;
        }
        $order['wego_coin_get_ids'] = $wegoCoinsIds;
        return $order;
    }

    public static function reverseUserWegoCoinsGet($oldOrder)
    {
        DB::table('wego_coins')->whereIn('id', array_values($oldOrder['wego_coin_get_ids']))->delete();
    }

    private static function getStoreExpirationDay($storeId)
    {
        return Store::where('id', '=', $storeId)->first()->wego_expiration;
    }

    private function getProvinceAndCity($cityId, $provinceId)
    {
        $provinceAndCity = ($this->provinceManager->getProvinceAndCity($provinceId, $cityId)->toJson());
        return [
            'city' => $provinceAndCity['cities']['Title'],
            'province' => $provinceAndCity['name']
        ];
    }

    public static function getTransaction($refNum, $bankType)
    {
        return Transaction::where('tracking_number', '=', $refNum)->where('bank_name', '=', $bankType)->first();
    }

    public function indexOrder()
    {
        $elasticStyleOrder = $this->getOrderElasticStyle();
        $this->addToElasticSearch($elasticStyleOrder);
    }

    public static function updateOrder($order)
    {
        $client = ClientBuilder::create()->build();
        $query = [
            'index' => 'wego_1',
            'type' => 'orders',
            'id' => $order['id'],
            'body' => $order,
        ];
        $client->index($query);
    }

    private function addToElasticSearch($order)
    {
        $order['id'] = $this->order->id;
        $this->updateOrder($order);
    }

    public function indexUpdatedOrder($oldOrder)
    {
        $elasticStyleOrder = $this->getOrderElasticStyle();
        $this->addToElasticSearch($elasticStyleOrder);
    }

    private function getOrderElasticStyle()
    {
        $firstProduct = array_values($this->orderContent)[0];
        $address = $this->getAddress($firstProduct);
        $stores = $this->getStoresDetail();
        $stores = $this->addAuditToStores($stores);
        $totalPriceAndDiscountAndWegoCoin = $this->getTotalPriceAndDiscountAndWegoCoin();
        $deliveryDate = $firstProduct['delivery_date'];
        return $order = [
            'id' => $this->order->id,
            'status' => $this->order->status,
            'payment_id' => $firstProduct['payment_id'],
            'progressable' => $this->isProgressable($firstProduct['payment_id']),
            'total_price' => $totalPriceAndDiscountAndWegoCoin['raw_price'],
            'final_price' =>
                $this->getFinalPrice($totalPriceAndDiscountAndWegoCoin['total_price'], $firstProduct['shipping_price'], $firstProduct['shipping_status']),
            'total_discount' => $this->getTotalDiscount($totalPriceAndDiscountAndWegoCoin['total_discount']),
            'total_wego_coin_used' => $totalPriceAndDiscountAndWegoCoin['wego_coin_use'],
            'shipping_price' => $firstProduct['shipping_price'],
            'shipping_status' => $firstProduct['shipping_status'],
            'shipping_company' => $firstProduct['shipping_company'],
            'shipping_company_id' => 1,
            'delivery_time' => $firstProduct['delivery_time'],
            'delivery_date' => $deliveryDate,
            "shamsi_delivery_date" => $this->setShamsiDeliveryDate($firstProduct,$deliveryDate),
            "shamsi_day" => $this->setShamsiDeliveryDay($firstProduct,$deliveryDate),
            'created_at' => $this->order->created_at->toDateTimeString(),
            'user_id' => $this->user->id,
            'address' => $address,
            'stores' => $stores,
            'wego_coin_used' => $this->getWegoCoinsToUpdate($this->orderContent, 'wego_coins_to_update'),
            'wego_coin_get_ids' => [],
            'coupon' => $this->setCouponIfNotNull()
        ];
    }

    private function isProgressable($paymentId)
    {
        switch ($paymentId) {
            case Payment::CARD:
                return true;
            case Payment::CASH:
                return true;
            case Payment::ONLINE:
                return false;
            default:
                throw new Exception('undefined payment id');
        }
    }

    private function getFinalPrice($totalPrice, $shippingPrice, $shippingStatus)
    {
        $priceWithShippingPrice = $totalPrice + (!strcmp(ShippingPrice::FREE, $shippingStatus) ? 0 : $shippingPrice);
        $priceWithShippingPrice = $this->checkCoupon($priceWithShippingPrice, $totalPrice);
        $this->finalPrice = $priceWithShippingPrice;
        return $priceWithShippingPrice;
    }


    public function getAddress($product)
    {
        $cityId = $product['city_id'];
        $provinceId = $product['province_id'];
        $cityAndProvince = $this->getProvinceAndCity($cityId, $provinceId);
        return [
            'id' => $product['buyer_address_id'],
            'province' => $cityAndProvince['province'],
            'city' => $cityAndProvince['city'],
            'address' => $product['address'],
            'receiver_mobile' => $product['receiver_mobile'],
            'receiver_prefix_mobile_number' => $product['receiver_prefix_mobile_number'],
            'receiver_prefix_phone_number' => $product['receiver_prefix_phone_number'],
            'receiver_phone' => $product['receiver_phone'],
            'receiver_postal_code' => $product['receiver_postal_code'],
            'receiver_first_name' => $product['receiver_first_name'],
            'receiver_last_name' => $product['receiver_last_name'],
        ];
    }

    public function getStoresDetail()
    {
        $stores = [];
        foreach ($this->orderContent as $product) {
            $stores[$product['store_id']]['id'] = $product['store_id'];
            $stores[$product['store_id']]['english_name'] = $product['store_english_name'];
            $stores[$product['store_id']]['persian_name'] = $product['store_persian_name'];
            $stores[$product['store_id']]['address'] = $product['store_address'];
            $stores[$product['store_id']]['province_id'] = $product['store_province_id'];
            $stores[$product['store_id']]['city_id'] = $product['store_city_id'];
            $stores[$product['store_id']]['phone'] = $product['phone_number'];
            $stores[$product['store_id']]['prefix_phone_number'] = $product['prefix_phone_number'];
            $stores[$product['store_id']]['manager_first_name'] = $product['manager_first_name'];
            $stores[$product['store_id']]['manager_last_name'] = $product['manager_last_name'];
            $stores[$product['store_id']]['manager_mobile'] = $product['manager_mobile'];
            $stores[$product['store_id']]['products'][] = $this->pruneProductDetail($product);
            $stores[$product['store_id']]['bazaar'] = $product['store_bazaar'];
            $stores[$product['store_id']]['bazaar_staff'] = $this->getBazaarStaffDetail($product['store_bazaar']);
        }

        return array_values($stores);
    }

    private function getBazaarStaffDetail($bazaarId)
    {
        if ($this->order->status == Order::CREATED)
            return [
                'first_name' => null,
                'last_name' => null,
                'mobile' => null
            ];
        $staff = Bazaar::where('id', $bazaarId)->first()->staffs;

        $staffDetails = $staff->isEmpty() ? [] : [
            'first_name' => $staff[0]->user->name,
            'last_name' => $staff[0]->last_name,
            'mobile' => $staff[0]->mobile
        ];
        return $staffDetails;
    }

    public function getTotalPriceAndDiscountAndWegoCoin()
    {
        $totalPrice = 0;
        $totalDiscount = 0;
        $totalWegoCoinUse = 0;
        $rawPrice = 0;
        foreach ($this->orderContent as $product) {
            $totalWegoCoinUse += $product['wego_coin_use'];
            $totalDiscount += $product['discount'];
            $totalPrice += $product['price'];
            $rawPrice += ($product['quantity'] * $product['product_price']);
        }
        return [
            'total_price' => $totalPrice,
            'total_discount' => $totalDiscount,
            'wego_coin_use' => $totalWegoCoinUse * WegoCoin::VALUE_TOMAN,
            'raw_price' => $rawPrice
        ];
    }

    public function pruneProductDetail($product)
    {
        return [
            "product_id" => $product['product_id'],
            'status' => Product::PRE_PURCHASE,
            "persian_name" => $product['product_persian_name'],
            "english_name" => $product['product_english_name'],
            "store_id" => $product['store_id'],
            "quantity" => $product['quantity'],
            "unit_price" => $product['product_price'],
            "discount" => $product['discount'],
            "total_price" => $product['price'],
            "wego_coin_use" => $product['wego_coin_use'],
            "wego_coin_get" => $product['wego_coin_get'],
            "gift_count" => $product['gift_count'],
            "gift" => $product['gift'],
            "gift_amount" => $product['gift_amount'],
            'specifications' => $product['specifications'],
            'category_unit' => $product['category_unit'],
            'color_name' => $product['color_name'],
            'color_id' => $product['color_id'],
        ];
    }

    public static function createTransaction($order, $status, $userId, $refNum, $ip)
    {
        DB::table('transactions')->insert([
            'amount' => self::getOrderPrice($order),
            'order_id' => $order['id'],
            'ip' => $ip,
            'status' => $status,
            'bank_name' => 'بانک سامان',
            'user_id' => $userId,
            'ref_num' => $refNum,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    private function getWegoCoinsToUpdate($productsWegoCoins, $wegoCoinColumn)
    {
        $productsWegoCoins = array_column($productsWegoCoins, $wegoCoinColumn);
        $result = [];
        $productsWegoCoinsSize = count($productsWegoCoins);
        for ($i = 0; $i < $productsWegoCoinsSize; $i++) {
            if (count($productsWegoCoins[$i]) == 0 || $productsWegoCoins[$i] == null) {
                unset($productsWegoCoins[$i]);
            } else {
                $result = array_merge($result, $productsWegoCoins[$i]);
            }
        }
        return $result;
    }

    private static function differenceFromNow($time)
    {
        $expirationTime = Carbon::parse($time);
        $result = Carbon::now()->diffInSeconds($expirationTime, false);
        return $result;
    }

    private static function paginateElasticSearchResult($elasticResult, $pageNumber, $pruneFunctionName = null, $pruneFunctionParam = null)
    {
        $result = ElasticHelper::paginate($elasticResult, $pageNumber);
        if (!is_null($pruneFunctionName))
            $result[self::BODY] = call_user_func_array([BuyStorageUtil::class, $pruneFunctionName], [$result[self::BODY], $pruneFunctionParam]);
        return $result;
    }

    private function addAuditToStores($stores)
    {
        $totalDiscount = 0;
        $totalProductsPrice = 0;
        $finalPrice = 0;
        $totalWegoCoinUse = 0;
        foreach ($stores as $storeKey => $store) {
            foreach ($store['products'] as $product) {
                $totalDiscount += $product['discount'];
                $totalWegoCoinUse += ($product['wego_coin_use'] * WegoCoin::VALUE_TOMAN);
                $totalProductsPrice += ($product['unit_price'] * $product['quantity']);
                $finalPrice += $product['total_price'];
            }
            $stores[$storeKey]['total_discount'] = $totalDiscount;
            $stores[$storeKey]['total_price'] = $totalProductsPrice;
            $stores[$storeKey]['final_price'] = $finalPrice;
            $stores[$storeKey]['total_wego_coin_use'] = $totalWegoCoinUse;

            $totalDiscount = 0;
            $totalProductsPrice = 0;
            $finalPrice = 0;

        }
        return $stores;
    }

    public function processTransaction()
    {
        DB::beginTransaction();
        try {
            //TODO man meghdare productha ro tooie elasticsearch update nemikonam
            $this->reduceProductsCount();
            $this->generateOrder();
            $this->updateWegoCoins();
            $this->addProductToOrder();
            $this->indexOrder();
            DB::commit();
        } catch (Exception $exception) {
            DB::rollback();
            throw new HttpException(500);
        }
    }

    private function updateCouponStatus()
    {
        if ($this->giftEntered()){
            $this->addGiftToOrder();
        } else {
            $this->addCouponToOrder();
        }
        $this->couponUsed =true;
    }

    /**
     * @return bool
     */
    private function couponHasEntered()
    {
        return (!empty($this->couponId));
    }

    /**
     * @return mixed
     */
    private function setCouponIfNotNull()
    {
        if ($this->couponHasEntered()) {
            if ($this->couponUsed) {
                return $this->coupon;
            }
        }
        return null;
    }

    /**
     * @param $couponId
     * @param $priceWithShippingPrice
     * @return mixed
     */
    private function checkCoupon($priceWithShippingPrice, $totalPrice)
    {
        if ($this->couponHasEntered()) {
            $this->findCouponOrGift();
            $this->checkCouponStatus();
            return $this->reduceCouponAmount($priceWithShippingPrice, $totalPrice);
        }
        return $priceWithShippingPrice;
    }

    private function getTotalDiscount($total_discount)
    {
        if ($this->couponHasEntered()) {
            if ($this->coupon->status == Coupon::USED || $this->giftEntered()) {
                $total_discount += $this->coupon->amount;
            }
        }
        return $total_discount;
    }

    private function checkCouponStatus()
    {
        if ($this->coupon->status == Coupon::EXPIRED) {
            (new ApiController())->respondWithError('کوپن مورد نظر منقضی شده است');
        } elseif ($this->coupon->status == Coupon::USED) {
            (new ApiController())->respondWithError('کوپن مورد نظر قبلا استفاده شده است');
        }
    }

    /**
     * @return mixed
     */
    private function findCouponOrGift()
    {
        $coupon = Coupon::find($this->couponId);
        if (is_null($coupon)) {
            $coupon = Gift::findOrFail($this->couponId);
        }
        $this->couponType = str_replace("App\\","",get_class($coupon));
        $this->coupon =$coupon;
    }

    /**
     * @param $priceWithShippingPrice
     * @param $totalPrice
     * @return mixed
     */
    private function reduceCouponAmount($priceWithShippingPrice, $totalPrice)
    {
        $couponAmount = $this->coupon->amount;
        $minPurchase = $this->coupon->min_purchase;
        if ($priceWithShippingPrice >= $minPurchase) {
            $priceWithShippingPrice -= $couponAmount;
            $this->updateCouponStatus();
        }
        return $priceWithShippingPrice;
    }

    private function addGiftToOrder()
    {
        try {
            $this->order->gift()->associate($this->coupon);
            $this->order->save();
        } catch (QueryException $e) {
            (new ApiController())->respondWithError('کد هدیه مورد نظر قبلا توسط شما استفاده شده است');
        }
    }

    private function addCouponToOrder()
    {
        $this->coupon->order()->associate($this->order);
        $this->coupon->status = Coupon::USED;
        $this->coupon->save();
    }

    /**
     * @return bool
     */
    private function giftEntered()
    {
        return $this->couponType == "Gift";
    }

    private function setShamsiDeliveryDate($firstProduct, $deliveryDate)
    {
        if($firstProduct['shipping_company'] != AbstractShipping::WEGO_DELIVERY_COMPANY){
            return $deliveryDate;
        } else {
            return !empty($deliveryDate) ? Shamsi::convert(Carbon::parse($deliveryDate)):"";
        }
    }

    private function setShamsiDeliveryDay($firstProduct, $deliveryDate)
    {
        //!empty($deliveryDate) ? Shamsi::timeDetail(Carbon::parse($deliveryDate))['weekday']:""
        if($firstProduct['shipping_company'] != AbstractShipping::WEGO_DELIVERY_COMPANY){
            return "";
        } else {
            return !empty($deliveryDate) ? Shamsi::timeDetail(Carbon::parse($deliveryDate))['weekday']:"";
        }
    }
}