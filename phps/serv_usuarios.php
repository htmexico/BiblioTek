<?php
	session_start();
	
	/*
	  
	  - Movimientos de Usuarios
	  - Inicio Aprox: 06-04-2009
	  
	  - 28 ago 2009:  Se elimina campo EMPLEADO de cfgusuarios, se cambia a cfgusuarios_grupos
	  - 23 oct 2009:  Se agrega $db en process_email
	  - 15 abr 2011: Se coloca un combo de grupo de usuarios para filtrar; se ordena por NOMBRE de usuario
	  - 31 may 2011: Se coloca una busqueda y otras opciones (rangos de paginacion, filtrado por status, etc.)
	  
	  PENDIENTES:
	  
	   - Verificar el permiso especifico de ADMINISTRAR USUARIOS
	   - Que regrese a la misma pagina del browse de usuarios 
    
	*/	
		
	include "../funcs.inc.php";
	include "../basic/bd.class.php";
	include "../privilegios.inc.php";
	
	include_language( "global_menus" );

	check_usuario_firmado(); 
	check_usuario_empleado();  // acceso solo a empleados
	
	$id_biblioteca = getsessionvar("id_biblioteca");

	include_language( "serv_usuarios" ); // archivo de idioma

	$id_usuario_created = read_param( "id_usuario_created", 0 );
	$id_user_pwd_changed = read_param( "id_user_pwd_changed", 0 );

	$page = read_param( "page", 1 );	
	$initial_letter = read_param( "initial_letter", "A" );
	$ordered_by 	= read_param( "ordered_by", "id" );
	
	$the_action = read_param( "the_action", "" );
	
	$id_group  = read_param( "id_group", "0" );
	$filter  = read_param( "filter", "" );

	$pr = read_param( "pr", "" );  // no cookie
	
	if( $pr == "" )
		if( isset( $_COOKIE["pagerange"]) )
			$pr = $_COOKIE["pagerange"];
	
	if( $pr == "" )
		$pr = "10";
	
	if( isset($_GET["pr"]) )
	{
		$fecha=mktime(0,0,0,1,1,2020);
		
		setcookie( "pagerange", $pr, $fecha, "", "", 0 );
	}
	
	$hide_inactive  = read_param( "hi", "Y" );
	
	$flag = 0;
	
	if( $the_action == "replace_pwd" )
	{
		// REEMPLAZAR EL PASSWORD
		$id_usuario_pwd_changing = read_param( "id_usuario", 0, 1 ); // fail if error
		$nvo_passwd = read_param( "new_pwd", "", 1 ); // fail if error
		
		$save_password = $nvo_passwd;

		$login_usuario = "";
		$email_usuario = "";
		
		$nvo_passwd = md5( $nvo_passwd );

		$db = new DB();
		$db->ExecSQL( "UPDATE cfgusuarios SET PASSWRD='$nvo_passwd' WHERE ID_BIBLIOTECA=$id_biblioteca and ID_USUARIO=$id_usuario_pwd_changing ");
		
		require_once( "../actions.inc.php" );
		agregar_actividad_de_usuario( CFG_USER_CHANGE_PASSWORD, $login_usuario ); 		
		
		require_once( "email_factory.inc.php" );		
		process_email( $db, $id_biblioteca, $id_usuario_pwd_changing, EMAIL_USER_PASSWORD_CHANGED, $save_password );
		
		$db->destroy();
		
		ges_redirect( "serv_usuarios.php?page=$page&id_user_pwd_changed=$id_usuario_pwd_changing" );
		
	}
	else if( $the_action == "importar_usuarios" )
	{
		// Importar Usuarios
		if( isset($_POST["uploadfile"]))
		{
			$db = new DB();
	
//require_once( "adm_dbsync_funcs.php" );
			$file_processing = 1;
		
			$id_grupo_migrar = read_param( "cmb_group_to_migrate_into", "", 1 );
		
			$creados = 0;
		
			if( is_uploaded_file($_FILES['userfile']['tmp_name']) ) 		
			{
				$nombre_archivo = $_FILES['userfile']['tmp_name'];
				
				$xml = simplexml_load_string( file_get_contents($nombre_archivo) );
				
				if (!is_object($xml))
					throw new Exception("Error en la lectura del archivo XML $nombre_archivo",1001);					
				
				$privilegios_marcados = trim($_POST['privilegios_marcados']);
				$array_privilegios = split( " ", $privilegios_marcados );		
				
				ini_set( "display_errors", "on" );
				
				global $IS_DEBUG;
				$IS_DEBUG = 1;
				
				foreach ($xml->usuario as $usuario ) 
				{				
					if( count($usuario->attributes()) > 0 )
						$usuario = $usuario->attributes();
								
					$user_name = "";
					$password_usuario = "";
					
					$apellido_paterno = "";
					$apellido_materno = "";
					$nombre = "";
					$genero = "";
					
					$direccion = "";
					$telefono = "";
					$email = "";
					$email_alterno = "";
					$chk_status = "A";
					
					$fecha_nacimiento = "";				
					
					if( isset($usuario->LOGIN) ) $user_name = $usuario->LOGIN;
					if( isset($usuario->USERNAME) ) $user_name = trim($usuario->USERNAME);
					
					if( isset($usuario->PASSWORD) ) $password_usuario = $usuario->PASSWORD;
					if( isset($usuario->SEC_CRITICO) ) $password_usuario = $usuario->SEC_CRITICO;
					
					if( isset($usuario->GENERO) ) $genero = $usuario->GENERO;
					if( isset($usuario->SEXO) ) $genero = $usuario->SEXO;
					
					if( isset($usuario->PATERNO) ) $apellido_paterno = utf8_decode($usuario->PATERNO);
					if( isset($usuario->MATERNO) ) $apellido_materno = utf8_decode($usuario->MATERNO);
					if( isset($usuario->NOMBRE) ) $nombre = utf8_decode($usuario->NOMBRE);
					
					if( isset($usuario->NOMBRE_USUARIO) and (!isset($usuario->NOMBRE)) )
					{ 
						$nombre = utf8_decode($usuario->NOMBRE_USUARIO);
					}
					
					if( isset($usuario->DIRECCION) ) $direccion = $usuario->DIRECCION;
					if( isset($usuario->TELEFONO) ) $telefono = $usuario->TELEFONO;
					
					if( isset($usuario->EMAIL) ) $email = $usuario->EMAIL;
					
					if( isset($usuario->FECHA_NACIMIENTO) )
					{
						$fecha_nacimiento = $usuario->FECHA_NACIMIENTO;  // debe venir en formato humano
					}
					
					if( $fecha_nacimiento == "" or $fecha_nacimiento == "00/00/0000" )
					{
						$fecha_nacimiento = "NULL";
					}
					else if( $fecha_nacimiento != "" )
					{
						$fecha_nacimiento = date_for_database_updates( $fecha_nacimiento );
						$fecha_nacimiento = "'$fecha_nacimiento'";
					}
					
					if( $user_name != "" )
					{
						$existe = false;
						
						$db->Open( "SELECT COUNT(*) AS CUANTOS FROM cfgusuarios WHERE ID_BIBLIOTECA=$id_biblioteca and USERNAME='$user_name' " );
									
						if( $db->NextRow() ) 
						{
							if( $db->row["CUANTOS"] > 0 )
								$existe = true;
						}
						
						$db->Close();						
						
						if( !$existe )
						{				
							// cada usuario que se procesa
							// generar el nuevo ID del usuario
							$id_usuario = 0;
							$db->Open( "SELECT MAX(ID_USUARIO) AS MAXID, COUNT(*) AS CUANTOS FROM cfgusuarios WHERE ID_BIBLIOTECA=$id_biblioteca " );
										
							if( $db->NextRow() ) 
							{
								if( $db->row["CUANTOS"] == 0 )
									$id_usuario = 1;
								else
									$id_usuario = $db->row["MAXID"] + 1;
							}
							
							$db->Close();					
							
							if( strlen($telefono)>20)
								$telefono = substr( $telefono, 0, 20 );
							
							$sql  = "INSERT INTO cfgusuarios ( ID_BIBLIOTECA, ID_USUARIO, USERNAME, PASSWRD, ID_GRUPO, PATERNO, MATERNO,";
							$sql .= " NOMBRE, DIRECCION, TELEFONO, E_MAIL, E_MAIL_ALTERNO, STATUS, GENERO, FECHA_NACIMIENTO) ";
							$sql .= " VALUES ( $id_biblioteca, $id_usuario, '$user_name', '$password_usuario', '$id_grupo_migrar', '$apellido_paterno', '$apellido_materno',";
							$sql .= " '$nombre', '$direccion', '$telefono', '$email', '$email_alterno', '$chk_status', '$genero', $fecha_nacimiento ) ";
							
							$insertados = $db->ExecSQL( $sql );
							
							$id_usuario_admin 	= getsessionvar('id_usuario');	
							
							//if( $insertados > 0 ) echo "Insertado $insertados <br>";
							if( !empty($privilegios_marcados) )
							{			
								for( $k=0; $k < count($array_privilegios); $k++ )
								{
									$num_priv = $array_privilegios[$k];
														
									$insert_sql_p  = "INSERT INTO cfgusuarios_privilegios ( ID_BIBLIOTECA, ID_USUARIO, PRIVILEGIO, FECHAYHORA, ID_USUARIO_ADMIN )";
									$insert_sql_p .= " VALUES ( $id_biblioteca, $id_usuario, $num_priv, CURRENT_TIMESTAMP, $id_usuario_admin )";
									
									$db->ExecSQL( $insert_sql_p );
								}		
							}								
							
							$creados += $insertados;
						}
						
					}
				}
				
			}
			
			if( $creados > 0 )
			{
				$msg_exito = "Se crearon $creados Usuarios durante la importaci&oacute;n.";
			}
			else
			{
				$msg_exito = "No se crearon usuarios";
			}
			
			$db->destroy();
		}
	}
	
	include ("../basic/head_handler.php");  // Coloca un encabezado HTML <head>
	HeadHandler( "$LBL_HEADER_V1", "../" );
	
	verificar_privilegio( PRIV_USERS, 1 );
	
?>

<script type="text/javascript" src="../basic/md5.js"></script>

<SCRIPT type="text/javascript" language="JavaScript">

	var aGruposAdmvos = new Array();
	
	function create_new_user()
	{
		var url = "serv_registrousuarios.php?page=<?php echo $page;?>";
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "create_user", 920, 550 );
		js_Status( "" );
	}
	
	function edit_user( id_usuario )
	{
		var url = "serv_registrousuarios.php?the_action=edit&id_usuario=" + id_usuario + "&page=<?php echo $page;?>";
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "edit_user", 920, 550 );
		js_Status( "" );
	}

	function VerBitacora( id_usuario, page )
	{   
		js_ChangeLocation("serv_usr_bitacora.php?id_usuario=" + id_usuario + "&pagina=" + page );
	}
	
	function CambiaPasswd( id_usuario, loginname, obj )
	{   
		if( ShowDiv( "div_change_pwd" ) )
		{
			var div_change_pwd = js_getElementByName( "div_change_pwd" );
			var usr_changing = js_getElementByName( "username_changing" );
			var obj_id_usuario = js_getElementByName( "id_usuario_changing" );
			
			div_change_pwd.style.zIndex = 1; // para que quede arriba de otros
			
			if( obj.getClientRects ) 
			{			
				var xpos = obj.getClientRects();
				
				if( xpos.length > 0 )
				{
					div_change_pwd.style.position = "absolute";
					div_change_pwd.style.top  = (xpos[0].top - 50) + "px";
					div_change_pwd.style.left = (xpos[0].left - 300) + "px";
					
					var new_pwd = js_getElementByName( "nvo_passwd" );
					
					if( new_pwd )
					    new_pwd.focus();
				}
			}

			usr_changing.innerHTML = loginname;
			obj_id_usuario.value = id_usuario;
		}
	}
	
	//
	// Reemplazar el Password
	//
	function replacePassword()
	{
		var obj_id_usuario = js_getElementByName( "id_usuario_changing" );
		var str_new_pwd = js_getElementByName_Value( "nvo_passwd" );
		var str_cnf_pwd = js_getElementByName_Value( "cnf_passwd" );
		var error = 0;
		
		if( str_new_pwd != str_cnf_pwd )
		{
			alert( "<?php echo $MSG_PASSWRD_NOT_MATCH;?>" );
			error = 1;
		}
		
		if( error == 0 )
		{
			if( confirm( octal("<?php echo $MSG_REPLACE_PWD;?>") ) )
			{			
				// 
				// val_md5 = hex_md5( str_cnf_pwd );
				//
				// PENDIENTE:
				//  Colocar un submit que se genere runtime
				//
				js_ChangeLocation( "serv_usuarios.php?page=<?php echo $page;?>&the_action=replace_pwd&id_usuario=" + obj_id_usuario.value+"&new_pwd=" + str_cnf_pwd );
			}
		}
		
	}
	
	//
	// cerrar area/popup de Agregar
	//
	function closeChangePwd()
	{
		if( HideDiv( "div_change_pwd" ) )
		{			
			HideDiv( "div_descrip_title" );
			HideDiv( "div_error_title" );
		}
	}	
	
	function changeGroup()
	{
		var id_group = js_getElementByName_Value("cmb_groups");
		
		if( id_group == "*" )
			location.href = "serv_usuarios.php";
		else
		{
			location.href = "serv_usuarios.php?id_group="+id_group;
		}
	}
	
	function changeFilterSt()
	{
		var stFilter = js_getElementByName_Value("ocultarinactivos");
		
		if( stFilter == true )
			location.href = "serv_usuarios.php?hi=Y";
		else
			location.href = "serv_usuarios.php?hi=N";
	}
	
	function goFilter()
	{
		var txtFilter = js_getElementByName_Value("txtFilter");
		
		if( txtFilter != "" )
		{
			if( txtFilter.length < 4 )
				alert( "<?php echo $MSG_ERROR_NOT_MININUM_CHARS; ?>" );
			else
				location.href = "serv_usuarios.php?filter="+txtFilter;		
		}
		else
			alert( "<?php echo $MSG_ERROR_NOT_TXT_TO_FILTER; ?>" );
	}
	
	function importar()
	{
		ShowDiv( "screen_block_layer" );
			
		//var tbl_name = js_getElementByName( "sync_table_name" );
		//tbl_name.value = info;
		
		ShowPopupDIV( "popup_importar" );		
	}
	
	function importar_go()
	{	
		if( document.import_users.userfile.value == "" )
			alert( "Para continuar debe seleccionar el archivo que contiene la información." );
		else
		{
			if( confirm( "El archivo ser&aacute; subido y se iniciará la importación." ) )
			{
				var id_group = js_getElementByName_Value("cmb_groups");
				
				var val_grupo = js_GroupOptionSelected("cmb_group_to_migrate_into");
		
				if( val_grupo.indexOf("*") == -1 )
				{
					document.import_users.privilegios_marcados.value = "";
				}
				
				document.import_users.id_group.value = "";
				document.import_users.method = "POST";
				document.import_users.target = "_self";
				document.import_users.submit();
			}
		}
	}	
	
	function CloseImportDialog()
	{
		HideDiv( "screen_block_layer" );
		HideDiv( "popup_importar" );		
	}		
	
		// devuelve el nombre del grupo elegido
		function js_GroupOptionSelected( obj_name )
		{
			var res = "";
			var val = document.getElementsByName( obj_name );
			
			for( i = 0; i < val[0].options.length; i++ )
			{
				if ( val[0].options[i].selected )
				{
					res = val[0].options[i].text;
					break;
				}
			}			
			
			return res;					
		}
		
		function seleccionaPriv()
		{	   
			var inputPriv  = document.getElementsByName("privilegios_marcados");		
			var elementos = "";		
			var elemento_checkbox;	
			
		 	var txt_total_privilegios = document.getElementsByName("privilegios_totales");
			  
			var total_privilegios = 0;
			
			if( txt_total_privilegios.length > 0 )
			   total_privilegios = parseInt( txt_total_privilegios[0].value );	
			
			inputPriv[0].value = "";		
			
			for( var i=1; i<=total_privilegios; i++ )
			{
				elemento_checkbox = document.getElementsByName( "priv_" + i );
				
				if( elemento_checkbox.length > 0 )
				{
					if( elemento_checkbox[0].checked )
					{
						if( elementos != "" )
						   elementos += " ";
						elementos += elemento_checkbox[0].value;							  					
					}
				}
			}
			
			inputPriv[0].value = elementos;		
			alert( elementos );
		}		
	
	function show_hide_privs()
	{
		var val_grupo = js_GroupOptionSelected("cmb_group_to_migrate_into");
		
		if( val_grupo.indexOf("*") != -1 )
		{
			var div_grupo = document.getElementById( "privilegios" );
			
			div_grupo.style.display = "block";
			div_grupo.style.visibility = "visible";
		}
		else
			HideDiv( "privilegios" );
		
		
	}

</SCRIPT>

<STYLE>

	#contenido_principal 
	{
		width: 97%;
	}

	#info_general 
	{
		width: 100%;
	}
	
	#buttonArea
	{
		margin-bottom: 8px;
		overflow: auto;
	}

	#div_change_pwd
	{	
		display: none;
		position: absolute;
		background-color: #FCFBD0;
		border: 3px solid gray; 
	
		left: 400px;
		top: 10px;
		width: 280px;
		height: 120px;
		
		font-size: 90%;
	}	
	
	 #popup_importar
	 {
		position: absolute;
		display: none;
	
		top:100px; 
		/*left: 50%;*/
		
		border: 2px solid black;
		
		width: 720px;
		height: 450px;
		z-Index: 100;
		color: black;
		
		padding: 10px;
		
		text-align: left;
		
		background: white;
		font-size: 120%;
	 }
	 	
	
/* page Range */
div.x-pageRanges
{
	margin-top: 4px; 
}

.x-pageRanges INPUT[type=button]
{
	text-decoration:  underline;
	padding-left: 2px;
}

.x-pageRanges span
{
	font-weight: none;	
	padding: 2px;
}

.x-pageRanges .current
{
	border: 0px solid black !important;
	text-decoration:  underline !important;
	font-weight: bold;
	background: silver;
}

/* capa que permite bloquear la pantalla para que aparezca un DIV popup*/
#screen_block_layer
{
	display: none;
	position: absolute;
	background-color: gray;
	left: 1px;
	
	top: 1px;
	width: 100%;
	height: 100%;
	
	filter:alpha(opacity=55);
	-moz-opacity:0.55;
	opacity: 0.55;
	
	border: 3px solid silver;
	border-bottom; 4px solid gray;
	
	z-Index: 48;
}	


</STYLE>

<body id="home">
<div id='screen_block_layer' name='screen_block_layer'></div>

<?php

  display_global_nav();  // barra de navegación superior
  
   
   $db = new DB();
   
   $array_groups = Array();
   
   $db->Open( "SELECT a.ID_GRUPO, a.NOMBRE_GRUPO, a.USUARIOS_ADMINISTRATIVOS FROM cfgusuarios_grupos a WHERE a.ID_BIBLIOTECA=$id_biblioteca ORDER BY a.ID_GRUPO " );
   
   while( $db->NextRow() )
   {
	   $array_groups[] = $db->row;
   }
   
   $db->Close();  
  
   ini_set( "display_errors", "on" );
   
 ?>

<!-- contenedor principal -->

	<!-- INICIA popup_importar -->
		<div class='groupbox' id='popup_importar' name='popup_importar' >
			<form enctype="multipart/form-data" action="serv_usuarios.php" name="import_users" id="import_users" >			
			
				<input type="hidden" name="MAX_FILE_SIZE" value="30000000" />
				<input type="hidden" class="hidden" name="uploadfile" value="YES" >
				<input type="hidden" class="hidden" name="id_group" id="id_group" value="" >
				<input type="hidden" class="hidden" name="the_action" value="importar_usuarios" >
			
				<h2>Importar Usuarios desde otros sistemas</h2><br>

					  <div style='border:0px solid red;'>

						<label for='userfile'><strong>Nombre del Archivo</strong></label><br><br>
						<input name="userfile" type="file" maxLength=80 size=80 value="">
						<input type='hidden' class='hidden' id="user_group" name="user_group" value="">
						
						<br><br>

						 <label for='userfile'><strong>Importar dentro del Grupo:</strong></label>

						 <?php
							
							echo "<select id='cmb_group_to_migrate_into' name='cmb_group_to_migrate_into' onchange='show_hide_privs();' >";  
							echo "<option value='--'>Elija un grupo</option>";
							foreach( $array_groups as $usr_group )
							{
								$usuarios_administrativos = "";
								
								if( $usr_group['USUARIOS_ADMINISTRATIVOS'] == "S" )
								{
									$usuarios_administrativos = "**";
									SYNTAX_JavaScript( 1, 1, "aGruposAdmvos.push( " . $db->row['ID_GRUPO'] . ");" );
								}
								
								echo "<option " . ( $id_group==$usr_group["ID_GRUPO"] ? "selected":"")." value='" . $usr_group["ID_GRUPO"] . "'>" . $usr_group["NOMBRE_GRUPO"]. "$usuarios_administrativos</option>";
							}
							echo "</select>";
						?>

						<div id="privilegios" name="privilegios" class="groupbox" style="display: none; font-size: 80%; height: 220px; overflow : auto; scrollbar-base-color:#369; scrollbar-highlight-color:#5569b4;  ">				
						   <br><h2>Elija los permisos</h2>
							<?php
							
								$db->Open( " SELECT a.PRIVILEGIO, a.DESCRIPCION " . 
										   " FROM cfgprivilegios a " .
										   " ORDER BY a.TIPOPRIVILEGIO, a.PRIVILEGIO ");
								
								$num=0;
								$checado="";
								
								$bold_active = true;
								$last_ciento = -1;
								
								while( $db->NextRow() )
								{
									$num++;
									
									if( ((int) ($db->Field("PRIVILEGIO") / 100)) != $last_ciento )
									{
										$last_ciento = (int) ($db->Field("PRIVILEGIO") / 100);
										$bold_active = !$bold_active;
									}
									
									$class_hilite = "hilite_even";
									
									if( $bold_active ) 
										$class_hilite = "hilite_odd";
									
									echo "<DIV class='$class_hilite'><input name='priv_$num' id='priv_$num' value='" . $db->Field("PRIVILEGIO") . 
										 "' type='checkbox'  onClick='javascript:seleccionaPriv();'>&nbsp;" . $db->Field("DESCRIPCION") . "</DIV>";		
										 		
								}
																	
								echo '<input id=privilegios_totales name=privilegios_totales type=hidden class=hidden value="' . $num . '">';
								echo "<input id=privilegios_marcados name=privilegios_marcados type='hidden' class=hidden value=''>";
										
								$db->Close();			
								
								echo "<br>";
							
						  	?>	
									
						</div>	
						 
						&nbsp;
						<br><br>
						<input class="boton" type="button" value="Proceder" name="btnBuscar" id="btnBuscar" onClick="javascript:importar_go();">&nbsp;
						<input class="boton" type="button" value="Cancelar" name="btnClose" id="btnClose" onClick='javascript:CloseImportDialog();'><br>

					  </div>

				<br>		

			</form>
		
		</div> <!-- popup_importar -->		
	  


<div id="contenedor">

<?php 
   display_banner();  // banner   
   display_menu('../'); // menu principal

   
 ?>

   <div id="bloque_principal"> 
      <div id="contenido_principal">
	   
	  <h1><?php echo $LBL_HEADER_V2;?></h1>
	  
		<!--- INICIA POPUP CAMBIAR PASSWORD -->
		<div class="groupbox" id="div_change_pwd" name="div_change_pwd">
			<div style='float:left; width: 260px; font-size: 90%;'>
				
				<div style='display:inline;'><strong><?php echo $LBL_USER;?></strong></div>
				<div style='display:inline;' name='username_changing' id='username_changing'>Usuario</div><br><br>
				
				<!-- ID USUARIO cambiando -->
				<input type='hidden' class='hidden' name='id_usuario_changing' id='id_usuario_changing' value=''>
				
				<dt>
					<label for='nvo_passwd'><?php echo $LBL_NEW_PWD;?></label><input class='campo_captura' type='password' name='nvo_passwd' id='nvo_passwd' value='' size='15' style='position: absolute; left: 120px; display:inline;'>
				</dt>
				<br>

				<dt>
					<label for='cnf_passwd'><?php echo $LBL_CONFIRM_PWD;?></label><input class='campo_captura' type='password' name='cnf_passwd' id='cnf_passwd' value='' size='15' style='position: absolute; left: 120px; display:inline;'>
				</dt>

				<div style='display: inline; position: relative; top: 15px; left: 40px;' >
					<input type="button" class="boton" value="<?php echo $BTN_SAVE;?>" name="btnSavePwd" id="btnSavePwd" onClick="javascript:replacePassword();">
				</div>

			</div>
			
			<!-- close icon -->
			<div style="float:right; padding:0px; position: relative; top: -10px; margin:0px;">
				<br>
				<a href="javascript:closeChangePwd();"><img src="../images/icons/close_button.gif"></a>
			</div><br>
			<!-- close icon -->
			
			<br style='clear:all'>
			
		</div>
		<!--- FIN POPUP CAMBIAR PASSWORD  -->	  

       <div id="info_general" class="caja_datos">
	   	   
	   <?php 
	   
			if( $id_usuario_created != 0 ) 
			{
				echo "<div class=caja_info>";
				echo " <strong>$SAVE_CREATED_DONE</strong>";
				echo "</div>";
			}
			if( $id_user_pwd_changed != 0 ) 
			{
				echo "<div class=caja_info>";
				echo " <strong>$CHANGED_PASSWD_DONE</strong>";
				echo "</div>";
			}			
			
			if( isset($msg_exito) )
			{
				echo "<div class=caja_info>";
				echo " <strong>$msg_exito</strong>";
				echo "</div>";				
			}
			
	/**		if( $id_biblioteca == 2 )
			{
				$db->Open( "SELECT ID_USUARIO, USERNAME, PASSWRD, PATERNO, MATERNO, NOMBRE FROM cfgusuarios WHERE ID_BIBLIOTECA=4 and STATUS='P' " );
				$array_users = Array();
				while( $db->NextRow() )
				{
					$array_users[] = Array( "ID_USUARIO" => $db->row["ID_USUARIO"],
												"USERNAME" => $db->row["USERNAME"],
													"PASSWRD" => $db->row["PASSWRD"] );
				}
				$db->Close();
				
				foreach( $array_users as $usuario )
				{	
					echo  "UPDATE cfgusuarios SET STATUS='A', PASSWRD='"  .md5($usuario["PASSWRD"]) . "' " .
							"WHERE ID_BIBLIOTECA=4 and ID_USUARIO=" . $usuario["ID_USUARIO"] . "<br>";
				}
				
			}
**/		
			
			// filtros por grupo y botones
			{
				echo "<div id='buttonArea'>";
					
					echo "<div style='float:left'>";
					echo "$LBL_TITLE_GROUP :&nbsp;";
					
					echo "<select id='cmb_groups' name='cmb_groups' onChange='javascript:changeGroup();'>";
					echo "<option value='*'>$LBL_TODOS</option>";
					
					foreach( $array_groups as $usr_group )
					{
						echo "<option " . ( $id_group==$usr_group["ID_GRUPO"] ? "selected":"")." value='" . $usr_group["ID_GRUPO"] . "'>" . $usr_group["NOMBRE_GRUPO"]. "</option>";
					}
					
					echo "</select>";
					echo "</div>";
					
					echo "<div style='float:left; margin-left: 15px;'>";
					echo "$LBL_CAPTION_SEARCH :&nbsp;<input type='text' name='txtFilter' id='txtFilter' size=25 value='$filter'><input class='boton' type='button' value='$BTN_SEARCH' onclick='javascript:goFilter();'>";
					echo "&nbsp;&nbsp;&nbsp;<input type='checkbox' onClick='javascript:changeFilterSt();' name='ocultarinactivos' id='ocultarinactivos' " . (($hide_inactive=="Y") ? "checked":""). ">&nbsp;" .   $LBL_HIDE_INACTIVE;
					echo "</div>";
					
					echo "<div style='float:right'>";
					echo "<input class='boton' type='button' value='$BTN_CREATE_NEW_USER' onclick='javascript:create_new_user();'>";
					
					echo "&nbsp;&nbsp;<a href='javascript:void(0);' title='Importar una lista de usuarios desde otro sistema' onclick='javascript:importar();'>Importar $LBL_USERS</a>";
					
					echo "</div>";
				
				echo "</div>";
			}
						
			$db->sql = "SELECT a.*, b.NOMBRE_GRUPO " . 
				       "FROM cfgusuarios a " . 
					   " LEFT JOIN cfgusuarios_grupos b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_GRUPO=a.ID_GRUPO) " .
					   "WHERE a.ID_BIBLIOTECA=$id_biblioteca ";
					   
			if( $id_group != 0 )
				$db->sql .= " and a.ID_GRUPO=$id_group ";
				
			if( $filter != "" )
			{
				$filter_v2 = strtolower($filter);
				$filter_v3 = strtoupper($filter);
				$filter_v4 = ucwords($filter);
				
				$db->sql .= " and ((a.NOMBRE LIKE '%$filter%' or a.NOMBRE LIKE '%$filter_v2%'  or a.NOMBRE LIKE '%$filter_v3%'  or a.NOMBRE LIKE '%$filter_v4%' ) ";
				$db->sql .= " or (a.PATERNO LIKE '%$filter%' or a.PATERNO LIKE '%$filter_v2%'  or a.PATERNO LIKE '%$filter_v3%'  or a.PATERNO LIKE '%$filter_v4%' ) ";
				$db->sql .= " or (a.MATERNO LIKE '%$filter%' or a.MATERNO LIKE '%$filter_v2%'  or a.MATERNO LIKE '%$filter_v3%'  or a.MATERNO LIKE '%$filter_v4%' ) ) ";
			}
			else
			{
				if( $hide_inactive == "Y" )
					$db->sql .= " and a.STATUS='A' ";
			}
					   
			$db->sql .= "ORDER BY a.NOMBRE, a.PATERNO, a.MATERNO  ";
				
			// crear el paginador		
			$paginador = new Pager( $db, "N", $pr );
				
			if( isset( $_GET["page"] ) )
				$paginador->page = $_GET["page"];
				
			if( $ordered_by == "id" )
			{
				$paginador->Calculate_Ranges();
				$paginador->RemoveParameters( "id_usuario_created=" );
				$paginador->RemoveParameters( "id_user_pwd_changed=" );
				$db->SetPage( $paginador->start_from, $paginador->Range, $paginador );  // se agrega $paginador
			}
			
			$paginador->Language( getsessionvar("language") );
				
			$db->Open();
				
			echo "<table width='100%'>\n";
			echo " <tr>" .
				  "  <td class='cuadricula columna columnaEncabezado' width=50px>$LBL_TITLE_ID_USER</td>" .
				  "  <td class='cuadricula columna columnaEncabezado' width=100px'>$LBL_TITLE_USERNAME</td>" . 
				  "  <td class='cuadricula columna columnaEncabezado' width=250px>$LBL_TITLE_FULLNAME</td>" . 
				  "  <td class='cuadricula columna columnaEncabezado' width=220px>$LBL_TITLE_GROUP</td>" . 
				  "  <td class='cuadricula columna columnaEncabezado' width=100px>$LBL_TITLE_EMAIL</td>" . 
				  "  <td class='cuadricula columna columnaEncabezado' width=20px>$LBL_TITLE_STATUS</td>" .
				  "  <td class='cuadricula columna columnaEncabezado' align='center' width=20px>$LBL_TITLE_LOG</td>" .
				  "  <td class='cuadricula columna columnaEncabezado' align='center' width=20px>$LBL_TITLE_CHANGE_PWD</td>" .
				 " </tr>\n";	
				
			while( $db->NextRow(1) )
			{
				$id_usuario = $db->row["ID_USUARIO"];
			
				$status 			= $db->row["STATUS"];
				if ($status=="A") $desc_status="S";
				if ($status=="I") $desc_status="N";
				
				//<!-- tabla de resultados -->

				$class_hilite = "hilite_odd";

				if( $db->numRows % 2 == 1 )
					$class_hilite = "hilite_even";
				  
				$link_url = "javascript:edit_user( $id_usuario )";
				  
				echo "<tr class='$class_hilite' onMouseOver='javascript:this.className=\"hilite_onmouse_enter\";' onMouseOut='javascript:this.className=\"$class_hilite\";'>" . 
					  " <td class='cuadricula columna'>$id_usuario</td> " .
					  " <td class='cuadricula columna'><a href='$link_url'>".$db->row["USERNAME"]."</a></td>" .					
					  " <td class='cuadricula columna'><a href='$link_url'>" . $db->row["NOMBRE"] . " " . $db->row["PATERNO"] . " " . $db->row["MATERNO"] . "</a></td> " .
					  " <td class='cuadricula columna'>" . $db->row["NOMBRE_GRUPO"] . "</td> " .
					  " <td class='cuadricula columna'>" . $db->row["E_MAIL"] . ($db->row["E_MAIL_ALTERNO"]!="" ? "<br>".$db->row["E_MAIL_ALTERNO"] : "") . "</td> " .
					  " <td class='cuadricula columna' align='center'>" . ICON_DisplayYESNO($desc_status) . "</td> " .
					  " <td class='cuadricula columna' align='center'><input type='button' class='boton' value='Bit' onClick='javascript:VerBitacora($id_usuario,$paginador->page)'></td>".
					  " <td class='cuadricula columna' align='center'><input type='button' class='boton' value='Passwd' onClick='javascript:CambiaPasswd( $id_usuario, \"" . $db->row["USERNAME"] . "\", this )'></td>".
					  " </tr>";
			} //fin while						
			
			$db->Close();
			
			echo "</table>";			
			//a partir de aqui viene la paginacion
			
			$paginador->DrawPages();
			
			/**if( $id_biblioteca == 12 )
			{
				echo "<a href='serv_importar_usuarios.php'>Importar Usuarios</a>";
			}**/
			
		?>		
		
       </div> <!-- - caja datos -->	   
	 
      </div>  <!-- contenido pricipal -->	
		
<?php  display_copyright(); ?>

   </div> <!--bloque principal-->
</div>    <!--bloque contenedor-->
       
</body>
</html>