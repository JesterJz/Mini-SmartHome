<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Thong bao loi thong qua middleware->khai bao trong kernel
        $message = '';
        
        try {
            // Checks token validation
            JWTAuth::parseToken()->authenticate();
            return $next($request);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e){
            // 
            $message = 'token Expired';

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e){
            // 
            $message = 'token Invalid';
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            // 
            $message = 'token Provide';
        }
        return response()->json([
            'success'   => false,
            'massage'   => $message 
        ]);

    }
}
