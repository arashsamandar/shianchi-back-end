<?php

namespace App\Http\Controllers;

use App\Brand;
use App\Category;
use App\Repositories\BrandRepository;
use App\Repositories\Category\CategoryRepository;
use App\SpecialCondition;
use Illuminate\Http\Request;

use App\Http\Requests;
use Wego\UserHandle\UserPermission;

class BrandController extends ApiController
{
    protected $brandRepository,$categoryRepository;

    function __construct(BrandRepository $brandRepository, CategoryRepository $categoryRepository)
    {
        $this->brandRepository = $brandRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function saveBrand(Request $request)
    {
        $this->brandRepository->create($request->all());
        return $this->respondOk();
    }

    public function setCategoryBrandRelation(Request $request)
    {
        $brandId = $request->input('brand_id');
        $categoryIds = $request->input('category_id');
        $this->brandRepository->attachCategories($brandId,$categoryIds);;
        SpecificationController::updateElastic($categoryIds);
        return $this->respondOk();
    }

    public function updateBrand(Request $request)
    {
        $id = $request->input('id');
        $this->brandRepository->update($request->all(), $id);
        return $this->respondOk();
    }

    public function deleteBrand($id)
    {
        $this->brandRepository->delete($id);
        return $this->respondOk('brand deleted successfully');
    }

    public function getBrandsByCategoryId($categoryId)
    {
        $brands = $this->categoryRepository->getBrands($categoryId);
        return $brands->toArray();
    }

    public function getAllBrands(Request $request)
    {
        $categoryName = $request->input('category_name');
        $brands = $this->haseNotCategory($categoryName) ? $this->brandRepository->all() : Category::where('name',$categoryName)->first()->brands;
        return $brands;
    }
    private function haseNotCategory ($categoryName){
        return empty($categoryName) || is_null($categoryName);
    }

    public function searchBrand(Request $request)
    {
        $name = $request->input('name');
        $brands= $this->brandRepository->getSimilarBrands($name);
        return $brands->toArray();
    }
}
