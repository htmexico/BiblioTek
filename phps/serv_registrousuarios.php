<?php
	session_start();
	
	/*
	  
	  - Registro y Modificacion de Usuarios
	  - Asignacion de privilegios 
	  - Inicio 06-04-2009
	  
	  - 07-ago-2009: Corrección de && por and
	  - 28 ago 2009: Se elimina campo EMPLEADO
	  - 15-abr-2010: Se corrige el envío del email, faltaba $db
	  - 24-nov-2010: Se agrega el campo EMAIL ALTERNO
	      
	*/
		
	include "../funcs.inc.php";
	include "../actions.inc.php";
	include "../basic/bd.class.php";
	
	include_language( "global_menus" );

	check_usuario_firmado();
	
	include_language( "serv_registrousuarios" ); // archivo de idioma	
	
	$id_usuario 		= "[por asignar]";
	$usuario	    	= "";
	$password_usuario	= "";
	$id_grupo           = 0;
	$nombre_usuario		= "";
	$apellido_paterno   = "";
	$apellido_materno   = "";	
	$direccion          = "";
	$telefono			= "";
	$email              = "";
	$email_alterno	   = "";
	$chk_status			= "I";
	$ultimo_ingreso		= ""; // Falta fecha
	$sexo               = "";
	$fecha_nacimiento   = getblankdate_human_format();
	
	$privilegios_marcados = "";
	$the_action = read_param( "the_action", "" );
	$error 	= 0;
	$vp		= "";
	
	if( isset( $_GET["page"] ) ) 	$page = $_GET["page"]; // Variable del paginador 
	if( isset( $_POST["page"] ) ) $page = $_POST["page"]; // Variable del paginador
		
	$id_biblioteca 		= getsessionvar("id_biblioteca");
	$id_usuario_admin 	= getsessionvar('id_usuario');	
		
	if( $the_action == "create_new" )
	{	
		$usuario	    	= $_POST["txt_usuario"];
		$save_password      = $_POST["txt_password_usuario"];
		
		$password_usuario	= md5($_POST["txt_password_usuario"]);
		$id_grupo           = $_POST["cmb_id_grupo"];
		$nombre_usuario		= $_POST["txt_nombre_usuario"];
		$apellido_paterno   = $_POST["txt_apellido_paterno"];
		$apellido_materno   = $_POST["txt_apellido_materno"];		
		$direccion          = $_POST["txt_direccion"];
		$telefono			= $_POST["txt_telefono"];
		$email              = $_POST["txt_correo"];
		$email_alterno      = $_POST["txt_correo_alterno"];
		$chk_status			= "I";
		$ultimo_ingreso		= date("Y-m-d");
		$genero             = $_POST["cmb_genero"];
		$fecha_nacimiento   = $_POST["fecha_nacimiento"];		
		
		if( isset($_POST["chk_status"]) ) $chk_status = "A";
				
		$db = new DB();
		
		$db->Open( " SELECT COUNT(*) AS NUM_USR FROM cfgusuarios WHERE ID_BIBLIOTECA=$id_biblioteca and USERNAME='$usuario' " );// Comprueba que no exista el nombre de usuario	
		
		if( $db->NextRow() ) 
		{
			if( $db->row["NUM_USR"] > 0 )
				$error = 2;
		}
		
		$db->Close();
	
		if ( $error == 0 )
		{ 
			// Graba en caso de que no exista el nombre de usuario y el correo este OK

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
			
			if( $fecha_nacimiento == "" )
			{
				$fecha_nacimiento = "NULL";
			}
			else if( $fecha_nacimiento != "" )
			{
				$fecha_nacimiento = date_for_database_updates( $fecha_nacimiento );
				$fecha_nacimiento = "'$fecha_nacimiento'";
			}
			
			
			$db->sql  = "INSERT INTO cfgusuarios ( ID_BIBLIOTECA, ID_USUARIO, USERNAME, PASSWRD, ID_GRUPO, PATERNO, MATERNO,";
			$db->sql .= " NOMBRE, DIRECCION, TELEFONO, E_MAIL, E_MAIL_ALTERNO, STATUS, GENERO, FECHA_NACIMIENTO) ";
			$db->sql .= " VALUES ( $id_biblioteca, $id_usuario, '$usuario', '$password_usuario', '$id_grupo', '$apellido_paterno', '$apellido_materno',";
			$db->sql .= " '$nombre_usuario', '$direccion', '$telefono', '$email', '$email_alterno', '$chk_status', '$genero', $fecha_nacimiento ) ";
			
			$db->ExecSQL( $db->sql );
				
			$privilegios_marcados = trim($_POST['privilegios_marcados']);	
			$fechayhora = current_dateandtime();
			$array_privilegios = split( " ", $privilegios_marcados );  
	
			if( !empty($privilegios_marcados) )
			{			
				for( $k=0; $k < count($array_privilegios); $k++ )
				{
					$num_priv = $array_privilegios[$k];
										
					$insert_sql_p  = "INSERT INTO cfgusuarios_privilegios ( ID_BIBLIOTECA, ID_USUARIO, PRIVILEGIO, FECHAYHORA, ID_USUARIO_ADMIN )";
					$insert_sql_p .= " VALUES ( $id_biblioteca, $id_usuario, $num_priv, '$fechayhora', $id_usuario_admin )";
					
					$db->ExecSQL( $insert_sql_p );
				}		
			}
			
			// enviar email
			require_once( "email_factory.inc.php" );
			process_email( $db, $id_biblioteca, $id_usuario, EMAIL_USER_CREATION, $save_password );

			agregar_actividad_de_usuario( SERV_USERS_CREATE, $usuario );  

			$error = 10;

			if( !allow_use_of_popups() )
				ges_redirect( "Location:serv_usuarios.php?id_usuario_created=$id_usuario&page=$page" );

		}
	}
	else if( $the_action == "save_changes" )
	{		
		$db = new DB();
		
		$id_usuario			= $_POST['id_usuario'];
		$usuario	  	  = $_POST["txt_usuario"];
		
		if ( $the_action == "create_new" )
			$password_usuario	= md5($_POST["txt_password_usuario"]);
		
		$id_grupo           = $_POST["cmb_id_grupo"];
		$nombre_usuario	  = $_POST["txt_nombre_usuario"];
		$apellido_paterno   = $_POST["txt_apellido_paterno"];
		$apellido_materno   = $_POST["txt_apellido_materno"];		
		$direccion          = $_POST["txt_direccion"];
		$telefono			  = $_POST["txt_telefono"];
		$email              = $_POST["txt_correo"];
		$email_alterno      = $_POST["txt_correo_alterno"];
		$chk_status			  = "I";
		$genero             = $_POST["cmb_genero"];
		$fecha_nacimiento   = $_POST["fecha_nacimiento"];
		
		if( $fecha_nacimiento == "" )
		{
			$fecha_nacimiento = "NULL";
		}
		else if( $fecha_nacimiento != "" )
		{
			$fecha_nacimiento = date_for_database_updates( $fecha_nacimiento );
			$fecha_nacimiento = "'$fecha_nacimiento'";
		}		
		
		if( isset($_POST["chk_status"]) ) $chk_status = "A";		
		
		$update_query  = "UPDATE cfgusuarios SET USERNAME='$usuario', ID_GRUPO='$id_grupo', PATERNO='$apellido_paterno', MATERNO='$apellido_materno', ";
		$update_query .= "   NOMBRE='$nombre_usuario', DIRECCION='$direccion', TELEFONO='$telefono', E_MAIL='$email', E_MAIL_ALTERNO='$email_alterno', ";
		$update_query .= "   STATUS='$chk_status', GENERO='$genero', FECHA_NACIMIENTO=$fecha_nacimiento ";
		$update_query .= "WHERE (ID_BIBLIOTECA=$id_biblioteca and ID_USUARIO=$id_usuario)";
		
		$db->ExecSQL( $update_query );		
		
		$privilegios_marcados = trim($_POST['privilegios_marcados']);	
		$id_usuario	= $_POST['id_usuario'];	
				
		// ELIMINA LOS PRIVILEGIOS DEL USUARIO
		$db->ExecSQL( "DELETE FROM cfgusuarios_privilegios WHERE ID_BIBLIOTECA=$id_biblioteca AND ID_USUARIO=$id_usuario" );
						
		$array_privilegios = split( " ", $privilegios_marcados );  
		$fechayhora = current_dateandtime();

		if ( !empty($privilegios_marcados) ) 
		{
			
			for($k=0; $k < count($array_privilegios); $k++) {
				
				$num_priv = $array_privilegios[$k];
									
				$insert_sql_p  = "INSERT INTO cfgusuarios_privilegios ( ID_BIBLIOTECA, ID_USUARIO, PRIVILEGIO, FECHAYHORA, ID_USUARIO_ADMIN )";
				$insert_sql_p .= " VALUES ( $id_biblioteca, $id_usuario, $num_priv, '$fechayhora', $id_usuario_admin )";			
				
				$db->ExecSQL( $insert_sql_p );
			}		
		}
		
		agregar_actividad_de_usuario( SERV_USERS_EDIT, $usuario );

		$error = 20;

		if( !allow_use_of_popups() )  // NO POPUPS entonces redireccionar
			ges_redirect( "Location:serv_usuarios.php?id_usuario_edited=$id_usuario&page=$page" );
		
	}
	else if( $the_action == "edit" )
	{
		$db = new DB();
		
		$id_usuario = $_GET["id_usuario"];
		$resultqry = db_query( "SELECT * FROM cfgusuarios WHERE ID_BIBLIOTECA=$id_biblioteca and ID_USUARIO=$id_usuario" );
				
		if( $row = db_fetch_row( $resultqry ) ) 
		{ 
			$id_usuario			= $row['ID_USUARIO'];
			$usuario	    	= $row["USERNAME"];
			$id_grupo           = $row["ID_GRUPO"];
			$nombre_usuario		= $row["NOMBRE"];
			$apellido_paterno   = $row["PATERNO"];
			$apellido_materno   = $row["MATERNO"];			
			$fecha_nacimiento   = get_str_datetime( $row["FECHA_NACIMIENTO"], 0, 0 );
			$direccion          = $row["DIRECCION"];
			$telefono			= $row["TELEFONO"];				
			$email              = $row["E_MAIL"];
			$email_alterno      = $row["E_MAIL_ALTERNO"];
			$chk_status			= $row["STATUS"];		
			$ultimo_ingreso		= $row["ULTIMO_INGRESO"];
			$sexo               = $row["GENERO"];		
						
			$the_action = "save_changes";  // acción derivada natural
		}
		
		$db->Open( " SELECT PRIVILEGIO FROM cfgusuarios_privilegios WHERE ID_BIBLIOTECA='$id_biblioteca' and ID_USUARIO='$id_usuario' " ); 
		
		while( $db->NextRow() ) {
			$vp.=$db->Field("PRIVILEGIO")." ";					
		}		
		
		free_dbquery( $resultqry );	
		$db->FreeResultSet();

	}
	else
	{
		$the_action = "create_new"; // acción por default
	}
	
	include "../basic/head_handler.php"; // Coloca un encabezado HTML <head>	
	HeadHandler( $LBL_TITLE_CAPTION, "../" );
	
?>

<script type="text/javascript" src="../basic/calend.js"></script>

<SCRIPT language="JavaScript">

	var aGruposAdmvos = new Array(0);

	function validar()
	{
		var error = 0;
		
		if( document.edit_form.txt_usuario.value == "" )		
		{
			error = 1;
			
			alert( "<?php echo $VALID_MSG_MISSINGUSERNAME;?>" );
			document.edit_form.txt_usuario.focus();
		}
		
		if( error == 0 && document.edit_form.fecha_nacimiento.value != "" )
		{
			if( !EsFechaValida( document.edit_form.fecha_nacimiento ) )
			{
				error = 1;				
				alert( "<?php echo $VALID_MSG_WRONGDATE;?>" );
				document.edit_form.fecha_nacimiento.focus();
			}
		}
		
		if( error == 0 )
		{
			if( !isValidEmail( document.edit_form.txt_correo.value ) )
			{
				error = 1;
				alert( "<?php echo $VALID_MSG_WRONGEMAIL;?>" );
				document.edit_form.txt_correo.focus();
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
	
	function mostrarOcultarLink()
	{
		var divID  = document.getElementsByName("privilegios");
		
		if( divID[0].style.display=="none" )
		    divID[0].style.display = "block";
		else
			divID[0].style.display = "none";
		
	}
	
	//
	// verifica que se muestre o no los privilegios
	//
	function verif_privilegios()
	{
		var cmbGrupo = js_getElementByName( "cmb_id_grupo" );
		
		if( cmbGrupo )
		{
			var divID  = js_getElementByName("privilegios");
			var divAsignar = js_getElementByName("btnAsignarPrivilegios");
			
			if( divID && divAsignar )
			{
				if( Array_Search( aGruposAdmvos, parseInt( cmbGrupo.value ) ) != -1 ) 
				{
					// solo aquí mostrar 
					divID.style.display = "block";
					divAsignar.style.display = "block";
				}
				else
				{
					// ocultar
					divID.style.display = "none";
					divAsignar.style.display = "none";
				}
			}
		}
		
	}
	
	function selecciona()
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
	}	
	
	window.onload=function()
	{
		prepareInputsForHints();
		verif_privilegios(); // para edición
	}	
	
</SCRIPT>

<STYLE>

 #caja_datos1 {
   float: left;
   width: 550px; 
   }
  
  #buttonarea { border: 0px solid red;  }; 
  #btnActualizar { position:absolute; left:0em; } 
  #btnCancelar { position:absolute; left:17em; } 

  #contenido_adicional
  { float: right; 
    width: 29%}

<?php
	if( allow_use_of_popups() )
		echo "#contenedor { width: 900px; margin-top: 10px; } ";
?>
	
</STYLE>

<body id="home">

<?php
  
	if( !allow_use_of_popups() )
		display_global_nav();    // barra de navegación superior
		
	if( allow_use_of_popups() )
	{
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
	}	

?>

<!-- contenedor principal -->
<div id="contenedor">

<?php

   if( !allow_use_of_popups() )
   {
	   display_banner(); // banner 	   
	   display_menu( "../" ); // menu principal
   }
   
 ?>
 
<div id="bloque_principal"> <!-- inicia contenido -->
 
 <div id="contenido_principal">

	<div class=caja_datos id=caja_datos1 style='width: 98%;' >
		<h2>
		<?php 
			if( $the_action == "create_new" )
				echo $LBL_HEADER_V1;
			else
				echo $LBL_HEADER_V2;
			
			echo "<HR></h2><br>";	
			
			if( $error == 2 )
			{
				echo "<div class=caja_errores>";
				echo " <strong>Error: Ya existe el Login usuario, intentelo de nuevo.</strong>";
				echo "</div>";			
			}
			else if( $error == 10 )
			{
				echo "<div class=caja_info>";
				echo " <strong>$SAVE_CREATED_DONE</strong>";
				echo "</div>";
				
				// Update Parent
				SYNTAX_JavaScript( 1, 1, "window.opener.document.location.reload();" );
			}
			else if( $error == 20 )
			{
				echo "<div class=caja_info>";
				echo " <strong>$SAVE_DONE</strong>";
				echo "</div>";
				
				// Update Parent
				SYNTAX_JavaScript( 1, 1, "window.opener.document.location.reload();" );
			}				

		 ?>
			<form action="serv_registrousuarios.php" method="post" name="edit_form" id="edit_form" class="forma_captura">
			    <input class="hidden" type="hidden" name="the_action" id="the_action" value="<?php echo $the_action;?>">
			    <input class="hidden" type="hidden" name="id_usuario" id="id_usuario" value="<?php echo $id_usuario;?>">
				<input class="hidden" type="hidden" name="page" id="page" value="<?php echo $page;?>">

				<label for="txt_id_usuario"><?php echo $LBL_ID_USUARIO;?></label>
				<span><?php echo $id_usuario;?></span>
				<br><br>
				
				<dt>
					<label for="txt_user_login_name"><?php echo $LBL_NOMBRE_USUARIO;?></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_usuario" id="txt_usuario" value="<?php echo $usuario;?>" size=20>
					<span class="sp_hint"><?php echo $HINT_LOGIN_NAME;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
			    <?php if( $the_action == "create_new" ) { ?>
				
					<dt>
						<label for="txt_password_usuario"><?php echo $LBL_PASSWORD;?></label>
					</dt>
					<dd>
						<input class="campo_captura" type="password" name="txt_password_usuario" id="txt_password_usuario" value="<?php echo $password_usuario;?>" size=20/>
						<span class="sp_hint"><?php echo $HINT_PASSWORD;?><span class="hint-pointer">&nbsp;</span></span>
					</dd>		
					
				<?php	
				      }
				 ?>					  
			    
				<dt>
					<label for="cmb_id_grupo"><?php echo $LBL_ID_GRUPO;?></label>
				</dt>				
				<dd>
					<select id='cmb_id_grupo' name='cmb_id_grupo' class='select_captura' onChange='javascript:verif_privilegios();' onClick='javascript:verif_privilegios();'>
					
				<?php	
					$db = new DB( "SELECT ID_GRUPO, NOMBRE_GRUPO, USUARIOS_ADMINISTRATIVOS FROM cfgusuarios_grupos WHERE ID_BIBLIOTECA=" . getsessionvar("id_biblioteca")  );
					
					while( $db->NextRow() )
					{ 
					    $str_selected = ($id_grupo==$db->row['ID_GRUPO']) ? "selected" : "";
						
						$usuarios_administrativos = "";
						
						if( $db->row['USUARIOS_ADMINISTRATIVOS'] == "S" )
						{
							$usuarios_administrativos = "**";
							SYNTAX_JavaScript( 1, 1, "aGruposAdmvos.push( " . $db->row['ID_GRUPO'] . ");" );
						}

					    echo "<option value='" . $db->row['ID_GRUPO'] . "' $str_selected>" . $db->row['NOMBRE_GRUPO'] . " $usuarios_administrativos</option>";
					}
					
					$db->Close();
				?>	
					</select>
					<span class="sp_hint"><?php echo $HINT_USER_GROUP;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				
				<dt>
					<label for="txt_nombre_usuario"><?php echo $LBL_NOMBRE;?></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_nombre_usuario" id="txt_nombre_usuario" value="<?php echo $nombre_usuario;?>" size='35'>
					<span class="sp_hint"><?php echo $HINT_USER_FULLNAME;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<dt>
					<label for="txt_apellido_paterno"><?php echo $LBL_PATERNO;?></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_apellido_paterno" id="txt_apellido_paterno" value="<?php echo $apellido_paterno;?>" size='35'>
				</dd>
			  
				<dt>
					<label for="txt_apellido_materno"><?php echo $LBL_MATERNO;?></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_apellido_materno" id="txt_apellido_materno" value="<?php echo $apellido_materno;?>" size='35'>
				</dd>
				
				<dt>
					<label for="cbm_sexo"><?php echo $LBL_GENERO;?></label>							
				</dt>
				<dd>
					<select name="cmb_genero" class="campo_captura">
					  <option value="H"	<?php if ($sexo=="H") echo "selected='selected'";?>> <?php echo $LBL_GENERO_MASC;?></option>
					  <option value="M"	<?php if ($sexo=="M") echo "selected='selected'";?>> <?php echo $LBL_GENERO_FEM;?></option>					  
					</select>										
				<dd>

				<dt>
					<label for="fecha_nacimiento"><?php echo $LBL_FECHA_NACIMIENTO;?></label>				
				</dt>
				<dd>
					<?php colocar_edit_date( "fecha_nacimiento", $fecha_nacimiento, 0, "" ); ?>
					<span class="sp_hint"><?php echo $HINT_BIRTHDAY_DATE;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				<br>	
				
				<label for="txt_direccion"><?php echo $LBL_DIRECCION;?></label>
				<input class="campo_captura" type="text" name="txt_direccion" id="txt_direccion" value="<?php echo $direccion;?>" size='50' maxlength='100'>
				
				<dt>
					<label for="txt_telefono"><?php echo $LBL_TELEFONO;?></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_telefono" id="txt_telefono" value="<?php echo $telefono;?>" size='35'>
				</dd>
				
				<dt>
					<label for="txt_correo"><?php echo $LBL_EMAIL;?></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_correo" id="txt_correo" value="<?php echo $email;?>" size='45' maxlength='50'>
					<span class="sp_hint"><?php echo $HINT_EMAIL;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<dt>
					<label for="txt_correo_alterno"><?php echo $LBL_EMAIL_ALTERNO;?></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_correo_alterno" id="txt_correo_alterno" value="<?php echo $email_alterno;?>" size='45' maxlength='50'>
					<span class="sp_hint"><?php echo $HINT_EMAIL_ALTERNO;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>				
				<br>
								
				<label for="chk_status"><?php echo $LBL_STATUS;?></label>
				<input class="checkbox" type="checkbox" name="chk_status" id="chk_status" <?php echo (($chk_status=="A") ? "checked" : ""); ?>/>
				<br><br>				
				
				<div id='btnAsignarPrivilegios' name='btnAsignarPrivilegios'>
					<a href='javascript:mostrarOcultarLink();'>&laquo;&laquo; <?php echo $LBL_ASSIGN_PRIVILEGES;?> &raquo;&raquo;</a>
				</div>
				
				<br>
									  
				<div id="buttonarea">
				 <input id="btnGuardar" name="btnGuardar" class="boton" type="button" value="<?php echo $BTN_SAVE;?>"  onClick='javascript:validar();'>
				 <input id="btnRegresar" name="btnRegresar" class="boton" type="button" value="<?php echo $BTN_CANCEL;?>"  onClick='<?php echo back_function();?>' >
			    </div>

			    <br>
				
				<?php		
				if( isset($_POST["privilegios_marcados"]) )
					$vp.=$_POST['privilegios_marcados']." ";
					
				echo "<input id=privilegios_marcados name=privilegios_marcados type='hidden' class=hidden value='$vp'>";				
			  	?>
				
			</form>
	  
	</div> <!-- caja_datos --> 	

 </div> <!-- contenido_principal -->

 <div id="contenido_adicional">
 
 	<div id="privilegios" name="privilegios" class="groupbox" 
	   style="<?php echo ($the_action=="create_new") ? "display:none;" : ""; ?> border:1px solid black; font-size: 80%; height: 480px; overflow : auto; scrollbar-base-color:#369; scrollbar-highlight-color:#5569b4;  ">				
<br>
		<h2><?php echo $LBL_PRIVILEGES;?></h2><br>
	   
		<?php
		
			if( $the_action == "create_new" ) 
			   $id_usuario = -9999;
			
			$db->Open( " SELECT a.PRIVILEGIO, a.DESCRIPCION, b.ID_USUARIO " . 
					   " FROM cfgprivilegios a " .
					   " LEFT JOIN cfgusuarios_privilegios b ON (b.ID_BIBLIOTECA=$id_biblioteca and b.ID_USUARIO=$id_usuario and b.PRIVILEGIO=a.PRIVILEGIO) " .
					   " ORDER BY a.TIPOPRIVILEGIO, a.PRIVILEGIO ");
			
			$num=0;
			$checado="";
			
			$bold_active = true;
			$last_ciento = -1;
			
			while( $db->NextRow() )
			{
				$num++;
				
				if ( $db->Field("ID_USUARIO") != 0 ) 
					$checado=" checked ";					
				else 	  
					$checado="";
									
				if( ((int) ($db->Field("PRIVILEGIO") / 100)) != $last_ciento )
				{
					$last_ciento = (int) ($db->Field("PRIVILEGIO") / 100);
					$bold_active = !$bold_active;
				}
				
				//echo $last_ciento;
				
				$bold_on  = "";
				$bold_off = "";
				
				$class_hilite = "hilite_even";
				
				if( $bold_active ) 
				{
					$bold_on  = "<strong>";
					$bold_off = "</strong>";
					$class_hilite = "hilite_odd";
				}
				
				echo "<DIV class='$class_hilite'><input name='priv_$num' id='priv_$num' value='" . $db->Field("PRIVILEGIO") . 
					 "' type=checkbox ". $checado ." onClick='javascript:selecciona();'>&nbsp;$bold_on" . $db->Field("DESCRIPCION") . "$bold_off</DIV>";		
					 		
			}
												
			echo '<input id=privilegios_totales name=privilegios_totales type=hidden class=hidden value="' . $num . '">';
					
			$db->Close();			
			
			echo "<br>";
		
	  	?>	
				
	</div>	
	
 </div> <!-- contenido_adicional -->
 
 <br style='clear:both;'>

</div>
<!-- end div bloque_principal -->

<?php  

if( !allow_use_of_popups() )
	display_copyright(); 

$db->destroy();

?>

</div><!-- end div contenedor -->	

</body>

</html>