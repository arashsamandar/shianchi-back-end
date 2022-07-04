<?php

namespace App\Http\Controllers;

use App\Category;

use App\Events\CategoriesUpdated;
use App\Http\Requests\StoreCategoryRequest;
use App\Product;
use App\Repositories\Category\CategoryRepository;
use App\Repositories\TitleRepository;
use App\Title;
use Carbon\Carbon;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;

use App\Http\Requests;
use Elasticsearch;
use Illuminate\Support\Facades\DB;
use Wego\Helpers\ArrayUtil;
use Wego\Helpers\JsonUtil;
use Wego\Product\ProductParser;

class CategoryController extends ApiController
{
    const PAGINATION_SIZE = 50;
    protected $repository;
    protected $titleRepository;

    function __construct(CategoryRepository $repository, TitleRepository $titleRepository)
    {
        $this->repository = $repository;
        $this->titleRepository = $titleRepository;
    }

    public function test()
    {
        return $this->repository->all();
    }
    public function similarCategory(Request $request)
    {
        $result = $this->getCategoryByRegex($this->getChildCategoryRegex($request->input('category_name')));
        return array_column($result['hits'], '_source');
    }

    public function getChildCategoryWithBreadCrumb(Request $request)
    {

        $rawCategories = $this->getCategoryByRegex($this->getChildCategoryRegex($request->input('category_name')), ['name', 'persian_name', 'path', 'english_path', 'isLeaf', 'id', 'unit']);

        $categories = array_column($rawCategories['hits'], '_source');

        $breadCrumb = (new ProductParser([]))->prepareBreadcrumb(
            ArrayUtil::removeLastNodeFromString($categories[0]['english_path']),
            ArrayUtil::removeLastNodeFromString(($categories[0]['path']))
        );

        return $this->respondArray(["child" => $categories, "bread_crumb" => $breadCrumb]);

    }

    public function getRootCategories()
    {
        $result = $this->getCategoryByRegex('[^_]+');
        return array_column($result['hits'], '_source');
    }

    public function getCategorySpecificationsAndValuesByName(Request $request)
    {
        $categoryDetail = Category::searchByQuery(['term' => ['name' => strtolower($request->input('category_name'))]])->getHits();
        foreach (array_keys($categoryDetail['hits'][0]['_source']['specifications']) as $key) {
            if ($categoryDetail['hits'][0]['_source']['specifications'][$key]['searchable'] == 0) {
                unset($categoryDetail['hits'][0]['_source']['specifications'][$key]);
            }
        }
        //TODO: WRONG THING JUST FOR ORDER IN VIEW WE REVERSE ARRAY
        $categoryDetail['hits'][0]['_source']['specifications'] = array_values($categoryDetail['hits'][0]['_source']['specifications']);
        return $this->setStatusCode(200)->respond($this->prettifyResult($categoryDetail));
    }

    public function getCategorySpecificationsAndValuesById($id)
    {
        return Category::searchByQuery(['term' => ['id' => $id]])->getHits();
    }

    public function getLeafCategoryPersianName(Request $request)
    {
        $keyword = $request->input('keyword');
        return $this->getLeafCategory(
            self::getKeywordCategoryRegex($keyword),
            ['id', 'unit', 'path'],
            'path'
        );
    }


    public function getCategoryByRegex($regex, $source = ['name', 'persian_name'], $field = 'english_path', $size = self::PAGINATION_SIZE)
    {
        return Category::searchByQuery(['regexp' => [$field => $regex]], null, $source, $size, null, ['id' => ['order' => 'asc']])->getHits();
    }

    public function getLeafCategory($regex, $source = ['name', 'persian_name'], $field = 'english_path', $size = self::PAGINATION_SIZE)
    {
        return Category::searchByQuery(['filtered' =>
            [
                'filter' =>
                    [
                        'bool' =>
                            [
                                'must' =>
                                    [
                                        ['term' => ['isLeaf' => 1]],
                                        ['regexp' => [$field => $regex]
                                        ]
                                    ]
                            ]
                    ]
            ]
        ], null, $source, $size, null, ['created_at' => 'asc'])->getHits();


    }

    /**
     * @param $englishPath
     * @return string
     * assume englishPath = electronics
     * this function return ....._electronics_(name)
     */
    public function getChildCategoryRegex($englishPath)
    {
        return '(([a-z]|[A-Z]|-|[0-9])*_)*' . strtoupper($englishPath) . '_[^_]+';
    }

    public static function getKeywordCategoryRegex($keyword)
    {
        return '.*_' . strtoupper($keyword) . '|'.strtoupper($keyword).'_.*|.*_'.strtoupper($keyword).'_.*';
    }

    private function prettifyResult($categoryDetail)
    {
        if ($categoryDetail['total'] < 1)
            return [];
        $breadcrumb = (new ProductParser([]))->prepareBreadcrumb($categoryDetail['hits'][0]['_source']['english_path'], $categoryDetail['hits'][0]['_source']['path']);

        $specification = JsonUtil::removeFields($categoryDetail['hits'][0]['_source']['specifications'], [
            '*.values.*.created_at', '*.values.*.updated_at', '*.created_at', '*.updated_at', '*.pivot'
        ]);
        //TODO: category is bad name
        return ['category' => $specification, 'breadcrumb' => $breadcrumb];
    }

    public function saveCategories(StoreCategoryRequest $request)
    {
        $categoriesToModify = $this->changeRequestCategoriesStyle($request->input('modify'));
        $categoriesToSave = $this->getCategoriesToSave($categoriesToModify);
        $categoriesToUpdate = $this->getCategoriesToUpdate($categoriesToModify);
        $categoriesToDelete = $request->input('delete');
        DB::transaction(function () use ($categoriesToSave, $categoriesToUpdate, $categoriesToDelete) {
            $this->updateCategoriesToDB(($categoriesToUpdate));
            $this->insertCategoryAndSpecificationToDB(JsonUtil::removeFields($categoriesToSave, ['*.category_id']));
            $this->updateElasticSearch(JsonUtil::removeFields($categoriesToSave, ['*.category_id']),$categoriesToUpdate);
            $this->deleteCategoriesInDB($categoriesToDelete);
            $this->deleteCategoriesInElasticSearch($categoriesToDelete);
        });
        event(new CategoriesUpdated(array_column($categoriesToUpdate,'category_id'),$categoriesToDelete));
        return $this->respondOk();
    }

    private function insertCategoryAndSpecificationToDB($categoriesToSave)
    {
        $this->repository->insert($categoriesToSave);
        $this->addGeneralTitleToInsertedCategories($categoriesToSave);
    }

    private function transformRequestJson($item)
    {
        return [
            'english_path' => str_replace('*_', '', strtoupper($item['english_path'])),
            'path' => str_replace('*_', '', $item['persian_path']),
            'name' => strtolower(str_replace(' ', '-', $item['english_name'])),
            'persian_name' => $item['persian_name'],
            'unit' => $item['unit'],
            'isLeaf' => (strcmp($item['is_leaf'], "false") === 0) ? 0 : 1,
            'category_id' => $item['category_id'],
            'menu_id' => $item['id'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
    }

    public function getCategoryForMenu()
    {
        if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
            // Ignores notices and reports all other kinds... and warnings
            error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
            //     error_reporting(E_ALL ^ E_WARNING); // Maybe this is enough
        }
        return array_merge([
            [
                "persian_path" => "*",
                "english_path" => "*",
                "persian_name" => "*",
                "english_name" => "*",
                "id" => "0",
                "unit" => "",
                "is_leaf" => "",
                "category_id" => 0,
            ]
        ], self::changeFormat($this->repository->all()->toArray()));
    }

    private static function changeFormat($categories)
    {
        return array_map(function ($category) {
            return [
                'persian_path' => '*_' . $category['path'],
                'english_path' => '*_' . strtolower($category['english_path']),
                'persian_name' => $category['persian_name'],
                'english_name' => $category['name'],
                'id' => $category['menu_id'],
                'unit' => $category['unit'],
                'is_leaf' => (strcmp($category['isLeaf'], 0) === 0) ? "false" : "true",
                'category_id' => $category['id']
            ];
        }, $categories);
    }

    private function getCategoriesToSave($categories)
    {
        return array_filter($categories, function ($category) {
            return strcmp($category['category_id'], '#') === 0;
        });
    }

    private function getCategoriesToUpdate($categories)
    {

        return array_filter($categories, function ($category) {
            return strcmp($category['category_id'], '#') !== 0;
        });
    }

    private function updateCategoriesToDB($categoriesToUpdate)
    {
        foreach ($categoriesToUpdate as $newCategory) {
            $this->repository->update(JsonUtil::removeFields($newCategory, ['category_id']), $newCategory['category_id']);
        }

    }

    private function changeRequestCategoriesStyle($categoriesToModify)
    {
        $result = [];
        if (count($categoriesToModify))
            $result = array_map([$this, 'transformRequestJson'], $categoriesToModify);
        return $this->removeEmptyItemFromCategories($result);
    }

    private function removeEmptyItemFromCategories($categories)
    {
        if (!array_key_exists(0, $categories))
            return $categories;
        if (!strcmp($categories[0]["path"], "*"))
            unset($categories[0]);
        return $categories;
    }

    private function deleteCategoriesInElasticSearch($categoriesToDelete)
    {
        $client = Elasticsearch\ClientBuilder::create()->build();
        $param = [
            'type' => 'categories', 'index' => 'wego_1',
            'body' => ['query' => ['filtered' => ['filter' => ['terms' => ['id' => $categoriesToDelete]]]]]
        ];
        if (!empty($categoriesToDelete))
            $client->deleteByQuery($param);
    }

    private function deleteCategoriesInDB($categoriesToDelete)
    {
        if (! empty($categoriesToDelete)) {
            $this->repository->deleteWhereIn('id', $categoriesToDelete);
        }
    }

    private function updateElasticSearch($categoriesToSave,$categoriesToUpdate)
    {
        Category::whereIn('id',array_column($categoriesToUpdate,'category_id'))->elastic()->addToIndex();
        Category::whereIn('name',array_column($categoriesToSave,'name'))->elastic()->addToIndex();
    }

    /**
     * @param $categoriesToSave
     */
    private function addGeneralTitleToInsertedCategories($categoriesToSave)
    {
        $count = count($categoriesToSave);
        if ($count) {
            $this->addTitleToInsertedCategories($count);
        }
    }

    /**
     * @return static
     */
    private function getGeneralTitle()
    {
        $title = $this->titleRepository->firstByField('title', 'مشخصات ظاهری');
        if (empty($title)) {
            $title = $this->titleRepository->create(['title' => 'مشخصات ظاهری']);
        }
        return $title;
    }

    /**
     * @param $count
     * @return array
     */
    private function getInsertedCategoryIds($count)
    {
        $categoryIds = [];
        $addedCategories = $this->repository->orderBy('created_at', 'desc')->take($count);

        foreach ($addedCategories as $newCategory) {
            $categoryIds[] = $newCategory->id;
        }
        return $categoryIds;
    }

    /**
     * @param $count
     */
    private function addTitleToInsertedCategories($count)
    {
        $categoryIds = $this->getInsertedCategoryIds($count);
        $title = $this->getGeneralTitle();
        $this->titleRepository->attachCategories($title['id'], $categoryIds);
    }

    public function deleteDuplicateCategory()
    {
        $client = ClientBuilder::create()->build();
        $param = [
            'type' => 'categories', 'index' => 'wego_1',
            'body' => ['query' => ['filtered' => ['filter' => ['terms' => ['id' => [468,469,470,471]]]]]]
        ];
        $client->deleteByQuery($param);
        return $this->respondOk();
    }

}