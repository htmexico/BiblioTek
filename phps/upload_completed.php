<?php 
  /****
    PENDIENTES: Colocar un archivo language
	
   **/
  session_start();

  include("../funcs.inc.php");
  
  check_usuario_firmado(); 
  
  include "../basic/head_handler.php";
  
  include_language( "global_menus" );	
  
  HeadHandler( "Uploaded finished", "../");
?>

<body>

<?php

	$url = $_GET['url'];
	$filename = $_GET['filename'];
	$filetype = $_GET['filetype'];

	echo "<head><title>Archivo anexado</title>";
	echo " <link href='../ee.css' type=text/css rel=stylesheet>";
	echo "</head>";
	
	echo "<body><br>";
	
	echo "\n<br><br><div id='contenedor' style='width:65%' align='center'><br>";
	echo "<H3>&nbsp;A partir de este momento el archivo '$filename' <span>";
	
	echo obtener_file_info( $filetype );
	
	echo "</span> está disponible.</H3>";

    echo "\n<br><input type='button' class='boton' onClick=\"javascript:location.href='" . $url . "'\" value='$BTN_GOBACK'><br><br>";
	
	echo "</div>";

?>

</body>
</html>

