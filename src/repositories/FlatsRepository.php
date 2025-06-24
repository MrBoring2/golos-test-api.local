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
            echo $e->getMessage();
            return [];
        } finally {
            $db = null;
        }
    }

    public function GetAllWithFilter($filterArray) {

        $filters = [
            'MinArea' => $filterArray['minArea'] ?? null,
            'MaxArea' => $filterArray['maxArea'] ?? null,
            'Rooms' => $filterArray['rooms'] ?? [],
            'MinPrice' => $filterArray['minPrice'] ?? null,
            'MaxPrice' => $filterArray['maxPrice'] ?? null,
            'MinFloor'=> $filterArray['minFloor'] ?? null,
            'MaxFloor'=> $filterArray['maxFloor'] ?? null,
            'OrderBy' => $filterArray['orderBy'] ?? null
        ];

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

        $params = [];

        foreach ($filters as $key => $value) {
            if($value === null) continue;

            if($key === 'MinArea' && $filters[$key] != null){
                $sql .= " AND flats.Area >= ?";
                $params[] = $value;
            }
            else if ($key === 'MaxArea'  && $filters[$key] != null){
                $sql .= " AND flats.Area <= ?";
                $params[] = $value; 
            }
            else if ($key === 'Rooms'  && !empty($filters[$key])){
                $rooms = is_array($filters['Rooms']) ? $filters['Rooms'] : [$filters['Rooms']];
                $sql .= " AND flats.Roominess IN (" . str_repeat('?,', count($rooms) - 1) . '?)';
                $params = array_merge($params, $rooms); 
            }
            else if ($key === 'MinPrice'  && $filters[$key] != null){
                $sql .= " AND flats.Price >= ?";
                $params[] = $value; 
            }
            else if ($key === 'MaxPrice'  && $filters[$key] != null){
                $sql .= " AND flats.Price <= ?";
                $params[] = $value; 
            }
            else if ($key === 'MinFloor' && $filters[$key] != null){
                $sql .= " AND flats.Floor >= ?";
                $params[] = $value; 
            }
            else if ($key === 'MaxFloor' && $filters[$key] != null){
                $sql .= " AND flats.Floor <= ?";
                $params[] = $value; 
            }
        }

        $orderBy = $filters['OrderBy'];
        if ($orderBy != null){
            if ($orderBy == 'price') $sql .= " ORDER BY flats.Price ASC";
            else if($orderBy == '-price') $sql .= " ORDER BY flats.Price DESC";
            else if($orderBy == 'area') $sql .= " ORDER BY flats.Area ASC";
            else if($orderBy == '-area') $sql .= " ORDER BY flats.Area DESC";
        }
 

        try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $flats = $stmt->fetchAll(PDO::FETCH_ASSOC);
     
     
        $flatsWithImages = self::computeImages($flats);

        return $flatsWithImages;
        }
        catch (PDOException $e) {
            //echo $e->getMessage();
            return [];
        } finally {
            $db = null;
        }
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
            'Section' => $item['Section'],
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