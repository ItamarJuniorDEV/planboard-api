<?php

namespace App\Models;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property int|null $user_id */
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'budget',
        'status',
        'deadline',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'deadline' => 'date',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Task, $this> */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /** @return HasMany<Board, $this> */
    public function boards(): HasMany
    {
        return $this->hasMany(Board::class);
    }

    /** @return HasMany<Milestone, $this> */
    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    /** @return HasMany<Label, $this> */
    public function labels(): HasMany
    {
        return $this->hasMany(Label::class);
    }
}
