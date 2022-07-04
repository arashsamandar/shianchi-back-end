<?php

namespace App\Http\Controllers;

use App\Product;
use App\Report;
use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use Tymon;
use Wego\Helpers\JsonUtil;
use Wego\UserHandle\UserPermission;

class ReportController extends ApiController
{
    use Helpers;
    protected static $types = [
        ['key' => 0, 'value' => 'کالا جزء کالاهای غیرمجاز برای خرید و فروش است.'],
        ['key' => 1, 'value' => 'کالا در گروه نامربوط ثبت شده است.'],
        ['key' => 2, 'value' => 'عکس کالا ربطی به توضیحات ندارد.'],
        ['key' => 3, 'value' => 'عکس کالا غیر اخلاقی است.'],
        ['key' => 4, 'value' => 'قیمت کالا اشتباه است.'],
        ['key' => 5, 'value' => 'سایر ...'],
    ];
    protected $requestProductRules = [
        'body' => 'required',
        'type' => 'required',
        'reported_product_id' => 'required'
    ];
    Protected $requestStoreRules = [
        'body' => 'required',
        'type' => 'required',
        'reported_store_id' => 'required'
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Report::where('is_read', '=', 'n')->get();
    }

    /**
     * @return mixed
     * @throws \App\Exceptions\NotStaffException
     */
    public function getAll()
    {
        UserPermission::checkStaffPermission();
        return Report::all()->get();
    }

    /**
     * Store a newly created resource in storage.
     *TODO:SHOULD BE FIXED -> DIRTY CODE
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function storeProductReport(Requests\StoreProductReportRequest $request){
        $user = $this->auth->user();
        $validator = Validator::make($request->toArray(), $this->requestProductRules);
        if ($validator->fails())
            return $this->setStatusCode(404)->respondWithError($validator->errors()->all());
        $productId = $request->input('reported_product_id');
        $storeId = Product::where('id', '=', $productId)->select('store_id')->first()->store_id;
        $reportItem = $this->makeReportItem($request, $storeId, $user->id);
        $reportItem['reported_product_id'] = $productId;
        if (Report::insert($reportItem))
            return $this->respond("report successfully added");
        return $this->respondNotFound();

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function storeStoreReport(Requests\StoreStoreReportRequest $request){
        $user = $this->auth->user();
        $storeId = $request->input('reported_store_id');
        $reportItem = $this->makeReportItem($request, $storeId, $user->id);
        if (Report::insert($reportItem))
            return $this->respond("report successfully added");
        return $this->respondNotFound();
    }

    /**
     * @param Request $request
     * @param $storeId
     * @param $userId
     * @return array
     */
    public function makeReportItem(Request $request, $storeId, $userId)
    {
        $reportItem = ['body' => $request->input('body'), 'type' => $request->input('type'),
            'sender_id' => $userId, 'reported_store_id' => $storeId,
            'created_at' => Carbon::now(), 'updated_at' => Carbon::now()];
        return $reportItem;
    }

    /**
<<<<<<< HEAD
=======
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $user = $this->auth->user();
        $validator = Validator::make($request->toArray(), $this->requestRules);

        if ($validator->fails())
            return $this->setStatusCode(404)->respondWithError($validator->errors()->all());
        $storeId = null;
        $productId = null;
        $reportItem = null;
        if ($this->isProductReport($request)) {
            $productId = $request->input('reported_product_id');
            $storeId = Product::where('id', '=', $productId)->select('store_id')->first()->store_id;
        } else {
            $storeId = $request->input('reported_store_id');
        }
        $userId = $user->id;
        $reportItem = ['body' => $request->input('body'), 'type' => $request->input('type'),
            'sender_id' => $userId, 'reported_store_id' => $storeId,
            'created_at' => Carbon::now(), 'updated_at' => Carbon::now()];
        if ($productId <> 0) {
            $reportItem['reported_product_id'] = $productId;
        }
        if (Report::insert($reportItem))
            return $this->respond("report successfully added");
        return $this->respondNotFound();
    }

    /**
>>>>>>> version2
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Report::where('id', '=', $id)->get();
    }

    /**
     * @return mixed
     * @throws \App\Exceptions\NotStaffException
     */
    public function getStoreReports()
    {
        return Report::where('reported_product_id', null)->get();

    }

    /**
     * @return mixed
     * @throws \App\Exceptions\NotStaffException
     */
    public function getProductReports()
    {
        return Report::where('reported_product_id', '<>', null)->get();
    }

    /**
     * @param $id
     * @return mixed
     * @throws \App\Exceptions\NotStaffException
     */
    public function getReportByStore($id)
    {
        return Report::where('reported_store_id', $id)->get();
    }

    /**
     * @param $id
     * @return mixed
     * @throws \App\Exceptions\NotStaffException
     */
    public function getReportByProduct($id)
    {
        return Report::where('reported_product_id', $id)->get();
    }

    /**
     * @param $id
     * @return mixed
     * @throws \App\Exceptions\NotStaffException
     */
    public function getReportedStore($id)
    {
        $response = Report::where('id', '=', $id)->with(['reportedStore' => function ($query) {
            $query->with(['user' => function ($query) {
                $query->select('users.userable_id', 'users.name');
            }])->select(['stores.id', 'stores.english_name']);
        }])->select(['reports.reported_store_id', 'reports.body', 'reports.type'])->get()->toArray();
        $response = JsonUtil::removeFields($response, ['*.reported_store_id', '*.reported_store.user.userable_id']);
        return ($response);
    }


    /**
     * @param $id
     * @return mixed
     * @throws \App\Exceptions\NotStaffException
     */
    public function getReportedProduct($id)
    {
        //dd("salam");
        if ($this->isProductReport($id)) {
            $response = Report::where('id', '=', $id)->with(['reportedProduct' => function ($query) {
                $query->with(['store' => function ($query) {
                    $query->with(['user' => function ($query) {
                        $query->select('users.userable_id', 'users.name');
                    }])->select(['stores.id', 'stores.english_name']);
                }])->select(['products.id', 'products.store_id', 'products.english_name', 'products.persian_name']);
            }])->select(['reports.reported_product_id', 'reports.body', 'reports.type'])->get()->toArray();
            $response = JsonUtil::removeFields($response, ['*.reported_product_id', '*.reported_product.store_id', '*.reported_product.store.user.userable_id']);
            return ($response);
        } else {
            return $this->respond("this report is not for a product");
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function isProductReport($id)
    {
        $report = Report::find($id);
        return $report->reported_product_id <> null;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $report = Report::where('id', '=', $id);
        $report->delete();
        return $this->respond('successfully deleted');
    }

    /**
     * @param $id
     * @return mixed
     * @throws \App\Exceptions\NotStaffException
     */
    public function reportRead($id)
    {
        Report::where('id', $id)->update(['is_read' => 'R']);
        return $this->respondOk("confirmed Ok", "message");
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return self::$types;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \App\Exceptions\NotStaffException
     */
    public function getReportByType(Request $request)
    {
        return Report::where('is_read', '=', 'N')->where('type', '=', $request->input('type'))->get();
    }
}
