<?php

declare(strict_types=1);
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QaReply extends Model
{
    use HasFactory, HasUlids;
    protected $fillable = [
        'user_id',
        'certification_id',
        'title',
        'body',
        'is_resolved',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(QaReply::class)
            ->orderBy('created_at');
    }

    public function scopeKeyword(
        Builder $query,
        ?string $keyword
    ): Builder {
        if ($keyword === null || trim($keyword) === '') {
            return $query;
        }

        $keyword = trim($keyword);

        return $query->where(function (Builder $query) use ($keyword) {
            $query
                ->where('title', 'like', "%{$keyword}%")
                ->orWhere('body', 'like', "%{$keyword}%");
        });
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }
}
