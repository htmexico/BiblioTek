<?php
	session_start();
	
	/*	  
	  - Impresion de Fichas Catalográficas individuales
	  - Permite imprimir en MARC, AACR y Etiquetas
	  
	  - Se Inicia: 07-oct-2009
	*/
		
	include "../funcs.inc.php";
	include "../actions.inc.php";
	include "../basic/bd.class.php";
	
	require_once( "marc.php" );

	check_usuario_firmado();	
	
	include_language( "global_menus" );
	include_language( "gral_vertitulo" );
	
	$id_biblioteca 		= getsessionvar("id_biblioteca");
	$id_titulo	= read_param( "id_titulo", 0, 1 ); // fail if not present
	$by_author  = read_param( "by_author", 0 );
	
	$aacr2   = read_param( "aacr2", 0 );
	$marc    = read_param( "marc", 0 );
	$labels  = read_param( "labels", 0 );
		
	$output_pdf = read_param( "output_pdf", 0 );
	
	// Coloca un encabezado HTML <head>
	include "../basic/head_handler.php";
	HeadHandler( $LBL_PRINT_CARD, "../");

	$db = new DB();			
	$db->Open( "SELECT a.ID_TITULO " . 
			   "FROM acervo_titulos a " . 
			   "WHERE a.ID_BIBLIOTECA='$id_biblioteca' and a.ID_TITULO=$id_titulo;" );
	
	if( $output_pdf == 1 )
	{
		$pdf=new FPDF();
		$pdf->AddPage();
		$pdf->SetFont('Arial','',8);
		
		while( $db->NextRow() )	
		{			
			
			//$pdf->;	
			$pdf->SetXY($columna,$f128);		
			$pdf->Write(5,$db->Field("ID_MATERIAL"));						
			
			$i++;
			
			if ($i<=$exf) {
				$columna=$columna+$esp_columna;
			}
			
			if ($i==$exf) {
				$fila=$fila+$salto_fila;
				$f128=$f128+$salto_fila;
				$columna=$txt_mi;
				$i=0;
				
				if ($fila >= $add_page ) {
					$fila		=$txt_ms;
					$columna	=$txt_mi;
					$f128		=$txt_ms+10;				
					$pdf->AddPage();
				}				
			}			
		}	
		
		$pdf->Output();
	
	}
	else
	{
		echo "<STYLE>";
		echo " #home";
		echo "  {";
		echo "	float: left; color: #000000; text-align:left; margin-left: 20px; margin-top: 10px; margin-bottom: 10px; background: #FFF; ";
		echo "  width: 750px; ";
		echo "}";
		echo "</STYLE>";  

		echo "<body id='home'>\n";

		$marc_record = new record_MARC21( $id_biblioteca, $db );
		
		$marc_record->ImprimeEncabezado(1);

		while( $db->NextRow() )
		{
			$marc_record->InicializarRegistroMARC21_DesdeBD_Titulo( $db->row["ID_TITULO"] );		
			//
			// modalidad
			//		
			$modalidad = 0;
			
			if( $by_author == 1 )
				$modalidad = 1;
			
			if( $aacr2 == 1 )
				$marc_record->ImprimeFichaAACR2( $modalidad, 0 );
			else if( $marc == 1 )
				$marc_record->ImprimeFichaMARC();
			else if( $labels == 1 )
				$marc_record->ImprimeFichaEtiquetas( 1, "" );
			
			$marc_record->clear_record();			
			
			echo "\n\n";
		
		}
		
		$marc_record->destroy();
		
		echo "\n<hr>";
		echo "<input type='button' value='$BTN_PRINT' onClick='javascript:print();'>";
		
		echo "</body>";
		echo "</html>";
	}

	
	$db->destroy();
	
	

?>