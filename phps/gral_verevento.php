<?php
 // Archivo de funciones básicas 
 // restricciones aplicadas en restricciones.inc.php
 
    /******
      Historial de Cambios
   
	  22-ene-2010: Se crea el archivo para ver eventos.
   */
   
	session_start();

	include "../funcs.inc.php";
	include_language( "global_menus" );

	$id_lib = read_param( "id_lib", 1 );
	$id = read_param( "id", 1 );
	
	require_once( "../basic/bd.class.php" );

	$db = new DB();

	$caption = "VIEW EVENT";
						  
	// Draw an html head
	include "../basic/head_handler.php";
	HeadHandler( "$caption", "../" );

?>

	<SCRIPT language="JavaScript" type="text/javascript">

	</SCRIPT>

<body id="home">
<?php
	display_global_nav();
  
 ?>

<div id="contenedor">

<?php 
   display_banner();  
   display_menu('../'); // menu principal
 ?>

<div id="bloque_principal"> <!-- inicia contenido -->
 <div id="contenido_principal">

		<?php
			$db->Open( "SELECT a.*, b.DESCRIPCION, c.ID_RED " . 
						"FROM recursos_contenido a " . 
						"  LEFT JOIN cfgubicaciones b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_UBICACION=a.ID_UBICACION) " .
						"   LEFT JOIN cfgbiblioteca c ON (c.ID_BIBLIOTECA=a.ID_BIBLIOTECA) " .
						"WHERE a.ID_BIBLIOTECA=$id_lib and a.ID_RECURSO=$id;" );
				
			$id_red = 0;
			$id_tipoevento = 0;
			$descripcion_tipo = "";
			$ubicacion = "";
			$sumario = "";
			
			$informacion_breve = "";
			$informacion_ampliada = "";
			
			$tempstr = "";
			$temp_time = "";			
				
			if( $db->NextRow() )
			{
				
				$id_tipoevento = $db->row["ID_TIPOEVENTO"];
				$ubicacion = $db->row["DESCRIPCION"];
				$sumario  = $db->row["SUMARIO"];
				
				$id_red = $db->row["ID_RED"];
				
				$desde  = get_str_date( $db->row["FECHA_DESDE"] );
				$hasta  = get_str_date( $db->row["FECHA_HASTA"] );
				
				if( $desde == $hasta )
					$tempstr = $desde;
				else 
				{
					$tempstr = "Del día $desde al $hasta";
				}
				
				$hora_desde = get_str_onlytime( $db->row["HORA_DESDE"], 0 );
				$hora_hasta = get_str_onlytime( $db->row["HORA_HASTA"], 0 );
				
				if( $hora_desde == $hora_hasta )
					$temp_time = $hora_desde;
				else
				{
					$temp_time = "de las $hora_desde al $hora_hasta";
				}
				
				$informacion_breve    = $db->GetBLOB( $db->row["INFORMACION_BREVE"], 1 );
				$informacion_ampliada = $db->GetBLOB( $db->row["INFORMACION_AMPLIADA"], 1 );
			}
			
			$db->Close();
			
			if( $id_tipoevento != 0 )
			{
				$db->Open(  "SELECT a.ID_TERMINO, b.TERMINO, b.CODIGO_CORTO, b.DESCRIPCION " .
							"FROM tesauro_terminos_categorias a " .
							" LEFT JOIN tesauro_terminos b ON (b.ID_RED=a.ID_RED and b.ID_TERMINO=a.ID_TERMINO) " .
							" WHERE a.ID_RED=$id_red and a.ID_CATEGORIA=22 and b.ID_TERMINO=$id_tipoevento;" );
							
				if( $db->NextRow() )
				{
					$descripcion_tipo = $db->row["DESCRIPCION"];
				}
				
				$db->Close();
			}
		 ?>

	<h1>Detalles del Evento</h1><br>

	<div class="caja_datos" style='overflow:auto;'>
		<h2><?php echo "Tipo: <strong>$descripcion_tipo</strong>";?></h2><hr>

		<div>Ubicación: <strong><?php echo $ubicacion;?></strong></div>
		<div>Horario: <strong><?php echo "$tempstr $temp_time"; ?></strong></div><br>
		<div><strong><?php echo $sumario;?></strong></div><br>
		
		<?php
		
			if( $informacion_breve != "" )		
			{
				echo "<div>$informacion_breve</div><br><br>";
			}
			
			if( $informacion_ampliada != "" )		
			{
				echo "<div>$informacion_ampliada</div><br><br>";
			}			
		
		  ?>
		  
		<input id='btnRegresar' name='btnRegresar' class='boton' type='button' value='<?php echo $BTN_GOBACK;?>' onclick="window.history.back();">
		<br>
		<br>
	  
	</div> <!-- caja_datos -->
	
	<br style='clear:all;'>

 </div> <!-- contenido_principal -->
 
	<div id="contenido_adicional">
	
		<?php
			if( isset($id_lib) )
			{
				LIBRARY_Display_Notes( $db, $id_lib );
			}
		 ?>
	
	</div> <!-- contenido_adicional -->

</div>
<!-- end div bloque_principal -->

<?php  

	display_copyright();
	
	$db->destroy();

 ?>

</div><!-- end div contenedor -->

</body>

</html>