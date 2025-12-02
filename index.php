<?php
session_start();
require 'config.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
?>