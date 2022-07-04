<?php

namespace App\Repositories\Elastic;

use Elasticquent\ElasticquentTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Container\Container as App;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryCriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Events\RepositoryEntityDeleted;
use Prettus\Repository\Exceptions\RepositoryException;


abstract class ElasticRepository implements RepositoryInterface,RepositoryCriteriaInterface
{
    use ElasticquentTrait;
    private $app;

    /**
     * @var
     */
    protected $criteria;
    protected $model;
    protected $skipCriteria = false;
    /**
     * @var array
     */
    protected $fieldSearchable = [];

    /**
     * @var PresenterInterface
     */
    protected $presenter;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * Validation Rules
     *
     * @var array
     */
    protected $rules = null;


    /**
     * @var bool
     */
    protected $skipPresenter = false;

    /**
     * @var \Closure
     */
    protected $scopeQuery = null;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    abstract function model();

    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = array('*'))
    {
        // dd($this->model);
        return $this->model->searchByQuery(['match_all' => []], null, $columns);
    }

    /**
     * @param int $perPage
     * @param array $columns
     * @return mixed
     */
    public function paginate($perPage = 15, $columns = array('*'))
    {
        return $this->model->searchByQuery(null, null, $columns, $perPage);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->addToIndex($data);
    }

    /**
     * @param array $data
     * @param $id
     * @param string $attribute
     * @return mixed
     */
    public function update(array $data, $id, $attribute = "id")
    {
        return $this->model->where($attribute, '=', $id)->update($data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = array('*'))
    {
        return $this->model->searchByQuery(['match' => ['_id' => $id]], null, $columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findBy($attribute, $value, $columns = array('*'))
    {
        return $this->model->searchByQuery(['match' => [$attribute => $value]], null, $columns);
    }

    public function findByRegex($attribute, $value, $columns = array('*'), $size, $sortAttr, $sortOrder)
    {
        // return Category::searchByQuery(['regexp' => [$field => $regex]], null, $source, $size, null, ['created_at' => ['order' => 'desc']])->getHits();
        $sort = empty($sortAttr) ? null : [$sortAttr => ['order' => $sortOrder]];
        return $this->model->searchByQuery(['regexp' => [$attribute => $value]], null, $columns, $size, null, $sort);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws RepositoryException
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model)
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");

        return $this->model = $model;
    }

    public function resetScope()
    {
        $this->skipCriteria(false);
        return $this;
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function skipCriteria($status = true)
    {
        $this->skipCriteria = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @param Criteria $criteria
     * @return $this
     */
    public function getByCriteria(CriteriaInterface $criteria)
    {
        $this->model = $criteria->apply($this->model, $this);
        return $this;
    }

    /**
     * @param Criteria $criteria
     * @return $this
     */
    public function pushCriteria($criteria)
    {
        $this->criteria->push($criteria);
        return $this;
    }

    /**
     * @return $this
     */
    public function  applyCriteria()
    {
        if ($this->skipCriteria === true)
            return $this;

        foreach ($this->getCriteria() as $criteria) {
            if ($criteria instanceof ElasticCriteria)
                $this->model = $criteria->apply($this->model, $this);
        }

        return $this;
    }

    /**
     *
     */
    public function boot()
    {

    }

    /**
     * @throws RepositoryException
     */
    public function resetModel()
    {
        $this->makeModel();
    }


    /**
     * Specify Presenter class name
     *
     * @return string
     */
    public function presenter()
    {
        return null;
    }

    /**
     * Specify Validator class name of Prettus\Validator\Contracts\ValidatorInterface
     *
     * @return null
     * @throws Exception
     */
    public function validator()
    {

        if (isset($this->rules) && !is_null($this->rules) && is_array($this->rules) && !empty($this->rules)) {
            if (class_exists('Prettus\Validator\LaravelValidator')) {
                $validator = app('Prettus\Validator\LaravelValidator');
                if ($validator instanceof ValidatorInterface) {
                    $validator->setRules($this->rules);

                    return $validator;
                }
            } else {
                throw new Exception(trans('repository::packages.prettus_laravel_validation_required'));
            }
        }

        return null;
    }

    /**
     * Set Presenter
     *
     * @param $presenter
     *
     * @return $this
     */
    public function setPresenter($presenter)
    {
        $this->makePresenter($presenter);

        return $this;
    }


    /**
     * @param null $presenter
     *
     * @return PresenterInterface
     * @throws RepositoryException
     */
    public function makePresenter($presenter = null)
    {
        $presenter = !is_null($presenter) ? $presenter : $this->presenter();

        if (!is_null($presenter)) {
            $this->presenter = is_string($presenter) ? $this->app->make($presenter) : $presenter;

            if (!$this->presenter instanceof PresenterInterface) {
                throw new RepositoryException("Class {$presenter} must be an instance of Prettus\\Repository\\Contracts\\PresenterInterface");
            }

            return $this->presenter;
        }

        return null;
    }

    /**
     * @param null $validator
     *
     * @return null|ValidatorInterface
     * @throws RepositoryException
     */
    public function makeValidator($validator = null)
    {
        $validator = !is_null($validator) ? $validator : $this->validator();

        if (!is_null($validator)) {
            $this->validator = is_string($validator) ? $this->app->make($validator) : $validator;

            if (!$this->validator instanceof ValidatorInterface) {
                throw new RepositoryException("Class {$validator} must be an instance of Prettus\\Validator\\Contracts\\ValidatorInterface");
            }

            return $this->validator;
        }

        return null;
    }

    /**
     * Get Searchable Fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Query Scope
     *
     * @param \Closure $scope
     *
     * @return $this
     */
    public function scopeQuery(\Closure $scope)
    {
        $this->scopeQuery = $scope;

        return $this;
    }

    /**
     * Retrieve data array for populate field select
     *
     * @param string      $column
     * @param string|null $key
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function lists($column, $key = null)
    {
        $this->applyCriteria();

        return $this->model->lists($column, $key);
    }

    /**
     * Retrieve first data of repository
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function first($columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        $results = $this->model->first($columns);

        $this->resetModel();

        return $this->parserResult($results);
    }

    /**
     * Retrieve all data of repository, simple paginated
     *
     * @param null  $limit
     * @param array $columns
     *
     * @return mixed
     */
    public function simplePaginate($limit = null, $columns = ['*'])
    {
        return $this->paginate($limit, $columns, "simplePaginate");
    }

    /**
     * Find data by field and value
     *
     * @param       $field
     * @param       $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findByField($field, $value = null, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();
        $model = $this->model->where($field, '=', $value)->get($columns);
        $this->resetModel();

        return $this->parserResult($model);
    }

    /**
     * Find data by multiple fields
     *
     * @param array $where
     * @param array $columns
     *
     * @return mixed
     */
    public function findWhere(array $where, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        $this->applyConditions($where);

        $model = $this->model->get($columns);
        $this->resetModel();

        return $this->parserResult($model);
    }

    /**
     * Find data by multiple values in one field
     *
     * @param       $field
     * @param array $values
     * @param array $columns
     *
     * @return mixed
     */
    public function findWhereIn($field, array $values, $columns = ['*'])
    {
        $this->applyCriteria();
        $model = $this->model->whereIn($field, $values)->get($columns);
        $this->resetModel();

        return $this->parserResult($model);
    }

    /**
     * Find data by excluding multiple values in one field
     *
     * @param       $field
     * @param array $values
     * @param array $columns
     *
     * @return mixed
     */
    public function findWhereNotIn($field, array $values, $columns = ['*'])
    {
        $this->applyCriteria();
        $model = $this->model->whereNotIn($field, $values)->get($columns);
        $this->resetModel();

        return $this->parserResult($model);
    }

    /**
     * Update or Create an entity in repository
     *
     * @throws ValidatorException
     *
     * @param array $attributes
     * @param array $values
     *
     * @return mixed
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        $this->applyScope();

        if (!is_null($this->validator)) {
            $this->validator->with($attributes)->passesOrFail(ValidatorInterface::RULE_UPDATE);
        }

        $temporarySkipPresenter = $this->skipPresenter;

        $this->skipPresenter(true);

        $model = $this->model->updateOrCreate($attributes, $values);

        $this->skipPresenter($temporarySkipPresenter);
        $this->resetModel();

        event(new RepositoryEntityUpdated($this, $model));

        return $this->parserResult($model);
    }


    /**
     * Delete multiple entities by given criteria.
     *
     * @param array $where
     *
     * @return int
     */
    public function deleteWhere(array $where)
    {
        $this->applyScope();

        $temporarySkipPresenter = $this->skipPresenter;
        $this->skipPresenter(true);

        $this->applyConditions($where);

        $deleted = $this->model->delete();

        event(new RepositoryEntityDeleted($this, $this->model));

        $this->skipPresenter($temporarySkipPresenter);
        $this->resetModel();

        return $deleted;
    }

    /**
     * Check if entity has relation
     *
     * @param string $relation
     *
     * @return $this
     */
    public function has($relation)
    {
        $this->model = $this->model->has($relation);

        return $this;
    }

    /**
     * Load relations
     *
     * @param array|string $relations
     *
     * @return $this
     */
    public function with($relations)
    {
        $this->model = $this->model->with($relations);

        return $this;
    }

    /**
     * Load relation with closure
     *
     * @param string $relation
     * @param closure $closure
     *
     * @return $this
     */
    function whereHas($relation, $closure)
    {
        $this->model = $this->model->whereHas($relation, $closure);

        return $this;
    }

    /**
     * Set hidden fields
     *
     * @param array $fields
     *
     * @return $this
     */
    public function hidden(array $fields)
    {
        $this->model->setHidden($fields);

        return $this;
    }

    public function orderBy($column, $direction = 'asc')
    {
        $this->model = $this->model->orderBy($column, $direction);

        return $this;
    }

    /**
     * Set visible fields
     *
     * @param array $fields
     *
     * @return $this
     */
    public function visible(array $fields)
    {
        $this->model->setVisible($fields);

        return $this;
    }


    /**
     * Pop Criteria
     *
     * @param $criteria
     *
     * @return $this
     */
    public function popCriteria($criteria)
    {
        $this->criteria = $this->criteria->reject(function ($item) use ($criteria) {
            if (is_object($item) && is_string($criteria)) {
                return get_class($item) === $criteria;
            }

            if (is_string($item) && is_object($criteria)) {
                return $item === get_class($criteria);
            }

            return get_class($item) === get_class($criteria);
        });

        return $this;
    }




    /**
     * Reset all Criterias
     *
     * @return $this
     */
    public function resetCriteria()
    {
        $this->criteria = new Collection();

        return $this;
    }


    /**
     * Apply scope in current Query
     *
     * @return $this
     */
    protected function applyScope()
    {
        if (isset($this->scopeQuery) && is_callable($this->scopeQuery)) {
            $callback = $this->scopeQuery;
            $this->model = $callback($this->model);
        }

        return $this;
    }


    /**
     * Applies the given where conditions to the model.
     *
     * @param array $where
     * @return void
     */
    protected function applyConditions(array $where)
    {
        foreach ($where as $field => $value) {
            if (is_array($value)) {
                list($field, $condition, $val) = $value;
                $this->model = $this->model->where($field, $condition, $val);
            } else {
                $this->model = $this->model->where($field, '=', $value);
            }
        }
    }

    /**
     * Skip Presenter Wrapper
     *
     * @param bool $status
     *
     * @return $this
     */
    public function skipPresenter($status = true)
    {
        $this->skipPresenter = $status;

        return $this;
    }

    /**
     * Wrapper result data
     *
     * @param mixed $result
     *
     * @return mixed
     */
    public function parserResult($result)
    {
        if ($this->presenter instanceof PresenterInterface) {

            if ($result instanceof Collection || $result instanceof LengthAwarePaginator) {
                $result->each(function ($model) {
                    if ($model instanceof Presentable) {
                        $model->setPresenter($this->presenter);
                    }

                    return $model;
                });
            } elseif ($result instanceof Presentable) {
                $result = $result->setPresenter($this->presenter);
            }

            if (!$this->skipPresenter) {
                return $this->presenter->present($result);
            }
        }

        return $result;
    }

}