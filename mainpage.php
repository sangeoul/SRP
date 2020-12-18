<link rel="stylesheet" type="text/css" href="mainstyle.css"> 
<body onload="javascript:LoadAllKillmails();">
<a href="./mainpage.php" class="mainbutton">Main</a>      
<a href="./Killmail_private.php" class="myloss">SRP 신청하기</a><br><br><br>
<?php
include $_SERVER['DOCUMENT_ROOT']."/CorpESI/shrimp/phplib.php";

dbset();
logincheck();
session_start();

$KILLMAIL_PER_PAGE=8;


if(!isset($_GET["page"])){
    $_GET["page"]=1;
}
$qr="select count(*) from Shrimp_killmails where SRP_status>0";
$result=$dbcon->query($qr)->fetch_row();
$KILLMAILN=$result[0];
$pln_start=max(1,$_GET["page"]-5);
$pln_end=min(ceil(max($KILLMAILN-1,0)/$KILLMAIL_PER_PAGE),$_GET["page"]+5);

for($i=$pln_start;$i<=$pln_end;$i++){
    if($i!=$_GET["page"]){
        echo("<a class='pagen' href='./mainpage.php?page=".$i."'>".$i."</a> ");
    }
    else{
        echo("<b class='pagen'>".$i."</b>");
    }
}
echo("<br/><br/>");

$qr="select * from Shrimp_killmails where SRP_status>0 order by submitdate desc limit ".(($_GET["page"]-1)*$KILLMAIL_PER_PAGE)." ,".$KILLMAIL_PER_PAGE.";";
$result=$dbcon->query($qr);

echo("<table>\n");
for($i=0;$i<$result->num_rows;$i++){
    $kmdata[$i]=$result->fetch_array();
    echo("<tr>\n");
    echo("<td rowspan=2 class=shipimg><img src=\"./images/loading.gif\" id=\"icon".$kmdata[$i]["killmail_id"]."\" style=\"width:64px;height:64px;\"></td>\n");
    echo("<td class=shipname><span id=\"ship".$kmdata[$i]["killmail_id"]."\">Ship</span></td>\n");
    echo("<td rowspan=2 class=userport><img src=\"./images/loading.gif\" id=\"port".$kmdata[$i]["killmail_id"]."\" style=\"width:64px;height:64px;\"></td>\n");
    echo("<td class=username><span id=\"name".$kmdata[$i]["killmail_id"]."\">Character</span></td>\n");
    //SRP_status=1: Pending , =2: Accepted , =3 : Rejected
    echo("<td rowspan=2 align=center class=\"SRP_result\">".number_format($kmdata[$i]["charge"])." ISK<br>".($kmdata[$i]["SRP_status"]==1?(in_array($_SESSION["shrimp_userid"],$SRP_ADMIN_ID)?("<span class=\"SRP_pending\" id=\"SRP_admin_pending".$kmdata[$i]["killmail_id"]."\"><input type=button value=\"Accept\" onclick=\"javascript:dealSRP(".$kmdata[$i]["killmail_id"].",1)\"><input type=button value=\"Reject\" onclick=\"javascript:dealSRP(".$kmdata[$i]["killmail_id"].",0)\"></span>"):("<span class=\"SRP_pending\">Pending</span>")):($kmdata[$i]["SRP_status"]==2?"<span class=\"SRP_accepted\">Accepted</span>":"<span class=\"SRP_rejected\">Rejected</span>") )."</td>\n");
    echo("<td rowspan=2 class=zklink><span class=\"zklink\" onclick=\"window.open('https://zkillboard.com/kill/".$kmdata[$i]["killmail_id"]."/','_blank');\"><img src=./images/wreck.png>zKill</span></td>");
    echo("</tr>\n<tr>\n");
    echo("<td class=location><span id=\"loca".$kmdata[$i]["killmail_id"]."\" style=\"loca\">Location</span> <span id=\"locasecu".$kmdata[$i]["killmail_id"]."\" style=\"locasecu\"></span></td>\n");
    echo("<td class=killdate><span id=\"date".$kmdata[$i]["killmail_id"]."\">Date</span></td>\n");
    echo("</tr>\n");
    echo("<tr ><td colspan=6 class=\"memo_space\"><div id=\"memo_space".$kmdata[$i]["killmail_id"]."\" class=\"memo_space\">".$kmdata[$i]["memo"]."</div></td></tr>\n");
    echo("<tr ><td colspan=6 class=\"emoji_space\"><div id=\"emoji_space".$kmdata[$i]["killmail_id"]."\" class=\"emoji_space\"></div></td></tr>\n");

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
else if(sizeof($kmdata)==1){
    echo("var killmails=new Array();\n");
    echo("killmails[0]=".$kmdata[0]["killmail_id"].";\n");

}


echo("</script>\n");
?>


<script>

function UpdateKillmail(maintoon){
    var ESIdata=new XMLHttpRequest();
    ESIdata.onreadystatechange=function(){

        if (this.readyState == XMLHttpRequest.DONE && this.status==200){
            

        }   
    }

    ESIdata.open("GET","./loadkillmail.php",true);
    ESIdata.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;");
    ESIdata.send("id="+killmail_id);
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
function LoadAllKillmails(){
    
    var kmdata=new Array();
    console.log(killmails);
    for(var i=0;i<killmails.length;i++){
        
        kmdata[i]=LoadKillmail(killmails[i]);
        document.getElementById("icon"+killmails[i]).src="https://images.evetech.net/types/"+kmdata[i].victim.ship_type_id+"/icon";
        document.getElementById("ship"+killmails[i]).innerHTML=getName("item",kmdata[i].victim.ship_type_id);
        document.getElementById("port"+killmails[i]).src="https://images.evetech.net/characters/"+kmdata[i].character_id+"/portrait?size=64";
        document.getElementById("name"+killmails[i]).innerHTML=getName("character",kmdata[i].character_id);
        document.getElementById("loca"+killmails[i]).innerHTML=getName("system",kmdata[i].solar_system_id);
        document.getElementById("date"+killmails[i]).innerHTML=kmdata[i].killmail_time;
        
        var systeminfo=getInfo("system",kmdata[i].solar_system_id);
        //console.log(systeminfo.security_status);
        document.getElementById("locasecu"+killmails[i]).innerHTML=parseFloat(systeminfo.security_status).toFixed(1);
        
        var secred=Math.round(215*(1-systeminfo.security_status));
        var secgreen=Math.round(210*(systeminfo.security_status));
        var secblue=Math.round(100*(systeminfo.security_status-0.5));
        //alert(secred+" "+secgreen+" "+secblue);//DEBUG
        secred=secred>255?255:secred;
        secgreen=secgreen<1?1:secgreen;
        secblue=secblue<0?0:secblue;
        var hexcolor=Number((secred*256*256)+(secgreen*256)+secblue).toString(16);
        hexcolor=hexcolor.length==5?"#0"+hexcolor:"#"+hexcolor;

        document.getElementById("loca"+killmails[i]).style.color=hexcolor;
        document.getElementById("locasecu"+killmails[i]).style.color=hexcolor;
        
        LoadEmoji(killmails[i]);
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

function LoadEmoji(killmail_id){
    var ESIdata=new XMLHttpRequest();

    var emojis=new Array();
    ESIdata.onreadystatechange=function(){

        if (this.readyState == XMLHttpRequest.DONE && this.status==200){
            
            //alert(this.responseText);
            var resultstring="<table class=\"emoji_table\">";
            emojis=JSON.parse(this.responseText);
        
            for(i=0;i<emojis.emoji.length;i++){
                if(i%8==0){
                    resultstring+="<tr>";
                }
                resultstring+="<td class=\"emoji_icon\"><img src=\"./images/emojis/"+emojis.emoji[i][0]+".png\" class=\"emoji_icon\" id=\"emoji"+killmail_id+"_"+emojis.emoji[i][0]+"\" onclick=\"javascript:toggleEmoji("+emojis.emoji[i][0]+","+killmail_id+");\"></td>\n";
                resultstring+="<td class=\"emoji_num\"><span class=\"emoji_num\" id=\"enum"+killmail_id+"_"+emojis.emoji[i][0]+"\">"+emojis.emoji[i][1]+"</span></td>\n";
                if(i%8==7){
                    resultstring+="</tr>";
                }   
            }
            if(i%8<7){
                while(i%8<7){
                    resultstring+="<td class=\"emoji_icon\"></td class=\"emoji_num\">";
                    i++;
                }
                resultstring+="</tr>";
            }
            resultstring+="</table>";
            document.getElementById("emoji_space"+killmail_id).innerHTML=resultstring;
            
            //아무도 안 찍은 이모지는 다르게 표시한다(인액티브)
            
            for(i=(emojis.emoji.length-1);i>=0 && emojis.emoji[i][1]==0;i--){
                
                document.getElementById("emoji"+killmail_id+"_"+emojis.emoji[i][0]).className="emoji_icon_zero";
            }
            //내가 찍은 이모지는 조금 다르게 표시한다(색깔을 넣던지)
            
            for(i=0;i<emojis.myemoji.length;i++){
                document.getElementById("enum"+killmail_id+"_"+emojis.myemoji[i]).className="emoji_num_my";
                
            }
            
        }        
    }

    ESIdata.open("GET","./loadEmoji.php?killmail_id="+killmail_id,true);
    ESIdata.send();
    

    
}

function LoadSingleEmoji(emojinum,killmail_id){
    
    var ESIdata=new XMLHttpRequest();

    ESIdata.onreadystatechange=function(){

        if (this.readyState == XMLHttpRequest.DONE && this.status==200){
            
            //alert(this.responseText);
            emojis=JSON.parse(this.responseText);
            //$jsonarray->count=$count;
            //$jsonarray->myemoji=$mycon;          
            document.getElementById("enum"+killmail_id+"_"+emojinum).innerHTML=emojis.count;
            
            //아무도 안 찍은 이모지는 다르게 표시한다(인액티브)
            if(emojis.count==0){
                document.getElementById("emoji"+killmail_id+"_"+emojinum).className="emoji_icon_zero";
            }
            else{
                document.getElementById("emoji"+killmail_id+"_"+emojinum).className="emoji_icon";
            }
            
            //내가 찍은 이모지는 조금 다르게 표시한다(색깔을 넣던지)
            if(emojis.myemoji){
                document.getElementById("enum"+killmail_id+"_"+emojinum).className="emoji_num_my";
            }
            else{
                document.getElementById("enum"+killmail_id+"_"+emojinum).className="emoji_num";
            }         
        }        
    }

    ESIdata.open("GET","./loadEmoji.php?killmail_id="+killmail_id+"&emojinum="+emojinum,true);
    ESIdata.send();

    
}

function toggleEmoji(emojinum,killmail_id){
    
    var ESIdata=new XMLHttpRequest();

    var emojis=new Array();
    ESIdata.onreadystatechange=function(){

        if (this.readyState == XMLHttpRequest.DONE && this.status==200){
            LoadSingleEmoji(emojinum,killmail_id);
        }        
    }

    ESIdata.open("GET","./toggleEmoji.php?killmail_id="+killmail_id+"&emojinum="+emojinum,false);
    ESIdata.send();
}

function dealSRP(killmail_id,decision){
    var ESIdata=new XMLHttpRequest();

    ESIdata.onreadystatechange=function(){

        if (this.readyState == XMLHttpRequest.DONE && this.status==200){
            var SRP_DOM=document.getElementById("SRP_admin_pending"+killmail_id);
            
            if(decision==1){
                
                SRP_DOM.innerHTML="Accepted";
                SRP_DOM.className="SRP_accepted";
            }
            else if(decision==0){
                SRP_DOM.innerHTML="Rejected";
                SRP_DOM.className="SRP_rejected";
            }
        }        
    }

    ESIdata.open("GET","./decideSRP.php?killmail_id="+killmail_id+"&decision="+decision,true);
    ESIdata.send();

}
</script>