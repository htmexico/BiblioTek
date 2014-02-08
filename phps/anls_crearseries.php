<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  19 sep 2009: Se crea el archivo PHP para editar/crear/eliminar series.
	  
     */
		
	include "../funcs.inc.php";	
	
	include_language( "global_menus" );

	check_usuario_firmado(); 

	include_language( "anls_series" );		// archivo de idioma

	include("../basic/bd.class.php");
	
	$id_serie = "$LBL_TO_BE_ASIGNED";

	$id_titulo = 0;
	
	$nombre					= "";
	
	$id_coleccion			= read_param( "id_coleccion", 0 );  // tesauro
	
	$periodicidad			= 0;  // tesauro
	$periodicidad_irregular	= "";
	
	$fecha_primera_recepcion	= getcurdate_human_format();
	$fecha_ultima_recepcion		= "";
	
	$papel						= "";
	//$papel_precio				= 0;
	$electronico				= "";
	//$electronico_precio			= 0;
	
	$observaciones				= "";

	$tipo_contenido				= "";  // tesauro	
	$forma_adquisicion    		= "";  // tesauro
	
	$id_suscripcion				= 0;
	//$status_suscripcion			= "";
	
	$num_sisac					= "";
	
	$ordered_by = "NAME";
	
	$the_action = read_param( "the_action", "" );
	
	$error = 0;
	
	$id_biblioteca = getsessionvar("id_biblioteca");
		
	if( $the_action == "create_new" )
	{
		// generar el nuevo ID de la persona
		$id_serie = 0;
		
		$db = new DB( "SELECT MAX(ID_SERIE) AS MAXID, COUNT(*) AS CUANTOS FROM series WHERE ID_BIBLIOTECA=$id_biblioteca" );
		
		if ($db->NextRow() ) 
			$id_serie = $db->Field("MAXID") + 1;
			
		$db->FreeResultset();
		
		$nombre 					= read_param( "txt_nombre", "", 1 );

		$id_coleccion				= read_param( "cmb_coleccion", 0, 1 );  // tesauro

		$periodicidad				= read_param( "cmb_periodicidad", 0, 1 );  // tesauro
		$periodicidad_irregular		= isset($_POST["chk_periodicidad_irregular"]) ? "S" : "N";

		$fecha_primera_recepcion	= date_for_database_updates( $_POST["txt_fecha_primera_recepcion"] );
		
		if( $_POST["txt_fecha_ultima_recepcion"] == "" )
			$fecha_ultima_recepcion = "NULL";
		else
			$fecha_ultima_recepcion	= "'" . date_for_database_updates( $_POST["txt_fecha_ultima_recepcion"] ) . "'";

		$papel						= isset($_POST["chk_papel"]) ? "S" : "N";
		//$papel_precio				= isset($_POST["txt_precio_papel"]) ? $_POST["txt_precio_papel"] : 0;
		$electronico				= isset($_POST["chk_electronico"]) ? "S" : "N";
		//$electronico_precio			= isset($_POST["txt_precio_electronico"]) ? $_POST["txt_precio_electronico"] : 0;

		$observaciones				= $_POST["txt_observaciones"];
		$num_sisac					= $_POST["txt_numsisac"];

		$tipo_contenido				= read_param( "cmb_tipocontenido", 5, 1 );  // tesauro
		$forma_adquisicion    		= read_param( "cmb_formaadquisicion", 5, 1 );  // tesauro

		$id_suscripcion				= read_param( "cmb_suscripcion", 0, 1 );
		//$status_suscripcion			= read_param( "cmb_statussuscripcion", 5, 1 );
		
		$db->sql  = "INSERT INTO series ( ID_BIBLIOTECA, ID_SERIE, NOMBRE, ID_COLECCION, ID_PERIODICIDAD, PERIODICIDAD_IRREGULAR, ";
		$db->sql .= "   FECHA_PRIMERA_RECEPCION, FECHA_ULTIMA_RECEPCION, PAPEL, ELECTRONICO, OBSERVACIONES, NUM_SISAC, ";
		$db->sql .= "   ID_TIPO_CONTENIDO, ID_FORMA_ADQUISICION, ID_SUSCRIPCION ) ";
		$db->sql .= " VALUES ( $id_biblioteca, $id_serie, '$nombre', $id_coleccion, $periodicidad, '$periodicidad_irregular', ";
		$db->sql .= "   '$fecha_primera_recepcion', $fecha_ultima_recepcion, '$papel', '$electronico', '$observaciones', '$num_sisac', ";
		$db->sql .= "   $tipo_contenido, $forma_adquisicion, $id_suscripcion ) ";
		$db->ExecSQL();

		require_once( "../actions.inc.php" );
		agregar_actividad_de_usuario( ANLS_SERIES_CREATE, "$ACTION_DESCRIP_CREATE $nombre" );

		$error = 10;

		if( !allow_use_of_popups() )
			ges_redirect( "anls_series.php?id_serie_created=$id_serie" );

	}
	else if( $the_action == "save_changes" )
	{
		$id_serie = read_param( "id_serie", "", 1 );

		$nombre 					= read_param( "txt_nombre", "", 1 );

		$id_coleccion				= read_param( "cmb_coleccion", 0, 1 );  // tesauro

		$periodicidad				= read_param( "cmb_periodicidad", 0, 1 );  // tesauro
		$periodicidad_irregular		= isset($_POST["chk_periodicidad_irregular"]) ? "S" : "N";

		$fecha_primera_recepcion	= date_for_database_updates( $_POST["txt_fecha_primera_recepcion"] );
		
		if( $_POST["txt_fecha_ultima_recepcion"] == "" )
			$fecha_ultima_recepcion = "NULL";
		else
			$fecha_ultima_recepcion	= "'" . date_for_database_updates( $_POST["txt_fecha_ultima_recepcion"] ) . "'";

		$papel						= isset($_POST["chk_papel"]) ? "S" : "N";
		//$papel_precio				= isset($_POST["txt_precio_papel"]) ? $_POST["txt_precio_papel"] : 0;
		$electronico				= isset($_POST["chk_electronico"]) ? "S" : "N";
		//$electronico_precio			= isset($_POST["txt_precio_electronico"]) ? $_POST["txt_precio_electronico"] : 0;

		$observaciones				= $_POST["txt_observaciones"];
		$num_sisac					= $_POST["txt_numsisac"];

		$tipo_contenido				= read_param( "cmb_tipocontenido", 5, 1 );  // tesauro
		$forma_adquisicion    		= read_param( "cmb_formaadquisicion", 5, 1 );  // tesauro

		$id_suscripcion				= read_param( "cmb_suscripcion", 0, 1 );
		//$status_suscripcion			= read_param( "cmb_statussuscripcion", 5, 1 );

		$db = new DB;

		$db->sql  = "UPDATE series SET NOMBRE='$nombre', ID_COLECCION=$id_coleccion, ID_PERIODICIDAD=$periodicidad, PERIODICIDAD_IRREGULAR='$periodicidad_irregular',";
		$db->sql .= "   FECHA_PRIMERA_RECEPCION='$fecha_primera_recepcion', FECHA_ULTIMA_RECEPCION=$fecha_ultima_recepcion, ";
		$db->sql .= "   PAPEL='$papel', ELECTRONICO='$electronico', ";
		$db->sql .= "   OBSERVACIONES='$observaciones', NUM_SISAC='$num_sisac', ID_TIPO_CONTENIDO=$tipo_contenido, ID_FORMA_ADQUISICION=$forma_adquisicion, ";
		$db->sql .= "   ID_SUSCRIPCION=$id_suscripcion ";
		$db->sql .= "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_SERIE=$id_serie ";
		$db->ExecSQL( );

		require_once( "../actions.inc.php" );
		agregar_actividad_de_usuario( ANLS_SERIES_EDIT, "$ACTION_DESCRIP_EDIT $nombre" );

		$error = 20;

		if( !allow_use_of_popups() )
			ges_redirect( "anls_series.php?id_serie_edited=$id_serie" );

	}
	else if( $the_action == "edit" )
	{
		$id_serie = $_GET["id_serie"];
		
		$db = new DB( "SELECT * FROM series WHERE ID_BIBLIOTECA=$id_biblioteca and ID_SERIE=$id_serie" );
		
		if( $db->NextRow() ) 
		{ 
			$id_titulo = $db->row["ID_TITULO"];
			
			$nombre					= $db->row["NOMBRE"];
			
			$id_coleccion			= $db->row["ID_COLECCION"];
			$periodicidad			= $db->row["ID_PERIODICIDAD"];
			$periodicidad_irregular	= $db->row["PERIODICIDAD_IRREGULAR"];
			
			$fecha_primera_recepcion	= dbdate_to_human_format( $db->row["FECHA_PRIMERA_RECEPCION"], 0 );
			$fecha_ultima_recepcion		= $db->row["FECHA_ULTIMA_RECEPCION"];
			
			$papel						= $db->row["PAPEL"];
			//$papel_precio				= $db->row["PAPEL_PRECIO"];
			$electronico				= $db->row["ELECTRONICO"];
			//$electronico_precio			= $db->row["ELECTRONICO_PRECIO"];			
			
			$observaciones				= $db->row["OBSERVACIONES"];
			$tipo_contenido				= $db->row["ID_TIPO_CONTENIDO"];
			
			$forma_adquisicion    		= $db->row["ID_FORMA_ADQUISICION"];
			
			$id_suscripcion				= $db->row["ID_SUSCRIPCION"];
			//$status_suscripcion			= $db->row["STATUS_SUSCRIPCION"];
			
			$num_sisac					= $db->row["NUM_SISAC"];

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
			
			$series = str_replace( "@", "ID_SERIE=", $series ); // 1st ocurrence
			$series = str_replace( ":", " or ID_SERIE=", $series ); // other ocurrences
			
			$db = new DB( "DELETE FROM series WHERE ID_BIBLIOTECA=$id_biblioteca and ($series) " );
			
			$error = 30;
			
			$series_borradas = $db->rowsAffected;
			
			$db->Destroy();
			
			require_once( "../actions.inc.php" );
			agregar_actividad_de_usuario( ANLS_SERIES_DELETE, "$ACTION_DESCRIP_DELETE $series <$series_borradas>" );
		}
		
		if( !allow_use_of_popups() )
			ges_redirect( "anls_series.php?id_series_deleted=$series_borradas" );
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

	function validar( e )
	{
		var error = 0;
		
		if( document.edit_form.txt_nombre.value == "" )		
		{
			error = 1;
			alert( "<?php echo $VALIDA_MSG_NONAME;?>" );
			document.edit_form.txt_nombre.focus();
		}

		if( error == 0 )
		{
			document.edit_form.submit();
			return true;
		}
		else
			return false;
	}
	
	function enablePrecioPapel( edit_check )
	{
		var edit_precio = js_getElementByName("txt_precio_papel");
		
		if( edit_check.checked )
		{
			edit_precio.disabled = false;
		}
		else
		{
			edit_precio.disabled = true;
		}
	}	
	
	function enablePrecioElectronico( edit_check )
	{
		var edit_precio = js_getElementByName("txt_precio_electronico");
		
		if( edit_check.checked )
		{
			edit_precio.disabled = false;
		}
		else
		{
			edit_precio.disabled = true;
		}
	}
	
	window.onload=function()
	{
		prepareInputsForHints();
		document.edit_form.txt_nombre.focus();
	}	

</script>

<STYLE>

 #caja_datos1 {
   float: none; 
   width: 750px; 
   }
  
  #buttonarea { border: 0px solid red;  }
  #btnActualizar { position:absolute; left:0em; } 
  #btnCancelar { position:absolute; left:17em; } 
  
form.forma_captura label {
   width: 14em;
}  
  
<?php
	if( allow_use_of_popups() )
		echo "#contenedor { width: 790px; margin-top: 10px; } ";
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
		echo "window.opener.document.location.href='anls_series.php?tab=1';";
		echo "window.close();";		
		SYNTAX_CLOSE_JavaScript();
	}
	else if( $error == 20 )
	{
		SYNTAX_BEGIN_JavaScript();
		echo "alert('$SAVE_EDIT_DONE');";
		echo "window.opener.document.location.href='anls_series.php?tab=1';";
		echo "window.close();";
		SYNTAX_CLOSE_JavaScript();
	}
	else if( $error == 30 )
	{
		SYNTAX_BEGIN_JavaScript();
		echo "alert('$DELETE_DONE');";
		echo "window.opener.document.location.href='anls_series.php?tab=1';";
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
				echo "window.opener.document.location.href='anls_series.php?tab=1';";
				SYNTAX_CLOSE_JavaScript();				
			}
			else if( $error == 20 )
			{
				echo "<div class=caja_info>";
				echo " <strong>$SAVE_DONE</strong>";
				echo "</div>";
				
				// Update Parent
				SYNTAX_BEGIN_JavaScript();
				echo "window.opener.document.location.href='anls_series.php?tab=1';";
				SYNTAX_CLOSE_JavaScript();				
			}

		 ?>

			<form action="anls_crearseries.php" method="post" name="edit_form" id="edit_form" class="forma_captura">
			  <input class='hidden' type='hidden' name="the_action" id="the_action" value="<?php echo $the_action;?>">
			  <input class='hidden' type='hidden' name="id_serie" id="id_serie" value="<?php echo $id_serie; ?>">
			  
				<label for="txt_id_serie"><strong><?php echo $LBL_ID_SERIE;?></strong></label>
				<span><?php echo $id_serie;?></span>
				<br>
				
				<?php 
				
					if( $id_titulo != 0 )
					{
						echo "<br>";
						echo "<label><strong>$LBL_ID_TITLE</strong></label>";
						echo "<span>$id_titulo</span>";
						echo "<br>";
					}
				
				 ?>
				 
				<br>
			  		  
				<!-- Nombre -->
				<dt>	
					<label for="txt_nombre"><strong><?php echo $LBL_NAME_SERIE;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_nombre" id="txt_nombre" value='<?php echo $nombre;?>' size="95" maxlength="250">
				</dd>

				<!-- Colección -->
				<dt>
					<label for="cmb_coleccion"><strong><?php echo $LBL_COLECTION;?></strong></label>
				</dt>
				<dd>

					<?php 
						combo_from_tesauro( "cmb_coleccion", getsessionvar("id_red"), 10, $id_coleccion );
					?>
					
					<span class="sp_hint"><?php echo $HINT_COLECTION;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<!-- Periodicidad -->
				<dt>
					<label for="cmb_periodicidad"><strong><?php echo $LBL_PERIODICITY;?></strong></label>
				</dt>
				<dd>

					<?php 
						combo_from_tesauro( "cmb_periodicidad", getsessionvar("id_red"), 34, $periodicidad );
					?>
					
					<span class="sp_hint"><?php echo $HINT_PERIODICITY;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<dt>
					<label for="chk_periodicidad_irregular">&nbsp;</label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_periodicidad_irregular" id="chk_periodicidad_irregular" <?php echo (($periodicidad_irregular=="S") ? "checked" : ""); ?>>					
					&nbsp;<span><?php echo $LBL_PERIODICITY_IRREGULAR;?></span>
				</dd>
				<br>
				
				<!-- Fechas -->
				<dt>
					<label for="txt_fecha_primera_recepcion"><strong><?php echo $LBL_DATE_FIRST_RECEPTION;?></strong></label>
				</dt>
				<dd>
					<?php colocar_edit_date( "txt_fecha_primera_recepcion", $fecha_primera_recepcion, 0, "" ); ?>
				</dd>
				
				<dt>
					<label for="txt_fecha_primera_recepcion"><strong><?php echo $LBL_DATE_LAST_RECEPTION;?></strong></label>
				</dt>
				<dd>
					<?php colocar_edit_date( "txt_fecha_ultima_recepcion", $fecha_ultima_recepcion, 0, "" ); ?>
				</dd>				
				<br>
				
				<!-- Papel -->
				<dt>
					<label for="chk_papel">&nbsp;</label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_papel" id="chk_papel" <?php echo (($papel=="S") ? "checked" : ""); ?> onClick='javascript:enablePrecioPapel(this);'>
					&nbsp;<div style='display:inline;'><?php echo $LBL_ON_PAPER;?></div><br>
					
					<!-- <div style='display:inline; position:absolute; left: 22em; width: 360px;'><strong><?php echo $LBL_PRICE_PAPER;?></strong>&nbsp;
						<input <?php echo ($papel=="S") ? "" : "disabled" ; ?> type="text" name="txt_precio_papel" id="txt_precio_papel" value="<?php echo $papel_precio;?>" size="15" maxlength="15" onblur="local_extractNumber(this,2,false);" onkeypress="return local_blockNonNumbers(this, event, true, false);">
					</div><br> -->
				</dd>

				<!-- Electrónico -->
				<dt>
					<label for="chk_electronico">&nbsp;</label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_electronico" id="chk_electronico" <?php echo (($electronico=="S") ? "checked" : ""); ?> onClick='javascript:enablePrecioElectronico(this);'>
					&nbsp;<div style='display:inline;'><?php echo $LBL_ON_ELECTRONIC;?></div>
					<!-- <div style='display:inline; position:absolute; left: 22em; width: 360px;'><strong><?php echo $LBL_PRICE_ELECTRONIC;?></strong>&nbsp;
						<input <?php echo ($electronico=="S") ? "" : "disabled"; ?> type="text" name="txt_precio_electronico" id="txt_precio_electronico" value="<?php echo $electronico_precio;?>" size="15" maxlength="15" onblur="local_extractNumber(this,2,false);" onkeypress="return local_blockNonNumbers(this, event, true, false);" >
					</div><br>	-->
					
				</dd>				
				<br>

				<!-- Observaciones -->
				<dt>	
					<label for="txt_observaciones"><strong><?php echo $LBL_OBS;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_observaciones" id="txt_observaciones" value="<?php echo $observaciones;?>" size=80 maxlength=250>
				</dd>
				
				<!-- NUM SISAC -->
				<dt>	
					<label for="txt_numsisac"><strong><?php echo $LBL_NUM_SISAC;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_numsisac" id="txt_numsisac" value="<?php echo $num_sisac;?>" size=50 maxlength=50>
				</dd>				
				
				<!-- Tipo de Contenido -->
				<dt>
					<label for="cmb_tipocontenido"><strong><?php echo $LBL_CONTENT_TYPE;?></strong></label>
				</dt>
				<dd>				
					<?php 
						combo_from_tesauro( "cmb_tipocontenido", getsessionvar("id_red"), 36, $tipo_contenido );
					?>	

					<span class="sp_hint"><?php echo $HINT_CONTENT_TYPE;?><span class="hint-pointer">&nbsp;</span></span>					
				</dd>
				
				<!-- Forma de Adquisicion -->
				<dt>
					<label for="cmb_formaadquisicion"><strong><?php echo $LBL_ADQUISITION_TYPE;?></strong></label>
				</dt>
				<dd>

					<?php 
						combo_from_tesauro( "cmb_formaadquisicion", getsessionvar("id_red"), 24, $forma_adquisicion );
					?>

					<span class="sp_hint"><?php echo $HINT_ADQUISITION_TYPE;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				<br>							
				
				<!-- DATOS suscripción -->			
				<dt>	
					<label for="cmb_suscripcion"><strong><?php echo $LBL_ID_SUSCRIPTION;?></strong></label>
				</dt>
				<dd>
					<select id="cmb_suscripcion" name="cmb_suscripcion" class='select_captura'>
						<option value='0' <?php echo ($id_suscripcion==0) ? "SELECTED" : ""; ?>>0 - <?php echo $LBL_NO_SUSCRIPTION;?></option>";					
						<?php
							$db = new DB();
							$db->sql = "SELECT a.*, b.APELLIDOS, b.NOMBRES " . 
									   "FROM suscripciones a LEFT JOIN personas b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PERSONA=a.ID_PROVEEDOR)" . 
									   "WHERE a.ID_BIBLIOTECA=$id_biblioteca; ";
							$db->Open();
							
							while ( $db->NextRow() )
							{
								$nombre_proveedor = $db->row["APELLIDOS"];
								
								if( strlen($nombre_proveedor) >= 30 )
									$nombre_proveedor = substr( $nombre_proveedor, 1, 27 ) . "...";
								
								$strselected = ($db->row["ID_SUSCRIPCION"]==$id_suscripcion) ? "SELECTED" : "";
								
								echo "<option value='" . $db->row["ID_SUSCRIPCION"] . "' $strselected>" . $db->row["ID_SUSCRIPCION"] . " " . $db->row["OBSERVACIONES"] . 
									 "  / $nombre_proveedor</option>";
							}

							$db->Close();
							$db->destroy();

						  ?>
					</select>
				</dd>			

				<br>

				<div id="buttonarea">
					<input id="btnGuardar" name="btnGuardar" class="boton" type="button" value="<?php echo $BTN_SAVE;?>"  onClick='javascript:validar();'>&nbsp;
					<input id="btnCancelar" name="btnCancelar" class="boton" type="button" value="<?php echo $BTN_CANCEL;?>" onClick='<?php echo back_function();?>'>
				</div>
				<br> <!-- for IE -->
			  
			</form>
	  
	</div> <!-- caja_datos --> 

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