<?php
	session_start();

	/**********
		
		HISTORIAL DE CAMBIOS
		
		29-Junio-2009	    Se crea el archivo genérico para buscar usuarios
		22-agosto-2009      Se anexa funcion de devoluciones
		26-agosto-2009      Se anexa función de renovaciones
		02-septiembre-2009  Se anexa función de Sanciones
		06-jun-2011:	Se implementa llamada (ajaxs_funcs.php) para agilizar la búsqueda y evitar el popup
		
	**********/		

	include ("../funcs.inc.php");
	include ("../basic/bd.class.php");
	include( "../privilegios.inc.php" );
	
	check_usuario_firmado(); 

	include_language( "global_menus" );
	include_language( "gral_elegir_usuario" ); // archivo de idioma

	$iduser			= "";
	$txt_id_usuario 	= read_param( "txt_id_usuario", "" );
	
	$the_action		= read_param( "the_action", "", 1 );  // fail if not exist
	$descrip_action	= "";
	$target_url		= "";
	$hint				= "";
	
	include ( "../basic/head_handler.php" );  // Coloca un encabezado HTML <head>
	HeadHandler( $LBL_HEADER, "../" );	
	
	if( $the_action == "reservas" )
	{
		$descrip_action = $HINT_FOR_ACTION_RESERVA;
		$target_url	    = "circ_reservaciones.php";
		$hint			= $HINT_FOR_ENTERING_USER_4_RESERVA;
		
		verificar_privilegio( PRIV_RESERVAS, 1 );
	}
	else if( $the_action == "prestamos" )
	{
		$descrip_action = $HINT_FOR_ACTION_LOAN;
		$target_url	    = "circ_prestamos.php";
		$hint			= $HINT_FOR_ENTERING_USER_4_LOAN;
		
		verificar_privilegio( PRIV_LOANS, 1 );
	}
	else if( $the_action == "devoluciones" )
	{
		$descrip_action = $HINT_FOR_ACTION_DEVS;
		$target_url	    = "circ_devoluciones.php";
		$hint			= $HINT_FOR_ENTERING_USER_4_DEVS;
		
		verificar_privilegio( PRIV_DEVOLUTIONS, 1 );
	}
	else if( $the_action == "renovaciones" )
	{
		$descrip_action = $HINT_FOR_ACTION_RENEWALS;
		$target_url	    = "circ_renovaciones.php";
		$hint			= $HINT_FOR_ENTERING_USER_4_RENEWALS;
		
		verificar_privilegio( PRIV_RENEWALS, 1 );
	}
	else if( $the_action == "sanciones" )
	{
		$descrip_action = $HINT_FOR_ACTION_SANCTIONS;
		$target_url	    = "serv_sanciones.php";
		$hint			= $HINT_FOR_ENTERING_USER_4_SANCTIONS;
		
		verificar_privilegio( PRIV_SANCTIONS, 1 );
	}
	else if( $the_action == "sanciones_cumplidas" )
	{
		$descrip_action = $HINT_FOR_ACTION_AC_SANCTIONS;
		$target_url	    = "serv_sanciones_cump.php";
		$hint			= $HINT_FOR_ENTERING_USER_4_AC_SANCTIONS;
		
		verificar_privilegio( PRIV_SANCTIONS_ACOMPLISHED, 1 );
	}		
	else if( $the_action == "restricciones" )
	{
		$descrip_action = $HINT_FOR_ACTION_RESTRICTIONS;
		$target_url	    = "serv_restricciones.php";
		$hint			= $HINT_FOR_ENTERING_USER_4_RESTRICTIONS;
		
		verificar_privilegio( PRIV_SANCTIONS, 1 );
	}	
	else
		$descrip_action = $the_action;

	$usuario = getsessionvar('usuario');
	$id_biblioteca =getsessionvar('id_biblioteca');

	$id_usuario = 0;
	
	$paterno = "";
	$materno = "";
	$nombre	 = "";
	
	$ok_user = 0;
	
?>

<script type='text/javascript' src='../ajax.js'></script>

<SCRIPT type="text/javascript" language="JavaScript">

	var user_selected = true;
	var ajaxRequest = new Request;

	/**
	
	Esta ventana abria un popup
	
	function elegirUsuario()
	{
		var nwidth = screen.width;
		var nheight = screen.height; 
		
		window.open( "gral_buscarusuario.php?return_type=USERNAME", "usuarios", "WIDTH=" + (nwidth-200) + ",HEIGHT=" + (nheight-330) + ",TOP=50,LEFT=80,resizable=yes,scrollbars=yes,status=yes" );
		window.status='';
	} **/
	
	// 06-jun-2011
	function busquedaDirecta()
	{
		var div = js_getElementByName( "nombre_usuario" );
		var val = js_getElementByName_Value("txt_id_usuario");
		
		if( val.length<4)
		{
			alert( "<?php echo $ENTER_4_LETTERS;?>" );
			document.elegir_form.txt_id_usuario.focus();
			return false;
		}
		
		// Eval code	
		ajaxRequest.submit({
			url : "ajax_funcs.php",
			params : "info=search_users&search="+val,
			xml : false,
			success : function(res, ajaxRequest)
			{
				ShowDiv( "nombre_usuario" );
				div.style.display = "block";
				div.innerHTML = res;
			}
		});

	}
	
	function inicializa_valores_default()
	{	
		prepareInputsForHints();
		
		document.elegir_form.txt_id_usuario.focus();
	}	

	// Continuar seleccionando un usuario
	function Continuar()
	{	
		// CONTINUAR HACIA LA SIGUIENTE 
		var idusuario = js_getElementByName_Value( "id_usuario" );
		
		if( idusuario == "" )
		{
			alert( "<?php echo $VALIDA_MSG_IDUSER;?>" );
			document.elegir_form.txt_id_usuario.focus();
			return false;
		}
		
		frames.location.href = "<?php echo $target_url;?>?id_usuario=" +  idusuario;
		
		return true;
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
			busquedaDirecta();
			
			return false;
		}

	}
	
	function setID( val, uname )
	{
		js_setElementByName_InitValue( "id_usuario", val )
	}
	

</SCRIPT>

<STYLE>

	.sp_hint { width: 300px; }
	
	#nombre_usuario { 
		position: relative;
		display: block; 		
		width: 35em; 
		height: auto;
		border: 1px dotted silver; 
		background: transparent;
		margin-top:5px; 
		margin-bottom:5px; 
		padding: 4px;
		left: 170px;
		overflow: auto;
	}
	
	.caja_datos {
		width: 130%; 
	}
	

</STYLE>

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

					<div id='caja_info' name='caja_info' style='display:none;' >
						<strong>&nbsp;</strong>
					</div>

				<form name="elegir_form" id="elegir_form" class="forma_captura" method='post' onSubmit='return validarUsuario();'>
				<input type="hidden" class="hidden" name="the_action" id="the_action" value="<?php echo $the_action;?>">
				<input type="hidden" class="hidden" name="id_usuario" id="id_usuario" value="">
				
				<dt>
					<label for="txt_id_usuario"><?php echo $LBL_IDUSUARIO;?></label>
				</dt>
				<dd>				
					<input class="campo_captura" type="text" name="txt_id_usuario" id="txt_id_usuario" size='45' value="<?php echo $txt_id_usuario;?>" onkeypress="return key_pressed(this,event);" style='display:inline;'>
					<!-- <input class="boton" type="button" value="?" name="btnUsuario" id="btnUsuario" onClick="javascript:elegirUsuario();"> -->
					<input class="boton" type="button" value="<?php echo $BTN_SEARCH;?>" name="btnListarUsuarios" id="btnListarUsuarios" onClick="javascript:busquedaDirecta();"> 
					<span class="sp_hint"><?php echo $hint . $SP_HINT_FOR_ENTERING_USER;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<div id='nombre_usuario' name='nombre_usuario' style='display:none'>
				</div>
				
				<br>
				<div id="buttonarea">
					<input class='boton' type='button' align="center" name='regresa' onClick='javascript:window.history.back()' value="<?php echo $BTN_GOBACK;?>">
				</div>
				
				<br>
			
				</form>
				
				<div class='caja_info'><img src='../images/some_info.gif'>&nbsp;<strong><?php echo $HINT_CHANGES_ALERT;?></strong>&nbsp;<?php echo $HINT_CHANGES_APPLIED_HERE;?></div>
				
			</div> <!-- - caja datos -->	   
			
			
			
        </div>  <!-- contenido pricipal -->
		
		<?php  display_copyright(); ?>
		
    </div> <!--bloque principal-->
 </div>  <!-- contenedor pricipal -->

</body>
</html>