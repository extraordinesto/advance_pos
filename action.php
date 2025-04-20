<?php
session_start();
include 'inventory.php';
$inventory = new Inventory();
if(!empty($_GET['action']) && $_GET['action'] == 'logout') {
	session_unset();
	session_destroy();
	header("Location:login.php");
}