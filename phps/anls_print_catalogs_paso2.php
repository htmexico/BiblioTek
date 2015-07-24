<?php
	session_start();

	/*	  
	  - Impresion de Catálogos
	  
	  - 12-nov-2009. 	Se inicia.
	  - 13 nov 2009.    Se adapta y se crea un modelo de clase interesante para reportear.
	  
	*/
		
	include "../funcs.inc.php";
	include "../actions.inc.php";
	include "../basic/bd.class.php";
	include "../basic/fpdf/fpdf.php";
	include "circulacion.inc.php";
	
	require_once( "marc.php" );
	
	include_language( "global_menus" );

	check_usuario_firmado();	
	
	include_language( "anls_print_catalogs" ); // archivo de idioma	
	include_language( "gral_vertitulo" ); // archivo de idioma	
	
	$id_biblioteca = getsessionvar("id_biblioteca");
	$id_usuario    = getsessionvar("id_usuario");
	
	$the_action = read_param( "action", "" );
	$id_tipomaterial = read_param( "id_tipomaterial", "", 1 );
	$cat_type = read_param( "cat_type", "author" );
	$output   = read_param( "output", 0 );
	$cols     = read_param( "cols", 1 );
	$contenido = read_param( "contenido", 1 );

	if( $cols == 0 )
		$en_tablas = 1;
	else
		$en_tablas = 0;

	$db = new DB();
	
	// 
	// usar el SESSION_TYPE = 2 ... (catalogos)
	$CUR_SESSION_TYPE = 2;
	
	//
	$db->ExecSQL( "DELETE FROM tmp_sessions_data " .
				  "WHERE ID_BIBLIOTECA=$id_biblioteca and SESSION_ID='" . session_id() . "' and SESSION_TYPE=$CUR_SESSION_TYPE and ID_USUARIO=$id_usuario; " );
	
	if ( $the_action == "go" ) 
	{		
		$db->Open( "SELECT DISTINCT b.ID_TITULO " .
				   " FROM acervo_copias a " .
				   "   INNER JOIN acervo_titulos b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_TITULO=a.ID_TITULO) " .
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and b.ID_TIPOMATERIAL='$id_tipomaterial'; " );
				   
		$array_catalog = Array();
				   
		while( $db->NextRow() )
		{
			$id_titulo = $db->row["ID_TITULO"];
			
			$item = new TItem_Basic( $id_biblioteca, $id_titulo, 0, $db );  // ID_TITULO
			
			$array_catalog[] = Array( "id_titulo" => $id_titulo, "id_item" => 0, "title"=> $item->cTitle, "author" => $item->cAutor );
		}
				   
		$db->Close();
		
		//
		// insertar tmp data
		//
		for( $xyz=0; $xyz<count($array_catalog); $xyz++ )
		{
			$id_titulo = $array_catalog[ $xyz ]["id_titulo"]; 

			$str_val = "";
			
			if( $cat_type == "author" )
				$str_val = $array_catalog[ $xyz ]["author"]; 
			if( $cat_type == "title" )
				$str_val = $array_catalog[ $xyz ]["title"];
				
			if( $str_val != "" )
			{
				if( strlen($str_val) > 250 )
					$str_val = substr( $str_val, 0, 250 );

			//	$db->ExecSQL( "INSERT INTO tmp_sessions_data (ID_BIBLIOTECA, SESSION_ID, SESSION_TYPE, ID_USUARIO, DATA, DATA_CAUX1, DATA_AUX1 ) " .
			//				" VALUES ($id_biblioteca, '" . session_id() . "', $CUR_SESSION_TYPE, $id_usuario, '$id_titulo', '$str_val', " . $array_catalog[ $xyz ]["id_titulo"] . " ) " );
			
				$db->ExecCommand( "INSERT INTO tmp_sessions_data (ID_BIBLIOTECA, SESSION_ID, SESSION_TYPE, ID_USUARIO, DATA, DATA_CAUX1, DATA_AUX1 ) " .
						" VALUES ($id_biblioteca, '" . session_id() . "', $CUR_SESSION_TYPE, $id_usuario, '$id_titulo', ?, " . $array_catalog[ $xyz ]["id_titulo"] . " ) ", 
							1, $str_val ); 
			
			}
		}
		
		// verificar los campos que se incluirán
		$incluir_num_control = 0;
		$incluir_autor      = 0;
		$incluir_titulo     = 0;
		$incluir_datos_edicion = 0;
		$incluir_descrip_fisica = 0;
		$incluir_temas = 0;
		$incluir_serie = 0;
			
		if( strpos( $contenido, '1' ) ) $incluir_num_control = 1;
		if( strpos( $contenido, '2' ) ) $incluir_autor = 1;			
		if( strpos( $contenido, '3' ) ) $incluir_titulo = 1;						
		if( strpos( $contenido, '4' ) ) $incluir_datos_edicion = 1;
		if( strpos( $contenido, '5' ) ) $incluir_descrip_fisica = 1;
		if( strpos( $contenido, '6' ) ) $incluir_temas = 1;
		if( strpos( $contenido, '7' ) ) $incluir_serie = 1;			

		if( $cat_type == "author" ) 
			$str_order_by = "$LBL_PRINT_CATALOG_SUBTITLE_ORDER_BY_AUTHOR";
		else if( $cat_type == "title" ) 
			$str_order_by = "$LBL_PRINT_CATALOG_SUBTITLE_ORDER_BY_TITLE";				

		// ARMAR CONTENIDO
		$aArrayCols = Array();

		if( $incluir_num_control == 1 )
		{
			$aArrayCols[] = Array( "caption" => "$LBL_CARD_NO_CONTROL", "field" => "001", "width" => 80 );
		}
		if( $incluir_autor == 1 )
		{
			$aArrayCols[] = Array( "caption" => "$LBL_CARD_AUTHOR", "field" => "100", "width" => 200 );
		}
		if( $incluir_titulo == 1 )
		{
			$aArrayCols[] = Array( "caption" => "$LBL_CARD_TITLE", "field" => '245:$a', "width" => 290 );
		}
		if( $incluir_datos_edicion == 1 )
		{
			$aArrayCols[] = Array( "caption" => "$LBL_CARD_EDITION", "field" => "260", "width" => 150 );
		}
		if( $incluir_descrip_fisica == 1 )
		{
			$aArrayCols[] = Array( "caption" => "$LBL_CARD_DESCRIPTION", "field" => "300", "width" => 120 );
		}
		if( $incluir_temas == 1 )
		{
			$aArrayCols[] = Array( "caption" => "$LBL_CARD_THEMES", "field" => "650@651@700@710", "width" => 250 );
		}
		if( $incluir_serie == 1 )
		{
			$aArrayCols[] = Array( "caption" => "$LBL_CARD_SERIES", "field" => "440&490", "width" => 100 );
		}		
			
		// Finalmente, abrir el dataset desde el TMP
		$db->Open( "SELECT DATA AS ID_TITULO, DATA_CAUX1 AS VALOR " .
				   " FROM tmp_sessions_data " .
				   "WHERE ID_BIBLIOTECA=$id_biblioteca and SESSION_ID='" . session_id() . "' and SESSION_TYPE=$CUR_SESSION_TYPE " . 
				   "ORDER BY DATA_CAUX1; " );
		
		if( $output == "pdf" )
		{
			//
			//  P D F 
			//
			class CATALOG_PDF extends FPDF
			{
				var $row_title;
				var $row_header;
				var $row_start_info;
				
				var $init_col_at_left;
				
				function NewPage( $title, $subtitle, $bPrint_col_captions=0, $aInfoCols=null )
				{
					$this->AddPage();
					$this->SetFont( "Arial", "B", 15 );
					
					$this->SetXY( $this->init_col_at_left, $this->row_title );
					$this->Cell( 200, 2, $title, 0, 0, "L" );
					$this->Ln(5);
					
					$this->SetX( $this->init_col_at_left );
					$this->SetFont( "Arial", "B", 12 );
					$this->Cell( 200, 2, $subtitle, 0, 0, "L" );
					
					$this->Line( $this->init_col_at_left , $this->GetY()+4, 210, $this->GetY()+4 );

					$this->SetFont( "Arial", "B", 7 );
					
					if( $bPrint_col_captions == 1 )
					{
						$offset = $this->init_col_at_left; // solo para PDF
						
						for( $i=0; $i<count($aInfoCols); $i++ )
						{
							$this->SetXY( $offset, $this->row_header );
							$this->MultiCell( ($aInfoCols[$i]["width"] / 7 ), 3, html_entity_decode($aInfoCols[$i]["caption"]), 0, "L", 0 );				
							$offset += ($aInfoCols[$i]["width"] / 7 ) + 5 ;
						}
					}	
					
					$this->Line( $this->init_col_at_left , $this->GetY()+4, 210, $this->GetY()+4 );

					$this->SetXY( 176, $this->row_title );
					$this->SetFont( "Arial", "", 6 );
					$this->Cell( 34, 2, "BiblioTEK Library Automation Software", 0, 0, "R" );
					
					$this->SetY( $this->row_start_info );
					$this->SetFont( "Arial", "", 7 );
				}
				
				function RecordMARC( $en_tablas, $cols, $aInfoCols, $marc_record, $this_col, $width_per_col )
				{
					$offset = $this->init_col_at_left;

					$max_rows = 0;
				
					$pdf_y = $this->GetY();
					
					if( $en_tablas == 0 ) 
					{
						$offset += (($this_col-1) * $width_per_col);
					}
					else
					{
						$this->SetX( $offset );
					}

					for( $i=0; $i<count($aInfoCols); $i++ )
					{
						$val = $marc_record->PreProcessor( $aInfoCols[$i]["field"], $temas );
						
						if( $en_tablas == 1 )
						{
							$this->SetXY( $offset, $pdf_y );
							$rows = $this->MultiCell( ($aInfoCols[$i]["width"] / 7), 2.5, html_entity_decode($val), 0, 'L', 0 );
							
							if( $rows > $max_rows )
								$max_rows = $rows;
								
							$offset += ($aInfoCols[$i]["width"] / 7 ) + 5 ;
						}
						else
						{
							$this->SetX( $offset );
							if( $aInfoCols[$i]["field"] == "001" )
								$this->SetFont( "Arial", "B", 7 );

							$rows = $this->MultiCell( $width_per_col, 2.5, html_entity_decode($val), 0, 'L', 0 );
							
							if( $aInfoCols[$i]["field"] == "001" )
								$this->SetFont( "Arial", "", 7 );
						}
					}
					
					if( $en_tablas == 1 )
						$this->Sety( $pdf_y );
					
					$this->Ln( $max_rows*2.5 );
					$this->Ln(4);
					
					return $max_rows;
				
				}
				
				//Pie de página
				function Footer()
				{
					//Posición: a 1,5 cm del final
					$this->SetY(-15);
					//Arial italic 8
					$this->SetFont('Arial','I',7);
					//Número de página
					$this->Cell(0,10,'Página ' . $this->PageNo() . '/{nb}', 0, 0, 'C' );
				}
				
			}	
		
			$pdf=new CATALOG_PDF( 'P', 'mm', 'letter' );
			$pdf->row_title = 5;
			$pdf->row_header = 16;
			
			if( $en_tablas == 1 )
				$pdf->row_start_info = 24;
			else
				$pdf->row_start_info = 18;
			
			$pdf->init_col_at_left = 5;
			
			$pdf->AliasNbPages();
			$pdf->SetAuthor( "BiblioTEK / Escolar HI-TECH" );
			
			$pdf->SetAutoPageBreak(false);
			
			$i = 0;
			
			$marc_record = new record_MARC21( $id_biblioteca, $db );
			
			$pdf->NewPage( "$LBL_PRINT_CATALOG_TITLE", $str_order_by, $en_tablas, $aArrayCols );

			$last_val_4_order = "";
			$this_col = 0;
			
			if( $cols==1 or $cols==0)  $div_100 = 210;
			else			$div_100 = 210 / $cols;
			
			$div_100 -= 5;
			
			while( $db->NextRow() )	
			{			
				$this_col++;
			
				$marc_record->InicializarRegistroMARC21_DesdeBD_Titulo( $db->row["ID_TITULO"] );
				
				if( $last_val_4_order != $db->row["VALOR"] )
				{
					if( $en_tablas == 1 )
					{
						$pdf->SetX( 5 );
						$pdf->SetFont( "Arial", "B", 12 );
						$pdf->MultiCell( 200, 4, html_entity_decode($db->row["VALOR"]), 0, 'L', 0 );
					}
					else
					{
						if ($pdf->GetY() >= 250 ) 
						{
							$pdf->NewPage( "$LBL_PRINT_CATALOG_TITLE", $str_order_by, $en_tablas, $aArrayCols );
						}				

						// en columnas estilo libro CERRAR la columnas pendientes y el renglón
						// por columnas (estilo libro)
						$pdf->SetX( 5 );
						$pdf->SetFont( "Arial", "B", 12 );
						$pdf->MultiCell( $div_100, 4, html_entity_decode($db->row["VALOR"]), 0, 'L', 0 );
					}
					
					$pdf->SetFont( "Arial", "", 7 );
					$pdf->Ln(2);					
					
					$save_pdf_y = $pdf->GetY();
					
					$last_val_4_order = $db->row["VALOR"];
					
					$this_col = 1;  // reset 
				}
				else
				{
					// mismo grupo
					$pdf->Ln( 5 );
					
					if( $en_tablas == 0 )
						$pdf->SetY( $save_pdf_y );
				}

				$pdf->SetDrawColor( 0, 0, 0 );
				
				// print to a PDF canvas
				$max_rows = $pdf->RecordMARC( $en_tablas, $cols, $aArrayCols, $marc_record, $this_col, $div_100 );
				
				$marc_record->clear_record();

				if( $en_tablas == 1 )
				{
					if ($pdf->GetY() >= 250 ) 
					{
						$pdf->NewPage( "$LBL_PRINT_CATALOG_TITLE", $str_order_by, $en_tablas, $aArrayCols );
					}						
				}
				else
				{
					// nueva columna
					if( $this_col == $cols )
					{
						// última columna (REINICIAR)
						$save_pdf_y = $pdf->GetY();
						$this_col = 0;
						
						if ($pdf->GetY() >= 250 ) 
						{
							$pdf->NewPage( "$LBL_PRINT_CATALOG_TITLE", $str_order_by, $en_tablas, $aArrayCols );
							
							$save_pdf_y = $pdf->GetY();
						}				
						
					}
				}
			}	
			
			$pdf->Output();
		
		}
		else
		{
			//
			// HTML
			//
			// Coloca un encabezado HTML <head>
			//
			include "../basic/head_handler.php";
			HeadHandler( $LBL_TITLE, "../");
		
			echo "<STYLE>";
			echo " #home";
			echo "  {";
			echo "	float: left; color: #000000; text-align:left; margin-left: 20px; margin-top: 10px; margin-bottom: 10px; background: white;";
			echo "  width: 800px; ";
			echo "}";
			echo "</STYLE>";   

			echo "<body id='home'>";

			echo "\n\n";
			
			$marc_record = new record_MARC21( $id_biblioteca, $db );
						
			echo "<h1>$LBL_PRINT_CATALOG_TITLE</h1>";
			echo "<h2>$str_order_by</h2><br>";
			
			echo "\n<table width='100%' border='2'>";
			
			$total_columnas = count( $aArrayCols );
			
			// TR Head
			if( $en_tablas == 1 )
			{
				echo "\n<tr>";
				$offset = 0; // solo para PDF
				
				for( $i=0; $i<count($aArrayCols); $i++ )
				{
					$offset = 0;
					echo "<td class='columna cuadricula' width='" . $aArrayCols[$i]["width"]. "px' valign='top'><strong>" . $aArrayCols[$i]["caption"] . "</strong></td>";
				}
				
				echo "</tr>";
			}

			$last_val_4_order = "";
			$this_col = 0;
			
			if( $cols==1 or $cols==0 )  $div_100 = 100;
			else			$div_100 = 100 / $cols;
			
			while( $db->NextRow() )
			{
				$this_col++;
				
				$marc_record->InicializarRegistroMARC21_DesdeBD_Titulo( $db->row["ID_TITULO"] );
				
				//
				// verificar Nuevo grupo (en la IMPRESION DE LA PRIMER O UNICA COLUMNA)
				//
				if( $last_val_4_order != $db->row["VALOR"] )
				{
					if( $en_tablas == 1 )
					{
						echo "<tr>";
						echo " <td class='columna cuadricula' colspan='$total_columnas'><strong>" . $db->row["VALOR"] ."</strong></td>";
						echo "</tr>";
					}
					else
					{
						// en columnas estilo libro CERRAR la columnas pendientes y el renglón
						if( $this_col > 1 )
						{
							for( $x=$this_col; $x<=$cols; $x++ )
							{
								echo "<td>&nbsp; -- </td>";  
							}
						}
						echo "</tr>";
						
						// por columnas (estilo libro)
						$colspan = $cols;

						if( $colspan==0 ) $colspan=1; // al menos una columa SIEMPRE
						
						echo "\n\n";
						echo "<tr><td colspan='$cols'><strong><span style='font-size:140%;'>" . $db->row["VALOR"] ."</span></strong></td></tr>";
						
						$this_col = 1;  // reset 
					}
					
					$last_val_4_order = $db->row["VALOR"];
				}

				// registros
				if( $this_col == 1 )
					echo "\n<tr>";
				
				if( $en_tablas == 0 )
				{
					echo "<td valign='top' width='$div_100%'>";
				}

				$offset = 0;
				
				for( $i=0; $i<count($aArrayCols); $i++ )
				{
					$val = $marc_record->PreProcessor( $aArrayCols[$i]["field"], $temas );
					
					if( $en_tablas == 1 )
					{
						echo "<td class='columna cuadricula' width='" . $aArrayCols[$i]["width"]. "px' valign='top'>$val</td>";
					}
					else
					{
						// en columnas estilo LIBRO
						if( $aArrayCols[$i]["field"] == "001" and $incluir_num_control )
							echo "<span><strong>$val</strong>&nbsp;</span>&nbsp;";
						else
						{
							if( $temas == 1 )
								echo "<br><br>"; // salto de línea antes del tema

							echo $val . " ";
						}
					}
				}
			
				if( $en_tablas == 1 )
				{
					echo "</tr>";
					$this_col = 0;
				}
				else
				{
					echo "<br><br></td>";  // en columnas estilo libro CERRAR la columna
					
					// nueva columna
					if( $this_col == $cols )
					{
						// última columna (REINICIAR)
						echo "</tr>"; 
						$this_col = 0;
					}
				}
				
				$marc_record->clear_record();			
			}
			
			// PREVEER QUE SIEMPRE ESTEN BALANCEADAS LAS COLUMNAS
			if( $en_tablas == 1 )
				;
			else
			{
				$this_col++;
				
				if( $this_col > 1 )
				{
					for( $x=$this_col; $x<=$cols; $x++ )
					{
						echo "<td>&nbsp; -- </td>";  
					}
				}			
				
				echo "</tr>";
			}
			
			echo "</table>";
			
			echo "<br>";
			echo "<input id='btnRegresar' name='btnRegresar' class='boton' type='button' value='$BTN_CLOSEWIN' onclick=\"window.close();\">&nbsp;";
			echo "<input id='btnPrint' name='btnPrint' class='boton' type='button' value='$BTN_PRINT' onclick=\"window.print();\">";
			
			$marc_record->destroy();
			
			echo "</body>";
			echo "</html>";
		}
		
		$db->Close();
	
	}
	
	$db->destroy();

?>