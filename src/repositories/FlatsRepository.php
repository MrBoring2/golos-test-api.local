<?php
namespace Repositories;
use PDO;
use PDOException;
use Models\Db;

class FlatsRepository implements IRepository {
    public function Get($id) {
        return null;
    }

    public function GetAll(){
        $sql = "SELECT BIN_TO_UUID(flats.Id) as Id, 
                        flats.Floor,
                        flats.Type,
                        flats.Area,
                        flats.Roominess,
                        flats.Price,
                        flats.Number,
                        flats.Housing,
                        flats.Section,
                        flats.Floor,
                        BIN_TO_UUID(images.Id) as ImageId,
                        images.Type as ImageType,
                        images.Path as ImagePath
                        FROM Flats flats
                        JOIN FlatImages images ON images.FlatId = flats.Id";
        try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->query($sql);
        $flats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return self::computeImages($flats);
        }
        catch (PDOException $e) {
            return [];
        } finally {
            $db = null;
        }
    }

    public function GetAllWithFilter($filterArray) {

    }

    public function Save($flat){
        return null;
    }

    public function Update($id, $flat) {

    }

    public function Delete($id) {

    }

    private static function computeImages($flats){
        $result = array_reduce($flats, function($carry, $item) {
        $id = $item['Id'];
    
        if (!isset($carry[$id])) {
            $carry[$id] = [
            'Id' => $item['Id'],
            'Floor' => $item['Floor'],
            'Type' => $item['Type'],
            'Area' => $item['Area'],
            'Roominess' => $item['Roominess'],
            'Price' => $item['Price'],
            'Number' => $item['Number'],
            'Housing' => $item['Housing'],
            'Images' => []
            ];
        }
    
        if ($item['ImageId']) {
        $carry[$id]['Images'][] = [
            'Id' => $item['ImageId'],
            'Type' => $item['ImageType'],
            'Path' => $item['ImagePath'],
        ];
        }
    
        return $carry;
    }, []);

    return array_values($result);
    }
}