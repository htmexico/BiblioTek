<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  Guarda/Modifica un registro MARC de catalogación
	  
	  01 may 2009: Se crea el archivo anls_catalogacion_save.php.
	  02 may 2009: Se crea/alimenta el registro MARC (en memoria)
	  04 may 2009: Se genera y comprueba CreateRecord_ISO2709() 
	  
     */
		
	include "../funcs.inc.php";
	include "../basic/bd.class.php";
	
	check_usuario_firmado(); 

	//define( "DEBUG", 1 );
	
	// Coloca un encabezado HTML <head>
	include "../basic/head_handler.php";
	
	include_language( "anls_catalogacion" );	
	
	$id_biblioteca = getsessionvar("id_biblioteca");

	$the_action = read_param( "the_action", "", 1 );

	include "catalog.inc.php";
	include "marc.php";
	
	load_getpost_vars();
	
	if( $the_action == "create_new" or $the_action == "edit" )
	{	
		$db = new DB();

		$marc_record = new record_MARC21( $id_biblioteca, $db );
		
		$marc_record->AgregarCampo( "$$$", false, true );
		
		if( $the_action == "edit" )
		{
			$marc_record->nIDTitulo = read_param( "id_titulo_editing", 0 );
		}
		
		$marc_record->nIDSerie  = read_param( "id_serie", 0 );
		
		// CAMPO 008
		$objCampo = $marc_record->AgregarCampo( "008", false, true );
		$valor = $_POST["field_008"];
		$objCampo->cValor = $valor;

		$marc_fields = $_POST["marc_fields"];
		$marc_fields = split( ",", $marc_fields );
		
		//if (defined('DEBUG')) 
		//   print_r( $marc_fields );
		
		foreach( $marc_fields as $element )
		{
			if (defined('DEBUG')) 
				echo " - " . $element . "<br>";
			
			$type_element = substr( $element, 0, 3 );
			
			$extra_element = substr( $element, 4, 100 );
			
			$id_campo = substr( $extra_element, 0, 3 );
			$id_elemento_campo = trim(substr( $extra_element, 0, 50 ));
			
			$id1 = 0;
			$id2 = 0;
			
			if( $pos = strpos( $id_elemento_campo, "X" ) )
			{
				if( strpos( $id_elemento_campo, "X1" ) )
					$id1 = 1;
					
				if( strpos( $id_elemento_campo, "X2" ) )
					$id2 = 1;					
				
				$id_elemento_campo = trim(substr( $id_elemento_campo, 0, $pos-1 ));
			}
			
			$valor = "";
			
			$cIDs = "";
			$cValID1 = "";
			$cValID2 = "";
			
			$id_subcampo = "";
			
			$objCampo = NULL;
			
			if( $type_element == "new" )  // nuevo campo - valor directo
			{
				if (defined('DEBUG')) 
				{ if( $id_campo != "$$$" ) echo "<br>"; }
				
				if( $id_campo == "$$$" )
				{ 
					// se deja para el final PARA REALIZAR EL CALCULO DE DIRECTORIO Y TAMAÑO
					// probablemente no venga
				}
				else if( $id_campo == "008" )
				{
					// se deja para el final
					// probablemente no venga
				}
				else 
				{
					$valor = read_param( "txt_" . $id_elemento_campo, "" );  // verificar si trae valor directo
					
					if( $id1==1 or $id2==1)
					{
						// se trata de un campo que debería traer INDICADORES						
						// verificar si trae valor directo						
						if( ($cIDs = read_param( "XX1_" . $id_elemento_campo, "" )) != "" )
						{
							$array_of_ids = split( "&", $cIDs ); // cuantos indicadores vienen
							
							foreach( $array_of_ids as $id )
							{
								list($whatid, $whatval) = split( "=", $id );
								
								if( $whatid == "X1" )
									$cValID1 = $whatval;
								
								if( $whatid=="X2" )
									$cValID2 = $whatval;									
							}
							// foreach
						}
					}
					
					$objCampo = $marc_record->AgregarCampo( $id_campo, false, true );
					$objCampo->cValor = $valor;
					$objCampo->ID	  = $id_elemento_campo;
					
					if( $cValID1 != "" )
						$objCampo->AgregarIdentificador( 1, "", "", $cValID1 );
					
					if( $cValID2 != "" )
						$objCampo->AgregarIdentificador( 2, "", "", $cValID2 );
					
				}
				// end -if
				
				if (defined('DEBUG')) 
				{
					echo $type_element . " [$id_campo] = $valor";
					if( ($id1==1 or $id2==1) and ($cIDs!=""))
					{ echo "<$cValID1|$cValID2>";}
					echo "<br>";
				}
			}
			else if( $type_element == "txt" )  // subcampo
			{
				$valor = trim($_POST[ "txt_$id_elemento_campo" ]);
				
				if( $valor != "" )
				{
					list($id_elemento_campo, $index_subcampo, $id_subcampo) = split( "@", $id_elemento_campo );

					if (defined('DEBUG')) 
					{
						echo "&nbsp;<$id_elemento_campo> $index_subcampo $id_subcampo <br>";
						echo " ";
					}
					
					$objCampo = $marc_record->BuscarCampoMARC_X_ID( $id_elemento_campo );
					
					if( $objCampo == NULL ) 	
					   die( "($id_elemento_campo) FIELD NOT FOUND" );
					else
					{
						$objSubCampo = $objCampo->AgregarSubCampo( $id_subcampo, "$index_subcampo", "", "xx", "", "" );
						$objSubCampo->cValor = $valor;
					}
					
					if (defined('DEBUG')) 
					{
						echo "&nbsp;" . $type_element . " [$id_campo]  $id_subcampo (". $objSubCampo->ObtenerDescripcion() .") = $valor";
						echo "<br>";
					}
				}
			}

		}
		// foreach
		
		// CABECERA
		if( ($objCampo = $marc_record->BuscarCampo( "$$$" )) != NULL )
		{
			// Se crea
			$marc_record->SetTipoMaterial( $_POST["id_tipomaterial"] );
			
			//$marc_record->FTipoRegistro 		 = $_POST["id_tiporegistro"];   // rs
			$marc_record->FEstadoRegistro 		 = $_POST["id_estadoregistro"];   // rs
			$marc_record->FTipoRegistro		  	 = $_POST["id_tipomaterial"];     // dt
			$marc_record->FNivelBibliografico	 = $_POST["id_nivelbibliografico"]; // bl
			$marc_record->FTipoControl			 = "#"; // 

			$marc_record->FCodigoEsquemaChars	 = "#"; // PENDIENTE REVISAR
			$marc_record->FConteoIndicadores	 = "2"; // 2 CARACTERES PARA ESPECIFICAR INDICADORES
			$marc_record->FConteoCodigoSubcampo  = "2"; // 2 CARACTERES PARA ESPECIFICAR LOS CODIGO DE SUBCAMPOS

			$marc_record->FDireccionBase_Datos   = 0;  // ba

			$marc_record->FNivelCodificacion     = $_POST["id_nivelcodificacion"]; // el
			$marc_record->FFormaCatalogacion     = $_POST["id_formacatalogacion"]; // cf
			$marc_record->FNivelRegistro_Recurso = "#";

			$marc_record->FMapaEntradas 		 = 0;  // em
			
			//$marc_record->GenerarCabecera(0);  LA CABECERA SE GENERA EN CreateRecord_ISO2709. 
		}		
		
		// genera el registro MARC según ISO2709
		// esto mismo genera la cabecera
		$marc_record->CreateRecord_ISO2709( false );
		
		/***
		    ESTAS LINEAS PERMITIRIAN EXPORTAR A UN ARCHIVO
		 
			//$contenido = $marc_record->CreateRecord_ISO2709( false );
			
			//echo $contenido;
		 
			$MyFile = "C:\libro_last_v2.mrc";
				
			$handling = fopen($MyFile, 'x');
			fwrite($handling, $contenido); 
			fclose($handling); 
			
		 **/
		
		/***
		foreach( $marc_record->aCamposMarc as $value )
		{
			echo "<br>";
			echo $value[1]->cIDCampo . " = " . $value[1]->cValor . " " . $value[1]->ContarSubcampos() . "<br>";
			
			foreach( $value[1]->subcampos as $subfield )
			{
				echo $subfield->cIDSubCampo . "= " . $subfield->cValor . "<br>";
			}	
		}
		
		die( "OK" );
		**/

		//
		// crear REGISTRO MARC en BD
		// PENDIENTE: se deben crear campo 001 y 005
		//
		if( $the_action == "create_new" )
			$marc_record->AgregarRegistro( getsessionvar("id_usuario") );
		else
		{
			$marc_record->ActualizarRegistro( getsessionvar("id_usuario") );
			$marc_record->InicializarModificacion();
		}
		
		// Agregar campo por campo
		foreach( $marc_record->aCamposMarc as $value )
		{
			$objCampo = $value[1];

			$marc_record->GuardarElementosSubCampos( $objCampo );
		}
		
		$marc_record->FinalizarGuardado();
		
		$marc_record->destroy();

		require_once("../actions.inc.php");		
		agregar_actividad_de_usuario( ANLS_CATALOGING, "" );
		
		$str_plantilla = "";
		
		$id_plantilla = read_param( "id_plantilla", 0 );
		
		if( $id_plantilla != 0 )
			$str_plantilla = "&id_plantilla=" . $id_plantilla;
		
		ges_redirect( "Location:anls_catalogacion_end.php?id_titulo=$marc_record->nIDTitulo".$str_plantilla );

	}
			
?>

<script type="text/javascript" language="JavaScript">
</script>

<STYLE type="text/css"> 
</STYLE>

<body id="home">

</body>	

</html>