<?php
	session_start();
		
	include "funcs.inc.php";

	check_usuario_firmado( "" ); 

	// Draw an html head
	include "basic/head_handler.php";
	HeadHandler( "Consulta al Catálogo en Línea" );

?>

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

	  <p class="info">Elija el tipo de consulta.&nbsp;<select name=tipoconsulta><option value="1">Por Autor</option></select></p>

	<br>
	
	<div class="caja_con_ligas">
		<h1>Resultados</h1>
		
		<div class="lista_elementos_indexada">
			
          <h2 style="text-align:left;">Ordenados por Título</h2>
									
          <ol>
            <li><a href="http://www.grupoges.com.mx/test.php">El Rey del Mar</a> (Salgari, Emilio)</li>
            <li><a href="http://www.grupoges.com.mx/test.php"> 
              Requerimientos del M&oacute;dulo</a></li>
            <li><a href="http://www.grupoges.com.mx/test.php"> 
              Dise&ntilde;o de Base de Datos</a></li>
            <li><a href="http://www.grupoges.com.mx/test.php"> Standar de programaci&oacute;n</a></li>
            <li><a href="http://www.grupoges.com.mx/test.php"> C&oacute;digo en 
              Delphi y PHP</a></li>
          </ol>
        </div>
		
	</div><!-- caja_con_ligas -->	

 </div> <!-- contenido_principal -->

</div>
<!-- end div bloque_principal -->

<?php  display_copyright(); ?>

</div><!-- end div contenedor -->

</body>

</html>
