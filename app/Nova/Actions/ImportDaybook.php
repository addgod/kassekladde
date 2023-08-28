<?php

namespace App\Nova\Actions;

use App\Models\Daybook;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class ImportDaybook extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $daybook = Daybook::create([
            'name' => $fields->name,
        ]);

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $fields->file->get());
        rewind($stream);
        $first = true;

        while ($line = fgetcsv($stream, null, ';')) {
            if ($first) {
                $first = false;

                continue;
            }

            $daybook->entries()->create([
                'attachment_number'      => $line[0],
                'date'                   => str_replace('/', '-', $line[1]),
                'text'                   => $line[2],
                'account_number'         => $line[3] ?: null,
                'account_type'           => $line[4] ?: null,
                'amount'                 => (float) str_replace(['.', ','], ['', '.'], $line[5]),
                'amount_foregin'         => (float) str_replace(['.', ','], ['', '.'], $line[6]),
                'reverse_account_number' => $line[7] ?: null,
                'reverse_account_type'   => $line[8] ?: null,
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
            Text::make('Name'),
            File::make('File'),
        ];
    }
}
