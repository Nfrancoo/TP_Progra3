<?php
require_once './controllers/log_transacciones_controller.php';
require_once './middlewares/JWT.php';

class LogMiddleware
{
    public static function LogTransaccion($request, $handler)
    {
        $authorizationHeader = $request->getHeader('Authorization');
        $idUsuario = -1; // Default to -1 for unauthenticated users

        if (!empty($authorizationHeader) && preg_match('/Bearer\s+(.*)$/i', $authorizationHeader[0], $matches)) {
            $token = $matches[1];
            try {
                Autentificador::VerificarToken($token);
                $datos = Autentificador::ObtenerData($token);
                $idUsuario = $datos->id;
            } catch (Exception $e) {
                $response = new \Slim\Psr7\Response();
                $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
            }
        }

        $response = $handler->handle($request);
        $code = $response->getStatusCode();
        $accion = $request->getUri()->getPath();

        // Log the transaction
        LogTransaccionesController::InsertarLogTransaccion($idUsuario, $accion, $code);

        return $response;
    }
}
