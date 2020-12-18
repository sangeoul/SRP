
<?php

include $_SERVER['DOCUMENT_ROOT']."/CorpESI/shrimp/phplib.php";
dbset();
logincheck(1,1);
header("Content-Type: application/json");

if(!isset($_GET["character_id"])){
    $_GET["character_id"]=getMainToon($_SESSION["shrimp_userid"]);
}
if(!isset($_GET["emojinum"])){
    $EMOJI_NUMBER=6;

    $qr="select emoji, count(*) as sum from Shrimp_killmails_emoji where killmail_id=".$_GET["killmail_id"]." group by emoji order by sum desc;";

    $emoji_list=$dbcon->query($qr);

    $myqr="select * from Shrimp_killmails_emoji where killmail_id=".$_GET["killmail_id"]." and character_id=".$_GET["character_id"];

    $myemoji_list=$dbcon->query($myqr);

    $jsonarray->emoji=array();

    $emoji_num=array();
    for($i=0;$i<$EMOJI_NUMBER;$i++){
        $emoji_num[$i]=0;
    }


    for($i=0;$i<$emoji_list->num_rows;$i++){
        $emojidata=$emoji_list->fetch_array();
        $jsonarray->emoji[$i]=array(intval($emojidata["emoji"]),intval($emojidata["sum"]));
        
        $emoji_num[intval($emojidata["emoji"])]=intval($emojidata["sum"]);

       

    }
 
    //$i는 emoji_list 의 갯수로부터 출발한다. 이모지가 없는것들을 0으로 채우는 작업.
    for($j=0;$i<$EMOJI_NUMBER;$i++,$j++){
        while($emoji_num[$j]>0 && $j<$EMOJI_NUMBER){
            $j++;
        }
        if($j<$EMOJI_NUMBER){
            $jsonarray->emoji[$i][0]=$j;
            $jsonarray->emoji[$i][1]=0;
        }
    }

    for($i=0;$i<$myemoji_list->num_rows;$i++){
        $emojidata=$myemoji_list->fetch_array();
        $jsonarray->myemoji[$i]=intval($emojidata["emoji"]);
    }
    if($myemoji_list->num_rows==0){
        $jsonarray->myemoji=[];
    }

    echo(json_encode($jsonarray));
}
else{
    $qr="select count(*) as sum from Shrimp_killmails_emoji where killmail_id=".$_GET["killmail_id"]." and emoji=".$_GET["emojinum"].";";
    $result=$dbcon->query($qr);
    if($result->num_rows==0){
        $count=0;
    }
    else{
        $data=$result->fetch_array();
        $count=$data[0];
    }
    $qr="select * from Shrimp_killmails_emoji where killmail_id=".$_GET["killmail_id"]." and emoji=".$_GET["emojinum"]." and character_id=".$_GET["character_id"].";";
    $result=$dbcon->query($qr);
    if($result->num_rows==0){
        $mycon=false;
    }
    else{
        $mycon=true;
    }
    $jsonarray->count=$count;
    $jsonarray->myemoji=$mycon;
    echo(json_encode($jsonarray));
}

?>