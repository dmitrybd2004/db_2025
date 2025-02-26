<?php
session_start();
try{
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if(isset($_FILES["item_image"])){
            //retrieve form data

            if(empty($_POST['item_name'])){
                throw new Exception("Please enter the item name");
            }
            if(empty($_FILES['item_image'])){
                throw new Exception("Please upload the image");
            }           
            $username = $_SESSION["username"];

            $name = $_POST['item_name']; 
            $price = $_POST['price'];
            $tmpname = $_FILES["item_image"]["tmp_name"];
            $file_name = basename($_FILES["item_image"]["name"]);
            $target_file = "./image/" . $file_name;
    
            //Database connection
    
            $host = "localhost";
            $dbusername = "root";
            $dbpassword = "3215979361";
            $dbname = "auth";
    
            $conn = new mysqli($host,$dbusername,$dbpassword,$dbname);
    
            if($conn->connect_error){
                die("Connetion failed: ". $conn->connect_error);
    
            }
            $query = "INSERT INTO lot (seller_name, item_name, price, image_name, positive_review) VALUES('$username', '$name', $price, '$file_name', 0)";
            $result = $conn->query($query);

            move_uploaded_file($tmpname, $target_file);
    
            $conn->close();
            header("Location: success.php");
            exit();
        }
    }
}
catch (Exception $e) {
    // Redirect back to the form with an error message
    header("Location: new_lot.php?error=" . urlencode($e->getMessage()));
    exit();
}

?>