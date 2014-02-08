<?php
	session_start();
	
	/*	  
	  - Impresion de Codigo de Barras
	  - Codificaciones EAN1, UPC-A, Codificacion 39, Codificacion 128 A, B, C
	  
	  - Inicio 17-04-2009.    
	  
	*/
		
	include "../funcs.inc.php";
	include "../basic/bd.class.php";
	include "../actions.inc.php";
	
	include_language( "global_menus" );

	check_usuario_firmado();	
	
	include_language( "anls_barcode" ); // archivo de idioma
	
	$cmb_plantilla 	= read_param( "cmb_plantilla", -1 );
	$cmb_codigo		= read_param( "cmb_codigo", -1 );
	$imagen1 = "";
	$imagen  = "";
	
	if ( isset( $_GET["imagen"] ) ) $cmb_codigo=$_GET["imagen"];		
	
	$id_biblioteca = getsessionvar("id_biblioteca");

	include "../basic/head_handler.php";
	HeadHandler( "$LBL_HEADER", "../" );

?>

<SCRIPT language="JavaScript">

function AbrirCentrado()
{
	var largo = screen.width;
	var altura = screen.height;
	var top = (screen.height-altura)/2;
	var izquierda = (screen.width-largo)/2;
	var error = 0;

	if( document.cod_form.cmb_plantilla.value == -1 ) 
	{
		alert( "<?php echo $MSG_PLEASE_SELECT_A_TEMPLATE;?>" );
	} else 	
	{
		if( document.cod_form.cmb_codigo.value == -1 ) 
		{
			alert( "<?php echo $MSG_PLEASE_SELECT_A_CODE;?>" );
		} 
		else 	
		{	
			if ( document.cod_form.cmb_plantilla.value == 4 ) 
			{
				if( ( document.cod_form.txt_ms.value == "" ) || ( document.cod_form.txt_mi.value == "" ) || ( document.cod_form.txt_sf.value == "" ) || ( document.cod_form.txt_ec.value == "" ) ) 
				{	
					alert( "<?php echo $MSG_INCOMPLETE_FORM;?>" );
					error=1;
				}
			}	
				
			var url = "anls_print_barcodes.php?cmb_plantilla=" + document.cod_form.cmb_plantilla.value + 
					  "&cmb_codigo=" + document.cod_form.cmb_codigo.value + 
					  "&txt_number_of_labels=" + document.cod_form.cmb_exf.value + 
					  "&txt_topmargin=" + document.cod_form.txt_ms.value +
					  "&txt_leftmargin=" + document.cod_form.txt_mi.value +
					  "&txt_row_inter_space=" + document.cod_form.txt_sf.value +
					  "&txt_col_inter_space=" + document.cod_form.txt_ec.value + "&Submit=1";
				
			if( error == 0 ) 
			{
				//nuevaVentana = window.open( url, "pagina", 'width=' + largo + ',height=' + altura + ',top=' + top + ',left=' + izquierda );		
				if( navigator.appName == "Microsoft Internet Explorer" )
					js_ProcessActionURL( 2, url );
				else
					js_ProcessActionURL( 1, url );

				//nuevaVentana.focus();
				//js_ChangeLocation( url );
			}
		}
	}
	
	//document.cod_form.txt_ms.focus();
}

function mostrarOcultarLink()
{
	var divID  = document.getElementsByName("personalizar");
	var divID1  = document.getElementsByName("desp_img");
	
	if ( document.cod_form.cmb_plantilla.value == 4 )
	{
		divID[0].style.display = "block";
		divID1[0].style.display = "block";
	}
	else 
		divID[0].style.display = "none";
		
	if ( document.cod_form.cmb_plantilla.value == 1 ) 
		divID1[0].style.display = "block";
	
	js_ChangeLocation("anls_barcodes.php?cmb_plantilla=" + document.cod_form.cmb_plantilla.value + "&cmb_codigo=" + document.cod_form.cmb_codigo.value);
}

</SCRIPT>

<STYLE>

 #contenido_principal
 {
	float:left;
 }
 
 #contenido_adicional
 { float: right; 
    width: 22%;
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
			
			$db->Open( "SELECT * FROM cfgplantillas_bc WHERE ID_BIBLIOTECA='$id_biblioteca';" );
			
			echo "<dt>";
			echo "  <label for='cmb_plantilla'><strong>$LBL_PLANTILLA</strong></label>";				
			echo "</dt>";
			echo "<dd>";
			echo "  <select name='cmb_plantilla' id='cmb_plantilla' class='select_captura' onchange='mostrarOcultarLink()'>";
				
			echo "<option value='-1'>$HINT_PLEASE_SELECT_A_TEMPLATE</option>";
				
			while( $db->NextRow() )
			{ 
				if ($cmb_plantilla==$db->row['ID_PLANTILLA']) 					    
					echo "<option value='" . $db->row['ID_PLANTILLA'] . "' selected>" . $db->row['DESCRIPCION'] . "</option>";					
				else
					echo "<option value='" . $db->row['ID_PLANTILLA'] . "'>" . $db->row['DESCRIPCION'] . "</option>";
			}			
			
			$db->Close();
				
			echo "</select>";	
			echo "</dd>";
				
			echo "<br>";			
			
			echo "<dt>";			
			echo "  <label for='cmb_codigo'><strong>$LBL_CODIGO</strong></label>"; 
			echo "</dt>";
			echo "<dd>";
			echo "  <select name='cmb_codigo' id='cmb_codigo' class='select_captura'>";
					
				if ( $cmb_codigo == -1 ) echo "<option value='-1' selected >-Selecciona la Codificacion-</option>";
				else echo "<option value='-1'>-Selecciona la Codificacion-</option>";
					
				if ( $cmb_codigo == 1 ) echo "<option value='1' selected >C&oacute;digo 128 A</option>";
				else echo "<option value='1'>C&oacute;digo 128 A</option>";
					
				if ( $cmb_codigo == 2 ) echo "<option value='2' selected >C&oacute;digo 128 B</option>";
				else echo "<option value='2'>C&oacute;digo 128 B</option>";
					
				if ( $cmb_codigo == 3 ) echo "<option value='3' selected >C&oacute;digo 128 C</option>";
				else echo "<option value='3'>C&oacute;digo 128 C</option>";
					
				if ( $cmb_codigo == 4 ) echo "<option value='4' selected >C&oacute;digo 39</option>";
				else echo "<option value='4'>C&oacute;digo 39</option>";
					
				if ( $cmb_codigo == 5 ) echo "<option value='5' selected >EAN 13</option>";
				else echo "<option value='5'>EAN 13</option>";
					
				if ( $cmb_codigo == 6 ) echo "<option value='6' selected >UPC-A</option>";
				else echo "<option value='6'>UPC-A</option>";
					
			echo "</select>";				
			echo "</dt>";
			
				?>
				
				<br>
				
				<div id="personalizar" name="personalizar" class="resaltados" 
					style=" <?php echo ($cmb_plantilla!=4) ? "display:none;" : ""; ?> border:1px solid black; background: yellow; ">				
				
				<?php	
					echo "<label for='cmb_exf'>$LBL_ET_X_FILA</label>"; 
					echo "<select name='cmb_exf' id='cmb_exf' class='select_captura'>";			
						echo "<option value='1'>1</option>";
						echo "<option value='2'>2</option>";
						echo "<option value='3'>3</option>";
						echo "<option value='4'>4</option>";
						echo "<option value='5'>5</option>";
					echo "</select>";
					echo "<br>";
					
					echo "<label for='txt_ms'>" . $LBL_MGEN_SUP . "</label>";
					echo "<input class='campo_captura' type='text' name='txt_ms' id='txt_ms' size=10/>";
					echo "<br>";
					
					echo "<label for='txt_mi'>" . $LBL_MGEN_IZQ . "</label>";
					echo "<input class='campo_captura' type='text' name='txt_mi' id='txt_mi' size=10/>";
					echo "<br>";
					
					echo "<label for='txt_sf'>" . $LBL_ESP_FILAS . "</label>";
					echo "<input class='campo_captura' type='text' name='txt_sf' id='txt_sf' size=10/>";
					echo "<br>";
					
					echo "<label for='txt_ec'>" . $LBL_ESP_COLS . "</label>";
					echo "<input class='campo_captura' type='text' name='txt_ec' id='txt_ec' size=10/>";
					echo "<br>";
					
				echo "</div>";
					
					
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