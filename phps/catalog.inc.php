<?php

 function load_getpost_vars()
 {
    global $id_tipomaterial;
	
	global $id_tiporegistro;
	global $id_estadoregistro;
	global $id_nivelbibliografico;
	global $id_nivelcodificacion;
	global $id_formacatalogacion;

	// cargar variables
	$id_tipomaterial       = "";
	
	$id_tiporegistro       = "";
	$id_estadoregistro     = "";
	$id_nivelbibliografico = "";
	$id_nivelcodificacion  = "";
	$id_formacatalogacion  = "";

	if( isset( $_GET["id_tipomaterial"] ) )
		$id_tipomaterial = $_GET["id_tipomaterial"];
	else if( isset( $_POST["id_tipomaterial"] ) )
	    $id_tipomaterial = $_POST["id_tipomaterial"];	
	
	if( isset( $_GET["id_tiporegistro"] ) )
		$id_tiporegistro = $_GET["id_tiporegistro"];
	else if( isset( $_POST["id_tiporegistro"] ) )
	    $id_tiporegistro = $_POST["id_tiporegistro"];

	if( isset( $_GET["id_estadoregistro"] ) )
		$id_estadoregistro = $_GET["id_estadoregistro"];
	else if( isset( $_POST["id_estadoregistro"] ) )
	    $id_estadoregistro = $_POST["id_estadoregistro"];

	if( isset( $_GET["id_nivelbibliografico"] ) )
		$id_nivelbibliografico = $_GET["id_nivelbibliografico"];
	else if( isset( $_POST["id_nivelbibliografico"] ) )
	    $id_nivelbibliografico = $_POST["id_nivelbibliografico"];

	if( isset( $_GET["id_nivelcodificacion"] ) )
		$id_nivelcodificacion = $_GET["id_nivelcodificacion"];
	else if( isset( $_POST["id_nivelcodificacion"] ) )
	    $id_nivelcodificacion = $_POST["id_nivelcodificacion"];

	if( isset( $_GET["id_formacatalogacion"] ) )
		$id_formacatalogacion = $_GET["id_formacatalogacion"];
	else if( isset( $_POST["id_formacatalogacion"] ) )
	    $id_formacatalogacion = $_POST["id_formacatalogacion"];

 }
 
	//
	//
	function colocar_popup( $idpopup, $dbx, $cTituloDIV, $cCampo, $cCodigo, $input_control_name, $filtro_subcodigo = "", $colocar_icono_flecha=true )
	{
		if( eregi( "{", $cCampo ) )
		{
			// se colocará un link para abrir un TESAURO DE SISTEMA (TABLAS {MARC})
			// se debe traer el CODIGO_CORTO desde el tesauro
			$cCampo = str_replace( "{", "", $cCampo );
			$cCampo = str_replace( "}", "", $cCampo );
			
			echo "<a class='icon_link' style='position: relative; top: -3px; ' href='javascript:showPopupMenuTesauro( $cCampo, \"$input_control_name\", 0 )'><img src='../images/arrow_down.gif' title='Elegir $cTituloDIV'></a>";
		}
		else if( eregi( "<", $cCampo ) )
		{
			// se colocará un link para abrir un TESAURO DEFINIDO POR LA RED DE BIBLIOTECAS (TESAURO PROPIO)
			// se debe traer el TERMINO desde el tesauro
			$cCampo = str_replace( "<", "", $cCampo );
			$cCampo = str_replace( ">", "", $cCampo );
			
			echo "<a class='icon_link' style='position: relative; top: -3px; ' href='javascript:showPopupMenuTesauro( $cCampo, \"$input_control_name\", 1 )'><img src='../images/arrow_down.gif' title='Elegir $cTituloDIV'></a>";
		}
		else
		{
			echo "\n";
			
			if( $colocar_icono_flecha )
				echo "<a class='icon_link' href='javascript:showPopupMenu($idpopup)'><img src='../images/arrow_down.gif' title='Elegir $cTituloDIV'></a>";
			
			$popupname = "popupMenu" . $idpopup;
			
			echo "<div id='$popupname' name='$popupname' class='popup' onmouseover='mcancelclosetime();' onmouseout='mclosetime();' >\n";
			echo "<h2>$cTituloDIV</h2>";
			
			$dbx->sql = "SELECT SUBCODIGO, DESCRIPCION " . 
						"FROM marc_codigo21 " . 
						"WHERE ID_CAMPO='$cCampo' and CODIGO='$cCodigo' and NIVEL_MARC=3 and OBSOLETO = '' ";
						
			if( $filtro_subcodigo != "" ) 
			{ $dbx->sql .= " and SUBCODIGO LIKE '$filtro_subcodigo%' "; }
			
			$dbx->sql .= "ORDER BY SUBCODIGO";
						
			$dbx->Open();
			
			while( $dbx->NextRow() ) 
			{
				$subcodigo = $dbx->row["SUBCODIGO"];

				if( ($pos =strpos( $subcodigo, "?" )) != 0 )
					 $subcodigo = substr( $subcodigo, $pos+1, 10 );

				echo "<a href='javascript:setValue(\"$input_control_name\",\"$subcodigo\",\"" . $dbx->row["DESCRIPCION"] . "\")'>"  . 
					 " <strong>$subcodigo</strong> " . $dbx->row["DESCRIPCION"] . "</a>\n";
			}

			$dbx->FreeResultset();
			
			echo "</div>\n";
		}
	}

	//
	//
	// 
	function insertMARCFields( $dbx, $cFrom, $cTo, $masterDIV="" )
	{
		global $marc_record;
		
		$dbx->Open( "SELECT ID_CAMPO, SUBCODIGO, DESCRIPCION, OBSOLETO " . 
					"FROM marc_codigo21 " . 
					"WHERE ((ID_CAMPO >= '$cFrom') and (ID_CAMPO <= '$cTo')) and NIVEL_MARC=1 ".
					"ORDER BY ID_CAMPO" );
		
		while( $dbx->NextRow() ) 
		{
			$disabled = "";
			$style = "";
			
			if( isset($marc_record ) )
			{			
				if( $dbx->row["OBSOLETO"] == "S" or $marc_record->BuscarCampo($dbx->row["ID_CAMPO"]) != NULL )
				{
					$disabled = "disabled";
					$style = "color: gray;";
				}
			}
			else
			{
				if( $dbx->row["OBSOLETO"] == "S" )
				{
					$disabled = "disabled";
					$style = "color: gray;";
				}
			}
			
			$field_name = "chk_fld_" . $dbx->row["ID_CAMPO"];
			$lbl_name = "lbl_fld_" . $dbx->row["ID_CAMPO"];
			
			echo "<input $disabled type='checkbox' class='checkbox' title='' name='$field_name' id='$field_name'>&nbsp;" .
				 "<span name='$lbl_name' id='$lbl_name' style='$style'><strong>" . $dbx->row["ID_CAMPO"] . "</strong>&nbsp;&nbsp;" . $dbx->row["DESCRIPCION"] . "</span><br>\n";
		}
		
		$dbx->FreeResultset();
	}
	
	function get_positions_from( $valor_campo_008, $array_positions )
	{
		if( $valor_campo_008 == "" ) 
		{
			if( count($array_positions) == 1 )
				return "#";
			else if( count($array_positions) == 2)
			{
				$ret = "";
				for( $i=0; $i<($array_positions[1]-$array_positions[0])+1; $i++ )
					$ret .= "#";

				return $ret;
			}
		}
		
		if( count($array_positions) == 1 )
		{
			$ret = substr( $valor_campo_008, $array_positions[0], 1 );
		}
		else if( count($array_positions) == 2)
		{
			$ret = substr( $valor_campo_008, $array_positions[0], ($array_positions[1]-$array_positions[0])+1 );
		}
		
		$ret = str_replace( " ", "#", $ret ); 
		
		return $ret;
	}
	
	//
	// Inicia las posiciones fijas del campo 008
	// que son derivadas de condiciones dadas en otros campos (fundamentalmente el $$$)	
	//
	// para inicializar los valores específicos se usa $valor_campo_008
	//
	function inicializar_008_Specials( $dbx, $marcx, $valor_campo_008 )
	{
		// campo por default de donde se deriva la condición
		$objCampo008 = $marcx->BuscarCampo( "008" );
		
		$dbx->sql = "SELECT CODIGO, SUBCODIGO, DESCRIPCION, DESCRIPCION_ORIGINAL, TESAURO, URL " .
					"FROM marc_codigo21 " . 
					"WHERE (ID_CAMPO='008' and NIVEL_MARC=2) and (SUBCODIGO LIKE '%=%') and (OBSOLETO<>'S') " .
					"ORDER BY CODIGO ";
		//$dbx->DebugSQL();
		$dbx->Open();
		
		$fields_008_Specials = Array();
		
		while( $dbx->NextRow() ) 
		{
			//
			// se evaluarán las condiciones del Result-Set
			// verificar expresión
			//
			$array_exprs = split( "=", $dbx->row["SUBCODIGO"] );
			
			$bOk = false;
			
			if( count($array_exprs) > 1 ) 
			{
				$valores = $array_exprs[0];
				$at_expr = $array_exprs[1];
				
				$at_expr = str_replace( "[", "", $at_expr ); 
				$at_expr = str_replace( "]", "", $at_expr ); 
				
				$campo = "008";
				$pos_fija = "";
				
				if( $posicion = strpos( $at_expr, ":" ) )
				{
					$campo = substr( $at_expr, 0, $posicion );
					$pos_fija = substr( $at_expr, $posicion+1, 100 );
					
					$objCampo = $marcx->BuscarCampo( $campo );
				}
				else
				{
					$campo = "008";
					$pos_fija = $at_expr;
					
					$objCampo = $objCampo008;
				}
				
				$aValores = split( ",", $valores );
				
				if( $objCampo != NULL )
				{
					//echo $objCampo->cValor . "<br>";
					
					for( $i=0; $i<count($aValores) and !$bOk; $i++ )
					{
						if( $objCampo->ObtenerValorDePosicionFija( $pos_fija ) == $aValores[$i] )
						{ 
							$bOk = true;
						}
					}
				}

				unset( $aValores );

				if( $bOk )
				{
					$fields_008_Specials[] = Array( $dbx->row["CODIGO"], 
													$dbx->row["SUBCODIGO"],
													$dbx->row["DESCRIPCION"], 
													$dbx->row["DESCRIPCION_ORIGINAL"],
													$dbx->row["TESAURO"],
													$dbx->row["URL"] );
				}
			}
			else
				die( "ERROR" );
				
			unset( $array_exprs );
		}
		
		$dbx->FreeResultset();
		
		echo "\n<table width='100%' border='0'>";
		
		//
		// ya conocemos cuantos elementos son
		// ahora inicializarlos
		//
		$i = 0;
		
		$array_2d_fields = Array();
		
		while( $i<count($fields_008_Specials) )
		{
			echo "\n<tr>";

			if( getsessionvar("language") == 1 )
				$descrip = $fields_008_Specials[$i][2];
			else
				$descrip = $fields_008_Specials[$i][3];			
			
			// primera columna
			echo " <td class='columna' width='110px' align='right'>[" . $fields_008_Specials[$i][0] . "] $descrip</td>\n";
			echo " <td class='columna TDBorderLeft' width='60px	'>\n";

			$idmenu = (800+$i);
			$popup_name = "popupMenu" . $idmenu;
			
			$positions = str_replace( "..", "_TO_", $fields_008_Specials[$i][0] );		
			$init_value = get_positions_from( $valor_campo_008, split( "_TO_", $positions  ) );
			
			if( strpos($positions, "_TO_" ) )
			{	
				$max_len = strlen($init_value);
				$control_name = "txt008_$positions";
				echo "&nbsp;<input name='$control_name' id='$control_name' type='text' value='$init_value' size='2' maxlength='$max_len'>";
			}
			else
			{
				$control_name = "cmb008_$positions";
				echo "&nbsp;<input name='$control_name' id='$control_name' type='button' value='$init_value' style='width:25px'>\n";
			}
			
			$array_2d_fields[] = Array( $control_name, $fields_008_Specials[$i][0] );

			colocar_popup( $idmenu, $dbx, $descrip, "008", $fields_008_Specials[$i][0], $control_name, $fields_008_Specials[$i][1] );

			echo "</td>";
			
			// segunda columna
			$i++;

			if ( $i<count($fields_008_Specials) )
			{
			if( getsessionvar("language") == 1 )
				$descrip = $fields_008_Specials[$i][2];
			else
				$descrip = $fields_008_Specials[$i][3];			

				echo " <td class='columna' width='100px' align='right'>[" . $fields_008_Specials[$i][0] . "] $descrip</td>\n";
				echo " <td class='columna TDBorderLeft' width='60px'>\n";
				
				$idmenu = (800+$i);
				$popup_name = "popupMenu" . $idmenu;
				
				$positions = str_replace( "..", "_TO_", $fields_008_Specials[$i][0] );		
				$init_value = get_positions_from( $valor_campo_008, split( "_TO_", $positions  ) );

				if( strpos($positions, "_TO_" ) )
				{	
					$max_len = strlen($init_value);
					$control_name = "txt008_$positions";
					echo "&nbsp;<input name='$control_name' id='$control_name' type='text' value='$init_value' size='2' maxlength='$max_len'>\n";
				}
				else
				{
					$control_name = "cmb008_$positions";
					echo "&nbsp;<input name='$control_name' id='$control_name' type='button' value='$init_value' style='width:25px'>\n";
				}
				
				$array_2d_fields[] = Array( $control_name, $fields_008_Specials[$i][0] );
				
				colocar_popup( $idmenu, $dbx, $descrip, "008", $fields_008_Specials[$i][0], $control_name, $fields_008_Specials[$i][1] );
				
				echo " </td>";
			}

			echo "\n</tr>\n\n";

			$i++;
		}

		echo "</table>";
		
		return $array_2d_fields;
	}	 

	//
	// Tipo de dato será 
	//
	//  "P" - Portada
	//  "C" - ContraPortada
	//  ? - Otros
	//
	function upload_data_from_file( $id_biblioteca, $id_titulo, $tipo_dato, $file_name, $mimetype="" )
	{
		$field_name = "";
		$field_name_mimetype = "";
		
		$result = false;
		
		if( $tipo_dato == "P" )
		{
		   $field_name = "PORTADA";
		   $field_name_mimetype = "PORTADA_MIMETYPE";
		}
		else if( $tipo_dato == "C" ) 
		{
		   $field_name = "CONTRAPORTADA";
		   $field_name_mimetype = "CONTRAPORTADA_MIMETYPE";
		}	

	    global $CFG;
	    require_once( "../config_db.inc.php" );
		
		// archivo subido
		$fd = fopen( $file_name, "rb" );

		if ($fd) 
		{
			if( $CFG->db_type == "mysql" )
			{
				$isize = filesize( $file_name );
				
				$blob = "";
				$blob = addslashes(fread($fd, $isize));

				$bOk = strlen($blob) > 0;
				
				if( $bOk )
				{
					$query = "UPDATE acervo_titulos SET IMAGEN='$blob' WHERE ID_ESCUELA=$escuela and ID_EVALUACION=$id_evaluacion and ID_SECCION=$id_seccion";

					$bOk = db_query( $query );
					
					$result = true;
				}
			}
			elseif( $CFG->db_type == "interbase" ) 
			{
				// PHP v5 o superir
				$iblink_2 = ibase_pconnect( $CFG->db_host . ":" . $CFG->db_name, $CFG->db_user, $CFG->db_pass, "ISO8859_1" ) ;

				$blob = ibase_blob_import($iblink_2, $fd);
				
				$bOk = is_string($blob);

				if( $bOk )
				{
					$query = "UPDATE acervo_titulos SET $field_name_mimetype='$mimetype', $field_name=? WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo";

					$prepared = ibase_prepare($query);
					$bOk = ibase_execute($prepared, $blob);
					
					$result = true;
				}
			}
		}
		
		fclose($fd);
		
		return $result;
	}
 
 ?>