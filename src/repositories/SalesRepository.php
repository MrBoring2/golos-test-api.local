<?php
namespace Repositories;
use PDO;
use PDOException;
use Models\Db;


class SalesRepository implements IRepository {
    public function Get($id) {
        return null;
    }

    public function GetAll() {
        $sql = "SELECT DISTINCT Title FROM FlatSales";
     
        try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->query($sql);

        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $sales;
        }
        catch (PDOException $e) {
            echo $e->getMessage();
            return [];
        } finally {
            $db = null;
        }
    }
    public function GetAllWithFilter($filterArray) {}
    public function Update($id, $data) {}
    public function Delete($id) {}
    public function Save($item) {}
}