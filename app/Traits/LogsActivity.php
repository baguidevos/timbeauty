<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * Determine if activity logging is currently enabled for this model instance.
     */
    protected static bool $activityLoggingDisabled = false;

    /**
     * Boot the LogsActivity trait.
     */
    public static function bootLogsActivity(): void
    {
        static::created(function ($model): void {
            if (static::$activityLoggingDisabled) {
                return;
            }

            $entity = $model->getActivityEntityName();
            $description = method_exists($model, 'getActivityDescription')
                ? $model->getActivityDescription('create')
                : "Création de {$entity} #{$model->getKey()}";

            ActivityLog::log(
                action: 'create',
                entity: $entity,
                entityId: $model->getKey(),
                details: $description,
                userId: Auth::id(),
            );
        });

        static::updated(function ($model): void {
            if (static::$activityLoggingDisabled) {
                return;
            }

            $ignoredFields = $model->getActivityIgnoredAttributes();
            $changes = array_diff(array_keys($model->getDirty()), $ignoredFields);

            if (empty($changes)) {
                return;
            }

            $entity = $model->getActivityEntityName();
            $description = method_exists($model, 'getActivityDescription')
                ? $model->getActivityDescription('update')
                : "Modification de {$entity} #{$model->getKey()} (".implode(', ', $changes).')';

            ActivityLog::log(
                action: 'update',
                entity: $entity,
                entityId: $model->getKey(),
                details: $description,
                userId: Auth::id(),
            );
        });

        static::deleted(function ($model): void {
            if (static::$activityLoggingDisabled) {
                return;
            }

            $entity = $model->getActivityEntityName();
            $description = method_exists($model, 'getActivityDescription')
                ? $model->getActivityDescription('delete')
                : "Suppression de {$entity} #{$model->getKey()}";

            ActivityLog::log(
                action: 'delete',
                entity: $entity,
                entityId: $model->getKey(),
                details: $description,
                userId: Auth::id(),
            );
        });
    }

    /**
     * Get the entity name used in activity logs.
     */
    public function getActivityEntityName(): string
    {
        return property_exists($this, 'activityEntity')
            ? $this->activityEntity
            : class_basename($this);
    }

    /**
     * Get attributes to ignore when determining if an update should be logged.
     *
     * @return array<string>
     */
    public function getActivityIgnoredAttributes(): array
    {
        return property_exists($this, 'activityIgnoredAttributes')
            ? $this->activityIgnoredAttributes
            : ['updated_at', 'created_at', 'remember_token', 'password'];
    }

    /**
     * Execute a callback without logging activity.
     */
    public static function withoutActivityLogging(callable $callback): mixed
    {
        static::$activityLoggingDisabled = true;

        try {
            return $callback();
        } finally {
            static::$activityLoggingDisabled = false;
        }
    }
}
