<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (isset($_SESSION["clid"])) {
  header("location: ../company");
  exit();
}

if (isset($inc) and $inc != '') {
  if ($inc == 'clients_addedit')
    $ttl = 'Client / add edit';
  if ($inc == 'search')
    $ttl = 'Search';
} else {
  $inc = "admin_home";
  $ttl = 'Home';
}
include "../home.php";
