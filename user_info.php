<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Welcome</title>
    </head>
    <body>
        <?php
            if (isset($_SESSION["username"])) {
        
                //Database connection
        
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

                echo "Profile name: ". $name . "</p>";

            }
        ?>
        <a href="success.php">
            <button>Home</button>
        </a>
        <br>
        <?php
        if (isset($_SESSION["username"])) {
            echo "Your balance is: " . $info[0]['balance'];
        }
        ?>
        <br>
        <?php
        if (isset($_SESSION["username"])) {
            echo "Your rating is: " . $info[0]['rating'];
        }
        ?>
        <br>
        <?php
        if (isset($_SESSION["username"])) {
            echo "You bought " . $info[0]['products_bought'] . " items";
        }
        ?>
        <br>
        <?php
        if (isset($_SESSION["username"])) {
            echo "You sold " . $info[0]['products_sold'] . " items";
        }
        ?>
        <br>
        <form action="deposit.php" method="POST">
            <input 
                type="number"
                step="0.01"
                min = 0.01
                name="amount"
                class="form-control" 
                placeholder="Enter deposit"
            /><br />
                <input type="submit" name="deposit_btn" value="Add funds" class="btn btn-success">
        </form>
        <?php
            if (isset($_GET['error'])) {
                echo "<p style='color:red;'>" ."Error: ". htmlspecialchars($_GET['error']) . "</p>";
            }
        ?>
        <h2>Bought Items</h2>
        <?php
        $query = "SELECT * FROM lot WHERE buyer_name = '$name'";
        $result = $conn->query($query);
        if ($result->num_rows > 0) {
            // Display each item
            while($row = $result->fetch_assoc()) {
                echo "<div class='item'>";
                echo "<img src='image/" . htmlspecialchars($row['image_name']) . "' alt='Item Image'>";
                echo "<h3>" . htmlspecialchars($row['item_name']) . "</h3>";
                if($row['positive_review'] == 0){
                    echo"No positive review";
                }
                else{
                    echo"Positively reviewed";
                }
                echo "<form action='leave_review.php' method='post'>";
                echo "<input type='hidden' name='item_id' value='" . $row['item_id'] . "'>";
                echo "<button type='submit' class='btn btn-success'>Change review</button>";
                echo "</form>";
                echo "</div>";
            }
        } else {
            echo "<p>No items were bought.</p>";
        }
        ?>

        <h2>Selling items</h2>
        <?php
        $query = "SELECT * FROM lot WHERE seller_name = '$name' AND buyer_name IS NULL";
        $result = $conn->query($query);
        if ($result->num_rows > 0) {
            // Display each item
            while($row = $result->fetch_assoc()) {
                echo "<div class='item'>";
                echo "<img src='image/" . htmlspecialchars($row['image_name']) . "' alt='Item Image'>";
                echo "<h3>" . htmlspecialchars($row['item_name']) . "</h3>";
                echo "<p>Price: $" . number_format($row['price'], 2) . "</p>";
                echo "<form action='delete_item.php' method='post'>";
                echo "<input type='hidden' name='item_id' value='" . $row['item_id'] . "'>";
                echo "<button type='submit' class='btn btn-success'>Remove from sale</button>";
                echo "</form>";
                echo "</div>";
            }
        } else {
            echo "<p>You don't sell any items.</p>";
        }

        // Close the connection
        $conn->close();
        ?>
    </body>
</html>