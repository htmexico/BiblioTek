<?php
	session_start();
	/*	  
	  - Impresion de Codigo de Barras
	  - Codificaciones EAN1, UPC-A, Codificacion 39, Codificacion 128 A, B, C
	  - Inicio 17-04-2009.    
	*/
		
	include "../funcs.inc.php";
	include "../actions.inc.php";
	include "../basic/bd.class.php";
	include "../basic/fpdf/fpdf.php";
	include "funcs_barcode.php";
	
	check_usuario_firmado();	
	
	// archivo de idioma
	//include_language( "anls_barcode" );
	
	$id_biblioteca 		= getsessionvar("id_biblioteca");
	$id_usuario_admin 	= getsessionvar('id_usuario');

	$plantilla	= read_param( "cmb_plantilla", -1 );
	$codigo		= read_param( "cmb_codigo", 0 );
	$i			= 0;

	if ( $plantilla != -1 and $plantilla != 4 ) 
	{
		$db = new DB();
		$db->Open( "SELECT * FROM cfgplantillas_bc WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PLANTILLA=$plantilla; ");
		
		if( $db->NextRow() ) 
		{
			$txt_no_etiquetas = $db->row["NO_ETIQUETAS"];
			$txt_topmargin	 = $db->row["MARGEN_SUP"];
			$txt_leftmargin	 = $db->row["MARGEN_IZQ"];
			$inter_space_row = $db->row["ESPACIO_FILAS"];
			$inter_space_col = $db->row["ESPACIO_COLS"];
		}
		$db->Close();
		
		$db->destroy();
	} 
	else
	{
		$txt_no_etiquetas	= read_param( "txt_number_of_labels", 2 ); // Def JELS = 2
		$txt_topmargin		= read_param( "txt_topmargin", 5 );  // Def JELS = 5
		$txt_leftmargin		= read_param( "txt_leftmargin", 0 );  // Def JELS = 0
		$inter_space_row	= read_param( "txt_row_inter_space", 0 );
		$inter_space_col	= read_param( "txt_col_inter_space", 5 );
		
		//$inter_space_col += 10;
	}	

	$f128     = $txt_topmargin + 10;
	$add_page = 290;

	$db = new DB();
	$db->Open( "SELECT ID_MATERIAL FROM acervo_copias WHERE ID_BIBLIOTECA=$id_biblioteca; " );

	//
	// Codificacion 128 A, B, C
	//
	if ( ($codigo==1) or ($codigo==2) or ($codigo==3) ) 
	{
		// A   A-Z, 0-9, Caracteres especiales, codigos de control ASCII, Densidad Alta, Checksum Si, Longitud Variable.
		// B   A-Z, a-z, 0-9, Caracteres especiales, Desidad alta, Checksum Si, Longitud Variable.
		// C   0-9 Densidad alta, chekcsum Si, Variable, numero par de caracteres.
		$pdf = new PDF_Code128();

		$pdf->AddPage();
		$pdf->SetFont( "Arial", "", 8 );

		$fila	 = (int) $txt_topmargin;
		$columna = (int) $txt_leftmargin;
		
		while( $db->NextRow() )	
		{
			$pdf->Code128( $columna, $fila, $db->row["ID_MATERIAL"], 40, 10 );	
			$pdf->SetXY( $columna, $f128 );		
			$pdf->Write(5,$db->row["ID_MATERIAL"] );

			$i++;

			if( $i <= $txt_no_etiquetas )
			{
				$columna = $columna + $inter_space_col;
			}

			if ( $i == $txt_no_etiquetas ) 
			{
				$fila=$fila+$inter_space_row;
				$f128=$f128+$inter_space_row;
				$columna=$txt_leftmargin;
				$i=0;

				if( $fila >= $add_page )
				{
					$fila		=$txt_topmargin;
					$columna	=$txt_leftmargin;
					$f128		=$txt_topmargin+10;				
					$pdf->AddPage();
				}				
			}			
		}	
		
	}	
	//Codificacion 39, A-Z, 0-9, -.$%+/, Densidad Media, Checksum opcional, Longitud variable.
	else if ($codigo==4) 
	{
		$pdf=new PDF_Code39();
		$pdf->AddPage();
		$pdf->SetFont('Arial','',8);		
		
		$fila	 = (int) $txt_topmargin;
		$columna = (int) $txt_leftmargin;		
		
		while( $db->NextRow() ) {
		
			if ( comprobar_codigo( $db->Field("ID_MATERIAL"), $codigo ) ) {
			
				$pdf->Code39($columna,$fila,$db->Field("ID_MATERIAL"),1,10);
			
				$i++;

				if ($i<=$txt_no_etiquetas)
				{
					$columna=$columna+$esp_columna;
				}

				if ($i==$txt_no_etiquetas) 
				{
					$fila=$fila+$salto_fila;
					$f128=$f128+$salto_fila;
					$columna=$txt_leftmargin;
					$i=0;

					if ($fila >= $add_page) 
					{
						$fila		=$txt_topmargin;
						$columna	=$txt_leftmargin;					
						$pdf->AddPage();
					}				
				}	
			}		
		}
		
	}
	// Codificacion EAN13, 0-9, Densidad Media, Checksum Si, Longitud Fija 13 Digitos.
	// Codificación UPC-
	else if (($codigo==5) or ($codigo==6)) 
	{		
		$pdf = new PDF();
		$pdf->Open();
		$pdf->AddPage();
		
		$fila	 = (int) $txt_topmargin;
		$columna = (int) $txt_leftmargin;		

		while( $db->NextRow() ) 
		{
			$ok = false;

			if ( $codigo == 5 )
			{
				if ( strlen($db->row["ID_MATERIAL"])==12 && is_numeric($db->row["ID_MATERIAL"]) )
				{
					$ok=true;
					$pdf->EAN13( $columna,$fila,$db->Field("ID_MATERIAL") );
				}
			}
			else if ( $codigo == 6 ) 
			{
				if ( strlen($db->field("ID_MATERIAL") )==12 && is_numeric($db->Field("ID_MATERIAL") ) ) {
					$ok=true;
					$pdf->UPC_A( $columna,$fila,$db->Field("ID_MATERIAL") );	
				}
			}				

			if ( $ok ) 
			{
				$i++;

				if ($i<=$txt_no_etiquetas) 
				{
					$columna=$columna+$esp_columna;
				}

				if ($i==$txt_no_etiquetas) 
				{
					$fila=$fila+$salto_fila;
					$f128=$f128+$salto_fila;
					$columna=$txt_leftmargin;
					$i=0;
					
					if ($fila >= $add_page ) 
					{
						$fila		=$txt_topmargin;
						$columna	=$txt_leftmargin;				
						$pdf->AddPage();
					}				
				}
			}
		}
	}
	
	if( isset( $pdf ) )
	{
		$pdf->Output();
	}
	
	$db->Close();

	$db->destroy();
?>