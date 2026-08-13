<?php

namespace App\Domain\Audit;

use App\Infrastructure\Persistence\AuditLog;
use Illuminate\Support\Str;

class AuditService
{
    /**
     * Record an audit event.
     *
     * @param string      $action      e.g. "reservation.created"
     * @param string|null $targetType  e.g. "Reservation"
     * @param string|null $targetId    ULID or ID of the target
     * @param array|null  $before      State before the action
     * @param array|null  $after       State after the action
     * @param array       $metadata    Extra contextual data (safe, no PII/secrets)
     */
    public static function log(
        string $action,
        ?string $targetType = null,
        ?string $targetId = null,
        ?array $before = null,
        ?array $after = null,
        array $metadata = []
    ): AuditLog {
        $request = request();
        $user    = auth()->user();

        return AuditLog::create([
            'ulid'            => (string) Str::ulid(),
            'correlation_id'  => $request->header('X-Correlation-ID') ?? $request->attributes->get('correlation_id'),
            'organization_id' => $user?->organization_id ?? $metadata['organization_id'] ?? null,
            'property_id'     => $metadata['property_id'] ?? null,
            'actor_user_id'   => $user?->id,
            'actor_type'      => $user ? 'user' : ($metadata['actor_type'] ?? 'system'),
            'action'          => $action,
            'target_type'     => $targetType,
            'target_id'       => $targetId,
            'before'          => $before,
            'after'           => $after,
            'metadata'        => $metadata,
            'source_ip'       => $request->ip(),
            'user_agent'      => substr($request->userAgent() ?? '', 0, 255),
            'created_at'      => now(),
        ]);
    }
}
