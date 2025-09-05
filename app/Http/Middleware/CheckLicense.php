<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\License;
use Carbon\Carbon;

class CheckLicense
{
    public function handle($request, Closure $next)
    {
        $licenca = License::first();

        if (!$licenca || Carbon::now()->gt($licenca->valid_until)) {
            abort(403, 'Licença inválida ou expirada!');
        }

        return $next($request);
    }
}
