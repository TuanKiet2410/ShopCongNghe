<?php
class Banner{
    private $conn;
    public $id;
    public $image;
    public $link;
    public function __construct($db){
        $this->conn = $db;
    }
    public function getAll(){
        $query = "SELECT * FROM banners";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    public function create(){
        $query = "INSERT INTO banners SET image=:image, link=:link";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':link', $this->link);
        if($stmt->execute()) return true;
        return false;
    }
    public function update(){
        $query = "UPDATE banners SET image=:image, link=:link WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':link', $this->link);
        $stmt->bindParam(':id', $this->id);
        if($stmt->execute()) return true;
        return false;
    }
    public function delete(){
        $query = "DELETE FROM banners WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        if($stmt->execute()) return true;
        return false;
    }
    public function getById(){
        $query = "SELECT * FROM banners WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row){
            $this->image = $row['image'];
            $this->link = $row['link'];
            return true;
        }
        return false;
    }
    
}




?>