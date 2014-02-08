<?php
	session_start();
		
	/*******
	 Historial de Cambios
		  
	 20 mar 2009: Se crea como parte de las funciones de localizacion
	 29 mar 2009: Se genera el registro en el log de actividades
	 */		
		
	include "../funcs.inc.php";
	include "../actions.inc.php";
	
	check_usuario_firmado(); 
	
	$descrip = "";
	
	if( isset($_GET["id"]) )
	{
		setsessionvar("language", $_GET["id"] );
		
		if( isset($_GET["descrip"]) )
			$descrip = $_GET["descrip"];
		
		$expire = mktime(0,0,0,1,1,2010); // un mes		
		setcookie( "language", $_GET["id"], $expire, "/", "", 0 );
		
		agregar_actividad_de_usuario( CFG_CHANGE_LANGUAGE, $descrip );
	}
	
	include_language( "global_menus" );
	include_language( "cambiar_idioma" );

	// Draw an html head
	include "../basic/head_handler.php";
	HeadHandler( "$LBL_HEADER", "../" );
	
	include( "../privilegios.inc.php" );
	verificar_privilegio( PRIV_CHANGELANGUAGE, 1 );			

?>

<body id="home">

<?php
  // barra de navegación superior
  display_global_nav();  
 ?>

<!-- contenedor principal -->
<div id="contenedor">

<?php 
   // banner
   display_banner();  
   
   // menu principal
   display_menu( "../" ); 
 ?>
 
<div id="bloque_principal"> <!-- inicia contenido -->

 <div id="contenido_principal">
 
 	<div class="info"><?php echo $LABEL_INFO;?></div>	  
	<p><?php echo $LABEL_INTRO;?></p>

	<div class="caja_con_ligas">
		<h1><?php echo $LABEL_CHOOSE_ONE;?></h1>
		
		<div class="lista_elementos_indexada">			
          <ul>
			<!-- ESPAÑOL -->
            <li><a href="conf_cambiar_idioma.php?id=1&descrip=ESPAÑOL"><?php echo $LABEL_LANG_1; ?>&nbsp;</a><img src='../images/idiomas/mexican_flag.png'></li>
            <!-- INGLES -->
			<li style='display:none;'><a href="conf_cambiar_idioma.php?id=2&descrip=ENGLISH"><?php echo $LABEL_LANG_2; ?>&nbsp;<img src='../images/idiomas/us_flag.png'></a></li>
            <!-- PORTUGUES -->
			<li style='display:none;'><a href="conf_cambiar_idioma.php?id=3&descrip=PORTUGUESE"><?php echo $LABEL_LANG_3; ?>&nbsp;<img src='../images/idiomas/brazil_flag.png'></a></li>
          </ul>
        </div>
		
	</div><!-- caja_con_ligas -->	

 </div> <!-- contenido_principal -->

 <div id="contenido_adicional">
  &nbsp;
  </div> <!-- contenido_adicional -->

</div>
<!-- end div bloque_principal -->

<?php  display_copyright(); ?>

</div><!-- end div contenedor -->

</body>

</html>
