
<?php

include $_SERVER['DOCUMENT_ROOT']."/CorpESI/shrimp/phplib.php";
$tset1=microtime(true);
dbset();
$tset2=microtime(true);
logincheck();
$tset3=microtime(true);
//header("Content-Type: application/json");

errordebug($tset2-$tset1);
errordebug($tset3-$tset2);
if(!isset($_GET["character_id"])){
    $_GET["character_id"]=getMainToon($_SESSION["shrimp_userid"]);
}

$qr= "select * from Shrimp_killmails_emoji where killmail_id=".$_GET["killmail_id"]." and emoji=".$_GET["emojinum"]." and character_id=".$_GET["character_id"].";";
$result=$dbcon->query($qr);

echo($qr."<br>\n");
if($result->num_rows>0){
    $qrd= "delete from Shrimp_killmails_emoji where killmail_id=".$_GET["killmail_id"]." and emoji=".$_GET["emojinum"]." and character_id=".$_GET["character_id"].";";
    $dbcon->query($qrd);
    echo($qrd."<br>\n");
}
else{
    $qri="insert into Shrimp_killmails_emoji (killmail_id,emoji,character_id) values (".$_GET["killmail_id"].",".$_GET["emojinum"].",".$_GET["character_id"].");";
    $dbcon->query($qri);
    echo($qri."<br>\n");
}
?>