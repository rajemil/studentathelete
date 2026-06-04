<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Applies organization-scoped queries when an authenticated user has an organization.
 * Complements explicit controller/policy filters; does not replace them.
 *
 * Must not call Auth::user() or Auth::check() inside the global scope: both load the User
 * model, which re-applies this scope and causes infinite recursion (memory exhaustion).
 *
 * @phpstan-require-extends Model
 *
 * @method static void addGlobalScope(string $identifier, \Closure $scope)
 * @method static void creating(\Closure $callback)
 */
trait BelongsToOrganization
{
    private static bool $applyingOrganizationScope = false;

    /** @var array<string, bool> */
    private static array $organizationColumnCache = [];

    protected static function bootBelongsToOrganization(): void
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::class;

        $modelClass::addGlobalScope('organization_scope', function (Builder $builder): void {
            if (static::$applyingOrganizationScope) {
                return;
            }

            static::$applyingOrganizationScope = true;

            try {
                $orgId = static::resolveAuthenticatedOrganizationId();
                if ($orgId === null) {
                    return;
                }

                static::applyOrganizationScope($builder, $orgId);
            } finally {
                static::$applyingOrganizationScope = false;
            }
        });

        $modelClass::creating(function (Model $model): void {
            if (! static::modelHasOrganizationColumn($model)) {
                return;
            }

            $orgId = static::resolveAuthenticatedOrganizationId();
            if ($orgId === null) {
                return;
            }

            if (empty($model->organization_id)) {
                $model->organization_id = $orgId;
            }
        });
    }

    /**
     * Organization ID for the current session user without loading User through Eloquent scopes.
     */
    protected static function resolveAuthenticatedOrganizationId(): ?int
    {
        $userId = Auth::id();
        if ($userId === null) {
            return null;
        }

        $orgId = DB::table('users')->where('id', $userId)->value('organization_id');

        return $orgId !== null ? (int) $orgId : null;
    }

    protected static function applyOrganizationScope(Builder $builder, int $orgId): void
    {
        $model = $builder->getModel();
        $table = $model->getTable();

        if (static::modelHasOrganizationColumn($model)) {
            $builder->where($table.'.organization_id', $orgId);

            return;
        }

        $relation = static::organizationScopeRelation();
        if ($relation !== null) {
            $builder->whereHas($relation, fn (Builder $q) => $q->where('organization_id', $orgId));
        }
    }

    /**
     * Relation used for tenant scope when the table has no organization_id column.
     */
    protected static function organizationScopeRelation(): ?string
    {
        return null;
    }

    protected static function modelHasOrganizationColumn(Model $model): bool
    {
        $table = $model->getTable();
        if (! array_key_exists($table, static::$organizationColumnCache)) {
            static::$organizationColumnCache[$table] = Schema::hasColumn($table, 'organization_id');
        }

        return static::$organizationColumnCache[$table];
    }
}
