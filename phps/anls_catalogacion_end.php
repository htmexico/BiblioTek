<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  Guarda/Modifica un registro MARC de catalogación
	  
	  05 may 2009: Se crea el archivo
	  
     */
		
	include "../funcs.inc.php";
	include "../basic/bd.class.php";
	
	check_usuario_firmado(); 

	// Coloca un encabezado HTML <head>
	include "../basic/head_handler.php";
	
	include_language( "anls_catalogacion_end" );	
	
	$id_biblioteca = getsessionvar("id_biblioteca");

	$id_titulo = read_param( "id_titulo", "", 0 );
	$id_plantilla = read_param( "id_plantilla", "", 0 );
	
	HeadHandler( $LBL_CATALOG_HEADER_1 . ": " . $id_titulo, "../");
			
?>

<script type="text/javascript" language="JavaScript">

	function elegir_plantilla()
	{	
		var nwidth = screen.width;
		var nheight = screen.height; 
		var sel_plantilla = document.getElementsByName("sel_plantilla");
		
		window.open("anls_catalogacion_paso2.php?id_plantilla=" + sel_plantilla[0].value, "catalogacion", "WIDTH=" + (nwidth-50) + ",HEIGHT=" + (nheight-120) + ",TOP=20,LEFT=30,resizable=yes,scrollbars=yes,status=yes" );
			
		window.status='';
	}
	
	function existencias( id_titulo )
	{
		window.opener.document.location.href = "anls_existencias_paso2.php?id_titulo=" + id_titulo;
		window.close();
	}
	
	function digitalizar_portadas( id_titulo )
	{	
		document.location.href = "anls_catalogacion_frontpage.php?id_titulo=" + id_titulo;
	}
	
	function anexar_archivos( id_titulo )
	{	
		document.location.href = "anls_catalogacion_files.php?id_titulo=" + id_titulo;
	}	

</script>

<STYLE type="text/css"> 
</STYLE>

<body id="home">

	<?php
		SYNTAX_BEGIN_JavaScript();
		echo "window.opener.document.reload();";
		SYNTAX_CLOSE_JavaScript();
	 ?>

<br>
	<!-- contenedor principal -->
	<div id="contenedor" style="width:95%">	
	
		<div id="bloque_principal"> <!-- inicia contenido -->	
				
			 <div id="contenido_principal">

				<h2><?php echo sprintf( $LBL_CATALOG_HEADER_1, $id_titulo ) ;?></h2><br>
				
				<p>A partir de este punto Usted puede dirigirse a otras opciones.</p>
				
								<br style='clear:both;'> 
				
				<?php
					$db = new DB("SELECT * FROM cfgplantillas_nombres WHERE ID_BIBLIOTECA=" . getsessionvar("id_biblioteca") . " and ID_TIPO='CAT'; " );
					
					echo "&nbsp;&nbsp;$LBL_NEW_CATALOG&nbsp;&nbsp;<SELECT id='sel_plantilla' name='sel_plantilla' >";
					
					while( $db->NextRow() )
					{
						echo "<OPTION value='" . $db->Field("ID_PLANTILLA") . "' ".($id_plantilla==$db->Field("ID_PLANTILLA") ? "selected": "").">" . $db->Field("NOMBRE_PLANTILLA"). "</OPTION>";
					}
					
					echo "</SELECT>";
					
					$db->destroy();
				 ?>				

				<input class="boton" type="button" value="<?php echo $BTN_GO_CATALOG;?>" name="btnCatalog" id="btnCatalog" onClick="javascript:elegir_plantilla();">&nbsp;

				 
				<br><br>
				&nbsp;&nbsp;<?php echo $LBL_NEW_COVERS;?>&nbsp;&nbsp;<input class="boton" type="button" value="<?php echo $BTN_GO_SCAN_COVERS;?>" name="btnGoCovers" id="btnGoCovers" onClick="javascript:digitalizar_portadas(<?php echo $id_titulo;?>);">&nbsp;
				
				<br><br>
				&nbsp;&nbsp;<?php echo $LBL_NEW_EXISTENCES;?>&nbsp;&nbsp;<input class="boton" type="button" value="<?php echo $BTN_GO_EXISTENCES;?>" name="btnGoExist" id="btnGoExist" onClick="javascript:existencias(<?php echo $id_titulo;?>);">&nbsp;
				<br><br>
				
				&nbsp;&nbsp;<?php echo $LBL_NEW_UPDATEFILE;?>&nbsp;&nbsp;<input class="boton" type="button" value="<?php echo $BTN_GO_UPDLOADFILES;?>" name="btnGoExist" id="btnGoExist" onClick="javascript:anexar_archivos(<?php echo $id_titulo;?>);">&nbsp;
				<br><br>				
				
				<div>
				<input class="boton" type="button" value="Regresar al menú" name="btnBuscar" id="btnBuscar" onClick="javascript:window.close();"><br>
				</div>
				
			</div>
			
			<br style='clear:both;'> 
				
		</div>
		<br><br>
	
	</div>

</body>	

</html>