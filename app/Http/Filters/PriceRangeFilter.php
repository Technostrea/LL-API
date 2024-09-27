<?php

namespace App\Http\Filters;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PriceRangeFilter
{
    public function __construct(
        protected Request $request
    )
    {
    }

    /**
     * Apply a filter to the query.
     * @param Builder $query
     * @param \Closure $next
     * @return Builder
     */
    public function handle(Builder $query, \Closure $next): Builder
    {
        if ($this->request->has('min_price')) {
            $query->where('price', '>=', $this->request->min_price);
        }

        if ($this->request->has('max_price')) {
            $query->where('price', '<=', $this->request->max_price);
        }

        return $next($query);
    }
}
