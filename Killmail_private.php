<link rel="stylesheet" type="text/css" href="mainstyle.css"> 
<body onload="javascript:LoadAllKillmails()">
<a href="./mainpage.php" class="mainbutton">Main</a>      
<a href="./Killmail_private.php" class="myloss">SRP 신청하기</a><br><br><br>
<?php

include $_SERVER['DOCUMENT_ROOT']."/CorpESI/shrimp/phplib.php";

dbset();
logincheck();
session_start();


$maintoon=getMainToon($_SESSION['shrimp_userid']);
$mytoons=array();
$toonresult=$dbcon->query("select * from Shrimp_accounts where maintoon=".$maintoon." and active>=1;");

for($i=0;$i<$toonresult->num_rows;$i++){
    $toondata=$toonresult->fetch_array();
    $mytoons[$i]=$toondata["userid"];
}



echo("<a class=\"updatekillmail\" href=\"javascript:updatekillmailpopup(".$_SESSION['shrimp_userid'].");\"> UPDATE KILLMAIL </a><br><br>\n");
$KILLMAIL_PER_PAGE=8;

if(!isset($_GET["page"])){
    $_GET["page"]=1;
}



$qr="select count(*) from Shrimp_killmails where ";

for($i=0;$i<sizeof($mytoons);$i++){
    $qr.="character_id=".$mytoons[$i]." ";

    if($i<(sizeof($mytoons)-1)){
        $qr.="or ";
    }
}
$qr.=";";
$result=$dbcon->query($qr)->fetch_row();
$kmn=$result[0];

$pln_start=max(1,$_GET["page"]-5);
$pln_end=min(ceil(max($kmn-1,0)/$KILLMAIL_PER_PAGE),$_GET["page"]+5);

for($i=$pln_start;$i<=$pln_end;$i++){
    if($i!=$_GET["page"]){
        echo("<a class='pagen' href='./Killmail_private.php?page=".$i."'>".$i."</a> ");
    }
    else{
        echo("<b class='pagen'>".$i."</b>");
    }
}

?>

<?php



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
        echo("<td rowspan=2 align=center><span class=\"submitSRP\" onclick=\"javascript:submitSRP(".$kmdata[$i]["killmail_id"].");\">Submit SRP</span></td>\n");
    }
    else{
        echo("<td rowspan=2 align=center><span class=\"srpcharge\">".number_format($kmdata[$i]["charge"])." ISK<br>".($kmdata[$i]["SRP_status"]==1?"<span class=\"SRP_pending\">Pending</span>":($kmdata[$i]["SRP_status"]==2?"<span class=\"SRP_accepted\">Accepted</span>":"<span class=\"SRP_rejected\">Rejected</span>"))."</span></td>\n");
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
    echo("var killmails=new Array();\n");
    echo("killmails[0]=".$kmdata[$i]["killmail_id"].";\n");

}

echo("</script>\n");


?>
</body>

<script>

var load_table=new Array();
if(killmails===undefined){
    var killmails=new Array();
}
for(var i=0;i<killmails.length;i++){
    load_table[i]=new Array();
    load_table[i]["ship"]=0;
    load_table[i]["name"]=0;
    load_table[i]["loca"]=0;
    load_table[i]["locasecu"]=0;
}



function LoadAllKillmails(){
    var kmdata=new Array();
    for(var i=0;i<killmails.length;i++){
        kmdata[i]=LoadKillmail(killmails[i]);
        document.getElementById("icon"+killmails[i]).src="https://images.evetech.net/types/"+kmdata[i].victim.ship_type_id+"/icon";
        document.getElementById("ship"+killmails[i]).innerHTML=getName("item",kmdata[i].victim.ship_type_id);
        document.getElementById("port"+killmails[i]).src="https://images.evetech.net/characters/"+kmdata[i].character_id+"/portrait?size=64";
        document.getElementById("name"+killmails[i]).innerHTML=getName("character",kmdata[i].character_id);
        document.getElementById("loca"+killmails[i]).innerHTML=getName("system",kmdata[i].solar_system_id);
        document.getElementById("date"+killmails[i]).innerHTML=kmdata[i].killmail_time;
        
        var systeminfo=getInfo("system",kmdata[i].solar_system_id);
        document.getElementById("locasecu"+killmails[i]).innerHTML=parseFloat(systeminfo.security_status).toFixed(1);
        
        var secred=Math.round(255*(1-systeminfo.security_status));
        var secgreen=Math.round(250*(systeminfo.security_status));
        var secblue=Math.round(100*(systeminfo.security_status-0.5));
        //alert(secred+" "+secgreen+" "+secblue);//DEBUG
        secred=secred>255?255:secred;
        secgreen=secgreen<1?1:secgreen;
        secblue=secblue<0?0:secblue;
        var hexcolor=Number((secred*256*256)+(secgreen*256)+secblue).toString(16);
        hexcolor=hexcolor.length==5?"#0"+hexcolor:"#"+hexcolor;

        document.getElementById("loca"+killmails[i]).style.color=hexcolor;
        document.getElementById("locasecu"+killmails[i]).style.color=hexcolor;
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
function updatekillmailpopup(killmailuserid){

    updatekillmailwindow=window.open("./updatekillmail.php?userid="+killmailuserid,"popUpWindow","height=500,width=600");
}


</script>