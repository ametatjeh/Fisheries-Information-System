<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Resolves per_page from request with allowed whitelist.
     *
     * @param  array<int, int>  $allowed
     */
    protected function getPerPage(Request $request, int $default = 25, array $allowed = [10, 25, 50, 75, 100, 250, 500]): int
    {
        $perPage = $request->integer('per_page', $default);

        return in_array($perPage, $allowed, true) ? $perPage : $default;
    }
}
