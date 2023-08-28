<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;
use League\Csv\Reader;

class Nets extends Action
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
        $reader = Reader::createFromString($fields->file->get());
        $reader->setDelimiter(',');
        $reader->setHeaderOffset(0);
        $payouts = collect($reader->getRecords());

        $accounts = collect([
            'Settled'                 => $fields->bank_account,
            'Subscription Fees'       => $fields->fees,
            'Chargeback / Adjustment' => $fields->fees,
            'Transaction Amount'      => $fields->creditcard_account_id,
            'Service Fees'            => $fields->subscriptions_account,
        ]);

        $payouts->each(function ($payout) use ($accounts, $fields, $daybooks) {
            DB::transaction(function () use ($payout, $accounts, $fields, $daybooks) {
                $daybooks->each(function ($daybook) use ($payout, $accounts, $fields) {
                    if (empty($payout['Settled'])) {
                        return;
                    }
                    $entries = $daybook->entries()->where('amount', -$payout['Settled']);

                    if ($entries->count() !== 1) {
                        return;
                    }

                    $entry = $entries->first();

                    $daybook->entries()->where('text', $entry->text)->delete();

                    $accounts->each(fn ($account, $key) => $daybook->entries()->create([
                        'attachment_number'      => $entry->attachment_number,
                        'date'                   => $entry->date,
                        'text'                   => $entry->text,
                        'account_number'         => $account,
                        'amount'                 => $account === $fields->creditcard_account_id ? -abs($payout[$key]) : abs($payout[$key]),
                        'reverse_account_number' => null,
                    ]));

                    if (round($daybook->entries()->where('text', $entry->text)->pluck('amount')->sum()) != 0) {
                        throw new \Exception('Summed amount not correct');
                    }
                });
            });
        });
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            File::make('File')->help('Download from: https://my.nets.eu/portal/accounting/settlements/overview Reember to delete the top lines that are not needed.'),
            Number::make('Bank Account')->default(55000),
            Number::make('Creditcard account id')->default(55005),
            Number::make('Fees')->default(7220),
            Number::make('Subscriptions account')->default(7400),
        ];
    }
}
