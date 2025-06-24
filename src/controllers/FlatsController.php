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
        $carry['flats'] = $flats;
        if(count($flats) > 0) 
            $carry = array_merge($carry, self::GetBoundaryValues($flats)); 

        $response->getBody()->write(json_encode($carry));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
   
    }

    public function GetStartBoundaryValues(Request $request, Response $response) {
        $flats = $this->flatsRepository->GetAll();
        $carry = self::GetBoundaryValues($flats); 
        $response->getBody()->write(json_encode($carry));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
    }


    public static function GetBoundaryValues($flats) {
        $minPrice = min(array_column($flats, "Price"));
        $maxPrice = max(array_column($flats, "Price"));
        $minFloor = min(array_column($flats, "Floor"));
        $maxFloor = max(array_column($flats, "Floor"));
        $minArea = min(array_column($flats, "Area"));
        $maxArea = max(array_column($flats, "Area"));
        $rooms = array_values(array_unique(array_column($flats, 'Roominess')));
        $totalItems = count($flats);
        $carry["minPrice"] = $minPrice;
        $carry["maxPrice"] = $maxPrice;
        $carry["minFloor"] = $minFloor;
        $carry["maxFloor"] = $maxFloor;
        $carry["minArea"] = $minArea;
        $carry["maxArea"] = $maxArea;
        $carry["rooms"] = $rooms;
        $carry["totalItems"] = $totalItems;

        return $carry;
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