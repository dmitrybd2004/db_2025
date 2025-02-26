<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <title>Welcome</title>
        <style>
            .item {
                border: 1px solid #ddd;
                padding: 10px;
                margin: 10px;
                display: inline-block;
                width: 250px;
                text-align: center;
            }
            img {
                max-width: 100%;
                height: auto;
            }
        </style>
    </head>
    <body>
        <?php

            $host = "localhost";
            $dbusername = "root";
            $dbpassword = "3215979361";
            $dbname = "auth";
    
            $conn = new mysqli($host,$dbusername,$dbpassword,$dbname);
    
            if($conn->connect_error){
                die("Connetion failed: ". $conn->connect_error);
    
            }

            if (isset($_SESSION["username"])) {
                $name = $_SESSION["username"]; // Retrieves everything after "?"
                echo "Welcome: ". $name . "</p>";
            }
            $query = "SELECT balance FROM user_info WHERE username = '$name'";
            $result = $conn->query($query);
        
            $info = mysqli_fetch_all($result, MYSQLI_ASSOC);
            $balance = $info[0]['balance'];
            echo "Your balance is: " . $balance;
        ?>
        <br />
        <a href="user_info.php">
            <button>User info</button>
        </a>
        <br />
        <a href="new_lot.php">
            <button>Post your item</button>
        </a>

        <form method="GET" action="">
            <input 
                type="number"
                step="0.01"
                min = 0
                name="min_price" 
                placeholder="Minimal price">
            <input 
                type="number"
                step="0.01"
                min = 0
                name="max_price"
                placeholder="Maximal price">
            <input 
                type="number" 
                name="min_rating"
                min = 0
                placeholder="Minimal rating">
            <input type="text" name="search" placeholder="Search items...">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>


        <h2>Available Items</h2>

        <?php

        $query = "SELECT item_id, seller_name, item_name, price, image_name FROM lot WHERE buyer_name IS NULL AND seller_name <> '$name'";

        if (!empty($_GET['search'])) {
            $search = htmlspecialchars($_GET['search']);
            $query .= " AND item_name LIKE '%{$search}%'";
        }
        if (!empty($_GET['min_price'])) {
            $min_price = (float) $_GET['min_price'];
            $query .= " AND price >= $min_price";
        }
        if (!empty($_GET['max_price'])) {
            $max_price = (float) $_GET['max_price'];
            $query .= " AND price <= $max_price";
        }
        if (!empty($_GET['min_rating'])) {
            $min_rating = (float) $_GET['min_rating'];
            $query .= " AND lot.seller_name IN (
                SELECT  user_info.username
                FROM user_info
                WHERE rating >= $min_rating
            )";
        }
        
        $result = $conn->query($query);
        if ($result->num_rows > 0) {
            // Display each item
            while($row = $result->fetch_assoc()) {
                echo "<div class='item'>";
                echo "<img src='image/" . htmlspecialchars($row['image_name']) . "' alt='Item Image'>";
                echo "<h3>" . htmlspecialchars($row['item_name']) . "</h3>";
                echo "<p>Price: $" . number_format($row['price'], 2) . "</p>";
                echo "<form action='buy.php' method='post'>";
                echo "<input type='hidden' name='item_name' value='" . htmlspecialchars($row['item_name']) . "'>";
                echo "<input type='hidden' name='item_id' value='" . $row['item_id'] . "'>";
                echo "<input type='hidden' name='price' value='" . $row['price'] . "'>";
                echo "<input type='hidden' name='seller_name' value='" . ($row['seller_name']) . "'>";
                echo "<input type='hidden' name='buyer_name' value='" . $name . "'>";
                if($row['price']<=$balance){
                    echo "<button type='submit' class='btn btn-success'>Buy</button>";
                }
                else{
                    echo "<button type='submit' class='btn btn-success' disabled>Buy</button>";
                }
                echo "</form>";
                echo "</div>";
            }
        } else {
            echo "<p>No items available.</p>";
        }

        // Close the connection
        $conn->close();
        ?>

    </body>
</html>