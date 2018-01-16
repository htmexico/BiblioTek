<?php
	session_start();
	
	/*	  
	  - Impresion de Catálogos
	  
	  - 12-nov-2009.	Se inicia.
	  
	*/
		
	include "../funcs.inc.php";
	include "../basic/bd.class.php";
	include "../actions.inc.php";
	
	include_language( "global_menus" );

	check_usuario_firmado();	
	
	include_language( "anls_print_catalogs" ); // archivo de idioma
	
	include( "../privilegios.inc.php" );
	verificar_privilegio( PRIV_PRINT_CATALOGS, 1 );
	
	$id_biblioteca = getsessionvar("id_biblioteca");

	$id_tipomaterial = read_param( "id_tipomaterial", "" );
	
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
		var catalog_type = js_getElementByName_Value("cmb_catalog_type"); 
		var output = js_getElementByName_Value("cmb_output_target"); 
		var cols = js_getElementByName_Value("cmb_style_columns"); 
		var chk_obj;
		var contenido = "";

		chk_obj = js_getElementByName("chk_content_num_control");
		if( chk_obj.checked )
			contenido = contenido +"{1}";

		chk_obj = js_getElementByName("chk_content_author");
		if( chk_obj.checked )
			contenido = contenido +"{2}";		

		chk_obj = js_getElementByName("chk_content_title");
		if( chk_obj.checked )
			contenido = contenido +"{3}";

		chk_obj = js_getElementByName("chk_content_issue_info");
		if( chk_obj.checked )
			contenido = contenido +"{4}";

		chk_obj = js_getElementByName("chk_content_phy_descript");
		if( chk_obj.checked )
			contenido = contenido +"{5}";

		chk_obj = js_getElementByName("chk_content_themes");
		if( chk_obj.checked )
			contenido = contenido +"{6}";		
			
		chk_obj = js_getElementByName("chk_content_series");
		if( chk_obj.checked )
			contenido = contenido +"{7}";					
			
		chk_obj = js_getElementByName("chk_content_location");
		if( chk_obj.checked )
			contenido = contenido +"{8}";	

		if( contenido == "" )
		{
			alert( "<?php echo $MSG_NOT_CONTENT_SELECTED;?>" );
		}
		else
		{
			var url = "anls_print_catalogs_paso2.php?action=go&id_tipomaterial="+tipo+"&cat_type="+catalog_type+"&output="+output+"&cols="+cols+"&contenido="+contenido;

			if( navigator.appName == "Microsoft Internet Explorer" )
				js_ProcessActionURL( 2, url );
			else
				js_ProcessActionURL( 1, url );
		}
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

			echo "<dt>";
			echo "   <label for=' '><strong>$LBL_CATALOG_STYLE</strong></label>";
			echo "</dt>";
			echo "<dd>";
			echo "   <select name='cmb_catalog_type' id='cmb_catalog_type' class='select_captura' >";
			echo "    <option value='author'>$LBL_STYLE_AUTHOR</option>";
			echo "    <option value='title'>$LBL_STYLE_TITLE</option>";
			echo "   </select>";
			echo "</dd>";
		
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
			
			echo "<dt>";
			echo "   <label for='cmb_style_columnas'><strong>$LBL_CATALOG_COLUMNS</strong></label>";
			echo "</dt>";
			echo "<dd>";
			echo "   <select name='cmb_style_columns' id='cmb_style_columns' class='select_captura' >";			
			echo "    <option value='0'>$LBL_CATALOG_CELLS</option>";
			echo "    <option value='1'>$LBL_CATALOG_COL_1</option>";
			echo "    <option value='2'>$LBL_CATALOG_COL_2</option>";
			echo "    <option value='3'>$LBL_CATALOG_COL_3</option>";
			echo "   </select>";
			echo "</dd>";		
			
			echo "<br>";

			echo "<dt>";
			echo "   <label><strong>$LBL_CATALOG_CONTENTS</strong></label>";
			echo "</dt>";
			echo "<dd>";
			echo "    <input type='checkbox' class='checkbox' name='chk_content_num_control' id='chk_content_num_control'>&nbsp;$LBL_CONTENT_INCLUDE_NUM_CONTROL<br>";
			echo "</dd>";				
			
			echo "<dt>";
			echo "   <label>&nbsp;</label>";
			echo "</dt>";
			echo "<dd>";
			echo "    <input type='checkbox' class='checkbox' name='chk_content_author' id='chk_content_author'>&nbsp;$LBL_CONTENT_INCLUDE_AUTHOR<br>";
			echo "</dd>";				
			
			echo "<dt>";
			echo "   <label>&nbsp;</label>";
			echo "</dt>";
			echo "<dd>";
			echo "    <input type='checkbox' class='checkbox' name='chk_content_title' id='chk_content_title'>&nbsp;$LBL_CONTENT_INCLUDE_TITLE<br>";
			echo "</dd>";				
			
			echo "<dt>";
			echo "   <label>&nbsp;</label>";
			echo "</dt>";
			echo "<dd>";
			echo "    <input type='checkbox' class='checkbox' name='chk_content_issue_info' id='chk_content_issue_info'>&nbsp;$LBL_CONTENT_INCLUDE_ISSUE<br>";
			echo "</dd>";				

			echo "<dt>";
			echo "   <label>&nbsp;</label>";
			echo "</dt>";
			echo "<dd>";
			echo "    <input type='checkbox' class='checkbox' name='chk_content_phy_descript' id='chk_content_phy_descript'>&nbsp;$LBL_CONTENT_INCLUDE_PH_DESCRIPT<br>";
			echo "</dd>";				

			echo "<dt>";
			echo "   <label>&nbsp;</label>";
			echo "</dt>";
			echo "<dd>";
			echo "    <input type='checkbox' class='checkbox' name='chk_content_themes' id='chk_content_themes'>&nbsp;$LBL_CONTENT_INCLUDE_THEMES<br>";
			echo "</dd>";				
			
			echo "<dt>";
			echo "   <label>&nbsp;</label>";
			echo "</dt>";
			echo "<dd>";
			echo "    <input type='checkbox' class='checkbox' name='chk_content_series' id='chk_content_series'>&nbsp;$LBL_CONTENT_INCLUDE_SERIE<br>";
			echo "</dd>";							

			echo "<dt>";
			echo "   <label>&nbsp;</label>";
			echo "</dt>";
			echo "<dd>";
			echo "    <input type='checkbox' class='checkbox' name='chk_content_location' id='chk_content_location'>&nbsp;$LBL_CONTENT_INCLUDE_LOCATION<br>";
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
			&nbsp;
		</div> <!-- div contenido adicional -->	
   </div> 	<!-- div bloque_principal -->
   
   <?php  display_copyright(); ?>
	
</div><!-- end div contenedor -->

</body>

</html>