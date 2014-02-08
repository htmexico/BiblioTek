<?php
  function HeadHandler( $html_title = "", $path_origen = "", $omitir_cierre_head=0 ) 
  {
	$path_to_skin = "$path_origen" . "css/";
	
	$skin = "";
	
	if( !issetsessionvar("skin") )
	    $skin = "Default";
	else
		$skin = getsessionvar( "skin" );
	
	$path_to_skin .= $skin;
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
      <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta name="ROBOTS" content="INDEX,FOLLOW">
	<meta name="description" content="Sistema Integral de Gestión Bibliotecaria">
	<meta name="description" content="Library Automation Software">
	<meta name="keywords" Content="software, gestión bibliotecaria, library automation, bibliotek, bibliotecas, software para bibliotecas">
	<meta name="keywords" content="software for library automation">
	
	<link href="<?php echo $path_to_skin; ?>/screen.css" type="text/css" rel="stylesheet" >
	<link href="<?php echo $path_to_skin; ?>/menu.css" type="text/css" rel="stylesheet">

	<script type="text/javascript" src="<?php echo $path_origen; ?>menus.js"></script><script type="text/javascript" src="<?php echo $path_origen; ?>utils.js"></script>

	<title><?php echo $html_title;?></title>	

<?php

	if( $omitir_cierre_head==0 )
		echo "</head>";

  }
?>