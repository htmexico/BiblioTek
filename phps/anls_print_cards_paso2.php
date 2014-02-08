<?php
	session_start();
	/*	  
	  - Impresion de Fichas Catalográficas
	  - Inicio 17-09-2009.  Sin filtro.
	  
	  - 10nov2009 - Se coloca el filtro previo.
	  
	*/
		
	include "../funcs.inc.php";
	include "../actions.inc.php";
	include "../basic/bd.class.php";
	include "../basic/fpdf/fpdf.php";
	
	require_once( "marc.php" );
	
	include_language( "global_menus" );

	check_usuario_firmado();	
	
	include_language( "anls_print_cards" ); // archivo de idioma	
	
	$id_biblioteca = getsessionvar("id_biblioteca");
	
	$output = read_param( "output", 0 );
	$by_author = read_param( "by_author", 0 );
	$id_tipomaterial = read_param( "id_tipomaterial", "", 1 );
	
	$the_action = read_param( "action", "" );

	$db = new DB();			
	$db->Open( "SELECT a.ID_TITULO, a.PORTADA, a.CONTRAPORTADA " .
			   " FROM acervo_titulos a " . 
			   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TIPOMATERIAL='$id_tipomaterial'; " );
	
	if ( $the_action == "go" ) 
	{		
		if( $output == "pdf" )
		{
	
			$pdf=new FPDF( 'P', 'mm', 'letter' );
			$pdf->AddPage();
			$pdf->SetFont( "Arial", "", 7 );
			
			$i = 0;
			
			$marc_record = new record_MARC21( $id_biblioteca, $db );
			
			$fila 	 = 1;
			
			$pdf->SetXY( 5, $fila );		
			
			while( $db->NextRow() )	
			{			
				$marc_record->InicializarRegistroMARC21_DesdeBD_Titulo( $db->row["ID_TITULO"] );
				
				$i++;
				
				$modalidad = 0;
				
				if( $by_author == 1 )
				    $modalidad = 1;				
				
				$pdf->SetDrawColor( 0, 0, 0 );
				
				// print to a PDF canvas
				$filas = $marc_record->ImprimeFichaAACR2( $modalidad, 0, $pdf, $fila );
				$marc_record->clear_record();							
				
				$pdf->Line( 39, $fila + 4, 39, $fila + 60 );
				
				$fila += ($filas * 3) + 1;

				//$pdf->Cell( 100, 2, " $filas $fila ", 0, 0, "L" );
				
				if ($fila >= 200 ) 
				{
					$pdf->AddPage();
					$fila = 1;
				}				
			}	
			
			$pdf->Output();
		
		}
		else
		{
			// Coloca un encabezado HTML <head>
			include "../basic/head_handler.php";
			HeadHandler( $LBL_TITLE, "../");
		
			echo "<STYLE>";

			echo " #home";
			echo "  {";
			echo "	float: left; color: #000000; text-align:left; margin-left: 20px; margin-top: 10px; margin-bottom: 10px; background: gray; ";
			echo "  width: 750px; ";
			echo "}";
			echo "</STYLE>";  

			echo "<body id='home'>";
			 
			echo "\n\n";
			
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
				
				//echo $db->row["ID_TITULO"] . "<br>";
				$marc_record->ImprimeFichaAACR2( $modalidad );
						
				$marc_record->clear_record();			
				
				echo "\n\n";
			
			}
			
			$marc_record->destroy();
			
			echo "</body>";
			echo "</html>";
		}
	
	}
	
	$db->destroy();

?>