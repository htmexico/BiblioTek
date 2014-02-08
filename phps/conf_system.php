<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  17 mar 2009: Se crea el archivo PHP de configuración.
	  29 mar 2009: Se agrega registro de acceso en el log de actividades
	  04 sep 2009: Se coloca verificacion de privilegios de acceso
	  17 nov 2009: Se agregan campos de email sender y responder
	  18 nov 2009: Se agrega botón de pagos y facturación (para usuario admin)
	  08 jun 2011: Se agregan botones de pago.
	  10 nov 2011: Corregidos errores de visualizacion de datos e inciializacion de pais, vista de hints
     */
		
	include "../funcs.inc.php";
	include "../actions.inc.php";
	include "../basic/bd.class.php";
	
	include_language( "global_menus" );

	check_usuario_firmado(); 

	include_language( "conf_system" );
		
	$nombre_biblioteca = "";
	$nombre_director   = "";
	$email_director    = "";
	$domicilio		   = "";
	$ciudad			   = "";
	$provincia		   = "";
	$pais 			   = "";
	$telefonos		   = "";
	
	$x_skin			   = "";
	$x_file_banner	   = "";
	$x_language		   = "";
	
	$email_default_sendername = "";
	$email_default_responder  = "";
	
	$cuenta_activa = "";
	$tipo_servicio = "";

	$action = "";
	
	if( isset( $_POST["action"] ) )
		$action = $_POST["action"];
		
	$db = new DB();
		
	if( $action == "save" )
	{
		$nombre_biblioteca = $_POST["txt_nombre_biblioteca"];
		$nombre_director   = $_POST["txt_nombre_director"];
		$email_director    = $_POST["txt_email_director"];
		$domicilio		   = $_POST["txt_domicilio"];
		$ciudad			   = $_POST["txt_ciudad"];
		$provincia		   = $_POST["txt_provincia"];
		$pais 			   = $_POST["cmb_pais"];
		$telefonos		   = $_POST["txt_telefonos"];
		
		$x_skin			   = $_POST["cmb_tema"];
		$x_file_banner	   = "NO USADO"; // $_POST["txt_banner"];
		$x_language		   = $_POST["cmb_idioma"];
		
		// first UPDATE
		$update_query  = "UPDATE cfgbiblioteca SET NOMBRE_BIBLIOTECA='$nombre_biblioteca', NOMBRE_DIRECTOR='$nombre_director',";
		$update_query .= "  EMAIL_DIRECTOR='$email_director', DOMICILIO='$domicilio', CIUDAD='$ciudad', PROVINCIA='$provincia', PAIS='$pais', TELEFONOS='$telefonos', ";
		$update_query .= "  TEMA='$x_skin', ARCHIVO_BANNER='$x_file_banner', IDIOMA='$x_language' ";
		$update_query .= "WHERE ID_BIBLIOTECA=" . getsessionvar( "id_biblioteca");
		
		$db->ExecSQL( $update_query );
		
		$email_default_sendername	   = $_POST["txt_def_email_sender"];
		$email_default_responder   = $_POST["txt_def_email_responder"];		
		
		// second UPDATE
		$update_query  = "UPDATE cfgbiblioteca_config SET DEFAULT_EMAIL_SENDERNAME='$email_default_sendername', DEFAULT_EMAIL_RESPONDER='$email_default_responder' ";
		$update_query .= "WHERE ID_BIBLIOTECA=" . getsessionvar( "id_biblioteca");
		
		$db->ExecSQL( $update_query );		
		
		setsessionvar( "skin", "$x_skin" );
		//setsessionvar( "file_banner", "$file_banner" );
		setsessionvar( "language_pref", "$x_language" );		
		
		if( getsessionvar("language_pref")=="Spanish" )
			setsessionvar( "language", 1 );
		else if( getsessionvar("language_pref")=="English")
			setsessionvar( "language", 2 );
		else if( getsessionvar("language_pref")=="Portuguese")
			setsessionvar( "language", 3 );		
		
		agregar_actividad_de_usuario( CFG_CHANGE_LIBRARY_DATA, "" );
		
		require_once( "email_factory.inc.php" );
		process_email( $db, getsessionvar("id_biblioteca"), -1, EMAIL_ORG_INFO_UPDATED );
		
		$error = 10;
		
		$resultqry = $db->Open( "SELECT a.CUENTA_ACTIVA, b.DEFAULT_EMAIL_SENDERNAME, b.DEFAULT_EMAIL_RESPONDER, c.DESCRIPCION, c.DESCRIPCION_ENG, c.DESCRIPCION_PORT " . 
							    " FROM cfgbiblioteca a LEFT JOIN cfgbiblioteca_config b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA) " .
								"   LEFT JOIN cfgservicios c ON (c.ID_TIPOSERVICIO=a.ID_TIPOSERVICIO) " . 
							    "WHERE a.ID_BIBLIOTECA=" . getsessionvar( "id_biblioteca") );
				
		if( $db->NextRow() ) 
		{ 	
			$cuenta_activa = $db->row["CUENTA_ACTIVA"];

		}
		
		$db->Close();
	}
	else
	{
		$resultqry = $db->Open( "SELECT a.*, b.DEFAULT_EMAIL_SENDERNAME, b.DEFAULT_EMAIL_RESPONDER, c.DESCRIPCION, c.DESCRIPCION_ENG, c.DESCRIPCION_PORT " . 
							    " FROM cfgbiblioteca a LEFT JOIN cfgbiblioteca_config b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA) " .
								"   LEFT JOIN cfgservicios c ON (c.ID_TIPOSERVICIO=a.ID_TIPOSERVICIO) " . 
							    "WHERE a.ID_BIBLIOTECA=" . getsessionvar( "id_biblioteca") );
				
		if( $db->NextRow() ) 
		{ 
			$nombre_biblioteca = $db->row["NOMBRE_BIBLIOTECA"];
			$nombre_director   = $db->row["NOMBRE_DIRECTOR"];
			$email_director    = $db->row["EMAIL_DIRECTOR"];
			$domicilio 		   = $db->row["DOMICILIO"];
			$ciudad		 	   = $db->row["CIUDAD"];
			$provincia	 	   = $db->row["PROVINCIA"];
			$pais		 	   = $db->row["PAIS"];
			$telefonos		   = $db->row["TELEFONOS"];
			
			$x_skin		   	   = $db->row["TEMA"];
			$x_file_banner	   = "NO USADO"; //$db->row["ARCHIVO_BANNER"];
			
			$x_language		   = $db->row["IDIOMA"];
			
			$email_default_sendername = $db->row["DEFAULT_EMAIL_SENDERNAME"];
			$email_default_responder  = $db->row["DEFAULT_EMAIL_RESPONDER"];
			
			$cuenta_activa = $db->row["CUENTA_ACTIVA"];
			
			$tipo_servicio = get_translation( $db->row["DESCRIPCION"], $db->row["DESCRIPCION_ENG"], $db->row["DESCRIPCION_PORT"] );
		}
		
		$db->Close();
		
		$error = 0;
	}
	
	$db->Destroy();
	
	// Coloca un encabezado HTML <head>
	include "../basic/head_handler.php";
	HeadHandler( "$LBL_CFG_TITLE", "../");
	
	include( "../privilegios.inc.php" );
	verificar_privilegio( PRIV_CONFIGINFOLIBRARY, 1 );
	
?>

<SCRIPT language="JavaScript" type="text/javascript">

	function validar( e )
	{
		var error = 0;
		
		if( document.cfg_system_form.txt_nombre_director.value == "" )		
		{
			error = 1;
			alert( "Teclee el nombre del director" );
		}
		
		if( document.cfg_system_form.txt_domicilio.value == "" )		
		{
			error = 1;
			alert( "Teclee un domicilio" );
		}

		if( error == 0 )
		{
			document.cfg_system_form.submit();
			return true;
		}
		else
			return false;
	}
	
	
	function payments()
	{
		js_ProcessActionURL( 1, "http://www.escolarhitech.com.mx/hitech_payment.php", "win_payments", screen.width-100, 600 );
	}
	
	function display_paybill()
	{
		var usd = js_getElementByName( 'pago_usd' );
		var mxn = js_getElementByName( 'pago_mxn' );
		
		var pais = js_getElementByName_Value( 'cmb_pais' );
		
		if( pais == "MEXICO" )
		{
			mxn.style.display = "block";
			usd.style.display = "none";
		}
		else 
		{
			usd.style.display = "block";
			mxn.style.display = "none";
		}
		
		prepareInputsForHints();
	}
	
</SCRIPT>

<STYLE type="text/css">

 .sp_hint { width: 150px; }

 #caja_datos1 {
   width: 750px; 
   }
  
 form.forma_captura label {
    width: 18em;
 }    
 
 #contenido_adicional
 {
	width: 170px;
 }
  
</STYLE>

<body id="home" onLoad='javascript:display_paybill()'>

<?php
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
 
<div id="bloque_principal"> <!-- inicia contenido -->
 
 <div id="contenido_principal">

	<div class=caja_datos id=caja_datos1 >
		<h2><?php echo $LBL_LOGIN_HEADER;?></h2><hr><br>
		
		<?php
			if( $error == 2 )
			{
				echo "<div class=caja_errores>";
				echo " <strong>Error: Al guardar los cambios.</strong>";
				echo "</div>";
			}
			else if( $error == 10 )
			{
				echo "<div class=caja_info>";
				echo " <strong>$SAVE_DONE</strong>";
				echo "</div>";
			}			
		?>

			<form action="conf_system.php" method="post" name="cfg_system_form" id="cfg_system_form" class="forma_captura">
			  <input class=hidden type=hidden name=action id=action value="save">
			  
				<dt>
					<label for="txt_nombre_biblioteca"><strong><?php echo $LBL_NAME_LIBRARY;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_nombre_biblioteca" id="txt_nombre_biblioteca" value="<?php echo $nombre_biblioteca;?>" size=65>
					<span class="sp_hint"><?php echo $HINT_NAME_OF_LIBRAY;?><span class="hint-pointer">&nbsp;</span></span>					
				</dd>
			  
				<dt>
					<label for="txt_nombre_director"><strong><?php echo $LBL_NAME_DIRECTOR;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_nombre_director" id="txt_nombre_director" value="<?php echo $nombre_director;?>" size=65>
					<span class="sp_hint"><?php echo $HINT_NAME_DIRECTOR;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
			  
				<dt>
					<label for="txt_email_director"><strong><?php echo $LBL_EMAIL_DIRECTOR;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_email_director" id="txt_email_director" value="<?php echo $email_director;?>" size=65>
					<span class="sp_hint"><?php echo $HINT_EMAIL_DIRECTOR;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>

				<dt>
					<label for="txt_domicilio"><strong><?php echo $LBL_ADDRESS;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_domicilio" id="txt_domicilio" value="<?php echo $domicilio;?>" size=65>
					<span class="sp_hint"><?php echo $HINT_ADDRESS;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>				
			
				<dt>
					<label for="txt_ciudad"><strong><?php echo $LBL_CITY;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_ciudad" id="txt_ciudad" value="<?php echo $ciudad;?>" size=50>
					<span class="sp_hint"><?php echo $HINT_CITY;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
			  
				<dt>
					<label for="txt_provincia"><strong><?php echo $LBL_STATE;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_provincia" id="txt_provincia" value="<?php echo $provincia;?>" size=50>
					<span class="sp_hint"><?php echo $HINT_STATE;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
			  
				<dt>
					<label for="cmb_pais"><strong><?php echo $LBL_COUNTRY;?></strong></label>
				</dt>
				<dd>
					<select class="select_captura" name="cmb_pais" id="cmb_pais" onChange='javascript:display_paybill();'>
						<option value='MEXICO' <?php echo ($pais=="MEXICO" ? "selected": ""); ?>>M&eacute;xico</option>
						<option value='USA'>U.S.A.</option>
						<option value='CANADA'>Canad&aacute;</option>
						<option value='ECUADOR' <?php echo ($pais=="ECUADOR" ? "selected": ""); ?>>Ecuador</option>
						<option value='PANAMA' <?php echo ($pais=="PANAMA" ? "selected": ""); ?>>Panam&aacute;</option>
						<option value='BRAZIL' <?php echo ($pais=="BRAZIL" ? "selected": ""); ?>>Brazil</option>
						<option value='PERU'>Per&uacute;</option>
						<option value='ARGENTINA' <?php echo ($pais=="ARGENTINA" ? "selected": ""); ?>>Argentina</option>
						<option value='COLOMBIA' <?php echo ($pais=="COLOMBIA" ? "selected": ""); ?>>Colombia</option>
						<option value='GUATEMALA'>Guatemala</option>	
						<option value='CHILE'>Chile</option>
					</select>
					<span class="sp_hint"><?php echo $HINT_COUNTRY;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>

				<dt>
					<label for="txt_telefonos"><strong><?php echo $LBL_PHONE;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_telefonos" id="txt_telefonos" value="<?php echo $telefonos;?>" size=50>
					<span class="sp_hint"><?php echo $HINT_PHONE;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>

				<dt>
					<label for="cmb_tema"><strong><?php echo $LBL_SKIN;?></strong></label>
				</dt>
				<dd>
					<select class="select_captura" name="cmb_tema" id="cmb_tema">
						<option value='Default' <?php echo ($x_skin=="Default" ? "selected": ""); ?> >DEFAULT</option>
						<option value='Serio' <?php echo ($x_skin=="Serio" ? "selected": ""); ?> >SERIO</option>
						<option value='Green' <?php echo ($x_skin=="Green" ? "selected": ""); ?> >GREEN</option>
						<option value='Pink' <?php echo ($x_skin=="Pink" ? "selected": ""); ?> >PINK</option>
						<option value='Bubbles' <?php echo ($x_skin=="Bubbles" ? "selected": ""); ?> >BUBBLES</option>
						<option value='Yellow' <?php echo ($x_skin=="Yellow" ? "selected": ""); ?> >YELLOW</option>
						<option value='Elegant' <?php echo ($x_skin=="Elegant" ? "selected": ""); ?> >ELEGANT</option>
					</select>
					<span class="sp_hint"><?php echo $HINT_SKIN;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>

				<dt>
					<label for="cmb_idioma"><strong><?php echo $LBL_LANGUAGE_PREF;?></strong></label>
				</dt>
				<dd>
					<select class="select_captura" name="cmb_idioma" id="cmb_idioma">
						<option value='Spanish' <?php echo ($x_language=="Spanish" ? "selected": ""); ?> >Espa&ntilde;ol</option>
						<option value='English' <?php echo ($x_language=="English" ? "selected": ""); ?> >English</option>
						<option value='Portuguese' <?php echo ($x_language=="Portuguese" ? "selected": ""); ?> >Portugu&eacute;s</option>
					</select>				
					<span class="sp_hint"><?php echo $HINT_LANGUAGE; ?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<!--
				<dt>
					<label for="txt_banner"><strong><?php echo $LBL_IMGFILE_BANNER;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_banner" id="txt_banner" value="<?php echo $file_banner;?>" size=50>
					<span class="sp_hint"><?php echo $HINT_BANNER;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>				
				-->
				<br> 
				
				<dt>
					<label for="txt_banner"><strong><?php echo $LBL_DEFAULT_EMAIL_SENDER;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_def_email_sender" id="txt_def_email_sender" value="<?php echo $email_default_sendername;?>" size=50>
					<span class="sp_hint"><?php echo $HINT_EMAILSENDER;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<br>

				<dt>
					<label for="txt_banner"><strong><?php echo $LBL_DEFAULT_EMAIL_RESPONDER;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_def_email_responder" id="txt_def_email_responder" value="<?php echo $email_default_responder;?>" size=50>
					<span class="sp_hint"><?php echo $HINT_EMAILRESPONDER;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<br>
				
				<dt>
					<label><strong><?php echo $LBL_SERVICE;?></strong></label>
				</dt>
				<dd>
					<?php 
					    echo $tipo_servicio;
					 ?>
				</dd>				
				<br>
				<dt>
					<label><strong><?php echo $LBL_ACTIVE_ACCOUNT;?></strong></label>
				</dt>
				<dd>
					<?php 
					    echo ICON_DisplayYESNO( $cuenta_activa );
					 ?>
				</dd>				
				
				<br>
			  
			  <div id="buttonarea">
				<input id=btnActualizar class="boton" type="button" value="<?php echo $BTN_SAVE;?>" name="btnActualizar" onClick='javascript:validar();'>
				<input id=btnCancelar class="boton" type="button" value="<?php echo $BTN_CANCEL;?>" name="btnCancelar" onClick='javascript:window.history.back();'>&nbsp;&nbsp;
				
				<?php 
				  if( getsessionvar("isadmin") == 1 )
				  {
					//echo "<input id='btnPayments' class='boton' type='button' value='$BTN_PAYMENTS' name='btnPayments' onClick='javascript:payments();'>";					
					
				  }
				?>
			  </div>
			  
			  <br style='clear:both;'>

			</form>
			
			<br style='clear:both;'>
	  
	</div> <!-- caja_datos --> 

 </div> <!-- contenido_principal -->

 <div id="contenido_adicional">
	<?php echo $NOTES_AT_RIGHT; 
	
	echo "<br><br><hr>$NOTES_PAYMENTS<br><br>";
	
					echo '					
<!-- USD -->
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="display:none;"  name="pago_usd" id="pago_usd"  target="_blank">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="H8YU3748LQWBC">	
<input type="image" src="https://www.paypalobjects.com/es_XC/MX/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal, la forma más segura y rápida de pagar en línea en USD.">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
					';
					
					echo '					
					
<!-- PESOS -->					
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="display:none;" name="pago_mxn" id="pago_mxn" target="_blank">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="JCW9H8AFC6KRA">
<input type="image" src="https://www.paypalobjects.com/es_XC/MX/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal, la forma más segura y rápida de pagar en línea en PESOS Mexicanos.">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
					';		
	
	?>
	
  </div> <!-- contenido_adicional -->

</div>
<!-- end div bloque_principal -->

<?php  display_copyright(); ?>

</div><!-- end div contenedor -->

</body>

</html>