<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 2/26/17
 * Time: 11:53 AM
 */
abstract class WegoBaseRepository extends BaseRepository
{
    public function insert($data)
    {
        return $this->model->insert($data);
    }

    public function take($count)
    {
        $model = $this->model->take($count)->get();
        $this->resetModel();
        return $this->parserResult($model);
    }

    public function firstByField($column, $value, $columns = ['*'])
    {
        $model = $this->model->where($column, $value)->first($columns);
        $this->resetModel();
        return $this->parserResult($model);
    }

    public function firstOrFailByField($column, $value, $columns = ['*'])
    {
        $model = $this->model->where($column, $value)->firstOrFail($columns);
        $this->resetModel();
        return $this->parserResult($model);
    }

    public function firstWhere(array $where, $columns = ['*'])
    {
        $this->applyConditions($where);
        $model = $this->model->first($columns);
        $this->resetModel();
        return $this->parserResult($model);
    }

    public function firstOrFailWhere(array $where, $columns = ['*'])
    {
        $this->applyConditions($where);
        $model = $this->model->firstOrFail($columns);
        $this->resetModel();
        return $this->parserResult($model);
    }

    public function deleteWhereIn($column, array $values)
    {
        $model = $this->model->whereIn($column, $values)->delete();
        $this->resetModel();
        return $this->parserResult($model);
    }

    public function deleteWhere(array $where, $columns = ['*'])
    {
        $this->applyConditions($where);
        $model = $this->model->delete();
        $this->resetModel();
        return $this->parserResult($model);
    }
}