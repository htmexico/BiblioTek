<?php
	session_start();
	/*******
	  Historial de Cambios
	  
	  22 sep 2009: Se crea para estadística general
	  09 oct 2009: Se agrega resumen de prestamos;
	  09 oct 2009: Se agrega resumen de catalogacion; Se perfecciona la actualización de la consulta después de cambiar fechas
	  
	  21 jul 2011 : Modifica validador y selector de fechas
	  
     */		
		
	include ("../funcs.inc.php");

	check_usuario_firmado(); 

	include ("../basic/bd.class.php");
	include ("../privilegios.inc.php");
	include ("estadisticas.inc.php");
	
	include_language( "global_menus" );
	include_language( "est_estadistica_general" ); 	// archivo de idioma

	// Coloca un encabezado HTML <head>
	include ("../basic/head_handler.php");	
	HeadHandler( $LBL_HEADER, "../", 1);
	
	verificar_privilegio( PRIV_EST_GENERAL, 1 );
	
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
				var url = "est_estadistica_general.php?desde=" + obj1.value + "&hasta=" + obj2.value;
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
				<div style='float:left; width:auto; margin-right:10px;'>
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
				
				$array_info_titulos = estadisticas_obtener_titulos( $db, $id_biblioteca );
				
				echo "<hr>";
				echo "<h1>$LBL_CHAPTER_1</h1>";
				
				for( $i=0; $i<count($array_info_titulos); $i++ )
				{ 
					// OBTENER ESTADISTICAS DE COPIAS
					$num_archivos = 0;
					$info_copias = estadisticas_obtener_info_copias_x_material( $db, $id_biblioteca, $array_info_titulos[$i]["id_tipomaterial"], $num_archivos );
					
					//
					// FIN OBTENER ESTADISTICAS
					//
					
					echo "<div class='mini_bullet'></div><H2 style='border-bottom: 1px silver dotted; padding-bottom: 5px;'><img src='../" . $array_info_titulos[$i]["icono"] ."'>&nbsp;" . $array_info_titulos[$i]["descripcion"] . "</H2>";
					echo " $LBL_TITLES_CATALOGUED_BY_TYPE " . $array_info_titulos[$i]["cuantos"] . "<br>";
					
					$num_copias = 0;
					for( $j=0; $j<count($info_copias); $j++ )
					{
						echo " $LBL_COPIES " . $info_copias[$j]["descrip_str"] . "<br>" ; 
						$num_copias += $info_copias[$j]["copias"];
					}
					echo "&nbsp;&nbsp;<strong>$LBL_COPIES_HOLDED</strong> = " . $num_copias . "<br>" ; 
					
					if( $num_archivos > 0 )
						echo "&nbsp;&nbsp;<strong>$LBL_FILES_ATTACHED</strong> = " . $num_archivos . "<br>" ; 
					
					
					echo "<br>";
					
					unset( $info_copias );
				}
				
				/* titulos catalogados */
				echo "<hr>";
				echo "<h1>$LBL_CHAPTER_2</h1>";
				$tots_catalog = 0;
				$info_catalog = estadisticas_catalogacion( $db, $id_biblioteca, $fecha_desde, $fecha_hasta, $tots_catalog );
				
				for( $i=0; $i<count($info_catalog); $i++ )
				{ 
					echo "&nbsp;<div class='mini_bullet'></div><strong>" . $info_catalog[$i]["descripcion"] . "</strong> = " . $info_catalog[$i]["titulos"] . "<br>" ; 
				}
				
				if( count($info_catalog) > 0 )
					echo "<br>";

				echo "&nbsp;&nbsp;<strong>$LBL_TOTAL_CATALOGD</strong>  " . $tots_catalog; 
				echo "<br>";
				echo "<br>";
				
				/* prestamos */
				if( getsessionvar("__basic_service") == "S" )
				{
					echo "<hr>";
					echo "<h1>$LBL_CHAPTER_3</h1>";
					$prestamos = 0;
					$info_prestamos = estadisticas_obtener_info_prestamos( $db, $id_biblioteca, $fecha_desde, $fecha_hasta, $prestamos );
					
					for( $i=0; $i<count($info_prestamos); $i++ )
					{ 
						echo "&nbsp;<div class='mini_bullet'></div><strong>" . $info_prestamos[$i]["descripcion"] . "</strong> = " . $info_prestamos[$i]["prestamos"] . "<br>" ; 
					}
					
					if( count($info_prestamos) > 0 )
						echo "<br>";
					echo "&nbsp;&nbsp;<strong>$LBL_TOTAL_LOANS</strong>  " . $prestamos; 
					echo "<br>";
					echo "<br>";
				
					/* usuarios */
					$empleados = 0;
					$info_users = estadisticas_obtener_info_usuarios( $db, $id_biblioteca, $users, $empleados );
									
					echo "<hr>";
					echo "<h1>$LBL_CHAPTER_4</h1>";				
					
					for( $i=0; $i<count($info_users); $i++ )
					{ 
						echo "&nbsp;&nbsp;<strong>$LBL_USERS '" . $info_users[$i]["grupo"] . "'</strong>: " . $info_users[$i]["usuarios"] . "<br>" ; 
					}
					
					echo "&nbsp;&nbsp;<strong>$LBL_TOTAL_USERS </strong>  " . $users . "<br>" ; 
					echo "<br>";
					
					echo "<hr>";
					echo "<h1>$LBL_CHAPTER_5</h1>";								
					
					echo "&nbsp;&nbsp;<strong>$LBL_HUMAN_RESOURCES</strong>  " . $empleados . "<br>" ; 
					
				}
				
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