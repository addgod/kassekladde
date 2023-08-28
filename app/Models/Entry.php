<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attachment_number',
        'date',
        'text',
        'account_number',
        'amount',
        'reverse_account_number',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date'   => 'datetime',
        'amount' => 'float',
    ];

    /**
     * The associated daybook.
     */
    public function daybook(): BelongsTo
    {
        return $this->belongsTo(Daybook::class);
    }
}
