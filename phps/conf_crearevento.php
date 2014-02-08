<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  28 ene 2010: Se crea el archivo PHP para editar/crear/eliminar eventos.
     */
		
	include "../funcs.inc.php";	
	
	include_language( "global_menus" );

	check_usuario_firmado(); 

	include_language( "conf_crearevento" );		// archivo de idioma

	/** INICIO - FUNCIONES DE MODIFICACION/CREACION/BORRADO **/
	
	include("../basic/bd.class.php");
	
	$id_evento = 0;
	
	$etiqueta = "";
	$id_tipoevento = 0;
	$id_ubicacion = 0;
	
	$publicarse_desde = getcurdate_human_format();
	$publicarse_hasta = $publicarse_desde;
	
	$fecha_desde =  $publicarse_desde;
	$fecha_hasta =  $publicarse_desde;
	
	$hora_desde = "00:00";
	$hora_hasta = "00:00";
	
	$info_breve = "";
	$info_ampliada = "";
	
	$the_action = read_param( "the_action", "" );
	
	$error = 0;
	
	$id_biblioteca = getsessionvar("id_biblioteca");
		
	if( $the_action == "create_new" )
	{
		// generar el nuevo ID de la persona
		$id_recurso = 0;
		
		$db = new DB( "SELECT MAX(ID_RECURSO) AS MAXID, COUNT(*) AS CUANTOS FROM recursos_contenido WHERE ID_BIBLIOTECA=$id_biblioteca; " );
		
		if ($db->NextRow() ) 
			$id_recurso = $db->Field("MAXID") + 1;
			
		$db->Close();
		
		$etiqueta			= $_POST["txt_etiqueta"];
		$id_tipoevento      = $_POST["cmb_tipoevento"];
		$id_ubicacion 	    = $_POST["cmb_ubicacion"];

		$publicarse_desde	= date_for_database_updates( $_POST["txt_publicarse_desde"] );		
		$publicarse_hasta	= date_for_database_updates( $_POST["txt_publicarse_hasta"] );		
		
		$fecha_desde		= date_for_database_updates( $_POST["txt_fecha_desde"] );		
		$fecha_hasta		= date_for_database_updates( $_POST["txt_fecha_hasta"] );		
		
		$hora_desde		    = time_for_database_updates( $_POST["txt_hora_desde"] );		
		$hora_hasta		    = time_for_database_updates( $_POST["txt_hora_hasta"] );

		$info_breve    		= $_POST["txt_info_breve"];
		$info_ampliada	 	= $_POST["txt_info_ampliada"];
		
		$db->sql  = "INSERT INTO recursos_contenido ( ID_BIBLIOTECA, ID_RECURSO, ID_TIPORECURSO, ID_TIPOEVENTO, ID_UBICACION, SUMARIO, PUBLICARSE_DESDE, PUBLICARSE_HASTA, " . 
					" FECHA_DESDE, FECHA_HASTA, HORA_DESDE, HORA_HASTA, INFORMACION_BREVE, INFORMACION_AMPLIADA ) ";
		$db->sql .= " VALUES ( $id_biblioteca, $id_recurso, 3, $id_tipoevento, $id_ubicacion, '$etiqueta', '$publicarse_desde', '$publicarse_hasta', " . 
					" '$fecha_desde', '$fecha_hasta', '$hora_desde', '$hora_hasta', '$info_breve', '$info_ampliada' );";
		$db->ExecSQL();
		
		require_once( "../actions.inc.php" );
		agregar_actividad_de_usuario( CFG_RESOURCE_CREATE, "$ACTION_DESCRIP_CREATE $etiqueta " );
		
		$error = 10;
		
		setsessionvar( "tab_used", 2 );
		
		if( !allow_use_of_popups() )
			ges_redirect( "conf_contents.php?id_resource_created=$id_persona&tab=2" );

	}
	else if( $the_action == "save_changes" )
	{
		$id_evento			= $_POST["id_evento"];
		
		$etiqueta			= $_POST["txt_etiqueta"];
		$id_tipoevento      = $_POST["cmb_tipoevento"];
		$id_ubicacion 	    = $_POST["cmb_ubicacion"];

		$publicarse_desde	= date_for_database_updates( $_POST["txt_publicarse_desde"] );		
		$publicarse_hasta	= date_for_database_updates( $_POST["txt_publicarse_hasta"] );		
		
		$fecha_desde		= date_for_database_updates( $_POST["txt_fecha_desde"] );		
		$fecha_hasta		= date_for_database_updates( $_POST["txt_fecha_hasta"] );		
		
		$hora_desde		    = time_for_database_updates( $_POST["txt_hora_desde"] );		
		$hora_hasta		    = time_for_database_updates( $_POST["txt_hora_hasta"] );

		$info_breve    		= $_POST["txt_info_breve"];
		$info_ampliada	 	= $_POST["txt_info_ampliada"];
		
		$db = new DB;
		$db->sql  = "UPDATE recursos_contenido SET SUMARIO='$etiqueta', ID_TIPOEVENTO=$id_tipoevento, ID_UBICACION=$id_ubicacion, PUBLICARSE_DESDE='$publicarse_desde', PUBLICARSE_HASTA='$publicarse_hasta', " .
					" FECHA_DESDE='$fecha_desde', FECHA_HASTA='$fecha_hasta', HORA_DESDE='$hora_desde', HORA_HASTA='$hora_hasta', " .
					" INFORMACION_BREVE='$info_breve', INFORMACION_AMPLIADA='$info_ampliada' ";
		$db->sql .= "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_RECURSO=$id_evento and ID_TIPORECURSO=3; ";
		$db->ExecSQL();

		require_once( "../actions.inc.php" );
		agregar_actividad_de_usuario( CFG_RESOURCE_EDIT, "$ACTION_DESCRIP_EDIT $etiqueta" );

		$error = 20;

		setsessionvar( "tab_used", 2 );
		
		if( !allow_use_of_popups() )
			ges_redirect( "conf_contents.php?id_resource_edited=$id_evento&tab=2" );

	}
	else if( $the_action == "edit" )
	{
		$id_evento = $_GET["id_evento"];
		
		$db = new DB( "SELECT * FROM recursos_contenido WHERE ID_BIBLIOTECA=$id_biblioteca and ID_RECURSO=$id_evento and ID_TIPORECURSO=3; " );
		
		if( $db->NextRow() ) 
		{ 
			$etiqueta			= $db->row["SUMARIO"];
			
			$id_tipoevento		= $db->row["ID_TIPOEVENTO"];
			$id_ubicacion	    = $db->row["ID_UBICACION"];

			$publicarse_desde	= dbdate_to_human_format( $db->row["PUBLICARSE_DESDE"], 0 );
			$publicarse_hasta	= dbdate_to_human_format( $db->row["PUBLICARSE_HASTA"], 0 ); 			
			
			$fecha_desde	= dbdate_to_human_format( $db->row["FECHA_DESDE"], 0 );
			$fecha_hasta	= dbdate_to_human_format( $db->row["FECHA_HASTA"], 0 ); 			
			
			$hora_desde = get_str_onlytime( $db->row["HORA_DESDE"], 0 );
			$hora_hasta = get_str_onlytime( $db->row["HORA_HASTA"], 0 );
			
			$info_breve	= $db->GetBLOB( $db->row["INFORMACION_BREVE"], 0, 1 ); 
			$info_ampliada	= $db->GetBLOB( $db->row["INFORMACION_AMPLIADA"], 0, 1 ); 

			$the_action = "save_changes";  // acción derivada natural
		}
		
		$db->destroy();
	}
	else if( $the_action == "delete" )
	{
		$eventos = "";
		$eventos_borrados = 0;
		
		if( isset($_GET["eventos"]) )
		{
			$eventos = $_GET["eventos"];
			
			$eventos = str_replace( "@", "ID_RECURSO=", $eventos ); // 1st ocurrence
			$eventos = str_replace( ":", " or ID_RECURSO=", $eventos ); // other ocurrences
			
			$db = new DB( "DELETE FROM recursos_contenido WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TIPORECURSO=3 and ($eventos) " );
			
			$error = 30;
			
			$eventos_borrados = $db->rowsAffected;
			
			$db->Destroy();
			
			setsessionvar( "tab_used", 2 );
			
			require_once( "../actions.inc.php" );
			agregar_actividad_de_usuario( CFG_RESOURCE_DELETE, "$ACTION_DESCRIP_DELETE {$eventos_borrados}" );
		}
		
		if( !allow_use_of_popups() )
			ges_redirect( "conf_contents.php?tab=2&id_resources_deleted=$eventos_borrados" );
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

<SCRIPT type='text/javascript' src='../basic/calend.js'></SCRIPT>

<SCRIPT language="JavaScript">

	function validar( e )
	{
		var error = 0;
		
		if( document.edit_form.txt_etiqueta.value == "" )		
		{
			error = 1;
			alert( "<?php echo $VALIDA_MSG_NOLABEL;?>" );
			document.edit_form.txt_etiqueta.focus();
		}
		
		// fechas de publicacion
		if( error == 0 )
		{
			if( !EsFechaValida( document.edit_form.txt_publicarse_desde ) )
			{
				error = 1;
				alert( "<?php echo $VALIDA_MSG_WRONGDATE;?>" );
				document.edit_form.txt_publicarse_desde.focus();			
			}
		}
		
		if( error == 0 )
		{
			if( !EsFechaValida( document.edit_form.txt_publicarse_hasta ) )
			{
				error = 1;
				alert( "<?php echo $VALIDA_MSG_WRONGDATE;?>" );
				document.edit_form.txt_publicarse_hasta.focus();			
			}
		}		
		
		if( error == 0 )
		{
			if( !Validar2Fechas( document.edit_form.txt_publicarse_desde.value, document.edit_form.txt_publicarse_hasta.value) )
			{
				error = 1;
				alert( "<?php echo $VALIDA_MSG_WRONGDATE_PERIOD;?>" );
				document.edit_form.txt_publicarse_hasta.focus();					
			}
		}
		
		// agenda
		if( error == 0 )
		{
			if( !EsFechaValida( document.edit_form.txt_fecha_desde ) )
			{
				error = 1;
				alert( "<?php echo $VALIDA_MSG_WRONGDATE;?>" );
				document.edit_form.txt_fecha_desde.focus();			
			}
		}

		if( error == 0 )
		{
			if( !EsFechaValida( document.edit_form.txt_fecha_hasta ) )
			{
				error = 1;
				alert( "<?php echo $VALIDA_MSG_WRONGDATE;?>" );
				document.edit_form.txt_fecha_hasta.focus();			
			}
		}		

		if( error == 0 )
		{
			if( !Validar2Fechas( document.edit_form.txt_fecha_desde.value, document.edit_form.txt_fecha_hasta.value) )
			{
				error = 1;
				alert( "<?php echo $VALIDA_MSG_WRONGDATE_PERIOD;?>" );
				document.edit_form.txt_fecha_hasta.focus();					
			}
		}

		if( error == 0 )
		{
			if( document.edit_form.txt_info_breve.value == "" )		
			{
				error = 1;
				alert( "<?php echo $VALIDA_MSG_NOINFO_AT_BRIEF;?>" );
				document.edit_form.txt_info_breve.focus();
			}
		}
		
		if( error == 0 )
		{
			if( document.edit_form.txt_info_ampliada.value == "" )		
			{
				if( !confirm( "<?php echo $VALIDA_MSG_NOINFO_AT_EXTENSE;?>" ) )
				{
					error = 1;
					document.edit_form.txt_info_breve.focus();
				}
			}
		}		
		
		if( error == 0 )
		{
			document.edit_form.submit();
			return true;
		}
		else
			return false;
	}
	
	window.onload=function()
	{
		prepareInputsForHints();
		document.edit_form.txt_etiqueta.focus();
	}	
	
	
</SCRIPT>

<STYLE>

 #caja_datos1 {
   float: left; 
   width: 750px; 
   }
  
 
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

			$db = new DB();
				
			$items_location = "";
				
			$db->Open( "SELECT ID_UBICACION, DESCRIPCION FROM cfgubicaciones WHERE ID_BIBLIOTECA=$id_biblioteca ORDER BY EDIFICIO, PISO, SECCION" );
			
			while( $db->NextRow() )
			{
				$str_selected = "";
				if( $db->Field("ID_UBICACION") == $id_ubicacion )
					$str_selected = "SELECTED";
				$items_location .= "<option $str_selected value='" . $db->Field("ID_UBICACION") . "'>" . $db->Field("DESCRIPCION") . "</option>";
			}
			
			$db->Close();				
			
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

			<form action="conf_crearevento.php" method="post" name="edit_form" id="edit_form" class="forma_captura">
			  <input class='hidden' type='hidden' name="the_action" id="the_action" value="<?php echo $the_action;?>">
			  <input class='hidden' type='hidden' name="id_evento" id="id_evento" value="<?php echo $id_evento; ?>">
			  
				<br>
			  
				<dt>	
					<label for="txt_nombre_grupo"><strong><?php echo $LBL_LABEL;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_etiqueta" id="txt_etiqueta" value='<?php echo $etiqueta;?>' size=60 maxlength=50>
					<span class="sp_hint"><?php echo $HINT_LABEL;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<!-- Forma de Adquisicion -->
				<dt>
					<label for="cmb_tipoevento"><strong><?php echo $LBL_EVENT_TYPE;?></strong></label>
				</dt>
				<dd>
					<?php 
						combo_from_tesauro( "cmb_tipoevento", getsessionvar("id_red"), 22, $id_tipoevento );
					?>
					<span class="sp_hint"><?php echo $HINT_EVENT_TYPE;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>		

				<!-- Periodo de publicacion -->
				<dt>	
					<label for="txt_nombres"><strong><?php echo $LBL_PUBLISH_FROM;?></strong></label>
				</dt>
				<dd>
					<?php colocar_edit_date( "txt_publicarse_desde", $publicarse_desde, 0, "" ); ?>
					
					<span class="sp_hint"><?php echo $HINT_PUBLISH_FROM;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
			  
				<dt>
					<label for="txt_domicilio"><strong><?php echo $LBL_PUBLISH_UNTIL;?></strong></label>
				</dt>
				<dd>
					<?php colocar_edit_date( "txt_publicarse_hasta", $publicarse_hasta, 0, "" ); ?>
					
					<span class="sp_hint"><?php echo $HINT_PUBLISH_UNTIL;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				<br>				
				
				<!-- ITEM LOCATION -->
				<dt>
					<label for="cmb_ubicacion"><strong><?php echo $LBL_EVENT_LOCATION;?></strong></label>
				</dt>
				
				<dd>
					<select class="select_captura" name="cmb_ubicacion" id="cmb_ubicacion">
						<?php echo $items_location;?>
					</select>				
					
					<span class="sp_hint"><?php echo $HINT_EVENT_LOCATION;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>				
				<br>

				<!-- AGENDA DEL EVENTO -->
				<dt>
					<label for="txt_url"><strong><?php echo $LBL_EVENT_SCHEDULE;?></strong></label>
				</dt>
				<dd>
					<div style='display:inline;'><?php colocar_edit_date( "txt_fecha_desde", $fecha_desde, 0, "" ); ?></div>
					<div style='display:inline; margin-left:25px;'> <?php echo $LBL_FROM_TO_CAPTION;?> <?php colocar_edit_date( "txt_fecha_hasta", $fecha_hasta, 0, "" ); ?></div>
				</dd>
				<br>
				
				<dt>
					<label for="txt_url"><strong><?php echo $LBL_EVENT_SCHEDULE_TIME;?></strong></label>
				</dt>
				<dd>
					<div style='display:inline;'><input class="campo_captura" style='display:inline;' type='text' maxlength=5 size='7' id='txt_hora_desde' name='txt_hora_desde' value='<?php echo $hora_desde;?>'></div>
					<div style='display:inline;'><?php echo $LBL_FROM_TO_CAPTION;?>&nbsp;<input class="campo_captura" style='display:inline;' type='text' maxlength=5 size=7 id='txt_hora_hasta' name='txt_hora_hasta' value='<?php echo $hora_hasta;?>'></div>
					<span class="sp_hint"><?php echo $HINT_SCHEDULE_TIME;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<br>
				
				<!-- INFO. BREVE -->
				<dt>
					<label for="txt_info_breve"><strong><?php echo $LBL_BRIEF_INFO;?></strong></label>
				</dt>
				<dd>
					<textarea cols='90' rows='4' id='txt_info_breve' name='txt_info_breve' ><?php echo $info_breve;?></textarea>
				</dd>				
				
				<!-- INFO. AMPLIADA -->				
				<dt>
					<label for="txt_info_ampliada"><strong><?php echo $LBL_EXTENSE_INFO;?></strong></label>
				</dt>
				<dd>
					<textarea cols='90' rows='10' id='txt_info_ampliada' name='txt_info_ampliada' ><?php echo $info_ampliada;?></textarea>
				</dd>				
				
				<br>

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