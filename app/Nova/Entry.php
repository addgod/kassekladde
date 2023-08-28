<?php

namespace App\Nova;

use App\Nova\Filters\EmptyAccountNumber;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Entry extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Entry>
     */
    public static $model = \App\Models\Entry::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'attachment_number';

    /**
     * Indicates if the resource should be displayed in the sidebar navigation.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'text',
    ];

    /**
     * The number of results to display when searching for resources.
     *
     * @var int
     */
    public static $perPageViaRelationship = 200;

    /**
     * Build an "index" query for the given resource.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        $query->getQuery()->orders = [];

        return $query->orderBy('attachment_number');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Number::make('Attachment Number'),
            Date::make('Date'),
            Text::make('Text'),
            Number::make('Account Number')->filterable(),
            Currency::make('Amount')->currency('DKK'),
            Number::make('Reverse Account Number'),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [
            EmptyAccountNumber::make(),
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }
}
