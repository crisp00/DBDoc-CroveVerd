<?php
require_once("../dbdoc/dbdoc.php");

$sp_config = new DbDocConfig();
$sp_config->db_host = "localhost";
$sp_config->db_user = "root";
$sp_config->db_pass = "";
$sp_config->db_database = "dbdoc_cv";
$sp_config->db_prefix = "cv";
$sp_config->sess_id = "cv_";

$sp_init = new DbDocDatabaseInitializer($sp_config);
$sp_init->init();

?>