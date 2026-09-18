<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'before_state',
        'after_state',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'before_state' => 'array',
        'after_state' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record($action, $entityType, $entityId = null, $before = null, $after = null, $userId = null)
    {
        return self::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'before_state' => $before,
            'after_state' => $after,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
