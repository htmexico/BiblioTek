<?php
	session_start();
	
	/*******
	
	  Función que permitirá agregar una plantilla a la aplicación
	  This functions allows the users to create a new template
	
	  Historial de Cambios
	  
	  18 jun 2009: Se crea el archivo PHP para editar/crear/eliminar plantillas.
	  
     */
		
	include "../funcs.inc.php";
	include "../actions.inc.php";
	include "../basic/bd.class.php";
	
	include_language( "global_menus" );

	check_usuario_firmado(); 

	include_language( "conf_templates" );		// archivo de idioma
	include_language( "conf_templates_createnew" );		// archivo de idioma

	/** INICIO - FUNCIONES DE MODIFICACION/CREACION/BORRADO **/
	
	$template_name      = "";
	$type_of_material   = "";

	$action = read_param( "action", "" );  // _POST o _GET
	$type = read_param( "type", "ENT", 1 );  // _POST o _GET
	$id_plantilla = read_param( "id_plantilla", $LBL_TO_BE_ASIGNED, 0 );  // _POST o _GET  (ABORTANDO EN EXCEPCION)
	
	$type_of_template = "";
	
	if( $type != "" )
	{
		if( $type == "ENT" )
			$type_of_template = $LABEL_CATEGORY_ENT;
		else if( $type == "CAT" )
			$type_of_template = $LABEL_CATEGORY_CAT;
	}
	
	$error = 0;
	
	$id_biblioteca = getsessionvar("id_biblioteca");
	$id_red = getsessionvar("id_red");
	
	$db = new DB();

	if( $action == "create_new" )
	{
		// generar el nuevo ID del grupo
		$id_plantilla = 0;

		$db->Open( "SELECT COUNT(*) AS CUANTOS, MAX(ID_PLANTILLA) AS MAXID FROM cfgplantillas_nombres WHERE ID_BIBLIOTECA=$id_biblioteca" );
		
		if( $db->NextRow() ) 
		{
			if( $db->row["CUANTOS"] == 0 )
				$id_plantilla = 1;
			else
				$id_plantilla = $db->Field("MAXID") + 1;
		}
			
		$db->FreeResultset();
		
		// obtener datos
		$nombre_plantilla		= $_POST["txt_name"];
		$type_of_material        = $_POST["cmb_tipomaterial"];
		
		$db->sql  = "INSERT INTO cfgplantillas_nombres ( ID_BIBLIOTECA, ID_PLANTILLA, ID_TIPO, NOMBRE_PLANTILLA, ID_TIPOMATERIAL ) ";
		$db->sql .= " VALUES ( $id_biblioteca, $id_plantilla, '$type', '$nombre_plantilla', '$type_of_material' )";
		
		$db->ExecSQL();
		
		agregar_actividad_de_usuario( CFG_CONFIG_TEMPLATES, "$ACTION_CREATE_TEMPLATE $id_plantilla - $nombre_plantilla ");
		
		$error = 10;
		
		if( !allow_use_of_popups() )
			ges_redirect( "conf_templates_cat.php?id_template_created=$id_plantilla" );

	}
	if( $action == "save_changes" )
	{
		// obtener datos
		$template_name		= $_POST["txt_name"];
		$type_of_material   = $_POST["cmb_tipomaterial"];
	
		$db->sql  = "UPDATE cfgplantillas_nombres SET NOMBRE_PLANTILLA='$template_name', ID_TIPOMATERIAL='$type_of_material' ";
		$db->sql .= "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PLANTILLA=$id_plantilla";
		$db->ExecSQL();
		
		agregar_actividad_de_usuario( CFG_CONFIG_TEMPLATES, "$ACTION_EDIT_TEMPLATE $id_plantilla - $template_name " );
		
		$error = 20;
		
		if( !allow_use_of_popups() )
			ges_redirect( "conf_thesaurus_cat.php?id_categoria=$id_categoria" );
	}
	else if( $action == "edit" )
	{
		$id_plantilla = $_GET["id_plantilla"];
		
		$db->Open( "SELECT * FROM cfgplantillas_nombres WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PLANTILLA=$id_plantilla" );
		
		if( $db->NextRow() ) 
		{ 
			$template_name      = $db->row["NOMBRE_PLANTILLA"];
			$type_of_material	= $db->row["ID_TIPOMATERIAL"];

			$action = "save_changes";  // acción derivada natural
		}
		
		$db->Close();
		
		$error = 0;
	}
	else if( $action == "delete" )
	{
		$plantillas = "";
		
		$db->ExecSQL( "DELETE FROM cfgplantillas_nombres WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PLANTILLA=$id_plantilla " );
		$plantillas = $db->rowsAffected;
					
		agregar_actividad_de_usuario( CFG_CONFIG_TEMPLATES, "$ACTION_DELETE_TEMPLATE $id_plantilla ($plantillas) " );
		
		if( !allow_use_of_popups() )
			ges_redirect( "conf_templates_cat.php?type=$type&id_plantilla=$id_plantilla" );

	}
	else
	{
		$action = "create_new";  // acción por default
	}
	
	/** FIN FUNCIONES DE MODIFICACION/CREACION/BORRADO **/
	
	// Coloca un encabezado HTML <head>
	include "../basic/head_handler.php";
	HeadHandler( ($action == "create_new") ? $LBL_CREATETEMPLATE_HEADER_V1 : $LBL_CREATETEMPLATE_HEADER_V1, "../");
		
?>

<SCRIPT language="JavaScript">

	function validar( e )
	{
		var error = 0;
		
		if( document.edit_form.txt_name.value == "" )		
		{
			error = 1;
			alert( "<?php echo $VALIDA_MSG_EDIT_1;?>" );
			document.edit_form.txt_name.focus();
		}

		if( error == 0 )
		{
			document.edit_form.submit();
			return true;
		}
		else
			return false;
	}
	
</SCRIPT>

<STYLE>

 #caja_datos1 
   {
   float: left; 
   width: 780px; 
   }
  
  #buttonarea { border: 0px solid red;  }; 
  #btnActualizar { position:absolute; left:0em; } 
  #btnCancelar { position:absolute; left:17em; } 
  
</STYLE>

<body id="home">

<!-- contenedor principal -->
<div id="contenedor">

<div id="bloque_principal"> <!-- inicia contenido -->

 <div id="contenido_principal">

	<div class=caja_datos id=caja_datos1 >
		<h2>
		<?php 

			if( $action == "create_new" )
				echo $LBL_CREATETEMPLATE_HEADER_V1;
			else
				echo $LBL_CREATETEMPLATE_HEADER_V2;

		?>

		<HR></h2><br>

		<?php
			if( $error != 0 )
			{
				if( $error == 2 )
				{
					echo "<div class=caja_errores>";
					echo " <strong>Error: Al guardar los cambios.</strong>";
					echo "</div>";
				}
				else if( $error == 10 )
				{
					echo "<div class=caja_info>";
					echo " <strong>$SAVE_CREATED_DONE</strong>";
					echo "</div>";

					echo "<SCRIPT language='javascript'>";
					echo "   window.opener.location.reload();";
					echo "   window.close();";
					echo "</SCRIPT>";
				}
				else if( $error == 20 )
				{
					echo "<div class=caja_info>";
					echo "   <strong>$SAVE_DONE</strong>";
					echo "</div>";

					echo "<SCRIPT language='javascript'>";
					echo "   window.opener.location.reload();";
					echo "</SCRIPT>";
				}
			}
		 ?>		

		 <form action="conf_templates_createnew.php" method="post" name="edit_form" id="edit_form" class="forma_captura">
			<input class='hidden' type='hidden' name="action" id="action" value="<?php echo $action;?>">
			<input class='hidden' type='hidden' name="id_plantilla" id="id_term" value="<?php echo $id_plantilla; ?>">
			<input class='hidden' type='hidden' name="type" id="id_categoria" value="<?php echo $type; ?>">

			<label><strong><?php echo $LBL_TYPE_OF_TEMPLATE;?></strong></label>
			<span class="span_captura"><strong><?php echo $type_of_template;?></strong></span>
			<br>

			<label><?php echo $LBL_ID_TEMPLATE;?></label>
			<span class="span_captura"><?php echo $id_plantilla;?></span>
			<br>			  

			<label for="txt_name"><?php echo $LBL_NAME_OF_TEMPLATE;?></label>
			<input class="campo_captura" type="text" name="txt_name" id="txt_name" value="<?php echo $template_name;?>" size=60 maxlength=100>
			
			<label for="cmb_tipomaterial"><?php echo $LBL_TYPE_MATERIAL;?></label>
			
			<?php 
			
			  $db->Open( "SELECT * FROM marc_material WHERE OBSOLETO <> 'S' or (OBSOLETO is NULL) ORDER BY ID_TIPOMATERIAL; " );
			
			 ?>
			<select id="cmb_tipomaterial" name="cmb_tipomaterial">
			
				<?php
				
					echo "<option value=''>-- $LBL_NOT_SELECTED_YET --</option>";
					
					while( $db->NextRow() )
					{
						$tempstr = get_translation( $db->row["DESCRIPCION"], $db->row["DESCRIPCION_ENG"], $db->row["DESCRIPCION_PORT"] );
						$codigo  = get_translation( $db->row["CODIGO_MARC"], $db->row["CODIGO_MARC_ENG"], $db->row["CODIGO_MARC_PORT"] );
						
						if( strlen($tempstr) >= 80 )
							$tempstr = substr( $tempstr, 0, 77 ) . "...";
						
						$strselect = "";
						
						if( $type_of_material == $db->row["ID_TIPOMATERIAL"] ) $strselect = "SELECTED";
						
						echo "<option value='" . $db->row["ID_TIPOMATERIAL"] ."' $strselect>($codigo) $tempstr</option>";
					}
				
				 ?>
			
				
			</select>
			
			<?php
			  $db->Close();
			 ?>
			<br>			
			<br>  

			<div id="buttonarea">
				<input id="btnGuardar" name="btnGuardar" class="boton" type="button" value="<?php echo $BTN_SAVE;?>"  onClick='javascript:validar();' />&nbsp;
				<input id="btnCancelar" name="btnCancelar" class="boton" type="button" value="<?php echo $BTN_CANCEL;?>" onClick='javascript:window.close();' />
			</div>
			<br>
			<br> <!-- for IE -->

		</form>

	</div> <!-- caja_datos --> 

 </div> <!-- contenido_principal -->

 <div id="contenido_adicional">
	&nbsp;
 </div> <!-- contenido_adicional -->

</div>
<!-- end div bloque_principal -->

<?php  

	display_copyright(); 
	$db->Destroy();
		
?>

</div><!-- end div contenedor -->

</body>

</html>