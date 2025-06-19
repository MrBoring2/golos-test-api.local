<?php
namespace Controllers;

use Models\Db;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;
use PDOException;

class FlatsController {
    public function getAll(Request $request, Response $response) {
        $sql = "SELECT BIN_TO_UUID(flats.Id) as Id, 
                        flats.Floor,
                        flats.Type,
                        flats.Area,
                        flats.Roominess,
                        flats.Price,
                        flats.Number,
                        flats.Housing,
                        flats.Section,
                        flats.Floor
                        FROM Flats flats";
        $imagesQuery = "
                SELECT 
                BIN_TO_UUID(FlatId) as FlatId,
                Type as ImageType,
                Path as ImagePath
                FROM FlatImages";
        try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->query($sql);
        $flats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $conn->query($imagesQuery);
        $allImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $db = null;

        $imagesByFlatId = [];
            foreach ($allImages as $image) {
            $imagesByFlatId[$image['FlatId']][] = [
            'ImageType' => $image['ImageType'],
            'ImagePath' => $image['ImagePath']
        ];

        foreach ($flats as &$flat) {
            $flat['Images'] = $imagesByFlatId[$flat['Id']] ?? [];
        }
        }


        $response->getBody()->write(json_encode($flats));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        }
        catch (PDOException $e) {
            return self::errorResponse($response, 400, "Ошибка доступа к данным");
        }
    }

    private static function errorResponse(Response $response, int $code, string $text) {
        $errorRes = $response->withStatus($code);
        $errorRes->getBody()->write(json_encode(
            ["message" => $text]
        ));
        return $errorRes
                ->withHeader('content-type', 'application/json')
                ->withStatus(500);;
    }
}