<?php
	/*******
	  Contiene múltiples funciones orientadas a compartir codigo común entre funciones que necesiten mostrar
	     algo dentro de la bandeja.
		 
	  Contents different functions for sharing common code between functions that need to show anything inside
	     the personal paperbin.
	  
	  Historial de Cambios / Changes History
	  
	  02 jun 2009: Se crea el archivo PHP
	  12-jun-2009: Se modifica el mini_bullet
     */

	//
	// Muestra los elementos que están dentro de la bandeja personal del usuario
	//
	function paperbin_show_contents( $id_biblioteca=0, $id_usuario = "" )
	{
		global $LINK_USER_REMOVE_ITEMS_FROM_BIN;
		
		require_once  getsessionvar("local_base_dir") . "basic/bd.class.php";
		require_once  getsessionvar("local_base_dir") . "phps/circulacion.inc.php";
		
		if( $id_biblioteca == 0 )
		   $id_biblioteca = getsessionvar("id_biblioteca");

		if( $id_usuario == "" )
		   $id_usuario = getsessionvar("id_usuario");
		
		$result = "";
		
		$db = new DB();
		
		$db->Open( "SELECT a.* FROM usuarios_bandeja a  " .
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_USUARIO=$id_usuario" );

		while( $db->NextRow() )
		{ 
			$id_titulo = $db->row["ID_TITULO"];
			$item = new TItem_Basic( $id_biblioteca, $id_titulo );
			
			$titulo_corto = $item->cTitle_ShortVersion;
			
			if( strlen($titulo_corto) > 75 )
				$titulo_corto = substr( $titulo_corto, 0, 72 ) . "...";
				
			// Remove this item from personal bin
			$delete_bandeja_item_link = "<a href='phps/circ_bandeja.php?accion=2&id_titulo=$id_titulo' title='$LINK_USER_REMOVE_ITEMS_FROM_BIN'><img src='../images/icons/cut.gif'></a>";
			
			//echo "<img src='" . getsessionvar("http_base_dir") ."images/mini_bullet.png'>";
			echo "<div class='mini_bullet' style='width:35px;'>&nbsp;&nbsp;&nbsp;&nbsp;$delete_bandeja_item_link</div>";
			echo "<div style='position:relative; top:-5px; display:inline; width: 90%;' onMouseOver='javascript:showSP_Hint(\"sp_hint_$id_titulo\");' " . 
										                  "onMouseOut='javascript:closeSP_Hint(\"sp_hint_$id_titulo\");'>" . 
											   "[" . $item->Material_ShortCode() . "] $titulo_corto</div>";
				
				// quitar item de la bandeja
				//echo "&nbsp;&nbsp;$delete_bandeja_item_link"; 
				
			echo "<span class='sp_hint' name='sp_hint_$id_titulo' id ='sp_hint_$id_titulo'>";
			
			// Verificar si hay portada registrada
			if( $item->cCover != NULL )
			{
				echo "<img src='" . getsessionvar("http_base_dir") ."phps/image.php?id_biblioteca=$id_biblioteca&id_titulo=$id_titulo&tipoimagen=PORTADA' width='80'\><br><br>";
			}
		
			echo "<img src='$item->cIcon'>&nbsp;$item->cDescrip_Item_Type_SP " . 
				 "<span class='hint-pointer'>&nbsp;</span>" .
				 "</span>";

			echo "<br clear='both'>\n\n";		
		
			unset( $item );

		}

		$db->FreeResultset();		
		
		return $result;

	}
	
	//
	// Esta función va a mostrar una lista de temas 
	// para los USUARIOS lectores 
	// a diferencia de los usuarios ADMVOS. éstos ultimos no podrán elegir una tema
	// personalizado.
	//
	function paperbin_show_themes_for_user()
	{
		echo "Tema:&nbsp;";
		echo "\n<SELECT name='sel_personal_skin' id='sel_personal_skin' onChange='javascript:changePersonalSkin()'>\n";
		echo "  <OPTION value='reader_default'>Default</OPTION>\n";
		echo "  <OPTION " . ((getsessionvar("personal_skin")=="reader_black_white") ? "selected" : "") ." value='reader_black_white'>Black and White</OPTION>\n";
		echo "  <OPTION " . ((getsessionvar("personal_skin")=="reader_bob") ? "selected" : "") ." value='reader_bob'>Bob Esponja</OPTION>\n";
		echo "  <OPTION " . ((getsessionvar("personal_skin")=="reader_boys") ? "selected" : "") ." value='reader_boys'>Para Chavos</OPTION>\n";
		echo "  <OPTION " . ((getsessionvar("personal_skin")=="reader_cars") ? "selected" : "") ." value='reader_cars'>Autos</OPTION>\n";
		echo "  <OPTION " . ((getsessionvar("personal_skin")=="reader_colors") ? "selected" : "") ." value='reader_colors'>Colors</OPTION>\n";
		echo "  <OPTION " . ((getsessionvar("personal_skin")=="reader_discreto") ? "selected" : "") ." value='reader_discreto'>Discreto</OPTION>\n";
		echo "  <OPTION " . ((getsessionvar("personal_skin")=="reader_girls") ? "selected" : "") ." value='reader_girls'>Para Chicas</OPTION>\n";
		echo "  <OPTION " . ((getsessionvar("personal_skin")=="reader_green") ? "selected" : "") ." value='reader_green'>Verde</OPTION>\n";
		echo "  <OPTION " . ((getsessionvar("personal_skin")=="reader_pink") ? "selected" : "") ." value='reader_pink'>Rosa</OPTION>\n";
		echo "  <OPTION " . ((getsessionvar("personal_skin")=="reader_purple") ? "selected" : "") ." value='reader_purple'>Púrpura</OPTION>\n";
		echo "  <OPTION " . ((getsessionvar("personal_skin")=="reader_spiderman") ? "selected" : "") ." value='reader_spiderman'>Spiderman</OPTION>\n";
		echo "  <OPTION " . ((getsessionvar("personal_skin")=="reader_yellow") ? "selected" : "") ." value='reader_yellow'>Yellow-Blue</OPTION>\n";
		echo "</SELECT><br>\n";
	}		
	
 ?>