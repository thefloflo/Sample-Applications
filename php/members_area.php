<?php
    DB_USER = 'root';
    DB_PASSWORD = 'password';
    DB_HOST = 'localhost';
    DB_NAME = "clef_users";

    // don't let those filthy nonmembers in here
    if(!isset($_SESSION["user_id"])) {
        header("Location: http://localhost:8888");
    } 

    $mysql = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD);
    $response = mysqli_query("SELECT logged_out_at FROM {DB_NAME}.users WHERE id='{$_SESSION['user_id']}'");
    $rows = mysqli_fetch_assoc($resource);

    $logged_out_at = $rows['logged_out_at'];

    if(!isset($_SESSION['logged_in_at']) || $_SESSION['logged_in_at'] < $logged_out_at) { // or if the user is logged out with Clef
        session_destroy(); // log the user out on this site

        header("Location: http://localhost:8888");
    }
?>

<!-- =======================================================-->
<!DOCTYPE html>
<html>
<head>
<title>PHP Sample</title>
</head>
<body>
    <div class='user-info'>
        <h3>Clef ID: <?=$_SESSION["user_id"]?></h3>
        <h3>Name: <?=$_SESSION['name']?></h3>
        <h3>Email: <?=$_SESSION['email']?></h3>
    </div>
</body>
</html>

