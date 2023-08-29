<?php

namespace App\Nova;

use App\Nova\Actions\Export;
use App\Nova\Actions\ImportDaybook;
use App\Nova\Actions\MobilePay;
use App\Nova\Actions\Nets;
use App\Nova\Actions\RunAllPresets;
use App\Nova\Actions\Search;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
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
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Text::make('Name'),
            Date::make('Created At')->hideWhenCreating()->hideWhenUpdating(),
            HasMany::make('Entries'),
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
            Export::make(),
            RunAllPresets::make(),
            MobilePay::make(),
            Nets::make(),
            Search::make(),
        ];
    }
}
