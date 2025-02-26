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

        <title>Login Page</title>
        <style>
            body{
                background-color: #1d2630;
            }
            .container{
                margin-top: 150px;
            }
            .login-frame {
                background: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                max-width: 350px;
                text-align: center;
            }
            .login-frame h2 {
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-md-offset-3" align="center">
                    <div class="login-frame">
                        <h2>Login</h2>
                        <form action="login.php" method="POST">
                            <input 
                                type="text" 
                                name="username" 
                                class="form-control" 
                                placeholder="Enter Username"
                            /><br />
                            <input 
                                type="password"
                                name="password" 
                                class="form-control" 
                                placeholder="Enter Password"
                            /><br />
                            <input type="submit" name="login_btn" value="Login" class="btn btn-success">
                            <input type="submit" name="sign_in_btn" value="Sign In" class="btn btn-success">
                        </form>
                        <?php
                        if (isset($_GET['error'])) {
                            echo "<p style='color:red;'>" ."Error: ". htmlspecialchars($_GET['error']) . "</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>