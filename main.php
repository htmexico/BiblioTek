<?php
	session_start();

	include "funcs.inc.php";	

	if( issetsessionvar( "biblio_firmado" ) )
	{
	
		if( issetsessionvar("last_url" ) and getsessionvar("last_url" ) != "" ) // 14-oct-2010
		{			
			ges_redirect( getsessionvar("last_url" ) ); // Workaround para F5 Refresh
		}	

		if( getsessionvar( "biblio_firmado" ) == "SI" )
			ges_redirect( "index.php" );
	}

	if( isset( $_GET["id_lib"] ) and isset( $_GET["init"] ))
	{
		$id_lib = $_GET["id_lib"];
		
		if( issetsessionvar("id_lib") )
		{
			if( getsessionvar("id_lib") != $id_lib )
			{
				unset( $_SESSION["id_lib"] );
			}
		}

		if( !issetsessionvar("id_lib") )
		{
			// verificaciones
			require_once( "basic/bd.class.php" );

			$db = new DB( "SELECT b.ID_RED, b.TEMA, b.ARCHIVO_BANNER, b.IDIOMA, b.PAIS, b.USAR_POPUPS, b.SS_PHYSICAL_DIR " .
						  "FROM cfgbiblioteca b " .
						  "WHERE b.ID_BIBLIOTECA=$id_lib " );

			if( $db->NextRow() )
			{
				setsessionvar( "ss_physical_dir", $db->row["SS_PHYSICAL_DIR"] );
				
				global $ACCESS_CFG;
				require_once( getsessionvar("ss_physical_dir") . "APP_CONFIG.php" );
				
				if( isset($ACCESS_CFG->banner) )
				{
					setsessionvar( "file_banner", $ACCESS_CFG->http_public_dir . $ACCESS_CFG->banner );
				}			
				
				setsessionvar( "id_lib", $id_lib );

				setsessionvar( "id_red", $db->row["ID_RED"] );
				setsessionvar( "language_pref", $db->row["IDIOMA"] );
				setsessionvar( "pais", $db->row["PAIS"] );
				setsessionvar( "usar_popups_transactions", $db->row["USAR_POPUPS"] );

				setsessionvar( "skin", $db->row["TEMA"] );
			
			}

			$db->Close();
			$db->destroy();
		}
	}
	else if( issetsessionvar("id_lib") ) 
	{
		// pasar
		$id_lib = issetsessionvar("id_lib");
	}
	else	
	{
		unset( $_SESSION["id_lib"] );
		unset( $_SESSION["id_red"] );
		unset( $_SESSION["language_pref"] );
		unset( $_SESSION[ "language" ] ); 
		unset( $_SESSION["pais"] );
		unset( $_SESSION["usar_popups_transactions"] );

		unset( $_SESSION["file_banner"] );

		unset( $_SESSION["skin"] );

		ges_redirect( "default.php" );
	}
	
	include_language( "global_menus" );

	$page = "";

	 if( isset( $_GET["page"] ) )
		$page .= "page=" . $_GET["page"];	

	$error = 0;
	$lastusr = "";

	if( isset( $_GET["error"] ) )
		$error = $_GET["error"];

	if( isset( $_GET["lastuser"] ) )
		$lastusr = $_GET["lastuser"];

	if( $lastusr == "" )
	{
		if( isset($_COOKIE["usuario"]) )
			$lastusr = $_COOKIE["usuario"];
	}

	include_language( "login" );

	// Draw an html head
	include "basic/head_handler.php";
	HeadHandler( "$LBL_CAPTION" );
?>

    <SCRIPT language="JavaScript" src="basic/md5.js" type="text/javascript"></script>
	<SCRIPT language="JavaScript" type="text/javascript">

		function valida()
		{
			var error = 0;
	
			if( document.login_form.nomusr.value == "" )
			{
				alert( "<?php echo $MSG_USERNAME_MISSING;?>" );
				error = 1;
			}
			
			if( error == 0 )
			{
				if( document.login_form.passwrd.value == "" )
				{
					alert( "<?php echo $MSG_PASSWORD_MISSING;?>" );
					error = 1;
				}
			}		
				
			if( error == 0 )
			{
			    var pass1 = hex_md5( document.login_form.passwrd.value );
		
			    document.login_form.val_int.value = pass1;
			    document.login_form.passwrd.value = '';
				
				<?php
					$app_dir = GetPublicAppDir(1);
				  ?>
				
				document.login_form.action = "<?php echo $app_dir;?>phps/validausuario.php";
				document.login_form.method = "POST";
				document.login_form.submit();
				
				return true;
			}
			else
			    return false;
		}
	
		// Sirve para hacer un submit cuando se oprime la tecla enter
		function submitenter(myfield,e)
		{
			var keycode;
		
			if (window.event) keycode = window.event.keyCode;
			else if (e) keycode = e.which;
			else return true;
		
			if (keycode == 13)
			{
				var retval = login_verify();
				
				return retval;	
			}
			else
				return true;
		}
				
		function init()
		{
			<?php 
			
			  if( $error == 2 or $lastusr != "")
			  {
			  	  echo "document.login_form.passwrd.focus();	";
			  }
			
			 ?>
		}
	
	</SCRIPT>

<body id="home" onLoad="javascript:init();">
<?php
	display_global_nav();
 ?>

<div id="contenedor">

<?php 
   display_banner();  
   display_menu(); 

 ?>

<div id="bloque_principal"> <!-- inicia contenido -->
 <div id="contenido_principal">

	<div style='font-size: 105%; font-weight: bold; '><?php echo $BIBLIOTEK_LOGIN_WELCOME;?></div><br>

	<div class="caja_datos" style="overflow:auto;">
		<h2><?php echo $LBL_LOGIN_HEADER;?></h2><hr>

		<?php
			if( $error == 2 )
			{
				echo "<div class=caja_errores>";
				echo " <strong>$ERROR_LOGIN_ERROR</strong>";
				echo "</div>";
			}			
			else if( $error == 3 )
			{
				echo "<div class=caja_errores>";
				echo " <strong>$ERROR_USER_INACTIVE</strong>";
				echo "</div>";
			}
			else if( $error==4)
			{
				// 03-may-2011
				echo "<div class='caja_errores' style='margin-bottom: 3px;'>";
				echo " <strong>Error: Esta biblioteca virtual está inactiva / This library account is inactive.</strong>";
				echo "</div>";
			}			
		 ?>

		<form action="" method="post" name="login_form" id="login_form" class="forma_captura" onSubmit="return valida(this);" style='position: relative; float: left; width:330px;'>
			<label for="nomusr" title="Enter your name"><?php echo $LBL_USERNAME;?></label>
			<input type="text" name="nomusr" id="nomusr" value="<?php echo $lastusr;?>"><br>
		  
			<label for="passwrd" title="Enter your password"><?php echo $LBL_PASSWORD;?></label>
			<input type="password" name="passwrd" id="passwrd" onkeypress='return submitenter(this,event);'><br>
			<br>
		  
			<div id="buttonarea">
				&nbsp;<input class="submit" type="submit" value="Ingresar" name="submit">
			</div>			  
			<input type='hidden' class='hidden' name='val_int' id='val_int'>
		  
		  <?php		  
			if( isset($id_lib) )
			{
				echo "<input type='hidden' class='hidden' name='id_lib' id='id_lib' value='$id_lib'>";
			}
		    ?>
		</form>

	    <div id="vinetas" style='position:relative; float: right; border:0px solid red; width:230px;'>
		  <ul>
			<li>Mantenga su contrase&ntilde;a en confidencialidad.</li>
			<li>C&aacute;mbiela frecuentemente.</li>
			<li>Utilice contrase&ntilde;as que no sean f&aacute;ciles de adivinar.</li>
		  </ul>
	    </div>
		
		<br style='clear:both;'><br>
	  
	</div> <!-- caja_datos -->
	
	<br style='clear:all;'>
	
	<?php
	
	   if( isset($id_lib) )
	   {
		   require_once( "phps/opac.php" );
		   require_once( "basic/bd.class.php" );
		   
		   $db = new DB();
		   
		   // PENDIENTE: verificar config. de la biblioteca
		   muestra_consulta( $db, $id_lib, -1 ); // -1 SHOW OPAC in main page
	   }
	   	
	  ?>

	  <!-- <br style='clear:all;'> -->
	
	  <div class="caja_con_ligas">
	
		<?php
			if( isset($id_lib) )
			{		
				LIBRARY_Display_Events( $db, $id_lib, 1, 1 );
			}
		?>
	   
	  </div>

 </div> <!-- contenido_principal -->
 
	<div id="contenido_adicional">
	
		<?php
			if( isset($id_lib) )
			{
				LIBRARY_Display_Notes( $db, $id_lib );
			}
		 ?>
	
	</div> <!-- contenido_adicional -->

</div>
<!-- end div bloque_principal -->

<?php  display_copyright();

 ?>

</div><!-- end div contenedor -->

</body>

</html>