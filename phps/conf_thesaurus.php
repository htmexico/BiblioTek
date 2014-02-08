<?php
	session_start();
		
	/*******
	 Historial de Cambios
		  
	 30 mar 2009: Se crea como parte de las funciones de config.

	 */		
		
	include "../funcs.inc.php";
	include "../actions.inc.php";
	include "../basic/bd.class.php";
	
	check_usuario_firmado(); 
	
	include_language( "global_menus" );	
	include_language( "conf_thesaurus" );

	// Draw an html head
	include "../basic/head_handler.php";
	HeadHandler( $LABEL_HEADER, "../" );
	
	include( "../privilegios.inc.php" );
	verificar_privilegio( PRIV_THESAURUS, 1 );	

?>

<STYLE type="text/css">

.caja_con_ligas .lista_elementos_indexada 
{

	width:65%;
	float:left;  }
	
</STYLE>

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

	<h1><?php echo $LABEL_HEADER_V2;?></h1>	  
	
	<p><?php echo $LABEL_INTRO;?></p>
	  
	  <p><?php echo $LABEL_CHOOSE_ONE;?></p>

	<div class="caja_con_ligas">
	
		<div class="lista_elementos_indexada">			
          <ul>
		  
		  <?php
		  
			$db = new DB( "SELECT ID_CATEGORIA, DESCRIPCION, SISTEMA FROM tesauro_categorias " .
						  "WHERE ID_RED=" . getsessionvar("id_red") . 
						  "ORDER BY ID_CATEGORIA" );
					   
			while( $db->NextRow() ) 
			{ 
				$id_categoria = $db->row["ID_CATEGORIA"];
				$descripcion   = $db->row["DESCRIPCION"];
				
				echo "<li><a href='conf_thesaurus_cat.php?id_categoria=$id_categoria'>$descripcion </a> " . (($db->row["SISTEMA"]=="S") ? "<img src='../images/db_internal.jpg'>" : "") . " </li>";
			}
			
			$db->FreeResultSet();
		  
		   ?>
			
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
