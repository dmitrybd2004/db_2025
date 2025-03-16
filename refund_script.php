<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $host = "localhost";
    $dbusername = "root";
    $dbpassword = "3215979361";
    $dbname = "auth";

    $conn = new mysqli($host,$dbusername,$dbpassword,$dbname);

    if($conn->connect_error){
        die("Connetion failed: ". $conn->connect_error);
    }


    $request_id = $_POST['request_id'];
    $infoquery = "SELECT * FROM refunds WHERE refunds.request_id = $request_id";
    $result = $conn->query($infoquery);
    $info = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $item_id = $info[0]['item_id'];
    $type = $info[0]['report_type'];

    $query = "SELECT buyer_name, seller_name, price FROM lot WHERE item_id = $item_id";
    $result = $conn->query($query);
    $info = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    $buyer = $info[0]['buyer_name'];
    $seller = $info[0]['seller_name'];
    $price = $info[0]['price'];

    if(isset($_POST['reject_btn'])){

        $query = "UPDATE refunds SET processed = 1 WHERE item_id = $item_id";
        $conn->query($query);     

    }
    if(isset($_POST['accept_btn'])){
        if($type == 0){
            $query = "UPDATE user_info SET balance = balance - $price WHERE username = '$seller'";
            $conn->query($query);
    
            $query = "UPDATE user_info SET balance = balance + $price WHERE username = '$buyer'";
            $conn->query($query);

            $query = "UPDATE user_info SET products_sold = products_sold - 1 WHERE username = '$seller'";
            $conn->query($query);
    
            $query = "UPDATE user_info SET products_bought = products_bought - 1 WHERE username = '$buyer'";
            $conn->query($query);
            
            $query = "UPDATE lot SET buyer_name = NULL WHERE buyer_name = '$buyer' AND item_id = $item_id";
            $conn->query($query);

            $query = "UPDATE refunds SET processed = 1 WHERE request_id = $request_id";
            $conn->query($query);

            $query = "UPDATE refunds SET accepted = 1 WHERE request_id = $request_id";
            $conn->query($query);  
        }
        else if ($type == 1){
            $query = "UPDATE user_info SET balance = balance - $price WHERE username = '$seller'";
            $conn->query($query);
    
            $query = "UPDATE user_info SET balance = balance + $price WHERE username = '$buyer'";
            $conn->query($query);

            $query = "UPDATE user_info SET products_sold = products_sold - 1 WHERE username = '$seller'";
            $conn->query($query);
    
            $query = "UPDATE user_info SET products_bought = products_bought - 1 WHERE username = '$buyer'";
            $conn->query($query);
            
            $query = "UPDATE lot SET buyer_name = NULL WHERE buyer_name = '$buyer' AND item_id = $item_id";
            $conn->query($query);

            $query = "UPDATE refunds SET processed = 1 WHERE request_id = $request_id";
            $conn->query($query);

            $query = "UPDATE refunds SET accepted = 1 WHERE request_id = $request_id";
            $conn->query($query);

            $query = "UPDATE login SET banned = 1 WHERE username = '$seller'";
            $conn->query($query);
            
            $query = "SELECT image_name FROM lot WHERE buyer_name IS NULL AND seller_name = '$seller'";
            $result = $conn->query($query);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $file_name = htmlspecialchars($row['image_name']);
                    $target_file = "./image/" . $file_name;
                    unlink($target_file);
                }
            }        
        
            $query = "DELETE FROM lot WHERE buyer_name IS NULL AND seller_name = '$seller'";
            $conn->query($query);   
        }
    }

    header("Location:home_mod.php");
    exit();

    $conn->close();
}
?>