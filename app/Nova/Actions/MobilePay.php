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

class MobilePay extends Action
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
        $reader->setDelimiter(';');
        $reader->setHeaderOffset(0);
        $payouts = collect($reader->getRecords());

        $accounts = [
            'Transfer'   => $fields->bank_account,
            'Retainable' => $fields->fees,
            'Payment'    => $fields->mobilepay_account_id,
            'ServiceFee' => $fields->subscriptions_account,
            'Refund'     => $fields->mobilepay_account_id,
        ];

        $payouts->groupBy('TransferID')->each(function ($lines, $transferId) use ($accounts, $fields, $daybooks) {
            DB::transaction(function () use ($lines, $transferId, $accounts, $fields, $daybooks) {
                $daybooks->each(function ($daybook) use ($lines, $transferId, $accounts, $fields) {
                    $entry = $daybook->entries()->where('text', $transferId)->first();
                    $daybook->entries()->where('text', $transferId)->delete();
                    collect($lines)->map(function ($line) use ($accounts) {
                        return [
                            'amount'         => (float) str_replace(',', '.', $line['Amount']),
                            'account_number' => $accounts[$line['Event']],
                        ];
                    })
                        ->groupBy('account_number')
                        ->map(fn ($accounts) => $accounts->sum('amount'))
                        ->each(function ($amount, $account) use ($daybook, $entry, $fields) {
                            $daybook->entries()->create([
                                'attachment_number'      => $entry->attachment_number,
                                'date'                   => $entry->date,
                                'text'                   => $entry->text,
                                'account_number'         => $account,
                                'amount'                 => $account == $fields->mobilepay_account_id ? -abs($amount) : abs($amount),
                                'reverse_account_number' => null,
                            ]);
                        })
                    ;

                    if ($daybook->entries()->where('text', $transferId)->sum('amount') !== 0) {
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
            File::make('File')->help('Download from: https://portal.mobilepay.dk/reports/transfers'),
            Number::make('Bank Account')->default(55000),
            Number::make('Mobilepay account id')->default(55006),
            Number::make('Fees')->default(7220),
            Number::make('Subscriptions account')->default(7400),
        ];
    }
}
