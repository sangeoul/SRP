<img src="./images/loading.gif" id="loading">
<div id="resulttext">
</div>

<?php
include $_SERVER['DOCUMENT_ROOT']."/CorpESI/shrimp/phplib.php";
dbset();
session_start();
$toonresult=$dbcon->query("select * from Shrimp_accounts where maintoon=".getMainToon($_GET["userid"])." and active>=1;");
$toondata=array();
for($i=0;$i<$toonresult->num_rows ;$i++){
    $toondata[$i]=$toonresult->fetch_array();
    refresh_token($toondata[$i]["userid"]);

}
?>

<script>

var resulttextarea=document.getElementById("resulttext");
var cn=new Array();
var en=new Array();
var un=0;
var tn=<?=$toonresult->num_rows?>;

function UpdateCharacter(userid,username,access_token){
    
    var ESIdata=new XMLHttpRequest();
    ESIdata.onreadystatechange=function(){
        
        if (this.readyState == XMLHttpRequest.DONE && this.status==200){

            obj_response=JSON.parse(this.responseText);
            if(obj_response["error"]===undefined){
                cn[username]=0;
                en[username]=obj_response.length;
                for(var i=0;i<obj_response.length;i++){
                    UpdateKillMail(obj_response[i]["killmail_id"],obj_response[i]["killmail_hash"],username);
                }
                
                
            }
            else{
                resulttextarea.innerHTML+=obj_response["error"];
                un++;
                resulttextarea.innerHTML+=username+"의 킬메일 정보 갱신에 실패하였습니다. - <br>원인 : "+obj_response["error"]+"<br><br>\n";
            }

        }
        else if(this.readyState == XMLHttpRequest.DONE && this.status!=200){
                un++;
                resulttextarea.innerHTML+=username+"의 킬메일 정보 갱신에 실패하였습니다.<br><br>\n";
        }

    }

    ESIdata.open("GET","https://esi.evetech.net/latest/characters/"+userid+"/killmails/recent/?datasource=tranquility&page=1",true);
    ESIdata.setRequestHeader("Content-Type", "application/json");
    ESIdata.setRequestHeader("Authorization","Bearer "+access_token);
    ESIdata.send();
}
function UpdateKillMail(kmid,hash,username){

    var ESIdata=new XMLHttpRequest();
    ESIdata.onreadystatechange=function(){

        if (this.readyState == XMLHttpRequest.DONE && this.status==200){
            cn[username]++;
            
            if(cn[username]==en[username]){
                un++;
                resulttextarea.innerHTML+=username+"의 킬메일 정보가 갱신되었습니다.("+un+"/"+tn+")<br><br>\n";
                document.getElementById("loading").style.display="none";
                
            }
        }   
    }

    ESIdata.open("POST","./loadkillmail.php",true);
	ESIdata.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    ESIdata.send("id="+kmid+"&hash="+hash);
}


</script>

<?php

for($i=0;$i<$toonresult->num_rows ;$i++){

    echo("<script>\n");
    echo("UpdateCharacter(".$toondata[$i]["userid"].",\"".$toondata[$i]["username"]."\",\"".$toondata[$i]["access_token"]."\");\n");
    echo("</script>");
}
?>