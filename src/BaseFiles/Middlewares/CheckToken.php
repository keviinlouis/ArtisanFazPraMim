<?php

namespace DumpNamespace;

use Config;
use Illuminate\Http\Response;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class CheckToken extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param null $guard
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, \Closure $next, $guard = null)
    {
        $this->checkForToken($request); // Ver se tem o token

        if(!$guard){
            throw new \Exception('Guard inválido', 500);
        }

        Config::set('auth.defaults.guard', $guard);

        try {
            $payload = $this->auth->parseToken()->getPayload();

            if (!$this->checkModels($payload['class'], $guard) || !auth()->guard($guard)->onceUsingId($payload['sub'])) { // Check user not found. Check token has expired.
                throw new UnauthorizedHttpException('jwt-auth', __('exceptions.user_not_founded')); //Se der problema com o token, virá um header chamado WWW-Authenticate com o valor de jwt-auth
            }
            return $next($request); // Se o usuario for autenticado e logado com token válido, continua request

        } catch (TokenExpiredException $t) { // Token expirado, usuario não logado
            $payload = $this->auth->manager()->getPayloadFactory()->buildClaimsCollection()->toPlainArray(); // Pega os dados do token para autenticação

            $refreshed = JWTAuth::refresh(JWTAuth::getToken()); // Faz refresh do token

            auth()->onceUsingId($payload['sub']); // Autentica pelo ID

            $response = $next($request); // Pega a request

            $response->header('new_token', $refreshed); // Adiciona o header com o novo token

            return $response; // Responde com o novo token no header

        } catch (JWTException $e) {
            throw new UnauthorizedHttpException('jwt-auth', __('exceptions.token_expired'), $e, Response::HTTP_UNAUTHORIZED); //Se der problema com o token, virá um header chamado WWW-Authenticate com o valor de jwt-auth
        }
    }

    public function getClassBybGuard($guard)
    {
        $provider = config('auth.guards.'.$guard.'.provider');
        $class = config('auth.providers.'.$provider.'.model');

        return $class;

    }

    public function checkModels($class, $guard)
    {
       $classAuth = $this->getClassBybGuard($guard);

       return $classAuth == $class;
    }
}
