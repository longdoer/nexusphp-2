<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;

class Locale
{
    public static array $languageMaps = [
        'en' => 'en',
        'chs' => 'zh_CN',
        'cht' => 'zh_TW',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user) {
            $locale = $user->locale;
            do_log("user: {$user->id}, set locale: $locale");
            App::setLocale($locale);
            Carbon::setLocale($locale);
        }
        /** @var Response $response */
        $response = $next($request);
        if ($response instanceof Response || $response instanceof JsonResponse) {
            $response->header('Request-Id', nexus()->getRequestId())->header('Running-In-Octane', RUNNING_IN_OCTANE ? 1 : 0);
        }
        return $response;
    }

}
