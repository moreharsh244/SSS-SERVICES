<?php
include('conn.php');
if(!isset($_GET['username'])){
    header('location:index.php');
    exit;
}
$username = mysqli_real_escape_string($con, rawurldecode($_GET['username']));
$sql = "DELETE FROM user_login WHERE username='$username'";
mysqli_query($con, $sql);
header('location:index.php');
exit;
?>
