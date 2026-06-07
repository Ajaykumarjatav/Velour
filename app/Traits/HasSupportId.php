<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HasSupportId
{
    public static function bootHasSupportId(): void
    {
        static::creating(function ($model) {
            if (empty($model->support_id)) {
                $model->support_id = static::generateSupportId();
            }
        });
    }

    public static function generateSupportId(): string
    {
        $prefix = static::supportIdPrefix();
        $offset = static::supportIdOffset();

        $lastId = DB::table((new static)->getTable())
            ->whereNotNull('support_id')
            ->where('support_id', 'LIKE', $prefix . '-%')
            ->selectRaw("MAX(CAST(SUBSTRING(support_id, ?) AS UNSIGNED)) as max_num", [strlen($prefix) + 2])
            ->value('max_num');

        $next = $lastId ? $lastId + 1 : $offset;

        return $prefix . '-' . $next;
    }

    protected static function supportIdPrefix(): string
    {
        return 'ID';
    }

    protected static function supportIdOffset(): int
    {
        return 10001;
    }
}
