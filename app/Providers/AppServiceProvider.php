<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Sanctum::ignoreMigrations();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        $registerInsensitiveSearch = function (string $methodName, string $boolean) {
            EloquentBuilder::macro($methodName, function (array $columns, string $term) use ($boolean) {
                $normalizedTerm = '%' . mb_strtolower(trim($term), 'UTF-8') . '%';

                return $this->where(function ($query) use ($columns, $normalizedTerm, $boolean) {
                    foreach ($columns as $index => $column) {
                        $wrappedColumn = $query->getQuery()->getGrammar()->wrap($column);
                        $clause = $index === 0 && $boolean === 'and' ? 'whereRaw' : 'orWhereRaw';
                        $query->{$clause}("LOWER({$wrappedColumn}) LIKE ?", [$normalizedTerm]);
                    }
                }, null, null, $boolean);
            });

            QueryBuilder::macro($methodName, function (array $columns, string $term) use ($boolean) {
                $normalizedTerm = '%' . mb_strtolower(trim($term), 'UTF-8') . '%';

                return $this->where(function ($query) use ($columns, $normalizedTerm, $boolean) {
                    foreach ($columns as $index => $column) {
                        $wrappedColumn = $query->getGrammar()->wrap($column);
                        $clause = $index === 0 && $boolean === 'and' ? 'whereRaw' : 'orWhereRaw';
                        $query->{$clause}("LOWER({$wrappedColumn}) LIKE ?", [$normalizedTerm]);
                    }
                }, null, null, $boolean);
            });
        };

        $registerInsensitiveSearch('whereAnyLikeInsensitive', 'and');
        $registerInsensitiveSearch('orWhereAnyLikeInsensitive', 'or');
        Paginator::defaultView('vendor.pagination.default');
        Paginator::defaultSimpleView('vendor.pagination.simple-default');
    }
}
