<?php
namespace Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Repositories\FlatsRepository;

class FlatsController {
    private $flatsRepository;
    public function __construct() {
        $this->flatsRepository = new FlatsRepository();
    }
    public function GetAll(Request $request, Response $response) {
        
        $flats = $this->flatsRepository->GetAll();
        $response->getBody()->write(json_encode($flats));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        
    }

    public function GetAllWithFilter(Request $request, Response $response) {
        $params = $request->getQueryParams();
        $flats = $this->flatsRepository->getAllWithFilter($params);

        $response->getBody()->write(json_encode($flats));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
   
    }

    private static function ErrorResponse(Response $response, int $code, string $text) {
        $errorRes = $response->withStatus($code);
        $errorRes->getBody()->write(json_encode(
            ["message" => $text]
        ));
        return $errorRes
                ->withHeader('content-type', 'application/json')
                ->withStatus($code);;
    }
}