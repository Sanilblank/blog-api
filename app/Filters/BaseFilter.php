<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Class BaseFilter
 *
 * @package App\Filters
 */
abstract class BaseFilter
{
    /**
     * @var Builder
     */
    protected Builder $builder;

    /**
     * @var array
     */
    protected array $request;

    /**
     * @param Builder $builder
     * @param array $request
     */
    public function __construct(Builder $builder, array $request)
    {
        $this->builder = $builder;
        $this->request = collect($request)->all();
    }

    /**
     * @return Builder
     */
    public function apply(): Builder
    {
        foreach ($this->request as $filter => $value) {
            if ($this->isFilterApplicable($filter)) {
                $this->builder = call_user_func_array([$this, $this->getMethodName($filter)], [$value]);
            }
        }

        return $this->builder;
    }

    /**
     * @param $filter
     *
     * @return bool
     */
    public function isFilterApplicable($filter): bool
    {
        return $this->methodExists($filter) && !$this->isEmpty($this->request[$filter]);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function isEmpty(mixed $value): bool
    {
        if (is_array($value)) {
            return empty(array_filter($value));
        }

        return empty($value);
    }

    /**
     * @param string $methodName
     *
     * @return bool
     */
    public function methodExists(string $methodName): bool
    {
        return method_exists($this, $this->getMethodName($methodName));
    }

    /**
     * @param String $name
     *
     * @return string
     */
    public function getMethodName(string $name): string
    {
        return Str::camel($name);
    }
}
