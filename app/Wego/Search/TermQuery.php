<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 31/05/16
 * Time: 19:09
 */

namespace Wego\Search;


class TermQuery extends ElasticSearchBuilder
{
    protected $field;

    public function setField($field)
    {
        $this->field = $field;
        return $this;
    }

    function setQuery($param)
    {
        //dd($this->source);
        return [
            'index' => 'wego_1',
            'type' => $this->type,
            'size' => $this->size,
            'body' =>
                [
                    "query"=>["term"=>[$this->field=>$this->searchParameter]],
                    "_source"=>$this->source
                ],
            'sort'=>$this->sortItem
        ];
    }
}
