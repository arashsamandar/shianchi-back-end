<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 12/10/16
 * Time: 6:00 PM
 */

namespace Wego\Search;


use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Log;
use Wego\Helpers\PersianUtil;

class ElasticQueryMaker
{
    protected $query;
    protected $filter;
    protected $innerQuery;
    protected $valueFilter;
    protected $request;
    protected $funcMap = [
        'store_id' => 'addStoreIdFilter',
        'brand_id' => 'addBrandIdFilter',
        'gift' => 'addSpecialConditionFilter',
        'discount' => 'addSpecialConditionFilter',
        'wego_coin' => 'addSpecialConditionFilter',
        'category' => 'addCategoryFilter',
        'sortBy' => 'addSortByField',
        'sortFor' => 'addSortOrder',
        'maxPrice' => 'addMaxPriceFilter',
        'minPrice' => 'addMinPriceFilter',
        'keyword' => 'addKeywordFilter',
        'key_name' => 'addKeyNameFilter',
        'exists' => 'addExistFilter'
    ];
    const SIZE = 32;
    const MUST = 'must';
    const SHOULD = 'should';
    const REGEX = 'regexp';
    const WILDCARD = 'wildcard';
    const TERM = 'term';
    const FROM = 0;
    const TYPE = 'products';
    const INDEX = 'wego_1';
    const DELIMITER = '_';
    const SOURCE = [
        "id" , "english_name", "persian_name", "pictures.path", "pictures.type" , "category_id",
        "current_price", "special_conditions.type", "special_conditions.amount",
        "special_conditions.expiration", "store.id", "store.english_name","key_name" , "exist_status" , "category.persian_name","category.name"
    ];
    const DEFAULT_SORT_ORDER = 'desc';
    const DEFAULT_SORT_BY = 'view_count';

    function __construct($request,$size=self::SIZE)
    {
        $this->request = $request;
        $this->initializeQuery($size);
        $this->initializeFilterQuery();
        $this->initializeInnerQuery();
    }

    public function addToSource($source)
    {
        $this->query['_source'] = array_merge($this->query['_source'],$source);
        return $this;
    }
    public function fillQuery()
    {
        foreach ($this->request as $key => $value) {
            $this->callTheRelatedFunction($key);
        }
        $this->compose();
        return $this;
    }

    /**
     * @param $key
     */
    private function callTheRelatedFunction($key)
    {
        if (array_key_exists($key, $this->funcMap)) {
            $this->{$this->funcMap[$key]}($key);
        } elseif ($this->isValueFilter($key)) {
            $this->addValueFilter($key);
        }
    }

    /**
     * @param $key
     */
    private function addStoreIdFilter($key)
    {
        $storeId = $this->request[$key];
        $this->appendFilter(self::MUST, self::TERM, $key, (int)$storeId);
    }

    /**
     * @param $key
     */
    private function addSpecialConditionFilter($key)
    {
        if ($this->HasSpecialConditionFilter($key)) {
            $this->appendFilter(self::MUST, self::TERM, "special_conditions.type", $key);
        }
    }

    /**
     * @param $key
     */
    private function addCategoryFilter($key)
    {

        $regex = CategoryController::getKeywordCategoryRegex($this->request[$key]);
        $this->appendFilter(self::MUST, self::REGEX, "category.english_path", $regex);

    }
    private function addKeyNameFilter($key)
    {
        $wildcardQuery= '.*'.$this->request[$key].'.*';
        $this->appendFilter(self::MUST,self::REGEX,'key_name',$wildcardQuery);
    }

    private function addBrandIdFilter($key)
    {
        $brandId = explode(self::DELIMITER, $this->request[$key]);
        $this->appendFilter(self::MUST, 'terms', $key, $brandId);
    }

    /**
     * @param $key
     */
    private function addSortByField($key)
    {
        $sortBy = $this->getSortByNameById($this->request[$key]); //TODO: change name
        $this->query['body']['sort'][$sortBy] = $this->query['body']['sort'][self::DEFAULT_SORT_BY];
        if ($this->request[$key] == 1000){
            $this->query['body']['sort'][$sortBy]['order'] = 'asc';
        }
        if ($this->isSortByFilterChange($sortBy))
            $this->removeDefaultSortByFilter();
    }

    /**
     * @param $sortBy
     * @return bool
     */
    private function isSortByFilterChange($sortBy)
    {
        return $sortBy != self::DEFAULT_SORT_BY;
    }

    private function removeDefaultSortByFilter()
    {
        unset($this->query['body']['sort'][self::DEFAULT_SORT_BY]);
    }

    /**
     * @param $key
     */
    private function addSortOrder($key)
    {
        $sortOrder = $this->getSortOrderNameById($this->request[$key]);
        next($this->query['body']['sort']);
        $sortBy = key($this->query['body']['sort']);
        $this->query['body']['sort'][$sortBy]['order'] = $sortOrder;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getSortByNameById($id)
    {
        $sortForMapping = [1000 => "current_price", 1001 => "current_price" , 1003 => "created_at", 1005 => "view_count",
            1007 => "average_score" , 1009 => "sale"];
        if (array_key_exists($id, $sortForMapping))
            return $sortForMapping[$id];
        return self::DEFAULT_SORT_BY;
    }

    public function getSortOrderNameById($id)
    {

        $sortOrderMapping = [0 => "desc", 1 => "asc"];
        if (array_key_exists($id, $sortOrderMapping))
            return $sortOrderMapping[$id];
        return self::DEFAULT_SORT_ORDER;

    }

    /**
     * @param $key
     */
    private function addMinPriceFilter($key)
    {
        $this->filter['bool']['must'][0]['range']['current_price']['gte'] = $this->request[$key];
    }

    /**
     * @param $key
     */
    private function addMaxPriceFilter($key)
    {
        $this->filter['bool']['must'][0]['range']['current_price']['lte'] = $this->request[$key];
    }

    private function addExistFilter($key)
    {
        if ($this->HasExistsFilter($key)) {
            $this->filter['bool']['must'][0]['range']['quantity']['gte'] = 1;
        }
    }

    /**
     * @param $key
     */
    private function addKeywordFilter($key)
    {
        $this->request[$key] = PersianUtil::toStandardPersianString($this->request[$key]);
        $this->innerQuery["bool"][self::MUST][] = ["bool" => [self::SHOULD => [["match" =>
            ["persian_name" => ['query'=>$this->request[$key],'operator'=>'and']]], ["match" => ["english_name" => ['query'=>$this->request[$key],'operator'=>'and']]]]]];
    }

    /**
     * @param $key
     */
    private function addValueFilter($key)
    {
        $subFilters = explode('_', $this->request[$key]);
        $this->addShouldQueryToInnerQuery();
        foreach ($subFilters as $subFilter) {
            $this->appendValueFilterToInner(self::TERM, "values.id", $subFilter);
        }
    }

    /**
     * @param $queryKind
     * @param $productField
     * @param $value
     */
    private function appendValueFilterToInner($queryKind, $productField, $value)
    {
        end($this->innerQuery["bool"][self::MUST]);
        $lastIndex = key($this->innerQuery["bool"][self::MUST]);
        array_push($this->innerQuery["bool"][self::MUST][$lastIndex]["bool"][self::SHOULD], [$queryKind => [$productField => $value]]);
    }

    /**
     *
     */
    private function addShouldQueryToInnerQuery()
    {
        $this->innerQuery["bool"][self::MUST][] = ["bool" => ["should" => []]];
    }

    /**
     * @param $boolOperator
     * @param $queryKind
     * @param $productField
     * @param $value
     */
    private function appendFilter($boolOperator, $queryKind, $productField, $value)
    {
        array_push($this->filter["bool"][$boolOperator], [$queryKind => [$productField => $value]]);
    }

    /**
     *
     */
    private function compose()
    {
        $this->query["body"]["query"]["filtered"]["filter"] = $this->filter;
        $this->query["body"]["query"]["filtered"]["query"] = $this->innerQuery;
    }

    /**
     * @param $key
     * @return bool
     */
    private function isValueFilter($key)
    {
        return strpos($key, 'filter') !== false;
    }

    /**
     * @param $key
     * @return int
     */
    private function HasSpecialConditionFilter($key)
    {
        return $this->request[$key] == 'true';
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    private function initializeQuery($size)
    {
        $this->query = [
            'index' => self::INDEX,
            'type' => self::TYPE,
            'body' => [
                'from' => $this->request['from'] * self::SIZE,
                'size' => $size,
                'query' => [
                    'filtered' => []
                ],
                'sort' => [
                    'exist_status'=> [
                        "order"=>'desc'
                    ],
                    self::DEFAULT_SORT_BY => [
                        "order" => self::DEFAULT_SORT_ORDER

                    ]
                ]
            ],
            '_source' => self::SOURCE
        ];
    }

    private function initializeFilterQuery()
    {
        $this->filter = [
            "bool" => [
                "must" => [
                    [
                        "range" => [
                            "current_price" => [
                                "gte" => 0,
                                "lte" => 100000000000
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    private function initializeInnerQuery()
    {
        $this->innerQuery = ["bool" => ["must" => []]];
    }

    private function HasExistsFilter($key)
    {
        return $this->request[$key] == 'true';
    }


}