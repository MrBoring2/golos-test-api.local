<?php
namespace Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Repositories\SalesRepository;

class SalesController {
     private $salesRepository;
    public function __construct() {
        $this->salesRepository = new SalesRepository();
    }

    public function GetAll(Request $request, Response $response) {
        
        $flats = $this->salesRepository->GetAll();
        $response->getBody()->write(json_encode($flats));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        
    }
}