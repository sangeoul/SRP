<?php
include $_SERVER['DOCUMENT_ROOT']."/CorpESI/shrimp/phplib.php";

dbset();
//logincheck();

//header("Content-Type: application/json");

$qr="select * from Shrimp_killmails where killmail_id=".$_GET["id"].";";

$result=$dbcon->query($qr);

if($result->num_rows==0 && isset($_GET["hash"])) {


    $killmail_curl= curl_init();
    $header_type= "Content-Type:application/json";
    
    curl_setopt($killmail_curl,CURLOPT_URL,"https://esi.evetech.net/latest/killmails/".$_GET["id"]."/".$_GET["hash"]."/?datasource=tranquility");
    
    curl_setopt($killmail_curl,CURLOPT_HTTPHEADER,array($header_type,"Authorization: Bearer ".$_SESSION["shrimp_access_token"]));
    curl_setopt($itemcurl,CURLOPT_HTTPGET,true);
    curl_setopt($killmail_curl,CURLOPT_RETURNTRANSFER,true);
    
    
    $curl_response=curl_exec($killmail_curl);
    $obj_response=json_decode($curl_response,true);
    echo($curl_response."<br><br>\n");
    var_dump($obj_response);
    echo("<br><br>\n");
    //DEBUG echo($curl_response."\n");
    
    $obj_response["killmail_time"]=str_replace("T"," ",$obj_response["killmail_time"]);
    $obj_response["killmail_time"]=str_replace("Z"," ",$obj_response["killmail_time"]);
    if(!isset($obj_response["moon_id"])){
        $obj_response["moon_id"]=0;
    }
    if(!isset($obj_response["war_id"])){
        $obj_response["war_id"]=0;
    }
    for($i=0;$i<sizeof($obj_response["attackers"]);$i++){
        if(!isset($obj_response["attackers"][$i]["character_id"])){
            $obj_response["attackers"][$i]["character_id"]=0;
        }
        if(!isset($obj_response["attackers"][$i]["corporation_id"])){
            $obj_response["attackers"][$i]["corporation_id"]=0;
        }
        if(!isset($obj_response["attackers"][$i]["alliance_id"])){
            $obj_response["attackers"][$i]["alliance_id"]=0;
        }
        if(!isset($obj_response["attackers"][$i]["faction_id"])){
            $obj_response["attackers"][$i]["faction_id"]=0;
        }
        if($obj_response["attackers"][$i]["final_blow"]){
            $obj_response["attackers"][$i]["final_blow"]=1;
        }
        else{
            $obj_response["attackers"][$i]["final_blow"]=0;
        }
        if(!isset($obj_response["attackers"][$i]["ship_type_id"])){
            $obj_response["attackers"][$i]["ship_type_id"]=0;
        }
        if(!isset($obj_response["attackers"][$i]["weapon_type_id"])){
            $obj_response["attackers"][$i]["weapon_type_id"]=0;
        }        

    }
    if(!isset($obj_response["victim"]["character_id"])){
        $obj_response["victim"]["character_id"]=0;
    }
    if(!isset($obj_response["victim"]["corporation_id"])){
        $obj_response["victim"]["corporation_id"]=0;
    }
    if(!isset($obj_response["victim"]["alliance_id"])){
        $obj_response["victim"]["alliance_id"]=0;
    }
    if(!isset($obj_response["victim"]["faction_id"])){
        $obj_response["victim"]["faction_id"]=0;
    }
    if(!isset($obj_response["victim"]["ship_type_id"])){
        $obj_response["victim"]["ship_type_id"]=0;
    }
    if(!isset($obj_response["victim"]["items"])){
        $obj_response["victim"]["items"]=array();
    }
    for($i=0;$i<sizeof($obj_response["victim"]["items"]);$i++){
        if(!isset($obj_response["victim"]["items"][$i]["items"])){
            $obj_response["victim"]["items"][$i]["items"]=array();
        }
        for($j=0;$j<sizeof($obj_response["victim"]["items"][$i]["items"]);$j++){
            if(!isset($obj_response["victim"]["items"][$i]["items"][$j]["quantity_destroyed"])){
                $obj_response["victim"]["items"][$i]["items"][$j]["quantity_destroyed"]=0;
            }
            if(!isset($obj_response["victim"]["items"][$i]["items"][$j]["quantity_dropped"])){
                $obj_response["victim"]["items"][$i]["items"][$j]["quantity_dropped"]=0;
            }   
        }
        if(!isset($obj_response["victim"]["items"][$i]["quantity_destroyed"])){
            $obj_response["victim"]["items"][$i]["quantity_destroyed"]=0;
        }
        if(!isset($obj_response["victim"]["items"][$i]["quantity_dropped"])){
            $obj_response["victim"]["items"][$i]["quantity_dropped"]=0;
        } 
    }
    if(!isset($obj_response["war_id"])){
        $obj_response["war_id"]=0;
    }

    //킬메일 기본값 저장       
    $qr1="insert into Shrimp_killmails (killmail_hash,killmail_id,killmail_time,moon_id,solar_system_id,war_id,character_id) values(\"".$_GET["hash"]."\",".$_GET["id"].",\"".$obj_response["killmail_time"]."\",".$obj_response["moon_id"].",".$obj_response["solar_system_id"].",".$obj_response["war_id"].",".$obj_response["victim"]["character_id"].");";

    $dbcon->query($qr1);


    //victim 정보저장
    $qrv="insert into Shrimp_killmails_victims (killmail_id,character_id,corporation_id,alliance_id,faction_id,damage_taken,position_x,position_y,position_z,ship_type_id) values (".$_GET["id"].",".$obj_response["victim"]["character_id"].",".$obj_response["victim"]["corporation_id"].",".$obj_response["victim"]["alliance_id"].",".$obj_response["victim"]["faction_id"].",".$obj_response["victim"]["damage_taken"].",".$obj_response["victim"]["position"]["x"].",".$obj_response["victim"]["position"]["y"].",".$obj_response["victim"]["position"]["z"].",".$obj_response["victim"]["ship_type_id"].");";

    $dbcon->query($qrv);
    //공격자 저장
    for($i=0;$i<sizeof($obj_response["attackers"]);$i++){
        $qra="insert into Shrimp_killmails_attackers (killmail_id,character_id,corporation_id,alliance_id,faction_id,damage_done,final_blow,security_status,ship_type_id,weapon_type_id) values (".$_GET["id"].",".$obj_response["attackers"][$i]["character_id"].",".$obj_response["attackers"][$i]["corporation_id"].",".$obj_response["attackers"][$i]["alliance_id"].",".$obj_response["attackers"][$i]["faction_id"].",".$obj_response["attackers"][$i]["damage_done"].",".$obj_response["attackers"][$i]["final_blow"].",".$obj_response["attackers"][$i]["security_status"].",".$obj_response["attackers"][$i]["ship_type_id"].",".$obj_response["attackers"][$i]["weapon_type_id"].");";
        $dbcon->query($qra);

    }

    //아이템 저장.
    for($i=0;$i<sizeof($obj_response["victim"]["items"]);$i++){

        //아이템이 다른 아이템을 포함하고 있지 않으면 is_contained=0 으로 저장
        if(sizeof($obj_response["victim"]["items"][$i]["items"])==0){
            $qri="insert into Shrimp_killmails_items (killmail_id,flag,item_type_id,is_contained,quantity_destroyed,quantity_dropped,singleton) values (".$_GET["id"].",".$obj_response["victim"]["items"][$i]["flag"].",".$obj_response["victim"]["items"][$i]["item_type_id"].",0,".$obj_response["victim"]["items"][$i]["quantity_destroyed"].",".$obj_response["victim"]["items"][$i]["quantity_dropped"].",".$obj_response["victim"]["items"][$i]["singleton"].");";
            $dbcon->query($qri);
        }
        //아이템이 다른 아이템을 포함하고 있으면 is_contained=-1 로 저장되며, 포함되어있는 아이템은 상위 아이템의 indexx 값을 is_contained에 갖는다.
        else{
            $qri="insert into Shrimp_killmails_items (killmail_id,flag,item_type_id,is_contained,quantity_destroyed,quantity_dropped,singleton) values (".$_GET["id"].",".$obj_response["victim"]["items"][$i]["flag"].",".$obj_response["victim"]["items"][$i]["item_type_id"].",-1,".$obj_response["victim"]["items"][$i]["quantity_destroyed"].",".$obj_response["victim"]["items"][$i]["quantity_dropped"].",".$obj_response["victim"]["items"][$i]["singleton"].");";

            $dbcon->query($qri);
            $qrc="select indexx from Shrimp_killmails_items where killmail_id=".$_GET["id"]." and item_type_id=".$obj_response["victim"]["items"][$i]["item_type_id"]." and is_contained=-1 order by indexx desc limit 1";
            $cont_result=$dbcon->query($qrc)->fetch_array();
            $contained_index=$cont_result[0];

            //debug 5555;
            //$contained_index=5555;
            for($j=0;$j<sizeof($obj_response["victim"]["items"][$i]["items"]);$j++){

                $qri=="insert into Shrimp_killmails_items (killmail_id,flag,item_type_id,is_contained,quantity_destroyed,quantity_dropped,singleton) values (".$_GET["id"].",".$obj_response["victim"]["items"][$i]["items"][$j]["flag"].",".$obj_response["victim"]["items"][$i]["items"][$j]["item_type_id"].",".$contained_index.",".$obj_response["victim"]["items"][$i]["items"][$j]["quantity_destroyed"].",".$obj_response["victim"]["items"][$i]["items"][$j]["quantity_dropped"].",".$obj_response["victim"]["items"][$i]["items"][$j]["singleton"].");";
                $dbcon->query($qri);
            }
        }
    }


    $qr="select * from Shrimp_killmails where killmail_id=".$_GET["id"].";";

    $result=$dbcon->query($qr);
}


$kmdata=$result->fetch_array();


$attackers_result=$dbcon->query("select * from Shrimp_killmails_attackers where killmail_id=".$kmdata["killmail_id"].";");
$victim_result=$dbcon->query("select * from Shrimp_killmails_victims where killmail_id=".$kmdata["killmail_id"].";");
$items_result=$dbcon->query("select * from Shrimp_killmails_items where killmail_id=".$kmdata["killmail_id"]." and is_contained=0;");
$containing_items_result=$dbcon->query("select * from Shrimp_killmails_items where killmail_id=".$kmdata["killmail_id"]." and is_contained=-1;");

$kmdata_victim=$victim_result->fetch_array();

echo("{\n");
echo("\"killmail_id\":".$kmdata["killmail_id"]." ,\n");
echo("\"killmail_hash\":\"".$kmdata["killmail_hash"]."\" ,\n");
echo("\"killmail_time\":\"".$kmdata["killmail_time"]."\" ,\n");
echo("\"solar_system_id\":".$kmdata["solar_system_id"]." ,\n");
echo("\"character_id\":".$kmdata["character_id"]." ,\n");

echo("\"victim\":{\n");
echo("\"corporation_id\":".$kmdata_victim["corporation_id"]." ,\n");
echo("\"alliance_id\":".$kmdata_victim["alliance_id"]." ,\n");
echo("\"damage_taken\":".$kmdata_victim["damage_taken"]." ,\n");
echo("\"ship_type_id\":".$kmdata_victim["ship_type_id"]."\n");
echo("}\n");

echo("}\n");


    

?>