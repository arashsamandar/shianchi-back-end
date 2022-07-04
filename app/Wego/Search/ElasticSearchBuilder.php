<?php
namespace Wego\Search;
use Elasticsearch\ClientBuilder;

abstract class ElasticSearchBuilder
{
	protected $source,$type,$client,$query,$searchParameter,$sortItem,$size;
	abstract function setQuery($param);

	function __construct()
	{
		$this->client = ClientBuilder::create()->build();
		$this->source = '*';
		$this->type = 'products';
		$this->sortItem='';
	}

	/**
	 * @param $source
	 * @return $this
	 * set which source of document need to see in search result
     */
	public function setSource($source)
	{
		$this->source = $source;
		return $this;
	}

	public function setSize($size)
	{
		$this->size = $size;
		return $this;
	}
	/**
	 * @param $type
	 * @return $this
	 * set searching in what elasticSearch type
     */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	public function setSortItem($sortItem)
	{
		$this->sortItem = $sortItem;
		return $this;
	}

	/**
	 * @param $searchParameter
	 * @return $this
	 * set what searching for id or array
     */
	public function setSearchParameter($searchParameter)
	{
		$this->searchParameter = $searchParameter;
		return $this;
	}

	/**
	 * @return array
	 * search and return what you want
     */
	public function search()
	{
		return $this->client->search($this->setQuery($this->searchParameter));
	}

}