<?php
	session_start();
	
	/*	  
	  - Impresion de Codigo de Barras
	  - Codificaciones EAN1, UPC-A, Codificacion 39, Codificacion 128 A, B, C
	  
	  - Inicio 17-04-2009.    
	  
	  - 10-nov-2009.   Refinación.
	  
	*/
		
	include "../funcs.inc.php";
	include "../basic/bd.class.php";
	include "../actions.inc.php";
	
	include_language( "global_menus" );

	check_usuario_firmado();	
	
	include_language( "anls_print_cards" ); // archivo de idioma
	
	include( "../privilegios.inc.php" );
	verificar_privilegio( PRIV_PRINT_CATALOG_CARDS, 1 );
	
	$cmb_plantilla 	= read_param( "cmb_plantilla", -1 );
	$cmb_codigo		= read_param( "cmb_codigo", -1 );
	$imagen1 = "";
	$imagen  = "";
	
	$id_biblioteca = getsessionvar("id_biblioteca");

	include "../basic/head_handler.php";
	HeadHandler( "$LBL_TITLE", "../" );

?>

<SCRIPT language="JavaScript">

function AbrirCentrado()
{
	var error = 0;
			
	if( error == 0 ) 
	{
		var tipo = js_getElementByName_Value("cmb_tipomaterial");
		var output = js_getElementByName_Value("cmb_output_target"); 
		
		var url = "anls_print_cards_paso2.php?action=go&id_tipomaterial="+tipo+"&output="+output;
		
		if( navigator.appName == "Microsoft Internet Explorer" )
			js_ProcessActionURL( 2, url );
		else
			js_ProcessActionURL( 1, url );

	}

}

</SCRIPT>

<STYLE>

 #contenido_principal
 {
	float:left;
	width: 80%;
 }
 
 #contenido_adicional
 { float: right; 
    width: 10%;
 }
	
</STYLE>

<body id="home">

<?php

display_global_nav(); // barra de navegación superior

?>

<div id="contenedor">  <!-- contenedor principal -->

<?php

   display_banner(); // banner
   
   display_menu( "../" ); // menu principal    

?> 
   <div id="bloque_principal"> <!-- inicia contenido -->
   	 
	 <div id="contenido_principal">	
	 
        <div class="caja_datos">
				
		<?php
			echo "<h2>$LBL_HEADER_V1</h2>";
			echo "<HR><br>";	
				
			echo "<form id='cod_form' name='cod_form' method='post' class='forma_captura' action='anls_barcode.php'>";
			
			$db = new DB();
			
			$db->Open( "SELECT ID_TIPOMATERIAL, CODIGO_MARC, CODIGO_MARC_ENG, CODIGO_MARC_PORT, DESCRIPCION, DESCRIPCION_ENG, DESCRIPCION_PORT " . 
						" FROM marc_material WHERE (OBSOLETO = 'N' or OBSOLETO is NULL) " .
						"ORDER BY ID_TIPOMATERIAL;" );
			
			echo "<dt>";
			echo "  <label for='cmb_tipomaterial'><strong>$LBL_MATERIAL</strong></label>";				
			echo "</dt>";
			echo "<dd>";
			echo "  <select name='cmb_tipomaterial' id='cmb_tipomaterial' class='select_captura' onchange='mostrarOcultarLink()'>";
				
			while( $db->NextRow() )
			{ 
				$codigo = get_translation( $db->row['CODIGO_MARC'], $db->row['CODIGO_MARC_ENG'], $db->row['CODIGO_MARC_PORT'] );
				$desc = get_translation( $db->row['DESCRIPCION'], $db->row['DESCRIPCION_ENG'], $db->row['DESCRIPCION_PORT'] );
				
				if( strlen($desc)>80 ) $desc = substr( $desc, 0, 78 ) . " ...";
				
				if ($id_tipomaterial==$db->row['ID_TIPOMATERIAL']) 					    
					echo "<option value='" . $db->row['ID_TIPOMATERIAL'] . "' selected>($codigo)&nbsp;" . $desc . "</option>";					
				else
					echo "<option value='" . $db->row['ID_TIPOMATERIAL'] . "'>($codigo)&nbsp;" . $desc . "</option>";
			}			
			
			$db->Close();
				
			echo "</select>";	
			echo "</dd>";
/**
			echo "<dt>";
			echo "   <label for='cmb_output_target'><strong>$LBL_CARD_STYLE</strong></label>";
			echo "</dt>";
			echo "<dd>";
			echo "   <select name='cmb_output_target' id='cmb_output_target'>";
			echo "    <option value='screen'>$LBL_OUTPUT_SCREEN</option>";
			echo "    <option value='pdf'>$LBL_OUTPUT_PDF</option>";
			echo "   </select>";
			echo "</dd>";
*/
			
			echo "<dt>";
			echo "   <label for='cmb_output_target'><strong>$LBL_OUTPUT_TARGET</strong></label>";
			echo "</dt>";
			echo "<dd>";
			echo "   <select name='cmb_output_target' id='cmb_output_target' class='select_captura' >";
			echo "    <option value='screen'>$LBL_OUTPUT_SCREEN</option>";
			echo "    <option value='pdf'>$LBL_OUTPUT_PDF</option>";
			echo "   </select>";
			echo "</dd>";
			
			echo "<br>";
				
			echo "<div id='buttonarea'>";	
			echo "<input onClick='href=AbrirCentrado();' type='button' class='boton' value='$BTN_PRINT'>";
			echo "<input id='btnRegresar' name='btnRegresar' class='boton' type='button' value='$BTN_GOBACK' onclick=\"location.href='../index.php'\">";
			echo "</div>";			
			echo "<br>";
				
			echo "</form>";		
				
			?>
			
        </div>	<!-- div caja datos -->
			
		</div> <!-- div contenido principal -->
			
		<div id="contenido_adicional">
			<div id="desp_img" name="desp_img" class="resaltados" 
				style=" <?php echo ($cmb_plantilla==0) ? "display:none;" : ""; ?> border:1px solid white; background: white; ">		
					
				<?php
					
					if( $cmb_plantilla != -1 )
						echo "<img src='../images/plantilla" . $cmb_plantilla . ".gif' >";
					
				?>	
			</div>
		</div> <!-- div contenido adicional -->	
   </div> 	<!-- div bloque_principal -->
   
   <?php  display_copyright(); ?>
	
</div><!-- end div contenedor -->

</body>

</html>