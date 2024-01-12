<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function results(): HasMany
    {
        return $this->hasMany(Result::class)->orderBy('milliseconds');
    }

    /**
     * Get the player's best time in milliseconds.
     */
    protected function bestTime(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->results?->first()?->milliseconds,
        );
    }
}
