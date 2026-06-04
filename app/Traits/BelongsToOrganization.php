<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * Applies organization-scoped queries when an authenticated user has an organization.
 * Complements explicit controller/policy filters; does not replace them.
 */
trait BelongsToOrganization
{
    protected static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope('organization_scope', function (Builder $builder): void {
            if (! Auth::check()) {
                return;
            }

            $orgId = Auth::user()->organization_id;
            if ($orgId === null) {
                return;
            }

            static::applyOrganizationScope($builder, (int) $orgId);
        });

        static::creating(function (Model $model): void {
            if (! Auth::check() || Auth::user()->organization_id === null) {
                return;
            }

            if (! static::modelHasOrganizationColumn($model)) {
                return;
            }

            if (empty($model->organization_id)) {
                $model->organization_id = Auth::user()->organization_id;
            }
        });
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
        static $cache = [];

        $table = $model->getTable();
        if (! array_key_exists($table, $cache)) {
            $cache[$table] = Schema::hasColumn($table, 'organization_id');
        }

        return $cache[$table];
    }
}
