
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .notification-container {
            width: 300px;
            margin: auto;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .notification {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .notification:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="notification-container">
        <h2>Comment Notifications</h2>
        <div id="notifications"></div>
    </div>
    
    <script>
        function fetchNotifications() {
            fetch('fetch_notifications.php') // PHP script to fetch notifications
            .then(response => response.json())
            .then(data => {
                const notificationsDiv = document.getElementById('notifications');
                notificationsDiv.innerHTML = '';
                
                if (data.length === 0) {
                    notificationsDiv.innerHTML = '<p>No new notifications</p>';
                    return;
                }
                
                data.forEach(notification => {
                    const notifElement = document.createElement('div');
                    notifElement.classList.add('notification');
                    notifElement.innerHTML = `<strong>${notification.commenter}</strong> commented on your product: "${notification.comment}"`;
                    notificationsDiv.appendChild(notifElement);
                });
            })
            .catch(error => console.error('Error fetching notifications:', error));
        }
        
        setInterval(fetchNotifications, 5000); // Refresh notifications every 5 seconds
        fetchNotifications();
    </script>
</body>
</html>
