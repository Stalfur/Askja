<?php
session_start();
require('includes/class.common.php');
require('includes/constants.php');


try {
    $dbh = new PDO('pgsql:host='.db_host.';dbname='.db_base.'; charset=utf8', db_user, db_pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}
?>