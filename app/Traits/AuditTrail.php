<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait AuditTrail
{
    protected static function bootAuditTrail()
    {
        static::creating(function ($model) {
            $model->created_by = Auth::id() ?? 1;
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id() ?? 1;
        });

        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                $model->deleted_by = Auth::id() ?? 1;
                $model->saveQuietly();
            }
        });
    }
}