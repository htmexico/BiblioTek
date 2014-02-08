<?php
	session_start();
	/*******
	  Historial de Cambios
	  
	  17 nov 2009: Se crea el para estadística de circulación
	  
	  21 jul 2011 : Modifica validador y selector de fechas
	  
     */		
		
	include ("../funcs.inc.php");

	check_usuario_firmado(); 

	include ("../basic/bd.class.php");
	include ("../privilegios.inc.php");
	include ("estadisticas.inc.php");
	
	include_language( "global_menus" );
	include_language( "est_estadistica_circulacion" ); 	// archivo de idioma

	// Coloca un encabezado HTML <head>
	include ("../basic/head_handler.php");	
	HeadHandler( $LBL_HEADER, "../", 1 );
	
	verificar_privilegio( PRIV_EST_CIRCULATION, 1 );
	
	$id_biblioteca = getsessionvar( "id_biblioteca" );
	
	$fecha_desde = read_param( "desde", "" );
	$fecha_hasta = read_param( "hasta", "" );
	
	setsessionvar( "fecha_desde", $fecha_desde );
	setsessionvar( "fecha_hasta", $fecha_hasta );

	$filter = read_param( "filter", "" );
	
?>

<SCRIPT type='text/javascript' src='../basic/calend.js'></SCRIPT>
<link rel="stylesheet" href="../basic/calendar/calendar.css">
<script type="text/javascript" language="javascript" src="../basic/calendar/calendar_mx.js"></script>

<SCRIPT language="JavaScript">

	function updateView()
	{
		var error = 0;
		var obj1 = js_getElementByName( "fecha_desde" );
		var obj2 = js_getElementByName( "fecha_hasta" );

		if( !EsFechaValida( obj1 ) || !EsFechaValida( obj2 ) )
		{
			alert( "<?php echo $VALIDA_MSG_DATES_INVALID;?>" );
			
			obj1.focus();
			
			error = 1;
		}
		
		if( error == 0 )
		{
			if( !Validar2Fechas( obj1.value, obj2.value ) )
			{
				alert( "<?php echo $VALIDA_MSG_DATES_WRONG;?>" );
			
				obj1.focus();
				
				error = 1;
			}
		
			if( error == 0 )
			{
				var url = "est_estadistica_circulacion.php?desde=" + obj1.value + "&hasta=" + obj2.value;
				js_ChangeLocation( url );
			}
		}
	}

</SCRIPT>

<style type="text/css">

#contenido_principal 
{
	width: 850px;
}

#info_general 
{
	width: 830px;
}

#buttonArea
{
	margin-bottom: 8px;
}

</style>

</head>

<body id="home">

<?php
  
  ob_start();   // para mostrar avance en procesamientos 
  
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
   <div id="bloque_principal" > 
      <div id="contenido_principal">
	   <h1><?php echo $LBL_HEADER;?></h1>
		  
	   <div id="info_general" class="caja_datos">

		   <form id='frm_fechas' name='frm_fechas'>
				<div style='float:left; width: auto; margin-right: 10px;'>
					<?php echo "<span>$LBL_DESDE</span>";
					
					colocar_edit_date_v2( "frm_fechas", "fecha_desde", $fecha_desde, 0, "" ); 
					
					?>
				</div>

				<div style='float:left;  width: auto;'>
					<?php echo "<span>$LBL_HASTA</span>";
					
					colocar_edit_date_v2( "frm_fechas", "fecha_hasta", $fecha_hasta, 0, "" ); 
					
					?>				
				</div>
				
				<input type='button' class='boton' value='<?php echo $BTN_UPDATE_VIEW;?>' onClick='javascript:updateView();'>
				
		   </form>

		   <?php 
	
				echo "<div id='showProcessing' name='showProcessing'><img src='../images/icons/wheel-loader_small.gif'>$MSG_PROCESSING_SOMETHING</div>";
				ob_end_flush();
				
				sleep(1);

				ob_start();  // start buffering echos				
				echo "<br>";
	
				$fecha_desde = date_for_database_updates( $fecha_desde ) . " 00:00:00";
				$fecha_hasta = date_for_database_updates( $fecha_hasta ) . " 23:59:59";
				
				$db = new DB;
				
				/* prestamos */
				echo "<hr>";
				echo "<h1>$LBL_CHAPTER_2</h1>";
				$prestamos = 0;
				$info_prestamos = estadisticas_obtener_info_prestamos( $db, $id_biblioteca, $fecha_desde, $fecha_hasta, $prestamos );
				
				for( $i=0; $i<count($info_prestamos); $i++ )
				{ 
					echo "&nbsp;<div class='mini_bullet'></div><strong>" . $info_prestamos[$i]["descripcion"] . "</strong> = " . $info_prestamos[$i]["prestamos"] . "<br>" ; 
				}
				
				if( count($info_prestamos) > 0 )
					echo "<br>";
				echo "&nbsp;&nbsp;<strong>$LBL_TOTAL_LOANS</strong>  " . $prestamos; 
				echo "<br><br>";
				
				/* reservaciones */
				
				// DEVOLUCIONES			
				echo "<hr>";
				echo "<h1>$LBL_CHAPTER_3</h1>";
				$devoluciones = 0;
				$info_devoluciones = estadisticas_obtener_info_devoluciones( $db, $id_biblioteca, $fecha_desde, $fecha_hasta, $devoluciones );
				
				for( $i=0; $i<count($info_devoluciones); $i++ )
				{ 
					echo "&nbsp;<div class='mini_bullet'></div><strong>" . $info_devoluciones[$i]["descripcion"] . "</strong> = " . $info_devoluciones[$i]["devoluciones"] . "<br>" ; 
				}
				
				if( count($info_devoluciones) > 0 )
					echo "<br>";
				echo "&nbsp;&nbsp;<strong>$LBL_TOTAL_DEVS</strong>  " . $devoluciones; 				

				echo "<br><br>";
				
				// RENOVACIONES
				
				echo "<hr>";
				echo "<h1>$LBL_CHAPTER_4</h1>";
				
				$renovaciones = 0;
				$info_renovaciomes = estadisticas_obtener_info_renovaciones( $db, $id_biblioteca, $fecha_desde, $fecha_hasta, $renovaciones );
				
				for( $i=0; $i<count($info_renovaciomes); $i++ )
				{ 
					echo "&nbsp;<div class='mini_bullet'></div><strong>" . $info_renovaciomes[$i]["descripcion"] . "</strong> = " . $info_renovaciomes[$i]["renovaciones"] . "<br>" ; 
				}
				
				if( count($info_renovaciomes) > 0 )
					echo "<br>";
				echo "&nbsp;&nbsp;<strong>$LBL_TOTAL_RENEWALS</strong>  " . $renovaciones; 				
				
				echo "<br>";				
				
				// Hide progressing
				SYNTAX_JavaScript( 1, 1, "HideDiv('showProcessing');" );
				//ob_end_clean() for debugging;
				
				echo "<br><input type='button' class='boton' value='$BTN_PRINT' onClick='javascript:print();'><br>";				
				
				ob_end_flush();
				
				$db->destroy();
				
		  
		   ?>
		
		
	   </div><!-- - caja datos -->
	   
	</div> <!-- contenido pricipal -->

<?php  display_copyright(); ?>
</div><!--bloque principal-->
 </div><!--bloque contenedor-->
       
</body>
</html>