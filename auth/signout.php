<?php
session_start();
$_SESSION = [];
session_destroy();
header("Location: /ordering-system/index.php");
exit();
?>