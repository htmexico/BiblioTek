<?php
	session_start();
/**********

28-enero-2009	Se crea el archivo circulacion.php

**********/
	include ("../funcs.inc.php");

	check_usuario_firmado(); 

	// Draw an html head
	include ("../basic/head_handler.php");
	HeadHandler( "Consulta al Catálogo en Línea" );

/*function display_menu_circulacion()
{
	if ( getsessionvar("biblio_firmado") == "SI" )
	{
		echo '<div id="nav">';
		echo '	<ul>';
		echo '		<li id="nav-home">'.
		     '      <a title="Prestamos" href="circulacion\prestamos.php" target=_self>Prestamos</a></li>';
		echo '		<li id="nav-about"><a href="devoluciones.php"><span>Devoluciones<br></span></a></li>';
		echo '		<li><a href="renovacion.php"><span>Renovacion<br></span></a></li>';
		echo '		<li><a href="autoprestamo.php"><span>Auto<br>Prestamo</span></a></li>';
		echo '		<li><a href="reservacion.php">Reservacion<br></a></li>';
		echo '		<li id="nav-cart"><a href="rastreo.php"><span>Rastreo<br></span></a></li>';
		echo '		<li id="nav-cart"><a href="e_mail.php"><span>Servicio de<br>E-mail</span></a></li>';
		echo '		<li id="nav-cart"><a href="formatos.php"><span>Impresion de<br>Formatos</span></a></li>';
		echo '	</ul>';
		echo '</div>  <!-- nav -->';
	}
}*/	

?>
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
  <div id="bloque_principal"> 
    <!-- inicia contenido -->
    <p class="info">
      <?php //echo display_menu_circulacion(); ?>
    </p>
    <div id="contenido_principal"> <br>
      <div class="caja_con_ligas"> Formato </div>
      <!-- caja_con_ligas -->
    </div>
    <!-- contenido_principal -->
  </div>
  <!-- end div bloque_principal -->
  <?php  display_copyright(); ?>
</div>
<!-- end div contenedor -->

</body>

</html>
