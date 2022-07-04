<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 21/05/16
 * Time: 14:06
 */

namespace Wego\Search;



class FindById extends ElasticSearchBuilder
{
    public function setQuery($param)
    {
        return [
            'index' => 'wego_1',
            'type' => $this->type,
            'body' =>
                [
                    "filter"=>["query"=>["ids"=>["values"=>[$this->searchParameter]]]],
                ],
            'sort'=>$this->sortItem
        ];
    }

}