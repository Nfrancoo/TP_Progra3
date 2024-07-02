<?php

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ConfirmarRolEntrega
{
    private $rolesPermitidos;

    public function __construct(...$roles)
    {
        $this->rolesPermitidos = $roles;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $header = $request->getHeaderLine('Authorization');
        if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            $token = $matches[1];
            try {
                Autentificador::verificarToken($token);
                $datos = Autentificador::ObtenerData($token);
                $rolUsuario = $datos->rol;
                $idPedido = $request->getQueryParams()['id'] ?? null;

                if (!$idPedido) {
                    throw new Exception('ID de pedido no proporcionado');
                }

                $detallePedido = Pedido::obtenerDetallePedidoPorId($idPedido);

                if (!$detallePedido) {
                    throw new Exception('Pedido no encontrado');
                }

                $rolesPermitidos = $this->obtenerRolesPermitidosParaTipoPedido($detallePedido->sector);

                if (in_array($rolUsuario, $rolesPermitidos)) {
                    return $handler->handle($request);
                } else {
                    throw new Exception('Perfil no autorizado para esta accion');
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

    private function obtenerRolesPermitidosParaTipoPedido(?string $tipoPedido): array
    {
        $rolesParaTiposDePedido = [
            'cocina' => ['socio', 'cocinero'],
            'barra de choperas' => ['socio', 'cervecero'],
            'barra de bebidas' => ['socio', 'bartender'],
            'postre' => ['socio', 'maestro pastelero']
        ];

        return $rolesParaTiposDePedido[$tipoPedido] ?? [];
    }
}
