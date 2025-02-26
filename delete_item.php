<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $item_id = $_POST['item_id'];


    //Database connection

    $host = "localhost";
    $dbusername = "root";
    $dbpassword = "3215979361";
    $dbname = "auth";

    $conn = new mysqli($host,$dbusername,$dbpassword,$dbname);

    if($conn->connect_error){
        die("Connetion failed: ". $conn->connect_error);
    }
    $query = "SELECT image_name FROM lot WHERE item_id=$item_id";
    $result = $conn->query($query);
    $info = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $file_name = $info[0]['image_name'];
    $target_file = "./image/" . $file_name;
    unlink($target_file);


    $query = "DELETE FROM lot WHERE item_id=$item_id";
    $conn->query($query);       

    header("Location:user_info.php");
    exit();

    $conn->close();
}
?>