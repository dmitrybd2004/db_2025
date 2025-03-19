<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Login Page</title>
    <style>
        body {
            background-color: #1d2630;
        }
        .container {
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
                    <form id="loginForm">
                        <input 
                            type="text" 
                            name="username" 
                            class="form-control" 
                            placeholder="Enter Username"
                            required
                        /><br />
                        <input 
                            type="password"
                            name="password" 
                            class="form-control" 
                            placeholder="Enter Password"
                            required
                        /><br />
                        <button type="submit" name="login_btn" class="btn btn-success">Login</button>
                        <button type="button" id="signUpBtn" class="btn btn-primary">Sign Up</button>
                    </form>
                    <div id="errorMessage" style="color: red; margin-top: 10px;"></div>
                </div>
            </div>
        </div>
    </div>

    <script>

        document.getElementById('loginForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = {
                username: formData.get('username'),
                password: formData.get('password')
            };

            fetch('/login/api/users/login/', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || "An error occurred. Please try again.");
                    });
                }
                return response.json();
            })
            .then(result => {
                if (result.status === 'success') {
                    localStorage.setItem('token', result.data.token);
                    window.location.href = result.data.redirect;
                } else {
                    document.getElementById('errorMessage').textContent = result.message;
                }
            })
            .catch(error => {
                document.getElementById('errorMessage').textContent = error.message;
            });
        });


        document.getElementById('signUpBtn').addEventListener('click', function () {
            const formData = new FormData(document.getElementById('loginForm'));
            const data = {
                username: formData.get('username'),
                password: formData.get('password')
            };

            fetch('/login/api/users/sign_in/', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || "An error occurred. Please try again.");
                    });
                }
                return response.json();
            })
            .then(result => {
                if (result.status === 'success') {
                    return fetch('/login/api/users/login/', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });
                } else {
                    throw new Error(result.message);
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || "An error occurred. Please try again.");
                    });
                }
                return response.json();
            })
            .then(result => {
                if (result.status === 'success') {
                    localStorage.setItem('token', result.data.token);
                    window.location.href = result.data.redirect;
                } else {
                    document.getElementById('errorMessage').textContent = result.message;
                }
            })
            .catch(error => {
                document.getElementById('errorMessage').textContent = error.message;
            });
        });
    </script>
</body>
</html>