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

	include_language( "conf_crearpersonas" );		// archivo de idioma

	/** INICIO - FUNCIONES DE MODIFICACION/CREACION/BORRADO **/
	
	include("../basic/bd.class.php");
	
	$id_persona = "$LBL_TO_BE_ASIGNED";
	
	$apellidos = "";
	$nombres   = "";
	
	$domicilio = "";
	$ciudad    = "";
	$provincia = "";
	
	$telefonos = "";
	$email	   = "";
	$observaciones = "";
	$website = "";
	$contacto = "";
	
	$the_action = read_param( "the_action", "" );
	
	$error = 0;
	
	$id_biblioteca = getsessionvar("id_biblioteca");
		
	if( $the_action == "create_new" )
	{
		// generar el nuevo ID de la persona
		$id_persona = 0;
		
		$db = new DB( "SELECT MAX(ID_PERSONA) AS MAXID, COUNT(*) AS CUANTOS FROM personas WHERE ID_BIBLIOTECA=$id_biblioteca" );
		
		if ($db->NextRow() ) 
			$id_persona = $db->Field("MAXID") + 1;
			
		$db->FreeResultset();
		
		$apellidos				= $_POST["txt_apellidos"];
		$nombres				= $_POST["txt_nombres"];

		$id_tipopersona		    = $_POST["cmb_id_tipopersona" ];

		$domicilio		= $_POST["txt_domicilio"];
		$ciudad			= $_POST["txt_ciudad"];
		$provincia		= $_POST["txt_provincia"];
		$pais			= $_POST["cmb_pais"];

		$telefonos	    = $_POST["txt_telefonos"];		
		$email			= $_POST["txt_email"];
		$website		= $_POST["txt_website"];		
		$contacto		= $_POST["txt_contacto"];
		$observaciones		= $_POST["txt_observaciones"];

		$db->sql  = "INSERT INTO personas ( ID_BIBLIOTECA, ID_PERSONA, ID_TIPOPERSONA, APELLIDOS, NOMBRES, DOMICILIO, CIUDAD, PROVINCIA, PAIS, ";
		$db->sql .= "  TELEFONOS, EMAIL, WEBSITE, CONTACTO ) ";
		$db->sql .= " VALUES ( $id_biblioteca, $id_persona, '$id_tipopersona', '$apellidos', '$nombres', '$domicilio', '$ciudad', '$provincia', '$pais', ";
		$db->sql .= " '$telefonos', '$email', '$website', '$contacto' ) ";
		$db->ExecSQL();
		
		require_once( "../actions.inc.php" );
		agregar_actividad_de_usuario( CFG_CONFIG_PERSONS_CREATE, "$ACTION_DESCRIP_CREATE $nombres $apellidos" );
		
		$error = 10;
		
		if( !allow_use_of_popups() )
			ges_redirect( "conf_personas.php?id_persona_created=$id_persona" );

	}
	else if( $the_action == "save_changes" )
	{
		$id_persona				= $_POST["id_persona"];
		$apellidos				= $_POST["txt_apellidos"];
		$nombres				= $_POST["txt_nombres"];

		$id_tipopersona		    = $_POST["cmb_id_tipopersona" ];

		$domicilio		= $_POST["txt_domicilio"];
		$ciudad			= $_POST["txt_ciudad"];
		$provincia		= $_POST["txt_provincia"];
		$pais			= $_POST["cmb_pais"];

		$telefonos	    = $_POST["txt_telefonos"];		
		$email			= $_POST["txt_email"];
		$website		= $_POST["txt_website"];		
		$contacto		= $_POST["txt_contacto"];
		$observaciones		= $_POST["txt_observaciones"];

		$db = new DB;
		$db->sql  = "UPDATE personas SET ID_TIPOPERSONA='$id_tipopersona', APELLIDOS='$apellidos', NOMBRES='$nombres',";
		$db->sql .= "  DOMICILIO='$domicilio', CIUDAD='$ciudad', PROVINCIA='$provincia', ";
		$db->sql .= "   PAIS='$pais', TELEFONOS='$telefonos', EMAIL='$email', WEBSITE='$website', CONTACTO='$contacto', OBSERVACIONES='$observaciones' ";
		$db->sql .= "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PERSONA=$id_persona";
		$db->ExecSQL();

		require_once( "../actions.inc.php" );
		agregar_actividad_de_usuario( CFG_CONFIG_PERSONS_EDIT, "$ACTION_DESCRIP_EDIT $nombres $apellidos" );

		$error = 20;

		if( !allow_use_of_popups() )
			ges_redirect( "conf_personas.php?id_persona_edited=$id_persona" );

	}
	else if( $the_action == "edit" )
	{
		$id_persona = $_GET["id_persona"];
		
		$db = new DB( "SELECT * FROM personas WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PERSONA=$id_persona" );
		
		if( $db->NextRow() ) 
		{ 
			$id_tipopersona		= $db->row["ID_TIPOPERSONA"];
			
			$apellidos			= $db->row["APELLIDOS"];
			$nombres			= $db->row["NOMBRES"];
			
			$domicilio			= $db->row["DOMICILIO"];
			$ciudad				= $db->row["CIUDAD"];
			$provincia			= $db->row["PROVINCIA"];
			$pais				= $db->row["PAIS"];
			
			$telefonos			= $db->row["TELEFONOS"];
			$email				= $db->row["EMAIL"];
			
			$website			= $db->row["WEBSITE"];
			$contacto			= $db->row["CONTACTO"];
			$observaciones		= $db->row["OBSERVACIONES"];
			
			$the_action = "save_changes";  // acción derivada natural
		}
		
		$db->destroy();
	}
	else if( $the_action == "delete" )
	{
		$personas = "";
		$personas_borradas = 0;
		
		if( isset($_GET["personas"]) )
		{
			$personas = $_GET["personas"];
			
			$personas = str_replace( "@", "ID_PERSONA=", $personas ); // 1st ocurrence
			$personas = str_replace( ":", " or ID_PERSONA=", $personas ); // other ocurrences
			
			$db = new DB( "DELETE FROM personas WHERE ID_BIBLIOTECA=$id_biblioteca and ($personas) " );
			
			$error = 30;
			
			$personas_borradas = $db->rowsAffected;
			
			$db->Destroy();
			
			require_once( "../actions.inc.php" );
			agregar_actividad_de_usuario( CFG_CONFIG_PERSONS_DELETE, "$ACTION_DESCRIP_DELETE $personas <$personas_borradas>" );
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
		
		if( document.edit_form.txt_apellidos.value == "" )		
		{
			error = 1;
			alert( "<?php echo $VALIDA_MSG_NONAME;?>" );
			document.edit_form.txt_apellidos.focus();
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

			<form action="conf_crearpersonas.php" method="post" name="edit_form" id="edit_form" class="forma_captura">
			  <input class='hidden' type='hidden' name="the_action" id="the_action" value="<?php echo $the_action;?>">
			  <input class='hidden' type='hidden' name="id_persona" id="id_persona" value="<?php echo $id_persona; ?>">
			  
				<label for="txt_nombre_biblioteca"><strong><?php echo $LBL_ID_PERSONA;?></strong></label>
				<span><?php echo $id_persona;?></span>
				<br><br>
			  
				<dt>
					<label for="cmb_id_tipopersona"><strong><?php echo $LBL_TIPO;?></strong></label>
				</dt>				
				<dd>
					<select id='cmb_id_tipopersona' name='cmb_id_tipopersona' class='select_captura'>
					<?php	
						$db = new DB( "SELECT ID_TIPOPERSONA, DESCRIPCION FROM cfgtipospersona ORDER BY ID_TIPOPERSONA " );
						
						while( $db->NextRow() )
						{ 
							$str_selected = ($id_tipopersona==$db->row['ID_TIPOPERSONA']) ? "selected" : "";
							
							echo "<option value='" . $db->row['ID_TIPOPERSONA'] . "' $str_selected>" . $db->row["DESCRIPCION"] . " &nbsp;&nbsp;&nbsp;</option>";
						}
						
						$db->Close();
					?>	
					</select>
					<span class="sp_hint"><?php echo $HINT_TIPOPERSONA;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
			  
				<dt>	
					<label for="txt_nombre_grupo"><strong><?php echo $LBL_APELLIDOS;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_apellidos" id="txt_apellidos" value="<?php echo $apellidos;?>" size=60 maxlength=50>
					<span class="sp_hint"><?php echo $HINT_APELLIDOS;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<dt>	
					<label for="txt_nombres"><strong><?php echo $LBL_NOMBRES;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_nombres" id="txt_nombres" value="<?php echo $nombres;?>" size=60 maxlength=100>
					<span class="sp_hint"><?php echo $HINT_NOMBRES;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
			  
				<dt>
					<label for="txt_domicilio"><strong><?php echo $LBL_DOMICILIO;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_domicilio" id="txt_domicilio" value="<?php echo $domicilio;?>" size=80 maxlength=250 >
					<span class="sp_hint"><?php echo $HINT_DOMICILIO;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>

				<dt>
					<label for="txt_ciudad"><strong><?php echo $LBL_CIUDAD;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_ciudad" id="txt_ciudad" value="<?php echo $ciudad;?>" size="50" maxlength="50">
				</dd>
			  
				<dt>
					<label for="txt_provincia"><strong><?php echo $LBL_PROVINCIA;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_provincia" id="txt_provincia" value="<?php echo $provincia;?>" size="50" maxlength="50">
				</dd>
			  
				<dt>
					<label for="cmb_pais"><strong><?php echo $LBL_PAIS;?></strong></label>
				</dt>
				<dd>
					<select class="select_captura" name="cmb_pais" id="cmb_pais">
						<option value='MEXICO'>México</option>
						<option value='USA'>U.S.A.</option>
						<option value='CANADA'>Canadá</option>
						<option value='GUATEMALA'>Guatemala</option>
						<option value='COLOMBIA'>Colombia</option>
						<option value='CHILE'>Chile</option>
						<option value='PERU'>Perú</option>
						<option value='ARGENTINA'>Argentina</option>
						<option value='ECUADOR'>Ecuador</option>
						<option value='BRAZIL'>Brazil</option>
					</select>
				</dd>
				
				<dt>
					<label for="txt_telefonos"><strong><?php echo $LBL_TELEFONOS;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_telefonos" id="txt_telefonos" value="<?php echo $telefonos;?>" size="50" maxlength="150">
					<span class="sp_hint"><?php echo $HINT_TELEFONOS;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<!-- Email  -->
				<dt>
					<label for="txt_email"><strong><?php echo $LBL_EMAIL;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_email" id="txt_email" value="<?php echo $email;?>" size="50" maxlength="255">
					<span class="sp_hint"><?php echo $HINT_EMAIL;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<dt>
					<label for="txt_website"><strong><?php echo $LBL_WEBSITE;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_website" id="txt_website" value="<?php echo $website;?>" size="70" maxlength="255">
					<span class="sp_hint"><?php echo $HINT_WEBSITE;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<dt>
					<label for="txt_contacto"><strong><?php echo $LBL_CONTACTO;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_contacto" id="txt_contacto" value="<?php echo $contacto;?>" size="70" maxlength="150">
					<span class="sp_hint"><?php echo $HINT_CONTACTO;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<dt>
					<label for="txt_observaciones"><strong><?php echo $LBL_OBSERVACIONES;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_observaciones" id="txt_observaciones" value="<?php echo $observaciones;?>" size="70" maxlength="250">
					<span class="sp_hint"><?php echo $HINT_OBSERVACIONES;?><span class="hint-pointer">&nbsp;</span></span>
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