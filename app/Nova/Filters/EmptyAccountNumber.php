<?php

namespace App\Nova\Filters;

use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class EmptyAccountNumber extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed                                 $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(NovaRequest $request, $query, $value)
    {
        return $query
            ->when($value === 'only-empty', fn ($query) => $query->whereNull('account_number'))
            ->when($value === 'only-filled', fn ($query) => $query->whereNotNull('account_number'))
        ;
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(NovaRequest $request)
    {
        return [
            'Only Empty'  => 'only-empty',
            'Only Filled' => 'only-filled',
        ];
    }
}
