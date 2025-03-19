<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background-color: rgb(244, 255, 183);
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
        .form-container input,
        .form-container button {
            width: 100%;
            margin-bottom: 10px;
        }
        .form-container h2 {
            margin-bottom: 15px;
        }
        .welcome-balance-container {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .buttons-container {
            display: flex;
            gap: 10px;
            margin-left: auto;
        }
    </style>
    <title>Fill your lot form</title>
</head>
<body>
    <div class="top-bar">
        <div class="top-left">
                <div>
                    Welcome, <span id="username">Loading...</span>
                    <br />
                    Balance: $<span id="user-balance">Loading...</span>
                </div>
                <div class="buttons-container">
                    <a href="../pages/home.php" class="btn btn-light">Home</a>
                    <a href="../pages/user_info.php?type=buy" class="btn btn-light">User Info</a>
                    <a href="../pages/new_lot.php" class="btn btn-light">Post Your Item</a>
                </div>
        </div>
        <button onclick="logout()" class="btn btn-light logout-btn">Log Out</button>
    </div>
    <div class="form-container">
        <form id="create-lot-form" enctype="multipart/form-data">
            <input
                type="text"
                name="item_name"
                class="form-control"
                placeholder="Enter the item name"
                required
            /><br />
            <input
                type="number"
                step="0.01"
                min="0.01"
                name="price"
                class="form-control"
                placeholder="Enter the price"
                required
            /><br />
            <input
                type="file"
                name="item_image"
                accept="image/png, image/jpeg, image/jpg"
                required
            /><br />
            <input type="submit" value="Create lot" class="btn btn-success">
        </form>
        <div id="message"></div>
    </div>

    <script>
        const token = localStorage.getItem('token');

        if (!token) {
            window.location.href = "/login/pages/login_page.php";
        }

        async function fetchUserData() {
            try {
                const response = await fetch('/login/api/users/info/', {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                if (!response.ok) {
                    throw new Error("Failed to fetch user data");
                }

                const data = await response.json();
                document.getElementById('username').textContent = data.username;
                document.getElementById('user-balance').textContent = data.balance;
            } catch (error) {
                alert("Failed to fetch user data. Please log in again.");
                window.location.href = "/login/pages/login_page.php";
            }
        }

        fetchUserData();

        document.getElementById('create-lot-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);
            const token = localStorage.getItem('token');

            try {
                const response = await fetch('/login/api/lots/create/', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    body: formData
                });

                const result = await response.json();

                if (response.ok) {
                    document.getElementById('message').innerHTML = `<p style="color:green;">Item uploaded successfully!</p>`;
                } else {
                    document.getElementById('message').innerHTML = `<p style="color:red;">Error: ${result.message || result.error}</p>`;
                }
            } catch (error) {
                document.getElementById('message').innerHTML = `<p style="color:red;">Error: ${error.message}</p>`;
            }
        });

        function logout() {
            localStorage.removeItem('token');
            window.location.href = "/login/pages/login_page.php";
        }
    </script>
</body>
</html>