<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  10 abr 2009: Se crea el archivo PHP para editar/crear/eliminar terminos del tesauro.
	  30 abr 2009: Se ajusta la pantalla y se hace el UPDATE
	  
	  PENDIENTE:
	  
		a) editar el término padre (que debe ser un macrotermino)
	  
     */
		
	include "../funcs.inc.php";
	include "../actions.inc.php";
	include "../basic/bd.class.php";
	
	include_language( "global_menus" );

	check_usuario_firmado(); 

	include_language( "conf_thesaurus" );		// archivo de idioma

	/** INICIO - FUNCIONES DE MODIFICACION/CREACION/BORRADO **/
	
	$id_term = "$LBL_TO_BE_ASIGNED";
	$term_name      = "";
	$code			= "";
	$description	= "";
	$use_instead 	= "";
	$source 		= "";
	$source_notes	= "";
	$term_padre = "";
	$macro_termino = "";

	$action = read_param( "action", "" );  // _POST o _GET
	$id_categoria = read_param( "id_categoria", "", 0 );  // _POST o _GET  (ABORTANDO EN EXCEPCION)
	$id_subcategoria = read_param( "id_subcategoria", "", 0 );  // _POST o _GET  (ABORTANDO EN EXCEPCION)
	
	$error = 0;
	
	$id_biblioteca = getsessionvar("id_biblioteca");
	$id_red = getsessionvar("id_red");

	if( $action == "create_new" )
	{
		// generar el nuevo ID del grupo
		$next_id_term = 0;
		
		$db = new DB( "SELECT MAX(ID_TERMINO) AS MAXID, COUNT(*) AS CUANTOS " .
					  " FROM tesauro_terminos WHERE ID_RED=$id_red" );
		
		if ($db->NextRow() ) 
		{
			if( $db->Field("CUANTOS") == 0 ) 
				$next_id_term = 1;
			else
				$next_id_term = $db->Field("MAXID") + 1;
		}
			
		$db->FreeResultset();
		
		$id_term    = $_POST["id_term"];
		$term_name	= $_POST["txt_term"];
		$code		= $_POST["txt_code"];
		$description = $_POST["txt_description"];
		
		$parent = $_POST["id_subcategoria"];
		
		if( $parent == "" )
			$parent = "NULL";
		
		$macroterm = "N";
		
		if( isset($_POST["chk_macroterm"]) )
			$macroterm = "S";
			
		$use = $_POST["txt_use"];
		
		$source = $_POST["txt_source"];
		$source_notes = $_POST["txt_source_notes"];
		
		$db->sql  = "INSERT INTO tesauro_terminos ( ID_RED, ID_TERMINO, TERMINO, CODIGO_CORTO, DESCRIPCION, ";
		$db->sql .= "  MACROTERMINO, TERMINO_PADRE, USAR, FUENTE_AGENCIA, FUENTE_NOTAS ) ";
		$db->sql .= " VALUES ( $id_red, $next_id_term, '$term_name', '$code', '$description', ";
		$db->sql .= " '$macroterm', $parent, '$use', '$source', '$source_notes' ) ";

/**		if( $id_red == 1 )
		{
			echo $db->sql;
			die( "STOP" );
		}
**/
		$db->ExecSQL();
		
		$db->sql  = "INSERT INTO tesauro_terminos_categorias ( ID_RED, ID_TERMINO, ID_CATEGORIA ) ";
		$db->sql .= " VALUES ( $id_red, $next_id_term, $id_categoria ) ";
		$db->ExecSQL();
		
		agregar_actividad_de_usuario( CFG_CHANGE_THESAURUS, "Crear Termino $id_term, Categoría $id_categoria" );
		
		$action = "save_changes";  // acción derivada natural
		
		if( !allow_use_of_popups() )
		{
			if( $parent != 0 )
				ges_redirect( "conf_thesaurus_cat.php?id_categoria=$id_categoria&id_subcategoria=$id_subcategoria" );
			else
				ges_redirect( "conf_thesaurus_cat.php?id_categoria=$id_categoria" );
		}
			
		$error = 10;

	}
	else if( $action == "save_changes" )
	{
		$id_term    = $_POST["id_term"];
		$term_name	= $_POST["txt_term"];
		$code		= $_POST["txt_code"];
		$description = $_POST["txt_description"];
		
		$parent = $_POST["id_subcategoria"];
		
		if( $parent == "" )
			$parent = "NULL";
		
		$macroterm = "N";
		
		if( isset($_POST["chk_macroterm"]) )
			$macroterm = "S";
			
		$use_instead = $_POST["txt_use"];
		
		$source = $_POST["txt_source"];
		$source_notes = $_POST["txt_source_notes"];		
		
		$db = new DB;
		$db->sql  = "UPDATE tesauro_terminos SET TERMINO='$term_name', CODIGO_CORTO='$code', DESCRIPCION='$description', MACROTERMINO='$macroterm', ";
		$db->sql .= "   TERMINO_PADRE=$parent, USAR='$use_instead', FUENTE_AGENCIA='$source', FUENTE_NOTAS='$source_notes' ";
		$db->sql .= "WHERE ID_RED=$id_red and ID_TERMINO=$id_term";
		
		$db->ExecSQL();
		
		agregar_actividad_de_usuario( CFG_CHANGE_THESAURUS, "Cambios a Termino $id_term, Categoría $id_categoria" );
		
		$error = 20;
		
		if( !allow_use_of_popups() )
		{			
			if( $parent != 0 )
				ges_redirect( "conf_thesaurus_cat.php?id_categoria=$id_categoria&id_subcategoria=$parent" );
			else
				ges_redirect( "conf_thesaurus_cat.php?id_categoria=$id_categoria" );
		}
	}
	else if( $action == "edit" )
	{
		$id_term = $_GET["id_termino"];
		
		$db = new DB( "SELECT * FROM tesauro_terminos WHERE ID_RED=$id_red and ID_TERMINO=$id_term" );
		
		if( $db->NextRow() ) 
		{ 
			$term_name      = $db->Field("TERMINO");
			$code			= $db->Field("CODIGO_CORTO");
			$description	= $db->Field("DESCRIPCION");
			$use_instead 	= $db->Field("USAR");
			$source 		= $db->Field("FUENTE_AGENCIA");
			$source_notes	= $db->Field("FUENTE_NOTAS");
			
			$term_padre     =  $db->Field("TERMINO_PADRE");
			$macro_termino  =  $db->Field("MACROTERMINO");

	
			$action = "save_changes";  // acción derivada natural
		}
		
		$db->destroy();
		
		$error = 0;
	}
	else
	{
		$action = "create_new";  // acción por default
	}
	
	/** FIN FUNCIONES DE MODIFICACION/CREACION/BORRADO **/
	
	// Coloca un encabezado HTML <head>
	include "../basic/head_handler.php";
	HeadHandler( ($action == "create_new") ? $LBL_CREATETERM_HEADER_V1 : $LBL_CREATETERM_HEADER_V2, "../");
		
?>

<SCRIPT language="JavaScript">

	function validar( e )
	{
		var error = 0;
		
		if( document.edit_form.txt_term.value == "" )		
		{
			error = 1;
			alert( "<?php echo $VALIDA_MSG_EDIT_1;?>" );
			document.edit_form.txt_term.focus();
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

 #caja_datos1 {
   width: 780px; 
   }
  
  #buttonarea { border: 0px solid red;  }; 
  #btnActualizar { position:absolute; left:0em; } 
  #btnCancelar { position:absolute; left:17em; } 
  
</STYLE>

<body id="home">

<!-- contenedor principal -->
<div id="contenedor">

<?php 
   
	$descrip_categoria = "";
   
	$db = new DB( "SELECT DESCRIPCION FROM tesauro_categorias WHERE ID_RED=$id_red and ID_CATEGORIA=$id_categoria ");
					   
	if( $db->NextRow() )
	{
		$descrip_categoria = $db->row["DESCRIPCION"];
	}
	
	$db->FreeResultSet();
	
	if( $id_subcategoria != 0 )
	{
		$db->Open( "SELECT TERMINO, DESCRIPCION FROM tesauro_terminos WHERE ID_RED=$id_red and ID_TERMINO=$id_subcategoria ");
						   
		if( $db->NextRow() )
		{
			$descrip_categoria .= "/ " . $db->row["TERMINO"];
		}
		
		$db->FreeResultSet();
	}

 ?>
 
<div id="bloque_principal"> <!-- inicia contenido -->
 
 <div id="contenido_principal">

	<div class=caja_datos id=caja_datos1 >
		<h2>
		<?php 
		
			if( $action == "create_new" )
				echo $LBL_CREATETERM_HEADER_V1;
			else
				echo $LBL_CREATETERM_HEADER_V2;
			
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
					//echo "<div class=caja_info>";
					//echo " <strong>$SAVE_CREATED_DONE</strong>";
					//echo "</div>";
					
					SYNTAX_BEGIN_JavaScript();
					echo "alert( octal('$SAVE_CREATED_DONE') );";
					echo "window.opener.document.location.reload();";
					echo "window.close();";		
					SYNTAX_CLOSE_JavaScript();
					
					//echo "<SCRIPT language='javascript'>";
					//echo "  window.opener.location.reload();";
					//echo "</SCRIPT>";
				}
				else if( $error == 20 )
				{
					echo "<div class=caja_info>";
					echo " <strong>$SAVE_DONE</strong>";
					echo "</div>";
				}	

				echo "<SCRIPT language='javascript'>";
				echo "  window.opener.location.reload();";
				echo "</SCRIPT>";
			}
		 ?>		

			<form action="conf_tesauro_termino.php" method="post" name="edit_form" id="edit_form" class="forma_captura">
			  <input class='hidden' type='hidden' name="action" id="action" value="<?php echo $action;?>">
			  <input class='hidden' type='hidden' name="id_term" id="id_term" value="<?php echo $id_term; ?>">
			  <input class='hidden' type='hidden' name="id_categoria" id="id_categoria" value="<?php echo $id_categoria; ?>">
			  <input class='hidden' type='hidden' name="id_subcategoria" id="id_subcategoria" value="<?php echo $id_subcategoria; ?>">
			  
				<label><?php echo $LBL_ID_TERM;?></label>
				<span class="span_captura"><?php echo $id_term;?></span>
				<br>

				<dt>
					<label for="txt_term"><?php echo $LBL_TERM;?></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_term" id="txt_term" value="<?php echo $term_name;?>" size=60 maxlength=100/>
				</dd>
			  
				<dt>
					<label for="txt_code"><?php echo $LBL_CODE;?></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_code" id="txt_code" value="<?php echo $code;?>" size=20 maxlength=20>
				</dd>

				<dt>
					<label for="txt_description"><?php echo $LBL_DESCRIPTION;?></label>
				</dt>
				<dd>			
					<input class="campo_captura" type="text" name="txt_description" id="txt_description" value="<?php echo $description;?>" size=70 maxlength=200/>
				</dd>
				
				<br>
				<dt>
					<label><?php echo $LBL_ASIGN_TERM_TO;?></label>
				</dt>
				<dd>
					<span class="span_captura"><strong><?php echo $descrip_categoria;?></strong></span>
				</dd>
				<br>

				<?php
				if( $id_subcategoria == 0 )
				{
					echo $term_padre;
				?>
				<dt>
					<label><?php echo $LBL_MACROTERM;?></label>
				</dt>
				<dd>
					<input type='checkbox' class='checkbox' name='chk_macroterm' id='chk_macroterm' <?php echo (($macro_termino == "S") ? "checked" : ""); ?>>
				</dd>
				<br>
				<?php 
				}
				?>
				
				<dt>
					<label for="txt_use"><?php echo $LBL_USE_INSTEAD;?></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_use" id="txt_use" value="<?php echo $use_instead;?>" size=50 maxlength=100/>
				</dd>
			  
				<dt>
					<label for="txt_source"><?php echo $LBL_SOURCE;?></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_source" id="txt_source" value="<?php echo $source;?>" size=60 maxlength=100>
				</dd>
				
				<dt>
					<label for="txt_source_notes"><?php echo $LBL_SOURCE_NOTES;?></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_source_notes" id="txt_source_notes" value="<?php echo $source_notes;?>" size=60 maxlength=100>
				</dd>
				<br>
			  
				  <div id="buttonarea">
					<input id="btnGuardar" name="btnGuardar" class="boton" type="button" value="<?php echo $BTN_SAVE;?>"  onClick='javascript:validar();' />&nbsp;
					<input id="btnCancelar" name="btnCancelar" class="boton" type="button" value="<?php echo $BTN_CANCEL;?>" onClick='javascript:window.close();' />
				  </div>
			  <br> <!-- for IE -->
			  
			</form>
	  
	</div> <!-- caja_datos --> 

 </div> <!-- contenido_principal -->

 <div id="contenido_adicional">
	&nbsp;
  </div> <!-- contenido_adicional -->

</div>
<!-- end div bloque_principal -->

<?php  display_copyright(); ?>

</div><!-- end div contenedor -->

</body>

</html>