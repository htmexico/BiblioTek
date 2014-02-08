<?php 
  session_start();
	
  global $ACCESS_CFG;
	
  require_once( "APP_CONFIG.php" );
  require_once( "GLOBAL_CONFIG.php" );
  
  global $www_dir;
	
 ?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<title>BiblioTEK - Starter</title>
</head>

<frameset scrolling="NO" framespacing="0" border="false" frameborder="0" rows="1,100%">
    <frame src="" name="TITLEBAR" id="TITLEBAR" scrolling="no" noresize marginwidth="0" marginheight="0" target="CONTENIDO2">
    <frame src="<?php echo $www_dir;?>main.php?init=1&id_lib=<?php echo $ACCESS_CFG->id_biblioteca;?>" name="CONTENIDO2" scrolling="yes" target="CONTENIDO2" marginwidth="0" marginheight="0" >
 <noframes>
    <body bgcolor="#FFFFFF">
    </body>
 </noframes>
 
</frameset>
</html>


