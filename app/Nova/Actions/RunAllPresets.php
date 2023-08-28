<?php

namespace App\Nova\Actions;

use App\Models\Preset;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class RunAllPresets extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * Perform the action on the given models.
     *
     * @param \Illuminate\Support\Collection $models
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $daybooks)
    {
        $presets = Preset::all();

        $presets->each(function ($preset) use ($daybooks) {
            $daybooks->each(
                fn ($daybook) => $daybook->entries()
                    ->where('text', 'LIKE', '%' . $preset->search . '%')
                    ->update(['account_number' => $preset->account_number])
            );
        });
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}
