<?php

namespace App\Http\Controllers;

use App\Banner;
use App\Http\Requests\StoreBannerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Wego\PictureHandler;

class BannerController extends ApiController
{
    public function saveTempPicture(Request $request)
    {
        $path =  (new PictureHandler($request,0))
            ->setUsePath("banner")
            ->save();
        return $this->respondOk($path,"path");
    }

    public function index()
    {
        $banners  = Banner::orderBy('priority','asc')->get();
        $result = $this->changeStyle($banners);
        return $result;
    }

    public function store(StoreBannerRequest $request)
    {
        $sliderBanners = $request->sliders;
        $middleBanners = $request->middle;
        $topRightBanner = $request->top_right;
        $this->saveSliders($sliderBanners);
        $this->saveMiddleBanner($middleBanners);
        $this->saveTopRightBanner($topRightBanner);
    }

    private function saveSliders($sliderBanners)
    {
        $allPriorities = [1,2,3,4,5];
        $priorities = array_column($sliderBanners,'priority');
        $diff = array_diff($allPriorities,$priorities);
        foreach ($diff as $pr){
            $mbanner = Banner::where('type',Banner::MOBILE_SLIDER_BANNER)->where('priority',$pr)->first();
            $dbanner = Banner::where('type',Banner::SLIDER_BANNER)->where('priority',$pr)->first();
            if(!is_null($mbanner)) {
                if (file_exists(public_path() . $mbanner->path)) {
                    File::delete(public_path() . $mbanner->path);
                }
                $mbanner->delete();
            }
            if(!is_null($dbanner)) {
                if (file_exists(public_path() . $dbanner->path)) {
                    File::delete(public_path() . $dbanner->path);
                }
                $dbanner->delete();
            }
        }
        foreach($sliderBanners as $sliderBanner){
            $desktopType = Banner::SLIDER_BANNER;
            $mobileType = Banner::MOBILE_SLIDER_BANNER;
            $mobileData = $sliderBanner['mobile'];
            $desktopData = $sliderBanner['desktop'];
            $mobileData['type'] = $mobileType;
            $desktopData['type'] = $desktopType;
            $mobileData['priority'] = $sliderBanner['priority'];
            $desktopData['priority'] = $sliderBanner['priority'];
            $this->deleteOldBanner($desktopData,$mobileData);
            $this->AddNewBanners($desktopData,$mobileData);
        }
    }

    private function saveMiddleBanner($middleBanners)
    {
        foreach($middleBanners as $middleBanner){
            $type = Banner::MIDDLE_BANNER;
            $data = $middleBanner;
            $data['type'] = $type;
            $data['priority'] = $middleBanner['priority'];
            $this->deleteOldMiddleBanner($data);
            $this->AddNewMiddleBanners($data);
        }
    }

    private function saveTopRightBanner($topRightBanner)
    {
        $desktopTopRight = Banner::where('type',Banner::TOP_RIGHT_BANNER)->first();
        $mobileTopRight = Banner::where('type',Banner::MOBILE_TOP_RIGHT_BANNER)->first();
        if(!is_null($desktopTopRight) && ($desktopTopRight->path == $topRightBanner['desktop']['path'])){
            $desktopTopRight->link = $topRightBanner['desktop']['link'];
            $desktopTopRight->alt = $topRightBanner['desktop']['alt'];
            $desktopTopRight->save();
        } else {
            $topRightBanner['desktop']['priority'] = 1;
            $topRightBanner['desktop']['type'] = Banner::TOP_RIGHT_BANNER ;
            Banner::create($topRightBanner['desktop']);
        }
        if(!is_null($mobileTopRight) && ($mobileTopRight->path == $topRightBanner['mobile']['path'])){
            $mobileTopRight->link = $topRightBanner['mobile']['link'];
            $mobileTopRight->alt = $topRightBanner['mobile']['alt'];
            $mobileTopRight->save();
        } else {
            $topRightBanner['mobile']['priority'] = 1;
            $topRightBanner['mobile']['type'] = Banner::MOBILE_TOP_RIGHT_BANNER ;
            Banner::create($topRightBanner['mobile']);
        }
    }

    private function deleteOldBanner($desktopData, $mobileData)
    {
        $mobileBanner = Banner::where('type',Banner::MOBILE_SLIDER_BANNER)
            ->where('priority',$mobileData['priority'])->where('path','<>',$mobileData['path'])->first();
        if(!is_null($mobileBanner)){
            if (file_exists(public_path() . $mobileBanner->path)) {
                File::delete(public_path() . $mobileBanner->path);
            }
            $mobileBanner->delete();
        }
        $desktopBanner = Banner::where('type',Banner::SLIDER_BANNER)
            ->where('priority',$desktopData['priority'])->where('path','<>',$desktopData['path'])->first();
        if(!is_null($mobileBanner)){
            if (file_exists(public_path() . $desktopBanner->path)) {
                File::delete(public_path() . $desktopBanner->path);
            }
            $desktopBanner->delete();
        }
    }

    private function AddNewBanners($desktopData, $mobileData)
    {
        $banner = Banner::where('path',$desktopData['path'])->first();
        if (is_null($banner)){
            Banner::create($desktopData);
        } else {
            $banner->priority = $desktopData['priority'];
            $banner->link = $desktopData['link'];
            $banner->alt = $desktopData['alt'];
            $banner->save();
        }
        $banner = Banner::where('path',$mobileData['path'])->first();
        if (is_null($banner)){
            Banner::create($mobileData);
        } else {
            $banner->priority = $mobileData['priority'];
            $banner->link = $mobileData['link'];
            $banner->alt = $mobileData['alt'];
            $banner->save();
        }
    }

    private function changeStyle($banners)
    {
        $result = [];
        $result['sliders']= [];
        $result['top_right']= null;
        $result['middle'] = [];
        foreach($banners as $banner){
            if ($banner->type == Banner::SLIDER_BANNER){
                $result['sliders'][$banner->priority-1]['desktop'] = $banner;
                $result['sliders'][$banner->priority-1]['priority'] = $banner->priority;
            }  elseif($banner->type == Banner::MOBILE_SLIDER_BANNER){
                $result['sliders'][$banner->priority-1]['mobile'] = $banner;
                $result['sliders'][$banner->priority-1]['priority'] = $banner->priority;
            } elseif($banner->type == Banner::TOP_RIGHT_BANNER) {
                $result['top_right']['desktop'] = $banner;
            } elseif ($banner->type == Banner::MOBILE_TOP_RIGHT_BANNER){
                $result['top_right']['mobile'] = $banner;
            } else {
                $result['middle'][] = $banner;
            }
        }
        $result['sliders'] = array_values($result['sliders']);
        return $result;
    }

    private function deleteOldMiddleBanner($data)
    {
        $middleBanner = Banner::where('type',Banner::MIDDLE_BANNER)
            ->where('priority',$data['priority'])->where('path','<>',$data['path'])->first();
        if(!is_null($middleBanner)){
            if (file_exists(public_path() . $middleBanner->path)) {
                File::delete(public_path() . $middleBanner->path);
            }
            $middleBanner->delete();
        }
    }

    private function AddNewMiddleBanners($data)
    {
        $banner = Banner::where('path',$data['path'])->first();
        if (is_null($banner)){
            Banner::create($data);
        } else {
            $banner->priority = $data['priority'];
            $banner->link = $data['link'];
            $banner->alt = $data['alt'];
            $banner->save();
        }
    }


}
