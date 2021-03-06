<?php
include "dbdefine.php"; // mysql details

if(!$_GET["user"] or !$_GET["room"] or
      !$_GET["course"] or $_GET["user"] == 'None'){
  exit;
}

$_GET["user"] = trim($_GET["user"]);
$_GET["room"] = trim($_GET["room"]);
// $_GET["course"] = trim($_GET["course"]);

// plus signs used in edX course_ids encode spaces for PHP, so we need to do this
$_GET["course"] =  str_replace(" ", "+", $_GET["course"]);
$_GET["queue"] = trim($_GET["queue"]);
$_GET["wtime"] = trim($_GET["wtime"]);

// print "room ".$_GET["room"]."<br>";
// print "user ".$_GET["user"]."<br>";
// print "course ".$_GET["course"]."<br>";
// print "queue ".$_GET["queue"]."<br>";
// print "wtime ".$_GET["wtime"]."<br>";

if(!$_GET["wtime"]){$_GET["wtime"] = 5;}

date_default_timezone_set('UTC');
$fecha = time()+($_GET["wtime"]*60);
$fecha = time()+($_GET["wtime"]*60);
$dia = date("d", $fecha);
$mes = date("m", $fecha);
$ano = date("Y", $fecha);
$hora = date("H", $fecha);
$minutos = date("i", $fecha);
$segundos = date("s", $fecha);

$endate = $ano."/".$mes."/".$dia." ".$hora.":".$minutos.":".$segundos;

// not sure about this
// $nick = substr($_GET["user"],0,strpos($_GET["user"],"@"));

// check if the user is already in this room and course
$stmt = $dbhchat->prepare("select * from teams where
                            user = ? and room = ? and course = ?
                            and full = 1 and team_seed != '' ");
$stmt->execute(array($_GET["user"],$_GET["room"],$_GET["course"]));
$rows = $stmt->fetch();

if($rows["team_seed"]){

   header("Location: /index.php?user=".$_GET["user"]."&room=".$rows["team_seed"]);
   exit;
  // echo "team! <b>";
}
?>


<style>
body{
  font-size: 1em;
  line-height: 1.6em !important;
  color: #3c3c3c;
  font-family:"Open Sans",Verdana,Geneva,sans-serif,sans-serif;
}
.button{
  border: 1px solid #cacaca;
  border-radius: 3px;
  box-shadow: inset 0 1px 0 0 #fff;
  color: #333;
  display: inline-block;
  font-size: inherit;
  font-weight: bold;
  background-color: #eee;
  background-image: -webkit-linear-gradient(#eee,#d2d2d2);
  background-image: linear-gradient(#eee,#d2d2d2);
  padding: 7px 18px;
  text-decoration: none;
  text-shadow: 0 1px 0 #f8f8f8;
  background-clip: padding-box;
  border: 1px solid #cfc6c6;
  border-radius: 3px;
  box-shadow: inset 0 1px 0 0 #fff;
  color: #333;
  display: inline-block;
  font-size: inherit;
  font-weight: bold;
  background-color: #eee;
  background-image: -webkit-linear-gradient(#eee,#d6cece);
  background-image: linear-gradient(#eee,#d6cece);
  padding: 7px 18px;
  text-decoration: none;
  text-shadow: 0 1px 0 #f9f8f8;
  background-clip: padding-box;
  font-size: 0.8125em;
  height: 40px;
  vertical-align: middle;
  font-weight: 600;
  margin-top:14px;
}
</style>
<script type="text/javascript" src="/js/jquery-1.11.3.min.js"></script>
<script type="text/javascript" src="/js/jquery.countdown.min.js"></script>

<button id = 'bot1' class='button' onclick='javascript:startsearch();'>
  Click to search for chat partners</button>


<div id='bot2'  style='display:none;'>
  Waiting for partners to chat with  - Time left: <span id='clock'></span>
</div>


<div id='bot3' style='display:none;'>
  Ok! Your partners are ready. Click <b>Begin</b> to start the chat.<br>
  <button class='button' onclick='javascript:launchat();'>Begin</button>
</div>

<!-- Why was this calling restt() and not just startsearch()? -->
<div id='bot4'  style='display:none;'>
  Sorry, nobody else is online now. Would you like to wait another <?php echo $_GET["wtime"];?> minutes? If so, click on the <b>Restart</b> button.<br>
  <button class='button' onclick='javascript:startsearch();'>Restart countdown</button>
</div>

<input type=hidden name=chatkey id=chatkey value=''>
<input type=hidden name=searching id=searching value=''>

<br>
<div id='debug'>
</div>



<script type="text/javascript">
var intervalID;

$('#clock').countdown(($.now()+<?php echo ($_GET["wtime"]*60)*1000 ;?>), function(event) { $(this).html(event.strftime('%M:%S')); } );

$('#clock').countdown('stop'); // this works but is it necessary?

$('#clock').on('finish.countdown', function() {
  $('#searching').val(0);
  $('#bot2').hide();
  // why is this happening?
  $.ajax({ url: "remov.php", data: { room : "<?php echo $_GET["room"]?>" , user: "<?php echo $_GET["user"]?>", queue: "<?php echo $_GET["queue"]?>"}, cache: false });
  $('#bot4').show();
});


function startsearch(){
  $('#searching').val(1);
  $('#bot1').hide();
  $('#bot3').hide();
  $('#bot4').hide();
  $('#bot2').show();
  $('#clock').countdown($.now()+<?php  echo ($_GET["wtime"]*60)*1000 ;?>);
  setTimeout(chekme,2500);
}
//
// function restt(){ // why is this different from startsearch()?
//   $('#bot2').show();
//   $('#bot1').hide();
//   $('#bot3').hide();
//   $('#bot4').hide();
//   $('#clock').countdown($.now()+<?php  echo ($_GET["wtime"]*60)*1000 ;?>);
//   $('#searching').val("1");
//   /// how is this not here?
//   setTimeout(chekme,2500);
// }

function chekme(){
  if($('#searching').val() == 1 ){
    $.ajax({
      url: "checkmates.php",
      data: { room : "<?php echo $_GET["room"]?>" ,
      user: "<?php echo $_GET["user"]?>",
      course: "<?php echo $_GET["course"]?>",
      queue: "<?php echo $_GET["queue"]?>"},
      cache: false
    })
    .done(function( html ) {
      $( "#debug" ).html( html );
    });
  }
}

function keepsearch(){
  if($('#searching').val() == 1 ){
    setTimeout(chekme,2500);
  }
}


function launchat(){
  $('#bot3').hide();
  window.location.replace("/index.php?user=<?php echo $_GET["user"];?>&room="+$('#chatkey').val());
  window.parent.location = document.referrer;
}
</script>
