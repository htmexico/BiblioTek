<?php
/***
 Operación por alumnos
 
 */
	session_start();
		
	include "funcs.inc.php";
	
	check_usuario_firmado( "" );   /* verifica si es un usuario que ya se logeo */

	include_language( "global_menus" );  /* incluir archivos de idioma */
	include_language( "index" );

	// Draw an html head
	include "basic/head_handler.php";    /* colocar TAGS o ETIQUETAS de la parte superior de cada pagina */
	HeadHandler( "Bienvenidos a WebEscolar.NET" );   // Coloca incluso el TITULO en la ventana del navegador
	
	$changing_personal_skin = read_param( "changing_personal_skin", 0 );
	
	if( $changing_personal_skin == 1 )
	{
		$personal_skin = read_param( "personal_skin", "" );
		
		db_query( "UPDATE cfgusuarios SET TEMA_PERSONAL='$personal_skin' WHERE ID_BIBLIOTECA=" .  getsessionvar("id_biblioteca") . " and ID_USUARIO=" . getsessionvar("id_usuario") );
		
		setsessionvar("personal_skin", $personal_skin);
	}	
	
?>

<script language='javascript'>

	function changePersonalSkin()
	{
		var skin_sel = document.getElementsByName("sel_personal_skin");
		
		if( skin_sel.length > 0 )
		{
			location.href = "index.php?changing_personal_skin=1&personal_skin=" + skin_sel[0].value;
		}
	}

</script>

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
	
	<?php 
		  
		require_once( "basic/bd.class.php" );
		
		$db = new DB();
		
		if( getsessionvar("empleado") == "S" )
		{
			echo "<div id='contenido_principal'>";
			
			echo "<p class='info'>$BIBLIO_TEK_WELCOME</p>";
			echo "<br>";
	
			echo "<div class='caja_con_ligas'>";

			echo "<div class='lista_elementos_indexada'>";
			echo "   <h1>$QUICK_LINKS</h1>";

			USER_SHOW_QuickLinks( $db, getsessionvar("id_biblioteca"), getsessionvar("id_usuario") );

			echo "	</div>\n";

			echo "	<div class='lista_elementos_indexada'>";
			echo "	  <h1>$RECENT_USED_OPTIONS</h1>";

			USER_HISTORY_RecentActions( $db, getsessionvar("id_biblioteca"), getsessionvar("id_usuario") );

			echo "	</div>";
			echo "</div><!-- caja_con_ligas -->	\n";

			echo "</div> <!-- contenido_principal -->\n";

			echo "<div id='contenido_adicional'>";
			
			LIBRARY_Display_Notes( $db, getsessionvar("id_biblioteca") );
			
			echo "</div> <!-- contenido_adicional -->\n";		
		}
		else
		{
			include ( "phps/circulacion.inc.php" );
			
			include_language( "circ_bandeja" );
			
			$user = new TUser( getsessionvar("id_biblioteca"), getsessionvar("id_usuario"), $db );
			
			if( getsessionvar("personal_skin") == "" or getsessionvar("personal_skin") == "reader_default" )
				include("templates/reader_default.php");
			else if( getsessionvar("personal_skin") == "reader_abs_blue" )
				include("templates/reader_abs_blue.php");	
			else if( getsessionvar("personal_skin") == "reader_abstracto" )
				include("templates/reader_abstracto.php");	
			else if( getsessionvar("personal_skin") == "reader_black_white" )
				include("templates/reader_black_white.php");
			else if( getsessionvar("personal_skin") == "reader_bob" )
				include("templates/reader_bob.php");			
			else if( getsessionvar("personal_skin") == "reader_boys" )
				include("templates/reader_boys.php");
			else if( getsessionvar("personal_skin") == "reader_cars" )
				include("templates/reader_cars.php");
			else if( getsessionvar("personal_skin") == "reader_colors" )
				include("templates/reader_colors.php");	
			else if( getsessionvar("personal_skin") == "reader_discreto" )
				include("templates/reader_discreto.php");	
			else if( getsessionvar("personal_skin") == "reader_girls" )
				include("templates/reader_girls.php");
			else if( getsessionvar("personal_skin") == "reader_green" )
				include("templates/reader_green.php");			
			else if( getsessionvar("personal_skin") == "reader_pink" )
				include("templates/reader_pink.php");			
			else if( getsessionvar("personal_skin") == "reader_purple" )
				include("templates/reader_purple.php");							
			else if( getsessionvar("personal_skin") == "reader_spiderman" )
				include("templates/reader_spiderman.php");	
			else if( getsessionvar("personal_skin") == "reader_yellow" )
				include("templates/reader_yellow.php");			

			$user->destroy();		
		}
		
		$db->destroy();
	?>

</div>
<!-- end div bloque_principal -->

<?php display_copyright(); ?>

</div><!-- end div contenedor -->

</body>

</html>
