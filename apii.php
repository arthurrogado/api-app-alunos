<?php

    header('Access-Control-Allow-Origin: *');

    require 'db.php';
    
    if($_GET)
    {
        isset($_GET["id"]) ? $id = $_GET["id"] : $id = 1;
    
        $sql = 'SELECT * FROM alunos WHERE id = :id';
        $query = $conn->prepare($sql);
        $query->bindValue(':id', $id);
        
        if($query->execute()) {
            $result = $query->fetchAll(PDO::FETCH_OBJ);
        } else {
            $result = "DEU ERRO";
        }
    }
    
    else if($_POST) 
    {
        isset($_POST['id']) ? $id = $_POST['id'] : $id = 1;
        
        $sql = 'SELECT * FROM alunos WHERE id = :id';
        $query = $conn->prepare($sql);
        $query->bindValue(':id', $id);
        
        if($query->execute())
        {
            $result = $query->fetchAll(PDO::FETCH_OBJ);
        } else
        {
            $result = "DEU RUIM!";
        }
    }

    echo json_encode($result);

?>