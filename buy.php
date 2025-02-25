<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    print_r($_POST);
    $item_name = $_POST['item_name'];
    $item_id = $_POST['item_id'];
    $price = $_POST['price'];
    $seller_name = $_POST['seller_name'];
    $buyer_name = $_POST['buyer_name'];

    // echo "<h2>Purchase Successful!</h2>";
    // echo "<p>You have bought: <strong>" . htmlspecialchars($item_name) . "</strong></p>";
    // echo "<p>Item_id: " . number_format($item_id) . "</p>";
    // echo "<p>Price: $" . number_format($price, 2) . "</p>";
    // echo "<p>Seller name: " . htmlspecialchars($seller_name) . "</p>";
    // echo "<p>Buyer name: " . htmlspecialchars($buyer_name) . "</p>";


    //Database connection

    $host = "localhost";
    $dbusername = "root";
    $dbpassword = "3215979361";
    $dbname = "auth";

    $conn = new mysqli($host,$dbusername,$dbpassword,$dbname);

    if($conn->connect_error){
        die("Connetion failed: ". $conn->connect_error);
    }

    // validate login authentication
    $query = "UPDATE user_info SET balance = balance + $price, products_sold = products_sold + 1 WHERE username = '$seller_name'";

    $conn->query($query);

    $query = "UPDATE user_info SET balance = balance - $price, products_bought = products_bought + 1 WHERE username = '$buyer_name'";

    $conn->query($query);

    $query = "UPDATE lot SET buyer_name = '$buyer_name' WHERE item_id = $item_id";

    $conn->query($query);

    header("Location:success.php?user=" . $buyer_name);
    exit();

    $conn->close();
}
?>