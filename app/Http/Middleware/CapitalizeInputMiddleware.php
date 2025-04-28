<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CapitalizeInputMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $request->merge($this->capitalizeArray($request->all()));

        return $next($request);
    }

    private function capitalizeArray(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->capitalizeArray($value);
            } elseif (is_string($value)) {
                $array[$key] = $this->capitalizeWords($value);
            }
        }
        return $array;
    }

    private function capitalizeWords($string)
    {
        return mb_convert_case($string, MB_CASE_TITLE, "UTF-8"); // Capitalizes first letter of every word
    }
}
