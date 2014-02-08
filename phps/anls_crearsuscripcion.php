<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  21 sep 2009: Se crea el archivo PHP para editar/crear/eliminar suscripciones.
	  
     */
		
	include "../funcs.inc.php";	
	
	include_language( "global_menus" );

	check_usuario_firmado(); 

	include_language( "anls_suscriptions" );		// archivo de idioma

	include("../basic/bd.class.php");
	
	$id_suscripcion = "$LBL_TO_BE_ASIGNED";
	
	$id_proveedor = 0;
	$observaciones = "";
	
	$periodicidad			= 0;  // tesauro
	$periodicidad_irregular	= "";
	
	$fecha_inicial	= getcurdate_human_format();
	$fecha_final	= "";
	
	$precio_suscripcion			= 0;
	$precio_papel				= 0;
	$precio_electronico			= 0;
	
	$status_suscripcion			= "";
	
	$the_action = read_param( "the_action", "" );
	
	$error = 0;
	
	$id_biblioteca = getsessionvar("id_biblioteca");
		
	if( $the_action == "create_new" )
	{
		// generar el nuevo ID de la persona
		$id_suscripcion = 0;
		
		$db = new DB( "SELECT MAX(ID_SUSCRIPCION) AS MAXID, COUNT(*) AS CUANTOS FROM suscripciones WHERE ID_BIBLIOTECA=$id_biblioteca" );
		
		if ($db->NextRow() ) 
			$id_suscripcion = $db->Field("MAXID") + 1;
			
		$db->FreeResultset();
		
		$id_proveedor		= read_param( "cmb_proveedor", 0, 1 );  // tesauro
		$observaciones		= $_POST["txt_obs"];

		$fecha_inicial		= date_for_database_updates( $_POST["txt_fecha_inicial"] );
		
		if( $_POST["txt_fecha_final"] == "" )
			$fecha_final = "NULL";
		else
			$fecha_final	= "'" . date_for_database_updates( $_POST["txt_fecha_final"] ) . "'";

		$precio_suscripcion		= $_POST["txt_precio_suscripcion"];
		$precio_papel			= $_POST["txt_precio_papel"];
		$precio_electronico		= $_POST["txt_precio_electronico"];

		$status_suscripcion		= read_param( "cmb_statussuscripcion", 5, 1 );
		
		$db->sql  = "INSERT INTO suscripciones ( ID_BIBLIOTECA, ID_SUSCRIPCION, ID_PROVEEDOR, OBSERVACIONES, ";
		$db->sql .= "   FECHA_INICIAL, FECHA_FINAL, PRECIO_SUSCRIPCION, PRECIO_PAPEL, PRECIO_ELECTRONICO, ";
		$db->sql .= "   STATUS_SUSCRIPCION ) ";
		$db->sql .= " VALUES ( $id_biblioteca, $id_suscripcion, $id_proveedor, '$observaciones', ";
		$db->sql .= "   '$fecha_inicial', $fecha_final, '$precio_suscripcion', '$precio_papel', '$precio_electronico', ";
		$db->sql .= "   '$status_suscripcion' ) ";
		$db->ExecSQL();

		require_once( "../actions.inc.php" );
		agregar_actividad_de_usuario( ANLS_SUSCRIPTS_CREATE, "$ACTION_DESCRIP_CREATE $id_proveedor / $observaciones" );

		$error = 10;

		if( !allow_use_of_popups() )
			ges_redirect( "anls_series.php?id_suscrip_created=$id_suscripcion" );

	}
	else if( $the_action == "save_changes" )
	{
		$id_suscripcion				= read_param( "id_suscripcion", "", 1 );	
		$id_proveedor				= read_param( "cmb_proveedor", 0, 1 );  // tesauro

		$observaciones				= $_POST["txt_obs"];
		
		$fecha_inicial				= date_for_database_updates( $_POST["txt_fecha_inicial"] );
		
		if( $_POST["txt_fecha_final"] == "" )
			$fecha_final = "NULL";
		else
			$fecha_final	= "'" . date_for_database_updates( $_POST["txt_fecha_final"] ) . "'";

		$precio_suscripcion		= $_POST["txt_precio_suscripcion"];
		$precio_papel			= $_POST["txt_precio_papel"];
		$precio_electronico		= $_POST["txt_precio_electronico"];

		$status_suscripcion		= read_param( "cmb_statussuscripcion", 5, 1 );

		$db = new DB;

		$db->sql  = "UPDATE suscripciones SET ID_PROVEEDOR='$id_proveedor', OBSERVACIONES='$observaciones', ";
		$db->sql .= "   FECHA_INICIAL='$fecha_inicial', FECHA_FINAL=$fecha_final, ";
		$db->sql .= "   PRECIO_SUSCRIPCION=$precio_suscripcion, PRECIO_PAPEL=$precio_papel, PRECIO_ELECTRONICO=$precio_electronico, ";
		$db->sql .= "   STATUS_SUSCRIPCION='$status_suscripcion' ";
		$db->sql .= "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_SUSCRIPCION=$id_suscripcion; ";
		$db->ExecSQL();

		require_once( "../actions.inc.php" );
		agregar_actividad_de_usuario( ANLS_SUSCRIPTS_EDIT, "$ACTION_DESCRIP_EDIT $id_proveedor / $observaciones " );

		$error = 20;

		if( !allow_use_of_popups() )
			ges_redirect( "anls_series.php?id_suscrip_edited=$id_suscripcion" );

	}
	else if( $the_action == "edit" )
	{
		$id_suscripcion				= read_param( "id_suscripcion", "", 1 );
		
		$db = new DB( "SELECT * FROM suscripciones WHERE ID_BIBLIOTECA=$id_biblioteca and ID_SUSCRIPCION=$id_suscripcion" );
		
		if( $db->NextRow() ) 
		{ 
			$id_proveedor = $db->row["ID_PROVEEDOR"];
			
			$observaciones = $db->row["OBSERVACIONES"];
			
			$fecha_inicial	= dbdate_to_human_format( $db->row["FECHA_INICIAL"], 0 );
			$fecha_final	= dbdate_to_human_format( $db->row["FECHA_FINAL"], 0 ); 
			
			$precio_suscripcion			= $db->row["PRECIO_SUSCRIPCION"];
			$precio_papel				= $db->row["PRECIO_PAPEL"];
			$precio_electronico			= $db->row["PRECIO_ELECTRONICO"];
			
			$status_suscripcion			= $db->row["STATUS_SUSCRIPCION"];
			
			$the_action = "save_changes";  // acción derivada natural
		}
		
		$db->destroy();
	}
	else if( $the_action == "delete" )
	{
		$series = "";
		$series_borradas = 0;
		
		if( isset($_GET["series"]) )
		{
			$series = $_GET["series"];
			
			$series = str_replace( "@", "ID_SUSCRIPCION=", $series ); // 1st ocurrence
			$series = str_replace( ":", " or ID_SUSCRIPCION=", $series ); // other ocurrences
			
			$db = new DB( "DELETE FROM suscripciones WHERE ID_BIBLIOTECA=$id_biblioteca and ($series) " );
			
			$error = 30;
			
			$series_borradas = $db->rowsAffected;
			
			$db->Destroy();
			
			require_once( "../actions.inc.php" );
			agregar_actividad_de_usuario( ANLS_SUSCRIPTS_DELETE, "$ACTION_DESCRIP_DELETE $series <$series_borradas>" );
		}
		
		if( !allow_use_of_popups() )
			ges_redirect( "anls_series.php?id_suscrip_deleted=$series_borradas" );
	}
	else
	{
		$the_action = "create_new";  // acción por default
	}
	
	/** FIN FUNCIONES DE MODIFICACION/CREACION/BORRADO **/
	
	// Coloca un encabezado HTML <head>
	include "../basic/head_handler.php";
	HeadHandler( ($the_action == "create_new") ? $LBL_HEADER_ON_CREATENEW: $LBL_HEADER_ON_EDIT, "../");
		
?>

<script type='text/javascript' src='../basic/calend.js'></script>

<script language="JavaScript">

	function validar()
	{
		var error = 0;
		
		if( document.edit_form.txt_obs.value == "" )		
		{
			error = 1;
			alert( "<?php echo $VALIDA_MSG_NONAME;?>" );
			document.edit_form.txt_obs.focus();
		}

		if( error == 0 )
		{
			document.edit_form.submit();
			return true;
		}
		else
			return false;
	}

	// AUXILIAR en la edición
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
		document.edit_form.cmb_proveedor.focus();
	}	

</script>

<STYLE>

 #caja_datos1 {
   float: left; 
   width: 800px; 
   }
  
  #buttonarea { border: 0px solid red;  }; 
  #btnActualizar { position:absolute; left:0em; } 
  #btnCancelar { position:absolute; left:17em; } 
  
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
		echo "window.opener.document.location.href='anls_series.php?tab=2';";
		echo "window.close();";		
		SYNTAX_CLOSE_JavaScript();
	}
	else if( $error == 20 )
	{
		SYNTAX_BEGIN_JavaScript();
		echo "alert('$SAVE_EDIT_DONE');";
		echo "window.opener.document.location.href='anls_series.php?tab=2';";
		echo "window.close();";
		SYNTAX_CLOSE_JavaScript();
	}
	else if( $error == 30 )
	{
		SYNTAX_BEGIN_JavaScript();
		echo "alert('$DELETE_DONE');";
		echo "window.opener.document.location.href='anls_series.php?tab=2';";
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
				echo $LBL_HEADER_ON_CREATENEW;
			else
				echo $LBL_HEADER_ON_EDIT;
			
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
				echo "window.opener.document.location.href='anls_series.php?tab=2';";
				SYNTAX_CLOSE_JavaScript();				
			}
			else if( $error == 20 )
			{
				echo "<div class=caja_info>";
				echo " <strong>$SAVE_DONE</strong>";
				echo "</div>";
				
				// Update Parent
				SYNTAX_BEGIN_JavaScript();
				echo "window.opener.document.location.href='anls_series.php?tab=2';";
				SYNTAX_CLOSE_JavaScript();				
			}

		 ?>

			<form action="anls_crearsuscripcion.php" method="post" name="edit_form" id="edit_form" class="forma_captura">
			  <input class='hidden' type='hidden' name="the_action" id="the_action" value="<?php echo $the_action;?>">
			  <input class='hidden' type='hidden' name="id_suscripcion" id="id_suscripcion" value="<?php echo $id_suscripcion; ?>">
			  
				<label for="txt_id_serie"><strong><?php echo $LBL_ID_SUSCRIP;?></strong></label>
				<span><?php echo $id_suscripcion;?></span>
				<br><br>
			  		  
				<!-- Proveedor -->
				<dt>	
					<label for="txt_nombre"><strong><?php echo $LBL_NAME_SUPPLIER;?></strong></label>
				</dt>
				<dd>
					<select id="cmb_proveedor" name="cmb_proveedor">
						<?php
							$db = new DB;
							$db->sql = "SELECT a.*, b.PROVEEDOR " . 
									   "FROM personas a LEFT JOIN cfgtipospersona b ON (b.ID_TIPOPERSONA=a.ID_TIPOPERSONA and b.PROVEEDOR='S')" . 
									   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and PROVEEDOR='S'; ";
							$db->Open();
							
							while ( $db->NextRow() )
							{
								$nombre_proveedor = $db->row["APELLIDOS"];
								
								$strselected = ($db->row["ID_PERSONA"]==$id_proveedor) ? "SELECTED" : "";
								
								echo "<option value='" . $db->row["ID_PERSONA"] . "' $strselected>$nombre_proveedor</option>";
							}
							
							$db->Close();
						  ?>
					</select>
					<span class="sp_hint"><?php echo $HINT_SUPPLIER;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				<br>
				<br>

				<!-- Observaciones -->
				<dt>
					<label for="txt_fecha_inicial"><strong><?php echo $LBL_OBS;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_obs" id="txt_obs" value="<?php echo $observaciones;?>" size=80 maxlength=150>
					<span class="sp_hint"><?php echo $HINT_OBS;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>

				
				<!-- Fechas -->
				<dt>
					<label for="txt_fecha_inicial"><strong><?php echo $LBL_DATE_INIT;?></strong></label>
				</dt>
				<dd>
					<?php colocar_edit_date( "txt_fecha_inicial", $fecha_inicial, 0, "" ); ?>
				</dd>
				
				<dt>
					<label for="txt_fecha_final"><strong><?php echo $LBL_DATE_END;?></strong></label>
				</dt>
				<dd>
					<?php colocar_edit_date( "txt_fecha_final", $fecha_final, 0, "" ); ?>
				</dd>				
				<br>
				
				<!-- Precio Suscripción -->
				<dt>
					<label for="txt_precio_suscripcion"><?php echo $LBL_PRICE_SUSCRIP;?></label>
				</dt>
				<dd>
					<input type="text" class='campo_captura' name="txt_precio_suscripcion" id="txt_precio_suscripcion" value="<?php echo $precio_suscripcion;?>" size="15" maxlength="15" onblur="local_extractNumber(this,2,false);" onkeypress="return local_blockNonNumbers(this, event, true, false);">
				</dd>
				
				<!-- $ Papel -->
				<dt>
					<label for="txt_precio_papel"><?php echo $LBL_PRICE_PAPER;?></label>
				</dt>
				<dd>
					<input type="text" class='campo_captura' name="txt_precio_papel" id="txt_precio_papel" value="<?php echo $precio_papel;?>" size="15" maxlength="15" onblur="local_extractNumber(this,2,false);" onkeypress="return local_blockNonNumbers(this, event, true, false);">
				</dd>
				

				<!-- $ Electrónico -->
				<dt>
					<label for="txt_precio_electronico"><?php echo $LBL_PRICE_ELECTRONIC;?></label>
				</dt>
				<dd>
					<input type="text" class='campo_captura' name="txt_precio_electronico" id="txt_precio_electronico" value="<?php echo $precio_electronico;?>" size="15" maxlength="15" onblur="local_extractNumber(this,2,false);" onkeypress="return local_blockNonNumbers(this, event, true, false);">
				</dd>			
				<br>
				<br>

				<!-- DATOS suscripción -->
				<dt>
					<label for="cmb_statussuscripcion"><strong><?php echo $LBL_ST_SUSCRIPTION;?></strong></label>
				</dt>
				<dd>
					<select name="cmb_statussuscripcion" id="cmb_statussuscripcion" class='select_captura'>
						<option value='A' <?php echo ($status_suscripcion=="A") ? "selected" :""; ?>><?php echo $ST_SUSCRIPCION_ACTIVE;?></option>
						<option value='S' <?php echo ($status_suscripcion=="S") ? "selected" :""; ?>><?php echo $ST_SUSCRIPCION_SUSPENDED;?></option>
						<option value='C' <?php echo ($status_suscripcion=="C") ? "selected" :""; ?>><?php echo $ST_SUSCRIPCION_CANCELLED;?></option>
					</select>
				</dd>
				
				<br><br>

				<div id="buttonarea">
					<input id="btnGuardar" name="btnGuardar" class="boton" type="button" value="<?php echo $BTN_SAVE;?>"  onClick='javascript:validar();'>&nbsp;
					<input id="btnCancelar" name="btnCancelar" class="boton" type="button" value="<?php echo $BTN_CANCEL;?>" onClick='<?php echo back_function();?>'>
				</div>
				<br> <!-- for IE -->
			  
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