<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_name = $_POST['item_name'];
    $item_id = $_POST['item_id'];
    $price = $_POST['price'];
    $seller_name = $_POST['seller_name'];
    $buyer_name = $_POST['buyer_name'];

    $host = "localhost";
    $dbusername = "root";
    $dbpassword = "3215979361";
    $dbname = "auth";

    $conn = new mysqli($host,$dbusername,$dbpassword,$dbname);

    if($conn->connect_error){
        die("Connetion failed: ". $conn->connect_error);
    }

    $query = "UPDATE user_info SET balance = balance + $price, products_sold = products_sold + 1 WHERE username = '$seller_name'";

    $conn->query($query);

    $query = "UPDATE user_info SET balance = balance - $price, products_bought = products_bought + 1 WHERE username = '$buyer_name'";

    $conn->query($query);

    $query = "UPDATE lot SET buyer_name = '$buyer_name' WHERE item_id = $item_id";

    $conn->query($query);

    header("Location:home.php");
    exit();

    $conn->close();
}
?>