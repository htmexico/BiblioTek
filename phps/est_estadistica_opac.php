<?php
	session_start();
	/*******
	  Historial de Cambios
	  
	  21 jul 2011: Se crea el estadistico de OPAC
     */		
		
	include ("../funcs.inc.php");

	check_usuario_firmado(); 

	include ("../basic/bd.class.php");
	include ("../privilegios.inc.php");
	include ("estadisticas.inc.php");
	
	include_language( "global_menus" );
	include_language( "est_estadisticas_opac" ); 	// archivo de idioma
	include_language( "anls_consultatitulos" );  // consulta de titulos

	// Coloca un encabezado HTML <head>
	include ("../basic/head_handler.php");	
	HeadHandler( $LBL_HEADER, "../", 1 );
	
	verificar_privilegio( PRIV_EST_TITLES_MOST_VIEWED, 1 );
	
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
				var url = "est_estadistica_opac.php?desde=" + obj1.value + "&hasta=" + obj2.value;
				js_ChangeLocation( url );
			}
		}		


	}

</SCRIPT>

<style type="text/css">

#contenido_principal 
{
	float: none;
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
  ob_start();
  
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
				<div style='float:left; width: auto; margin-right:10px; '>
					<?php echo "<span>$LBL_DESDE</span>";
					
					colocar_edit_date_v2( "frm_fechas", "fecha_desde", $fecha_desde, 0, "" ); 
					
					?>
				</div>
				
				<div style='float:left; width: auto;'>
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
				
				ob_start();  // start buffering echos
	
				$fecha_desde = date_for_database_updates( $fecha_desde ) . " 00:00:00";
				$fecha_hasta = date_for_database_updates( $fecha_hasta ) . " 23:59:59";
				
				$db = new DB;
				
				$array_info_terminos = Array();

				estadisticas_consulta_opac( $db, $id_biblioteca, $fecha_desde, $fecha_hasta, $array_info_terminos );

				// TITULOS MÁS CONSULTADOS
				//echo "<hr>";
				echo "<h1>$LBL_CHAPTER_1</h1>";

				echo "<table width='100%'>";

				
				for( $i=0; $i<count($array_info_terminos); $i++ )
				{ 
					if( $i == 0 )
					{
						echo "<tr>" . 
							 " <td class='columna columnaEncabezado cuadricula' width='20%'>$LBL_TIPOBUSQUEDA</td>" .
							 " <td class='columna columnaEncabezado cuadricula' width='70%'>$LBL_TITLE</td>" .
							 " <td align='center' class='columna columnaEncabezado cuadricula' width='10%'>$LBL_HITS</td>" .
							 "</tr>";							 
					}
					$tipobusqueda = $array_info_terminos[$i]["tipobusqueda"];
					
					if( $tipobusqueda == "KEYWORDS" )
						$tipobusqueda = $LBL_KEYWORDS;
					
					/**
					
  $LBL_IDCONTROL	  = "ID / Control Interno";
  	  = "Palabras Clave";
  $LBL_TITLE		  	  = "T&iacute;tulo";
  $LBL_AUTHOR		  = "Autor";
  $LBL_SUBJECTS		  = "Materias";
  $LBL_ISBN		 	  = "I.S.B.N.";
  $LBL_ISSN		 	  = "I.S.S.N.";
  $LBL_CALLNUMBER     = "Signatura Topogr&aacute;fica";					
					**/
					echo "<tr>" . 
						 " <td class='columna cuadricula' valign='top'>" . $tipobusqueda . "</td>" .
						 " <td class='columna cuadricula'>" . $array_info_terminos[$i]["termino"] . "</td>" .
						 " <td align=center class='columna cuadricula' valign='top'>" . $array_info_terminos[$i]["consultas"] . "</td>" .
						 "</tr>";

				}

				echo "</table>";
				
				if( count($array_info_terminos) == 0 )
				{
					echo "<div class='caja_errores'>No se encontraron resultados en estas fechas</div>";
				}
				
				unset( $array_info_terminos );
				
				echo "<br>";		
				
				// Hide progressing
				SYNTAX_JavaScript( 1, 1, "HideDiv('showProcessing');" );
				//ob_end_clean() for debugging;
				
				echo "<br><input type='button' class='boton' value='$BTN_PRINT' onClick='javascript:print();'><br>";

				ob_end_flush();
				
				$db->destroy();
		  
		   ?>
		
		
	   </div><!-- - caja datos -->
	   
	   <?php DisplayChangeNotice(); ?>
	   
	</div> <!-- contenido pricipal -->
	
	

<?php  display_copyright(); ?>
</div><!--bloque principal-->
 </div><!--bloque contenedor-->
       
</body>
</html>