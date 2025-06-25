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
                        images.Path as ImagePath,
                        BIN_TO_UUID(sales.Id) as SaleId,
                        sales.Title as SaleTitle
                        FROM Flats flats
                        JOIN FlatImages images ON images.FlatId = flats.Id
                        LEFT JOIN FlatSales sales ON sales.FlatId = flats.Id WHERE 1=1";
     
        try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->query($sql);

        $flats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return self::groupData($flats);
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
            'Sales' => $filterArray['sales'] ?? null,
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
                        images.Path as ImagePath,
                        BIN_TO_UUID(sales.Id) as SaleId,
                        sales.Title as SaleTitle
                        FROM Flats flats
                        JOIN FlatImages images ON images.FlatId = flats.Id
                        LEFT JOIN FlatSales sales ON sales.FlatId = flats.Id WHERE 1=1";

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
            else if($key === 'Sales' && !empty($filters[$key])){
                $sales = is_array($filters['Sales']) ? $filters['Sales'] : [$filters['Sales']];
                $sql .= ' AND sales.Title = (' . str_repeat('?,', count($sales) - 1) . '?)';
                $params = array_merge($params, $sales);
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
     
     
        $flatsWithImages = self::groupData($flats);

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

    private static function groupData($flats){
    $grouped = [];
    
    foreach ($flats as $row) {
        $flatId = $row['Id'];
        
        if (!isset($grouped[$flatId])) {
            // Основные данные квартиры (без дублирования)
            $grouped[$flatId] = [
                'Id'        => $flatId,
                'Floor'     => $row['Floor'],
                'Type'      => $row['Type'],
                'Area'      => $row['Area'],
                'Roominess' => $row['Roominess'],
                'Price'     => $row['Price'],
                'Number'    => $row['Number'],
                'Housing'  => $row['Housing'],
                'Section'   => $row['Section'],
                'Images'   => [],
                'Sales'     => []
            ];
        }
        
        // Добавляем изображение (если есть и еще не добавлено)
        if ($row['ImageId'] && !self::imageExists($grouped[$flatId]['Images'], $row['ImageId'])) {
            $grouped[$flatId]['Images'][] = [
                'Id'   => $row['ImageId'],
                'Type' => $row['ImageType'],
                'Path' => $row['ImagePath']
            ];
        }
        
        // Добавляем акцию (если есть и еще не добавлена)
        if ($row['SaleId'] && !self::saleExists($grouped[$flatId]['Sales'], $row['SaleId'])) {
            $grouped[$flatId]['Sales'][] = [
                'Id'    => $row['SaleId'],
                'Title' => $row['SaleTitle']
            ];
        }
    }
    
    return array_values($grouped);
    }

    private static function imageExists(array $images, string $imageId): bool 
    {
    foreach ($images as $img) {
        if ($img['Id'] === $imageId) {
            return true;
        }
    }
    return false;
    }

    private static function saleExists(array $sales, string $saleId): bool 
    {
    foreach ($sales as $sale) {
        if ($sale['Id'] === $saleId) {
            return true;
        }
    }
    return false;
    }
}