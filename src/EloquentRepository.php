<?php

namespace Berle\LaraKit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class EloquentRepository
{

    protected $model;
    protected $searchable = [];

    public function search(array $cond = [], array $opt = [])
    {
        $query = $this->buildSearchQuery($cond);

        $page = 1;

        if ($take = array_get($opt, 'take')) {
            $total = $query->count();
            $page = array_get($opt, 'page', $page);
            $query->take($take);
            $query->skip(($page - 1) * $take);
            $results = $query->get();
        } else {
            $results = $query->get();
            $take = $total = $results->count();
        }

	$take = $take ?: 1;

        return new LengthAwarePaginator($results, $total, $take, $page);
    }

    public function get(array $cond)
    {
        $query = $this->buildSearchQuery($cond)->take(1);

        return $query->get()->first();
    }

    protected function buildSearchQuery(array $cond)
    {
        $model = $this->model;

        $query = (new $model)->newQuery();

        foreach ($cond as $key => $value) {
            if ($spec = $this->getSearchable($key)) {
                if (array_key_exists('op', $spec)) {
                    $query->where($spec[ 'col' ], $spec[ 'op' ], $value);
                } else {
                    if (is_array($value)) {
                        $query->whereIn($spec[ 'col' ], $value);
                    } else {
                        $query->where($spec[ 'col' ], $value);
                    }
                }
            }
        }

        return $query;
    }

    protected function getSearchable($key)
    {
        if (array_key_exists($key, $this->searchable)) {
            $spec = $this->searchable[ $key ];

            if (is_array($spec)) {
                return $spec;
            } else {
                return [
                    'col' => $spec,
                ];
            }
        }

        return null;
    }

    public function store(Model $model)
    {
        $model->save();
    }

    public function remove(Model $model)
    {
        $model->delete();
    }

}
