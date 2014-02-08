<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  21 oct 2009: Se crea el archivo PHP para editar/crear/eliminar usuarios.

     */
		
	include "../funcs.inc.php";	
	
	include_language( "global_menus" );

	check_usuario_firmado(); 

	include_language( "conf_crearrestricciones" );		// archivo de idioma

	/** INICIO - FUNCIONES DE MODIFICACION/CREACION/BORRADO **/
	
	include("../basic/bd.class.php");
	
	$tipo_restriccion = "$LBL_TO_BE_ASIGNED";
	
	$descripcion = "";
	
	$impedir_consultas = "";
	$impedir_prestamos = "";
	$impedir_reservas  = "";
	$impedir_renovaciones = "";
	
	$the_action = read_param( "the_action", "" );
	
	$error = 0;
	
	$id_biblioteca = getsessionvar("id_biblioteca");
		
	if( $the_action == "create_new" )
	{
		// generar el nuevo ID de la restricción
		$tipo_restriccion = 0;
		
		$db = new DB( "SELECT MAX(TIPO_RESTRICCION) AS MAXID, COUNT(*) AS CUANTOS FROM cfgrestricciones WHERE ID_BIBLIOTECA=$id_biblioteca" );
		
		if ($db->NextRow() ) 
			$tipo_restriccion = $db->Field("MAXID") + 1;
			
		$db->FreeResultset();
		
		$descripcion			= $_POST["txt_descripcion"];
		
		$block_consultas  	= "";
		$block_reservas		= "";
		$block_prestamos	= "";
		$block_renovs		= "";
		
		if( isset($_POST["chk_impedir_consultas"]) )
			$block_consultas = "S";
			
		if( isset($_POST["chk_impedir_reservas"]) )
			$block_reservas = "S";			
			
		if( isset($_POST["chk_impedir_prestamos"]) )
			$block_prestamos = "S";

		if( isset($_POST["chk_impedir_renovaciones"]) )
			$block_renovs = "S";

		$db->sql  = "INSERT INTO cfgrestricciones ( ID_BIBLIOTECA, TIPO_RESTRICCION, DESCRIPCION, IMPEDIR_CONSULTAS, IMPEDIR_RESERVACIONES, IMPEDIR_PRESTAMOS, IMPEDIR_RENOVACIONES ) ";
		$db->sql .= " VALUES ( $id_biblioteca, '$tipo_restriccion', '$descripcion', '$block_consultas', '$block_reservas', '$block_prestamos', '$block_renovs' ); ";
		$db->ExecSQL();
		
		require_once( "../actions.inc.php" );
		agregar_actividad_de_usuario( CFG_CONFIG_RESTRICTIONS_CREATE, "$ACTION_DESCRIP_CREATE $descripcion" );
		
		$error = 10;
		
		if( !allow_use_of_popups() )
			ges_redirect( "conf_restricciones.php?id_restriccion_created=$tipo_restriccion" );

	}
	else if( $the_action == "save_changes" )
	{
		$tipo_restriccion 			= $_POST["tipo_restriccion"];
		$descripcion			= $_POST["txt_descripcion"];
		
		$block_consultas  	= "";
		$block_reservas		= "";
		$block_prestamos	= "";
		$block_renovs		= "";
		
		if( isset($_POST["chk_impedir_consultas"]) )
			$block_consultas = "S";
			
		if( isset($_POST["chk_impedir_reservas"]) )
			$block_reservas = "S";			
			
		if( isset($_POST["chk_impedir_prestamos"]) )
			$block_prestamos = "S";

		if( isset($_POST["chk_impedir_renovaciones"]) )
			$block_renovs = "S";

		$db = new DB;
		$db->sql  = "UPDATE cfgrestricciones SET TIPO_RESTRICCION='$tipo_restriccion', DESCRIPCION='$descripcion',";
		$db->sql .= "  IMPEDIR_CONSULTAS='$block_consultas', IMPEDIR_RESERVACIONES='$block_reservas', IMPEDIR_PRESTAMOS='$block_prestamos', IMPEDIR_RENOVACIONES='$block_renovs' ";
		$db->sql .= "WHERE ID_BIBLIOTECA=$id_biblioteca and TIPO_RESTRICCION='$tipo_restriccion';";
		$db->ExecSQL();

		require_once( "../actions.inc.php" );
		agregar_actividad_de_usuario( CFG_CONFIG_RESTRICTIONS_EDIT, "$ACTION_DESCRIP_EDIT $descripcion" );

		$error = 20;

		if( !allow_use_of_popups() )
			ges_redirect( "conf_restricciones.php?id_restriccion_edited=$tipo_restriccion" );

	}
	else if( $the_action == "edit" )
	{
		$tipo_restriccion = $_GET["tipo_restriccion"];
		
		$db = new DB( "SELECT * FROM cfgrestricciones WHERE ID_BIBLIOTECA=$id_biblioteca and TIPO_RESTRICCION='$tipo_restriccion';" );
		
		if( $db->NextRow() ) 
		{ 
			$descripcion		= $db->row["DESCRIPCION"];
			
			$impedir_consultas		= $db->row["IMPEDIR_CONSULTAS"];
			$impedir_reservas   	= $db->row["IMPEDIR_RESERVACIONES"];
			$impedir_prestamos		= $db->row["IMPEDIR_PRESTAMOS"];
			$impedir_renovaciones	= $db->row["IMPEDIR_RENOVACIONES"];
			
			$the_action = "save_changes";  // acción derivada natural
		}
		
		$db->destroy();
	}
	else if( $the_action == "delete" )
	{
		$restricciones = "";
		$restricciones_borradas = 0;
		
		if( isset($_GET["restricciones"]) )
		{
			$restricciones = $_GET["restricciones"];
			
			$restricciones = str_replace( "@", "TIPO_RESTRICCION=", $restricciones ); // 1st ocurrence
			$restricciones = str_replace( ":", " or TIPO_RESTRICCION=", $restricciones ); // other ocurrences
			
			$db = new DB( "DELETE FROM cfgrestricciones WHERE ID_BIBLIOTECA=$id_biblioteca and ($restricciones) " );
			
			$error = 30;
			
			$restricciones_borradas = $db->rowsAffected;
			
			$db->Destroy();
			
			require_once( "../actions.inc.php" );
			agregar_actividad_de_usuario( CFG_CONFIG_RESTRICTIONS_DELETE, "$ACTION_DESCRIP_DELETE $restricciones <$restricciones_borradas>" );
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

			<form action="conf_crearrestricciones.php" method="post" name="edit_form" id="edit_form" class="forma_captura">
			  <input class='hidden' type='hidden' name="the_action" id="the_action" value="<?php echo $the_action;?>">
			  <input class='hidden' type='hidden' name="tipo_restriccion" id="tipo_restriccion" value="<?php echo $tipo_restriccion; ?>">
			  
				<label><strong><?php echo $LBL_TIPORESTRICTION;?></strong></label>
				<span><?php echo $tipo_restriccion;?></span>
				<br><br>
			  		  
				<dt>	
					<label for="txt_descripcion"><strong><?php echo $LBL_DESCRIPCION;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_descripcion" id="txt_descripcion" value="<?php echo $descripcion;?>" size=100 maxlength=250>
					<br>
					<span class="sp_hint"><?php echo $HINT_DESCRIPTION;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<dt>
					<label for="chk_impedir_consultas"></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_impedir_consultas" id="chk_impedir_consultas" <?php echo (($impedir_consultas=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_BLOCK_OPAC;?></span>&nbsp;&nbsp;
				</dd>						
				
				<dt>
					<label for="chk_impedir_reservas"></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_impedir_reservas" id="chk_impedir_reservas" <?php echo (($impedir_reservas=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_BLOCK_RESERVS;?></span>&nbsp;&nbsp;
				</dd>						
				
				<dt>
					<label for="chk_impedir_prestamos"></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_impedir_prestamos" id="chk_impedir_prestamos" <?php echo (($impedir_prestamos=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_BLOCK_LOANS;?></span>&nbsp;&nbsp;
				</dd>	

				<dt>
					<label for="chk_impedir_renovaciones"></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_impedir_renovaciones" id="chk_impedir_renovaciones" <?php echo (($impedir_renovaciones=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_BLOCK_RENEWALS;?></span>&nbsp;&nbsp;
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