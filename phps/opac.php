<?php

	/*******
	  Historial de Cambios

	  19 mar 2009: Se crea el archivo PHP
	  22 mar 2009: Se perfecciona la interface y la consulta como tal
	  24 mar 2009: Se agrega la capacidad de mostrar portadas
	  11 abr 2009: Se agrega la capacidad para mostrar las copias o ejemplares de un título (en conjunto con marc.php)

	  10 sep 2009: Se agrega función muestra_consulta( )
				   Se agregan obtener_consulta_default_admva() y obtener_consulta_default_x_usuario()

	  10 ago 2013: se implementa UPPER(a.VALOR COLLATE PT_BR) para busquedas case INSENSITIVE

     */

	function muestra_consulta( $dbx, $id_biblioteca, $id_consulta=-1, $searchFor = "", $searchBy = "BY_KEYWORDS", $ctarget = "", $extra_params = "", $big_screen=0 )
	{
		include_language( "anls_consultatitulos" );

		require_once getsessionvar("local_base_dir") . "basic/bd.class.php";

		//$db = new DB();

		if( $id_consulta == -1 )
		{
			$dbx->sql = "SELECT * FROM cfgconsultas_catalogo " .
					   " WHERE ID_BIBLIOTECA=$id_biblioteca and OPAC='S' and ACTIVA='S'; ";
		}
		else
		{
			$dbx->sql = "SELECT * FROM cfgconsultas_catalogo " .
					   " WHERE ID_BIBLIOTECA=$id_biblioteca and ID_CONSULTA=$id_consulta and ACTIVA='S'; ";
		}

		$dbx->Open();

		$consulta_encontrada = 0;
		
		if( $dbx->NextRow() )
		{			
			global $LBL_KEYWORDS, $LBL_TITLE, $LBL_AUTHOR, $LBL_SUBJECTS, $LBL_ISBN, $LBL_ISSN, $LBL_CALLNUMBER;
			global $LBL_IDCONTROL, $LBL_SEARCH_FOR, $LBL_SEARCH_BY, $BTN_SEARCH;
			global $LBL_QUICK_SEARCH, $LBL_CONSULT_HEADER;

			$consulta_encontrada = 1;
			
			// GRAN DIV
			echo "<!-- consulta $id_consulta -->";
			echo "<div class='caja_datos' id='consulta' name='consulta' style='overflow:auto; text-align:left !important;'>";  // pendiente cerrar
			echo "<h4>" . $dbx->row["DESCRIPCION"] . "</h4>";			
			
			$width_for_div = "100%";
			
			if( issetsessionvar("id_usuario") )
			{
				$width_for_div = "65%";
			}
			
			echo "<div id='form_search' style='display:block; float:left; height: 80px; width: $width_for_div; overflow: auto;' class='forma_captura'>";

			echo "<br>";
			
			$path = "";
			
			if( strpos($_SERVER["PHP_SELF"], "phps/" ) )
				$path = "";
			else
				$path = "phps/";
				
			if( $ctarget == "" )
				$path .= "anls_consultatitulos.php";
			else
				$path .= $ctarget;
			
				echo "<!-- BY -->\n\n";
				echo "<div style='float:left; width: 95px; text-align:right; top:3px; position: relative;'>$LBL_SEARCH_BY&nbsp;&nbsp;</div>";

				echo "<select name='search_By' id='search_By' style='display:inline;'>";
				
					if( $searchBy == "" )
					{
						$searchBy = getsessionvar( "opac_last_searchBy");
					}
				
					// 07jun2011
					// Control Interno para todo tipo de búsquedas
					if( $dbx->row["ADMINISTRATIVA"] == "S" )
					{
						echo "<option value='BY_ID' " . (($searchBy=="BY_ID") ? "selected" : "") . ">$LBL_IDCONTROL&nbsp;&nbsp;</option>";
					}
				
					if( $dbx->row["INCLUIR_PALABRASCLAVE"] == "S" ) 
					{
						echo "<option value='BY_KEYWORDS' " . (($searchBy=="BY_KEYWORDS") ? "selected" : "") . ">$LBL_KEYWORDS&nbsp;&nbsp;</option>";
					}				
					
					if( $dbx->row["INCLUIR_TITULO"] == "S" ) 
					{
						echo "<option value='BY_TITLE' " . (($searchBy=="BY_TITLE") ? "selected" : "") . " >$LBL_TITLE&nbsp;&nbsp;</option>";
					}
					
					if( $dbx->row["INCLUIR_AUTOR"] == "S" ) 
					{
						echo "<option value='BY_AUTHOR' " . (($searchBy=="BY_AUTHOR") ? "selected" : "") . " >$LBL_AUTHOR&nbsp;&nbsp;</option>";
					}
					
					if( $dbx->row["INCLUIR_MATERIAS"] == "S" ) 
					{
						echo "<option value='BY_SUBJECT' " . (($searchBy=="BY_SUBJECT") ? "selected" : "") . " >$LBL_SUBJECTS&nbsp;&nbsp;</option>";
					}
					
					if( $dbx->row["INCLUIR_SIGNATURA_TOPOG"] == "S" ) 
					{
						// C - Call Number
						echo "<option value='BY_CALLNUM' " . (($searchBy=="BY_CALLNUM") ? "selected" : "") . " >$LBL_CALLNUMBER&nbsp;&nbsp;</option>";
					}				
					
					if( $dbx->row["INCLUIR_ISBN"] == "S" ) 
					{
						echo "<option value='BY_ISBN' " . (($rad_searchBy=="BY_ISBN") ? "selected" : "") . " >$LBL_ISBN&nbsp;&nbsp;</option>";
					}				
					
					if( $dbx->row["INCLUIR_ISSN"] == "S" ) 
					{
						echo "<option value='BY_ISSN' " . (($rad_searchBy=="BY_ISSN") ? "selected" : "") . " >$LBL_ISSN&nbsp;&nbsp;</option>";
					}							
				
				echo "</select><br>";
			
			
				echo "<!-- SEARCH TERM -->\n\n";
				echo "<div style='float:left; width: 95px; text-align:right; top:3px; position: relative;'>$LBL_SEARCH_FOR&nbsp;&nbsp;</div>";
				
				if( $big_screen==1 )
					$search_term_size = 60;
				else
					$search_term_size = 33;
				
				echo "<input style='display:inline;' type='text' name='search_For' id='search_For' value='$searchFor' size='$search_term_size' onkeypress='return submit_search(this,event,$id_biblioteca,$id_consulta,\"$path\",\"$extra_params\");'>&nbsp;&nbsp;";

				echo "&nbsp;&nbsp;<input class='boton' type='button' value='$BTN_SEARCH' name='btnSearch' id='btnSearch' onClick='javascript:search_catalog($id_biblioteca,$id_consulta,\"$path\",\"$extra_params\");'>";
				
				// para evaluar en runtime y hacer la búsqueda
				echo "<input name='hintEvalForSearch' id='hintEvalForSearch' type=hidden class=hidden value='search_catalog($id_biblioteca,$id_consulta,\"$path\",\"$extra_params\")'>";

				echo "</div>";  // form_search
		}
		
		$dbx->Close();
		
		if( $consulta_encontrada == 1 )
		{
			//
			//  HISTORIAL
			//
			if( issetsessionvar("id_usuario") )
			{
				global $LBL_LATEST_SEARCHES;
				
				echo "<div id='lista_historial' style='position:relative; top: -10px; display:block; float:right; height: 95px; width: 32%; overflow: auto; border: 1px dotted gray; padding: 5px;'>";
				echo "<h4>$LBL_LATEST_SEARCHES</h4><br>";
				
				$sql = "SELECT CAUX1, OBSERVACIONES  FROM usuarios_bitacora_eventos ".
					   "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_USUARIO=" . getsessionvar( "id_usuario" ) . " and ID_ACCION=210 " .
					   "ORDER BY FECHA DESC, CAUX1, OBSERVACIONES";
				
				$dbx->Open( $sql );
				
				$info = Array();
				
				while( $dbx->NextRow() )
				{
					if( !in_array( $dbx->row["OBSERVACIONES"], $info ) )
					{				
						$link = "javascript:quick_search(\"" . $dbx->row["OBSERVACIONES"]. "\", \"" . $dbx->row["CAUX1"] . "\" )'";
						echo  "<div class='mini_bullet'></div><a href='$link'>". $dbx->row["OBSERVACIONES"] . "</a><br>";
						
						$info[] = $dbx->row["OBSERVACIONES"];
						
						if( count($info)==20 )	
							$dbx->Close();
					}
				}
				
				$dbx->Close();
				
				echo "</div>";
			}
			
			// CIERRA GRAN DIV
			echo "</div>";  // caja datos
		}
		
		echo "<br style='clear:both;'>";
		
	}
	
	function inserta_resultados_consulta( $dbx, $id_biblioteca, $id_consulta, $rad_searchBy, $txt_search, $link_for_titles, $action_button_for_selection, $mostrar_ejemplares=-1, $permitir_elegir_items_prestados=0 )
	{
		global $LBL_TITLE, $LBL_IMAGES, $LBL_PUBLISHING;
				
		require_once getsessionvar("local_base_dir") . "basic/bd.class.php";

		//$db = new DB();

		$dbx->Open( "SELECT MOSTRAR_ITEMS_EXISTENTES, MOSTRAR_ARCHIVOS_ANEXOS, PERMITIR_DESCARGAR_ANEXOS, PERMITIR_CONSULTAR_TITULOS, OPAC " . 
				   "FROM cfgconsultas_catalogo " .	
				   " WHERE ID_BIBLIOTECA=$id_biblioteca and ID_CONSULTA=$id_consulta and ACTIVA='S' ");
				   
		$mostrar_digital_resource = 0;
		$mostrar_link_digital_resource = 0;
		
		$aInfo = Array();

		if( $dbx->NextRow() )
		{		
			$aInfo = Array( "OPAC"=> $dbx->row["OPAC"], "RECORDS"=>0  );
			
			if( $mostrar_ejemplares == -1 )  // inicializar según lo configurado en la consulta
				$mostrar_ejemplares = $dbx->row["MOSTRAR_ITEMS_EXISTENTES"] == "S" ? 1 : 0;

			$mostrar_digital_resource = $dbx->row["MOSTRAR_ARCHIVOS_ANEXOS"] == "S" ? 1 : 0;
			$mostrar_link_digital_resource = $dbx->row["PERMITIR_DESCARGAR_ANEXOS"] == "S" ? 1 : 0;
			$link_for_titles = $dbx->row["PERMITIR_CONSULTAR_TITULOS"] == "S" ? 1 : 0;
		}
		
		$dbx->Close();

		// conversiones
		$txt_search_lcase = strtolower( $txt_search );  // todo en minusculas
		$txt_search_fcase = strtoupper( substr($txt_search_lcase,0,1) ) . substr($txt_search_lcase, 1, 256 );  // primera letra mayúscula
		$txt_search_ucase = strtoupper( $txt_search );  // todo en mayúsculas
		
		require_once "marc.php";
		
		$dbx->sql  = "SELECT a.ID_TITULO, b.ID_TIPOMATERIAL, b.ID_SERIE, b.STATUS, b.FECHA_REGISTRO, b.PORTADA, b.CONTRAPORTADA, c.CODIGO_MARC, c.ICONO ";
		$dbx->sql .= "FROM acervo_catalogacion a " . 
					" LEFT JOIN acervo_titulos b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_TITULO=a.ID_TITULO) " .
					"  LEFT JOIN marc_material c ON (c.ID_TIPOMATERIAL=b.ID_TIPOMATERIAL) ";

		$campo = "";
		
		if( $rad_searchBy == "BY_KEYWORDS" )
		{
			// POR PALABRAS CLAVE, 245 $a o $c
			//UPPER(NOMBREASIGNATURA COLLATE PT_BR)
			$dbx->sql .= 'WHERE a.ID_BIBLIOTECA=' . $id_biblioteca . ' and (a.ID_CAMPO=\'245\' and (a.CODIGO=\'$a\' or a.CODIGO=\'$b\' or a.CODIGO=\'$c\')) 	';
			$dbx->sql .= '  and (a.VALOR LIKE \'%' . $txt_search . '%\' or UPPER(a.VALOR COLLATE PT_BR) LIKE \'%' . $txt_search_lcase . '%\' or a.VALOR LIKE \'%' . $txt_search_fcase . '%\' or a.VALOR LIKE \'%' . $txt_search_ucase . '%\' ) ';
		}
		else if( $rad_searchBy == "BY_TITLE" )
		{
			// POR TITULO CAMPO 245, subcampo $a
			$dbx->sql .= 'WHERE a.ID_BIBLIOTECA=' . $id_biblioteca . ' and a.ID_CAMPO=\'245\' and (a.CODIGO=\'$a\' or a.CODIGO=\'$b\') ';
			$dbx->sql .= '  and (a.VALOR LIKE \'%' . $txt_search . '%\' or UPPER(a.VALOR COLLATE PT_BR) LIKE \'%' . $txt_search_lcase . '%\' or a.VALOR LIKE \'%' . $txt_search_fcase . '%\' or a.VALOR LIKE \'%' . $txt_search_ucase . '%\' ) ';
		}
		else if( $rad_searchBy == "BY_AUTHOR" )
		{
			// POR AUTOR CAMPO 245 subcampo $c - MENCION DE RESPONSABILIDAD
			//           CAMPO 100 subcampo $a - NOMBRE PERSONAL 
			//           CAMPO 700 subcampo $a - ASIENTO SECUNDARIO - NOMBRE PERSONAL 
			$dbx->sql .= 'WHERE a.ID_BIBLIOTECA=' . $id_biblioteca . ' and ((a.ID_CAMPO=\'245\' and a.CODIGO=\'$c\') or (a.ID_CAMPO=\'100\' and a.CODIGO=\'$a\') or (a.ID_CAMPO=\'700\' and a.CODIGO=\'$a\')) ';
			$dbx->sql .= '  and (a.VALOR LIKE \'%' . $txt_search . '%\' or UPPER(a.VALOR COLLATE PT_BR) LIKE \'%' . $txt_search_lcase . '%\' or a.VALOR LIKE \'%' . $txt_search_fcase . '%\' or a.VALOR LIKE \'%' . $txt_search_ucase . '%\' ) ';
		}
		else if( $rad_searchBy == "BY_SUBJECT" )  // Materia
		{
			// POR MATERIA CAMPO 650, subcampos $a, $b, $c - TERMINO TEMATICO
			//   
			// 652?? y 653??
			//
			$dbx->sql .= 'WHERE a.ID_BIBLIOTECA=' . $id_biblioteca . ' and ((a.ID_CAMPO=\'650\' and a.CODIGO=\'$a\') or (a.ID_CAMPO=\'650\' and a.CODIGO=\'$b\') or (a.ID_CAMPO=\'650\' and a.CODIGO=\'$c\')) ';
			$dbx->sql .= '  and (a.VALOR LIKE \'%' . $txt_search . '%\' or UPPER(a.VALOR COLLATE PT_BR) LIKE \'%' . $txt_search_lcase . '%\' or a.VALOR LIKE \'%' . $txt_search_fcase . '%\' or a.VALOR LIKE \'%' . $txt_search_ucase . '%\' ) ';
		}
		else if( $rad_searchBy == "BY_CALLNUM" )
		{
			// POR ISBN CAMPO 020, subcampo $a
			$dbx->sql .= 'WHERE a.ID_BIBLIOTECA=' . $id_biblioteca . ' and a.ID_CAMPO=\'082\' and a.CODIGO=\'$a\' ';
			$dbx->sql .= "  and (a.VALOR LIKE '" . $txt_search . "%') ";
		}
		else if( $rad_searchBy == "BY_ISBN" )
		{
			// POR ISBN CAMPO 020, subcampo $a
			$dbx->sql .= 'WHERE a.ID_BIBLIOTECA=' . $id_biblioteca . ' and a.ID_CAMPO=\'020\' and a.CODIGO=\'$a\' ';
			$dbx->sql .= "  and (a.VALOR LIKE '" . $txt_search . "%') ";
		}
		else if( $rad_searchBy == "BY_ISSN" )
		{
			// PENDIENTE
			// POR ISSN CAMPO 020, subcampo $a
			$dbx->sql .= 'WHERE a.ID_BIBLIOTECA=' . $id_biblioteca . ' and a.ID_CAMPO=\'020\' and a.CODIGO=\'$a\' ';
			$dbx->sql .= "  and (a.VALOR LIKE '" . $txt_search . "%') ";
		}		
		else if( $rad_searchBy == "BY_ID" )
		{
			// POR NUMERO DE CONTROL ... averiguar el campo MARC
			$dbx->sql .= "WHERE a.ID_BIBLIOTECA=$id_biblioteca and b.ID_TITULO=$txt_search ";
		}		
		else
		{
			echo( "CONSULTA INCORRECTA" );
		}
		
		$dbx->sql .= "ORDER BY a.ID_TITULO";
		
		setsessionvar( "opac_last_searchBy", $rad_searchBy );
		
		$dbx->Open();
		
		echo "\n\n<!-- CONSULTA -->\n";
		
		echo "<table border=0 width='100%' style='margin-top:5px;'>";
				
		$marc_record = new record_MARC21( $id_biblioteca, $dbx );
		
		$marc_record->ImprimeEncabezado(1);
		
		$last_IDTITULO = -1;
		
		while( $dbx->NextRow() )
		{			
			if( $dbx->row["ID_TITULO"] != $last_IDTITULO )
			{
				if( $last_IDTITULO == -1 ) 				
				{
					echo "<tr>";
					echo " <td class='cuadricula columna columnaEncabezado'>ID</td>";
					echo " <td class='cuadricula columna columnaEncabezado'>$LBL_TITLE</td>";
					echo " <td class='cuadricula columna columnaEncabezado'>$LBL_IMAGES</td>";
					echo " <td class='cuadricula columna columnaEncabezado'>$LBL_PUBLISHING</td>";
					echo "</tr>";
				}

				$last_IDTITULO = $dbx->row["ID_TITULO"];
				
				echo "\n\n<!-- TITULO -->";
				echo "<tr>";
				
				$marc_record->Icono( $dbx->row["ICONO"] );
				$marc_record->InicializarRegistroMARC21_DesdeBD_Titulo( $dbx->row["ID_TITULO"] );

				$keywords_4_hilite = "";

				if( strlen($txt_search) > 4 ) $keywords_4_hilite = $txt_search . " " . $txt_search_lcase . " " . $txt_search_fcase . " " . $txt_search_ucase;

				$marc_record->ImprimeFicha( 1, $keywords_4_hilite, $dbx->row["PORTADA"], $dbx->row["CONTRAPORTADA"], $link_for_titles, 
												"id_consulta=$id_consulta&search=" .$txt_search . "&", $action_button_for_selection, $mostrar_ejemplares, $permitir_elegir_items_prestados,  $mostrar_digital_resource, $mostrar_link_digital_resource );

				$marc_record->clear_record();
				
				echo "</tr>";
				
				// separador
				echo "<tr><td colspan=4><img src='..\images\pxl.gif' height=3></td></tr>";
			}
		}
		
		$marc_record->destroy();

		echo "\n\n";
		
		echo "</table>";
		
		if ( $dbx->numRows == 0 ) 
		{
			global $MSG_NO_RECORDS_FOUND;
			
			//echo "<tr><td colspan=4>";
			echo "<br>";
			echo "<div class=caja_errores>";
			echo " <center><strong><img src='../images/icons/warning.gif'>&nbsp;$MSG_NO_RECORDS_FOUND</strong></center>";
			echo "</div>";				 
			//echo "</td></tr>";
		}	
		
		//$dbx->destroy();
		if( count($aInfo) == 1 )
			$aInfo[0]["RECORDS"] = $dbx->numRows;
		
		echo "\n<!-- FIN CONSULTA -->\n";
		
		return $aInfo;

	}
	
	
	function obtener_consulta_default_admva( $dbx, $id_biblioteca )
	{
		if( strpos($_SERVER["PHP_SELF"], "phps/" ) )
			$path = "../";
		else
			$path = "";

		require_once $path . "basic/bd.class.php";
		
		$dbx->Open( "SELECT ID_CONSULTA FROM cfgconsultas_catalogo " .	
					" WHERE ID_BIBLIOTECA=$id_biblioteca and ACTIVA='S' and ADMINISTRATIVA='S' " . 
					"ORDER BY OPAC ASC, ID_CONSULTA " );
		$ret = 0;
					  
		if( $dbx->NextRow() )
		{
			$ret = $dbx->row["ID_CONSULTA"];
		}
		
		$dbx->Close();		
				
		return $ret;
	}	
	
	//
	// 25mar2011: Se verifica restricción de consulta
	//
	function obtener_consulta_default_x_usuario( $dbx, $id_biblioteca )
	{
		
		global $user;
		
		if( isset($user) )
		{
			if( $user->EXIST_RESTRICTION_CONSULTA )
				return 0;
		}
		
		
		if( strpos($_SERVER["PHP_SELF"], "phps/" ) )
			$path = "../";
		else
			$path = "";

		require_once $path . "basic/bd.class.php";
		
		//$db = new DB();
		
		$dbx->sql = "SELECT ID_CONSULTA FROM cfgconsultas_catalogo " .	
				   " WHERE ID_BIBLIOTECA=$id_biblioteca and ACTIVA='S' ";
				   
		if( getsessionvar("empleado") == "S" )
			$dbx->sql .= " and ADMINISTRATIVA='S' ";

		$dbx->sql .= "ORDER BY OPAC ASC, ID_CONSULTA ";
		
		//$dbx->DebugSQL();
		$dbx->Open();
		
		$ret = 0;
					  
		if( $dbx->NextRow() )
		{
			$ret = $dbx->row["ID_CONSULTA"];
		}
		
		$dbx->Close();		
				
		return $ret;
	}
	
	// 26-mar-2009
	// 19-oct-2009: Se mueve de funcs.inc.php
	function hightlight($str, $keywords = '')   
	{   
		if( $keywords == "" ) return $str;
		
		$keywords = preg_replace('/\s\s+/', ' ', strip_tags(trim($keywords))); // filter   

		$style = 'highlight';   
		$style_i = 'highlight_important';   
	  
		/* Apply Style */  
	  
		$var = '';   
	  
		foreach(explode(' ', $keywords) as $keyword)   
		{   
			$replacement = "<xxyyzzxxyyyzz>" . $keyword . "</xxyyzzxxyyyzz>";
			$var .= $replacement." ";   
	  
			$str = str_replace($keyword, $replacement, $str);   
		} 
		
		
		
		//$replacement = "<span class='".$style."'>".$keyword."</span>";
	  
		/* Apply Important Style */  
		$str = str_replace( "<xxyyzzxxyyyzz>", "<span class='$style'>", $str);   
		$str = str_replace( "</xxyyzzxxyyyzz>", "</span>", $str);   
	  
		return $str;   
	}  	

 ?>