<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  21ene2009: Se crea archivo modelo.
     */
		
	include "funcs.inc.php";
	include_language( "global_menus" );
	
	check_usuario_firmado(); 
	
	/** INICIO - FUNCIONES DE MODIFICACION/CREACION/BORRADO **/

	// Coloca un encabezado HTML <head>
	include "basic/head_handler.php";
	HeadHandler( "COLOQUE AQUI TITULO A MOSTRAR", "../" );

?>

<SCRIPT language="JavaScript">

	function a1()
	{
	}
	
</SCRIPT>

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
   display_menu(); 
 ?>
 
<div id="bloque_principal"> <!-- inicia contenido -->
 <div id="contenido_principal">

	<p class="info">
	   <span style="font-variant:small-caps;font-size:110%;">Biblio TEK</span> es un sitio protegido desarrollado por <SPAN>Grupo GES</SPAN>, por lo cual es necesario que proporcione sus datos de acceso.</strong>. 
	</p>

	<br>
	
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
		<h2>Título Resaltado:</h2>
		<ul>
		  <li>Nota 1: <a href="minota.htm">El camino a las decisiones informadas, con Jared M. Spool</a></li>
		  <li>Nota 2: <a href="minota2.htm">Diseño de Documentación con Dan Brown </a></li>
		  <li>Nota 3: <strong> <a href="minota3.htm">Cumbre de Desarrolladores</a></strong><br/>
		    Newport Beach, CA. </a></li>
		</ul>
	</div>	
	
  </div> <!-- contenido_adicional -->

</div>
<!-- end div bloque_principal -->

<?php  display_copyright(); ?>

</div><!-- end div contenedor -->

</body>

</html>