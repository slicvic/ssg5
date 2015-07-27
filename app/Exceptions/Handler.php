<?php namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Exceptions\ValidationException;
use Flash;

class Handler extends ExceptionHandler {

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        'Symfony\Component\HttpKernel\Exception\HttpException',
        'App\Exceptions\ValidationException'
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($request->ajax())
        {
            return $this->renderAjaxError($request, $e);
        }
        else
        {
            return $this->renderError($request, $e);
        }
    }

    /**
     * Renders a request exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    private function renderError($request, Exception $e)
    {
        if ($e instanceof ValidationException)
        {
            Flash::error($e->errors());

            return redirect()->back()->withInput();
        }
        elseif ($e instanceof ModelNotFoundException)
        {
            Flash::error(trans('messages.error_model_not_found'));

            return redirect()->back();
        }
        else
        {
            return parent::render($request, $e);
        }
    }

    /**
     * Renders an AJAX request exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    private function renderAjaxError($request, Exception $e)
    {
        if ($e instanceof ValidationException)
        {
            return response()->json(['error' => Flash::view($e->errors())], 400);
        }
        elseif ($e instanceof ModelNotFoundException)
        {
            return response()->json(['error' => Flash::view(trans('messages.error_model_not_found'))], 404);
        }
        else
        {
            $message = env('APP_DEBUG') ? $e->getMessage() : trans('messages.error_500');

            return response()->json(['error' => Flash::view($message)], 500);
        }
    }
}
