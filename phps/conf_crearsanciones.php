<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  28 mar 2009: Se crea el archivo PHP para editar/crear/eliminar usuarios.
	  21 ago 2009: Se agregan campos PERMITIR_PRESTAMOS y PERMITIR_COMENTARIOS.
     */
		
	include "../funcs.inc.php";	
	
	include_language( "global_menus" );

	check_usuario_firmado(); 

	include_language( "conf_crearsanciones" );		// archivo de idioma

	/** INICIO - FUNCIONES DE MODIFICACION/CREACION/BORRADO **/
	
	include("../basic/bd.class.php");
	
	$tipo_sancion = "$LBL_TO_BE_ASIGNED";
	
	$descripcion = "";
	
	$economica_sn			= "";
	$economica_monto_fijo	= 0;
	$economica_monto_x_dia	= 0;
	
	$laborsocial_sn			= "";
	$laborsocial_hrs		= 0;
	
	$especie_sn				= "";	
	
	$the_action = read_param( "the_action", "" );
	
	$error = 0;
	
	$id_biblioteca = getsessionvar("id_biblioteca");
		
	if( $the_action == "create_new" )
	{
		// generar el nuevo ID de la sancion
		$tipo_sancion = 0;
		
		$db = new DB( "SELECT MAX(TIPO_SANCION) AS MAXID, COUNT(*) AS CUANTOS FROM cfgsanciones WHERE ID_BIBLIOTECA=$id_biblioteca" );
		
		if ($db->NextRow() ) 
			$tipo_sancion = $db->Field("MAXID") + 1;
			
		$db->FreeResultset();
		
		$descripcion			= $_POST["txt_descripcion"];
		
		$modalidad = $_POST["cmb_modalidad"];
		
		$economica_sn			= "";
		$economica_monto_fijo	= 0;
		$economica_monto_x_dia	= 0;
		
		$laborsocial_sn			= "";
		$laborsocial_hrs		= 0;
		
		$especie_sn				= "";
		
 		if( $modalidad == "E" )  // economica
		{
			$economica_sn			= "S";
			$economica_monto_fijo	= $_POST["txt_economica_monto_fijo"];
			
			if( $economica_monto_fijo == "" )
				$economica_monto_fijo = "0";
			
			$economica_monto_x_dia	= $_POST["txt_economica_monto_x_dia"];
			
			if( $economica_monto_x_dia == "" )
				$economica_monto_x_dia = "0";
		}
		if( $modalidad == "S" ) // labor social
		{
			$laborsocial_sn 		= "S";
			$laborsocial_hrs		= $_POST["laborsocial_hrs"];

			if( $laborsocial_hrs == "" )
				$laborsocial_hrs = "0";
		}
		if( $modalidad == "M" ) // especie
			$especie_sn				= "S";		

		$db->sql  = "INSERT INTO cfgsanciones ( ID_BIBLIOTECA, TIPO_SANCION, DESCRIPCION, ECONOMICA_SN, ECONOMICA_MONTO_FIJO, ECONOMICA_MONTO_X_DIA, ";
		$db->sql .= "  LABOR_SOCIAL_SN, LABOR_SOCIAL_HORAS, ESPECIE_SN ) ";
		$db->sql .= " VALUES ( $id_biblioteca, '$tipo_sancion', '$descripcion', '$economica_sn', $economica_monto_fijo, $economica_monto_x_dia, ";
		$db->sql .= "   '$laborsocial_sn', $laborsocial_hrs, '$especie_sn' ) ";
		$db->ExecSQL();
		
		require_once( "../actions.inc.php" );
		agregar_actividad_de_usuario( CFG_CONFIG_SANCTIONS_CREATE, "$ACTION_DESCRIP_CREATE $descripcion" );
		
		$error = 10;
		
		if( !allow_use_of_popups() )
			ges_redirect( "conf_sanciones.php?id_sancion_created=$tipo_sancion" );

	}
	else if( $the_action == "save_changes" )
	{
		$tipo_sancion 			= $_POST["tipo_sancion"];
		$descripcion			= $_POST["txt_descripcion"];
		
		$modalidad = $_POST["cmb_modalidad"];
		
		$economica_sn			= "";
		$economica_monto_fijo	= 0;
		$economica_monto_x_dia	= 0;
		
		$laborsocial_sn			= "";
		$laborsocial_hrs		= 0;
		
		$especie_sn				= "";
		
 		if( $modalidad == "E" )  // economica
		{
			$economica_sn			= "S";
			$economica_monto_fijo	= $_POST["txt_economica_monto_fijo"];
			
			if( $economica_monto_fijo == "" )
				$economica_monto_fijo = "0";
			
			$economica_monto_x_dia	= $_POST["txt_economica_monto_x_dia"];
			
			if( $economica_monto_x_dia == "" )
				$economica_monto_x_dia = "0";
		}
		if( $modalidad == "S" ) // labor social
		{
			$laborsocial_sn 		= "S";
			$laborsocial_hrs		= $_POST["laborsocial_hrs"];
			
			if( $laborsocial_hrs == "" )
				$laborsocial_hrs = "0";
		}
		if( $modalidad == "M" ) // especie
			$especie_sn				= "S";

		$db = new DB;
		$db->sql  = "UPDATE cfgsanciones SET TIPO_SANCION='$tipo_sancion', DESCRIPCION='$descripcion',";
		$db->sql .= "  ECONOMICA_SN='$economica_sn', ECONOMICA_MONTO_FIJO=$economica_monto_fijo, ECONOMICA_MONTO_X_DIA=$economica_monto_x_dia, ";
		$db->sql .= "   LABOR_SOCIAL_SN='$laborsocial_sn', LABOR_SOCIAL_HORAS=$laborsocial_hrs, ESPECIE_SN='$especie_sn' ";
		$db->sql .= "WHERE ID_BIBLIOTECA=$id_biblioteca and TIPO_SANCION='$tipo_sancion';";
		$db->ExecSQL();

		require_once( "../actions.inc.php" );
		agregar_actividad_de_usuario( CFG_CONFIG_SANCTIONS_EDIT, "$ACTION_DESCRIP_EDIT $descripcion" );

		$error = 20;

		if( !allow_use_of_popups() )
			ges_redirect( "conf_sanciones.php?id_sancion_edited=$tipo_sancion" );

	}
	else if( $the_action == "edit" )
	{
		$tipo_sancion = $_GET["tipo_sancion"];
		
		$db = new DB( "SELECT * FROM cfgsanciones WHERE ID_BIBLIOTECA=$id_biblioteca and TIPO_SANCION='$tipo_sancion';" );
		
		if( $db->NextRow() ) 
		{ 
			$descripcion		= $db->row["DESCRIPCION"];
			
			$economica_sn			= $db->row["ECONOMICA_SN"];
			$economica_monto_fijo	= $db->row["ECONOMICA_MONTO_FIJO"];
			$economica_monto_x_dia	= $db->row["ECONOMICA_MONTO_X_DIA"];
			
			$laborsocial_sn			= $db->row["LABOR_SOCIAL_SN"];
			$laborsocial_hrs		= $db->row["LABOR_SOCIAL_HORAS"];
			
			$especie_sn				= $db->row["ESPECIE_SN"];
			
			$the_action = "save_changes";  // acción derivada natural
		}
		
		$db->destroy();
	}
	else if( $the_action == "delete" )
	{
		$sanciones = "";
		$sanciones_borradas = 0;
		
		if( isset($_GET["sanciones"]) )
		{
			$sanciones = $_GET["sanciones"];
			
			$sanciones = str_replace( "@", "TIPO_SANCION=", $sanciones ); // 1st ocurrence
			$sanciones = str_replace( ":", " or TIPO_SANCION=", $sanciones ); // other ocurrences
			
			$db = new DB( "DELETE FROM cfgsanciones WHERE ID_BIBLIOTECA=$id_biblioteca and ($sanciones) " );
			
			$error = 30;
			
			$sanciones_borradas = $db->rowsAffected;
			
			$db->Destroy();
			
			require_once( "../actions.inc.php" );
			agregar_actividad_de_usuario( CFG_CONFIG_SANCTIONS_DELETE, "$ACTION_DESCRIP_DELETE $sanciones <$sanciones_borradas>" );
		}
		
		if( !allow_use_of_popups() )
			ges_redirect( "serv_usuariosgrupos.php?id_grupos_borrados=$grupos_borrados" );
	}
	else
	{
		$the_action = "create_new";  // acción por default
	}
	
	/** FIN FUNCIONES DE MODIFICACION/CREACION/BORRADO **/
	
	// Coloca un encabezado HTML <head>
	include "../basic/head_handler.php";
	HeadHandler( ($the_action == "create_new") ? $LBL_HEADER_V1 : $LBL_HEADER_V2, "../");
		
?>

<SCRIPT language="JavaScript">

	function validar( e )
	{
		var error = 0;
		
		if( document.edit_form.txt_descripcion.value == "" )		
		{
			error = 1;
			alert( "<?php echo $VALIDA_MSG_NONAME;?>" );
			document.edit_form.txt_descripcion.focus();
		}

		if( error == 0 )
		{
			document.edit_form.submit();
			return true;
		}
		else
			return false;
	}
	
	function ShowDiv()
	{
		var sel = js_getElementByName_Value( "cmb_modalidad" );
		
		var div1 = js_getElementByName( "datos_monetarios");
		var div2 = js_getElementByName( "datos_labor_social");
		
		div1.style.display = "none";
		div2.style.display = "none";
		
		if( sel == "E" )
			div1.style.display = "inline";
		else if( sel == "S" )
			div2.style.display = "inline";
			
	}
	
	function local_extractNumber(obj, decimalPlaces, allowNegative)
	{
		extractNumber(obj, decimalPlaces, allowNegative);
	}

	function local_blockNonNumbers(obj, e, allowDecimal, allowNegative)
	{
	   return blockNonNumbers(obj, e, allowDecimal, allowNegative)
	}	
	
	window.onload=function()
	{
		prepareInputsForHints();
		
		document.edit_form.txt_descripcion.focus();
	}	
	
	
</SCRIPT>

<STYLE>

 #caja_datos1 {
   float: left; 
   width: 750px; 
   }
  
  #buttonarea { border: 0px solid red;  }; 
  #btnActualizar { position:absolute; left:0em; } 
  #btnCancelar { position:absolute; left:17em; } 
  
form.forma_captura label 
{
   width: 15em;
}
  
<?php
	if( allow_use_of_popups() )
		echo "#contenedor { width: 900px; margin-top: 10px; } ";
?>  
  
</STYLE>

<body id="home">

<?php
  // barra de navegación superior
  if( !allow_use_of_popups() )
	display_global_nav();  
	
  if( allow_use_of_popups() )
  {
	//
	// cuando POPUPS
	//
	if( $error == 10 )
	{
		SYNTAX_BEGIN_JavaScript();
		echo "alert('$SAVE_CREATED_DONE');";
		echo "window.opener.document.location.reload();";
		echo "window.close();";		
		SYNTAX_CLOSE_JavaScript();
	}
	else if( $error == 20 )
	{
		SYNTAX_BEGIN_JavaScript();
		echo "alert('$SAVE_EDIT_DONE');";
		echo "window.opener.document.location.reload();";
		echo "window.close();";
		SYNTAX_CLOSE_JavaScript();
	}
	else if( $error == 30 )
	{
		SYNTAX_BEGIN_JavaScript();
		echo "alert('$DELETE_DONE');";
		echo "window.opener.document.location.reload();";
		echo "window.close();";
		SYNTAX_CLOSE_JavaScript();
	}		
  }
 ?>

<!-- contenedor principal -->
<div id="contenedor">

<?php 

   if( !allow_use_of_popups() )
   {
	   // banner
	   display_banner();  
	   
	   // menu principal
	   display_menu( "../" ); 
   }

 ?>
 
<div id="bloque_principal"> <!-- inicia contenido -->
 
 <div id="contenido_principal">

	<div class=caja_datos id=caja_datos1 >
		<h2>
		<?php 
		
			if( $the_action == "create_new" )
				echo $LBL_HEADER_V1;
			else
				echo $LBL_HEADER_V2;
			
		?>
		
		<HR></h2>
		
		<?php
			if( $error == 2 )
			{
				echo "<div class=caja_errores>";
				echo " <strong>$MSG_ERROR_SAVING_CHANGES</strong>";
				echo "</div>";
			}
			else if( $error == 10 )
			{
				echo "<div class=caja_info>";
				echo " <strong>$SAVE_CREATED_DONE</strong>";
				echo "</div>";
				
				// Update Parent
				SYNTAX_BEGIN_JavaScript();
				echo "window.opener.document.location.reload();";
				SYNTAX_CLOSE_JavaScript();				
			}
			else if( $error == 20 )
			{
				echo "<div class=caja_info>";
				echo " <strong>$SAVE_DONE</strong>";
				echo "</div>";
				
				// Update Parent
				SYNTAX_BEGIN_JavaScript();
				echo "window.opener.document.location.reload();";
				SYNTAX_CLOSE_JavaScript();				
			}
			
		 ?>		

			<form action="conf_crearsanciones.php" method="post" name="edit_form" id="edit_form" class="forma_captura">
			  <input class='hidden' type='hidden' name="the_action" id="the_action" value="<?php echo $the_action;?>">
			  <input class='hidden' type='hidden' name="tipo_sancion" id="tipo_sancion" value="<?php echo $tipo_sancion; ?>">
			  
				<label for="txt_tipo_sancion><strong><?php echo $LBL_TIPOSANCTION;?></strong></label>
				<span><?php echo $tipo_sancion;?></span>
				<br><br>
			  		  
				<dt>	
					<label for="txt_descripcion"><strong><?php echo $LBL_DESCRIPCION;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_descripcion" id="txt_descripcion" value="<?php echo $descripcion;?>" size=100 maxlength=250>
					<br>
					<span class="sp_hint"><?php echo $HINT_DESCRIPCION;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
							
				<dt>
					<label for="cmb_modalidad"><strong><?php echo $LBL_SANCTION_KIND;?></strong></label>
				</dt>
				<dd>
					<select class="select_captura" name="cmb_modalidad" id="cmb_modalidad" onChange='javascript:ShowDiv();'>
						<option value='E' <?php echo ($economica_sn=="S") ? "selected" : ""; ?> ><?php echo $LBL_SANCTION_KIND_OPT1;?>&nbsp;&nbsp;</option>
						<option value='S' <?php echo ($laborsocial_sn=="S") ? "selected" : ""; ?> ><?php echo $LBL_SANCTION_KIND_OPT2;?>&nbsp;&nbsp;</option>
						<option value='M' <?php echo ($especie_sn=="S") ? "selected" : ""; ?> ><?php echo $LBL_SANCTION_KIND_OPT3;?>&nbsp;&nbsp;</option>
					</select>
				</dd>				
			  
				<br>
			  
				<!-- MONETARY -->
				<div id='datos_monetarios' name='datos_monetarios' style='display:none;'>
					<dt>
						<label for="txt_economica_monto_fijo"><strong><?php echo $LBL_MONETARY_FIXED;?></strong></label>
					</dt>
					<dd>
						<input class="campo_captura" type="text" name="txt_economica_monto_fijo" id="txt_economica_monto_fijo" value="<?php echo $economica_monto_fijo;?>" size="20" maxlength="20"
							 onblur="local_extractNumber(this,2,false);" onkeypress="return local_blockNonNumbers(this, event, true, false);" >
						<span class="sp_hint"><?php echo $HINT_MONTO_FIJO;?><span class="hint-pointer">&nbsp;</span></span>
					</dd>

					<dt>
						<label for="txt_economica_monto_x_dia"><strong><?php echo $LBL_MONETARY_VAR;?></strong></label>
					</dt>
					<dd>
						<input class="campo_captura" type="text" name="txt_economica_monto_x_dia" id="txt_economica_monto_x_dia" value="<?php echo $economica_monto_x_dia;?>" size="20" maxlength="20" 
							 onblur="local_extractNumber(this,2,false);" onkeypress="return local_blockNonNumbers(this, event, true, false);">
						<span class="sp_hint"><?php echo $HINT_MONTO_X_DIA;?><span class="hint-pointer">&nbsp;</span></span>
					</dd>
					
					<br>
				</div>
			  
				
				
				<!-- SOCIAL -->
				<div id='datos_labor_social' name='datos_labor_social' style='display:none;'>
					<dt>
						<label for="txt_laborsocial_hrs"><strong><?php echo $LBL_SOCIAL_WORK_HRS;?></strong></label>
					</dt>
					<dd>
						<input class="campo_captura" type="text" name="txt_laborsocial_hrs" id="txt_laborsocial_hrs" value="<?php echo $laborsocial_hrs;?>" size="50" maxlength="50" 						
							onblur="local_extractNumber(this,0,false);" onkeypress="return local_blockNonNumbers(this, event, false, false);">
					</dd>
				</div>
			  
				<br>

				<div id="buttonarea">
					<input id="btnGuardar" name="btnGuardar" class="boton" type="button" value="<?php echo $BTN_SAVE;?>"  onClick='javascript:validar();'>&nbsp;
					<input id="btnCancelar" name="btnCancelar" class="boton" type="button" value="<?php echo $BTN_CANCEL;?>" onClick='<?php echo back_function();?>'>
				</div>
				<br> <!-- for IE -->
				
				<?php SYNTAX_JavaScript( 1, 1, "ShowDiv();" );?>
			  
			</form>
	  
	</div> <!-- caja_datos --> 

 </div> <!-- contenido_principal -->

 <div id="contenido_adicional">
	&nbsp;
  </div> <!-- contenido_adicional -->
  
  <br style='clear:both;'>

</div>
<!-- end div bloque_principal -->

<?php  if( !allow_use_of_popups() ) display_copyright(); ?>

</div><!-- end div contenedor -->

</body>

</html>