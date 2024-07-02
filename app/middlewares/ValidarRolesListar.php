<?php

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

class ValidarRolesListar
{
    private $rolesPermitidos;

    public function __construct(...$roles)
    {
        $this->rolesPermitidos = $roles;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $header = $request->getHeaderLine('Authorization');
        $response = new Response();

        if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            $token = $matches[1];
            try {
                Autentificador::verificarToken($token);
                $datos = Autentificador::ObtenerData($token);
                $detallesPendientes = Pedido::obtenerDetallePedidosPendientes();
                $mensaje = [];

                foreach ($detallesPendientes as $detalle) {
                    switch ($detalle->sector) {
                        case 'cocina':
                            if (in_array($datos->rol, ['cocinero', 'socio'])) {
                                $mensaje[] = $detalle;
                            }
                            break;
                        case 'barra de choperas':
                            if (in_array($datos->rol, ['cervecero', 'socio'])) {
                                $mensaje[] = $detalle;
                            }
                            break;
                        case 'barra de bebidas':
                            if (in_array($datos->rol, ['bartender', 'socio'])) {
                                $mensaje[] = $detalle;
                            }
                            break;
                        case 'candybar':
                            if (in_array($datos->rol, ['maestro pastelero', 'socio'])) {
                                $mensaje[] = $detalle;
                            }
                            break;
                        default:
                            throw new Exception('Sector no válido');
                    }
                }

                if (empty($mensaje)) {
                    throw new Exception('No tienes acceso a ningun sector');
                }

                $response->getBody()->write(json_encode(['mensaje' => $mensaje]));
                return $response->withHeader('Content-Type', 'application/json');
            } catch (Exception $e) {
                $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
            }
        } else {
            $response->getBody()->write(json_encode(['error' => 'Token no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    }
}