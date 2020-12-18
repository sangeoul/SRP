<?php

include $_SERVER['DOCUMENT_ROOT']."/CorpESI/shrimp/phplib.php";
session_start();
dbset();
logincheck();

$mymaintoon=getMainToon($_SESSION["shrimp_userid"]);

$result=$dbcon->query("select character_id from Shrimp_killmails where SRP_status=0 and killmail_id=".$_GET["killmail_id"].";");

if($result->num_rows==0){
    errorclose("잘못된 접근입니다.1");
}
else if(!isset($_GET["charge"])) {
    echo("<form method=\"get\">\n");
    echo(" 금액 : ");
    echo("<input type=number step=1 style=\"width:180;\" id=\"charge\" name=\"charge\"> ISK<br>\n");
    echo("<input type=hidden id=\"killmail_id\" name=\"killmail_id\" value=".$_GET["killmail_id"].">\n");
    echo("<textarea id=\"memo\" name=\"memo\" cols=50 rows=6>메모 및 하고 싶은 말</textarea><br>\n");
    echo("<input type=submit value=\"신청하기\"><br>\n");
    echo("</form>\n");

}

else{
    $dbcon->query("update Shrimp_killmails set SRP_status=1, submitdate=UTC_TIMESTAMP , charge=".$_GET["charge"]." , memo=\"".$_GET["memo"]."\" where killmail_id=".$_GET["killmail_id"].";");

    errordebug("신청 완료되었습니다.");
    echo("<script>window.close();</script>");

}


?>