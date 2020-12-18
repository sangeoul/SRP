<?php

include $_SERVER['DOCUMENT_ROOT']."/CorpESI/shrimp/phplib.php";

dbset();
session_start();
logincheck();
if(isset($_GET["killmail_id"]) && in_array($_SESSION["shrimp_userid"],$SRP_ADMIN_ID)) {

    if($_GET["decision"]==1){
        $dbcon->query("update Shrimp_killmails set SRP_status=2 where killmail_id=".$_GET["killmail_id"]." and SRP_status=1;");
    }
    else if($_GET["decision"]==0){
        $dbcon->query("update Shrimp_killmails set SRP_status=3 where killmail_id=".$_GET["killmail_id"]." and SRP_status=1;");
    }
}
else{
    echo("잘못된 접근입니다.");
}
?>