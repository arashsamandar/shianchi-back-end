<?php

namespace App\Http\Controllers;

use App\Category;
use App\Repositories\Category\CategoryRepository;
use App\Repositories\SpecificationRepository;
use App\Repositories\TitleRepository;
use App\Repositories\ValueRepository;
use App\Specification;
use App\SpecificationTitle;
use App\Value;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Wego\Helpers\ArrayUtil;
use Wego\Helpers\JsonUtil;
use Wego\Helpers\PersianUtil;

class SpecificationController extends ApiController
{
    use Helpers;
    protected $specificationRepository, $valueRepository, $titleRepository, $categoryRepository;

    function __construct(SpecificationRepository $specificationRepository, ValueRepository $valueRepository,
                         TitleRepository $titleRepository, CategoryRepository $categoryRepository)
    {
        $this->specificationRepository = $specificationRepository;
        $this->valueRepository = $valueRepository;
        $this->titleRepository = $titleRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function saveValues(Requests\StoreValueRequest $request)
    {
        $requestValue = $request->input('data');
        $valueToSave = ArrayUtil::addTimeStampToArrays($requestValue);
        $this->valueRepository->insert($valueToSave);
        $this->updateValuesInElastic($valueToSave);
        return $this->respondOk();
    }

    //todo this function need refactor!!
    public function saveSpecification(Requests\StoreSpecificationRequest $request)
    {
        $specificationsToSave = $request->input('data');
        $count = count($specificationsToSave);
        $specificationsToSave = ArrayUtil::addTimeStampToArrays($specificationsToSave);
        foreach ($specificationsToSave as $newSpec) {
            $savedSpec = $this->specificationRepository->create($newSpec);
            $this->updateElastic($savedSpec->category_id);
        }
        $specs = $this->specificationRepository->orderBy('created_at', 'desc')->take($count);
        foreach ($specs as $spec) {
            $ids[] = $spec->id;
        }
        return $this->respondOk($ids, "specification_ids");
    }

    public function changeSpecifications(Request $request)
    {
//        $offset = $request->offset;
//        $startId= (300 * $offset) ;
//        $endId = (300 * ($offset+1));
        $categoryId = $request->category_id;
        $specifications = Specification::where('category_id',$categoryId)->get();
        foreach ($specifications as $spec) {
            $spec->name = PersianUtil::toStandardPersianString($spec->name);
            $spec->save();
        }
        $this->updateElastic($categoryId);
        return $this->respondOk();
    }


    public function deleteAllSpecificationsByCategories(Request $request)
    {
        $categoryIds = $request->input('category_id');
        $this->specificationRepository->deleteWhereIn('category_id', $categoryIds);
        return $this->respondOk("specifications of selected categories deleted");
    }

    public function deleteAllSpecificationTitlesByCategories(Request $request)
    {
        $categoryIds = $request->input('category_id');
        $this->titleRepository->deleteWhereIn('category_id', $categoryIds);
        return $this->respondOk();
    }

    public function updateSpecification(Requests\UpdateSpecificationRequest $request)
    {
        $specificationsToUpdate = $request->input('data');
        $categoryId = $specificationsToUpdate[0]['category_id'];
        $specifications = $this->specificationRepository->findWhere(['category_id' => $categoryId]);
        $this->updateSpecificationsInDB($specifications, $specificationsToUpdate);
        $this->updateElastic($categoryId);
        return $this->respondOk();
    }

    public function updateValues(Request $request)
    {
        $valuesToUpdate = $request->input('data');
        $specificationId = $valuesToUpdate[0]['specification_id'];
        $categoryId = Specification::find($specificationId)->category_id;
        $values = $this->valueRepository->findWhere(['specification_id' => $specificationId]);
        $this->updateValuesInDB($values, $valuesToUpdate, $specificationId);
        $this->updateElastic($categoryId);
        return $this->respondOk();
    }

    public function updateTitles(Request $request)
    {
        $specificationTitlesToUpdate = $request->input('data');
        $categoryId = $specificationTitlesToUpdate[0]['category_id'];
        $specificationTitles = $this->categoryRepository->getTitles($categoryId);
        $this->updateTitlesInDB($specificationTitles, $specificationTitlesToUpdate, $categoryId);
        return $this->respondOk();
    }

    public function deleteSpecification($id)
    {
        //todo integrate this function code with repository
        $specification = Specification::find($id);
        $categoryId = $specification->category_id;
        $this->specificationRepository->delete($id);
        $this->updateElastic($categoryId);
        return $this->respondOk();
    }

    private function insertValuesAndSpecificationToDB($valuesToSave)
    {
        DB::transaction(function () use ($valuesToSave) {
            Value::insert($valuesToSave);
            $this->updateValuesInElastic($valuesToSave);
        });
    }

    public static function updateElastic($categoryId)
    {
        Category::where('id', '=', $categoryId)
            ->elastic()->addToIndex();
    }

    public function saveTitle(Requests\StoreTitleRequest $request)
    {
        $titlesToSave = $request->all();
        foreach ($titlesToSave['title'] as $name) {
            $titleBody = ['title' => $name];
            $title = $this->titleRepository->create($titleBody);
            foreach ($titlesToSave['category_id'] as $categoryId) {
                $title->categories()->attach($categoryId);
            }
        }
        return $this->respondOk('data inserted correctly');
    }

    public function deleteTitle($id)
    {
        $this->titleRepository->delete($id);
        return $this->respondOk();
    }

    public function getSpecificationsByCategory($categoryId)
    {
        $specifications = $this->specificationRepository->findWhere(['category_id' => $categoryId]);
        return $specifications->toArray();
    }

    public function getSpecificationTitlesByCategory(Request $request)
    {
        $titleIds = [];
        $categoryIds = $request->input('category_id');
        $ids = $this->categoryRepository->getAllCategoriesTitles($categoryIds, 'title_id');
        foreach ($ids as $id) {
            $titleIds[] = $id->title_id;
        }
        $titles = $this->titleRepository->findWhereIn('id',$titleIds);
        return $titles->toArray();
    }

    private function prepareCategorySpecificationArray($input)
    {
        $result = JsonUtil::removeFields($input, ['name']);
        $result = ArrayUtil::addTimeStampToSingleArray($result);
        return $result;
    }

    private function findSpecificationById($oldSpecification, $specificationsToUpdate)
    {
        foreach ($specificationsToUpdate as $newSpecification) {
            if ($newSpecification['id'] == $oldSpecification->id) {
                Specification::where('id', '=', $newSpecification['id']->update($newSpecification));
            }
        }
    }

    private function updateSpecificationsInDB($specifications, $specificationsToUpdate)
    {
        foreach ($specificationsToUpdate as $specification) {
            $this->updateExistingSpec($specifications, $specification);
        }
    }

    public function updateValuesInDB($existingValues, $newValues, $specificationId)
    {
        $newVals = [];
        $ids = [];
        foreach ($newValues as $value) {
            $ids[] = $value['id'];
            $this->updateExistingValues($existingValues, $value);
            if ($value['id'] == '#') {
                $newVals[] = $value;
            }
        }
        $this->valueRepository->deleteNotMentionedSpecificationValues($specificationId, $ids);
        $this->valueRepository->insert($newVals);
    }

    private function updateTitlesInDB($specifications, $specificationTitlesToUpdate, $categoryId)
    {
        $newSpecTitle = [];
        $ids = [];
        foreach ($specificationTitlesToUpdate as $title) {
            $ids[] = $title['id'];
            $this->updateExistingTitle($specifications, $title);
            if ($title['id'] == '#') {
                $newSpecTitle[] = $title;
            }
        }
        $this->categoryRepository->detachNotMentionedTitles($categoryId, $ids);
        //DB::table('category_title')->where('category_id', $categoryId)->whereNotIn('title_id', $ids)->delete();
    }

    /**
     * @param $specifications
     * @param $specification
     */
    private function updateExistingSpec($specifications, $specification)
    {
        foreach ($specifications as $spec) {
            if ($specification['id'] == $spec->id) {
                $this->specificationRepository->update($specification, $specification['id']);
            }
        }
    }

    private function updateExistingTitle($specificationsTitle, $title)
    {
        foreach ($specificationsTitle as $specTitle) {
            if ($title['id'] == $specTitle->id) {
                unset($title['category_id']);
                $this->titleRepository->update($title, $title['id']);
            }
        }
    }

    private function updateExistingValues($existingValues, $value)
    {
        foreach ($existingValues as $val) {
            if ($value['id'] == $val->id) {
                $this->valueRepository->update($value, $value['id']);
            }
        }
    }

    public function getValuesBySpecification($id)
    {
        $values = $this->specificationRepository->getValues($id);
        return $values->toArray();
    }

    private function updateValuesInElastic($valuesToSave)
    {
        foreach ($valuesToSave as $value) {
            $specificationId = $value['specification_id'];
            $specification = $this->specificationRepository->find($specificationId);
            $category = $specification->category;
            self::updateElastic($category->id);
        }
    }
}
