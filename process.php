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
            .form-container {
                background-color: rgb(137, 191, 249);
                padding: 20px;
                border-radius: 8px;
                width: 400px;
                margin: 50px auto;
                text-align: center;
                box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            }

            .form-container input,.form-container button {
                width: 100%;
                margin-bottom: 10px;
            }

            .form-container h2 {
                margin-bottom: 15px;
            }

            img {
                width: 200px;
                height: 250px;
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
            
            $request_id = $_POST['request_id'];
            $infoquery = "SELECT * FROM refunds WHERE refunds.request_id = $request_id";
            $result = $conn->query($infoquery);
            $info = mysqli_fetch_all($result, MYSQLI_ASSOC);

            $item_id = $info[0]['item_id'];
            $request_type = $info[0]['report_type'];

            if($request_type == 0){
                $type = "Refund";
            }
            if($request_type == 1){
                $type = "Refund + report";
            }
            

            $name = $_SESSION["username"];

            $infoquery = "SELECT * FROM lot JOIN user_info ON lot.seller_name = user_info.username
            WHERE lot.item_id = $item_id";
            $result = $conn->query($infoquery);
            $info = mysqli_fetch_all($result, MYSQLI_ASSOC);

            $item_name = $info[0]['item_name'];
            $price = $info[0]['price'];
            $buyer_name = $info[0]['buyer_name'];
            $seller_name = $info[0]['seller_name'];
            $seller_rating = $info[0]['rating'];
            $image = $info[0]['image_name'];

            $infoquery = "SELECT * FROM refunds WHERE refunds.request_id = $request_id";
            $result = $conn->query($infoquery);
            $info = mysqli_fetch_all($result, MYSQLI_ASSOC);
            
            $description = $info[0]['description'];
        ?>
        <div class="top-bar">
            <div class="top-left">
                Welcome, <?php echo $name; ?>
                <a href="home_mod.php" class="btn btn-light">Home</a>
            </div>
            <a href="login_page.php" class="btn btn-light logout-btn">Log Out</a>
        </div>
        
        <div class="form-container">
            <form action="refund_script.php" method="POST">

            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                <div style="text-align: left;">
                    <p><strong>Item name:</strong> <?php echo $item_name; ?></p>
                    <p><strong>Price:</strong> <?php echo $price; ?></p>
                    <p><strong>Buyer name:</strong> <?php echo $buyer_name; ?></p>
                    <p><strong>Seller name:</strong> <?php echo $seller_name; ?></p>
                    <p><strong>Seller rating:</strong> <?php echo $seller_rating; ?></p>
                    <p><strong>Request type:</strong> <?php echo $type; ?></p>
                </div>
                <div>
                    <img src="image/<?php echo htmlspecialchars($image); ?>" alt="Item Image" style="width: 160px; height: 200px; border-radius: 8px;">
                </div>
            </div>

                <label for="description">Request description:</label>
                <textarea id="description" class="form-control" 
                        style="background-color: white; width: 100%; height: 100px; resize: none;" 
                        readonly><?php echo htmlspecialchars($description); ?></textarea>
                <br>

                <input type='hidden' name='request_id' value="<?php echo $request_id; ?>">
                <input type="submit" name="accept_btn" value="Accept request" class="btn btn-success">
                <input type="submit" name="reject_btn" value="Reject request" class="btn btn-danger">
            </form>
        </div>
    </body>
</html>