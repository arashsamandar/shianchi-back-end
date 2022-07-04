<?php

namespace App\Http\Controllers;

use App\Audit;
use App\Buyer;
use App\Order;
use App\OrderProduct;
use App\Product;
use App\Repositories\AuditRepository;
use App\StoreAudit;
use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;

use App\Http\Requests;

class AuditController extends Controller
{
    use Helpers;
    static protected $auditRepository;
    const WEGO_RIGHT_PERCENT = 0.05;
    const CALCULATING_PERIOD = 'daily';

    function __construct(AuditRepository $auditRepository)
    {
        self::$auditRepository = $auditRepository;
    }

    /**
     *
     */
    public static function calculate()
    {
        $startDate = self::getPeriodBeginningDate();
        $endDate = self::getPeriodEndingDate();
        $orders = self::getOrdersInPeriod($startDate, $endDate);
        $storeOrder = self::getOrdersGroupedByStore($orders);
        $amounts = self::calculateOrdersAudits($storeOrder);
        $storeOrderNumbers = self::getOrderNumbersGroupedByStores($storeOrder);
        self::buildAudits($amounts, $storeOrderNumbers, $startDate, $endDate);
    }

    /**
     * @param $storeOrders
     * @return array
     */
    public static function calculateOrdersAudits($storeOrders)
    {
        $amounts = [];
        foreach ($storeOrders as $storeId => $orders) {
            $crudePrice = 0;
            foreach ($orders as $order) {
                foreach ($order['products'] as $product) {
                    $crudePrice += $product['total_price'];
                }
            }
            $wegoRight = $crudePrice * self::WEGO_RIGHT_PERCENT;
            $final_amount = $crudePrice - $wegoRight;
            $amounts[$storeId] = ['final_amount' => $final_amount, 'wego_right' => $wegoRight, 'crude_price' => $crudePrice];
        }
        return $amounts;
    }

    /**
     * @param $storeOrders
     * @return array
     */
    public static function getOrderNumbersGroupedByStores($storeOrders)
    {
        $orderNumbers = [];
        foreach ($storeOrders as $storeId => $orders) {
            foreach ($orders as $order) {
                $orderNumbers[$storeId][] = $order['order_id'];
            }
        }
        return $orderNumbers;
    }

    /**
     * @param $amounts
     * @param $storeOrderNumbers
     * @param $startDate
     * @param $endDate
     */
    public static function buildAudits($amounts, $storeOrderNumbers, $startDate, $endDate)
    {
        foreach ($amounts as $storeId => $amount) {
            $audit = self::$auditRepository->create(['store_id' => $storeId, 'start_date' => $startDate, 'end_date' => $endDate, 'type' => Audit::ORDER_TYPE,
                'crude_price' => $amount['crude_price'], 'wego_rights' => $amount['wego_right'], 'final_amount' => $amount['final_amount']]);
            $audit->orders()->attach($storeOrderNumbers[$storeId]);
        }
    }

    /**
     * @return string
     */
    public static function getPeriodBeginningDate()
    {
        switch (self::CALCULATING_PERIOD) {
            case 'daily':
                return Carbon::today()->subDay(10)->toDateTimeString();
                break;
        }
    }

    /**
     * @return string
     */
    public static function getPeriodEndingDate()
    {
        switch (self::CALCULATING_PERIOD) {
            case 'daily':
                return Carbon::tomorrow()->toDateTimeString();
                break;
        }
    }

    public function payAudit(Request $request)
    {
        $id = $request->input('id');
        $trackingCode = $request->input("tracking_code");
        $audit = Audit::find($id);
        $audit->status = Audit::PAID_STATUS;
        $audit->tracking_code = $trackingCode;
        $audit->update();
    }

    /**
     * @return mixed
     */
    public function getStoreAudits()
    {
        $user = $this->auth->user();
        $store = $user->userable;
        $audits = Audit::where('store_id', '=', $store->id)->get();
        return $audits;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return array
     * TODO check order status delivered
     */
    public static function getOrdersInPeriod($startDate, $endDate)
    {
        $elasticResult = Order::searchByQuery([
            'bool' => [
                'must' => [
                    [
                        "range" => [
                            "created_at" => [
                                "gte" => $startDate,
                                "lte" => $endDate
                            ]
                        ]
                    ],
                    [
                        "term" => [
                            "status" => Order::DELIVERED
                        ]
                    ]
                ]
            ]
        ])->getHits();

        $orders = array_column($elasticResult['hits'], '_source');
        return $orders;
    }

    /**
     * @param $orders
     * @return array
     */
    public static function getOrdersGroupedByStore($orders)
    {
        $result = [];
        foreach ($orders as $order) {
            foreach ($order['stores'] as $store) {
                $result[$store['id']][] = [
                    'order_id' => $order['id'],
                    'shipping_price' => $order['shipping_price'],
                    'receiver_first_name' => $order['address']['receiver_first_name'],
                    'receiver_last_name' => $order['address']['receiver_last_name'],
                    'final_price' => $order['final_price'],
                    'total_discount' => $order['total_discount'],
                    'created_at' => $order['created_at'],
                    'products' => $store['products']
                ];
            }
        }
        return $result;
    }


    //
}
