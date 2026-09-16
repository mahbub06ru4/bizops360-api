<?php

declare(strict_types=1);

namespace App\Http\Concerns;

use Illuminate\Http\Request;

/**
 * Shared `per_page` handling for index endpoints: reads the query param and
 * clamps it to [1, 100] rather than trusting it verbatim, defaulting to 15
 * (Laravel's own paginate() default) when absent.
 */
trait HasPerPage
{
    private const int DEFAULT_PER_PAGE = 15;

    private const int MAX_PER_PAGE = 100;

    private function perPage(Request $request): int
    {
        $requested = $request->integer('per_page', self::DEFAULT_PER_PAGE);

        return min(max($requested, 1), self::MAX_PER_PAGE);
    }
}
