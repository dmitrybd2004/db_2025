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
            body{
                background-color:rgb(244, 255, 183);
            }
            .top-bar {
                background: black;
                color: white;
                padding: 10px;
                display: flex;
                justify-content: flex-start;
                align-items: center;
                gap: 15px;
            }
            .top-left {
                display: flex;
                align-items: center;
                gap: 15px; /* Add spacing between elements */
            }
            .logout-btn {
                margin-left: auto; /* Push Log Out button to the right */
            }
            .container {
                margin-top: 50px;
                text-align: center;
            }
            .search-form {
                margin: 10px 0;
            }
            .items-container {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
                justify-content: center;
            }

            .item {
                border: 2px solid black;
                padding: 10px;
                margin: 10px;
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 250px;
                text-align: center;
                background-color: #fff;
                border-radius: 5px;
            }

            .item img {
                width: 200px;
                height: 250px;
            }
            .search-frame {
                border: 2px solid #ddd;
                padding: 10px;
                margin: 10px auto;
                width: 30%;
                background: #f8f9fa;
                border-radius: 10px;
                text-align: center;
            }

            .search-frame .form-group {
                display: flex;
                justify-content: space-between;
            }

            .search-frame input {
                width: 100%;
                margin-bottom: 3px; /* Less vertical space between inputs */
                padding: 4px;
                font-size: 14px;
            }

            .search-frame .form-group input {
                width: 48%; /* Make min/max price inputs fit on the same line */
            }

            .search-frame button {
                padding: 5px 10px;
                font-size: 14px;
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
                $name = $_SESSION["username"];
            }
            $query = "SELECT balance FROM user_info WHERE username = '$name'";
            $result = $conn->query($query);
        
            $info = mysqli_fetch_all($result, MYSQLI_ASSOC);
            $balance = $info[0]['balance'];
        ?>
        <div class="top-bar">
            <div class="top-left">
                Welcome, <?php echo $name; ?>
                <br />
                Balance: $<?php echo $balance ?>
                <a href="success.php" class="btn btn-light">Home</a>
                <a href="user_info.php?type=none" class="btn btn-light">User Info</a>
                <a href="new_lot.php" class="btn btn-light">Post Your Item</a>
            </div>
            <a href="index.php" class="btn btn-light logout-btn">Log Out</a>
        </div>

        <div class="container">
            <div class="search-form">
                <div class="search-frame">
                    <form method="GET" action="">
                        <div class="form-group">
                            <input 
                                type="number"
                                step="0.01"
                                min="0"
                                name="min_price" 
                                placeholder="Minimal price"
                                class="form-control"
                            >
                            <input 
                                type="number"
                                step="0.01"
                                min="0"
                                name="max_price"
                                placeholder="Maximal price"
                                class="form-control"
                            >
                        </div>
                        <input 
                            type="number" 
                            name="min_rating"
                            min = 0
                            placeholder="Minimal rating"
                            class="form-control">
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Search items..."
                            class="form-control">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </form>
                </div>
            </div>

            <h2>Available Items</h2>
            <div class="items-container">
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
                    $query .= "ORDER BY item_id DESC LIMIT 30";
                    
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
            </div>
        </div>
    </body>
</html>