<?php
require_once("dbdoc/dbdoc.php");

$config = new DbDocConfig();
$config->db_host = "localhost";
$config->db_user = "root";
$config->db_pass = "";
$config->db_database = "dbdoc_cv";
$config->db_prefix = "cv";
$config->sess_prefix = "cv_";

$dbdoc = new DbDoc($config);
$page = "login";
if(isset($_POST["login"])){
    $user = $_POST["user"];
    $pass = $_POST["pass"];
    $dbdoc->login($user, $pass);
}else if(isset($_GET["logout"])){
    $dbdoc->logout();
}

if($user = $dbdoc->isLoggedIn()){
    $page = "user";
    $user = $user->user;
}
?>

<html>
    <head>
        <?php
            require("./templates/head.html");
        ?>
    </head>
    </body>
        <?php
            switch($page){
                case "login":
                    require("./templates/login.html");
                break;
                case "user":
                    require("./templates/user.php");
                break;
            }
        ?>
    </body>
</html>