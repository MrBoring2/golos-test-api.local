<?php
namespace Repositories;

interface IRepository {
    public function Get($id);
    public function GetAll();
    public function GetAllWithFilter($filterArray);
    public function Save($fitem);
    public function Delete($id);
    public function Update($id, $item);
}