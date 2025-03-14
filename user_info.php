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
            .item form {
                width: 100%;
                display: flex;
                justify-content: center;
                margin-top: 10px;
                flex-direction: column;
                align-items: center;
            }

            .item button {
                width: 80%;
                padding: 10px;
                margin-bottom: 15px;
            }
            .info-frame {
                background-color: rgb(137, 191, 249);
                color: black;
                padding: 20px;
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .info-content {
                width: 50%;
                max-width: 500px;
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .info-content input, 
            .info-content .btn {
                width: 100%;
            }
            .info-frame input {
                width: 100%;
                margin-bottom: 8px;
            }
            .info-frame p {
                margin-bottom: 2px;
            }
            .main-container {
                display: flex;
                align-items: flex-start;
            }
            .button-group {
                display: flex;
                gap: 10px;
                margin-top: 10px;
            }
            .main-content {
                display: flex;
                justify-content: center;
                align-items: flex-start;
                width: 100%;
            }
            .items-section {
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 100%;
                padding: 20px 0;
            }

            .items-container {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
                justify-content: center;
                max-width: 100%;
            }

            .items-title {
                font-size: 28px;
                font-weight: bold;
                text-align: center;
                margin-bottom: 20px;
                width: 100%;
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
            if (isset($_SESSION["username"])) {
        
                $host = "localhost";
                $dbusername = "root";
                $dbpassword = "3215979361";
                $dbname = "auth";
        
                $conn = new mysqli($host,$dbusername,$dbpassword,$dbname);
        
                if($conn->connect_error){
                    die("Connetion failed: ". $conn->connect_error);
        
                }

                $name = $_SESSION["username"];
                $infoquery = "SELECT * FROM user_info WHERE user_info.username='$name'";
                $result = $conn->query($infoquery);
                $info = mysqli_fetch_all($result, MYSQLI_ASSOC);

                $balance = $info[0]['balance'];

            }
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
        <div class="main-container">
            <div class="info-frame">
                <div class="info-content">
                    <p>Your username is: <?php echo $name; ?></p>
                    <p>Your balance is: $<?php echo number_format($balance, 2); ?></p>

                    <form action="deposit.php" method="POST">
                        <input 
                            type="number"
                            step="0.01"
                            min="0.01"
                            name="amount"
                            class="form-control"
                            placeholder="Enter deposit"
                        />
                        <input type="submit" name="deposit_btn" value="Add funds" class="btn btn-success">
                    </form>

                    <?php
                        if (isset($_GET['error'])) {
                            echo "<p style='color:red;'>Error: " . htmlspecialchars($_GET['error']) . "</p>";
                        }
                    ?>

                    <p>Your rating: <?php echo $info[0]['rating']; ?></p>
                    <p>You bought <?php echo $info[0]['products_bought']; ?> items</p>
                    <p>You sold <?php echo $info[0]['products_sold']; ?> items</p>
                    <div class="button-group">
                            <a href="user_info.php?type=buy" class="btn btn-light">Show bought items</a>
                            <br>
                            <a href="user_info.php?type=sell" class="btn btn-light">Show items you sell</a>
                            <br>
                            <a href="user_info.php?type=sold" class="btn btn-light">Show items sold</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="items-section">
            <div class="items-container">
                <?php
                if (htmlspecialchars($_GET['type']) == "buy") {
                    $query = "SELECT * FROM lot WHERE buyer_name = '$name'";
                    $result = $conn->query($query);
                    if ($result->num_rows > 0) {
                        echo "<div class='items-title'>Items bought</div>";
                        while ($row = $result->fetch_assoc()) {
                            echo "<div class='item'>";
                            echo "<img src='image/" . htmlspecialchars($row['image_name']) . "' alt='Item Image'>";
                            echo "<h3>" . htmlspecialchars($row['item_name']) . "</h3>";
                            if($row['review'] == 0){
                                echo "No review";
                            }
                            else if ($row['review'] == 1){
                                echo "Positively reviewed";
                            }
                            else{
                                echo "Negatively reviewed";
                            }
                            echo "<form action='leave_review.php' method='post' onsubmit='saveScrollPosition()'>";
                            echo "<input type='hidden' name='scrollPosition' class='scrollPosition' />";
                            echo "<input type='hidden' name='item_id' value='" . $row['item_id'] . "'>";

                            if($row['review'] == 0 || $row['review'] == -1){
                                echo "<button type='submit' name='positive_btn' value='posititve' class='btn btn-success'>Leave positive review</button>";
                            }
                            else if ($row['review'] == 1){
                                echo "<button type='submit' name='remove_btn' value='remove' class='btn btn-success'>Remove review</button>";
                            }

                            if($row['review'] == 0 || $row['review'] == 1){
                                echo "<button type='submit' name='negative_btn' value='negatitve' class='btn btn-success'>Leave negative review</button>";
                            }
                            else if ($row['review'] == -1){
                                echo "<button type='submit' name='remove_btn' value='remove' class='btn btn-success'>Remove review</button>";
                            }
                            echo "</form>";
                            echo "</div>";
                        }
                    }
                    else{
                        echo "<div class='items-title'>No items were bought</div>";
                    }
                } else if (htmlspecialchars($_GET['type']) == "sell") {
                    $query = "SELECT * FROM lot WHERE seller_name = '$name' AND buyer_name IS NULL";
                    $result = $conn->query($query);
                    if ($result->num_rows > 0) {
                        echo "<div class='items-title'>Items you sell</div>";
                        while ($row = $result->fetch_assoc()) {
                            echo "<div class='item'>";
                            echo "<img src='image/" . htmlspecialchars($row['image_name']) . "' alt='Item Image'>";
                            echo "<h3>" . htmlspecialchars($row['item_name']) . "</h3>";
                            echo "<p>Price: $" . number_format($row['price'], 2) . "</p>";
                            echo "<form action='delete_item.php' method='post' onsubmit='saveScrollPosition()'>";
                            echo "<input type='hidden' name='item_id' value='" . $row['item_id'] . "'>";
                            echo "<button type='submit' class='btn btn-success'>Remove from sale</button>";
                            echo "</form>";
                            echo "</div>";
                        }
                    }
                    else{
                        echo "<div class='items-title'>No items are being sold</div>";
                    }
                }
                else if(htmlspecialchars($_GET['type']) == "sold"){
                    $query = "SELECT * FROM lot WHERE seller_name = '$name' AND buyer_name IS NOT NULL";
                    $result = $conn->query($query);
                    if ($result->num_rows > 0) {
                        echo "<div class='items-title'>Sold items</div>";
                        while ($row = $result->fetch_assoc()) {
                            echo "<div class='item'>";
                            echo "<img src='image/" . htmlspecialchars($row['image_name']) . "' alt='Item Image'>";
                            echo "<h3>" . htmlspecialchars($row['item_name']) . "</h3>";
                            if($row['review'] == 0){
                                echo "No review";
                            }
                            else if ($row['review'] == 1){
                                echo "Positively reviewed";
                            }
                            else{
                                echo "Negatively reviewed";
                            }
                            echo "</div>";
                        }
                    }
                    else{
                        echo "<div class='items-title'>No items were sold</div>";
                    }
                }
                ?>
            </div>
        </div>
    </body>
</html>