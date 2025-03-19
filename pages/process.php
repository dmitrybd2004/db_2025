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
        .form-container input, .form-container button {
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
    <script>
        document.addEventListener("DOMContentLoaded", async function () {
            const token = localStorage.getItem('token');

            if (!token) {
                alert("Token not found. Please log in again.");
                window.location.href = "/login/pages/login_page.php";
                return;
            }

            try {
                const userData = await fetchUserData(token);

                if (userData.data.role !== 'moderator') {
                    alert("Access denied. You are not a moderator.");
                    window.location.href = "/login/pages/login_page.php";
                    return;
                }

                document.querySelector('.top-left').innerHTML = `
                    Welcome, ${userData.data.username}
                    <a href="../pages/home_mod.php" class="btn btn-light">Home</a>
                `;

                const requestId = new URLSearchParams(window.location.search).get('request_id');
                if (!requestId) {
                    alert("Request ID is missing.");
                    window.location.href = "/login/pages/home_mod.php";
                    return;
                }

                const requestData = await fetchRequestDetails(token, requestId);
                renderRequestDetails(requestData);
            } catch (error) {
                alert("Failed to initialize page. Please try again.");
                window.location.href = "/login/pages/login_page.php";
            }
        });

        async function fetchUserData(token) {
            const response = await fetch('/login/api/role/', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });

            if (!response.ok) {
                throw new Error(`Failed to fetch user data: ${response.statusText}`);
            }

            const data = await response.json();
            return data;
        }

        async function fetchRequestDetails(token, requestId) {
            const response = await fetch(`/login/api/refunds/unprocessed/${requestId}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });

            if (!response.ok) {
                throw new Error(`Failed to fetch request details: ${response.statusText}`);
            }

            const data = await response.json();
            return data;
        }

        function renderRequestDetails(requestData) {
            const formContainer = document.querySelector('.form-container');
            formContainer.innerHTML = '';

            if (!requestData || !requestData.data) {
                formContainer.innerHTML = `<p>No request details available.</p>`;
                return;
            }

            const request = requestData.data;
            const type = request.report_type === 0 ? "Refund" : "Refund + report";

            formContainer.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                    <div style="text-align: left;">
                        <p><strong>Item name:</strong> ${request.item_name}</p>
                        <p><strong>Price:</strong> ${request.price}</p>
                        <p><strong>Buyer name:</strong> ${request.buyer_name}</p>
                        <p><strong>Seller name:</strong> ${request.seller_name}</p>
                        <p><strong>Seller rating:</strong> ${request.rating}</p>
                        <p><strong>Request type:</strong> ${type}</p>
                    </div>
                    <div>
                        <img src="../image/${request.image_name}" alt="Item Image" style="width: 160px; height: 200px; border-radius: 8px;">
                    </div>
                </div>

                <label for="description">Request description:</label>
                <textarea id="description" class="form-control" 
                        style="background-color: white; width: 100%; height: 100px; resize: none;" 
                        readonly>${request.description}</textarea>
                <br>

                <button onclick="handleAcceptRequest(${request.request_id})" class="btn btn-success">Accept request</button>
                <button onclick="handleRejectRequest(${request.request_id})" class="btn btn-danger">Reject request</button>
            `;
        }

        async function handleAcceptRequest(requestId) {
            const token = localStorage.getItem('token');
            if (!token) {
                alert("Token not found. Please log in again.");
                window.location.href = "/login/pages/login_page.php";
                return;
            }

            try {
                const response = await fetch('/login/api/refunds/accept/', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({ request_id: requestId })
                });

                if (!response.ok) {
                    throw new Error(`Failed to accept request: ${response.statusText}`);
                }

                const data = await response.json();
                alert(data.message);
                window.location.href = "/login/pages/home_mod.php";
            } catch (error) {
                alert("Failed to accept request. Please try again.");
            }
        }

        async function handleRejectRequest(requestId) {
            const token = localStorage.getItem('token');
            if (!token) {
                alert("Token not found. Please log in again.");
                window.location.href = "/login/pages/login_page.php";
                return;
            }

            try {
                const response = await fetch('/login/api/refunds/reject/', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({ request_id: requestId })
                });

                if (!response.ok) {
                    throw new Error(`Failed to reject request: ${response.statusText}`);
                }

                const data = await response.json();
                alert(data.message);
                window.location.href = "/login/pages/home_mod.php";
            } catch (error) {
                alert("Failed to reject request. Please try again.");
            }
        }

        function logout() {
            localStorage.removeItem('token');
            window.location.href = "/login/pages/login_page.php";
        }
    </script>
</head>
<body>
    <div class="top-bar">
        <div class="top-left">
        </div>
        <button onclick="logout()" class="btn btn-light logout-btn">Log Out</button>
    </div>
    
    <div class="form-container">
    </div>
</body>
</html>