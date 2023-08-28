<?php

namespace App\Nova\Actions;

use App\Models\Preset;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Search extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $daybooks)
    {
        $preset = Preset::find($fields->preset);

        $query = $preset->search ?? $fields->query;
        $accountNumber = $preset->account_number ?? $fields->account_number;

        $daybooks->each(
            fn ($daybook) => $daybook->entries()
                ->where('text', 'LIKE', '%' . $query . '%')
                ->update(['account_number' => $accountNumber])
        );

        if ($fields->remember && $fields->query && $fields->account_number) {
            Preset::create([
                'search'         => $fields->query,
                'account_number' => $fields->account_number,
            ]);
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Select::make('Preset')->options(Preset::all()->pluck('search', 'id')->toArray()),
            Text::make('Search query', 'query'),
            Number::make('Account Number'),
            Boolean::make('Remember'),
        ];
    }
}
