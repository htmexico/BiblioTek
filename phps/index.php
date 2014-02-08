<?php
	session_start();

	include "funcs.inc.php";
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

	//include_language( "login" );
	
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
				
				document.login_form.action = "phps/validausuario.php";
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

	<div class="caja_datos">
		<h2><?php echo $LBL_LOGIN_HEADER;?></h2><hr>

		<?php
			if( $error == 2 )
			{
				echo "<div class=caja_errores>";
				echo " <strong>$ERROR_LOGIN_ERROR</strong>";
				echo "</div>";
			}			
			if( $error == 3 )
			{
				echo "<div class=caja_errores>";
				echo " <strong>$ERROR_USER_INACTIVE</strong>";
				echo "</div>";
			}			
		 ?>

		<form action="" method="post" name="login_form" id="login_form" class="forma_captura" onSubmit="return valida(this);" style='display: block; position: relative; float: left; border:0px solid red; width:330px;'>
			<label for="nomusr" title="Enter your name"><?php echo $LBL_USERNAME;?></label>
			<input type="text" name="nomusr" id="nomusr" value="<?php echo $lastusr;?>"><br>
		  
			<label for="passwrd" title="Ingrese la contraseña"><?php echo $LBL_PASSWORD;?></label>
			<input type="password" name="passwrd" id="passwrd" onkeypress='return submitenter(this,event);'><br>
			<br>
		  
			<div id="buttonarea">
				&nbsp;<input class="submit" type="submit" value="Ingresar" name="submit">
			</div>			  
		  <input type='hidden' class='hidden' name=val_int id=val_int>
		  
		  <?php
		  
			if( isset($id_lib) )
			{
		  
				echo "<input type='hidden' class='hidden' name='id_lib' id='id_lib' value='$id_lib'>";
		  
			}
		    ?>
		</form>

	    <div id="vinetas" style='display: block; float: right; border:0px solid red; width:230px; '>
		  <ul>
            <li>Mantenga su contrase&ntilde;a en confidencialidad.</li>
            <li>C&aacute;mbiela frecuentemente.</li>
            <li>Utilice contrase&ntilde;as que no sean f&aacute;ciles de adivinar.</li>
		  </ul>
	    </div>
		
		<br style='clear:both;'>
	  
	</div> <!-- caja_datos -->
	
	<br style='clear:all;'>
	
	<?php
	
	   if( isset($id_lib) )
	   {
		   require_once( "phps/opac.php" );
		   
		   // PENDIENTE: verificar config. de la biblioteca
		   muestra_consulta( $id_lib, -1 ); // -1 SHOW OPAC in main page	   
	   }
	   	
	  ?>

	  <!-- <br style='clear:all;'> -->

	<div class="caja_con_ligas">
		<h1>Ultimas noticias Grupo GES</h1>
		<p>En Grupo GES Sistemas Avanzados, buscamos siempre la innovaci&oacute;n 
          como un factor de valor agregado y que nos concede diferenciaci&oacute;n 
          de nuestros competidores.</p>
		
		<div class="lista_elementos_indexada">
			
          <h2>&Uacute;ltimas Novedades</h2>
									
          <ol>
            <li><a href="http://www.grupoges.com.mx/test.php"> Nuevos Lanzamientos</a></li>
            <li><a href="http://www.grupoges.com.mx/test.php"> 
              Requerimientos del M&oacute;dulo</a></li>
            <li><a href="http://www.grupoges.com.mx/test.php"> 
              Dise&ntilde;o de Base de Datos</a></li>
            <li><a href="http://www.grupoges.com.mx/test.php"> Standar de programaci&oacute;n</a></li>
            <li><a href="http://www.grupoges.com.mx/test.php"> C&oacute;digo en 
              Delphi y PHP</a></li>
          </ol>
        </div>
		
		<div class="lista_elementos_indexada">
			
          <h2>Art&iacute;culos del Newsletter</h2>
								
          <ol>
            <li><a href="http://www.grupoges.com.mx/grupoges_newsletter.php?num=1"> 
              C&oacute;mo exportar datos con Consultas Libres</a></li>
            <li><a href="http://www.grupoges.com.mx/grupoges_newsletter.php?num=2"> 
              Aprenda la operaci&oacute;n b&aacute;sica de Control Escolar GES 
              para Palm</a></li>
            <li><a href="http://www.grupoges.com.mx/grupoges_newsletter.php?num=3"> 
              M&oacute;dulo de Ttitulaci&oacute;n (obtenci&oacute;n de grado)</a></li>
            <li><a href="http://www.grupoges.com.mx/grupoges_newsletter.php?num=4"> 
              Obtenga un presupuesto de Ingresos al inicio del ciclo escolar</a></li>
            <li><a href="http://www.grupoges.com.mx/grupoges_newsletter.php?num=5"> 
              Aprenda a colocar etiquetas y controles con inclinaci&oacute;n dentro 
              de un formato.</a></li>
          </ol>
        </div>
	</div><!-- caja_con_ligas -->	

 </div> <!-- contenido_principal -->
 
 <div id="contenido_adicional">
	
	<div class="resaltados" >
		<h2>Notas Destacadas:</h2>
		<ul>
		  <li>Demo Control Escolar GES <a href="www.grupoges.com.mx/demo_intro.php">Introducción al Software</a></li>
		  <li>Solicita ser distribuidor <a href="www.grupoges.com.mx/want_to_be_partenr.php">Solicitamos socios de negocio</a></li>
		  <li>Enero 26-30, 2009:<br/>
		  <strong> <a href="http://www.techba.com">Semana de Inducción TechBA</a></strong><br/>
		    San José, CA. </a></li>
		</ul>
	</div>	
	
  </div> <!-- contenido_adicional -->

</div>
<!-- end div bloque_principal -->

<?php  display_copyright();

 ?>

</div><!-- end div contenedor -->

</body>

</html>