<?php

namespace Berle\LaraKit;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

abstract class Presenter
{

    protected $presentable;

    public function __construct(
        $presentable
    ) {
        $this->presentable = $presentable;
    }

    public function __get($key)
    {
        $method = 'get' . studly_case($key);

        if (method_exists($this, $method)) {
            return call_user_func([ $this, $method ], $this->presentable);
        } else {
            return $this->presentable->$key;
        }
    }

    public static function wrap($wrappable)
    {
        if (is_array($wrappable)) {
            return static::wrapArray($wrappable);
        } elseif ($wrappable instanceof Collection) {
            return static::wrapCollection($wrappable);
        } elseif ($wrappable instanceof LengthAwarePaginator) {
            return static::wrapPager($wrappable);
        } else {
            return static::wrapInstance($wrappable);
        }
    }

    public static function wrapInstance($instance)
    {
        return new static($instance);
    }

    public static function wrapArray(array $items)
    {
        return array_map(function ($w) { return static::wrap($w); }, $items);
    }

    public static function wrapCollection(Collection $collection)
    {
        return $collection->map(function ($w) { return static::wrap($w); });
    }

    public static function wrapPager(LengthAwarePaginator $pager)
    {
        $collection = static::wrapCollection($pager->getCollection());

        return new LengthAwarePaginator($collection, $pager->total(), $pager->perPage(), $pager->currentPage());
    }

}
