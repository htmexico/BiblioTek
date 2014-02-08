<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  28 mar 2009: Se crea el archivo PHP para editar/crear/eliminar usuarios.
	  21 ago 2009: Se agregan campos PERMITIR_PRESTAMOS y PERMITIR_COMENTARIOS.
	  18 sep 2009: Se modifica ventana de edición, se agregan algunos campos
	  21 oct 2009: Se ajustan ultimos campos faltantes por configurar 
	  05 nov 2009: Se validan los valores estrictos numéricos.

     */
		
	include "../funcs.inc.php";	
	
	include_language( "global_menus" );

	check_usuario_firmado(); 

	include_language( "serv_creargrupos" );		// archivo de idioma

	/** INICIO - FUNCIONES DE MODIFICACION/CREACION/BORRADO **/
	
	include("../basic/bd.class.php");
	
	$id_grupo = "$LBL_TO_BE_ASIGNED";
	$nombre_grupo        = "";
	$usuarios_administrativos = "";

	$permitir_prestamos  = "";
	$max_dias_prestamo   = "0";
	$max_items_prestados = "0";
	$permitir_prestamos_con_retrasos = "";
	$permitir_prestamos_con_sanciones = "";
	
	$max_reservaciones	 = "0";
	$permitir_reservas_con_sanciones = "";
	
	$max_renovaciones 	 = "0";
	$dias_renovacion_default = 0;
	$permitir_renova_con_retraso   = "";
	$permitir_renova_con_sanciones = "";
	
	$multa_economica     = "";
	$multa_horas         = "";
	$multa_especie	     = "";
	
	$sancion_x_retraso_dev = "";
	
	$permitir_comentarios = "";
		
	$notifica_email_reserva = "";
	$notifica_email_prestamos = "";
	$notifica_email_renova = "";
	$notifica_email_retraso_dev = "";
	$notifica_email_devolutions = "";
	$notifica_email_restrictions = "";
	$notifica_email_sanctions = "";

	$the_action = read_param( "the_action", "" );
	
	$error = 0;
	
	$id_biblioteca = getsessionvar("id_biblioteca");
	
	$db = new DB();
		
	if( $the_action == "create_new" )
	{
		// generar el nuevo ID del grupo
		$id_grupo = 0;
		
		$db->Open( "SELECT MAX(ID_GRUPO) AS MAXID FROM cfgusuarios_grupos WHERE ID_BIBLIOTECA=$id_biblioteca" );
		
		if ($db->NextRow() ) 
			$id_grupo = $db->Field("MAXID") + 1;
			
		$db->FreeResultset();
		
		$nombre_grupo			= $_POST["txt_nombre_grupo"];
		
		$usuarios_administrativos = "N";
		
		if( isset($_POST["chk_usuarios_administrativos"]) )
			$usuarios_administrativos = "S";
		
		$permitir_prestamos     = "N";
		$max_dias_prestamo      = 0;
		$max_items_prestados	= 0;
		$max_reservaciones		= 0;
		$max_renovaciones		= 0;
		
		if( isset($_POST["chk_permitir_prestamos"]) )
			$permitir_prestamos = "S";
			
		if( $permitir_prestamos == "S" )
		{
			if( isset($_POST["chk_permitir_prestamos_con_retrasos"]) )
				$permitir_prestamos_con_retrasos = "S";
				
			if( isset($_POST["chk_permitir_prestamos_con_sanciones"]) )
				$permitir_prestamos_con_sanciones = "S";				

			$max_dias_prestamo		= $_POST["txt_max_dias_prestamo"];
			$max_items_prestados	= $_POST["txt_max_items_prestados"];
			
			$max_renovaciones		= $_POST["txt_max_renovaciones"];
			$dias_renovacion_default =  $_POST["txt_dias_renovacion_default"];
			
			if( isset($_POST["chk_permitir_renova_con_retraso"]) ) $permitir_renova_con_retraso   = "S";			
			if( isset($_POST["chk_permitir_renova_con_sanciones"]) ) $permitir_renova_con_sanciones = "S";
			
			$max_reservaciones		= $_POST["txt_max_reservaciones"];
			if( isset($_POST["chk_permitir_reserv_con_sanciones"]) )
				$permitir_reservas_con_sanciones = "S";
		}	
		
		$permitir_comentarios   = "N";
		if( isset($_POST["chk_permitir_comentarios"]) )
			$permitir_comentarios = "S";		
		
		$multa_economica		= "N";
		$multa_horas			= "N";
		$multa_especie		    = "N";		
		
		if( isset($_POST["chk_sancion_economica"]) )
			$multa_economica = "S";

		if( isset($_POST["chk_sancion_horas"]) )
			$multa_horas	 = "S";
			
		if( isset($_POST["chk_sancion_especie"]) )
			$multa_especie	 = "S";		
			
		$sancion_x_retraso_dev = $_POST["sel_sancion"];

		// Notificaciones de Email
		if( isset($_POST["chk_notifica_reserva"]) ) $notifica_email_reserva = "S";		
		if( isset($_POST["chk_notifica_prestamos"]) ) $notifica_email_prestamos = "S";
		if( isset($_POST["chk_notifica_renova"]) ) $notifica_email_renova = "S";
		if( isset($_POST["chk_notifica_retraso_dev"]) ) $notifica_email_retraso_dev = "S";
		if( isset($_POST["chk_notifica_devoluciones"]) ) $notifica_email_devolutions = "S";
		if( isset($_POST["chk_notifica_restricciones"]) ) $notifica_email_restrictions = "S";
		if( isset($_POST["chk_notifica_sanciones"]) ) $notifica_email_sanctions = "S";				

		$db->sql  = "INSERT INTO cfgusuarios_grupos ( ID_BIBLIOTECA, ID_GRUPO, NOMBRE_GRUPO, USUARIOS_ADMINISTRATIVOS, PERMITIR_PRESTAMOS, MAX_DIAS_PRESTAMO, MAX_ITEMS_PRESTADOS, ";
		$db->sql .= "  MAX_RESERVACIONES, MAX_RENOVACIONES, PERMITIR_COMENTARIOS, MULTA_ECONOMICA_SN, MULTA_HORAS_SN, MULTA_ESPECIE_SN ) ";
		$db->sql .= " VALUES ( $id_biblioteca, $id_grupo, '$nombre_grupo', '$usuarios_administrativos', '$permitir_prestamos', $max_dias_prestamo, $max_items_prestados, ";
		$db->sql .= "          $max_reservaciones, $max_renovaciones, '$permitir_comentarios', '$multa_economica', '$multa_horas', '$multa_especie' ) ";
		$db->ExecSQL();
		
		require_once( "../actions.inc.php" );
		agregar_actividad_de_usuario( SERV_USERS_GROUPS_CREATE, "$ACTION_DESCRIP_CREATE $nombre_grupo" );
		
		$error = 10;
		
		if( !allow_use_of_popups() )
		{
			$db->Destroy();
			
			ges_redirect( "serv_usuariosgrupos.php?id_grupo_created=$id_grupo" );
		}
	}
	if( $the_action == "save_changes" )
	{
		$id_grupo				= $_POST["id_grupo"];
		$nombre_grupo			= $_POST["txt_nombre_grupo"];
		
		$usuarios_administrativos = "N";
		
		if( isset($_POST["chk_usuarios_administrativos"]) )
			$usuarios_administrativos = "S";
		
		$permitir_prestamos     = "N";
		$max_dias_prestamo      = 0;
		$max_items_prestados	= 0;
		$max_reservaciones		= 0;
		$max_renovaciones		= 0;
		
		if( isset($_POST["chk_permitir_prestamos"]) )
			$permitir_prestamos = "S";
			
		if( $permitir_prestamos == "S" )
		{
			if( isset($_POST["chk_permitir_prestamos_con_retrasos"]) )
				$permitir_prestamos_con_retrasos = "S";
				
			if( isset($_POST["chk_permitir_prestamos_con_sanciones"]) )
				$permitir_prestamos_con_sanciones = "S";				

			$max_dias_prestamo		= $_POST["txt_max_dias_prestamo"];
			$max_items_prestados	= $_POST["txt_max_items_prestados"];
			
			$max_renovaciones		= $_POST["txt_max_renovaciones"];
			$dias_renovacion_default =  $_POST["txt_dias_renovacion_default"];
			
			if( isset($_POST["chk_permitir_renova_con_retraso"]) ) $permitir_renova_con_retraso   = "S";			
			if( isset($_POST["chk_permitir_renova_con_sanciones"]) ) $permitir_renova_con_sanciones = "S";
			
			$max_reservaciones		= $_POST["txt_max_reservaciones"];
			if( isset($_POST["chk_permitir_reserv_con_sanciones"]) )
				$permitir_reservas_con_sanciones = "S";
		}		

		$permitir_comentarios   = "N";
		if( isset($_POST["chk_permitir_comentarios"]) )
			$permitir_comentarios = "S";		
		
		$multa_economica		   = "N";
		$multa_horas			   = "N";
		$multa_especie	           = "N";
		
		if( isset($_POST["chk_sancion_economica"]) )
			$multa_economica = "S";

		if( isset($_POST["chk_sancion_horas"]) )
			$multa_horas = "S";
			
		if( isset($_POST["chk_sancion_especie"]) )
			$multa_especie	 = "S";
			
		$sancion_x_retraso_dev = $_POST["sel_sancion"];

		// Notificaciones de Email
		if( isset($_POST["chk_notifica_reserva"]) ) $notifica_email_reserva = "S";		
		if( isset($_POST["chk_notifica_prestamos"]) ) $notifica_email_prestamos = "S";
		if( isset($_POST["chk_notifica_renova"]) ) $notifica_email_renova = "S";
		if( isset($_POST["chk_notifica_retraso_dev"]) ) $notifica_email_retraso_dev = "S";
		if( isset($_POST["chk_notifica_devoluciones"]) ) $notifica_email_devolutions = "S";
		if( isset($_POST["chk_notifica_restricciones"]) ) $notifica_email_restrictions = "S";
		if( isset($_POST["chk_notifica_sanciones"]) ) $notifica_email_sanctions = "S";			 
			
		$update_sql  = "UPDATE cfgusuarios_grupos SET NOMBRE_GRUPO='$nombre_grupo', USUARIOS_ADMINISTRATIVOS='$usuarios_administrativos', PERMITIR_PRESTAMOS='$permitir_prestamos', MAX_DIAS_PRESTAMO=$max_dias_prestamo,";
		$update_sql .= " MAX_ITEMS_PRESTADOS=$max_items_prestados, PERMITIR_PREST_CON_RETRASOS='$permitir_prestamos_con_retrasos', PERMITIR_PREST_CON_SANCIONES='$permitir_prestamos_con_sanciones', ";
		$update_sql .= "  MAX_RESERVACIONES=$max_reservaciones, PERMITIR_RESERV_CON_SANCIONES='$permitir_reservas_con_sanciones', MAX_RENOVACIONES=$max_renovaciones, DIAS_RENOVACION_DEFAULT=$dias_renovacion_default, ";
		$update_sql .= "  PERMITIR_RENOV_CON_RETRASO='$permitir_renova_con_retraso', PERMITIR_RENOV_CON_SANCIONES='$permitir_renova_con_sanciones', " ;
		$update_sql .= "   PERMITIR_COMENTARIOS='$permitir_comentarios', MULTA_ECONOMICA_SN='$multa_economica', MULTA_HORAS_SN='$multa_horas', MULTA_ESPECIE_SN='$multa_especie', ";
		$update_sql .= "    SANCION_X_RETRASO_DEV ='$sancion_x_retraso_dev', ";
		$update_sql .= "     NOTIFICA_EMAIL_RESERVA='$notifica_email_reserva', NOTIFICA_EMAIL_PRESTAMO='$notifica_email_prestamos', NOTIFICA_EMAIL_RENOVA='$notifica_email_renova', ";
		$update_sql .= "      NOTIFICA_EMAIL_RETRASO_DEV ='$notifica_email_retraso_dev', NOTIFICA_EMAIL_DEVOLUCIONES ='$notifica_email_devolutions', NOTIFICA_EMAIL_RESTRICCIONES ='$notifica_email_restrictions', "; 
		$update_sql .= "       NOTIFICA_EMAIL_SANCIONES = '$notifica_email_sanctions' ";
		$update_sql .= "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_GRUPO=$id_grupo";
		
		$db->ExecSQL( $update_sql );
		
		require_once( "../actions.inc.php" );
		agregar_actividad_de_usuario( SERV_USERS_GROUPS_EDIT, "$ACTION_DESCRIP_EDIT $nombre_grupo" );
		
		$error = 20;
		
		if( !allow_use_of_popups() )
		{
			$db->Destroy();
			ges_redirect( "serv_usuariosgrupos.php?id_grupo_edited=$id_grupo" );
		}
	}
	else if( $the_action == "edit" )
	{
		$id_grupo = $_GET["id_grupo"];
		
		$db->Open( "SELECT * FROM cfgusuarios_grupos WHERE ID_BIBLIOTECA=$id_biblioteca and ID_GRUPO=$id_grupo" );
		
		if( $db->NextRow() ) 
		{ 
			$nombre_grupo			= $db->Field("NOMBRE_GRUPO");
			$usuarios_administrativos = $db->Field("USUARIOS_ADMINISTRATIVOS");
			
			$permitir_prestamos     = $db->Field("PERMITIR_PRESTAMOS");
			
			$max_dias_prestamo		= $db->Field("MAX_DIAS_PRESTAMO");
			$max_items_prestados	= $db->Field("MAX_ITEMS_PRESTADOS");
			
			$max_reservaciones		= $db->Field("MAX_RESERVACIONES");
			$permitir_reservas_con_sanciones = $db->Field("PERMITIR_RESERV_CON_SANCIONES");
				
			$max_renovaciones		       = $db->row["MAX_RENOVACIONES"];
			$dias_renovacion_default       = $db->Field("DIAS_RENOVACION_DEFAULT");
			
			if( $dias_renovacion_default == "" )
				$dias_renovacion_default = "0";

			$permitir_renova_con_retraso   = $db->Field("PERMITIR_RENOV_CON_RETRASO");
			$permitir_renova_con_sanciones = $db->Field("PERMITIR_RENOV_CON_SANCIONES");
			
			$multa_economica		= $db->Field("MULTA_ECONOMICA_SN");
			$multa_horas			= $db->Field("MULTA_HORAS_SN");
			$multa_especie			= $db->Field("MULTA_ESPECIE_SN");
			
			$sancion_x_retraso_dev = $db->Field("SANCION_X_RETRASO_DEV");
			
			$permitir_comentarios   = $db->Field("PERMITIR_COMENTARIOS");
			
			$notifica_email_reserva 	= $db->row["NOTIFICA_EMAIL_RESERVA"];
			$notifica_email_prestamos 	= $db->row["NOTIFICA_EMAIL_PRESTAMO"];
			$notifica_email_renova 		= $db->row["NOTIFICA_EMAIL_RENOVA"];
			$notifica_email_retraso_dev = $db->row["NOTIFICA_EMAIL_RETRASO_DEV"];
			$notifica_email_devolutions = $db->row["NOTIFICA_EMAIL_DEVOLUCIONES"];
			$notifica_email_restrictions = $db->row["NOTIFICA_EMAIL_RESTRICCIONES"];
			$notifica_email_sanctions 	 = $db->row["NOTIFICA_EMAIL_SANCIONES"];
			
			$the_action = "save_changes";  // acción derivada natural
		}

	}
	else if( $the_action == "delete" )
	{
		$grupos = "";
		$grupos_borrados = 0;
		
		if( isset($_GET["grupos"]) )
		{
			$grupos = $_GET["grupos"];
			
			$grupos = str_replace( "@", "ID_GRUPO=", $grupos ); // 1st ocurrence
			$grupos = str_replace( ":", " or ID_GRUPO=", $grupos ); // other ocurrences
			
			$db->ExecSQL( "DELETE FROM cfgusuarios_grupos WHERE ID_BIBLIOTECA=$id_biblioteca and ($grupos) " );
			
			$error = 30;
			
			$grupos_borrados = $db->rowsAffected;
			
			$db->Destroy();
			
			require_once( "../actions.inc.php" );
			agregar_actividad_de_usuario( SERV_USERS_GROUPS_DELETE, "$ACTION_DESCRIP_DELETE $grupos <$grupos_borrados>" );
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
		
		if( document.edit_form.txt_nombre_grupo.value == "" )		
		{
			error = 1;
			alert( "<?php echo $VALIDA_MSG_21;?>" );
			document.edit_form.txt_nombre_grupo.focus();
		}

		if( error == 0 )
		{
			document.edit_form.submit();
			return true;
		}
		else
			return false;
	}
	
	function activate_prestamos()
	{
		var input_max_dias = js_getElementByName("txt_max_dias_prestamo");
		var input_max_items = js_getElementByName("txt_max_items_prestados");		
		var permitir_prestamos_con_retrasos = js_getElementByName("chk_permitir_prestamos_con_retrasos");
		var permitir_prestamos_con_sanciones = js_getElementByName("chk_permitir_prestamos_con_sanciones");
		
		var input_max_reservs = js_getElementByName("txt_max_reservaciones");
		var permitir_reservas_con_sanciones = js_getElementByName("chk_permitir_reserv_con_sanciones");
		
		var input_max_renova = js_getElementByName("txt_max_renovaciones");
		var dias_renovacion_default = js_getElementByName("txt_dias_renovacion_default");
		var permitir_renova_con_retrasos = js_getElementByName("chk_permitir_renova_con_retraso");
		var permitir_renova_con_sanciones = js_getElementByName("chk_permitir_renova_con_sanciones");
		
		var st = js_getElementByName_Value( "chk_permitir_prestamos" );
		
		if( input_max_dias ) input_max_dias.disabled = !st;
		if( input_max_items ) input_max_items.disabled = !st;
		if( permitir_prestamos_con_retrasos ) permitir_prestamos_con_retrasos.disabled = !st;
		if( permitir_prestamos_con_sanciones ) permitir_prestamos_con_sanciones.disabled = !st;
		
		if( input_max_reservs ) input_max_reservs.disabled = !st;
		if( permitir_reservas_con_sanciones ) permitir_reservas_con_sanciones.disabled = !st;
		
		if( input_max_renova ) input_max_renova.disabled = !st;
		if( dias_renovacion_default ) dias_renovacion_default.disabled = !st;
		if( permitir_renova_con_retrasos ) permitir_renova_con_retrasos.disabled = !st;
		if( permitir_renova_con_sanciones ) permitir_renova_con_sanciones.disabled = !st;
		
	}
	
	window.onload=function()
	{
		prepareInputsForHints();
		activate_prestamos();
	}	
	
	
</SCRIPT>

<STYLE>

 #caja_datos1 {
   float: left; 
   width: 780px; 
   }
   
form.forma_captura label {   
   width: 17em;   
  }
  
  #buttonarea { border: 0px solid red; position: absolute; left: 17em; }; 
  
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

			<form action="serv_creargrupos.php" method="post" name="edit_form" id="edit_form" class="forma_captura">
			  <input class='hidden' type='hidden' name="the_action" id="the_action" value="<?php echo $the_action;?>">
			  <input class='hidden' type='hidden' name="id_grupo" id="id_grupo" value="<?php echo $id_grupo; ?>">
			  
				<label for="txt_nombre_biblioteca"><strong><?php echo $LBL_ID_GRUPO;?></strong></label>
				<span><?php echo $id_grupo;?></span>
				<br><br>
			  
			  
				<dt>	
					<label for="txt_nombre_grupo"><strong><?php echo $LBL_NOMBRE_GRUPO;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_nombre_grupo" id="txt_nombre_grupo" value="<?php echo $nombre_grupo;?>" size=70 maxlength=50>
					<span class="sp_hint"><?php echo $HINT_NOMBRE_GRUPO;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
						
				<dt>
				<label for="chk_usuarios_administrativos"></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_usuarios_administrativos" id="chk_usuarios_administrativos" <?php echo (($usuarios_administrativos=="S") ? "checked" : ""); ?>/>
					&nbsp;<span><?php echo $LBL_USUARIOS_ADMVOS;?></span>
				</dd>				
			  
				<!-- relativos a prestamos -->
				<br>
				<dt>
					<label for="chk_permitir_prestamos"></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" onChange='javascript:activate_prestamos();' name="chk_permitir_prestamos" id="chk_permitir_prestamos" <?php echo (($permitir_prestamos=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_PERMITIR_PRESTAMOS;?></span>
				</dd>

				<dt>
					<label for="txt_max_dias_prestamo"><strong><?php echo $LBL_MAX_DIAS_PRESTAMO;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_max_dias_prestamo" id="txt_max_dias_prestamo" value="<?php echo $max_dias_prestamo;?>" size="10" onblur="extractNumber(this,0,false);" onkeypress="return blockNonNumbers(this, event, false, false);">
					<span class="sp_hint"><?php echo $HINT_MAX_DIAS_PRESTAMO;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>

				<dt>
					<label for="txt_max_items_prestados"><strong><?php echo $LBL_MAX_ITEMS_PRESTADOS;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_max_items_prestados" id="txt_max_items_prestados" value="<?php echo $max_items_prestados;?>" size="10" onblur="extractNumber(this,0,false);" onkeypress="return blockNonNumbers(this, event, false, false);">
					<span class="sp_hint"><?php echo $HINT_MAX_ITEMS_PRESTADOS;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<dt>
					<label for="chk_permitir_prestamos_con_retrasos"></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_permitir_prestamos_con_retrasos" id="chk_permitir_prestamos_con_retrasos" <?php echo (($permitir_prestamos_con_retrasos=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_PERMITIR_PRESTAMOS_RETRASOS;?></span>&nbsp;&nbsp;
					<input class="checkbox" type="checkbox" name="chk_permitir_prestamos_con_sanciones" id="chk_permitir_prestamos_con_sanciones" <?php echo (($permitir_prestamos_con_sanciones=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_PERMITIR_PRESTAMOS_SANCIONES;?></span>
				</dd>				
				
				<br>
			  
				<!-- relativos reservas -->
				<dt>
					<label for="txt_max_reservaciones"><strong><?php echo $LBL_MAX_RESERVACIONES;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_max_reservaciones" id="txt_max_reservaciones" value="<?php echo $max_reservaciones;?>" size=10 onblur="extractNumber(this,0,false);" onkeypress="return blockNonNumbers(this, event, false, false);">
					<span class="sp_hint"><?php echo $HINT_MAX_RESERVACIONES;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
			  
				<dt>
					<label for="chk_permitir_reserv_con_sanciones"></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_permitir_reserv_con_sanciones" id="chk_permitir_reserv_con_sanciones" <?php echo (($permitir_reservas_con_sanciones=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_PERMITIR_RESERVAS_SANCIONES;?></span>&nbsp;&nbsp;
				</dd>				
			  
				<!-- relativos a renovaciones -->
				<br>
				<dt>
					<label for="txt_max_renovaciones"><strong><?php echo $LBL_MAX_RENOVACIONES;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_max_renovaciones" id="txt_max_renovaciones" value="<?php echo $max_renovaciones;?>" size=10 onblur="extractNumber(this,0,false);" onkeypress="return blockNonNumbers(this, event, false, false);">
					<span class="sp_hint"><?php echo $HINT_MAX_RENOVACIONES;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>

				<dt>
					<label for="txt_dias_renovacion_default"><strong><?php echo $LBL_DIAS_RENOVACION_DEFAULT;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_dias_renovacion_default" id="txt_dias_renovacion_default" value="<?php echo $dias_renovacion_default;?>" size=10 onblur="extractNumber(this,0,false);" onkeypress="return blockNonNumbers(this, event, false, false);">
					<span class="sp_hint"><?php echo $HINT_DIAS_RENOVACION_DEFAULT;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>

				<dt>
					<label for="chk_permitir_renova_con_retraso"></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_permitir_renova_con_retraso" id="chk_permitir_renova_con_retraso" <?php echo (($permitir_renova_con_retraso=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_PERMITIR_RENOVA_RETRASOS;?></span>&nbsp;&nbsp;
					<input class="checkbox" type="checkbox" name="chk_permitir_renova_con_sanciones" id="chk_permitir_renova_con_sanciones" <?php echo (($permitir_renova_con_sanciones=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_PERMITIR_RENOVA_SANCIONES;?></span>
				</dd>					
				
				<br>
				
				<!-- otros -->
				<dt>
					<label for="chk_permitir_comentarios"></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_permitir_comentarios" id="chk_permitir_comentarios" <?php echo (($permitir_comentarios=="S") ? "checked" : ""); ?>/>
					&nbsp;<span><?php echo $LBL_PERMITIR_COMENTARIOS;?></span>
				</dd>
				<br>
				
				<dt>
					<label for="chk_sancion_economica"></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_sancion_economica" id="chk_sancion_economica" <?php echo (($multa_economica=="S") ? "checked" : ""); ?>/>
					&nbsp;<span><?php echo $LBL_SANCION_ECONOMICA;?></span>
				</dd>

				<dt>
				<label for="chk_sancion_horas"></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_sancion_horas" id="chk_sancion_horas" <?php echo (($multa_horas=="S") ? "checked" : ""); ?>/>
					&nbsp;<span><?php echo $LBL_SANCION_HORAS;?></span>
				</dd>
				
				<dt>
				<label for="chk_sancion_especie"></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_sancion_especie" id="chk_sancion_especie" <?php echo (($multa_especie=="S") ? "checked" : ""); ?>/>
					&nbsp;<span><?php echo $LBL_SANCION_ESPECIE;?></span>
				</dd>

				<br>
				
				<dt>
					<label for="chk_sancion_x_retraso_dev"><strong><?php echo $LBL_SANCION_X_RETRASO_DEV;?></strong></label>
				</dt>
				<dd>
				
					<select name='sel_sancion' id='sel_sancion'>				
					<?php				
						echo "<option value='-1'>$HINT_NO_SANCTION</option>";
						
						$db->sql = "SELECT TIPO_SANCION, DESCRIPCION, ECONOMICA_SN, ECONOMICA_MONTO_FIJO, ECONOMICA_MONTO_X_DIA, LABOR_SOCIAL_SN, LABOR_SOCIAL_HORAS, ESPECIE_SN ".
								   "FROM cfgsanciones " .
								   "WHERE ID_BIBLIOTECA=$id_biblioteca ";
						$db->sql .= "ORDER BY DESCRIPCION" ;
						
							$db->DebugSQL();

						$db->Open();
									   
						
					   
						while( $db->NextRow() )
						{	
							$str_selected = "";
							
							if( $sancion_x_retraso_dev == $db->row["TIPO_SANCION"] )
								$str_selected = "SELECTED";
							
							echo "<option $str_selected value='" . $db->row["TIPO_SANCION"] . "'>" . $db->FIELD("DESCRIPCION") ."</option>";

						}
						
						$db->Close();

					?>					
					</SELECT>
	
				</dd>

				<br style='clear:all;'>
				<br>
				<dt>
					<label><strong><?php echo $LBL_EMAIL_NOTIFICATIONS;?></strong></label>
				</dt>
				<dd>
					<table width='500px'>
						<tr>
							<td width='50%'>
								<input class="checkbox" type="checkbox" name="chk_notifica_reserva" id="chk_notifica_reserva" <?php echo (($notifica_email_reserva=="S") ? "checked" : ""); ?>/>
								&nbsp;<span><?php echo $LBL_NOTIFY_ON_RESERVAS;?></span>							
							</td>
							<td width='50%'>
								<input class="checkbox" type="checkbox" name="chk_notifica_prestamos" id="chk_notifica_prestamos" <?php echo (($notifica_email_prestamos=="S") ? "checked" : ""); ?>/>
								&nbsp;<span><?php echo $LBL_NOTIFY_ON_LOANS;?></span>							
							</td>
		
						</tr>
						<tr>
							<td width='50%'>
								<input class="checkbox" type="checkbox" name="chk_notifica_renova" id="chk_notifica_renova" <?php echo (($notifica_email_renova=="S") ? "checked" : ""); ?>/>
								&nbsp;<span><?php echo $LBL_NOTIFY_ON_RENEWALS;?></span>							
							</td>
							<td width='50%'>
								<input class="checkbox" type="checkbox" name="chk_notifica_retraso_dev" id="chk_notifica_retraso_dev" <?php echo (($notifica_email_retraso_dev=="S") ? "checked" : ""); ?>/>
								&nbsp;<span><?php echo $LBL_NOTIFY_ON_DELAYS;?></span>							
							</td>
		
						</tr>						
						<tr>
							<td width='50%'>
								<input class="checkbox" type="checkbox" name="chk_notifica_devoluciones" id="chk_notifica_devoluciones" <?php echo (($notifica_email_devolutions=="S") ? "checked" : ""); ?>/>
								&nbsp;<span><?php echo $LBL_NOTIFY_ON_DEVOLUTIONS;?></span>							
							</td>
							<td width='50%'>
								<input class="checkbox" type="checkbox" name="chk_notifica_restricciones" id="chk_notifica_restricciones" <?php echo (($notifica_email_restrictions=="S") ? "checked" : ""); ?>/>
								&nbsp;<span><?php echo $LBL_NOTIFY_ON_RESTRICTIONS;?></span>							
							</td>
		
						</tr>
						<tr>
							<td width='50%'>
								<input class="checkbox" type="checkbox" name="chk_notifica_sanciones" id="chk_notifica_sanciones" <?php echo (($notifica_email_sanctions=="S") ? "checked" : ""); ?>/>
								&nbsp;<span><?php echo $LBL_NOTIFY_ON_SANCTIONS;?></span>
							</td>
							<td width='50%'>&nbsp;
							</td>
		
						</tr>							
					
					<table>
				</dd>
				
				<br>

			  <div id="buttonarea">
				<input id="btnGuardar" name="btnGuardar" class="boton" type="button" value="<?php echo $BTN_SAVE;?>"  onClick='javascript:validar();' />&nbsp;
				<input id="btnCancelar" name="btnCancelar" class="boton" type="button" value="<?php echo $BTN_CANCEL;?>" onClick='<?php echo back_function();?>' />
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