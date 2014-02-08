<?php
	session_start();
		
	/*******
	 Permite configurar los campos MARC que estarán disponibles en una PLANTILLA
	 
	 Historial de Cambios
		  
	 17 jun 2009: Se crea como parte de las funciones de config.
	 25 ago 2009: Se modifica ligeramente la interface de usuario, se coloca un <SELECT></SELECT> con las plantillas
	 
	 PENDIENTES:
	    Colocar el botón de Save a lado de los AUXILIARES para DAR EL UPDATE o INSERT (SE IGNORA SI SIGUE PENDIENTE)

	 */		
		
	include "../funcs.inc.php";
	include "../actions.inc.php";
	include "../basic/bd.class.php";
	include "marc.php";
	
	check_usuario_firmado(); 
	
	$descrip = "";

	$id_biblioteca = getsessionvar("id_biblioteca");
	
	$type = read_param( "type", "", 1 );
	$id_plantilla = read_param( "id_plantilla", "", 0 );
	$action = read_param( "action", "", 0 );
	
	$descrip_plantilla = "";
		
	include_language( "global_menus" );	
	include_language( "conf_templates" );
	
	$info = 0;
	
	if( $type != "" )
	{
		if( $type == "ENT" )
			$descrip_plantilla = $LABEL_CATEGORY_ENT;
		else if( $type == "CAT" )
			$descrip_plantilla = $LABEL_CATEGORY_CAT;
	}
	
	$db = new DB();
	
	if( $action == "update_valor_subcampo" )
	{
		$idcampo    = read_param( "idcampo", "", 0 );
		$idsubcampo = read_param( "idsubcampo", "", 0 );
		$valor	    = read_param( "valor", "", 0 );

		$edit = 0;
		
		$db->Open( "SELECT COUNT(*) AS CUANTOS FROM cfgplantillas " . 
				   "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PLANTILLA=$id_plantilla and ID_CAMPO='$idcampo' and DATO='$idsubcampo';" );

		if( $db->NextRow() )
		{
			if( $db->row["CUANTOS"] > 0 )
				$edit = 1;
		}
		
		$db->Close();
		
		if( $edit == 1 )
		{
			$db->ExecSQL( "UPDATE cfgplantillas SET VALOR_DEFAULT='$valor' " .
						  "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PLANTILLA=$id_plantilla and ID_CAMPO='$idcampo' and DATO='$idsubcampo' " );
		}
		else
		{
			$num = 0;
			
			$db->Open(  "SELECT COUNT(*) AS CUANTOS, MAX(ID_NUMCAMPO) AS MAXIMO FROM cfgplantillas " .
						"WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PLANTILLA=$id_plantilla " );
			
			if( $db->NextRow() )
			{
				if( $db->row["CUANTOS"] == 0 )
					$num = 1;
				else
					$num = $db->row["MAXIMO"] + 1;
			}
			$db->Close();

			$db->ExecSQL( "INSERT INTO cfgplantillas (ID_BIBLIOTECA, ID_PLANTILLA, ID_NUMCAMPO, ID_CAMPO, DATO, VALOR_DEFAULT ) " .
						  "VALUES ($id_biblioteca, $id_plantilla, $num, '$idcampo', '$idsubcampo', '$valor') " );
		}
	}
	else if( $action == "update_valor_auxiliares" )
	{
		$idcampo    = read_param( "idcampo", "", 0 );
		$valor_aux1 = read_param( "valoraux1", "" );
		$valor_aux2 = read_param( "valoraux2", "" );
		
		if( $valor_aux1 == "UNDEF" )
		{ 	$valor_aux1 = "#"; 	}
		
		if( $valor_aux2 == "UNDEF" )
		{ 	$valor_aux2 = "#"; 	}		
		
		if( $valor_aux1 != "" )
		{
			$db->ExecSQL( "UPDATE cfgplantillas SET VALOR_DEFAULT='$valor_aux1' " .
						  "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PLANTILLA=$id_plantilla and ID_CAMPO='$idcampo' and DATO='X1' " );
		}
		
		if( $valor_aux2 != "" )
		{
			$db->ExecSQL( "UPDATE cfgplantillas SET VALOR_DEFAULT='$valor_aux2' " .
						  "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PLANTILLA=$id_plantilla and ID_CAMPO='$idcampo' and DATO='X2' " );
		}		
		
	}
	else if( $action == "remove_subcampo" )
	{
		$idcampo    = read_param( "idcampo", "", 0 );
		$idsubcampo = read_param( "idsubcampo", "", 0 );
		
		$db->ExecSQL( "DELETE FROM cfgplantillas  " .
						  "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PLANTILLA=$id_plantilla and ID_CAMPO='$idcampo' and DATO='$idsubcampo' " );
						  
		$db->Open( "SELECT COUNT(*) AS CUANTOS FROM cfgplantillas " .
						"WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PLANTILLA=$id_plantilla and ID_CAMPO='$idcampo' and (DATO<>'X1' and DATO<>'X2')" );
				
		$campo_vacio = false;
				
		if( $db->NextRow() )
		{
			if( $db->row["CUANTOS"] == 0 )
				$campo_vacio = true;
		}
		
		// borrar los auxiliares
		if( $campo_vacio )
		{
			$db->ExecSQL( "DELETE FROM cfgplantillas  " .
							  "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PLANTILLA=$id_plantilla and ID_CAMPO='$idcampo' and (DATO='X1' or DATO='X2'); " );
		}
		
		$db->Close();
						  
		
	}	
	else if( $action == "add_campo" )
	{
		$idcampo = read_param( "idcampo", "", 0 );
		$campos  = explode( ":", $idcampo );
		
		$db_verif = new DB();
		
		for( $i=0; $i<count($campos); $i++ )
		{			
			// este array se llenará con el primer subcampo disponible
			// o con ambos indicadores
			$subcampos = "";
			
			$db_verif->Open( "SELECT CODIGO,OBSOLETO,NIVEL_MARC FROM marc_codigo21 " . 
							 "WHERE ID_CAMPO='" . $campos[$i] . "' and (NIVEL_MARC=5 or NIVEL_MARC=9) ");

			$hay_subcampos = false;
			while( $db_verif->NextRow() )
			{
				if( $db_verif->row["OBSOLETO"] != "S" )
				{
					if( $db_verif->row["NIVEL_MARC"]==5 )
					{
						if( $subcampos != "" )
							$subcampos .= ":";
							
						$subcampos .= $db_verif->row["CODIGO"];
					}
					else if( $db_verif->row["NIVEL_MARC"]==9 )
					{
						if( !$hay_subcampos )
						{
							if( $subcampos != "" )
								$subcampos .= ":";
							
							$subcampos .= $db_verif->row["CODIGO"];
							$hay_subcampos = true;
						}
					}
				}
			}
			
			$db_verif->FreeResultset();
			
			$db_verif->Open( "SELECT COUNT(*) AS CUANTOS FROM cfgplantillas " . 
							 "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PLANTILLA=$id_plantilla and ID_CAMPO='" . $campos[$i] . "'");
			
			if( $db_verif->NextRow() )
			{
				if( $db_verif->row["CUANTOS"]==0 )
				{
					// dividir los subcampos
					$aSubCampos  = explode( ":", $subcampos );
					
					for( $j=0; $j<count($aSubCampos); $j++ )
					{
						// calcular ID_NUMCAMPO
						$num = 0;
						
						$db->Open(  "SELECT COUNT(*) AS CUANTOS, MAX(ID_NUMCAMPO) AS MAXIMO FROM cfgplantillas " .
									"WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PLANTILLA=$id_plantilla " );
						
						if( $db->NextRow() )
						{
							if( $db->row["CUANTOS"] == 0 )
								$num = 1;
							else
								$num = $db->row["MAXIMO"] + 1;
						}
						$db->FreeResultset();					
					
						$db->ExecSQL( "INSERT INTO cfgplantillas (ID_BIBLIOTECA, ID_PLANTILLA, ID_NUMCAMPO, ID_CAMPO, DATO ) " .
									  "VALUES ($id_biblioteca, $id_plantilla, $num, '$idcampo', '" . $aSubCampos[$j] . "') " );
					}
				}
			}
			
			$db_verif->FreeResultset();
		}
		
	}
	else if( $action == "add_subcampo" )
	{
		$idcampo    = read_param( "idcampo", "", 0 );
		$idsubcampo = read_param( "idsubcampo", "", 0 );
		
		$subcampos = explode( ":", $idsubcampo );
		
		$db_verif = new DB();
		
		for( $i=0; $i<count($subcampos); $i++ )
		{
			$db_verif->Open( "SELECT COUNT(*) AS CUANTOS FROM cfgplantillas " . 
							 "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PLANTILLA=$id_plantilla and ID_CAMPO='$idcampo' and DATO='" . $subcampos[$i]. "' ");
			
			if( $db_verif->NextRow() )
			{
				if( $db_verif->row["CUANTOS"]==0 )
				{
					$num = 0;
					
					$db->Open(  "SELECT COUNT(*) AS CUANTOS, MAX(ID_NUMCAMPO) AS MAXIMO FROM cfgplantillas " .
								"WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PLANTILLA=$id_plantilla " );
					
					if( $db->NextRow() )
					{
						if( $db->row["CUANTOS"] == 0 )
							$num = 1;
						else
							$num = $db->row["MAXIMO"] + 1;
					}
					$db->FreeResultset();
					
					$db->ExecSQL( "INSERT INTO cfgplantillas (ID_BIBLIOTECA, ID_PLANTILLA, ID_NUMCAMPO, ID_CAMPO, DATO ) " .
								  "VALUES ($id_biblioteca, $id_plantilla, $num, '$idcampo', '" . $subcampos[$i] . "') " );
				}
			}
			
			$db_verif->FreeResultset();
		}
		
	}
	else if( $action == "delete_template" )	
	{
		$descrip = read_param( "descrip", "" );
		ask_user_confirmation( "$BTN_DELETE_TEMPLATE ($descrip_plantilla -- $descrip)", $LABEL_SINGLE_TERM, "SELECT COUNT(*) AS CUANTOS FROM cfgplantillas WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PLANTILLA=$id_plantilla", 
				"conf_templates_cat.php?type=$type&id_plantilla=$id_plantilla&action=delete_confirmed" );
				
		die("");
	}
	else if( $action == "delete_confirmed" )	
	{
		$db->ExecSQL( "DELETE FROM cfgplantillas  " .
				      "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PLANTILLA=$id_plantilla" );
					  
		$db->ExecSQL( "DELETE FROM cfgplantillas_nombres  " .
				      "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PLANTILLA=$id_plantilla" );
		
		$info = 30;
		
	}

	// Draw an html head
	include "../basic/head_handler.php";
	HeadHandler( "$LABEL_HEADER " . ($descrip_plantilla!="" ? " -- " : "") . $descrip_plantilla, "../" );

?>

<script type="text/javascript" src="../utils.js"></script>
<script type="text/javascript">

 function change_st_modified( btnSave )
 {
	var the_div = document.getElementsByName( btnSave );
	
	if( the_div.length > 0 )
	{
		the_div[0].style.display = "inline";
		
		var cur_div = the_div[0].parentNode.parentNode;
		
		cur_div.style.backgroundColor = "#EFF9BE";
	}
 }
 
 // hace el submit para guardar el cambio de valor
 // does the submit to save new value of subfield
 function saveChangeValueSubCampo( idplantilla, idcampo, idsubcampo, input_name )
 {
	var the_input = document.getElementsByName( input_name );
	
	if( the_input.length > 0 )
	{
		location.href = "conf_templates_cat.php?type=<?php echo $type;?>&id_plantilla=" + idplantilla + "&action=update_valor_subcampo&idcampo=" + idcampo + "&idsubcampo=" + idsubcampo + "&valor=" + the_input[0].value;
	}
 }
 
 // hace el submit para guardar un cambio de valor en indicadores
 function saveChangeValueIndicadores( idplantilla, idcampo, input_name_aux1, input_name_aux2 )
 {
	var url = "conf_templates_cat.php?type=<?php echo $type;?>&id_plantilla=" + idplantilla + "&action=update_valor_auxiliares&idcampo=" + idcampo;
	
	if( input_name_aux1 != "" )
	{
		var the_input = document.getElementsByName( input_name_aux1 );
		if( the_input.length > 0 )
		{
			var val = the_input[0].value;
			
			if( val == "#" )
				val = "UNDEF";

			url = url + "&valoraux1=" + val;
		}
	}
	
	if( input_name_aux2 != "" )
	{
		var the_input = document.getElementsByName( input_name_aux2 );
		if( the_input.length > 0 )
		{
			var val = the_input[0].value;
			
			if( val == "#" )
				val = "UNDEF";

			url = url + "&valoraux2=" + val;
		}
	}	
	
	//alert( url );
	location.href = url;
 }

 // borra un subcampo
 // remove a subfield
 function removeSubCampo( idplantilla, idcampo, idsubcampo )
 {
	if( confirm( "<?php echo $WARINIG_MSG_REMOVE_2;?>") )
	{
		location.href = "conf_templates_cat.php?type=<?php echo $type;?>&id_plantilla=" + idplantilla + "&action=remove_subcampo&idcampo=" + idcampo + "&idsubcampo=" + idsubcampo;
	}
 }
 
 // Agregar un SubCampo
 function agregarsubcampo( idplantilla, idcampo )
 {
	var nwidth = screen.width;
	var nheight = screen.height; 
	var url = "gral_elegir_subcampo.php?id_plantilla=" + idplantilla + "&id_campo=" + idcampo;
	var input_aux_params = document.getElementsByName( "aux_params" ); 
	
	input_aux_params[0].value = "id_plantilla=" + idplantilla + "&idcampo=" + idcampo;
	
	if( <?php echo allow_use_of_popups() ? "1" : "0"; ?> )
		window.open( url, "elegir_subcampo", "WIDTH=" + (nwidth-50) + ",HEIGHT=" + (nheight-120) + ",TOP=20,LEFT=30,resizable=yes,scrollbars=yes,status=yes" );
	else
		js_ChangeLocation( url );

	window.status='';
 }
 
 function nuevosCampos() 
 {
	var input_aux_params = document.getElementsByName( "aux_params" ); 
	
	if( input_aux_params.length > 0 )
	{
		location.href = "conf_templates_cat.php?id_plantilla=<?php echo $id_plantilla;?>&type=<?php echo $type;?>&action=add_campo" + input_aux_params[0].value;
	}
 } 
 
 function nuevosSubCampos() 
 {
	var input_aux_params = document.getElementsByName( "aux_params" ); 
	
	if( input_aux_params.length > 0 )
	{
		location.href = "conf_templates_cat.php?id_plantilla=<?php echo $id_plantilla;?>&type=<?php echo $type;?>&action=add_subcampo&" + input_aux_params[0].value;
		//alert( "conf_templates_cat.php?type=<?php echo $type;?>&action=add_subcampo&" + input_aux_params[0].value );
	}
 }
 
 // Crear una nueva plantilla
 // Create a template
 function createNewTemplate() 
 {
	var nwidth = screen.width;
	var nheight = screen.height; 
	var url = "conf_templates_createnew.php?type=<?php echo $type;?>";
	
	if( <?php echo allow_use_of_popups() ? "1" : "0"; ?> )
		window.open( url, "elegir_subcampo", "WIDTH=" + (nwidth-200) + ",HEIGHT=" + (nheight-320) + ",TOP=100,LEFT=100,resizable=yes,scrollbars=yes,status=yes" );
	else
		js_ChangeLocation( url );

	window.status=''; 
 } 
 
 // Eliminar una plantilla
 // Delete a template
 function removeTemplate( id_plantilla, nombre_plantilla )
 {
	var nwidth = screen.width;
	var nheight = screen.height; 
	var url = "conf_templates_cat.php?type=<?php echo $type;?>&action=delete_template&id_plantilla="+id_plantilla+"&descrip=" + nombre_plantilla;
	
	js_ChangeLocation( url );

	window.status='';  
 }
 
 // Eliminar una plantilla
 // Delete a template
 function editTemplateProperties( id_plantilla )
 {
	var nwidth = screen.width;
	var nheight = screen.height; 
	var url = "conf_templates_createnew.php?type=<?php echo $type;?>&action=edit&id_plantilla=" + id_plantilla;
	
	if( <?php echo allow_use_of_popups() ? "1" : "0"; ?> )
		window.open( url, "elegir_subcampo", "WIDTH=" + (nwidth-200) + ",HEIGHT=" + (nheight-320) + ",TOP=100,LEFT=100,resizable=yes,scrollbars=yes,status=yes" );
	else
		js_ChangeLocation( url );

	window.status=''; 
 } 
 
 function showDialogIndicadores( campo, input_name, id )
 {
	var nwidth = screen.width;
	var nheight = screen.height; 
	var id_control = document.getElementsByName( input_name );
	var url = "gral_indicadores.php?campo='" + campo + "'&control=" + input_name + "&id=" + id;
	
	if( id_control.length > 0 )
	{  
		url = url + "&val=" + id_control[0].value; 
	}
	
	var ret = showModalDialog( url, "", "dialogLeft:100px;dialogWidth:760px;dialogHeight:500px;center:yes;status:no;" );
	
	if( ret != null )
	{
		if( id_control.length > 0 )
		{
			id_control[0].value = ret;
			
			var save_aux_btn = document.getElementsByName( "btnSaveAuxs_Campo_" + campo );
			
			if( save_aux_btn.length > 0 )
			{
				save_aux_btn[0].style.display = "inline";
				
				var parent_div = save_aux_btn[0].parentNode.parentNode.parentNode;
				
				parent_div.style.backgroundColor = "#EFF9BE";				
			}
		}
	}
 } 
 
 function showDialogSelectField()
 {
	var nwidth = screen.width;
	var nheight = screen.height; 
	var input_name = "txtNewField";
	var id_control = document.getElementsByName( input_name );
	var url = "gral_elegir_campo.php?&control=" + input_name;
	
	if( id_control.length > 0 )
	{  
		url = url + "&val=" + id_control[0].value; 
	}
	
	var ret = showModalDialog( url, "", "dialogLeft:100px;dialogWidth:800px;dialogHeight:600px;center:yes;status:no;" );
	
	if( ret != null )
	{
		if( id_control.length > 0 )
		{
			id_control[0].value = ret;
			
			var save_aux_btn = document.getElementsByName( "btnSaveAuxs_Campo_" + campo );
			
			if( save_aux_btn.length > 0 )
			{
				save_aux_btn[0].style.display = "inline";
				
				var parent_div = save_aux_btn[0].parentNode.parentNode.parentNode;
				
				parent_div.style.backgroundColor = "#EFF9BE";				
			}
		}
	}
 } 
 
 function edit_template( obj )
 {
	var url = "conf_templates_cat.php?type=<?php echo $type;?>&id_plantilla=" + obj.value;
	
	js_ChangeLocation( url );
 }
 
 
</script>


<STYLE type="text/css">
 
  #datos_generales 
  {
	font-size: 90%;
  }

 #contenido_principal {
   float: left;
   width: 90%;
  }

 #contenido_adicional {
   float: right; 
   width: 5%;
  }

  .header_minitext
  {	
	font-size: 12px;
  }
  
  .minitext
  {	
	font-size: 11px;
  }
  
</STYLE>

<body id="home">

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
    <h2><?php echo $descrip_plantilla; ?></h2>
    <p><?php echo $LABEL_INTRO_V2;?>&nbsp;<input type='button' class='boton' value='<?php echo $BTN_CREATE_NEW_TEMPLATE;?>' onClick='javascript:createNewTemplate();'>&nbsp;&nbsp;	
	<input type='button' class='boton' value='<?php echo $BTN_GOBACK;?>' onClick='location.href="conf_templates.php";'>
	<br>
	</p>
	
	<?php	
		if( $info == 30 )
		{
			echo "<div class=caja_info>";
			echo " <strong>$INFO_DELETETEMPLATE_DONE</strong>";			
			echo "</div>";
		}
	?>

	<div class="caja_con_ligas">
	
		<div class="lista_elementos_indexada" style='width: 95%; '>
		  
		  <span><?php echo $LABEL_CHOOSE_TEMPLATE;?></span>&nbsp;
		  
		  <?php
		  
			$db->Open( "SELECT a.* " .
					   "FROM cfgplantillas_nombres a " .
					   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TIPO='$type'" );

			echo "<SELECT id='cmb_plantillas' name='cmb_plantillas' onChange='javascript:edit_template(this)'>\n\n";
					   
			echo "<OPTION value='-1'> --- " . $LABEL_CHOOSE_TEMPLATE. " -- </OPTION>\n"; 
			
			$nombre_plantilla = "";
				
			while( $db->NextRow() ) 
			{
				$str_selected = "";
				
				if( $db->row["ID_PLANTILLA"] == $id_plantilla )
				{
					$nombre_plantilla = $db->row["NOMBRE_PLANTILLA"];
					$str_selected = "SELECTED";
				}
					
				echo "<OPTION value='" . $db->row["ID_PLANTILLA"] ."' $str_selected>" . $db->row["NOMBRE_PLANTILLA"] . "</OPTION>\n"; 
			}			
					   
			echo "</SELECT >\n\n";
			
			$db->Close();

			if( $id_plantilla != 0 )
			{
				echo "<div style='display:inline; width:20px; height:20px;'><a title='$HINT_DELETE_TEMPLATE' href='javascript:removeTemplate($id_plantilla,\"$nombre_plantilla\");'><img src='../images/icons/cut.gif'></a></div>";
				echo "<div style='display:inline; width:20px; height:20px;'><a title='$HINT_EDIT_TEMPLATE' href='javascript:editTemplateProperties($id_plantilla);'><img src='../images/icons/edit.gif'></a></div>";
				echo "<div style='display:inline;'><input type=button class='boton' name='btnAddField' value='Agregar Campo' onClick='javascript:showDialogSelectField();'></div><br>";
				
				$marc_record = new record_MARC21( getsessionvar("id_biblioteca"), $db, getsessionvar("language") );
				$marc_record->InicializarRegistroMARC21_DesdePlantilla( $id_plantilla, true, true );
				
				echo "<br><div>";
				
				echo "<span><strong>$LABEL_MATERIAL</strong></span>&nbsp;";
				
				if( $marc_record->cTipoMaterial == "" )
				{
					echo "$HINT_MATERIAL_IS_MISSING";
				}
				else
				{
					$db->Open( "SELECT DESCRIPCION, DESCRIPCION_ENG, DESCRIPCION_PORT, CODIGO_MARC, CODIGO_MARC_ENG, CODIGO_MARC_PORT, ICONO " . 
							   "FROM marc_material WHERE ID_TIPOMATERIAL='$marc_record->cTipoMaterial'" );
					
					$icono = "";
					
					if( $db->NextRow() )
					{
						echo "<img src='../" . $db->row["ICONO"]. "'>";
						echo "(" . get_translation( $db->row["CODIGO_MARC"], $db->row["CODIGO_MARC_ENG"], $db->row["CODIGO_MARC_PORT"] ) . ") ";
						echo get_translation( $db->row["DESCRIPCION"], $db->row["DESCRIPCION_ENG"], $db->row["DESCRIPCION_PORT"] );
					}
					
					$db->Close();
				}
				echo "</div>";
				
				
				// Campo Encabezado
				$objCampo_Header = $marc_record->BuscarCampo( "$$$" );
				
				echo $objCampo_Header->cValor;
				
				if( $objCampo_Header )
				{
					$objCampo_Header->AgregarSubCampo( "05", "", "", $marc_record->FEstadoRegistro, "", "" );
					$objCampo_Header->AgregarSubCampo( "06", "", "", $marc_record->FTipoRegistro, "", "" );
					$objCampo_Header->AgregarSubCampo( "07", "", "", $marc_record->FNivelBibliografico, "", "" );						
					$objCampo_Header->AgregarSubCampo( "17", "", "", $marc_record->FNivelCodificacion, "", "" );
					$objCampo_Header->AgregarSubCampo( "18", "", "", $marc_record->FFormaCatalogacion, "", "" );
				}
				
				// Campo Encabezado
				$objCampo_General = $marc_record->BuscarCampo( "008" );
				
				if( $objCampo_General )
				{
					$objCampo_General->AgregarSubCampo( "06", "", "", $marc_record->F008_TipoFechaEstadoPub, "", "" );
					$objCampo_General->AgregarSubCampo( "15..17", "", "", $marc_record->F008_LugarPublicacion, "", "" );
					$objCampo_General->AgregarSubCampo( "35..37", "", "", $marc_record->F008_Idioma, "", "" );
					$objCampo_General->AgregarSubCampo( "38", "", "", $marc_record->F008_RegistroModificado, "", "" );
					$objCampo_General->AgregarSubCampo( "39", "", "", $marc_record->F008_FuenteCatalogacion, "", "" );
//				 else if( $row["DATO"] == "07..10" ) $this->F008_Fecha_1   			 = $row["VALOR_DEFAULT"];
			 //else if( $row["DATO"] == "11..14" ) $this->F008_Fecha_2   			 = $row["VALOR_DEFAULT"];
				}
				
				$total_campos = $marc_record->ContarCampos();
				
				echo "<br>";
				echo "<table width='100%'>";
				echo " <tr>" .
					  "  <td class='cuadricula columna columnaEncabezado' width=40px>$LBL_TBL_COLUMN1</td>" .
					  "  <td class='cuadricula columna columnaEncabezado' width=350px>$LBL_TBL_COLUMN2</td>" . 
					  "  <td class='cuadricula columna columnaEncabezado' width=150px>$LBL_TBL_COLUMN3</td>" .
					 " </tr>\n";
				
				for( $i=0; $i<$total_campos; $i++ )
				{
					$objCampo = $marc_record->ObtenerCampoMARC($i);
					$idcampo = $objCampo->cIDCampo;
					
					$cStrAuxiliares = "";
					$input_name_Aux1 = "";
					$input_name_Aux2 = "";
					
					if( $objCampo->objID1 != NULL )
					{
						$input_name_Aux1 = "inputAux1_" . $idcampo;
						$cStrAuxiliares .= "X1 <input name='$input_name_Aux1' id='$input_name_Aux1' type=button value='" . $objCampo->objID1->cValor . "' maxlength='2' size='2' style='width: 20px;' onClick='javascript:showDialogIndicadores( \"$idcampo\", \"$input_name_Aux1\", 1 );'>";
					}
					
					if( $objCampo->objID2 != NULL )
					{
						if( $cStrAuxiliares != "" )
							$cStrAuxiliares .= "&nbsp;&nbsp;&nbsp;";
						$input_name_Aux2 = "inputAux2_" . $idcampo;
						
						$cStrAuxiliares .= "X2 <input name='$input_name_Aux2' id='$input_name_Aux2' type=button value='" . $objCampo->objID2->cValor . "' maxlength='2' size='2' style='width: 20px;' onClick='javascript:showDialogIndicadores( \"$idcampo\", \"$input_name_Aux2\", 2 );'>";
					}
					
					echo "<tr onMouseOver='javascript:Hilite(this)' onMouseOut='javascript:UnHilite(this)'>" .
						 "  <td class='cuadricula columna'><strong>" . $objCampo->cIDCampo . "</strong></td>" .
						 "  <td class='cuadricula columna header_minitext'><strong>" . $objCampo->ObtenerDescripcion() . "</strong></td>" .
						 "  <td class='cuadricula columna header_minitext'><strong>$cStrAuxiliares";
					
					if( $cStrAuxiliares != "" )
					{
						$saveAux = "btnSaveAuxs_Campo_" . $idcampo;
						echo "<div style='display:none;' name='$saveAux' id='$saveAux'>" . 
								 "<a href='javascript:saveChangeValueIndicadores($id_plantilla,\"$idcampo\",\"$input_name_Aux1\",\"$input_name_Aux2\");'><img src='../images/icons/save.png'></a>".
								 "</div>";							 
					}
						 
					echo "</strong></td></tr>\n";
					
					$subcampos = $objCampo->ContarSubcampos();						
					
					for( $j=0; $j<$subcampos; $j++ )
					{
						$objSubCampo =  $objCampo->ObtenerSubCampo($j);
						$idsubcampo = $objSubCampo->cIDSubCampo;
						
						echo " <tr>" .
							  "  <td class='cuadricula columna'>&nbsp;</td>" .
							  "  <td class='cuadricula columna minitext'>";
							  
						$input_name = "inputSubCampo_" . $idcampo . "_" . $idsubcampo;

						echo "<div style='float:left; display:inline; width:20px; height:20px;'><a title='$HINT_REMOVE_SUBFIELD' href='javascript:removeSubCampo($id_plantilla,\"$idcampo\",\"$idsubcampo\");'><img src='../images/icons/cut.gif'></a></div>";
						echo "<div style='float:left; width: 90%;'><strong>" . $objSubCampo->cIDSubCampo . "</strong> " . $objSubCampo->ObtenerDescripcion() . "</div><br>";

						$saveBtnName = "btnSaveSubCampo_" . $idcampo . "_" . $idsubcampo;
						
						if( $j==$subcampos-1 )
						{	
							if( $objCampo->cIDCampo != "$$$" )
							{
								echo "<div style='float:right;'><img src='../images/icons/add.png'>&nbsp;<a href='javascript:agregarsubcampo($id_plantilla,\"$idcampo\");'>&nbsp;$LBL_ADD_OTHER_SUBFIELD</a></div>";
							}
						}
						
						if( $objCampo->bCampoControl )
							$size='70px';
						else
							$size='250px';
							
						echo "   </td>" .
							 "  <td valign='top' class='cuadricula columna minitext'><input name='$input_name' id='$input_name' value='" . $objSubCampo->cValor. "' maxlength='100' size='55' style='width: $size;' onChange='javascript:change_st_modified(\"$saveBtnName\");'>&nbsp;";
						
						echo "<div style='display:none;' name='$saveBtnName' id='$saveBtnName'>" . 
							 "<a href='javascript:saveChangeValueSubCampo($id_plantilla,\"$idcampo\",\"$idsubcampo\",\"$input_name\");'><img src='../images/icons/save.png'></a>".
							 "</div>";							 
						echo " </td></tr>\n";
					}
				}
				
				$marc_record->destroy();
				
				echo "</table>";
			}
			
			if( $db->numRows == 0 )
			{	
				echo "<br><br><h1>$LBL_INFO_NO_TEMPLATES $descrip_plantilla</h1>";
			}
			
			$db->FreeResultSet();
		  
		   ?>
          
        </div>
		
	</div><!-- caja_con_ligas -->	
	
	<!-- input's donde se colocarán valores al momento de agregar un campo o subcampos -->
	<input type=hidden class=hidden name='aux_params' id='aux_params' value=''>

 </div> <!-- contenido_principal -->

 <div id="contenido_adicional">
  &nbsp;
 </div> <!-- contenido_adicional -->

</div>
<!-- end div bloque_principal -->

<?php  display_copyright(); 

  $db->destroy();

?>

</div><!-- end div contenedor -->

</body>

</html>
