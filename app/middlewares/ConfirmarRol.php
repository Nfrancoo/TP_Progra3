<?php

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;

class ConfirmarRol
{
    private $rolesPermitidos;

    public function __construct(...$roles)
    {
        $this->rolesPermitidos = $roles;
    }

    public function __invoke(Request $request, RequestHandler $handler)
    {
        $header = $request->getHeaderLine('Authorization');
        if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            $token = $matches[1];
            try {
                Autentificador::verificarToken($token);
                $datos = Autentificador::ObtenerData($token);
                if (in_array($datos->perfil, $this->rolesPermitidos)) {
                    return $handler->handle($request);
                } else {
                    throw new Exception('Perfil no autorizado para esta acción');
                }
            } catch (Exception $e) {
                $response = new \Slim\Psr7\Response();
                $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
            }
        } else {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(["error" => "Token no encontrado"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    }
}