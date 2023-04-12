<?php

namespace App\Nova;

use App\Nova\Actions\Export;
use App\Nova\Actions\ImportDaybook;
use App\Nova\Actions\MobilePay;
use App\Nova\Actions\Nets;
use App\Nova\Actions\Search;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class Daybook extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Daybook>
     */
    public static $model = \App\Models\Daybook::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'attachment_number';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'text', 'account_number', 'reverse_account_number',
    ];

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
        return [];
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
        return [
            ImportDaybook::make()->standalone(),
            MobilePay::make()->standalone(),
            Nets::make()->standalone(),
            Search::make()->standalone(),
            Export::make()->standalone(),
        ];
    }
}
