<?php
	session_start();
/**********

28-enero-2009	Se crea el archivo autoprestamos.php

**********/

	include ("../funcs.inc.php");
	include_language( "global_menus" ); // agregar en todos los archivos
	include_language( "cambiar_idioma" );// agregar en todos los archivos

	check_usuario_firmado(); 

	// Draw an html head
	include ("../basic/head_handler.php");
	HeadHandler( "Consulta al Catálogo en Línea" );

?>
<SCRIPT language="JavaScript">
	function validamaterial()
	{
		if( numeromat.value == '' ) 
		{
			alert( "Para proceder a la búsqueda es necesario introducir un valor..." ); 
		}
		else
		{
			var url;
			 url = "../circulacion/autoprestamo.php?idmaterial=" + numeromat.value;
			 document.frames.location.href = url;
		}		
	}
</SCRIPT>

  <LINK href="../css/screen.css" type=text/css rel=stylesheet>
  <LINK href="../css/reset.css" type=text/css rel=stylesheet>
  <LINK href="../css/fonts.css" type=text/css rel=stylesheet>

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
   display_menu('../'); 
 ?>
  <?php
 	//codigos de programacion de autoprestamos---luis
	if( isset($_GET["idmaterial"]) )
	   $idmaterial = $_GET["idmaterial"];
	else
	{
	   $idmaterial 		= "";
	   $nombrematerial  ="";
	   $statusmaterial	="";
	}
	   
 ?>
 
  <div id="bloque_principal"> Auto Prestamo 
    <p class="info"><hr> </p>
    <div id="contenido_principal"> <br>

	  <div class="caja_datos">
		<h2>Introdusca  los datos requeridos para registrar el registro</h2><br>

		<div id="caja_datos_login">

		 <form action="" method="post" name="prestamo_form" id="prestamo_form" class="forma_captura" onSubmit="return valida(this);">
			  <div>
				<label for="nomusr" title="Enter your name">Usuario</label>
				
            <input type="text" name="nomusr" id="nomusr" value="<?php echo getsessionvar('usuario'); ?>" disabled>
			  </div>
			  <div>
				<label for="passwrd" title="Ingrese la contraseña">Numero Material</label>
				
            <input type="text" name="id_material" id="id_material" value="">
			  </div>
			  <div>
				<label for="passwrd" title="Ingrese la contraseña">Nombre Material</label>
				
            <input type="text" name="nommaterial" id="nommaterial" value="" size="75" disabled>
			  </div>
			  <div>
				<label for="passwrd" title="Ingrese la contraseña">Fecha de Prestamo</label>
				
            <input type="text" name="fecha_prestamo" id="fecha_prestamo" value="<?php echo strftime('%d/%m/%Y'); ?>" disabled>
			  </div>
			  <div>
				<label for="passwrd" title="Ingrese la contraseña">Status</label>
				
            <input type="text" name="status_item" id="status_item" value="" disabled>
			  </div>
 			  <div id="buttonarea">
				
            <input class="submit" type="submit" value="Ingresar" name="submit" />
			  </div>
			  
			  
          <input type=hidden name=val_int id=val_int>
			 </form>		  
		</div>

	</div> <!-- caja_datos -->
	

  </div>
  <?php  display_copyright(); ?>
</div>
</body>

</html>
