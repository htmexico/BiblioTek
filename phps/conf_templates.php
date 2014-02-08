<?php
	session_start();
		
	/*******
	 Permite configurar elegir una categoría de plantillas
	 
	 Historial de Cambios
		  
	 17 jun 2009: Se crea como parte de las funciones de config.

	 */		
		
	include "../funcs.inc.php";
	include "../actions.inc.php";
	include "../basic/bd.class.php";
	
	check_usuario_firmado(); 
	
	include_language( "global_menus" );	
	include_language( "conf_templates" );

	// Draw an html head
	include "../basic/head_handler.php";
	HeadHandler( $LABEL_TITLE, "../" );
	
	include( "../privilegios.inc.php" );
	verificar_privilegio( PRIV_TEMPLATES, 1 );	

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
	  <h1><?php echo $LABEL_HEADER; ?></h1>
	  <p><?php echo $LABEL_INTRO;?></p>
	  <br>
	  <p class='info'><strong><?php echo $LABEL_CHOOSE_ONE;?></strong></p>
<br>
	<div class="caja_con_ligas">
		<div class="lista_elementos_indexada">			
          <ul>  
			<?php
				if( getsessionvar("__advanced_service") == "S" )
				{
					echo "<li><a href='conf_templates_cat.php?type=ENT'>$LABEL_CATEGORY_ENT</a></li>";
				}
			  ?>			
				<li><a href='conf_templates_cat.php?type=CAT'><?php echo $LABEL_CATEGORY_CAT;?> </a></li>
			
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
