<?php
	session_start();

	/**********
		HISTORIAL DE CAMBIOS
		
		26-Jun-2009		Se crea el archivo serv_sanciones.php
		23-oct-2009		
		
		PENDIENTE:
			
			Mayor Capacidad en material solicitado.
		
	**********/		

	include ("../funcs.inc.php");
	include ("../basic/bd.class.php");
	include_language( "global_menus" );
	
	check_usuario_firmado(); 

	include_language( "serv_sanciones" ); // archivo de idioma

	$id_usuario		= read_param( "id_usuario", 0, 1 ); // fail

	$usuario = getsessionvar('usuario');
	$id_biblioteca = getsessionvar('id_biblioteca');

	$items_actualmente_prestados = 0;
	
	$nombre_usuario = "";
	$admite_sancion_econmica = ""; 
	$admite_sancion_horas    = "";
	$admite_sancion_especie  = "";
	
	$error = 0;
	$error_message = "";
	$info  = 0;
	
	$forma_pago_sancion = "";
	$motivo = "";
	$monto = 0;
		
	include ( "../basic/head_handler.php" );  // Coloca un encabezado HTML <head>
	HeadHandler( $LBL_HEADER, "../" );
	
	$curdate = getcurdate_human_format();
		
?>

<script type='text/javascript' src='../basic/calend.js'></script>

<SCRIPT type="text/javascript" language="JavaScript">

	var solo_numeros = false; // only numbers

	function inicializa_valores_default()
	{			
		prepareInputsForHints();
		
		Seleccionar();
	}	
	
	function Seleccionar()
	{
		var sel_sancion = js_getElementByName("sel_sancion"); // lista de tipos de sancion 
      
	    solo_numeros  = false;
	  
		if( sel_sancion )
		{  
			var valores_sancion = sel_sancion.value.split(";"); // 5 valores que vienen separados x puntosycomas
			  
			var var_tipo_sancion = js_getElementByName("tipo_sancion");
			var var_lbl_descrip_monto = js_getElementByName("lbl_descrip_monto");			  
			var var_txt_monto = js_getElementByName("txt_monto");
			
			if ( var_tipo_sancion )
				var_tipo_sancion.value = valores_sancion[0];  //Propiedad Value para inputs
			  
			if ( var_lbl_descrip_monto )
				var_lbl_descrip_monto.innerHTML = valores_sancion[1];  //Propiedad innerHTML para labels
			  
			if ( var_txt_monto )
			{
				if( valores_sancion[4] == "H" )  // Horas
				{
					var_txt_monto.style.width = "50px";  // 50px					
					solo_numeros  = true;
				}
				else if( valores_sancion[4] == "$" )  // Cantidad
				{
					var_txt_monto.style.width = "100px"; // 100px
					
					if( var_txt_monto.value == "" )
					{
						if( valores_sancion[2] != "0" )
							var_txt_monto.value = valores_sancion[2]; // Propiedad de valor MONTO FIJO
						else if( valores_sancion[3] != "0" )
							var_txt_monto.value = valores_sancion[3]; // Propiedad de valor MONTO X DIA
					}	
					solo_numeros  = true;
				}
				else  // Descrip
				{
					if( validarNumero( document.agregar_form.txt_monto.value) )  // si hay un número: QUITARLO
						var_txt_monto.value = "";
					
					var_txt_monto.style.width = "450px"; // 450px
					//var_txt_monto.size = "100";
				}
				
			}
		}
	}

	function Ocultar_Info_Usuario()
	{
		var div_info = js_getElementByName("caja_info"); // lista de tipos de sancion 
		
		if( div_info )
		{
			div_info.style.display = "none";
			div_info.style.marginBottom = "0px";
			div_info.innerHTML = "";
		}
	}
	
	// tipo  0 = Error,   1 = Info
	function Habilitar_Info_Usuario( msg, tipo )
	{
		var div_info = js_getElementByName("caja_info"); // lista de tipos de sancion 
		
		if( div_info )
		{
			div_info.style.display = "block";
			div_info.style.marginBottom = "10px";
			div_info.innerHTML = "<strong>" + msg + "</strong>";
			div_info.className = (tipo==0) ? "caja_errores" : "caja_info";
		}
	}	

	function Registra()
	{	
		var fecha_limite   = document.agregar_form.fecha_limite.value;
		var monto_total    = document.agregar_form.txt_monto.value;
		var motivo_sancion = document.agregar_form.txt_motivo.value;
		var error         = 0;		
		
		Ocultar_Info_Usuario();
		
		if( error == 0 )
		{
			if( !EsFechaValida( document.agregar_form.fecha_limite ) )
			{
				Habilitar_Info_Usuario( "<?php echo $ALERT_DATELIMIT_WRONGFORMAT;?>", 0 );
				document.agregar_form.fecha_limite.focus();
				error = 1;
			}
		}
		
		if( error == 0 )
		{
			if( !Validar2Fechas( "<?php echo $curdate ?>", fecha_limite ) )
			{
				Habilitar_Info_Usuario( "<?php echo $ALERT_DATES_WRONG;?>", 0 );
				document.agregar_form.fecha_limite.focus();
				error = 1;			
			}
		}
		
		if( error == 0 )
		{
			if (  monto_total == "" || motivo_sancion == "" )
			{
					Habilitar_Info_Usuario(  "<?php echo $ALERT_DETAILS_NEEDED;?>", 0 );
					document.agregar_form.txt_monto.focus();
					error = 1;
			}
		}
		
		if( error == 0 )
		{
			if( solo_numeros )
			{	
				if( !validarNumero( document.agregar_form.txt_monto.value ) )
				{
					Habilitar_Info_Usuario( "<?php echo $ALERT_CURRENCY_VALUE_NEEDED; ?>", 0 );
					document.agregar_form.txt_monto.focus();
					error = 1;
				}
			}
		}
		
		if( error == 0 )
		{
			if( confirm( "<?php echo $MSG_WANT_TO_SAVE; ?>" ) )
			{
				document.agregar_form.the_action.value = "save";
				document.agregar_form.method = "post";
				document.agregar_form.action = "serv_sanciones_paso2.php";
				document.agregar_form.submit();
			}
		}
	}
	
	function validarNumero(valor)
	{ 
		if( valor == "" )
			return false;

		//Compruebo si es un valor numérico 
		if ( isNaN(valor) ) 
		{ 
            // no es un numero
			return false;			
		}
		else
		{ 
			// entonces (Si era un número) devuelvo el valor  
			return true; 
		} 
	} 
	
	// AUXILIAR en la edición
	function local_extractNumber(obj, decimalPlaces, allowNegative)
	{
		if( solo_numeros )
			extractNumber(obj, decimalPlaces, allowNegative);
	}

	function local_blockNonNumbers(obj, e, allowDecimal, allowNegative)
	{
		if( solo_numeros )  // solo numeros
		   return blockNonNumbers(obj, e, allowDecimal, allowNegative)
		else
		   return true;  // aceptar todos 
	}
	
</SCRIPT>

<STYLE>

    .sp_hint { width: 300px; }
   
	#nombre_usuario { 
		display: inline; 
		position: absolute;
		left: 13em;
		width: 45em; 
		border: 1px dotted green; 
		background: transparent;
		padding: 3px;
	}
	
	#caja_datos {
		width: 140%; 
	}

	#buttonarea { 
		border: 0px solid red;  
		left: 160px;
	} 
	
	#contenedor {
		background: #FFF;
	}

</STYLE>

<body id="home" onLoad='javascript:inicializa_valores_default();'>

<?php
  
  display_global_nav();  // barra de navegación superior

 ?>

<!-- contenedor principal -->
<div id="contenedor" class="contenedor">

<?php

	display_banner();    // banner   
	display_menu('../'); // menu principal
?>  
 
	<div id="bloque_principal">
        <div id="contenido_principal">
			<div class="caja_datos" id="caja_datos"> 
				<H1><?php echo $LBL_HEADER;?></H1>
				<HR>
				<h4><?php echo $LBL_HEADER_SUB; ?></h4>
				<br>

					<?php 
					
					//
					// VALIDAR USUARIO
					// 
					$grupo = "";

					if( $id_usuario != 0 )
					{
						require_once( "circulacion.inc.php" );

						$user = new TUser( $id_biblioteca, $id_usuario );
						
						if( $user->NOT_FOUND )
						{
							SYNTAX_JavaScript( 1, 1, "alert( '$ALERT_WRONG_USER_NOT_FOUND' );" );
							
							echo "<br><div class=caja_errores>";
							echo " <strong> $ALERT_WRONG_USER_NOT_FOUND </strong>";
							echo "</div>";				
						}
						else
						{
							if( $user->STATUS == "A" )
							{
								$nombre_usuario = $user->NOMBRE_COMPLETO;					
								$grupo = $user->NOMBRE_GRUPO;
								
								$items_actualmente_prestados = $user->ObtenerNumItemsPrestados();
								$sanciones = $user->ObtenerNumSanciones();
								
								$max_renovaciones  = $user->GRUPO_MAX_RENOVACIONES;				
								$dias_renovacion   = $user->GRUPO_DIAS_RENOVACION_DEFAULT;
								$permite_renovacion_con_retraso = $user->GRUPO_PERMITIRRENOVA_CON_RETRASO;
								
								$admite_sancion_econmica = $user->ADMITE_SANCION_ECONOMICA; 
								$admite_sancion_horas    = $user->ADMITE_SANCION_HORAS; 
								$admite_sancion_especie  = $user->ADMITE_SANCION_ESPECIE; 
								
								if ( ($admite_sancion_econmica == "N") and 
									 ($admite_sancion_horas == "N") and 
									 ($admite_sancion_especie == "N"))  // al grupo no se le registran sanciones. 
								{
									$error = 1;
									$error_message = $VALIDA_MSG_IFGROUP;										
								}								
							}
							else 
							{
								$error = 2;
								$error_message = $VALIDA_MSG_STATUS;
							}
						}
						
						$user->destroy();
					}
					
					if( $error != 0 )
					{
						echo "<br><div class='caja_errores'>";
						echo " <strong>&nbsp;&nbsp;&nbsp;$error_message</strong>";
						echo "</div>";
					}
					
/**					if( $info != 0 )
					{
						echo "<br><div class='caja_info'>";
						echo " <strong>&nbsp;&nbsp;&nbsp;$info_message</strong>";
						echo "</div>";
					} **/
					
					echo "<div id='caja_info' name='caja_info' style='display:none' >";
					echo "	<strong>&nbsp;</strong>";
					echo "</div>";
							
					$db = new DB;
							
					 ?>
            
				<form name="agregar_form" id="agregar_form" class="forma_captura">
				
					<input type="hidden" class="hidden" value="" name="the_action" id="the_action">
					<input type="hidden" class="hidden" name="id_usuario" id="id_usuario" value="<?php echo $id_usuario;?>">
					
					<input type="hidden" class="hidden" name="tipo_sancion" id="tipo_sancion" value="">
					
					<label for="txt_id_usuario"><?php echo $LBL_USUARIO; ?></label>
					
					&nbsp;&nbsp;
					<span id="nombre_usuario" name="nombre_usuario">
						<img src="../images/icons/user.gif">&nbsp;

						<?php 
							echo $nombre_usuario . "<br> $grupo "; 

							if( $items_actualmente_prestados > 0 )
							{
								echo "&nbsp;<img src='../images/icons/warning.gif'>&nbsp;";
								echo sprintf( $HINT_MAX_ITEMS_ALREADY_HAD, $items_actualmente_prestados );
							}

						?>

					</span>					
					
					<br>						
					<br>
					<br>					
					
					<!-- fechad de registro -->
					
					<dt>
						<label><?php echo $LBL_DATE_REGISTRO; ?></label>
					</dt>
					<dd>
						<?php 
							echo "<strong>$curdate</strong>";
						?>
					</dd>
					<br>

					<!-- fecha limite -->
					<dt>
						<label for="fecha_limite"><?php echo $LBL_DATE_LIMITE; ?></label>
					</dt>
					<dd>
						<?php colocar_edit_date( "fecha_limite", $curdate, 0, "" ); ?>
						<span class="sp_hint"><?php echo $HINT_DATE_LIMITE;?><span class="hint-pointer">&nbsp;</span></span>
					</dd>
					<br>						
					
					<!-- tipo de sanción -->
					<dt>
						<label for="sel_sancion"><?php echo $LBL_TIPO_SANCION; ?></label>
					</dt>
					<dd>
				<?php
					$db->sql = "SELECT TIPO_SANCION, DESCRIPCION, ECONOMICA_SN, ECONOMICA_MONTO_FIJO, ECONOMICA_MONTO_X_DIA, LABOR_SOCIAL_SN, LABOR_SOCIAL_HORAS, ESPECIE_SN ".
							   "FROM cfgsanciones " .
							   "WHERE ID_BIBLIOTECA=$id_biblioteca ";
							   
					$condiciones = "";
							   
					if( $admite_sancion_econmica == "S" )
						$condiciones = "(ECONOMICA_SN = 'S') ";
						
					if( $admite_sancion_horas == "S" ) // labor social
					{
						if( $condiciones != "" ) $condiciones .= " or ";
						$condiciones .= " (LABOR_SOCIAL_SN = 'S') ";						
					}
						
					if( $admite_sancion_especie == "S" )					
					{
						if( $condiciones != "" ) $condiciones .= " or ";
						$condiciones .= " (ESPECIE_SN = 'S') ";											
					}

						
					$db->sql .= " and ($condiciones)";
					$db->sql .= "ORDER BY DESCRIPCION" ;
						
					$db->Open();
				   				   
					echo "<select name='sel_sancion' onChange='javascript:Seleccionar(this);'>";   //se agrega catalogo de sanciones en combo box
				   
					while( $db->NextRow() )
					{	
					  $forma_pago = "";
					  $importe_sancion_fijo ="";
					  $importe_sancion_xdia = "";
					  
					  $indicador = "";
												//Verificación del tipo de sanción para concatenar, horas, pesos o especie
					  if( $db->row["ECONOMICA_SN"] == "S" )
					  {
						$forma_pago = $LBL_FORMA_ECO;
						$importe_sancion_fijo = $db->row["ECONOMICA_MONTO_FIJO"];
						$importe_sancion_xdia = $db->row["ECONOMICA_MONTO_X_DIA"];
						$indicador = "$";
					  }
					  else if( $db->row["LABOR_SOCIAL_SN"] == "S" )
					  {
						$forma_pago = $LBL_FORMA_SOC;
						$importe_sancion_fijo = $db->row["LABOR_SOCIAL_HORAS"];
						$importe_sancion_xdia = $db->row["ECONOMICA_MONTO_X_DIA"];
						$indicador = "H";
					  }
					  else if( $db->row["ESPECIE_SN"] == "S" )
					  {
						$forma_pago = $LBL_FORMA_ESP;
						$indicador = "?";
					  }
					  echo "<option value='" . $db->row["TIPO_SANCION"] . ";$forma_pago;$importe_sancion_fijo;$importe_sancion_xdia;$indicador'>" . $db->FIELD("DESCRIPCION") ."</option>";

					}
					
					$db->Close();
					
					echo "</select>";			
				?>
					<span class="sp_hint"><?php echo $HINT_SELECT_SANCTION;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<br>
				
				<!-- monto, se requiere el id y name para lbl_descrip_monto -->
				<dt>
				<label for="txt_monto" id="lbl_descrip_monto" name="lbl_descrip_monto"><?php echo $forma_pago_sancion; ?></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_monto" id="txt_monto" size=40 maxlength=38 value="<?php echo $monto ;?>" 
					   onblur="local_extractNumber(this,2,false);" onkeypress="return local_blockNonNumbers(this, event, true, false);">
					<span class="sp_hint"><?php echo $HINT_TYPE_AMOUNT;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				<br>
				
				<!-- motivo -->
				<dt>	
				 <label for="txt_motivo"><?php echo $LBL_MOTIVO; ?></label>
				</dt>
				<dd>
				 <input class="campo_captura" type="text" name="txt_motivo" id="txt_motivo" size=60 maxlength=250 value="<?php echo $motivo;?>">
				 <span class="sp_hint"><?php echo $HINT_CAUSE_SANCTION;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				<br>
				<br>
					
			
				<div id="buttonarea">
					<input class='boton' type='button' align="center" name='registra' onClick='javascript:Registra()' value="<?php echo $BTN_CONTINUE;?>">&nbsp;&nbsp;
					<input class='boton' type='button' align="center" name='regresa' onClick='javascript:window.history.back()' value="<?php echo $BTN_GOBACK;?>">
				</div>
				
				<br>
			
				</form>
				
			</div> <!-- - caja datos -->	   
        </div>  <!-- contenido principal -->		
    <?php  display_copyright(); ?>
    </div> <!--bloque principal-->
 </div>  <!-- contenedor pricipal -->

</body>
</html>