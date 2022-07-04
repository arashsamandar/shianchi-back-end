<?php

namespace App\Http\Controllers;

use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;

use App\Http\Requests;
use Wego\Helpers\PersianUtil;
use Wego\Search\TermQuery;

class StoreViewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @param string $category
     * @param string $search
     * @return \Illuminate\Http\Response
     */
    public function show($id,$search='',$category= '')
    {

        $client = ClientBuilder::create()->build();
        $specification = [];
        if(isset($search) && strcasecmp($search,'search') == 0)
            $specification = $client->search($this->getCategoryQuery($category));


        $finder = new TermQuery();
        $store = $finder->setType('stores')->setField('url')->setSearchParameter($id)->search()['hits']['hits'][0]['_source'];
//
//        if(empty($store))
//            return view('errors.404')->;
        $pic = $this->setStorePicture($store['pictures']);

        $collectionItem = (array_merge($store,$pic));

        $storePhones = array_only($collectionItem,'store_phones');
        $workTime = $collectionItem['work_times'];
        unset($collectionItem['work_times']);
        unset($collectionItem['store_phones']);

        ($this->toPersianWorkTime($workTime));
        $phoneNumber = $this->toPersianPhone($storePhones['store_phones']);
        return view('Pages.store')->with(['spec'=>json_encode($specification),'phoneNumber'=>$phoneNumber,'workTimes'=>$workTime,'store'=>$collectionItem,'catName'=>$this->unique_multidim_array($store['products'])]);
    }

    public function aboutUs($id)
    {
        $finder = new TermQuery();
        $store = $finder->setType('stores')->setField('url')->setSearchParameter($id)->search()['hits']['hits'][0]['_source'];
//
        $storeInside = $store['pictures'][array_search('inside',array_column($store['pictures'],'type'))];
        //dd($store);
        return view('Pages.store_about-us')->with(['store'=>$store,'storeInside'=>$storeInside['path']]);
    }
    public function contactUs($id)
    {
        $finder = new TermQuery();
        $store = $finder->setType('stores')->setField('url')->setSearchParameter($id)->search()['hits']['hits'][0]['_source'];
//
        $storeLogo = $store['pictures'][array_search('logo',array_column($store['pictures'],'type'))];

        //dd($store);
        return view('Pages.store_contact-us')->with(['store'=>$store,'counter'=>0,'logo'=>$storeLogo['path']]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    private function setStorePicture($picture)
    {
        $logoId = array_search("logo",array_column($picture,'type'));
        $coverId = array_search("cover",array_column($picture,'type'));
        $insideId = array_search("inside",array_column($picture,'type'));

        $logo = ($logoId !== false)?$picture[$logoId]["path"] : '/images/user.png';
        $cover = ($coverId !== false)?$picture[$coverId]["path"] : '/images/user.png';
        $inside = ($insideId !== false)?$picture[$insideId]["path"] : '/images/user.png';

        return [

            "logo" =>   $logo ,
            "cover"=>   $cover ,
            "inside"=>  $inside ,
        ];

    }

    private function unique_multidim_array($item) {
        return collect(array_column($item,'category'))->unique('id')->toArray();
    }

    private function getCategoryQuery($name)
    {
        return
            [
                'index' => 'wego_1',
                'type' => 'categories',
                'body' =>
                    [
                        'query' => ["term"=>["name"=>strtolower($name)]],
                    ]
            ];
    }

    private function mapToStore()
    {

    }

    private function prepareWorkTime(&$workTime)
    {
        //dd($workTime);
        $count = 0;
        $array = [];
        $tempOpen  = '';
        $tempClose = '';
        $dayOfWeek = '';
//        $array[0]['opening'] = $tempOpen;
//        $array[0]['from_days'] = $dayOfWeek;
//        $array[0]['closing'] = $tempClose;
        //unset($workTime[0]);
        $collect = collect($workTime)->groupBy('opening_time')->toArray();
        //dd($collect);
        foreach ($collect as $item) {
            $workTimes = collect($item)->groupBy('closing_time')->toArray();
            //dd($workTimes);
            foreach ($workTimes as $key=>$days) {
                var_dump($key);
                if(count($workTimes[$key]) > 1){
                    foreach ( $days as $day) {
                        if($tempOpen == $day['opening_time'] && $tempClose == $day['closing_time']){
                            $array[$count]['to_days'] = $day['days'];
                            $array[$count]['opening'] = $day['opening_time'];
                            $array[$count]['closing'] = $day['closing_time'];
                            $array[$count]['from_day'] = $dayOfWeek;
                        }
                        else{
                            $tempOpen = $day['opening_time'];
                            $tempClose = $day['closing_time'];
                            $dayOfWeek = $day['days'];
                        }
                    }
                    ++$count;
                }
                else
                {
                    ++$count;
                    $array[$count]['opening'] = $days[0]['opening_time'];
                    $array[$count]['from_days'] = $days[0]['days'];
                    $array[$count]['closing'] = $days[0]['closing_time'];
                    $array[$count]['to_days'] = '';

                }
            }
        }
        dd($array);
    }

    private function toPersianWorkTime(&$workTime)
    {
        foreach ($workTime as $key =>$time) {
            $workTime[$key]['opening_time'] = PersianUtil::to_persian_num($time['opening_time']);
            $workTime[$key]['closing_time'] = PersianUtil::to_persian_num($time['closing_time']);
        }
    }

    private function toPersianPhone($storePhones)
    {
        return PersianUtil::to_persian_num($storePhones[0]['prefix_phone_number']).'-'.PersianUtil::to_persian_num($storePhones[0]['phone_number']);
    }
}
