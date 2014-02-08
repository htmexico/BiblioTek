<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  19 mar 2009: Se crea el archivo PHP
	  22 mar 2009: Se perfecciona la interface y la consulta como tal
	  24 mar 2009: Se agrega la capacidad de mostrar portadas
	  09 abr 2009: Se extrae el resultado de la consulta hacia el archivo opac.php
	  
	  21 jul 2011: Se agrega logeo de las consultas OPAC... antes no se logeaba
	  
	  PENDIENTE:
	  
	    - Mejorar el mecanismo de consulta para que el resultado o el QUERY PROCESADO
		  se almacene en un buffer que haga más rápida la navegación.
		  
		- PAGINAR LA BUSQUEDA
		
		- Filtrar la búsqueda con respecto a la edad del usuario.
	  
     */
		
	include "../funcs.inc.php";
	
	include_language( "global_menus" );
	include_language( "anls_consultatitulos" );
	include "../basic/bd.class.php";
	
	//check_usuario_firmado(); 

	// Coloca un encabezado HTML <head>
	include "../basic/head_handler.php";
	HeadHandler( "$LBL_PAGE_TITLE", "../");
	
	if( issetsessionvar( "id_biblioteca" ) )
	{
		$id_biblioteca = getsessionvar("id_biblioteca");
		$id_consulta = 0;
	}
	else
	{
		$id_biblioteca = read_param( "id_biblioteca", 0, 1 ); // fail if don't	
		$id_consulta = read_param("id_consulta",0,1); // fail if don't
	}
	
	//
	// valores posibles
	// BY_KEYWORDS - Palabras Clave (Keywords)
	// BY_TITLE - Title
	// BY_AUTHOR - Author
	// BY_SUBJECT - Subjects / Materias
	// BY_CALLNUM - Por signatura topográfica
	// BY_ISBN - Por ISBN
	// BY_ISSN - Por ISSN
	
	$action = read_param( "action", "" );
	$rad_searchBy = read_param( "type", "" );
	$txt_search = read_param( "search", "" );
	
	if( getsessionvar("biblio_firmado") == "SI" )
	{
		require_once( "../privilegios.inc.php" );
		verificar_privilegio( PRIV_CATALOGUE_SEARCH, 1, 1 );
	}

?>

<SCRIPT type="text/javascript" language="JavaScript">

	window.onload=function()
	{
		prepareInputsForHints();
		
		var obj = js_getElementByName( "search_For" );
		
		if( obj )
			obj.focus();
		
	}	
	
</SCRIPT>

<STYLE>

  #consulta
  {
    float: left;
    width: 99%;
  }
  
  #contenido_principal 
  {
	float:none;  /* evita el flicker */
	width: 98%;
  }
  
  #contenido_adicional
  {
	width: 5px;
  }
  
 
</STYLE>

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
   display_menu( "../" ); 

?>
 
<div id="bloque_principal"> <!-- inicia contenido -->
 
 <div id="contenido_principal">
	
<?php

	require_once "opac.php";
	
	$db = new DB();
	
	if( $id_consulta == 0  )
	{
		$id_consulta = obtener_consulta_default_x_usuario( $db, $id_biblioteca );

		if( $id_consulta == 0 )
		{
			echo "<br>";
			echo "$NO_SEARCHES_FOUND";
		}
	}
	
	if( $id_consulta <> 0 )
		muestra_consulta( $db, $id_biblioteca, $id_consulta, $txt_search, $rad_searchBy, "", "", 1 ); 
		
	if( $action == "go" )
	{
		//$mostrar_link_para_ver_info_titulos = verificar_privilegio( PRIV_VIEW_TITLES_INFO, 0 );
			
		$info = inserta_resultados_consulta( $db, $id_biblioteca, $id_consulta, $rad_searchBy, $txt_search, null, "", 0 );
		
		if ( getsessionvar("biblio_firmado") == "SI" )
		{
			require_once("../actions.inc.php");		

			agregar_actividad_de_usuario( ANLS_TITLES_SEARCHES, "$txt_search", 0, 0, str_replace( "BY_", "", "$rad_searchBy") );
		}
		
		if( $id_consulta == -1 )
		{
			// OPAC
			$fecha = current_dateandtime();
			if (getenv("HTTP_X_FORWARDED_FOR")) 
				$ip_addr = getenv("HTTP_X_FORWARDED_FOR");
			else 
				$ip_addr = getenv("REMOTE_ADDR");							
			
			$query = "INSERT INTO opac_bitacora ( ID_BIBLIOTECA, FECHA,  BUSQUEDA, IP_ESTACION, CAUX1) " . 
						"VALUES ( $id_biblioteca, '$fecha', '$txt_search', '$ip_addr', '".str_replace( "BY_", "", $rad_searchBy) ."' ) ";
			$db->ExecSQL( $query );
		}
	}
	
	$db->destroy();
	
 ?>	

 </div> <!-- contenido_principal -->

 <div id="contenido_adicional">
	&nbsp;
  </div> <!-- contenido_adicional -->
 
</div>
<!-- end div bloque_principal -->

<?php  display_copyright(); ?>

</div><!-- end div contenedor -->

</body>

</html>