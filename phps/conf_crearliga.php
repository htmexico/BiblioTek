<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  28 ene 2010: Se crea el archivo PHP para editar/crear/eliminar ligas.
     */
		
	include "../funcs.inc.php";	
	
	include_language( "global_menus" );

	check_usuario_firmado(); 

	include_language( "conf_crearligas" );		// archivo de idioma

	/** INICIO - FUNCIONES DE MODIFICACION/CREACION/BORRADO **/
	
	include("../basic/bd.class.php");
	
	$id_liga = 0;
	
	$etiqueta = "";
	$fecha_desde = getcurdate_human_format();
	$fecha_hasta = $fecha_desde;
	
	$url = "";
	
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
		$url				= $_POST["txt_url"];
		
		$fecha_desde		= date_for_database_updates( $_POST["txt_fecha_desde"] );		
		$fecha_hasta		= date_for_database_updates( $_POST["txt_fecha_hasta"] );
		
		$db->sql  = "INSERT INTO recursos_contenido ( ID_BIBLIOTECA, ID_RECURSO, ID_TIPORECURSO, SUMARIO, PUBLICARSE_DESDE, PUBLICARSE_HASTA, URL ) ";
		$db->sql .= " VALUES ( $id_biblioteca, $id_recurso, 1, '$etiqueta', '$fecha_desde', '$fecha_hasta', '$url' );";
		$db->ExecSQL();
		
		require_once( "../actions.inc.php" );
		agregar_actividad_de_usuario( CFG_RESOURCE_CREATE, "$ACTION_DESCRIP_CREATE $etiqueta " );
		
		$error = 10;
		
		setsessionvar( "tab_used", 3 );
		
		if( !allow_use_of_popups() )
			ges_redirect( "conf_contents.php?id_resource_created=$id_persona&tab=3" );

	}
	else if( $the_action == "save_changes" )
	{
		$id_liga				= $_POST["id_liga"];
		
		$etiqueta			= $_POST["txt_etiqueta"];
		$url				= $_POST["txt_url"];
		
		$fecha_desde		= date_for_database_updates( $_POST["txt_fecha_desde"] );		
		$fecha_hasta		= date_for_database_updates( $_POST["txt_fecha_hasta"] );		


		$db = new DB;
		$db->sql  = "UPDATE recursos_contenido SET SUMARIO='$etiqueta', PUBLICARSE_DESDE='$fecha_desde', PUBLICARSE_HASTA='$fecha_hasta', URL='$url' ";
		$db->sql .= "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_RECURSO=$id_liga and ID_TIPORECURSO=1; ";
		$db->ExecSQL();

		require_once( "../actions.inc.php" );
		agregar_actividad_de_usuario( CFG_RESOURCE_EDIT, "$ACTION_DESCRIP_EDIT $etiqueta" );

		$error = 20;

		setsessionvar( "tab_used", 3 );
		
		if( !allow_use_of_popups() )
			ges_redirect( "conf_contents.php?id_resource_edited=$id_liga&tab=3" );

	}
	else if( $the_action == "edit" )
	{
		$id_liga = $_GET["id_liga"];
		
		$db = new DB( "SELECT * FROM recursos_contenido WHERE ID_BIBLIOTECA=$id_biblioteca and ID_RECURSO=$id_liga and ID_TIPORECURSO=1; " );
		
		if( $db->NextRow() ) 
		{ 
			$etiqueta			= $db->row["SUMARIO"];
			
			$fecha_desde	= dbdate_to_human_format( $db->row["PUBLICARSE_DESDE"], 0 );
			$fecha_hasta	= dbdate_to_human_format( $db->row["PUBLICARSE_HASTA"], 0 ); 			

			$url		= $db->row["URL"];
			
			$the_action = "save_changes";  // acción derivada natural
		}
		
		$db->destroy();
	}
	else if( $the_action == "delete" )
	{
		$ligas = "";
		$ligas_borradas = 0;
		
		if( isset($_GET["ligas"]) )
		{
			$ligas = $_GET["ligas"];
			
			$ligas = str_replace( "@", "ID_RECURSO=", $ligas ); // 1st ocurrence
			$ligas = str_replace( ":", " or ID_RECURSO=", $ligas ); // other ocurrences
			
			$db = new DB( "DELETE FROM recursos_contenido WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TIPORECURSO=1 and ($ligas) " );
			
			$error = 30;
			
			$ligas_borradas = $db->rowsAffected;
			
			$db->Destroy();
			
			setsessionvar( "tab_used", 3 );
			
			require_once( "../actions.inc.php" );
			agregar_actividad_de_usuario( CFG_RESOURCE_DELETE, "$ACTION_DESCRIP_DELETE <$ligas_borradas>" );
		}
		
		if( !allow_use_of_popups() )
			ges_redirect( "conf_contents.php?id_grupos_borrados=$grupos_borrados" );
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

			<form action="conf_crearliga.php" method="post" name="edit_form" id="edit_form" class="forma_captura">
			  <input class='hidden' type='hidden' name="the_action" id="the_action" value="<?php echo $the_action;?>">
			  <input class='hidden' type='hidden' name="id_liga" id="id_liga" value="<?php echo $id_liga; ?>">
			  
				<br>
			  
				<dt>	
					<label for="txt_nombre_grupo"><strong><?php echo $LBL_LABEL;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_etiqueta" id="txt_etiqueta" value="<?php echo $etiqueta;?>" size=60 maxlength=50>
					<span class="sp_hint"><?php echo $HINT_LABEL;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<dt>	
					<label for="txt_nombres"><strong><?php echo $LBL_PUBLISH_FROM;?></strong></label>
				</dt>
				<dd>
					<?php colocar_edit_date( "txt_fecha_desde", $fecha_desde, 0, "" ); ?>
					
					<span class="sp_hint"><?php echo $HINT_PUBLISH_FROM;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
			  
				<dt>
					<label for="txt_domicilio"><strong><?php echo $LBL_PUBLISH_UNTIL;?></strong></label>
				</dt>
				<dd>
					<?php colocar_edit_date( "txt_fecha_hasta", $fecha_hasta, 0, "" ); ?>
					
					<span class="sp_hint"><?php echo $HINT_PUBLISH_UNTIL;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				<br>

				<dt>
					<label for="txt_ciudad"><strong><?php echo $LBL_URL;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_url" id="txt_url" value="<?php echo $url;?>" size="100" maxlength="255">
					<span class="sp_hint"><?php echo $HINT_URL;?><span class="hint-pointer">&nbsp;</span></span>
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