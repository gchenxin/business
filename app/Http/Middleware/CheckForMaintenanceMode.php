<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode as Middleware;
use Illuminate\Routing\Exceptions\UrlEntranceException;

class CheckForMaintenanceMode extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array
     */
    protected $except = [
        //
    ];

    public function handle($request, Closure $next)
    {
        //检查域名
        $allowed_origins = config('logic.allow_origin');
        if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins) && !empty($_SERVER['HTTP_REFERER'])) {
            header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Methods: GET, POST');
            header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
            return $next($request);
        }
        //阻止外来域名访问
//        die();
        throw new UrlEntranceException;

    }
}
