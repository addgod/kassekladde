<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use League\Csv\Writer;
use SplTempFileObject;

class Export extends Action
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
        $daybook = $daybooks->first();

        $entries = $daybook->entries()->orderBy('attachment_number')->get();
        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $csv->setDelimiter(';');
        $csv->insertOne(['Bilag nr.', 'Dato', 'Tekst', 'Konto', 'Beløb', 'Modkonto']);

        $entries->each(fn ($entry) => $csv->insertOne([
            $entry->attachment_number,
            $entry->date->format('d-m-Y'),
            $entry->text,
            $entry->account_number,
            number_format($entry->amount, '2', ',', '.'),
            $entry->reverse_account_number,
        ]));

        Storage::disk('public')->put('export.csv', $csv->toString());

        return Action::download(Storage::disk('public')->url('export.csv'), 'kassekladde-export.csv');
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
