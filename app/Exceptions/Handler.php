<?php

namespace App\Exceptions;

use Exception;
use App\Package\Permissions\PermissionException;
use App\Exceptions\SubscriptionException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        PermissionException::class,
        SubscriptionException::class,
        NotFoundHttpException::class,
        MethodNotAllowedHttpException::class
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
     public function report(Exception $exception)
     {
       if ($this->shouldReport($exception) && app()->bound('sentry')) {
         app('sentry')->captureException($exception);
      }

      parent::report($exception);
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
        if ($e instanceof SubscriptionException && $request->user) {
            return back()->withErrors(['You have reached your subscription limit.'])->withInput();
        }

        if($e instanceof NotFoundHttpException)
        {
            return redirect(route('login'));
        }

        if($e instanceof MethodNotAllowedHttpException)
        {
            return redirect(route('login'));
        }

        return parent::render($request, $e);
    }
}
