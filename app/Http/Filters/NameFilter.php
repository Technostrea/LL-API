<?php

namespace App\Http\Filters;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class NameFilter
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
        if ($this->request->has('name')) {
            $query->where('name', 'like', '%' . $this->request->name . '%');
        }

        return $next($query);
    }
}
