<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

/**
 * AdminOrHigherMiddleware
 *
 * @author Victor Lantigua <vmlantigua@gmail.com>
 */
class AdminOrHigherMiddleware {

    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->auth->user()->isAdmin())
        {
            return $next($request);
        }

        if ($request->ajax())
        {
            return response('Unauthorized.', 401);
        }
        else
        {
            return redirect('dashboard');
        }
    }
}
