<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  17 nov 2009: Se crea el archivo PHP de especificación de parámetros de catalogación.
	  
     */
		
	include "../funcs.inc.php";
	include "../actions.inc.php";
	include "../basic/bd.class.php";
	
	include_language( "global_menus" );

	check_usuario_firmado(); 

	include_language( "conf_cat_params" );
		
	$puntuacion_auto = "";
	$marc100_forzoso = "";

	$the_action = read_param( "the_action", "" );
	$info_st = read_param( "info_st", "" );
	
	$id_red = 0;
	$error	= 0;
	$error_msg = "";
	
	$db = new DB();
	
	$db->Open( "SELECT a.ID_RED " . 
				"FROM cfgbiblioteca a " .  	
				"WHERE a.ID_BIBLIOTECA=" . getsessionvar( "id_biblioteca") );	
				
	if( $db->NextRow() )
	{
		$id_red = $db->row["ID_RED"];	
	}
	
	$db->Close();
		
	if( $the_action == "save" )
	{
		$nombre_biblioteca = $_POST["txt_nombre_biblioteca"];
		$nombre_director   = $_POST["txt_nombre_director"];
		
		$update_query  = "UPDATE cfgbiblioteca SET NOMBRE_BIBLIOTECA='$nombre_biblioteca', NOMBRE_DIRECTOR='$nombre_director',";
		$update_query .= "  EMAIL_DIRECTOR='$email_director', DOMICILIO='$domicilio', CIUDAD='$ciudad', PROVINCIA='$provincia', PAIS='$pais', TELEFONOS='$telefonos', ";
		$update_query .= "  TEMA='$skin', ARCHIVO_BANNER='$file_banner', IDIOMA='$language' ";
		$update_query .= "WHERE ID_BIBLIOTECA=" . getsessionvar( "id_biblioteca");
		
		$db->ExecSQL( $update_query );
		
		setsessionvar( "skin", "$skin" );
		setsessionvar( "file_banner", "$file_banner" );
		setsessionvar( "language_pref", "$language" );		
		
		agregar_actividad_de_usuario( CFG_CHANGE_LIBRARY_DATA, "" );
		
		$error = 10;
	}
	else if( $the_action == "create_new_auth" )
	{
		$field = read_param( "field", "", 1 );
		$subfield = read_param( "subfield", "", 1 );
		$cathegory = read_param( "cathegory", 0, 1 );
		$control_type = read_param( "control_type", "" );
		$strict = read_param( "strict", "", 1 );
		
		$cuantos = 0;
		
		$db->Open( "SELECT COUNT(*) AS CUANTOS " . 
				    "FROM marc_codigo21 a " .  	
					"WHERE a.ID_CAMPO='$field' and a.CODIGO='$subfield';" );
					
		if( $db->NextRow() )
		{
			$cuantos = $db->row["CUANTOS"];
		}
		
		$db->Close();		
					
		if( $cuantos == 0 )
		{
			$error = 5;
			$error_msg = $ALERT_FIELD_SUBFIELD_INCORRECT;
		}
		else
		{	
			// Segunda verificación
			$db->Open( "SELECT COUNT(*) AS CUANTOS " . 
						"FROM cfgautoridades a " .  	
						"WHERE a.ID_RED=$id_red and a.ID_CAMPO='$field' and a.CODIGO='$subfield';" );

			if( $db->NextRow() )
			{
				$cuantos = $db->row["CUANTOS"];
			}
			
			$db->Close();
			// Fin 2da verif
		
			if( $cuantos > 0 )
			{
				$error = 5;
				$error_msg = $ALERT_FIELD_SUBFIELD_ALREADY_EXIST;
			}
			else
			{	
				$sql = "INSERT INTO cfgautoridades " .
					   " (ID_RED, ID_CAMPO, CODIGO, ID_CATEGORIA, CODIGO_TERMINO ) " .
					   " VALUES ($id_red, '$field', '$subfield', $cathegory, '$control_type' ); ";
				$db->ExecSQL( $sql );
				
				$db->destroy();
				
				ges_redirect( "conf_cat_params.php?info_st=1" );
			}
		}
	}
	else if( $the_action == "edit_auth" )
	{
		$field = read_param( "field", "", 1 );
		$subfield = read_param( "subfield", "", 1 );
		$cathegory = read_param( "cathegory", 0, 1 );
		$control_type = read_param( "control_type", "" );
		$strict = read_param( "strict", "", 1 );
		
		$sql = "UPDATE cfgautoridades SET ID_CATEGORIA='$cathegory', CODIGO_TERMINO='$control_type', ESTRICTO='$strict' " .
			   "WHERE ID_RED=$id_red and ID_CAMPO='$field' and CODIGO='$subfield'; "; 

		$db->ExecSQL( $sql );
				
		$db->destroy();
				
		ges_redirect( "conf_cat_params.php?info_st=2" );
	}
	else if ( $the_action == "delete_auth" )
	{
		$valores = read_param( "values", "", 1 );
		
		$aValues = split( ";", $valores );
		
		for( $i=0; $i < count($aValues); $i++ )
		{
			if( $aValues[$i] != "" )
			{
				// Posicion 0 - Campo,  1 - SubCampo
				$codigos = split( "@", $aValues[$i] );
				
				$field = $codigos[0];
				$subfield = $codigos[1];
				
				$sql = "DELETE FROM cfgautoridades " .
					   "WHERE ID_RED=$id_red and ID_CAMPO='$field' and CODIGO='$subfield'; "; 

				$db->ExecSQL( $sql );				
			}
		}
		
		ges_redirect( "conf_cat_params.php?info_st=3" );

	}
	else
	{
		// Consulta NORMAL
		$db->Open( "SELECT a.CATALOG_PUNTUACION_AUTO, a.CATALOG_OBLIGATORIO_MARC100 " . 
				    "FROM cfgbiblioteca_config a " .  	
					"WHERE a.ID_BIBLIOTECA=" . getsessionvar( "id_biblioteca") );
				
		if( $db->NextRow() ) 
		{ 
			$puntuacion_auto = $db->row["CATALOG_PUNTUACION_AUTO"];
			$marc100_forzoso = $db->row["CATALOG_OBLIGATORIO_MARC100"];
		}
		
		$db->Close();
		
		$error = 0;
	}
	
	// Coloca un encabezado HTML <head>
	include "../basic/head_handler.php";
	HeadHandler( "$LBL_CFG_TITLE", "../");
	
	include( "../privilegios.inc.php" );
	verificar_privilegio( PRIV_CFG_CATALOGING_RULES, 1 );
	
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
	
	window.onload=function()
	{
		prepareInputsForHints();
	}
	
	function create_new_auth()
	{
		if( ShowDiv( "div_edit_authority" ) )
		{
			var div_edit_authority = js_getElementByName( "div_edit_authority" );
			div_edit_authority.style.zIndex = 1; // para que quede arriba de otros	

			js_setElementByName_InitValue( "id_action", "create_new_auth" );

			js_setElementByName_InitValue( "txt_field", "" );
			js_setElementByName_InitValue( "txt_subfield", "" );
			js_setElementByName_InitValue( "cmb_cathegories", "" );
			js_setElementByName_InitValue( "cmb_control_type", "" );
			js_setElementByName_InitValue( "chk_strict", "" );
		}
	}
	
	function closeEditDiv()
	{
		if( HideDiv( "div_edit_authority" ) )
		{			
			HideDiv( "div_descrip_title" );
			HideDiv( "div_error_title" );
		}	
	}

	function edit_auth( field, subfield, id_categoria, xcontrol, estricto )
	{
		if( ShowDiv( "div_edit_authority" ) )
		{
			var div_edit_authority = js_getElementByName( "div_edit_authority" );
			div_edit_authority.style.zIndex = 1;   // para que quede arriba de otros

			js_setElementByName_InitValue( "id_action", "edit_auth" );

			js_setElementByName_InitValue( "txt_field", field );
			js_setElementByName_InitValue( "txt_subfield", subfield );
			js_setElementByName_InitValue( "cmb_cathegories", id_categoria );
			js_setElementByName_InitValue( "cmb_control_type", xcontrol );
			js_setElementByName_InitValue( "chk_strict", estricto );
		} // end - ShowDiv
	}
	
	function saveAuthChanges()
	{
		var msg = "<?php echo $MSG_WANT_TO_SAVE;?>";
		var the_action = js_getElementByName_Value( "id_action" );

		if( the_action == "edit_auth" )
			var msg = "<?php echo $MSG_WANT_TO_EDIT;?>";

		if( confirm( msg ) )
		{
			var txt_field = js_getElementByName( "txt_field" );
			var txt_subfield = js_getElementByName( "txt_subfield" );
			var id_categoria = js_getElementByName( "cmb_cathegories" );
			var control_type = js_getElementByName( "cmb_control_type" );
			var is_strict_val = js_getElementByName_Value( "chk_strict" );
			var error = 0;
			
			if( is_strict_val == true )
				is_strict_val = "S";
			else if( is_strict_val == false )
				is_strict_val = "N";				

			//alert( is_strict_val );

			if( txt_field.value == "" )
			{
				alert( "<?php echo $ALERT_NO_FIELD_ENTERED;?>" );
				txt_field.focus();
				error = 1;
			}

			if( error == 0 )
			{
				if( txt_subfield.value == "" )
				{
					alert( "<?php echo $ALERT_NO_SUBFIELD_ENTERED;?>" );
					txt_subfield.focus();
					error = 1;
				}
			}

			if( error == 0 )
			{
				js_ChangeLocation( "conf_cat_params.php?the_action=" + the_action + 
									"&field=" + txt_field.value + 
									"&subfield=" + txt_subfield.value + 
									"&cathegory=" + id_categoria.value + 
									"&control_type=" + control_type.value +
									"&strict=" + is_strict_val );
			}
		}
	}
	
	function delete_auth()
	{
		var txt = "";
		var num_auths = js_getElementByName_Value( "num_auths" );
		var chk_obj;
		var val;
		
		for( i = 1; i <= parseInt(num_auths); i++ )
		{
			chk_obj = js_getElementByName_Value( "chk_auth_num_" + i );
			
			if( chk_obj )
			{
				val = js_getElementByName_Value( "auth_" + i );
				txt = txt + val + ";";
			}
		}
		
		js_ChangeLocation( "conf_cat_params.php?the_action=delete_auth&values=" + txt );
	}
	
</SCRIPT>

<STYLE type="text/css">

 .sp_hint { width: 150px; }

 #caja_datos1, #caja_datos2 {
   width: 750px; 
   }
   
  #btnActualizar { position:absolute; left:0em; } 
  #btnCancelar { position:absolute; left:17em; } 
  
 form.forma_captura label {
    width: 20em;
 }  
 
#div_edit_authority
{	
	display: none;
	position: absolute;
	background-color: #FCFBD0;
	border: 3px solid gray; 

	left: 250px;
	top: 100px;
	width: 480px;
	height: 190px;
	
	font-size: 90%;
}

#buttonArea2
{
	position: relative;
	top: -15px;
	float: none;
}

#contenido_adicional
{
	width: 170px;
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

	<div class=caja_datos id=caja_datos1 >
		<h1><?php echo $LBL_HEADER;?></h1><hr><br>
		
		<!--- INICIA POPUP CAMBIAR PASSWORD -->
		<div class="groupbox" id="div_edit_authority" name="div_edit_authority">
			<div style='float:left; width: 260px; font-size: 90%;'>
				
				<div style='display:inline; font-size:120%;'><strong><?php echo "Agregando Autoridad";?></strong></div>
				<br><br>
				
				<!-- ACTION  -->
				<input type='hidden' class='hidden' name='id_action' id='id_action' value=''>
				
				<dt>
					<label for='nvo_passwd'><?php echo $LBL_FIELD;?>
					</label><input class='campo_captura' type='text' name='txt_field' id='txt_field' value='' size='5' style='position: absolute; left: 120px; display:inline;'>
				</dt>
				<br>

				<dt>
					<label for='cnf_passwd'><?php echo $LBL_SUBFIELD;?></label>
					<input class='campo_captura' type='text' name='txt_subfield' id='txt_subfield' value='' size='5' style='position: absolute; left: 120px; display:inline;'>
				</dt>
				
				<br>
				
				<dt>
					<label for='cmb_cathegories' style='display:inline;'><?php echo $LBL_CATHEGORY;?></label>&nbsp;&nbsp;
					<select name='cmb_cathegories' id='cmb_cathegories' style='position: absolute; left: 120px; display:inline;'>
						<?php
							$db = new DB( "SELECT ID_CATEGORIA, DESCRIPCION, SISTEMA FROM tesauro_categorias " .
										  "WHERE ID_RED=" . getsessionvar("id_red") . 
										  "ORDER BY ID_CATEGORIA" );

							while( $db->NextRow() )
							{ 
								$id_categoria = $db->row["ID_CATEGORIA"];
								$descripcion   = $db->row["DESCRIPCION"];

								echo "<option value='$id_categoria'>$id_categoria - $descripcion</li>";
							}

							$db->Close();
						?>
					</select>
				</dt>				
				
				<br>
				
				<dt>
					<label><?php echo $LBL_CONTROL_TYPE;?></label>&nbsp;&nbsp;
					<select name='cmb_control_type' id='cmb_control_type' style='position: absolute; left: 120px; display:inline;'>
						<option value='C'><?php echo $LBL_CONTROL_BY_CODE;?></option>
						<option value='T'><?php echo $LBL_CONTROL_BY_TERM;?></option>
					</select>
				</dt>
				
				<br>
				
				<dt>
					<label><?php echo $LBL_IS_STRICT;?></label>&nbsp;&nbsp;
					<input type='checkbox' class='checkbox' name='chk_strict' style='position: absolute; left: 120px; display:inline;'> 
				</dt>				

				<div style='display: inline; position: relative; top: 15px; left: 100px;' >
					<input type="button" class="boton" value="<?php echo $BTN_SAVE;?>" name="btnSavePwd" id="btnSavePwd" onClick="javascript:saveAuthChanges();">
					<input type="button" class="boton" value="<?php echo $BTN_CANCEL;?>" name="btnSavePwd" id="btnSavePwd" onClick="javascript:closeEditDiv();">
				</div>
				
			</div>
			
			
			<!-- close icon -->
			<div style="float:right; padding:0px; position: relative; top: -10px; margin:0px;">
				<br>
				<a href="javascript:closeEditDiv();"><img src="../images/icons/close_button.gif"></a>
			</div><br>
			<!-- close icon -->
			
			<br style='clear:all'>
			<br>
			
		</div>
		<!--- FIN POPUP CAMBIAR PASSWORD  -->	  		
		
		<?php

			if( $error == 10 )
			{
				echo "<div class=caja_info>";
				echo " <strong>$SAVE_DONE</strong>";
				echo "</div>";
			}			
		?>

			<form action="conf_system.php" method="post" name="cfg_system_form" id="cfg_system_form" class="forma_captura">
			  <input class=hidden type=hidden name="the_action" id="the_action" value="save">
			  
				<dt>
					<label for="txt_nombre_biblioteca"><?php echo $LBL_PUNCTUATION_AUTO;?></label>
				</dt>
				<dd>
					<select class="select_captura" name="cmb_punctuation_auto" id="cmb_punctuation_auto">
						<option value='S' <?php echo ($puntuacion_auto=="S") ? "SELECTED" : ""; ?>><?php echo $LBL_YES;?></option>
						<option value='N' <?php echo ($puntuacion_auto=="N") ? "SELECTED" : ""; ?>><?php echo $LBL_NO;?></option>
					</select>					
					
					<span class="sp_hint"><?php echo $HINT_PUNCTUATION_AUTO;?><span class="hint-pointer">&nbsp;</span></span>					
				</dd>
			  
				<dt>
					<label for="txt_nombre_director"><?php echo $LBL_MANDATORY_MARC100;?></label>
				</dt>
				<dd>
					<select class="select_captura" name="cmb_mandatory_marc100" id="cmb_mandatory_marc100">
						<option value='S' <?php echo ($marc100_forzoso=="S") ? "SELECTED" : ""; ?>><?php echo $LBL_YES;?></option>
						<option value='N' <?php echo ($marc100_forzoso=="N") ? "SELECTED" : ""; ?>><?php echo $LBL_NO;?></option>
					</select>					

					<span class="sp_hint"><?php echo $HINT_MANDATORY_MARC100;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				<br>
			  
			  <div id="buttonarea">
				<input id=btnActualizar class="boton" type="button" value="<?php echo $BTN_SAVE;?>" name="btnActualizar" onClick='javascript:validar();'>
				<input id=btnCancelar class="boton" type="button" value="<?php echo $BTN_CANCEL;?>" name="btnCancelar" onClick='javascript:window.history.back();'>
			  </div>
			  
			  <br style='clear:both;'>
			  <br>
			  
			</form>
	  
	</div> <!-- caja_datos --> 
	
	<br>
	<div class=caja_datos id=caja_datos2>
		<h1><?php echo $LBL_HEADER_2;?></h1><hr>
		<h2><?php echo $LBL_SUBTITLE_AUTHORITIES;?></h2>
		
		<?php 	

		echo "<div id='buttonArea2' align=right>";				
			echo " <input class='boton' type='button' value='$BTN_CREATE_NEW_AUTHORITY' onclick='javascript:create_new_auth();'>";
			echo " <input class='boton' type='button' value='$BTN_DELETE_AUTHORITY' onclick='javascript:delete_auth();'>";
			echo "</div>"; 	   		

		if( $error == 5 )
		{
			echo "<div class=caja_errores>";
			echo " <strong>$error_msg.</strong>";
			echo "</div>";
		}			
		if( $info_st == 1 )
		{
			echo "<div class=caja_info>";
			echo " <strong>$MSG_AUTH_RECORD_SAVED</strong>";
			echo "</div>";
		}
		if( $info_st == 2 )
		{
			echo "<div class=caja_info>";
			echo " <strong>$MSG_AUTH_RECORD_EDITED</strong>";
			echo "</div>";
		}
		if( $info_st == 3 )
		{
			echo "<div class=caja_info>";
			echo " <strong>$MSG_AUTH_RECORDS_DELETED</strong>";
			echo "</div>";		
		}

		?>
		
		<br>
		
		<?php
			
			echo "<table width='100%'>";
			echo "<tr>" .
				 " <td align='center' class='cuadricula columna columnaEncabezado' width='15px'>&nbsp;</td>" .
				 " <td align='center' class='cuadricula columna columnaEncabezado' width='35px'>$LBL_FIELD</td>" .
				 " <td align='center' class='cuadricula columna columnaEncabezado' width='35px'>$LBL_SUBFIELD</td>" . 
				 " <td class='cuadricula columna columnaEncabezado' width='300px'>$LBL_CATHEGORY</td>" .
				 " <td align='center' class='cuadricula columna columnaEncabezado' width='100px'>$LBL_CONTROL_TYPE</td>" .
				 " <td align='center' class='cuadricula columna columnaEncabezado' width='100px'>$LBL_IS_STRICT</td>" .
				 "</tr>";		
		
			$db->Open( "SELECT a.*, b.DESCRIPCION FROM cfgautoridades a LEFT JOIN tesauro_categorias b ON (b.ID_RED=a.ID_RED and b.ID_CATEGORIA=a.ID_CATEGORIA) WHERE a.ID_RED=$id_red ORDER BY a.ID_CAMPO, a.CODIGO " );
			
			while ($db->NextRow())
			{
				$i = $db->numRows;
				
				$tipo = "";
				
				$control_type = $db->row["CODIGO_TERMINO"];
				
				if( $db->row["CODIGO_TERMINO"] == "C" )
					$tipo = "Por Código";
				if( $db->row["CODIGO_TERMINO"] == "T" )
					$tipo = "Por Término";
				
				$estricto = $db->row["ESTRICTO"];
				
				$field = $db->row["ID_CAMPO"];
				$subfield = $db->row["CODIGO"];
				
				$link_on = "<a href='javascript:edit_auth( \"$field\", \"$subfield\", " . $db->row["ID_CATEGORIA"] . ", \"$control_type\", \"" . $estricto ."\" )'>";
				$link_off = "</a>";
					
				$tmp_val = $field . "@" . $subfield;
					
				echo "<tr>";
				echo " <td align='center' class='cuadricula'>";
				echo "   <input type='checkbox' class='checkbox' name='chk_auth_num_$i' id='chk_auth_num_$i'>";
				echo " </td>";
				echo " <td align='center' class='cuadricula columna'>$link_on" . $db->row["ID_CAMPO"] . "$link_off</td>";
				echo " <td align='center' class='cuadricula columna'>$link_on" . $db->row["CODIGO"] . "$link_off</td>";
				echo " <td class='cuadricula columna'>$link_on" . $db->row["ID_CATEGORIA"] . " - " . $db->row["DESCRIPCION"] . "$link_off</td>";
				echo " <td align='center' class='cuadricula columna'>" . $tipo . "</td>";
				echo " <td align='center' class='cuadricula columna'>" . ICON_DisplayYESNO( $estricto ) . "<input type='hidden' class='hidden' id='auth_$i' name='auth_$i' value='$tmp_val'></td>";
				echo "</tr>";
			}
			
			echo "</table>";
			
			echo "<input type='hidden' class='hidden' id='num_auths' name='num_auths' value='" . $db->numRows . "'>";
			echo "<br>";
			
			$db->Close();
		 ?>
		
	</div>

 </div> <!-- contenido_principal -->

 <div id="contenido_adicional">
	<?php echo $NOTES_AT_RIGHT;?>
  </div> <!-- contenido_adicional -->

</div>
<!-- end div bloque_principal -->

<?php  
	display_copyright(); 
	
	$db->destroy();
	
	?>

</div><!-- end div contenedor -->

</body>

</html>