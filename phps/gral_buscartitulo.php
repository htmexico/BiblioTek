<?php
	session_start();

	/**********

		09-abril-2009:  Se crea el archivo "gral_buscartitulo.php"
		27-abril-2009:  Se agrega la funcionalidad para ver copias desde la pantalla principal
		09-julio-2009:  Ajustes a seleccion de elementos (para aparecer el parentNode)
		15-oct-2009:    Permitir elegir Items prestados
		07-jun-2011:   Se configura el parám. embeded para permitir obtener una consulta ($id_consulta) cualquiera
					  para una llamada Ajax

	 **/

	include ("../funcs.inc.php");

	check_usuario_firmado(); 
	
	$embeded = read_param( "embeded", 0 );

	if( $embeded == 0 )
	{
		// Draw an html head
		include ("../basic/head_handler.php");
	}
	
	include_language( "global_menus" );
	include_language( "anls_consultatitulos" );

	if( $embeded == 0 )
	{
		HeadHandler( "$LBL_PAGE_TITLE", "../" );	
	}

	$action = read_param( "action", "" );

	// valores posibles K, T, A o S
	// K - Palabras Clave (Keywords)
	// T - Title
	// A - Author
	// S - Subjects / Materias
	//	
	$id_consulta = read_param( "id_consulta", 0, 0 );
	
	$rad_searchBy = read_param( "type", "" );
	$txt_search = read_param( "search", "" );
	
	$ver_copias=read_param( "ver_copias", 0 );
	$elegir_prestados=read_param( "elegir_prestados", 0 );
	
?>

<script type="text/javascript">	

	// Cuando la función sea utilizada solo para elegir un TITULO
	function setIDTITULO( val, descr, icon )
	{
		var id = window.opener.document.getElementsByName( "txt_id_title" );
		var lbl_id = window.opener.document.getElementsByName( "lbl_id_title" );
		var btn_continuar = window.opener.document.getElementsByName( "btnContinuar" );
		
		if( id.length == 0 )
			id= window.opener.document.getElementsByName( "txt_id_material" );
				
		if( id.length > 0 )
			id[0].value = val;

		if( lbl_id.length > 0 )
		{
			// aparecer el parentNode en caso de que esté oculto
			if( lbl_id[0].parentNode.style.visibility == "hidden" )
			{
				lbl_id[0].parentNode.style.visibility = "visible";   /* solo cuando se requiere el display en BLOCK*/
				lbl_id[0].parentNode.style.display = "block";
			}					
			else if( lbl_id[0].parentNode.style.display == "none" || lbl_id[0].parentNode.style.display == "" )
			{
				lbl_id[0].parentNode.style.display = "inline";
				lbl_id[0].parentNode.style.visibility = "visible";
			}		
		
			var codeHTML = "";
			
			if( icon != "" ) { codeHTML = "<img src='../" + icon + "'>"; }
			
			codeHTML += "<bold>" + descr +"</bold>";
			
			lbl_id[0].innerHTML = codeHTML;
		}
			
		if( btn_continuar.length > 0 )
			btn_continuar[0].disabled = false;

		window.close();		
	}	
	
	// Cuando la función sea utilizada para elegir una copia o ejemplar
	// podrá colocar valores en un campo llamado txt_id_material o txt_id_item
	// colocará la descripción del ITEM en lbl_id_title
	function setIDITEM( id_titulo, id_material, id_item, descr, icono )
	{
		var txt_id_titulo = window.opener.document.getElementsByName( "txt_id_title" );
		var txt_id_item = window.opener.document.getElementsByName( "txt_id_item" );
		var txt_id_material = window.opener.document.getElementsByName( "txt_id_material" );
		var lbl_descrip = window.opener.document.getElementsByName( "lbl_id_title" );
		var btn_continuar = window.opener.document.getElementsByName( "btnContinuar" );
		
		var selected_value = window.opener.document.getElementsByName( "selected_value" );

		if( txt_id_titulo.length > 0 )
		{
			txt_id_titulo[0].value = id_titulo;
		}

		if( txt_id_material.length > 0 )
		{
			txt_id_material[0].value = id_material;			
			
			// cuando se elija el ID del MATERIAL
			if( selected_value.length > 0 )
				selected_value[0].value = id_item; // colocar el item
		}
		
		if( txt_id_item.length > 0 )
		{
			txt_id_item[0].value = id_item;			
		}		

		if( lbl_descrip.length > 0 )
		{
			// aparecer el parentNode en caso de que esté oculto
			if( lbl_descrip[0].parentNode.style.visibility == "hidden" )
			{
				lbl_descrip[0].parentNode.style.visibility = "visible";   /* solo cuando se requiere el display en BLOCK*/
				lbl_descrip[0].parentNode.style.display = "block";
			}			
			else if( lbl_descrip[0].parentNode.style.display == "none" || lbl_descrip[0].parentNode.style.display == "" )
			{
				lbl_descrip[0].parentNode.style.display = "inline";
				lbl_descrip[0].parentNode.style.visibility = "visible";
			}

			lbl_descrip[0].innerHTML = "<bold><img src=../" + icono + "> " + descr + "</bold>";
			lbl_descrip[0].style.visibility = "visible";
		}
			
		if( btn_continuar.length > 0 )
			btn_continuar[0].disabled = false;

		window.close();
	}	
	
	function init_focus()
	{
		var edit_search = js_getElementByName("search_For");
		
		if( edit_search )
			edit_search.focus();
	}

</script>

<body id="home" onLoad='javascript:init_focus()'>

<br>

<?php

	if( $embeded == 1 )
		echo '<div>';
	else
		echo '<div id="contenedor">';

	if( $embeded == 1 )
		echo '<div>';
	else
		echo '<div id="bloque_principal">';

	if( $embeded == 1 )
		echo "<div style='width: 99%;'>";
	else
		echo "<div id='contenido_principal' style='width: 90%;'>";
	
	if( $embeded == 0 )
		echo "<h2>$LBL_PAGE_TITLE_V2</H2><hr>";
	
	require_once "opac.php";
	require_once "../basic/bd.class.php";
	
	$db = new DB();
	
	$id_biblioteca = getsessionvar("id_biblioteca");
	
	if( $id_consulta == 0  )
	{
		$id_consulta = obtener_consulta_default_admva( $db, $id_biblioteca );

		if( $id_consulta == 0 )
		{
			echo "<br>";
			echo "$NO_SEARCHES_FOUND";
		}
	}

	$extra_url = "";

	if( $ver_copias == 1 )
		$extra_url .= "ver_copias=1";
		
	if( $elegir_prestados == 1 )
		$extra_url .= "&elegir_prestados=1";

	if( $id_consulta <> 0 )
		muestra_consulta( $db, $id_biblioteca, $id_consulta, $txt_search, $rad_searchBy, "gral_buscartitulo.php", $extra_url ); 	
		
	if( $embeded == 1 )
	{
		echo "<div id='embeded_search' name='embeded_search' style='text-align:left; max-height:300px; overflow: auto;'>&nbsp;</div>";
	}

	if( $action == "go" )
	{			
		echo "<br>";

		$func = "";

		if( $ver_copias==0 )
		{
			// SOLO se puede seleccionar un TITULO
			
			// ? ID Titulo
			// @ Descripción
			// % icono
			$func = "javascript:setIDTITULO(?,@,%);";
		}				
		else if( $ver_copias == 1 )
		{
			// Se permite seleccionar un ejemplar/copia
			
			// ? ID Titulo
			// # ID Material (Copia/Ejemplar)
			// & ID del ITEM
			// @ Descripción
			$func = "javascript:setIDITEM(?,#,&,@,%);";
		}
		
		inserta_resultados_consulta( $db, $id_biblioteca, 0, $rad_searchBy, $txt_search, 0, $func, $ver_copias, $elegir_prestados );
	}

?>		
	
  </div>  <!-- contenido_principal -->
				
  <br>

</div>

<?php  if( $embeded == 0 ) display_copyright(); ?>

</div>
<!-- end div contenedor -->

</body>

</html>	