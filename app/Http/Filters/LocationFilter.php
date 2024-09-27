<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LocationFilter
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
        if ($this->request->has('location')) {
            $query->where('location', 'like', '%' . $this->request->location . '%');
        }

        return $next($query);
    }
}
