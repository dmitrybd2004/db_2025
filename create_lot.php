<?php
try{
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if(isset($_FILES["item_image"])){
            //retrieve form data

            print_r($_FILES);
            $username = htmlspecialchars($_GET['user']);
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
    
            echo $file_name;

            $query = "INSERT INTO lot (seller_name, item_name, price, image_name, positive_review) VALUES('$username', '$name', $price, '$file_name', 0)";
            $result = $conn->query($query);

            move_uploaded_file($tmpname, $target_file);
    
            $conn->close();
            header("Location: success.php?user=" . $username);
            exit();
        }
    }
}
catch (Exception $e) {
    // Redirect back to the form with an error message
    header("Location: index.php?error=" . urlencode($e->getMessage()));
    exit();
}

?>