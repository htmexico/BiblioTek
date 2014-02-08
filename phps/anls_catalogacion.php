<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  03 abr 2009: Se crea el archivo catalogacion.php
     */
		
	include "../funcs.inc.php";
	include ("../basic/bd.class.php");
	include "../privilegios.inc.php";

	check_usuario_firmado(); 
	
	include_language( "global_menus" );
	include_language( "anls_catalogacion" );

	// Coloca un encabezado HTML <head>
	include "../basic/head_handler.php";
	HeadHandler( $LBL_CATALOGACION, "../");
	
	$id_biblioteca = getsessionvar("id_biblioteca");

	$action = "";
	
	if( isset( $_GET["action"] ) )
		$action = $_GET["action"];
		
	verificar_privilegio( PRIV_CATALOGING, 1 );

?>

<SCRIPT type="text/javascript" language="JavaScript">

	function elegir_plantilla()
	{	
		var nwidth = screen.width;
		var nheight = screen.height; 
		var sel_plantilla = document.getElementsByName("sel_plantilla");
		
		window.open("anls_catalogacion_paso2.php?id_plantilla=" + sel_plantilla[0].value, "catalogacion", "WIDTH=" + (nwidth-50) + ",HEIGHT=" + (nheight-120) + ",TOP=20,LEFT=30,resizable=yes,scrollbars=yes,status=yes" );
			
		window.status='';
	}
	
</SCRIPT>

<STYLE>

  #caja_datos1 
  {
    float: left; 
    width: 750px; 
  }
  
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

	<div class=caja_datos id=caja_datos1 >
		<h2><?php echo $LBL_CATALOG_HEADER_1;?></h2><br>

			  <div style='border:0px solid red;'>
				<span style='width:250px; text-align:right; border: 0px solid black;'>&nbsp;<?php echo $LBL_TEMPLATE;?>&nbsp;&nbsp;&nbsp;</span>
				
			<?php

				$db = new DB("SELECT * FROM cfgplantillas_nombres WHERE ID_BIBLIOTECA=" . getsessionvar("id_biblioteca") . " and ID_TIPO='CAT'; " );
				
				echo "<SELECT id='sel_plantilla' name='sel_plantilla'>";
				
				while( $db->NextRow() )
				{
					echo "<OPTION value='" . $db->Field("ID_PLANTILLA") . "'>" . $db->Field("NOMBRE_PLANTILLA"). "</OPTION>";
				}
				
				echo "</SELECT>";
				
				$db->destroy();
					
			?>
				 
				 &nbsp;
				 
				 <input class="boton" type="button" value="<?php echo $BTN_START;?>" name="btnBuscar" id="btnBuscar" onClick="javascript:elegir_plantilla();">&nbsp;
				 
				 <br>

			  </div>
			  
			  <br>
			  
	</div> <!-- caja_datos --> 
	
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