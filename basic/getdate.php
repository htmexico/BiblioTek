<?php
session_start();
	
if( isset($_GET["action"]) ) 
   $action = $_GET["action"]; 
   
if( isset($_GET["select"]) ) 
   $select = $_GET["select"]; 

if( !isset( $action ) )
{
  require("inc.date_functions.php");
  require("class.calendar.php");
}
else{
  require("inc.date_functions.php");
  require("class.calendar.php");
  }

  include( "../funcs.inc.php" ); 
  
  include ("../basic/head_handler.php");
  HeadHandler( "Seleccione una fecha ... Calendario", "../" );	
  
?>

<style type="text/css">

#home
{
	background: #FFF;
	margin:0px;
	padding:0px;
	text-align:left;
}

#contenedor 
{
	align: left;
	text-align:left;
	margin:0px;
	border: 0px;
	width: 200px;
}

</style>

<body id="home">

<!-- contenedor principal -->
 <div id="contenedor">

<?php 
 
    if( isset($_GET["nday"]) ) 
       $_day = $_GET["nday"];
	
    if( isset($_GET["nmonth"]) ) 	
       $_month = $_GET["nmonth"];
	   
	if( isset($_GET["nyear"]) ) 	   
       $_year = $_GET["nyear"];

    if( isset($select) )
    {
       // el formato de fecha debe venir en dd/mm/AAAA
	   if( strpos( $select, "/" ) !== false )
	   {
	      $_day = substr( $select, 0, 2 );
	      $_month = substr( $select, 3, 2 );
		  
		  if( $_day[0] == '0' ) $_day = substr( $_day, 1, 5 );
		  if( $_month[0] == '0' ) $_month = substr( $_month, 1, 5 );
		  
	      $_year = substr( $select, 6, 4 );
	   }
    }

    // por default obtener la fecha de HOY
	if( !isset( $_day ) ) $_day = $GLOBALS["day"];  
	if( !isset( $_month ) ) $_month = $GLOBALS["month"];
	if( !isset( $_year ) ) $_year = $GLOBALS["year"];
	
	$c = new calendar( $_day, $_month, $_year, 1 );   // 4th parameter pick a date

	$c->startonmonday = 1;
	$c->events = Array();
	
	$c->show( 1, 1, 0 );
	
	//print("<p><p>");
	
?>

 </div><!--bloque contenedor-->

</BODY>
</HTML>