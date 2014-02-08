<?php
	session_start();

	/**********
		
		HISTORIAL DE CAMBIOS
		
		Se consideran acciones: 

		  - est_general: Estadística General
		  - est_catalog: Estadísticas de catalogacion
		  - 
		
		08-septiembre-2009  Se crea archivo
		
		19 nov 2009: Se agrega est_opac, se actualiza verificacion de privilegios
				
		***********
		
		20 jul 2011:  Cambios en elección de fecha
			
	**********/		

	include ("../funcs.inc.php");
	include ("../basic/bd.class.php");
	include( "../privilegios.inc.php" );
	
	check_usuario_firmado(); 

	include_language( "global_menus" );
	include_language( "gral_rango_fechas" ); // archivo de idioma

	$fecha_desde = getcurdate_human_format();
	
	if( issetsessionvar("fecha_desde") )
		$fecha_desde = getsessionvar("fecha_desde");
		
	$fecha_hasta = $fecha_desde;
	
	if( issetsessionvar("fecha_hasta") )
		$fecha_hasta = getsessionvar("fecha_hasta");		
	
	$txt_id_usuario 	= read_param( "txt_id_usuario", "" );
	
	$the_action		    = read_param( "the_action", "", 1 );  // fail if not exist
	$descrip_action	    = "";
	$target_url		    = "";
	$hint			    = "";
	
	include ( "../basic/head_handler.php" );  // Coloca un encabezado HTML <head>
	HeadHandler( $LBL_HEADER, "../", 1 );	
	
	if( $the_action == "est_general" )
	{
		$descrip_action = $HINT_FOR_ACTION_EST_GENERAL;
		$target_url	    = "est_estadistica_general.php";
		$hint			= $HINT_FOR_ENTERING_USER_4_EST_GENERAL;
		
		verificar_privilegio( PRIV_EST_GENERAL, 1 );
	}
	else if( $the_action == "titulos_freq" )
	{
		$descrip_action = $HINT_FOR_ACTION_TIT_MAS_CONS;
		$target_url	    = "est_titulos_mas_consultados.php";
		$hint			= $HINT_FOR_ENTERING_USER_4_TIT_MAS_CONS;
		
		verificar_privilegio( PRIV_EST_TITLES_MOST_VIEWED, 1 );
	}
	else if( $the_action == "prest_otorgados" )
	{
		$descrip_action = $HINT_FOR_ACTION_PREST_OTORGADOS;
		$target_url	    = "est_prestamos_otorgados.php";
		$hint			= $HINT_FOR_ENTERING_USER_4_PREST_OTORGADOS;
		
		verificar_privilegio( PRIV_EST_LOANS_ISSUED, 1 );
	}	
	else if( $the_action == "prest_vencidos" )
	{
		$descrip_action = $HINT_FOR_ACTION_PREST_VENCIDOS;
		$target_url	    = "est_prestamos_vencidos.php";
		$hint			= $HINT_FOR_ENTERING_USER_4_PREST_VENCIDOS;
		
		verificar_privilegio( PRIV_EST_LOANS_ON_DUE, 1 );
	}		
	else if( $the_action == "est_catalog" )
	{
		$descrip_action = $HINT_FOR_ACTION_EST_CATALOG;
		$target_url	    = "est_estadistica_catalogacion.php";
		$hint			= $HINT_FOR_ENTERING_USER_4_EST_CATALOG;
		
		verificar_privilegio( PRIV_EST_CATALOGING, 1 );
	}	
	else if( $the_action == "est_sanciones" )
	{
		$descrip_action = $HINT_FOR_ACTION_EST_SANCTIONS;
		$target_url	    = "est_estadistica_sanciones.php";
		$hint			= $HINT_FOR_ENTERING_USER_4_EST_SANCTIONS;
		
		verificar_privilegio( PRIV_EST_SANCTIONS, 1 );
	}	
	else if( $the_action == "est_opac" )
	{
		$descrip_action = $HINT_FOR_ACTION_EST_OPAC;
		$target_url	    = "est_estadistica_opac.php";
		$hint			= $HINT_FOR_ENTERING_USER_4_OPAC;
		
		verificar_privilegio( PRIV_EST_OPAC, 1 );
	}
	else if( $the_action == "est_circulacion" )
	{
		$descrip_action = $HINT_FOR_ACTION_EST_CIRCULATION;
		$target_url	    = "est_estadistica_circulacion.php";
		$hint			= $HINT_FOR_ENTERING_USER_4_CIRCULATION;
		
		verificar_privilegio( PRIV_EST_CIRCULATION, 1 );
	}		
	
	else
		$descrip_action = $the_action;

	$usuario 		= getsessionvar( "usuario" );
	$id_biblioteca 	= getsessionvar( "id_biblioteca" );

	$id_usuario = 0;
	
	$ok_user = 0;
?>

<script type='text/javascript' src='../basic/calend.js'></script>

<link rel="stylesheet" href="../basic/calendar/calendar.css">
<script type="text/javascript" language="javascript" src="../basic/calendar/calendar_mx.js"></script>

<SCRIPT type="text/javascript" language="JavaScript">

	var user_selected = true;

	function inicializa_valores_default()
	{	
		prepareInputsForHints();
		
		document.elegir_form.fecha_desde.focus();
	}	
	
	// Continuar seleccionando las fechas
	function Continuar()
	{	
		var error = 0;

		if( !EsFechaValida( document.elegir_form.fecha_desde ) )
		{
			error = 1;
			alert( "<?php echo $VALIDA_MSG_DATE1_WRONG;?>" );
			document.elegir_form.fecha_desde.focus();
		}
		
		if( error == 0 )
		{
			if( !EsFechaValida( document.elegir_form.fecha_hasta ) )
			{
				error = 1;
				alert( "<?php echo $VALIDA_MSG_DATE2_WRONG;?>" );
				document.elegir_form.fecha_hasta.focus();
			}		
		}
		
		if( error == 0 )
		{
			if( !Validar2Fechas( document.elegir_form.fecha_desde.value, document.elegir_form.fecha_hasta.value ) )
			{
				error = 1;
				alert( "<?php echo $VALIDA_MSG_DATES_ARE_WRONG;?>" );
				document.elegir_form.fecha_hasta.focus();
			}
		}
		
		if( error == 0 )
		{		
			// CONTINUAR HACIA LA SIGUIENTE 
			frames.location.href = "<?php echo $target_url;?>?desde=" + document.elegir_form.fecha_desde.value + "&hasta=" + document.elegir_form.fecha_hasta.value;
			return true;
		}
		else 
			return false;
	}

	function key_pressed(myfield,e)
	{
		var keycode;
	
		if (window.event) keycode = window.event.keyCode;
		else if (e) keycode = e.which;
		else return true;
	
		//alert( keycode );
	
		if (keycode == 13)
		{
			if( !Continuar() )
				return false;
			else			
				return true;
		}
		else
		{
			return true;
		}
	}
	

</SCRIPT>

<STYLE>

	.sp_hint { width: 300px; }
	
	#caja_datos {  
	    float: none;
		width: 120%; 
	}
	
</STYLE>

</head>

<body id="home" onLoad='javascript:inicializa_valores_default();'>

<?php 
  display_global_nav();  // barra de navegación superior
 ?>

<!-- contenedor principal -->
<div id="contenedor" class="contenedor">

<?php
	display_banner();      // banner   
	display_menu( "../" ); // menu principal
?>  
 
	<div id="bloque_principal">
        
		<div id="contenido_principal">
			<div class="caja_datos" id="caja_datos"> 
				<h2><?php echo $LBL_HEADER_V2 . " " . $descrip_action; ?></h2>
				<hr>
				<h4><?php echo $LBL_CAPTION_1;?></h4><br>
            
				<form name="elegir_form" id="elegir_form" class="forma_captura" method='post'>
					<input type="hidden" class="hidden" name="the_action" id="the_action" value="<?php echo $the_action;?>">
				
					<dt>
						<label for="fecha_desde"><?php echo $LBL_DESDE; ?></label>
					</dt>
					<dd>				
						<?php 
						  colocar_edit_date_v2( "elegir_form", "fecha_desde", $fecha_desde, 0, "" ); 
						 ?>
						 <span class="sp_hint"><?php echo $hint;?><span class="hint-pointer">&nbsp;</span></span>
					</dd>
					
					<dt>
						<label for="fecha_hasta"><?php echo $LBL_HASTA; ?></label>
					</dt>
					<dd>				
						<?php 
						  colocar_edit_date_v2( "elegir_form", "fecha_hasta", $fecha_hasta, 0, " onkeypress='return key_pressed(this,event);' " ); 
						 ?>
					</dd>
					<br>				
				
				</form>
				
				<br>
				<div id="buttonarea">
					<input class='boton' type='button' onClick='javascript:Continuar()' value='<?php echo $BTN_CONTINUE;?>'>
					<input class='boton' type='button' name='regresa' onClick='javascript:window.history.back()' value="<?php echo $BTN_GOBACK;?>">
				</div>
				
				<br>
				<br>
				
			</div> <!-- - caja datos -->	   
			
			<br>
			
        </div>  <!-- contenido pricipal -->
		
		<?php  display_copyright(); ?>
		
    </div> <!--bloque principal-->
 </div>  <!-- contenedor pricipal -->

</body>
</html>