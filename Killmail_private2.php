<style>
table{

border-collapse:collapse;
}
td{

border-collapse:collapse;
border:solid 1px black;

}
span.loca,locasecu{

    font-weight:bold;
}
span.zklink{

    background-color:black;
    color:white;
    padding:5px;
    padding-top:8px;
    padding-bottom:8px;
    
}
a.updatekillmail{
    font-size:20px;
    background-color:rgba(0,0,160,1);
    color:white;
    padding:4px;
    text-decoration:none;
}

a.updatekillmail:hover{
    background-color:rgba(0,0,240,1);  
}
</style>

<body onload="javascript:LoadAllKillmails()">
<a href="./mainpage.php">메인 페이지로</a><br>
<a href="./Killmail_private.php">내 로스 보기</a><br>
<?php

include $_SERVER['DOCUMENT_ROOT']."/CorpESI/shrimp/phplib.php";

dbset();
logincheck();
session_start();

echo("<a class=\"updatekillmail\" href=\"./updatekillmail.php?userid=".$_SESSION['shrimp_userid']."\" target=\"_blank\"> UPDATE KILLMAIL </a><br><br>\n");
$KILLMAIL_PER_PAGE=20;

if(!isset($_GET["page"])){
    $_GET["page"]=1;
}



$maintoon=getMainToon($_SESSION['shrimp_userid']);
$mytoons=array();
$toonresult=$dbcon->query("select * from Shrimp_accounts where maintoon=".$maintoon." and active>=1;");

for($i=0;$i<$toonresult->num_rows;$i++){
    $toondata=$toonresult->fetch_array();
    $mytoons[$i]=$toondata["userid"];
}


$qr="select * from Shrimp_killmails where ";

for($i=0;$i<sizeof($mytoons);$i++){
    $qr.="character_id=".$mytoons[$i]." ";

    if($i<(sizeof($mytoons)-1)){
        $qr.="or ";
    }
}
$qr.="order by killmail_time desc limit ".(($_GET["page"]-1)*$KILLMAIL_PER_PAGE)." ,".$KILLMAIL_PER_PAGE.";";
$result=$dbcon->query($qr);

$kmdata=array();
echo("<table>\n");
for($i=0;$i<$result->num_rows;$i++){
    $kmdata[$i]=$result->fetch_array();
    echo("<tr>\n");
    echo("<td rowspan=2><img src=\"./images/loading.gif\" id=\"icon".$kmdata[$i]["killmail_id"]."\" style=\"width:64px;height:64px;\"></td>\n");
    echo("<td><span id=\"ship".$kmdata[$i]["killmail_id"]."\">Ship</span></td>\n");
    echo("<td rowspan=2><img src=\"./images/loading.gif\" id=\"port".$kmdata[$i]["killmail_id"]."\" style=\"width:64px;height:64px;\"></td>\n");
    echo("<td><span id=\"name".$kmdata[$i]["killmail_id"]."\">Character</span></td>\n");
    if($kmdata[$i]["SRP_status"]==0){
        echo("<td rowspan=2><span class=\"submitSRP\" onclick=\"javascript:submitSRP(".$kmdata[$i]["killmail_id"].");\">Submit SRP</span></td>\n");
    }
    else{
        echo("<td rowspan=2><span class=\"srpcharge\">".number_format($kmdata[$i]["charge"])." ISK</span></td>\n");
    }
    echo("<td rowspan=2><span class=\"zklink\" onclick=\"window.open('https://zkillboard.com/kill/".$kmdata[$i]["killmail_id"]."/','_blank');\"><img src=./images/wreck.png>zKill</span></td>");
    echo("</tr>\n<tr>\n");
    echo("<td><span id=\"loca".$kmdata[$i]["killmail_id"]."\" style=\"loca\">Location</span> <span id=\"locasecu".$kmdata[$i]["killmail_id"]."\" style=\"locasecu\"></span></td>\n");
    echo("<td><span id=\"date".$kmdata[$i]["killmail_id"]."\">Date</span></td>\n");
    echo("</tr>\n");
}
echo("</table>\n");

echo("<script>\n");
if(sizeof($kmdata)>1){
echo("var killmails=new Array(");

    for($i=0;$i<sizeof($kmdata);$i++){
        echo($kmdata[$i]["killmail_id"]);
        if($i<sizeof($kmdata)-1){
            echo(",");
        }
    }
    echo(");\n");
}
else{
    echo("var killmails=new Array()\n");
    echo("killmails[0]=".$kmdata[$i]["killmail_id"].";\n");

}

echo("</script>\n");


?>
</body>

<script>

var submitwindow;

var load_table=new Array(killmails.length);

for(var i=0;i<killmails.length;i++){
    load_table[i]= new Array();
    load_table[i]["ship"]=0;
    load_table[i]["name"]=0;
    load_table[i]["loca"]=0;
    load_table[i]["locasecu"]=0;
    
    
}



function LoadAllKillmails(){
    var kmdata=new Array(killmails.length);
    for(var i=0;i<killmails.length;i++){
        kmdata[i]=LoadKillmail(killmails[i]);
        document.getElementById("icon"+killmails[i]).src="https://images.evetech.net/types/"+kmdata[i].victim.ship_type_id+"/icon";
        setLoadShipName(kmdata[i].victim.ship_type_id,i);
        document.getElementById("port"+killmails[i]).src="https://images.evetech.net/characters/"+kmdata[i].character_id+"/portrait?size=64";
        
        setLoadUserName(kmdata[i].character_id,i);
        
        setLoadLocaName(kmdata[i].solar_system_id,i);
        setLoadLocaSecu(kmdata[i].solar_system_id.i);
        document.getElementById("date"+killmails[i]).innerHTML=kmdata[i].killmail_time;


    }
}

function LoadKillmail(killmail_id){
    var ESIdata=new XMLHttpRequest();
    var kmdata;
    ESIdata.onreadystatechange=function(){

        if (this.readyState == XMLHttpRequest.DONE && this.status==200){
            
            //alert(this.responseText);

            kmdata=JSON.parse(this.responseText);

        }        
    }

    ESIdata.open("POST","./loadkillmail.php",false);
    ESIdata.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;");
    ESIdata.send("id="+killmail_id);

    return kmdata;

}
function submitSRP(killmail_id){
    
    //submitwindow.close();
    submitwindow=window.open("./submitSRP.php?killmail_id="+killmail_id,"popUpWindow","height=500,width=600");



}

function getName(type,typeid){
    var ESIdata=new XMLHttpRequest();
    var data;
    ESIdata.onreadystatechange=function(){

        if (this.readyState == XMLHttpRequest.DONE && this.status==200){
            
            //alert(this.responseText);
            
            data=JSON.parse(this.responseText);
            data=data.name;
            
        }

            
    }

    ESIdata.open("GET","../shrimp/getname.php?type="+type+"&id="+typeid,false);
    ESIdata.send();
    return data;
 

}

function getInfo(type,typeid){
    var ESIdata=new XMLHttpRequest();
    var data;
    ESIdata.onreadystatechange=function(){

        if (this.readyState == XMLHttpRequest.DONE && this.status==200){
            
            //alert(this.responseText);
            
            data=JSON.parse(this.responseText);
            
        }

            
    }

    ESIdata.open("GET","../shrimp/getinfo.php?type="+type+"&id="+typeid,false);
    ESIdata.send();
    return data;
 

}


function showload(){

    var complete=1;
    for(var i=0;i<killmails.length;i++){
        if(load_table[i]["finished"]==0){
            if(load_table[i]["ship"]!=0&&load_table[i]["name"]!=0&&load_table[i]["loca"]!=0&&load_table[i]["locasecu"]!=0){
                document.getElementById("ship"+killmails[i]).innerHTML=load_table[i]["ship"];
                document.getElementById("name"+killmails[i]).innerHTML=load_table[i]["name"];
                document.getElementById("loca"+killmails[i]).innerHTML=load_table[i]["loca"];
                document.getElementById("locasecu"+killmails[i]).innerHTML=parseFloat(load_table[i]["locasecu"]).toFixed(1);

                var secred=Math.round(255*(1-load_table[i]["locasecu"]));
                var secgreen=Math.round(250*(load_table[i]["locasecu"]));
                var secblue=Math.round(100*(load_table[i]["locasecu"]-0.5));

                secred=secred>255?255:secred;
                secgreen=secgreen<1?1:secgreen;
                secblue=secblue<0?0:secblue;
                var hexcolor=Number((secred*256*256)+(secgreen*256)+secblue).toString(16);
                hexcolor=hexcolor.length==5?"#0"+hexcolor:"#"+hexcolor;

                document.getElementById("loca"+killmails[i]).style.color=hexcolor;
                document.getElementById("locasecu"+killmails[i]).style.color=hexcolor;
                load_table[i]["finished"]=1;
            }
            else {
                complete=0;
            }

        }
    }

    if(complete==0){
        setTimeout(showload,100);
    }

}


function setLoadShipName(typeid,num){
    
    var ESIdata=new XMLHttpRequest();
    var data;
    ESIdata.onreadystatechange=function(){

        if (this.readyState == XMLHttpRequest.DONE && this.status==200){
            
            //alert(this.responseText);
            
            data=JSON.parse(this.responseText);
            load_table[num]["ship"]=data.name;
            
        }

            
    }

    ESIdata.open("GET","../shrimp/getname.php?type=item&id="+typeid,true);
    ESIdata.send();
    return data;
 

}






function setLoadUserName(typeid,num){
    
    var ESIdata=new XMLHttpRequest();
    var data;
    
    ESIdata.onreadystatechange=function(){

        if (this.readyState == XMLHttpRequest.DONE && this.status==200){
            
            //alert(this.responseText);
            data=JSON.parse(this.responseText);
            
            load_table[num]["name"]=data.name;
            
            
        }

            
    }
    
    ESIdata.open("GET","../shrimp/getname.php?type="+type+"&id="+typeid,true);
    ESIdata.send();
 

}
function setLoadLocaName(typeid,num){
    var ESIdata=new XMLHttpRequest();
    var data;
    ESIdata.onreadystatechange=function(){

        if (this.readyState == XMLHttpRequest.DONE && this.status==200){
            
            //alert(this.responseText);
            
            data=JSON.parse(this.responseText);
            
            load_table[num]["loca"]=data.name;
            
        }

            
    }

    ESIdata.open("GET","../shrimp/getname.php?type=character&id="+typeid,true);
    ESIdata.send();
 

}
function setLoadLocaSecu(typeid,num){
    var ESIdata=new XMLHttpRequest();
    var data;
    ESIdata.onreadystatechange=function(){

        if (this.readyState == XMLHttpRequest.DONE && this.status==200){
            
            //alert(this.responseText);
            
            data=JSON.parse(this.responseText);
            load_table[i]["locasecu"]=data.name;
            
        }

            
    }

    ESIdata.open("GET","../shrimp/getname.php?type="+type+"&id="+typeid,true);
    ESIdata.send();
 

}
function setLoadLocaSecu(typeid,num){
    var ESIdata=new XMLHttpRequest();
    var data;
    ESIdata.onreadystatechange=function(){

        if (this.readyState == XMLHttpRequest.DONE && this.status==200){
            
            data=JSON.parse(this.responseText);
            load_table[i]["locasecu"]=data.security_status;
            
        }

            
    }

    ESIdata.open("GET","../shrimp/getinfo.php?type=system&id="+typeid,true);
    ESIdata.send();

}
</script>