<?php

namespace App\Models;

use App\Observers\InvalidatesProjectStats;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property int|null $user_id */
#[ObservedBy(InvalidatesProjectStats::class)]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'title',
        'description',
        'priority',
        'status',
        'column_id',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return HasMany<Subtask, $this> */
    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class);
    }

    /** @return HasMany<Comment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** @return BelongsTo<Column, $this> */
    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }
}
