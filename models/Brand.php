<?php
class Brand
{
    private $conn;
    private $table = 'brands';
    public function __construct($db) {
        $this->conn = $db;
    }

    private $id;
    private $name;
    private $website;
    private $country;
    private $quantity;
}

?>