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
                gap: 15px;
            }
            .logout-btn {
                margin-left: auto;
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
                margin-bottom: 3px;
                padding: 4px;
                font-size: 14px;
            }

            .search-frame .form-group input {
                width: 48%;
            }

            .search-frame button {
                padding: 5px 10px;
                font-size: 14px;
            }
            .show-more-container {
                width: 100%;
                display: flex;
                justify-content: center;
                margin-top: 20px;
            }
            .show-more-btn {
                padding: 10px 20px;
                font-size: 16px;
            }
            .sort-search-container {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 10px;
                padding-top: 5px;
            }

            .sort-by {
                text-align: left;
            }
            hr {
            border: 1;
            clear:both;
            display:block;
            width: 110%;               
            background-color:black;
            height: 3px;
            }
        </style>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                if (sessionStorage.getItem("scrollPosition")) {
                    setTimeout(() => {
                        window.scrollTo({ 
                            top: sessionStorage.getItem("scrollPosition"), 
                            behavior: "instant"
                        });
                        sessionStorage.removeItem("scrollPosition");
                    }, 0);
                }
            });

            function saveScrollPosition() {
                sessionStorage.setItem("scrollPosition", window.scrollY);
            }
        </script>
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
                <a href="home.php" class="btn btn-light">Home</a>
                <a href="user_info.php?type=buy" class="btn btn-light">User Info</a>
                <a href="new_lot.php" class="btn btn-light">Post Your Item</a>
            </div>
            <a href="login_page.php" class="btn btn-light logout-btn">Log Out</a>
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

                        <div class="form-group sort-search-container">
                            <div class="sort-by">
                                <select name="sort" id="sort" class="form-control">
                                    <option value="time">Sort by: Time of publication</option>
                                    <option value="price_asc">Sort by: Price (Ascending)</option>
                                    <option value="price_desc">Sort by: Price (Descending)</option>
                                    <option value="rating">Sort by: Rating</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Search</button>
                        </div>
                    </form>
                </div>
            </div>

            <h2>Available Items</h2>
            <div class="items-container">
                <?php
                    $query = "SELECT lot.item_id, lot.seller_name, lot.item_name, lot.price, lot.image_name, user_info.rating FROM 
                    lot JOIN user_info ON user_info.username = lot.seller_name
                    WHERE buyer_name IS NULL AND seller_name <> '$name'";

                    if (!empty($_GET['search'])) {
                        $search = htmlspecialchars($_GET['search']);
                        $query .= " AND item_name LIKE '%{$search}%'";
                    }
                    else{
                        $search = "";
                    }
                    if (!empty($_GET['min_price'])) {
                        $min_price = (float) $_GET['min_price'];
                        $query .= " AND price >= $min_price";
                    }
                    else{
                        $min_price = "";
                    }
                    if (!empty($_GET['max_price'])) {
                        $max_price = (float) $_GET['max_price'];
                        $query .= " AND price <= $max_price";
                    }
                    else{
                        $max_price = "";
                    }
                    if (!empty($_GET['min_rating'])) {
                        $min_rating = (int) $_GET['min_rating'];
                        $query .= " AND lot.seller_name IN (
                            SELECT  user_info.username
                            FROM user_info
                            WHERE rating >= $min_rating
                        )";
                    }
                    else{
                        $min_rating = "";
                    }
                    if (!empty($_GET['limit'])) {
                        $limit = (int) $_GET['limit'];
                    }
                    else{
                        $limit = 30;
                    }
                    if (!empty($_GET['sort'])) {
                        $sort_type = htmlspecialchars($_GET['sort']);
                    }
                    else{
                        $sort_type = "time";
                    }

                    if($sort_type == "time"){
                        $query .= " ORDER BY item_id DESC";
                    }
                    else if ($sort_type == "price_asc"){
                        $query .= " ORDER BY price";
                    }
                    else if ($sort_type == "price_desc"){
                        $query .= " ORDER BY price DESC";
                    }
                    else if ($sort_type == "rating"){
                        $query .= " ORDER BY rating DESC";
                    }
                    $query .= " LIMIT $limit";
                    $result = $conn->query($query);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {                      
                            echo "<div class='item'>";
                            echo "<img src='image/" . htmlspecialchars($row['image_name']) . "' alt='Item Image'>";
                            echo "<hr>";
                            echo "<h4>" . htmlspecialchars($row['item_name']) . "</h4>";
                            echo "<h4>$" . number_format($row['price'], 2) . "</h4>";
                            echo "<h5> Sold by: "  . htmlspecialchars($row['seller_name']) . "(" . htmlspecialchars($row['rating']) . ")</h5>";
                            echo "<form action='buy.php' method='post'>";
                            echo "<input type='hidden' name='item_name' value='" . htmlspecialchars($row['item_name']) . "'>";
                            echo "<input type='hidden' name='item_id' value='" . $row['item_id'] . "'>";
                            echo "<input type='hidden' name='price' value='" . $row['price'] . "'>";
                            echo "<input type='hidden' name='seller_name' value='" . ($row['seller_name']) . "'>";
                            echo "<input type='hidden' name='buyer_name' value='" . $name . "'>";
                            if($row['price']<=$balance){
                                echo "<button type='submit' class='btn btn-success' onclick='saveScrollPosition()'>Buy</button>";
                            }
                            else{
                                echo "<button type='submit' class='btn btn-success' disabled>Buy</button>";
                            }
                            echo "</form>";
                            echo "</div>";
                        }
                        if($result->num_rows > $limit){
                            $new_limit = $limit + 30;
                            echo "<div class='show-more-container'>";
                            echo "<a href='home.php?min_price=$min_price&max_price=$max_price&min_rating=$min_rating&search=$search&limit=$new_limit&sort=$sort_type' class='btn btn-success' onclick='saveScrollPosition()'>Show more</a>";
                            echo "</div>";
                        }
                        else{
                            echo "<div class='show-more-container'>";
                            echo "<button type='submit' class='btn btn-success' disabled>No more items available</button>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No items available.</p>";
                    }

                    $conn->close();
                ?>
            </div>
        </div>
    </body>
</html>