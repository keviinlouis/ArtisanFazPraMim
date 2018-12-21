<?php

namespace DumpNamespace;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use ReflectionException;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $exception
     * @return void
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $exception
     * @return \Illuminate\Http\JsonResponse|Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $exception)
    {
        if($request->isJson() || $request->expectsJson()) {
            return $this->makeResponseJson($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * @param Request $request
     * @param Exception $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function makeResponseJson(Request $request, Exception $exception)
    {
        $production = env('APP_ENV') != 'production';

        if($exception instanceof ValidationException) { // Exception de validator
            return $this->buildValidationResponse($exception);
        }

        $code = $this->getCodeException($exception);

        $headers = [];

        //Começo de toda exception inesperada
        $response = [
            'success' => false,
            'message' => $exception->getMessage(),
        ];

        if($exception instanceof TokenBlacklistedException) {

            $code = Response::HTTP_UNAUTHORIZED;
            $response['message'] = 'Sessão Expirada';
            $response['error'] = 'token_expired';
            $headers['WWW-Authenticate'] = 'jwt-auth';

        } else if($exception instanceof MethodNotAllowedHttpException) {

            $code = Response::HTTP_BAD_REQUEST;
            $response['message'] = 'Método não permitido';
            $response['url'] = $request->getUriForPath('/' . $request->path());
            $response['method'] = $request->getMethod();
            $headers = $exception->getHeaders();

        } else if($exception instanceof AuthenticationException) {

            $code = Response::HTTP_UNAUTHORIZED;
            $response['message'] = 'Requisição não autorizada';
            $response['error'] = 'unauthorized';

        } else if($exception instanceof UnauthorizedHttpException) {

            $headers = $exception->getHeaders();

        } else if($exception instanceof ModelNotFoundException) {

            $model = explode('\\', $exception->getModel());
            $response['message'] = "Recurso não encontrado";
            $response['data'] = end($model);
            $code = Response::HTTP_NOT_FOUND;

        } else if($exception instanceof NotFoundHttpException) {

            $response['message'] = "Recurso não encontrado";
            $response['url'] = $request->getUriForPath('/' . $request->path());
            $response['method'] = $request->getMethod();
            $code = Response::HTTP_NOT_FOUND;
            $headers = $exception->getHeaders();

        } else if($exception instanceof ReflectionException) {

            $response['message'] = "Erro Interno";
            $response['error'] = $exception->getMessage();
            $response['url'] = $request->getUriForPath('/' . $request->path());
            $response['method'] = $request->getMethod();
            $code = Response::HTTP_INTERNAL_SERVER_ERROR;

        } else if($exception instanceof InvalidArgumentException) {
            $response['message'] = "Erro Interno";
            $code = Response::HTTP_INTERNAL_SERVER_ERROR;

        } //Exception para desenvolvimento
        else if(!$production && $exception instanceof QueryException) {

            $code = Response::HTTP_INTERNAL_SERVER_ERROR;
            $response['error'] = $exception->getPrevious()->getMessage();
            $response['query'] = $exception->getSql();
            $response['bindings'] = $exception->getBindings();

        } else if($exception instanceof ReflectionException) {

            $code = Response::HTTP_INTERNAL_SERVER_ERROR;

        } else if($exception instanceof FatalThrowableError) {

            $response['message'] = $exception->getMessage();
            $code = Response::HTTP_INTERNAL_SERVER_ERROR;

        }

        if(!$production && $code == Response::HTTP_INTERNAL_SERVER_ERROR) {
            $response['url'] = $request->getUriForPath('/' . $request->path());
            $response['method'] = $request->getMethod();
            $response['line'] = $exception->getLine();
            $response['file'] = $exception->getFile();
            $response['trace'] = $exception->getTrace();
        }

        return response()->json($response, $code, $headers);
    }

    public function getCodeException(Exception $exception)
    {
        return $exception->getCode() != 0 && !is_string($exception->getCode()) ? $exception->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    private function buildValidationResponse(ValidationException $validator)
    {
        $response = [
            'success' => false,
            'message' => $validator->validator->errors()->first(),
            'errors' => $validator->validator->errors(),
        ];

        return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY;
    }
}
