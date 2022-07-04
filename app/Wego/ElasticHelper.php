<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 2/15/17
 * Time: 2:27 PM
 */

namespace Wego;


class ElasticHelper
{
    const MATCHED_ELEMENT_NUMBER_STRING = 'matched_element_number';
    const PAGINATION_SIZE_STRING = 'pagination_size';
    const PAGINATION_LAST_PAGE_STRING = 'last_page';
    const PAGINATION_SIZE = 10;
    const PAGINATION_CURRENT_PAGE_STRING = 'current_page';
    const BODY = 'body';

    public static function paginate($elasticResult, $from , $pagination_size = self::PAGINATION_SIZE)
    {
        $result = [
            self::PAGINATION_SIZE_STRING => $pagination_size,
        ];
        $result[self::MATCHED_ELEMENT_NUMBER_STRING] = $elasticResult['hits']['total'];
        $result[self::PAGINATION_CURRENT_PAGE_STRING] = self::convertFromInElasticSearchToPageValue($from);
        $result[self::PAGINATION_LAST_PAGE_STRING] = (int)ceil($result[self::MATCHED_ELEMENT_NUMBER_STRING] / $pagination_size);
        $messages = array_column($elasticResult['hits']['hits'], '_source');
        $ids = array_column($elasticResult['hits']['hits'], '_id');
        foreach ($ids as $key => $id) {
            $messages[$key]['doc_id'] = $id;
        }
        $result[self::BODY] = $messages;
        return $result;
    }

    private static function convertFromInElasticSearchToPageValue($from)
    {
        return is_null($from) || $from == 0 ? 1 : $from + 1;
    }

    public static function convertPageValueToFromInElasticSearch($pageNumber)
    {
        return is_null($pageNumber) || $pageNumber == 0 ? 0 : $pageNumber - 1;
    }

}