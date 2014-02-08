<?php
/**
 * Implements class for MARC records cataloging
 *
 * 22-mar-2009:  File created.
 * 11-abr-2009:  Se agrega la función GetItemStatus la clase REGISTRO_MARC.
 * 13-abr-2009:  Se agregan variables globales para indicar el status de los libros.
 * 24 abr 2009:  Se adaptan de Delphi las funciones InicializarCabecera() y GenerarCabecera().
 * 24 abr 2009:  Se elimina el status Reservado.
 * 27 abr 2009:  Se incorpora el uso de la clase ISO2709
 * 04 may 2009:  Se genera una cabecera Perfecta
                 Se logra generar un registor MARC 2709, legible/exportable
 * 08 may 2009:  Se implementa función ImprimeFichaAACR2()
 * 19 jun 2009:  Se implementa diferenciador de idioma en ObtenerDescripcion() de Campo, Subcampo e Indicadores
 * 11 ago 2009:  Se agrega propiedad notFound en TMARC21_SubCampo
 * 16 ago 2009:  Se adapta carga de Autoridades en TRegistroMARC21
 * 02 oct 2009:  Se elimina el uso de db_query y se reutiliza un puntore a un objeto DB()
 * 05 oct 2009:  Se adaptan los DIVS en ImprimeFichaMARC()
 * 15 oct 2009:  Se agrega parm. permitir_elegir_items_prestados en ImprimeFicha()
 * 10 nov 2009:  Se agregan filtros a ImprimeFichaAACR2() para FPDF
 * 13 nov 2009:  Se crea función PreProcessor() para utilizar desde archivos de reporteo
 
 PENDIENTES:
 
   - documentar uso de INTERNALUS1 en ActualizarGuardar 
   
 */

require_once '../funcs.inc.php';
require_once 'iso2709.inc.php';

/**
  SubCampo MARC
 */
 
class TMARC21_SubCampo
{
	var $cValor;

	var $cDescripcion_ESP;
	var $cDescripcion_ENG;

	var $cIDCampo;
	var $cIDSubCampo;

	var $cUrl;
	var $cNota;

	var $bObsoleto;
	
	var $notFound;

	var $cRepetible_SN;

	var $cTesauro;
	var $cConectorAACR;

	var $cTag;
   
	var $Parent;
	
	var $cID; 

	function TMARC21_SubCampo( $id_campo, $id_subcampo,
        $c_DescripESP, $c_DescripENG, $c_ValorDefault, $c_Tesauro, $c_ConectorAACR, $pParent )
	{
		$this->cIDCampo     	= $id_campo;
		$this->cIDSubCampo      = $id_subcampo;
		$this->cDescripcion_ESP = $c_DescripESP;
		$this->cDescripcion_ENG = $c_DescripENG;
		$this->cNota            = "";
		$this->cURL             = "";
		$this->cValor           = $c_ValorDefault;

		$this->cTesauro         = $c_Tesauro;
		$this->cConectorAACR    = $c_ConectorAACR;

		$this->Parent           = $pParent;

		$this->bObsoleto        = false;

		$this->cRepetible_SN    = "";

		if( ($posNR = strpos( $this->cDescripcion_ESP, "(NR)" )) != 0 ) 
		{
			$this->cRepetible_SN    = "N";
			$this->cDescripcion_ESP = substr_replace( $this->cDescripcion_ESP, "", $posNR, 4 );
		}
		else if( ($posR = strpos( $this->cDescripcion_ESP, "(R)" )) != 0 ) 
		{
			$this->cRepetible_SN    = "S";
			$this->cDescripcion_ESP = substr_replace( $this->cDescripcion_ESP, "", $posR, 4 );
		}

		$this->cTag = "";			
		$this->cID = "";
		
		$this->notFound = false;
	}
 
	function destroy() 
	{
	}
	
	//
	// Devuelve un ID único para cada CAMPO + SUBCAMPO + OCURRENCIA
	// el formato que devuelve es SUBCAMPO + OCURRENCIA + CAMPO
	//
	function ObtenerID( $unique_index = -1 )   // 14-ago-2009, Antes el unique_index estaba por default en CERO = 0
	{
		$Result = $this->Parent->cIDCampo;
		
		if( $unique_index != -1 )
			$Result .= "_$unique_index";

		$nPos = $this->Parent->ObtenerIndexSubCampo( $this );

		if( $nPos != -1 )
			$Result .= "@" . "$nPos";

		$Result .= "@" . $this->cIDSubCampo;

		$this->cID = $Result;

		return $this->cID;
	}
	
	//
	//
	//
	function ObtenerDescripcion()
	{
		$Result = get_translation( $this->cDescripcion_ESP,  $this->cDescripcion_ENG, "" );
		
/*		if( $this->Parent->marc_Parent->language == 1 )
			$Result = $this->cDescripcion_ESP;
		else
			$Result = $this->cDescripcion_ENG;
*/
		if( $Result == "" )
			$Result = $this->cDescripcion_ENG;

		return( $Result );
	}
	
	//
	// Corregir errores de presentación
	//
	function FixValue()
	{
		// remover caracteres ESCAPADOS (Unix style)
		
		// REMOVER COMILLA SIMPLE (SINGLE QUOTE)
		$this->cValor = str_replace( "\'", "'", $this->cValor );
	}
 
}


/****
 Identificadores de Campo MARC
 */
 class TMARC21_Identificador
 {
    var $cValor;

    var $cDescripcion_ESP;
    var $cDescripcion_ENG;

    var $nID; // 1 o 2

    var $aValoresPosibles = NULL;

    var $Parent;

    function TMARC21_Identificador( $id_campo, $id, $cDescripESP, $cDescripENG, $c_ValorDefault )
	{
	   //$this->cIDCampo = $id_campo;

	   $this->aValoresPosibles = array();

	   $this->cValor        = $c_ValorDefault;

	   //$this->cDescripcion_ESP = $cDescripEsp;
	   //$this->cDescripcion_ENG = $cDescripEng;

	   $this->nID = $id;
	}
	
    function destroy()
	{
		$this->aValoresPosibles = array();
	}

    function AgregarValorPosible( $cSubCodigo, $cDescripESP, $cDescripENG ) // 11feb2009
	{
		
	}
    
	function CargarValoresPosibles()
	{
	}
	

    function ObtenerDescripcion()
	{
	}

 }
 

/**
 Campo MARC
 */
class TMARC21_Campo
{
	var $cDescripcion_ESP; //: string;
	var $cDescripcion_ENG; //: string;

	var $ElementosEncabezado; 

	var $cIDCampo;

	var $cUrl;
	var $cNota;

	var $bEncabezado;
	var $bDirectorio;

	var $bObsoleto; //: boolean;
	var $cRepetible_SN;
	var $notFound;
	
	var $marc_Parent; //: TMARC21_Registro;

	var $objID1;
	var $objID2;
	var $subcampos;

	var $cValor;
	
	var $bCampoControl;
	
	var $ID;
   
	function TMARC21_Campo( $id_campo, $parent )
	{
		$this->marc_Parent = $parent;
		$this->cIDCampo = $id_campo;
		
		$this->subcampos = Array();
		
		$this->objID1 = NULL;
		$this->objID2 = NULL;
		
		$this->ID = ""; // se asignará un ID particular 
 		
		//$this->bSinSubcampos = true;
		$this->bCampoControl = false;
		
		$this->bEncabezado = false;
		$this->bDirectorio = false;
		//bGeneralesCampo008 = false;

		$this->bAutomatico = false;

		$this->ActualizaRepetible();

		$this->notFound = false;
		$this->bObsoleto = false;
		
		if( $id_campo == '$$$' ) { $this->bEncabezado = true; }
		if( $id_campo == '###' ) { $this->bDirectorio = true; }
		
		if( $id_campo == "$$$" or $id_campo == "001" or $id_campo == "003" or $id_campo == "005" or
			$id_campo == "006" or $id_campo == "007" or $id_campo == "008")
		{
			$this->bCampoControl = true;
		}
		
		$this->cValor = "";
		$this->cUrl = "";
    }	
	
	//
	// elimina los (R) o (NR) y actualiza el campo cRepetible_SN
	//
	function ActualizaRepetible()
	{
		$this->cRepetible_SN = "";

		if( ($posNR = strpos( $this->cDescripcion_ESP, "(NR)" )) != 0 ) 
		{
			$this->cRepetible_SN    = "N";
			$this->cDescripcion_ESP = substr_replace( $this->cDescripcion_ESP, "", $posNR, 4 );
		}
		else if( ($posR = strpos( $this->cDescripcion_ESP, "(R)" )) != 0 ) 
		{
			$this->cRepetible_SN    = "S";
			$this->cDescripcion_ESP = substr_replace( $this->cDescripcion_ESP, "", $posR, 4 );
		}

	}
	
	function destroy() 
	{
		foreach($this->subcampos as $value) 
		{
			 $value->destroy();
		}
		
		$this->subcampos = Array();
		
		if( $this->objID1 != NULL )
		   $this->objID1->destroy();
		   
		if( $this->objID2 != NULL )
		   $this->objID2->destroy();		   
	}   
	
	function BuscarSubcampo( $idsubcampo )
	{
		for( $i=0; $i<=count($this->subcampos)-1; $i++ )
		{
			$xObjSubCampo = $this->subcampos[$i];
			
			if( $xObjSubCampo != NULL )
				if( $xObjSubCampo->cIDSubCampo == $idsubcampo )
				{
					return $xObjSubCampo;
				}
		}
		
		return NULL;
	}
	
	function ContarSubcampos()
	{
		return count($this->subcampos);
	}

	function ObtenerSubCampo( $idx )
	{
		if( $idx < count( $this->subcampos ) )
			{ return $this->subcampos[$idx]; }
		else
			{ return NULL; }

	}
	
    function AgregarIdentificador( $nID, $cDescripESP, $cDescripENG, $cValorDefault )
	{
	  if( $nID==1)
	  {
		 $this->objID1 = new TMARC21_Identificador( $this->cIDCampo, 1, $cDescripESP, $cDescripENG, $cValorDefault );
		 $this->objID1->Parent = $this;
	  }

	  if($nID==2)
	  {
		 $this->objID2 = new TMARC21_Identificador( $this->cIDCampo, 2, $cDescripESP, $cDescripENG, $cValorDefault );
		 $this->objID2->Parent = $this;
	  }
	}
	
    function AgregarSubCampo( $cIDSubcampo, $cDescripESP, $cDescripENG, $cValorDefault, $cTesauro, $cConectorAACR )
	{
		$record_MARC_not_found = false;
		
		//if( $cDescripESP == "" and $cDescripENG == "" and $cValorDefault == "" and $cTesauro == "" and $cConectorAACR == "" )
		if( $cDescripESP == "" and $cDescripENG == "" and $cTesauro == "" and $cConectorAACR == "" )
		{
			if( $this->cIDCampo == "$$$" )
				$nMarcVal = 2;
			else if( $this->cIDCampo == "008" )
				$nMarcVal = 2;				
			else
				$nMarcVal = 9;
			
			//
			// hacer query a codigo MARC
			// para obtener los datos de la descripción y otros códigos 
			//
			$sql = "SELECT a.CODIGO, a.SUBCODIGO, a.DESCRIPCION, a.DESCRIPCION_ORIGINAL, a.NOTA, a.TESAURO, a.AUTOMATICO, a.CONECTOR_AACR, a.OBSOLETO ";
			$sql .= "FROM marc_codigo21 a ";
			$sql .= "WHERE (a.ID_CAMPO='$this->cIDCampo' and a.CODIGO='$cIDSubcampo' and a.NIVEL_MARC=$nMarcVal) and (a.OBSOLETO!='S') ";
			
			require_once( "../basic/bd.class.php" );
			
			//echo $this->marc_Parent->dbx->last_sql_executed;
			
			//echo $this->cIDCampo . "<br>";
			
			$resultqry = $this->marc_Parent->dbx->SubQuery( $sql );
			
			if( $row = $this->marc_Parent->dbx->FetchRecord( $resultqry ) )
			{
				$cDescripESP   = $row["DESCRIPCION"];
				$cDescripENG   = $row["DESCRIPCION_ORIGINAL"];
				$cTesauro      = $row["TESAURO"];
				$cConectorAACR = $row["CONECTOR_AACR"];		
			}
			else	
				$record_MARC_not_found = true;
			
			$this->marc_Parent->dbx->ReleaseResultset( $resultqry );
		}

		$xObjMarc21 = new TMARC21_SubCampo( $this->cIDCampo, $cIDSubcampo, $cDescripESP, $cDescripENG, $cValorDefault, $cTesauro, $cConectorAACR, $this );
		
		$this->subcampos[] = $xObjMarc21;
		
		$xObjMarc21->ObtenerID();
		$xObjMarc21->notFound = $record_MARC_not_found;
		
		return $xObjMarc21;
	}
	
	//
	// Modificaciones: 
	// 07 may 2009, se agregaron params. subfield_code_start y subfield_code_end
	// 08 may 2009, se agregó parametro $bSoloSubCamposALFABETICOS
	// 
	// TMARC21_Campo.ConcatenarValores
	//
	function ConcatenarValores( $concatenarCon=" ", $incluirIDSubCampos=false, $bSoloSubCamposALFABETICOS=false, $subfield_code_start="", $subfield_code_end="" )
	{
		$xObjSubCampo = NULL;
		$Result = "";

		$nSubCampos = count($this->subcampos);
		
		for( $i=0; $i<=$nSubCampos-1; $i++ )
		{
			$xObjSubCampo = $this->subcampos[$i];

			if( $bSoloSubCamposALFABETICOS )
			{
				$_cIDSubCampo = substr($xObjSubCampo->cIDSubCampo, 1, 5 ); 
				
				if (ord( $_cIDSubCampo ) > 48 and ord( $_cIDSubCampo ) < 57 ) 
					{ continue; }
			}

			$xObjSubCampo->cValor = trim( $xObjSubCampo->cValor );
			
			if( $xObjSubCampo->cValor != "" )
			{
				if( $Result != "" )
				{
					if( $xObjSubCampo->cConectorAACR != "" )
					{
						$cLastChar = substr( $Result, (strlen($Result)-strlen($xObjSubCampo->cConectorAACR)), strlen($xObjSubCampo->cConectorAACR) );
						
						$is_lastchar_a_connector = false;
						
						if( $cLastChar == "/" or $cLastChar == ":" or $cLastChar == ";" )
						{
							// si el último caracter fuese un conector que el usuario catalogó
							// aunque sea incorrecto debe permanecer
							$is_lastchar_a_connector = true;
						}
						
						if( $cLastChar != $xObjSubCampo->cConectorAACR and !$is_lastchar_a_connector )
						{
							// el último caracter es diferente del conector AACR2 y el mismo caracter no es un conector ; entonces Agregarlo
							
							$Result = $Result . " " . $xObjSubCampo->cConectorAACR;
							
							$Result .= $concatenarCon;							
						}
						else
						{
							// ya viene el conector registrado por usuario
							if( substr( $Result, strlen($Result),1 ) != " " )
							{
								$Result .= $concatenarCon;	
							}
						}
					}
					else
					{
						$Result .= $concatenarCon;
					}
			   }

			   $Result .= $subfield_code_start;
			   
				// concatenar
				if(($incluirIDSubCampos) and ($nSubCampos>1))
				{
					$Result .= $xObjSubCampo->cIDSubCampo;
				}
		  
			   $Result .= $subfield_code_end;
			   
			   $Result .= htmlentities( $xObjSubCampo->cValor );
			}

		} // for
		
		return $Result;
		
		//if Result <> '' then
		//   if Pos( Result[Length(Result)], PUNTUACION_AACR ) = 0 then
		//      Result := Result + '.';		

	} // func
	
	//
	// MARC21_Campo.Valor
	//
	function Valor()
	{	
		if( $this->bCampoControl )
		{
			if( $bEncabezado )
			{
			   // Armar Encabezado según los parametros del
			   // registro MARC 
			   $cValor = $marc_record->GenerarCabecera();
			   
			   return $cValor;
			}
			else //if( $this->bSinSubcampos )
				return $this->cValor;
		}
		else
		   return $this->ConcatenarValores(false,true);
	}

	function ObtenerDescripcion()
	{
		$Result = get_translation( $this->cDescripcion_ESP, $this->cDescripcion_ENG, $this->cDescripcion_ENG );

/**		if( $this->marc_Parent->language == 1 )
			$Result = $this->cDescripcion_ESP;
		else
	 		$Result = $this->cDescripcion_ENG; **/

		if( $Result == "" )
			$Result = $this->cDescripcion_ENG;
			
		if( $this->notFound ) $Result .= "Field definition NOT FOUND";

		return( $Result );
	}

	//
	// Exclusivo para LEADER / CAMPO 008 y otros campos que no tienen subcampos
	// Permite obtener 
	//
	//
	function ObtenerValorDePosicionFija( $posicion )
	{
		if( $this->cValor == "" )
			return "";
		else
		{
			// hay datos de varias posiciones
			if( $pos = strpos( $posicion, ":" ) ) 
			{
				// desde - hasta
			}
			else
			{
				//  posición única
				return substr( $this->cValor, $posicion, 1 );
			}
		}
	}
	
	// TCampo
	function ObtenerID()
	{
		$nPos = 0;
		
		$Result = "F" . $this->cIDCampo;

		$nPos = $this->marc_Parent->ObtenerIndexCampo( $this );

		if( $nPos != -1 )
		  $Result .= "_" . $nPos;

		return $Result;
	}
	
	function ObtenerIndexSubCampo( $objSubCampo )
	{
	  $Result = -1;

	  for( $i = 0; $i<count($this->subcampos) and $Result==-1; $i++ )
	  {
		if( $this->subcampos[$i] == $objSubCampo )
		{ $Result = $i; }
	  }
	  
	  return $Result;
	}
	
	
	//
	// Cargar subcampos de un registro ISO2709
	//
	function Cargar_SubCampos_ISO2709( $iso2709_record, $index, $depurar=0 )
	{
		if( $this->objID1 != NULL )
			{ $this->objID1->cValor = ""; }
		if( $this->objID2 != NULL )
			{ $this->objID2->cValor = ""; }

		if( $this->bCampoControl ) 
		{
			$valor = $iso2709_record->get_value_by_index( $index );
			
			$this->cValor = $valor[0];
		}
		else
		{
			// para indicadores
			// obtener todo el valor del campo
			$field_def = $iso2709_record->get_value_by_index( $index );
			
			if( count($field_def)> 0 )
			{
				$id = "";				
								
	 			if( ($pos = strpos( $field_def[0], "$iso2709_record->rgx_subfield_begin" )) )
				{
					// ubicado el inicio del primer subcampo
					$id = substr( $field_def[0], 0, $pos );
					
					if( $this->objID1 != NULL )
						{ $this->objID1->cValor = trim(substr($id,0,1)); }
					if( $this->objID2 != NULL )
						{ $this->objID2->cValor = trim(substr($id,1,1)); }
				}				

				/**if( $this->cIDCampo == "020" )
				{
					//print_r( $subfields );
					print_r($field_def);
					echo count($field_def) . "<br>";
				} **/

				$subfields = $iso2709_record->get_array_subfields( $field_def[0] );

/**				if( $this->cIDCampo == "020" )
				{
					//print_r( $subfields );
					print_r($subfields);
					echo count($subfields) . "<br>";
				} 
				*/
				
			}
			else
				$subfields = Array();

			for( $j=0; $j<count($subfields); $j++ )
			{
				// Agregar los subcampos con defaults
				// NO IMPORTA que vengan duplicados
				// $subfields[$j] vienen SIN $
				$objSubCampo = $this->BuscarSubCampo( "$" . $subfields[$j] );
				
				if( $objSubCampo == NULL )
					$objSubCampo = $this->AgregarSubCampo( "$" . $subfields[$j], "", "", "", "", "" );
				
				if( $objSubCampo != NULL )
				{
				    // 1er index = campo ( NUM o ID )
					// 2do. index = subcampo
					$field_def = $iso2709_record->get_value_by_index( $index, $j );

					if( count( $field_def ) > 0 )
					{ 
						
						$objSubCampo->cValor = trim( $field_def[0] ); 
						
						if( strlen( $objSubCampo->cValor ) > 1024 )
							$objSubCampo->cValor = substr( $objSubCampo->cValor, 0, 1024 );
						
						//echo substr($objSubCampo->cValor, strlen($objSubCampo->cValor)-1, 1 ) . ":" . ord( substr($objSubCampo->cValor, strlen($objSubCampo->cValor)-1, 1 ) ) . " ";
					}
				}
			}			
						
		}
		
		if( $depurar == 1 )
		{
			// depurar subcampos
			// eliminando todos los subcampos que no se han utilizado
			do
			{
				$bContinuar = false;
				
				$objSubCampo = NULL;
				$index = -1;
				
				for( $j=0; $j<$this->ContarSubcampos(); $j++ )
				{
					$objSubCampo = $this->ObtenerSubCampo($j);
					
					if( $objSubCampo->cValor == "" )
					{
						$bContinuar = true;
						$index = $j;
						break;
					}
				}

				if( $index != -1 )
				{
					$objSubCampo->destroy();
					array_splice( $this->subcampos, $index, 1 );
				}
			}
			while ( $bContinuar );
			
		}
	}
}

/**
 * Registro MARC 21 
 */
class record_MARC21 
{
	var $aCamposMarc;
	
	var $dbx; // pointer a un objeto DB

    // Datos del LEADER / Encabezado
    var $FLongitudRegistro;
    var $FEstadoRegistro;
    var $FTipoRegistro;
    var $FNivelBibliografico;
    var $FTipoControl;
    var $FCodigoEsquemaChars;
    var $FConteoIndicadores;
    var $FConteoCodigoSubcampo;
    var $FDireccionBase_Datos;
    var $FNivelCodificacion;
    var $FFormaCatalogacion;
    var $FNivelRegistro_Recurso;
    var $FMapaEntradas;

	// PROPIEDAD DIFERENCIADORA DEL TIPO DE MATERIAL
	// ES DE CONTROL EXTERNO AL LEADER
	var $cTipoMaterial;	
	
	// Datos del campo 008 {TODOS LOS MATERIALES}
	// propiedades Campo 008 que aplican para todos los materiales
	// hay otras propiedades del mismo campo que
	// solo aplican para ciertos materiales según
	// lo que haya en el campo 006:00
	//
	var $F008_FechaIngresoRegistro;
	var $F008_TipoFechaEstadoPub;
	var $F008_Fecha_1;
	var $F008_Fecha_2;
	var $F008_LugarPublicacion;
	var $F008_Idioma;
	var $F008_RegistroModificado;
	var $F008_FuenteCatalogacion;
	
	var $nIDBiblioteca;
	var $nIDTitulo;	
	var $nIDSerie;
	
	// cataloging stuff
	var $bAgregar_Puntuacion_Automatica;
	var $bObligatorio_MARC100;

	var $imgIcono = "";

	var $bPortada;
	var $bContraportada;

	var $usuario_registro;
	var $fecha_registro;
	var $usuario_modifico;
	var $fecha_modificacion;
	var $usuario_catalogo;
	var $fecha_catalogacion;

	var $language;
	var $digital;
	
	var $aArrayAuthorities;

    function record_MARC21( $ib_biblioteca, $dbx, $pref_language=1 )
	{
		$this->nIDBiblioteca 		= $ib_biblioteca;
		$this->cNombreBiblioteca    = "";
		$this->nIDSerie      		= 0;
		$this->nIDTitulo      		= 0;
		
		$this->dbx = $dbx;  // Inicializar puntero a la base de datos
		
		$this->aCamposMarc 			= array();
		$this->aArrayAuthorities  	= Array();
		
		$this->bAgregar_Puntuacion_Automatica = false;
		$this->bObligatorio_MARC100		  	  = false;

		$this->bPortada = false;
		$this->bContraportada = false;
		
		$this->cTipoMaterial = "";
		
		$this->LoadConfig();
		
		// Leader elements
		$this->FLongitudRegistro      = 0;
		$this->FEstadoRegistro        = "";
		$this->FTipoRegistro          = "";
		$this->FNivelBibliografico    = "";
		$this->FTipoControl           = "";
		$this->FCodigoEsquemaChars    = "";
		$this->FConteoIndicadores     = 0;
		$this->FConteoCodigoSubcampo  = 0;
		$this->FDireccionBase_Datos   = 0;
		$this->FNivelCodificacion     = "";
		$this->FFormaCatalogacion     = "";
		$this->FNivelRegistro_Recurso = "";
		$this->FMapaEntradas          = "";

		// elementos generales del campo 008
		$this->F008_FechaIngresoRegistro = "";
		$this->F008_TipoFechaEstadoPub   = "";
		$this->F008_Fecha_1              = "";
		$this->F008_Fecha_2              = "";
		$this->F008_LugarPublicacion     = "";
		$this->F008_Idioma               = "";
		$this->F008_RegistroModificado   = "";
		$this->F008_FuenteCatalogacion   = "";		
		
		$this->usuario_registro   = 0;
		$this->fecha_registro     = "";
		$this->usuario_modifico   = 0;
		$this->fecha_modificacion = "";		
		$this->usuario_catalogo   = 0;
		$this->fecha_catalogacion = "";
		
		$this->language 		  = $pref_language;
		
		$this->digital			  = "";
    }	
	
	function destroy() 
	{
		$this->clear_record();
		
		unset( $this->aCamposMarc );
		unset( $this->aArrayAuthorities );
	}
	
	function clear_record()
	{
		foreach($this->aCamposMarc as $value) 
		{
			 $value[1]->destroy();
			 
			 //echo $value[1]->cIDCampo . ",";
		}
		
		$this->aCamposMarc = Array();
		$this->aArrayAuthorities = Array();
	}
	
	function LoadConfig()
	{
		$sql = "SELECT * FROM cfgbiblioteca_config ";
		$sql .= "WHERE ID_BIBLIOTECA=$this->nIDBiblioteca";

		$resultqry = $this->dbx->SubQuery( $sql );

		if( $row = $this->dbx->FetchRecord( $resultqry ) )
		{
			$this->bAgregar_Puntuacion_Automatica = $row["CATALOG_PUNTUACION_AUTO"] == "S";
			$this->bObligatorio_MARC100		      = $row["CATALOG_OBLIGATORIO_MARC100"] == "S";
		}		

		$this->dbx->ReleaseResultset( $resultqry );
	
	}

	//
	// Agrega un campo individual al objeto MARC21_Registro
	// 24 mar 2009: Se agrega la característica para permitir duplicar
	//
	function AgregarCampo( $cIDCodigo, $bAgregarDefaults, $bPermitirDuplicar, $bAgregarTodosSubCampos=false )
	{
		$nuevoCampo = NULL;

		$bOk = true;
		$cCodigo = "";

		$i = 0;

		$Result = NULL;

		if( !$bPermitirDuplicar  )
		{ 	
			$bOk = $this->BuscarCampo( $cIDCodigo ) == NULL; 
		}

		if( $bOk )
		{
			$nuevoCampo = new TMARC21_Campo( $cIDCodigo, $this );

			$nUltimaPosicion = -1;

			for( $i = 0; $i<count($this->aCamposMarc); $i++ )
			{
			   if( $this->aCamposMarc[$i][0] == $cIDCodigo )
				  { $nUltimaPosicion = $i; }
			}

			if( $nUltimaPosicion != -1 )
			{   
				//VERIFICACION echo "Campo ya en lista $nUltimaPosicion "	;
				array_splice( $this->aCamposMarc, $nUltimaPosicion, 0, Array( Array( $cIDCodigo, $nuevoCampo ) ) ); 		
			}
			else
				{ $this->aCamposMarc[] = Array( $cIDCodigo, $nuevoCampo ); }

			$nuevoCampo->ID = $nuevoCampo->ObtenerID();

			if( $bAgregarDefaults )
			{
				$nuevoCampo->notFound = true;
				
				// hacer query a codigo MARC
				$sql = "SELECT a.NIVEL_MARC, a.OBSOLETO, a.CODIGO, a.SUBCODIGO, a.DESCRIPCION, a.DESCRIPCION_ORIGINAL, a.NOTA, a.URL, a.TESAURO, a.AUTOMATICO, a.CONECTOR_AACR ";
				$sql .= "FROM marc_codigo21 a ";
				$sql .= "WHERE (a.ID_CAMPO='$cIDCodigo' and (a.NIVEL_MARC=1 or a.NIVEL_MARC=5 or a.NIVEL_MARC=6 or a.NIVEL_MARC=9)) and (a.OBSOLETO!='S') ";
				$sql .= "ORDER BY NIVEL_MARC, CODIGO ";

				$resultqry = $this->dbx->SubQuery( $sql );

				while( $row = $this->dbx->FetchRecord( $resultqry ) )
				{
					if( $row["NIVEL_MARC"] == 1 ) 
					{
						$nuevoCampo->cDescripcion_ESP = $row["DESCRIPCION"];	
						$nuevoCampo->cDescripcion_ENG = $row["DESCRIPCION_ORIGINAL"];
						$nuevoCampo->cNota            = $row["NOTA"];
						$nuevoCampo->bObsoleto        = $row["OBSOLETO"] == "S";
						$nuevoCampo->bAutomatico      = $row["AUTOMATICO"] == "S";
						$nuevoCampo->ActualizaRepetible();
						$nuevoCampo->cUrl             = $row["URL"];
						
						$nuevoCampo->notFound 		  = false;  // agregado 07-oct-2009
					}
					else if ( $row["NIVEL_MARC"] == 5 )
					{
						if( $row["OBSOLETO"] != "S" )
						{
							if( $row["CODIGO"] == "X1" )
							    $nuevoCampo->AgregarIdentificador( 1,
															   $row["DESCRIPCION"],
															   $row["DESCRIPCION_ORIGINAL"], "" );
							else if ($row["CODIGO"] == "X2" )
								$nuevoCampo->AgregarIdentificador( 2,
															   $row["DESCRIPCION"],
															   $row["DESCRIPCION_ORIGINAL"], "" );
					   }
					}
					else if ( $row["NIVEL_MARC"] == 6 )
					{
						// valores disponibles para indicadores
						if( $row["CODIGO"] == "X1" )
						{
							if (($nuevoCampo->objID1 != NULL) and ($row["OBSOLETO"] != "S" ) )
							{
								$nuevoCampo->objID1->AgregarValorPosible( $row["SUBCODIGO"],
																		  $row["DESCRIPCION"],
																		  $row["DESCRIPCION_ORIGINAL"] );

								if( count( $nuevoCampo->objID1->aValoresPosibles) == 1 )
									$nuevoCampo->objID1->cValor = $row["SUBCODIGO"];
							}
						}
						else if ( $row["CODIGO"] == "X2" )
						{
							if (($nuevoCampo->objID2 != NULL) and ($row["OBSOLETO"] != "S"))
							{
								$nuevoCampo->objID2->AgregarValorPosible( $row["SUBCODIGO"],
																			$row["DESCRIPCION"],
																				$row["DESCRIPCION_ORIGINAL"] );

								if( count( $nuevoCampo->objID2->aValoresPosibles) == 1)
									$nuevoCampo->objID2->cValor = $row["SUBCODIGO"];
							}
						}
					}
					else if( $row["NIVEL_MARC"] == 9 )
					{
						//$nuevoCampo->bSinSubCampos = false;

						if( $nuevoCampo->ContarSubCampos() == 0 or $bAgregarTodosSubCampos )
						{
							// Solamente agregará el primer SUBCAMPO alfabético
							$cCodigo = $row["CODIGO"];

							if( substr($cCodigo, 0, 1) == "$" )
								$cCodigo = substr( $cCodigo, 1, 5 );

							if( strlen( $cCodigo ) > 0 )
							{
								
								if( !is_numeric( $cCodigo ) )
								{
									$nuevoCampo->AgregarSubCampo( $row["CODIGO"],
															 	  $row["DESCRIPCION"],
																  $row["DESCRIPCION_ORIGINAL"], 
																  "",
																  $row["TESAURO"],
																  $row["CONECTOR_AACR"] );
								}
							}
						}
					}
				}  /* while */
		   
				$this->dbx->ReleaseResultset( $resultqry );
			}

			$Result = $nuevoCampo;
		}

		return $Result;
	}

	function EliminarCampo( $objCampo )	 // TMARC21_Campo
	{
		$result = false;

		$ndx = -1;

		if( $objCampo != null )
		{
			for( $i = 0; $i<count( $this->aCamposMarc ); $i++ )
			{
				$xObjMarc21 = $this->ObtenerCampoMARC( $i );

				if( $xObjMarc21 == $objCampo )
				{
				   $ndx = $i;
				   $result = true;
				   break;
				}
			}
		}

	  if(($result) and ($ndx!=-1))
	  {
			$xObjMarc21->destroy();
			array_splice( $this->aCamposMarc, $ndx, 1 ); 		
	  }
	}
	
	function ContarCampos()
	{
	   return count( $this->aCamposMarc );
	}

	function ObtenerCampoMARC( $idx )
	{
		if( $idx <= count( $this->aCamposMarc ) )
		{
			return $this->aCamposMarc[$idx][1];
		}
	  else
		{
			return NULL;
		}
	}
	
	function BuscarCampo( $id_campo )
	{
		foreach( $this->aCamposMarc as $value )
		{
			if( $value[0] == $id_campo )
			   return $value[1];
		}
		
		return NULL;
	}
	
	function BuscarCampoMARC_X_ID( $cID )
	{
		$Result = NULL;

		foreach($this->aCamposMarc as $value) 
		{
			$xObjCampo = $value[1];

			if( $xObjCampo->ID == $cID )
			{
			   $Result = $xObjCampo;
			   break;
			}
		}
		
		return $Result;
	}
	
	//
	// obtiene el index para cada objeto TMARC21_Campo
	//
	function ObtenerIndexCampo( $objCampo )
	{
		$Result = -1;
		
		for( $i = 0; $i<count($this->aCamposMarc) && $Result==-1; $i++ )
		{
			if( $this->aCamposMarc[$i][1] == $objCampo )
			{ 
				$Result = $i;
			}
		}
		
		return $Result;		
	}
	
	function InicializarRegistroMARC21_DesdePlantilla( $nPlantilla, $bForceHeader=false, $bForceGeneral=false )
	{

		if( $bForceHeader )
		{	
			$this->AgregarCampo( "$$$", true, false );
		}

		if( $bForceGeneral )
		{	
			$this->AgregarCampo( "008", true, false );
		}

		$sql  = "SELECT z.ID_TIPOMATERIAL, a.ID_CAMPO, a.DATO, a.VALOR_DEFAULT, b.DESCRIPCION, b.DESCRIPCION_ORIGINAL, b.OBSOLETO, b.URL, ";
		$sql .= "        x.DESCRIPCION AS DEFAULT_DESCRIP, x.DESCRIPCION_ORIGINAL AS DEFAULT_DESCRIP_ORIGINAL,";
		$sql .= "         c.DESCRIPCION AS ID_DESCRIP, c.DESCRIPCION_ORIGINAL AS ID_DESCRIP_ORIGINAL, ";
		$sql .= "          d.DESCRIPCION AS SUBC_DESCRIP, d.DESCRIPCION_ORIGINAL AS SUBC_DESCRIP_ORIGINAL, d.CONECTOR_AACR, d.TESAURO ";
		$sql .= "FROM cfgplantillas a ";
		$sql .= " LEFT JOIN cfgplantillas_nombres z ON (z.ID_BIBLIOTECA=a.ID_BIBLIOTECA and z.ID_PLANTILLA=a.ID_PLANTILLA) ";
		$sql .= "  LEFT JOIN marc_codigo21 b ON (b.ID_CAMPO=a.ID_CAMPO and b.NIVEL_MARC=1)";
		$sql .= "   LEFT JOIN marc_codigo21 x ON (x.ID_CAMPO=a.ID_CAMPO and x.CODIGO=a.DATO and x.SUBCODIGO=a.VALOR_DEFAULT and x.NIVEL_MARC=3)";
		$sql .= "    LEFT JOIN marc_codigo21 c ON (c.ID_CAMPO=a.ID_CAMPO and c.CODIGO=a.DATO and c.NIVEL_MARC=5) ";
		$sql .= "      LEFT JOIN marc_codigo21 d ON (d.ID_CAMPO=a.ID_CAMPO and d.CODIGO=a.DATO and d.NIVEL_MARC=9)";
		$sql .= " WHERE (a.ID_BIBLIOTECA=$this->nIDBiblioteca and a.ID_PLANTILLA=$nPlantilla) ";
		$sql .= "ORDER BY a.ID_CAMPO, a.DATO";
		
		$resultqry = $this->dbx->SubQuery( $sql );
		
		$warning_sent = false;

		while( $row = $this->dbx->FetchRecord( $resultqry ) )
		{
 			$objCampo = $this->BuscarCampo( $row["ID_CAMPO"] );

			if( $this->cTipoMaterial == "" )
			{
				$this->cTipoMaterial = $row["ID_TIPOMATERIAL"];
				
				if( $this->cTipoMaterial == "" )
				{
					if( !$warning_sent )
					{
						$warning_sent = true;
						echo "WARNING: TYPE OF MATERIAL NOT stablished on template.! <br>";
						echo "ADVERTENCIA: El Tipo de material no ha sido establecido en la Plantilla de Catalogación. ";
					}
				}
			}
			
			if( $objCampo == NULL )
			{
				 $objCampo = new TMARC21_Campo( $row["ID_CAMPO"], $this );
				 $objCampo->cDescripcion_ESP = $row["DESCRIPCION"];
				 $objCampo->cDescripcion_ENG = $row["DESCRIPCION_ORIGINAL"];
				 $objCampo->cNota            = "";
				 $objCampo->bObsoleto        = $row["OBSOLETO"] = "S"; 
				 $objCampo->cUrl             = $row["URL"];				 
				 
				 $objCampo->ActualizaRepetible();
				 
				 $this->aCamposMarc[] = Array( $row["ID_CAMPO"], $objCampo );
			}

			if ($row["ID_CAMPO"] == "$$$")
			{
				 // agrega elementos de NIVEL_MARC 3
				 // CABECERA
				 if( $row["DATO"] == "05") $this->FEstadoRegistro = $row["VALOR_DEFAULT"];
				 if( $row["DATO"] == "06") $this->FTipoRegistro = $row["VALOR_DEFAULT"];
				 if( $row["DATO"] == "07") $this->FNivelBibliografico = $row["VALOR_DEFAULT"];
				 if( $row["DATO"] == "08" ) $this->FTipoControl = $row["VALOR_DEFAULT"];
				 if( $row["DATO"] == "17" ) $this->FNivelCodificacion = $row["VALOR_DEFAULT"];
				 if( $row["DATO"] == "18" ) $this->FFormaCatalogacion = $row["VALOR_DEFAULT"];
			}
			else if( $row["ID_CAMPO"] == "008" )
			{
				 if ( $row["DATO"] == "00..05" ) $this->F008_FechaIngresoRegistro = $row["VALOR_DEFAULT"];
				 else if( $row["DATO"] == "06" ) $this->F008_TipoFechaEstadoPub   = $row["VALOR_DEFAULT"];
				 else if( $row["DATO"] == "07..10" ) $this->F008_Fecha_1   			 = $row["VALOR_DEFAULT"];
				 else if( $row["DATO"] == "11..14" ) $this->F008_Fecha_2   			 = $row["VALOR_DEFAULT"];
				 else if( $row["DATO"] == "15..17" ) $this->F008_LugarPublicacion     = $row["VALOR_DEFAULT"];
				 else if( $row["DATO"] == "35..37" ) $this->F008_Idioma               = $row["VALOR_DEFAULT"];
				 else if( $row["DATO"] == "38" )     $this->F008_RegistroModificado   = $row["VALOR_DEFAULT"];
				 else if( $row["DATO"] == "39" )   	 $this->F008_FuenteCatalogacion   = $row["VALOR_DEFAULT"];
			}
			else if( $row["DATO"] == "X1" )
			{
				$objCampo->AgregarIdentificador( 1, $row["ID_DESCRIP"], $row["ID_DESCRIP_ORIGINAL"], $row["VALOR_DEFAULT"] );
			}
			else if( $row["DATO"] == "X2" )
			{
				$objCampo->AgregarIdentificador( 2, $row["ID_DESCRIP"], $row["ID_DESCRIP_ORIGINAL"], $row["VALOR_DEFAULT"] );
			}
			else if( $row["DATO"] != "" )
			{
				 if( substr( $row["DATO"], 0, 1 ) == "$" )
					$objCampo->AgregarSubCampo( $row["DATO"], $row["SUBC_DESCRIP"], $row["SUBC_DESCRIP_ORIGINAL"],
												 $row["VALOR_DEFAULT"], $row["TESAURO"], $row["CONECTOR_AACR"] );
			}

		} /* while */
		   
		$this->dbx->ReleaseResultset( $resultqry );
	}
	
	//
	// Inicializa un registro MARC desde la base de datos
	// Exclusivamente para un título
	//
	// Inicialmente esta función se utilizará en Busquedas.pas
	//	
	function InicializarRegistroMARC21_DesdeBD_Titulo( $id_titulo )
	{
		$this->nIDTitulo = $id_titulo;
		$this->usuario_registro   = 0;
		$this->fecha_registro     = "";
		$this->usuario_modifico   = 0;
		$this->fecha_modificacion = "";		
		$this->usuario_catalogo   = 0;
		$this->fecha_catalogacion = "";		
		
		// titulo
		$sql  = "SELECT a.ID_SERIE, a.ID_TIPOMATERIAL, a.USUARIO_REGISTRO, a.USUARIO_MODIFICO, a.USUARIO_CATALOGO, a.FECHA_REGISTRO, a.FECHA_MODIFICACION, a.FECHA_CATALOGACION, a.DIGITAL, b.NOMBRE_BIBLIOTECA ";
		$sql .= "FROM acervo_titulos a ";
		$sql .= " LEFT JOIN cfgbiblioteca b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA) ";
		$sql .= "WHERE (a.ID_BIBLIOTECA=$this->nIDBiblioteca and a.ID_TITULO=$this->nIDTitulo)";
		
		if( $row = $this->dbx->FetchRecord( ($resultqry = $this->dbx->SubQuery( $sql ))) )
		{
			$this->cNombreBiblioteca  = $row["NOMBRE_BIBLIOTECA"];
			
			$this->nIDSerie		      = $row["ID_SERIE"];
			
			if( $this->nIDSerie == "" )
				$this->nIDSerie = 0;
			
			$this->usuario_registro = $row["USUARIO_REGISTRO"];
			$this->fecha_registro   = get_str_datetime($row["FECHA_REGISTRO"], 0);
			
			$this->usuario_modifico   = $row["USUARIO_MODIFICO"];
			$this->fecha_modificacion = get_str_datetime( $row["FECHA_MODIFICACION"], 0 );
			
			$this->usuario_catalogo   = $row["USUARIO_CATALOGO"];
			$this->fecha_catalogacion = get_str_datetime( $row["FECHA_CATALOGACION"], 0 );
			
			$this->digital			  = $row["DIGITAL"];
			
			$this->cTipoMaterial	  = $row["ID_TIPOMATERIAL"];
		}
		
		$this->dbx->ReleaseResultset( $resultqry );
	
		$sql  = "SELECT a.ID_DESCRIPTOR, a.ID_CAMPO, a.CODIGO, a.SUBCODIGO, a.ID1, a.ID2, a.VALOR, ";
		$sql .= "       b.DESCRIPCION, b.DESCRIPCION_ORIGINAL, b.OBSOLETO, b.URL, ";
		$sql .= "        x.DESCRIPCION AS DEFAULT_DESCRIP, x.DESCRIPCION_ORIGINAL AS DEFAULT_DESCRIP_ORIGINAL,";		
		$sql .= "         c.DESCRIPCION AS SUBC_DESCRIP, c.DESCRIPCION_ORIGINAL AS SUBC_DESCRIP_ORIGINAL, c.CONECTOR_AACR, c.TESAURO, ";
	    $sql .= " (SELECT e.CODIGO FROM marc_codigo21 e WHERE e.ID_CAMPO=a.ID_CAMPO and e.CODIGO='X1' and e.NIVEL_MARC=5 and e.OBSOLETO<>'S') AS HAY_ID1, ";
		$sql .= "   (SELECT f.CODIGO FROM marc_codigo21 f WHERE f.ID_CAMPO=a.ID_CAMPO and f.CODIGO='X2' and f.NIVEL_MARC=5 and f.OBSOLETO<>'S') AS HAY_ID2  ";	
		$sql .= "FROM acervo_catalogacion a ";
        $sql .= " LEFT JOIN marc_codigo21 b ON (b.ID_CAMPO=a.ID_CAMPO and b.NIVEL_MARC=1) ";
		$sql .= "  LEFT JOIN marc_codigo21 x ON (x.ID_CAMPO=a.ID_CAMPO and x.CODIGO=a.CODIGO and x.SUBCODIGO=a.VALOR and x.NIVEL_MARC=3)";
		$sql .= "    LEFT JOIN marc_codigo21 c ON (c.ID_CAMPO=a.ID_CAMPO and c.CODIGO=a.CODIGO and c.NIVEL_MARC=9) ";
        $sql .= "WHERE (a.ID_BIBLIOTECA=$this->nIDBiblioteca and a.ID_TITULO=$this->nIDTitulo) ";
        $sql .= "ORDER BY a.ID_DESCRIPTOR ";
		//$sql .= "ORDER BY a.ID_CAMPO, a.CODIGO";
		
		$resultqry = $this->dbx->SubQuery( $sql );
		
		// UN ID se generará por CAMPO + ID_DESCRIPTOR
		$last_campo = "";
		$last_campo_unique_id = "";
		
		$real_fields = 0;

		while( $row = $this->dbx->FetchRecord( $resultqry ) )
		{			
 			if( $last_campo == $row["ID_CAMPO"] and $row["CODIGO"]=="")
			{
				$last_campo = "";
			}
			
			if( $last_campo != $row["ID_CAMPO"] )
			{
				$real_fields++;
				
				$last_campo = $row["ID_CAMPO"];
				
				if( $last_campo_unique_id != ($row["ID_CAMPO"] . "_" . $real_fields) ) 
				{
					$last_campo_unique_id = ($row["ID_CAMPO"] . "_" . $real_fields);
				}
			}

			$objCampo = $this->BuscarCampoMARC_X_ID( $last_campo_unique_id );

			if( $objCampo == NULL )
			{
				 $objCampo = new TMARC21_Campo( $row["ID_CAMPO"], $this );
				 $objCampo->cDescripcion_ESP = $row["DESCRIPCION"];
				 $objCampo->cDescripcion_ENG = $row["DESCRIPCION_ORIGINAL"];
				 $objCampo->cNota            = "";
				 $objCampo->bObsoleto        = $row["OBSOLETO"] = "S"; 
				 $objCampo->cUrl             = $row["URL"];				 
				 
				 $objCampo->ActualizaRepetible();
				 
				 $objCampo->ID = $last_campo_unique_id;

				 $this->aCamposMarc[] = Array( $row["ID_CAMPO"], $objCampo );
			}

			if ($row["ID_CAMPO"] == "$$$")
			{
				// agrega elementos de NIVEL_MARC 3
				// CABECERA
				$this->InicializarCabecera( $row["VALOR"] );
			}
			else if( $row["ID_CAMPO"] == "008" )
			{
				//$valor_campo008 = $row["VALOR"];
				$this->InicializarValoresCampo008( $row["VALOR"] );
			}			
			else
			{
				if( trim($row["ID1"]) != "" or $row["HAY_ID1"] != "" )
				{
					//echo $row["ID_CAMPO"] . "=" . $row["ID1"] . " ";
					if( $objCampo->objID1 == NULL )
					{ $objCampo->AgregarIdentificador( 1, $row["DEFAULT_DESCRIP"], $row["DEFAULT_DESCRIP_ORIGINAL"], trim($row["ID1"]) ); }
				}

				if( trim($row["ID2"]) != "" or $row["HAY_ID2"] != "" )
				{
					if( $objCampo->objID2 == NULL )
					{ $objCampo->AgregarIdentificador( 2, $row["DEFAULT_DESCRIP"], $row["DEFAULT_DESCRIP_ORIGINAL"], trim($row["ID2"]) ); }
				}
			}

			if($row["CODIGO"] != "")
			{
				if( substr( $row["CODIGO"], 0, 1 ) == "$" )
				{
					$objCampo->AgregarSubCampo( $row["CODIGO"], $row["SUBC_DESCRIP"], $row["SUBC_DESCRIP_ORIGINAL"],
												 $row["VALOR"], $row["TESAURO"], $row["CONECTOR_AACR"] );
												 
				}
			}
			else
			{
				if( $row["CODIGO"] == "" and $row["SUBCODIGO"]=="" and $row["VALOR"] != "" )
				{
					if ($row["ID_CAMPO"] == "001")
					{
						$objCampo->cValor = $id_titulo; // el valor real de 001 debería ser el mismo ID_TITULO
														// con este forzamos para que siempre lo sea
					}
					else					
						$objCampo->cValor = $row["VALOR"];
				}
			}	// if($row["CODIGO"] != "")
		}		

		$this->dbx->ReleaseResultset( $resultqry );

	}

	//
	// record_MARC21.ConcatenarValores
	//
	function ConcatenarValores( $cIDCampo, $cIDSubCampo="", $bSoloCamposALFABETICOS=false, $bConcatenarCON=" ", $Cond_ID1="", $Cond_ID2="" )
	{
		$ret = "";
		$mismocampo = 0;
		
		// HACER UN LOOP porque puede haber varios campos repetivos
		foreach( $this->aCamposMarc as $value )
		{
			if( $value[0] == $cIDCampo )
			{		
				$mismocampo++;
				
				$objCampo = $value[1];
				
				if( $cIDSubCampo == "" )
				{
					$bEval = false;
			
					if( $Cond_ID1 == "" and $Cond_ID2 == "" )
					{
						$bEval = true;
					}
					else if( $Cond_ID1 != "" and $Cond_ID2 == "" )
					{
						// una soal  condicion en ID1
						$bEval = $objCampo->objID1->cValor == $Cond_ID1;
					}
					else if( $Cond_ID1 != "" and $Cond_ID2 != "" )
					{
						// ambas condiciones en ID1 e ID2
						$bEval = ($objCampo->objID1->cValor == $Cond_ID1) and
								 ($objCampo->objID2->cValor == $Cond_ID2);
					}
					else if( $Cond_ID1 == "" and $Cond_ID2 != "" )
					{
						// una sola condicion en ID2
						$bEval = $objCampo->objID2->cValor == $Cond_ID2;
					}
					
					if( $bEval )
					{				
						$tmpval = $objCampo->ConcatenarValores( $bConcatenarCON, false, $bSoloCamposALFABETICOS );
						
						if( $tmpval != "" )
						{
							if( $ret != "" )
								$ret .= $bConcatenarCON;
							
							$ret .= $tmpval;
						}

						if( $Cond_ID1 <> "" )
							echo " $cIDCampo " ;
					}
					
				}
				else
				{
					$subcampo = $objCampo->BuscarSubcampo( $cIDSubCampo );
					
					if( $subcampo != NULL )
						$ret .= $subcampo->cValor;
				}
				
			}
		}
		
		return $ret;
	}
	
	// record_MARC21.Icono
	function Icono( $imgfile )
	{	
		$this->imgIcono = $imgfile;
	}
	
	// record_MARC21.ImprimeEncabezado	
	function ImprimeEncabezado( $estilo )
	{
	}
	
	// record_MARC21.ObtenerCallNumber	
	function ObtenerCallNumber( $prefix, $class_no, $book_no )
	{
		return $prefix . " " . $class_no . " " . $book_no;
	}
	
	//
	// Imprime una ficha condensada
	// que permite colocarse en una lista
	//
	// $mostrarLink4Titulo - Permite mostrar/ocultar el link hacia el título
	//
	// record_MARC21.ImprimeFicha
	//	
	function ImprimeFicha( $estilo, $keywords, $campoPortada = NULL, $campoContraportada = NULL, $mostrarLink4Titulo=1, $parameterslink = "", 
		$showButtonActionGo="", $mostrar_ejemplares=0, $elegir_items_prestados=0, $mostrar_recursos_digitales=0, $mostrar_liga_recurso_digital=0 )
	{
		global $LBL_ID_MATERIAL, $LBL_CALLNUMBER;
		
		if( $estilo == 1 )
		{
			$cValor = $this->ConcatenarValores('245');

			$cValor100 = $this->ConcatenarValores('100');

			$cValor260 = $this->ConcatenarValores('260');
			$cValor300 = $this->ConcatenarValores('300');
			
			$cDeweyCallNumber = $this->ConcatenarValores('082');
			
			$cISBN = $this->ConcatenarValores('020');

			echo "<td valign=top class='cuadricula columna' width=5%>";
						
			if( $showButtonActionGo != "" and $mostrar_ejemplares==0 )
			{
				$val = $showButtonActionGo;
				
				// SE SUSTITUYE EL COMODIN ? por el ID_TITULO
				$val = str_replace( "?", $this->nIDTitulo, $val );
				// SE SUSTITUYE EL COMODIN @ por el TITULO
				$val = str_replace( "@", "\"" . $cValor . "\"", $val );
				
				// SE SUSTITUYE EL COMODIN % por el ICONO
				$val = str_replace( "%", "\"" . $this->imgIcono . "\"", $val );
				// PROBABLEMENTE OTROS PARAMETROS
				
				echo "<input type=button class=boton value='$this->nIDTitulo&raquo;' onClick='$val'>";
			}
			else
				echo "$this->nIDTitulo";
			
			echo "</td>\n";
			
			echo "<td class='cuadricula columna' valign=top width=75%>";
			
			if( $mostrarLink4Titulo == 1 )
			{
				echo "<a onMouseOver='return js_Status(\"\");' href=gral_vertitulo.php?id_biblioteca=$this->nIDBiblioteca&id_titulo=$this->nIDTitulo&" . $parameterslink . ">" . hightlight( $cValor, $keywords ) . "</a>";
			}
			else
				echo hightlight( $cValor, $keywords );

			if( $cValor100 != "" )
			{
				echo "<br/>";
				echo "<span style='font-size: 95%'>";
				echo $cValor100;
				echo "</span>";
			}						

			if( $cValor260 != "" )
			{
				echo "<br/>";
				echo "<span style='font-size: 95%'>";
				echo $cValor260;
				echo "</span>";
			}

			if( $cValor300 != "" )
			{
				echo "<br/>";
				echo "<span style='font-size: 95%'>";
				echo $cValor300;
				echo "</span>";
			}
			
			if( $cDeweyCallNumber != "" )
			{
				echo "<br>";
				echo "<span style='font-size: 95%'>";
				echo "Clasificaci&oacute;n " . $cDeweyCallNumber;
				echo "</span>";
			}
			
			if( $cISBN != "" )
			{
				echo "<br>";
				echo "<span style='font-size: 95%'>";
				echo "ISBN " . $cISBN;
				echo "</span>";
			}

			echo "<br><br>";
			
			// Verificar si deseamos mostrar ejemplares
			if( $mostrar_ejemplares == 1 )
			{
				$sql = "SELECT a.*, d.DESCRIPCION AS DESCRIP_CATEGORIA_PRESTAMO, e.DESCRIPCION AS DESCRIP_UBICACION, e.NOTAS_UBICACION " . 
						"FROM acervo_copias a " . 
						"  LEFT JOIN cfgbiblioteca b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA) " .
						"    LEFT JOIN tesauro_terminos_categorias c ON (c.ID_RED=b.ID_RED and c.ID_CATEGORIA=6 and c.ID_TERMINO=a.CATEGORIA_PRESTAMO)" .
						"     LEFT JOIN tesauro_terminos d ON (d.ID_RED=b.ID_RED and d.ID_TERMINO=a.CATEGORIA_PRESTAMO) " .
						"            LEFT JOIN cfgubicaciones e ON (e.ID_BIBLIOTECA=a.ID_BIBLIOTECA and e.ID_UBICACION=a.ID_UBICACION) " .
						"WHERE a.ID_BIBLIOTECA=$this->nIDBiblioteca and a.ID_TITULO=$this->nIDTitulo " .
						"ORDER BY a.ID_ITEM ";				
						
				$resultqry = $this->dbx->SubQuery( $sql );

				$ejemplares = 0;
				while( $row = $this->dbx->FetchRecord( $resultqry ) )
				{
					$ejemplares++;
					
					if( $ejemplares == 1 )
					{
						echo "<div><table width=85% border=0 align=right>";
						echo "<tr>" .
						     "<td class='cuadricula columna columnaEncabezado'>$LBL_ID_MATERIAL</td>".
							 "<td class='cuadricula columna columnaEncabezado'>$LBL_CALLNUMBER</td>".
							 "<td class='cuadricula columna columnaEncabezado'>Status</td>".
							 "</tr>";
					}

					$elemento_x_material = $row["ID_MATERIAL"];
					
					$class_extra = "";
					
					$colocar_boton_para_elegir = "N";
					
					if( $elegir_items_prestados==1 )
					{
						if( $row["STATUS"] == "P" ) $colocar_boton_para_elegir = "S";
					}
					else
						if( $row["STATUS"] == "D" ) $colocar_boton_para_elegir = "S";
					
					if( $colocar_boton_para_elegir == "S" ) 		
					{					
						$val = "";
						if( $showButtonActionGo != "")
						{
							$val = $showButtonActionGo;
							
							// SE SUSTITUYE EL COMODIN ? por el ID_TITULO
							$val = str_replace( "?", $this->nIDTitulo, $val );
							$val = str_replace( "#", "\"" . $row["ID_MATERIAL"] . "\"", $val );
							$val = str_replace( "&", "\"" . $row["ID_ITEM"] . "\"", $val );
							$val = str_replace( "@", "\"" . $cValor . "\"", $val );
							
							// SE SUSTITUYE EL COMODIN % por el ICONO
							$val = str_replace( "%", "\"" . $this->imgIcono . "\"", $val );
							
							
							// PROBABLEMENTE OTROS PARAMETROS
							
							$val = "onClick='$val'";
							
							$elemento_x_material = "<input type=button class=boton value='$elemento_x_material&nbsp;&raquo;' $val>";
						}

						$class_extra = "caja_info";
					}
					
					if( $row["STATUS"] == "B") 
						$class_extra = "caja_errores";
					
					echo "<tr>" .
					     "<td class='columna cuadricula $class_extra'>" . $elemento_x_material . "</td>" . 
						 "<td class='columna cuadricula $class_extra'>" . $this->ObtenerCallNumber( $row["SIGNATURA_PREFIJO"], $row["SIGNATURA_CLASE"], $row["SIGNATURA_LIBRISTICA"] ) . "</td>" .
						 "<td class='columna cuadricula $class_extra'>" . $this->GetItemStatus( $row["STATUS"] ) . "</td>" .
						 "</tr>";;
				}
				
				$this->dbx->ReleaseResultset( $resultqry );
				
				if( $ejemplares == 0 )
				{
					global $MSG_NO_COPIES_FOUND;
					echo "<img src='../images/icons/warning.gif'>&nbsp;$MSG_NO_COPIES_FOUND <br><br>";
				}
				else if( $ejemplares > 0 )
					echo "</table></div>";
			}
			// end - if( $mostrar_ejemplares == 1 )
			
			echo "</td>";

			echo "<td valign=top align=center class='cuadricula columna' width='10%'>";

			if( $campoPortada != NULL )
			{
				echo "<img name='theimage' src='../phps/image.php?id_biblioteca=$this->nIDBiblioteca&id_titulo=$this->nIDTitulo&tipoimagen=PORTADA' width='80'\>";
			}

			echo "</td>";

			$cEdicion = $this->ConcatenarValores('260','$c');

			echo "<td valign=top align=center class='cuadricula columna' width=5%>";

			if( $this->imgIcono != "" )
			{
				echo "<img src='../$this->imgIcono'><br>";
			}

			echo "$cEdicion<br>";
			
			if( issetsessionvar("empleado") )
			{
				
				if( getsessionvar("empleado") != "S" )
				{
					// SOLO PARA ALUMNOS
					echo "\n<br><a href='circ_bandeja.php?id_biblioteca=$this->nIDBiblioteca&id_titulo=$this->nIDTitulo&accion=1' title='Colocar en la bandeja personal'><img src='../images/bandeja.png'></a>";
				}
			}
			
			if( $mostrar_recursos_digitales == 1 )
			{
				if( $this->digital == "S" )
				{
					global $MSG_DOWNLOAD_DIGITAL_FILE, $MSG_DIGITAL_FILE_AVAILABLE;
					
					echo "<br>";
					
					if( $mostrar_liga_recurso_digital == 1 )
					{
						echo "<a href='javascript:download_digital_file( \"catalogacion\", $this->nIDTitulo, 0 );'><img src='../images/icons/download_available.png'>$MSG_DOWNLOAD_DIGITAL_FILE</a>";
					}
					else
						echo "<img src='../images/icons/download_available.png'>$MSG_DIGITAL_FILE_AVAILABLE";
					
				}
			}

			echo "</td>";
		}
	}
	
	//
	// Despliega los valores concatenados de un campo
	//
	// record_MARC21.DISPLAY_NOTE
	//
	function DISPLAY_NOTE( $cCampo, $printLabel=1, $cConstante="", $bSoloCamposALFABETICOS=false, $bConcatenarCON = " ", $Cond_ID1="", $Cond_ID2="", $pdfArray=null )
	{
		if( $cConstante != "" )
		{
			$array_tmp = Array( $cConstante );
		}
		else
		{
			$array_tmp = Array();
		
			foreach( $this->aCamposMarc as $value )
			{
				if( $value[0] == $cCampo )
				{
					if( $Cond_ID1=="" and $Cond_ID2=="" )
						$array_tmp[] = $value[1]->ConcatenarValores( $bConcatenarCON, false, $bSoloCamposALFABETICOS );
					else
					{
						$bEval = false;
						
						if( $Cond_ID1 == "" and $Cond_ID2 == "" )
						{
							$bEval = true;
						}
						else if( $Cond_ID1 != "" and $Cond_ID2 == "" )
						{
							// una soal  condicion en ID1
							$bEval = $value[1]->objID1->cValor == $Cond_ID1;
						}
						else if( $Cond_ID1 != "" and $Cond_ID2 != "" )
						{
							// ambas condiciones en ID1 e ID2
							$bEval = ($value[1]->objID1->cValor == $Cond_ID1) and
									 ($value[1]->objID2->cValor == $Cond_ID2);
						}
						else if( $Cond_ID1 == "" and $Cond_ID2 != "" )
						{
							// una sola condicion en ID2
							$bEval = $value[1]->objID2->cValor == $Cond_ID2;
						}						
						
						if( $bEval )
						{
							$array_tmp[] = $value[1]->ConcatenarValores( $bConcatenarCON, false, $bSoloCamposALFABETICOS );
						}
					}
				}
			}
			
			unset( $value );
		}

		$rows_printed = 0;
		
		if( count($array_tmp) > 0 )
		{
			for( $ij=0; $ij<count($array_tmp); $ij++ )
			{
				$this->note_index++;

				if( $pdfArray == null )
				{
					echo "<div style='display: block; overflow: auto;'>";
				}
				
				$tmp_str = "";
				
				if( $this->show_index )
				{
					if($this->show_index_as_roman)
						$tmp_str = to_roman($this->note_index) . ".";
					else
						$tmp_str =  "$this->note_index.";

					// Agrega un número a cada término
					if( $pdfArray == null )
						echo "<div style='float: left; text-align:right; width:30px;'>$tmp_str&nbsp;</div>"; // elemento enumerado
				}
				else
				{
					if( $pdfArray == null )
						echo "<div style='float: left; text-align:right; '></div>";  // elemento NO ENUMERADO
				}
				
				if( $pdfArray == null )
				{
					echo "<div style='float:left;'>";
				}
			
				if( $printLabel == 1 )
				{
					$objCampo = $this->BuscarCampo( $cCampo );
					
					if( $objCampo != NULL )
					{
						if( $pdfArray == null )
							echo "<em>" . $objCampo->ObtenerDescripcion() ."</em>";
						else
							$tmp_str .= $objCampo->ObtenerDescripcion();
					}
				}
				
				if( $pdfArray == null )
				{
					echo $array_tmp[$ij];
					echo "</div>";
					echo "</div>\n";
				}
				else
				{
					// PDF
					$tmp_str .= " " . $array_tmp[$ij];

					$pdfArray[0]->SetX( $pdfArray[1] );
					             //$pdfArray[0]->Cell( 100, 2, html_entity_decode($tmp_str), 0, 0, "L" );
					$rows = $pdfArray[0]->MultiCell( 120, 2.5, html_entity_decode($tmp_str), 0, 'J', 0 );
					
						//$pdfArray[0]->SetX( 0 );
						//$pdfArray[0]->Cell( 1, 2, $rows, 0, 0, "L" );
					
					$pdfArray[0]->Ln( 1 );

					$rows_printed += $rows;
				}
			}
		}
		
		return $rows_printed;
	}
	
	//
	// Impresión de FICHA AACR2
	// 08-may-2009
	// 
	// 15-sep-2009:  Se agrega Modalidad
	//
	//  Valores para modalidad:
	//     0 - Default
	//     1 - Por Autor
	//     2 - XYZ
	//
	function ImprimeFichaAACR2( $modalidad = 0, $border=1, $pdf=null, $pdf_offset_line=0 )
	{
		$cAutor			 = "";
		$cTitulo 	     = $this->ConcatenarValores('245');
		$cISBN  	     = $this->ConcatenarValores('020');
		
		if( $modalidad == 1 )
			$cAutor  	     = $this->ConcatenarValores('100');
		
		//$cTituloUniforme = $this->ConcatenarValores('240');
		$cEdicion	     = $this->ConcatenarValores('250');
		$cPublicacion    = $this->ConcatenarValores('260');
		
		$cDescripFisica  = $this->ConcatenarValores('300');
		
		// ENLACES / LIGAS / LINKS			
		$cLinks = $this->ConcatenarValores( "856", '$u' );		
		
		require_once "../basic/fpdf/fpdf.php";
		
		if( $pdf != null )
		{
			$rows_left = 0;
			$rows_right = 0;
			
			$line_break_space = 4;
			
			$pdf->SetXY( 5, $pdf_offset_line );
			
			for( $xyz=1; $xyz<=35; $xyz++ )
			{
				$pdf->Line( ($xyz*5), $pdf_offset_line, ($xyz*5)+2, $pdf_offset_line );
			}
			
			
			$pdf->Ln( $line_break_space );
			
			// inicia panel izq
			if( ($str = $this->ConcatenarValores( "050" )) != "" )
			{
				$pdf->SetX( 5 );
				$pdf->Cell(30,2,html_entity_decode($str),0,0,'L');
				$pdf->Ln( $line_break_space ); // Skip
				
				$rows_left++;
			}
			
			if( ($str = $this->ConcatenarValores( "080" )) != "" )
			{
				$pdf->SetX( 5 );
				$pdf->Cell(30,2,html_entity_decode($str),0,0,'L');
				$pdf->Ln( $line_break_space );  // Skip
				
				$rows_left++;
			}
			
			if( ($str = $this->ConcatenarValores( "082" )) != "" )
			{
				$pdf->SetX( 5 );
				$pdf->Cell(30,2,html_entity_decode($str),0,0,'L');
				$pdf->Ln( $line_break_space );
				
				$rows_left++;
			}
			
			if( ($str = $this->ConcatenarValores( "084" )) != "" )
			{
				$pdf->SetX( 5 );
				$pdf->Cell(30,2,html_entity_decode($str),0,0,'L');
				$pdf->Ln( $line_break_space );
				
				$rows_left++;
			}		

			if( $rows_left > 0 )
			{
				$stuff_at_left = true;
			}
				
			// fin panel izq
			
			//
			// PANEL DERECHO
			//
			// Regresar al offset inicial
			//
			$pdf->SetXY( 40, $pdf_offset_line );
			$pdf->Ln( $line_break_space );

			if( $modalidad == 1 )
			{
				$pdf->Cell( 100, 2, html_entity_decode($cAutor), 0, 0, "L" );				
				$pdf->Ln( $line_break_space );
				$rows_right++;
			}
			
			$pdf->SetX( 40 );
			$rows = $pdf->MultiCell( 120, 2.5, html_entity_decode($cTitulo), 0, 'J', 0 );
			$rows_right += $rows + 1 ;
			
			//$pdf->Ln( $line_break_space );  // Break Line
			
			if( $cEdicion != "" )
			{
				$pdf->Ln( $line_break_space );
				$rows_right++;			

				$pdf->SetX( 40 );
				$pdf->Cell( 100, 2, " -- " . html_entity_decode($cEdicion), 0, 0, "L" );
				$rows_right++;
				
			}
				
			if( $cPublicacion != "" )
			{
				$pdf->Ln( $line_break_space );
				$rows_right++;
				
				$pdf->SetX( 40 );
				$pdf->Cell( 100, 2, " -- " . html_entity_decode($cPublicacion), 0, 0, "L" );
				$rows_right++;
			}

			$cMencionSerie = "";
				
			if( $cDescripFisica != "" )
			{
				$pdf->Ln( $line_break_space );
				$rows_right++;

				$pdf->SetX( 40 );
				$pdf->Cell( 100, 2, "    " . html_entity_decode($cDescripFisica), 0, 0, "L" );
				$rows_right++;

				if( ($cMencionSerie = $this->ConcatenarValores('440')) != "" )
				{
					$pdf->Ln( $line_break_space );
					$rows_right++;

					$pdf->SetX( 40 );
					$pdf->Cell( 100, 2, " -- " . html_entity_decode($cMencionSerie), 0, 0, "L" );
					$rows_right++;
				}
				
				$pdf->Ln( $line_break_space );
				$rows_right++;				
			}			
				
			if( $cISBN != "" )
			{
				// Salto de Línea
				$pdf->Ln( $line_break_space );
				$rows_right++;	
				
				$pdf->SetX( 40 );
				$pdf->Cell( 100, 2, "I.S.B.N.  " . html_entity_decode($cISBN), 0, 0, "L" );
				$rows_right++;
			}
				
			// NOTAS
			if( ($cGeneralNote = $this->ConcatenarValores('500')) != "" )
			{
				// salto de Línea
				$pdf->Ln( $line_break_space );
				$rows_right++;

				$pdf->SetX( 40 );
				$rows = $pdf->MultiCell( 120, 2.5, html_entity_decode($cGeneralNote), 0, 'J', 0 );
				$rows_right += $rows;
			}				

			// salto de Línea
			$pdf->Ln( $line_break_space );
			$rows_right++;
			
				//
				// OTRAS NOTAS
				//
				$this->show_index = false;
			
				$row_notes_no_numbers = 0;
			
				$pdfArray = Array( $pdf, 40, $line_break_space );
				$row_notes_no_numbers += $this->DISPLAY_NOTE( '501', 1, "", false, " ", "", "", $pdfArray );
				$row_notes_no_numbers += $this->DISPLAY_NOTE( '502', 1, "", false, " ", "", "", $pdfArray );
				$row_notes_no_numbers += $this->DISPLAY_NOTE( '503', 1, "", false, " ", "", "", $pdfArray );
				$row_notes_no_numbers += $this->DISPLAY_NOTE( '504', 0, "", false, " ", "", "", $pdfArray );
				$row_notes_no_numbers += $this->DISPLAY_NOTE( '505', 0, "", false, " ", "", "", $pdfArray );
				
				$row_notes_no_numbers += $this->DISPLAY_NOTE( '520', 0, "", false, " ", "", "", $pdfArray );
				$row_notes_no_numbers += $this->DISPLAY_NOTE( '521', 0, "", false, " ", "", "", $pdfArray );
				
				$row_notes_no_numbers += $this->DISPLAY_NOTE( '538', 0, "", false, " ", "", "", $pdfArray );  // Detalles del Sistema
				
				$row_notes_no_numbers += $this->DISPLAY_NOTE( '540', 1, "", false, " ", "", "", $pdfArray );  // Condiciones de Uso
				
				$rows_right += $row_notes_no_numbers;
				
				$pdf->SetFont( "Courier", "", 7 );
				
				// TERMINOS TEMÁTICOS
				//$pdf->Line( 40, $pdf_offset_line+($rows_right*2), 230, $pdf_offset_line+($rows_right*2) );
				$pdf->SetDrawColor( 240, 240, 240 );
				$pdf->Line( 40, $pdf->GetY(), 180, $pdf->GetY() );
				
				//if( $row_notes_no_numbers > 0 )
				//{
					$pdf->Ln( $line_break_space );
					$rows_right++;				
				//}
				
				$this->show_index = true;
				$this->show_index_as_roman = false;
				$this->note_index = 0;
				
				$non_roman_notes = 0;
				
				$non_roman_notes += $this->DISPLAY_NOTE( '650', 0, "", true, " -- ", "", "", $pdfArray );  // Condiciones de Uso / Tematización
				
				$non_roman_notes += $this->DISPLAY_NOTE( '651', 0, "", true, " -- ", "", "", $pdfArray );  // Nombre Geográfica
				$non_roman_notes += $this->DISPLAY_NOTE( '652', 0, "", true, " -- ", "", "", $pdfArray );  // Término Geográfico Invertido
				$non_roman_notes += $this->DISPLAY_NOTE( '653', 0, "", true, " -- ", "", "", $pdfArray );  // Término de Indización no controlado
				
				$rows_right += $non_roman_notes;
				
				// ASIENTOS
				if( $non_roman_notes > 0 )
				{
					$pdf->Ln( $line_break_space );
					$rows_right++;
				}
				
				$roman_notes = 0;
				$this->note_index = 0;
				$this->show_index_as_roman = true;
				$roman_notes += $this->DISPLAY_NOTE( '100', 0, "", true, " -- ", "", "", $pdfArray );  // Condiciones de Uso
				$roman_notes += $this->DISPLAY_NOTE( '700', 0, "", true, " -- ", "", "", $pdfArray );  // Asiento secundario nombre personal
				$roman_notes += $this->DISPLAY_NOTE( '710', 0, "", true, " ", "", "", $pdfArray );  // Condiciones de Uso
				
				$objCampo = $this->BuscarCampo( '245' );

				if( $objCampo != NULL )
					if( $objCampo->objID1 != NULL )
					{
						if( $objCampo->objID1->cValor == "1" )
						{
							$roman_notes += $this->DISPLAY_NOTE( '245', 0, "T&iacute;tulo.", false, " ", "", "", $pdfArray );  // Condiciones de Uso
						}
					}
				
				if( $cMencionSerie != "" )
				{
					$roman_notes += $this->DISPLAY_NOTE( '440', 0, "Serie.", false, " ", "", "", $pdfArray );  // Condiciones de Uso
				}
				
				$rows_right += $roman_notes;
				
				
				$pdf->SetFont( "Arial", "", 7 );
			
			$ret_max = max( $rows_left, $rows_right );
			
			return max( $ret_max, 21 );
		}
		else
		{		
			//
			// HTML / Screen
			//
			echo "<DIV id='ficha' style='width: 100%; background: #fff; " . (($border==1) ? "border: 1px solid silver; " : "" ) . "overflow: auto;'>";

			$stuff_at_left = false;
		   
			// panel izq.
			echo "<DIV id='aacr2_panel_left' style='overflow: auto; float: left; width: 12%; height: 200px; background: #fff; padding: 5px;'>&nbsp;"; 
		   				
				if( $cLinks <> "" )
				{
					$cLink_Label = $this->ConcatenarValores( "856", '$y' );
					echo "<div class=data_info><a href='$cLinks' target='_new'>$cLink_Label</a></div><br>";
					$stuff_at_left = true;
				}
				else
				{
					$info = $this->ConcatenarValores( "050" );
					
					if( $info != "" )
						$info .= " ";
					
					$info .= $this->ConcatenarValores( "080" );
					$info .= $this->ConcatenarValores( "082" );
					$info .= $this->ConcatenarValores( "084" );
					
					if( $info != "" )
						echo "<div class=data_info>&nbsp;$info</div><br>";
					$stuff_at_left = true;
				}
		   
			echo "</DIV>";
			
			// panel der.		
			echo "<DIV id='aacr2_panel_right' style='overflow: auto; float: left; width: 84%; background: #fff; padding: 5px; " . (($stuff_at_left) ? "border-left: 2px dotted silver;" : ""). "'>"; 

				// 
				if( $modalidad == 1 )
					echo "<strong>" . $cAutor . "</strong><br>";
				//
				//
				echo "&nbsp;&nbsp;&nbsp;&nbsp;" . $cTitulo;

				if( $cEdicion != "" )
					echo " -- " . $cEdicion;
					
				if( $cPublicacion != "" )
					echo " -- " . $cPublicacion;
					
				$cMencionSerie = "";
					
				if( $cDescripFisica != "" )
				{
					echo "<br><br>";
					
					$cMencionSerie = $this->ConcatenarValores('440');

					echo "&nbsp;&nbsp;&nbsp;&nbsp;" . $cDescripFisica;
					
					if( $cMencionSerie != "" )
						echo " -- " . $cMencionSerie;
				}
				
				if( $cISBN != "" )
				{
					echo "<br><br>";
					
					echo "&nbsp;&nbsp;&nbsp;&nbsp;ISBN:&nbsp;" . $cISBN;
				}
					
				echo "<br>";
				
				// NOTAS
				$cGeneralNote = $this->ConcatenarValores('500');
				if( $cGeneralNote != "" )
				{
					echo "<br>";
					echo "<span style='font-size:91%;'>&nbsp;&nbsp;&nbsp;&nbsp;$cGeneralNote</span><br><br>\n";
				}
				
				// OTRAS NOTAS
				$this->show_index = false;
				$this->DISPLAY_NOTE( '501' );
				$this->DISPLAY_NOTE( '502' );
				$this->DISPLAY_NOTE( '503' );
				$this->DISPLAY_NOTE( '504', 0 );
				$this->DISPLAY_NOTE( '505', 0 );
				
				$this->DISPLAY_NOTE( '520' );
				$this->DISPLAY_NOTE( '521' );
				
				$this->DISPLAY_NOTE( '538', 0 );  // Detalles del Sistema
				
				$this->DISPLAY_NOTE( '540', 1 );  // Condiciones de Uso
				
				// TERMINOS TEMÁTICOS
				echo "<br><div style='width:auto; border-bottom: 2px dotted silver;'></div>";
				
				$this->show_index = true;
				$this->show_index_as_roman = false;
				$this->note_index = 0;
				
				$this->DISPLAY_NOTE( '650', 0, "", true, " -- " );  // Condiciones de Uso / Tematización
				
				$this->DISPLAY_NOTE( '651', 0, "", true, " -- " );  // Nombre Geográfica
				$this->DISPLAY_NOTE( '652', 0, "", true, " -- " );  // Término Geográfico Invertido
				$this->DISPLAY_NOTE( '653', 0, "", true, " -- " );  // Término de Indización no controlado
				
				// ASIENTOS
				echo "<br>";
				$this->note_index = 0;
				$this->show_index_as_roman = true;
				$this->DISPLAY_NOTE( '100', 0, "", true, " -- " );  // Condiciones de Uso
				$this->DISPLAY_NOTE( '700', 0, "", true, " -- " );  // Asiento secundario nombre personal
				$this->DISPLAY_NOTE( '710', 0, "", true, " " );  // Condiciones de Uso
				
				$objCampo = $this->BuscarCampo( '245' );

				if( $objCampo != NULL )
					if( $objCampo->objID1 != NULL )
					{
						if( $objCampo->objID1->cValor == "1" )
						{
							$this->DISPLAY_NOTE( '245', 0, "T&iacute;tulo." );  // Condiciones de Uso
						}
					}
				
				if( $cMencionSerie != "" )
				{
					$this->DISPLAY_NOTE( '440', 0, "Serie." );  // Condiciones de Uso
				}
				
			echo "<br>";
			
			echo "<DIV style='float: right'>";
			
			// FALTA APARECER EL NO. DE CONTROL / FECHA ABAJO DE LA FICHA AACR2
			
			echo $this->ConcatenarValores('001');
			
			if( ($cNumero = $this->ConcatenarValores('001')) != "" )
			{
				echo "&nbsp;&nbsp;&nbsp;&nbsp;ISBN:&nbsp;" . $cNumero;
			}
			
			echo "</DIV>";
			
			echo "</DIV><br>";
			echo "</DIV><br>";
			
			return 0;
		}
	
	}
	
	//
	// Impresión de FICHA MARC
	// Se mueve de anls_vertitulo.php 08-may-2009
	//	
	function ImprimeFichaMARC()
	{
	
		for( $i=0; $i<$this->ContarCampos(); $i++ )
		{
			$campoMARC21 = $this->ObtenerCampoMARC( $i );
			echo "<div style='float: left; width: 6%;'><strong>" . $campoMARC21->cIDCampo . "</strong></div>";
			
			$idVals = " ";
			
			if( $campoMARC21->objID1 != NULL )
				$idVals .= $campoMARC21->objID1->cValor;
			else
			   $idVals .= "&nbsp;&nbsp;";

			$idVals .= "&nbsp;";

			if( $campoMARC21->objID2 != NULL )
				$idVals .= $campoMARC21->objID2->cValor;
			else
			   $idVals .= "&nbsp;&nbsp;";

			// class='data_info' 
			echo "<div style='float:left;width: 4%;'>$idVals</div>" .
				 "<div style='float:left; width: 85%;'>";

			if( $campoMARC21->bCampoControl ) 
				echo $campoMARC21->cValor . "<br>";
			else
				echo $campoMARC21->ConcatenarValores( " ", true, false, "<strong>", "</strong>&nbsp;" );

			echo "</div><br style='clear:both;'>";
		}
	
	}
	
	//
	// Impresión de FICHA con Etiquetas
	//	
	function ImprimeFichaEtiquetas( $showLibraryName=0, $class4boxes="" )
	{
		global $LBL_CARD_LIBRARYNAME, $LBL_CARD_AUTHOR, $LBL_CARD_ADDED_TITLE, $LBL_CARD_TITLE, $LBL_CARD_PUBLISHING, $LBL_CARD_PUBLISHER_NUMBER, $LBL_CARD_EDITION;
		
		global $LBL_CARD_SERIES, $LBL_CARD_CALLNUMBER, $LBL_CARD_ADDED_ENTRY, $LBL_CARD_OTHER_TITLES, $LBL_CARD_CLASIF_LC, $LBL_CARD_CLASIF_DEWEY;
		
		global $LBL_CARD_DESCRIPTION, $LBL_CARD_SUMMARY;
		global $LBL_CARD_NOTES;
		
		global $LBL_CARD_TARGET_AUDIENCE;
		
		global $LBL_CARD_OTHERNAME, $LBL_CARD_LANGUAGE;
		
		global $LBL_CARD_PERFORMER, $LBL_CARD_PRODUCTION_CREDITS;
		
		//
		// Datos Principales
		//
		$classname = "";
		
		if( $class4boxes != "" )
			$classname = "class='$class4boxes'";
		
		echo "\n\n<div $classname>";
		
		if( $showLibraryName == 1 )
			echo "<div class=label_info>$LBL_CARD_LIBRARYNAME</div><div class=data_info>$this->cNombreBiblioteca</div><br style='clear:both'><br>"; 
		
		// visualización estándar
		if( ($cAutor = $this->ConcatenarValores("100")) != "" )
		{ 
			echo "<div class=label_info>$LBL_CARD_AUTHOR</div><div class=data_info>$cAutor</div><br style='clear:both'>"; 
		}
		
		if( ($cTituloUniforme = $this->ConcatenarValores("240")) <> "" )
		{
			echo "<div class=label_info>$LBL_CARD_ADDED_TITLE</div><div class=data_info>$cTituloUniforme</div><br style='clear:both'>";
		}
		
		if( ($cTitulo = $this->ConcatenarValores("245")) <> "" )
		{
			echo "<div class=label_info>$LBL_CARD_TITLE</div><div class='data_info'>$cTitulo</div><br style='clear:both'>";
		}
		
		if( ($cSerie = $this->ConcatenarValores("440")) <> "" )
		{
			echo "<div class='label_info'>$LBL_CARD_SERIES</div><div class='data_info'>$cSerie</div><br>";
		}
		
		if( ($cPublicacion = $this->ConcatenarValores("260")) != "" )
		{
			echo "<div class=label_info>$LBL_CARD_PUBLISHING</div><div class='data_info'>$cPublicacion</div><br style='clear:both'>";
		}
		
		if( ($cPublisherNumber = $this->ConcatenarValores("028")) != "" )
		{
			echo "<div class=label_info>$LBL_CARD_PUBLISHER_NUMBER</div><div class='data_info'>$cPublisherNumber</div><br style='clear:both'>";
		}		

		if( ($cEdicion = $this->ConcatenarValores("250")) <> "" )
		{
			echo "<div class=label_info>$LBL_CARD_EDITION</div><div class='data_info'>$cEdicion</div><br style='clear:both'>";
		}		
		
		echo "</div><br>\n"; 
		
		//
		// datos adicionales
		//
		echo "<div $classname>";
		
		if( ($cDescrip = $this->ConcatenarValores('300')) <> "" )
		{
			echo "<div class='label_info'>$LBL_CARD_DESCRIPTION</div><div class='data_info'>$cDescrip</div><br style='clear:both'>";
		}
		
		if( ($cNotaDetallesSistema = $this->ConcatenarValores('538')) <> "" )
		{
			echo "<div class='label_info'>&nbsp;</div><div class='data_info'>$cNotaDetallesSistema</div><br style='clear:both'>";
		}
		
		// Nota de Audiencia 521
		if( ($cTargetAudience = $this->ConcatenarValores('521')) <> "" )
		{
			echo "<div class='label_info'>$LBL_CARD_TARGET_AUDIENCE</div><div class='data_info'>$cTargetAudience</div><br style='clear:both'>";
		}				
		
		if( ($cSerie = $this->ConcatenarValores('490')) <> "" )
		{
			echo "<div class='label_info'>$LBL_CARD_SERIES</div><div class='data_info'>$cSerie</div><br style='clear:both'>";
		}
		
		if( ($cLocalCallNumber = $this->ConcatenarValores('090')) <> "" )
		{
			echo "<div class='label_info'>$LBL_CARD_CALLNUMBER</div><div class=data_info>$cLocalCallNumber</div><br style='clear:both'>";
		}
	
		if( ($cAsientoSecundario = $this->ConcatenarValores('730')) <> "" )
		{
			echo "<div class=label_info>$LBL_CARD_ADDED_ENTRY:</div><div class=data_info>$cAsientoSecundario</div><br style='clear:both'>";
		}
		
		// otros titulos
		if( ($cOtrosTitulos = $this->ConcatenarValores('246')) <> "" )
		{
			echo "<div class=label_info>$LBL_CARD_OTHER_TITLES</div><div class=data_info>$cOtrosTitulos</div><br style='clear:both'>";
		}
		
		// Número de Clasificación de la Biblioteca del Congreso
		if( ($cLC = $this->ConcatenarValores('050')) <> "" )
		{
			echo "<div class=label_info>$LBL_CARD_CLASIF_LC</div><div class=data_info>$cLC</div><br style='clear:both'>";
		}
		
		// DEWEY
		if( ($cDewey = $this->ConcatenarValores('082')) <> "" )
		{
			echo "<div class=label_info>$LBL_CARD_CLASIF_DEWEY</div><div class=data_info>$cDewey</div><br style='clear:both'>";
		}

		// SUMMARY
		if( ($cSummary = $this->ConcatenarValores("520")) <> "" )
		{
			echo "<div class=label_info>$LBL_CARD_SUMMARY</div><div class=data_info>$cSummary</div><br style='clear:both'>";
		}
		
		// ISBN
		if( ($cISBN = $this->ConcatenarValores('020')) <> "" )
		{
			echo "<div class=label_info>ISBN:</div><div class=data_info>$cISBN</div><br style='clear:both'>";
		}
		
		//
		// TERMINOS TEMATICOS
		//	
		echo "<br>";
		
		//
		// LCSH
		//
		
		// Obtener String del campo 650 cuando el ID2 sea 0 (LCSH), NO IMPORTA el valor de ID1
		// 650
		if( ($cTematizacion_LCSH= $this->ConcatenarValores( "650", "", false, " ", "", "0" )) != "" )
		{
			echo "<div class=label_info>Términos Temáticos (LCSH):</div>";
			echo "<div class=data_info>";
			
			// NO Agrega un número a cada término
			$this->show_index = false;
			// el TRUE es para excluir los ALFABETICOS
			$this->DISPLAY_NOTE( '650', 0, "", true, " -- ", "", "0" ) ;  // Tematización LCSH
			
			echo "</div><br style='clear:both;'><br>";
		}
		
		// 651
		if( ($cTematizacion_LCSH= $this->ConcatenarValores( "651", "", false, " ", "", "0" )) != "" )
		{
			echo "<div class=label_info>Términos Temáticos Geográficos (LCSH):</div>";
			echo "<div class=data_info>";
			
			// NO Agrega un número a cada término
			$this->show_index = false;
			// el TRUE es para excluir los ALFABETICOS
			$this->DISPLAY_NOTE( '651', 0, "", true, " -- ", "", "0" ) ;  // Tematización LCSH
			
			echo "</div><br style='clear:both;'><br>";
		}		

		//
		// LCSH INFANTIL
		//

		// Obtener String del campo 650 cuando el ID2 sea 1 (Theasuru LC Infantil), NO IMPORTA el valor de ID1
		if( ($cTematizacion_LCSH= $this->ConcatenarValores( "650", "", false, " ", "", "1" )) != "" )
		{
			echo "<div class=label_info>Términos Temáticos (Infantil):</div>";
			echo "<div class=data_info>";
			
			// NO Agrega un número a cada término
			$this->show_index = false;
			// el TRUE es para excluir los ALFABETICOS
			$this->DISPLAY_NOTE( '650', 0, "", true, " -- ", "", "1" ) ;  // Tematización LCSH
			
			echo "</div><br style='clear:both;'><br>";
		}
		
		
		// Obtener String del campo 650 cuando el ID2 sea 4 (Fuente no especificada), NO IMPORTA el valor de ID1
		if( ($cTematizacion_Otros = $this->ConcatenarValores( "650", "", false, " ", "", "4" )) != "" )
		{
			echo "<div class=label_info>Términos Temáticos (Fuente sin especificar):</div>";
			echo "<div class=data_info>";
			
			// NO Agrega un número a cada término
			$this->show_index = false;
			// el TRUE es para excluir los ALFABETICOS
			$this->DISPLAY_NOTE( '650', 0, "", true, " -- ", "", "4" ) ;  // Fuente sin especificar
			
			echo "</div><br style='clear:both;'>";
		}					

		// Obtener String del campo 650 cuando el ID2 sea 7 (Others), NO IMPORTA el valor de ID1
		if( ($cTematizacion_Otros = $this->ConcatenarValores( "650", "", false, " ", "", "7" ))!= "" )
		{
			echo "<div class=label_info>Términos Temáticos (Otros):</div>";
			echo "<div class=data_info>";
			
			// NO Agrega un número a cada término
			$this->show_index = false;
			// el TRUE es para excluir los ALFABETICOS
			$this->DISPLAY_NOTE( '650', 0, "", true, " -- ", "", "7" ) ;  // Tematización Otros
			
			echo "</div><br style='clear:both;'>";
		}		

		//
		// OTHER NAMES
		// 
		if( ($cTematizacion_Otros = $this->ConcatenarValores( "700", "", false, " ", "", "" ))!= "" )
		{
			echo "<div class=label_info>$LBL_CARD_OTHERNAME</div>";
			echo "<div class=data_info>";
			
			// NO Agrega un número a cada término
			$this->show_index = false;
			// el TRUE es para excluir los ALFABETICOS
			$this->DISPLAY_NOTE( "700", 0, "", true, " -- ", "", "" ) ;  // Tematización Otros
			
			echo "</div><br style='clear:both;'>";
		}		

		if( ($cTematizacion_Otros = $this->ConcatenarValores( "710", "", false, " ", "", "" ))!= "" )
		{
			echo "<div class=label_info>&nbsp;</div>";
			echo "<div class=data_info>";
			
			// NO Agrega un número a cada término
			$this->show_index = false;
			// el TRUE es para excluir los ALFABETICOS
			$this->DISPLAY_NOTE( "710", 0, "", true, " -- ", "", "" ) ;  // Tematización Otros
			
			echo "</div><br style='clear:both;'>";
		}

		// LANGUAGE 546
		if( ($cTmpStr = $this->ConcatenarValores("546")) <> "" )
		{
			echo "<div class=label_info>$LBL_CARD_LANGUAGE</div><div class=data_info>$cTmpStr</div><br style='clear:both'>";
		}

		// PERFORMER 511
		if( ($cTmpStr = $this->ConcatenarValores("511")) <> "" )
		{
			echo "<div class=label_info>$LBL_CARD_PERFORMER</div><div class=data_info>$cTmpStr</div><br style='clear:both'>";
		}		

		// PRODUCTION CREDITS 508
		if( ($cTmpStr = $this->ConcatenarValores("508")) <> "" )
		{
			echo "<div class=label_info>$LBL_CARD_PRODUCTION_CREDITS</div><div class=data_info>$cTmpStr</div><br style='clear:both'>";
		}		

		// NOTAS
		if( ($cNotes = $this->ConcatenarValores("500")) <> "" )
		{
			echo "<div class=label_info>$LBL_CARD_NOTES</div><div class=data_info>$cNotes</div><br style='clear:both'>";
		}		

		// ENLACES / LIGAS / LINKS
		if( ($cLinks = $this->ConcatenarValores( "856", '$u' )) <> "" )
		{
			$cLink_Label = $this->ConcatenarValores( "856", '$y' );
			
			echo "<div class=label_info>Enlaces:</div><div class=data_info><a href='$cLinks' target='_new'>$cLink_Label</a></div><br>";
		}			

		echo "\n";
		echo "<br style='clear:both'>\n";
		echo "\n</div><!-- caja_datos -->\n";
	}
	
	//
	// 11-abr-2009: Se preven los siguientes status
	//
	// Ult. modificacion: 13-abr-2009.
	//
	function GetItemStatus( $strStatus )
	{
		global $LBL_STATUS_AVAILABLE, $LBL_STATUS_BLOCKED, $LBL_STATUS_BORROWED, $LBL_STATUS_MISSING, $LBL_STATUS_RESERVED, $LBL_STATUS_DISABLED;

		$cResult = "";

		if( $strStatus == "D" ) 
			$cResult = $LBL_STATUS_AVAILABLE;
		else if( $strStatus == "B" ) 
			$cResult = $LBL_STATUS_BLOCKED;
		else if( $strStatus == "P" ) 
			$cResult = $LBL_STATUS_BORROWED;
		else if( $strStatus == "F" ) 
			$cResult = $LBL_STATUS_MISSING;		   
	//	else if( $strStatus == "R" ) 
		   //$cResult = $LBL_STATUS_RESERVED;
		else if( $strStatus == "X" ) 
			$cResult = $LBL_STATUS_DISABLED;

		return $cResult;
	}
	
	function GetCallNumber( $prefix, $book_class, $book_type )
	{	
		return "$prefix $book_class $book_type";
	}
	
	
	// El encabezado debe venir así:
	//  00-04: Longitud de registro
	//  05:    Estado del registro
	//  06:    Tipo del registro
	//  07:    Nivel Bibliografico
	//  08:    Tipo de control
	//  09:    Código del Esquema de Caracteres
	//  10:
	//  11:
	//  ... PENDIENTE COMPLEMENTAR
	function InicializarCabecera( $cValue )
	{
		$MARC_OFFSET = 0;
		
		// LOS VALORES SERÁN INICIALIZADOS
		$tmptiporegistro 	= "";
		
		if( $this->FTipoRegistro != "" )	
			$tmptiporegistro = $this->FTipoRegistro;
		
		$this->FLongitudRegistro   = (int) substr( $cValue, 0+$MARC_OFFSET, 5 ); // 5 caracteres
		$this->FEstadoRegistro     = substr( $cValue, 5+$MARC_OFFSET, 1 ); // 1 caracter
		$this->FTipoRegistro       = substr( $cValue, 6+$MARC_OFFSET, 1 );    // 1 caracter
		$this->FNivelBibliografico = substr( $cValue, 7+$MARC_OFFSET, 1 ); // 1 caracter
		
		if( $tmptiporegistro != "" )
		{
			if( $this->FTipoRegistro != $tmptiporegistro )	
				$this->FTipoRegistro = $tmptiporegistro;
		}

		$this->FTipoControl        = substr( $cValue, 8+$MARC_OFFSET, 1 ); // 1 caracter
		$this->FCodigoEsquemaChars = substr( $cValue, 9+$MARC_OFFSET, 1 ); // 1 caracter
		$this->FConteoIndicadores  = (int) substr( $cValue, 10+$MARC_OFFSET, 1 ); // 1 caracteres

		$this->FConteoCodigoSubcampo = (int) substr( $cValue, 11+$MARC_OFFSET, 1 ); // 1 caracteres
		$this->FDireccionBase_Datos  = (int) substr( $cValue, 12+$MARC_OFFSET, 5 ); // 5 caracteres

		$this->FNivelCodificacion     = substr( $cValue, 17+$MARC_OFFSET, 1 ); // 1 caracteres
		$this->FFormaCatalogacion     = substr( $cValue, 18+$MARC_OFFSET, 1 ); // 1 caracteres
		$this->FNivelRegistro_Recurso = substr( $cValue, 19+$MARC_OFFSET, 1 ); // 1 caracteres
		$this->FMapaEntradas          = substr( $cValue, 20+$MARC_OFFSET, 4 ); // 4 caracteres
		
		if( strlen( $cValue ) <> 24 )
		{
			echo( "Longitud incorrecta de encabezado / INCORRECT HEADER LENGTH; Should be 24 bytes length: $this->nIDTitulo" );
			
			echo " " . strlen( $cValue ) .  " " . $cValue ;
		}

	}
	
	function InicializarValoresCampo008( $cValue )
	{
		$this->F008_FechaIngresoRegistro = substr( $cValue, 0, 6 );
		$this->F008_TipoFechaEstadoPub   = substr( $cValue, 6, 1 );
		$this->F008_Fecha_1			     = substr( $cValue, 7, 4 );
		$this->F008_Fecha_2			     = substr( $cValue, 11, 4 );
		$this->F008_LugarPublicacion     = substr( $cValue, 15, 3 );
		$this->F008_Idioma			     = substr( $cValue, 35, 3 );
		$this->F008_RegistroModificado   = substr( $cValue, 38, 1 );
		$this->F008_FuenteCatalogacion   = substr( $cValue, 39, 1 );
	}

	//
	// Crea un registro
	//
	// los terminadores de campo 
	// e inicio de subcampo pueden ir en ASCII o en ISO
	//
	function CreateRecord_ISO2709( $ascii=true )
	{
		$size = 0;
		
		$directory = "";
		
		$address = 0;		
		
		if( $ascii )
		{
			$end_of_field_byte = "^"; 			// ASCII fin de campo 
			$begin_of_subfield_byte = "$";   	// ASCII inicio de subcampo 
			$no_indicador = " ";
		}
		else
		{
			$end_of_field_byte = chr(0x1e); 		// fin de campo (IS2 de l'ISO 6630)
			$begin_of_subfield_byte = chr(0x1f);    // inicio de sub-campo (IS1 de l'ISO 6630)
			$no_indicador = chr(0x20);
		}
		
		$full_record = $end_of_field_byte;
		
		foreach( $this->aCamposMarc as $value )
		{
			if( $value[0] != "$$$" and $value[0] != "###" )
			{
				// solo se generarán los campos que tienen subcampos
				// o aquellos que tenan un valor directamente asignado
				//
				if( count( ($value[1]->subcampos )> 0) or ($value[1]->cValor != "") ) 
				{
					$one_field = "";  // almacenará el contenido de un campo
					$subfields = "";

					foreach( $value[1]->subcampos as $subfield )
					{				
						$str_idsubcampo = $subfield->cIDSubCampo;
						// bibliotek maneja el codigo $x, eliminar primer caracter
						$str_idsubcampo = $begin_of_subfield_byte . substr( $str_idsubcampo, 1, 5 );
						
						$subfields .= $str_idsubcampo . $subfield->cValor;
					}

					if( $subfields != "" or $value[1]->cValor != "" )
					{					
						// agrega primero los INDICADORES, para campos que no son de Control
						if( $value[1]->bCampoControl )
						{
							// agrega el valor directo
							if( $value[1]->cValor != "" ) $one_field .= $value[1]->cValor;
						}
						else
						{
							$one_field .= ( $value[1]->objID1 != NULL ) ? $value[1]->objID1->cValor : "$no_indicador";
							$one_field .= ( $value[1]->objID2 != NULL ) ? $value[1]->objID2->cValor : "$no_indicador";
							
							// agrega los elementos de subcampos
							$one_field .= $subfields;
						}

						// agrega caracter de fin de campo
						$one_field .= $end_of_field_byte; // fin de registro

						$full_record .= $one_field;

						$size_per_field = strlen( $one_field );

						$size += $size_per_field;

						$directory .= $value[0] . 
									  str_pad( $size_per_field, 4, "0", STR_PAD_LEFT) . 
									  str_pad( $address, 5, "0", STR_PAD_LEFT);

						$address += $size_per_field;  // puntero a cada campo
					}
				}
				// if - count
			}
		}	
		
		$size += strlen($directory);
		$size += 24;
		
		//
		// genera cabecera
		//
		$this->FLongitudRegistro = $size;

		$header = str_pad($this->FLongitudRegistro, 5, "0", STR_PAD_LEFT);
		
		$header .= $this->FEstadoRegistro;       // c
		$header .= $this->FTipoRegistro;         // a 
		$header .= $this->FNivelBibliografico;   // m
		
		$header .= $this->FTipoControl;

		$header .= $this->FCodigoEsquemaChars;
		$header .= $this->FConteoIndicadores;
		$header .= $this->FConteoCodigoSubcampo;

		$this->FDireccionBase_Datos = 24 + strlen($directory) + 1;
		
		$header .= str_pad($this->FDireccionBase_Datos, 5, "0", STR_PAD_LEFT);

		$header .= $this->FNivelCodificacion;
		$header .= $this->FFormaCatalogacion;
		$header .= $this->FNivelRegistro_Recurso;

		$this->FMapaEntradas = "4500";
		
		$header .= str_pad($this->FMapaEntradas, 4, "0", STR_PAD_RIGHT);

		if( strlen($header) > 24 )
			die("<br><br>Se generó una cabecera de Longitud incorrecta");
		
		$objCampoCabecera = $this->BuscarCampo('$$$');
		if ( $objCampoCabecera != NULL )
			$objCampoCabecera->cValor = $header;

		// fin - genera cabeecera
		
		$full_record = $header . $directory . $full_record;
		
		return $full_record;
	}
	
	//
	// Generar una cabecera
	//
	function GenerarCabecera( $debug=0 )
	{
		$objCampoCabecera = $this->BuscarCampo('$$$');

		$cValor = "";

		// verificar que campo cabecera exista
		if ( $objCampoCabecera != NULL )
		{
			// Tipo Material (de Registro
			$contenido = $this->CreateRecord_ISO2709();
			
			//if( $debug == 1 )
			  // echo $contenido;
			   
			$cValor = $objCampoCabecera->cValor;
			
			if( $debug == 1 )
			   echo $cValor;			
	   }

	   return $cValor;
	}

	// Propertie external of LEADER
	function SetTipoMaterial( $newValue )
	{
	  $this->cTipoMaterial = $newValue;
	}

	
	// Protected methods of TMARC21_Registro
	function SetLongitudRegistro( $newValue )
	{
	   $this->FLongitudRegistro = $newValue;
	}

	function SetTipoRegistro( $newValue )
	{
	  $this->FTipoRegistro = $newValue;
	}

	function SetEstadoRegistro( $newValue )
	{
	   $this->FEstadoRegistro = $newValue;
	}	
	
	function SetNivelBibliografico( $newValue )
	{
	  $this->FNivelBibliografico = $newValue;
	}

	function SetTipoControl( $newValue )
	{
	  $this->FTipoControl = $newValue;
	}

	function SetCodigoEsquemaChars( $newValue )
	{
	  $this->FCodigoEsquemaChars = $newValue;
	}

	// Número de posiciones de caracteres que usan
	// los indicadores en un registro MARC  p.e. 012 AB
	// donde AB son los indicadores
	//
	// IMPORTANTE: Este valor siempre debería ser 2
	//
	function SetConteoIndicadores( $newValue )
	{
	  if( $newValue <> 2)
		 die ("Valor de posiciones de indicadores diferente de 2");
	  else
		 $this->FConteoIndicadores = $newValue;
	}

	// Número de posiciones de caracteres utilizadas
	// para cada código de subcampo en un registro MARC  p.e. $a, |a
	//
	// IMPORTANTE: Este valor siempre debería ser 2
	//
	function SetConteoCodigoSubcampo( $newValue )
	{
	  if( $newValue <> 2 )
		 die('Valor de posiciones de subcampos diferente de 2');
	  else
		 $this->FConteoCodigoSubcampo = $newValue;
	}

	function SetDireccionBase_Datos( $newValue )
	{
	  $this->FDireccionBase_Datos = $newValue;
	}

	function SetNivelCodificacion( $newValue )
	{
	  $this->FNivelCodificacion = $newValue;
	}

	function SetFormaCatalogacion( $newValue )
	{
	  $this->FFormaCatalogacion = $newValue;
	}

	function SetNivelRegistro_Recurso( $newValue )
	{
	  $this->FNivelRegistro_Recurso = $newValue;
	}

	function SetMapaEntradas( $newValue )
	{
		if( $newValue <> '4500' )
			die('Valor de mapa de entradas diferente de 4500');
		else
			$this->FMapaEntradas = $newValue;
	}

	// Devuelve el valor de un campo dentro del registor MARC
	function ObtenerValorCampo_X_UniqueID( $UNIQUE_IDCampo, $cCodigo, $cSubCodigo )
	{
		$objCampo = $this->BuscarCampoMARC_X_ID( $UNIQUE_IDCampo );

		$Result = "";

		if( $objCampo != NULL )
		{
			if( $objCampo->bCampoControl )
			{ $Result = $objCampo->cValor; }
			else
			{
				// otros
				$objSubCampo = $objCampo->BuscarSubCampo( $cCodigo );

				if( $objSubCampo != NULL )
				   $Result = $objSubCampo->cValor;
			}
		}

		return $Result;
	}

	//
	//
	// FUNCIONES DE GUARDADO DE BbDD
	//
	//

	// Actualiza o Agrega el Registro de Catalogación
	// en tabla ACERVO_CATALOGACION
	function AgregarActualizarCatalogacion_Valor( $objCampo, $cCodigo, $cSubCodigo )
	{
		$unique_id = $objCampo->ID . ":{" . $cCodigo. "}[" . $cSubCodigo. "]";

		$sql  = "SELECT ID_DESCRIPTOR FROM acervo_catalogacion ";
		$sql .= "WHERE ID_BIBLIOTECA=$this->nIDBiblioteca and ID_TITULO=$this->nIDTitulo and ID_CAMPO='$objCampo->cIDCampo' and CODIGO='$cCodigo' and SUBCODIGO='$cSubCodigo'";
		$sql .= "  and INTERNALUSE1='$unique_id'";

		$resultqry = $this->dbx->SubQuery( $sql );
	   
	    $bExiste = false;
	   
		if( $row = $this->dbx->FetchRecord( $resultqry ) )
		{ 
			if( $row["ID_DESCRIPTOR"] != 0 )
			{
				$bExiste = true;
				$nIDDescriptor = $row["ID_DESCRIPTOR"];
			}
		}

		$this->dbx->ReleaseResultset( $resultqry );
	   
		if( !$bExiste )
		{
			// agregar registro DESCRIPTOR
			$sql  = "SELECT COUNT(*) AS CUANTOS, MAX(ID_DESCRIPTOR) AS MAXIMO ";
			$sql .= "FROM acervo_catalogacion ";
			$sql .= "WHERE ID_BIBLIOTECA=$this->nIDBiblioteca and ID_TITULO=$this->nIDTitulo";

			$resultqry = $this->dbx->SubQuery( $sql );

			if( $row = $this->dbx->FetchRecord( $resultqry ) )
			{
				if( $row["CUANTOS"] == 0 )
					$nIDDescriptor = 1;
				else
					$nIDDescriptor = $row["MAXIMO"] + 1;
			}
			$this->dbx->ReleaseResultset( $resultqry );

			$cID_Fields = "";
			$cID_Values = "";

			$cValor = $this->ObtenerValorCampo_X_UniqueID( $objCampo->ID, $cCodigo, $cSubCodigo );
			
			if( strlen($cValor) > 1024 )
			   $cValor = substr( $cValor, 0, 1024 );

			//
			// G u a r d a r
			// i d e n t i f i c a d o r e s
			//
			if(($objCampo->cIDCampo != '$$$') and ($objCampo->cIDCampo != '###'))
			{
				if(($cCodigo == '') and ($cSubCodigo==''))
				{
					if($objCampo->objID1 != NULL)
					{
						$cID_Fields = "ID1,";
						$cID_Values = "'". $objCampo->objID1->cValor . "',";
					}

					if($objCampo->objID2 != NULL)
					{
						$cID_Fields .= "ID2,";
						$cID_Values .= "'" . $objCampo->objID2->cValor . "',";
					}
				}
			}
			
			$unique_id = $objCampo->ID . ":{" . $cCodigo. "}[" . $cSubCodigo. "]";
			
			$cValor = remove_scaped_quotes( $cValor );
			
			$sql  = "INSERT INTO acervo_catalogacion ";
			$sql .= " (ID_BIBLIOTECA, ID_TITULO, ID_DESCRIPTOR, ID_CAMPO, CODIGO, SUBCODIGO, $cID_Fields VALOR, INTERNALUSE1 ) ";
			$sql .= " VALUES ($this->nIDBiblioteca, $this->nIDTitulo, $nIDDescriptor, '" . $objCampo->cIDCampo . "', '$cCodigo', '$cSubCodigo', $cID_Values '$cValor', '$unique_id' ) ";
			$this->dbx->SubQuery( $sql );
		}
		else
		{
			$cValor = $this->ObtenerValorCampo_X_UniqueID( $objCampo->ID, $cCodigo, $cSubCodigo );
			
			$sql  = "UPDATE acervo_catalogacion ";
			$sql .= " SET VALOR='$cValor' ";
			$sql .= "WHERE ID_BIBLIOTECA=$this->nIDBiblioteca and ID_TITULO=$this->nIDTitulo and ID_DESCRIPTOR=$nIDDescriptor ";
			$this->dbx->SubQuery( $sql );
		}

	}
	
	function GuardarElementosSubCampos( $objCampo )
	{
		$objSubCampo = NULL;

		//
		// G u a r d a r     i d e n t i f i c a d o r e s
		//
		if(($objCampo->objID1 != NULL) or ($objCampo->objID2 != NULL))
		{
		   // XX = Codigo especial para Identificadores
		   $this->AgregarActualizarCatalogacion_Valor( $objCampo, "", "" );
		}

		if( $objCampo->bCampoControl )
		   $this->AgregarActualizarCatalogacion_Valor( $objCampo, "", "" );
		else
		{
			//
			// G u a r d a r     s u b c a m p o s
			//
			for( $i = 0; $i<$objCampo->ContarSubCampos(); $i++ )
			{
				$objSubCampo = $objCampo->ObtenerSubCampo($i);
				
				if( $objSubCampo != NULL )
				   $this->AgregarActualizarCatalogacion_Valor( $objCampo, $objSubCampo->cIDSubCampo, "" );

			}
		}

	}

	//
	// Crea el ID de la catalogacion en ACERVO_TITULOS
	//
	function AgregarRegistro( $usuario )
	{
		$sql  = "SELECT COUNT(*) AS CUANTOS, MAX(ID_TITULO) AS MAXIMO FROM acervo_titulos ";
		$sql .= " WHERE ID_BIBLIOTECA=$this->nIDBiblioteca ";
		
		$resultqry = $this->dbx->SubQuery( $sql );
	   
		if( $row = $this->dbx->FetchRecord( $resultqry ) )
		{ 
		   if( $row["CUANTOS"] == 0 )
			  $this->nIDTitulo = 1;
		   else
			  $this->nIDTitulo = $row["MAXIMO"] + 1;
		}

		$this->dbx->ReleaseResultset( $resultqry );
		
		$fecha_registro = current_dbtime(1);
		
		// Agregar el registro a ACERVO_TITULOS
		$sql =  "INSERT INTO acervo_titulos " . 
				" (ID_BIBLIOTECA, ID_TITULO, ID_SERIE, ID_TIPOMATERIAL, STATUS, USUARIO_REGISTRO, FECHA_REGISTRO, USUARIO_CATALOGO, FECHA_CATALOGACION ) " .
				" VALUES ($this->nIDBiblioteca, $this->nIDTitulo, $this->nIDSerie, '$this->cTipoMaterial', '$this->FEstadoRegistro', $usuario, '$fecha_registro', $usuario, '$fecha_registro' ) ";

		$this->dbx->SubQuery( $sql );
		
		// NO. DE CONTROL
		$objCampo = $this->BuscarCampo( "001" );
		if( $objCampo == NULL )
			$objCampo = $this->AgregarCampo( "001", false, false, false );
			
		$objCampo->cValor = $this->nIDTitulo;
		
		// FECHA Y HORA ULT. TRANSACCION
		$objCampo = $this->BuscarCampo( "005" );
		if( $objCampo == NULL )
			$objCampo = $this->AgregarCampo( "005", false, false, false );

		$objCampo->cValor = strftime("%Y%m%d%H%M%S.0");

	}
	
	//
	// Crea el ID de la catalogacion en ACERVO_TITULOS
	//
	function ActualizarRegistro( $usuario_actualiza )
	{
		$fecha_modificacion = current_dbtime(1);

		// Actualizar el registro a ACERVO_TITULOS
		$sql =  "UPDATE acervo_titulos " . 
				" SET ID_SERIE=$this->nIDSerie, ID_TIPOMATERIAL='$this->cTipoMaterial', STATUS='$this->FEstadoRegistro', " .
				"     USUARIO_MODIFICO=$usuario_actualiza, FECHA_MODIFICACION='$fecha_modificacion' " .
				"WHERE ID_BIBLIOTECA=$this->nIDBiblioteca and ID_TITULO=$this->nIDTitulo ";
	
		$this->dbx->SubQuery( $sql );
		
		// VERIFICAR QUE EXISTA 
		// NO. DE CONTROL campo 001, SI NO EXISTIERA se crea
		$objCampo = $this->BuscarCampo( "001" );
		if( $objCampo == NULL )
			$objCampo = $this->AgregarCampo( "001", false, false, false );
			
		$objCampo->cValor = $this->nIDTitulo;
		
		// VERIFICAR QUE EXISTA 
		// FECHA Y HORA ULT. TRANSACCION
		// SI NO EXISTIERA SE CREA
		$objCampo = $this->BuscarCampo( "005" );
		if( $objCampo == NULL )
			$objCampo = $this->AgregarCampo( "005", false, false, false );

		$objCampo->cValor = strftime("%Y%m%d%H%M%S.0");
	}
	
	function InicializarModificacion()
	{  
		$sql  = "UPDATE acervo_catalogacion SET INTERNALUSE1='' ";
		$sql .= " WHERE ID_BIBLIOTECA=$this->nIDBiblioteca and ID_TITULO=$this->nIDTitulo";
		
		$this->dbx->SubQuery( $sql );
	}

	function FinalizarGuardado()
	{
		// depurar posibles campos YA NO UTILIZADOS
		// cuando es modificacion
		$sql  = "DELETE FROM acervo_catalogacion ";
		$sql .= " WHERE ID_BIBLIOTECA=$this->nIDBiblioteca and ID_TITULO=$this->nIDTitulo and INTERNALUSE1='' ";
		$this->dbx->SubQuery( $sql );
	
	   // PENDIENTE DEPURAR EL ID_DESCRIPTOR
		$sql  = "SELECT MIN(ID_DESCRIPTOR) AS MIN_DESCRIP, MAX(ID_DESCRIPTOR) AS MAX_DESCRIP FROM acervo_catalogacion ";
		$sql .= " WHERE ID_BIBLIOTECA=$this->nIDBiblioteca and ID_TITULO=$this->nIDTitulo ";
		
		$resultqry = $this->dbx->SubQuery( $sql );
	   
	    $min_descrip = 1;
		$max_descrip = 1;
	   
		if( $row = $this->dbx->FetchRecord( $resultqry ) )
		{ 
		   $min_descrip = $row["MIN_DESCRIP"];
		   $max_descrip = $row["MAX_DESCRIP"];
		}

		$this->dbx->ReleaseResultset( $resultqry );
		
		if( $min_descrip > 1 )
		{
			// REASIGNAR ID_DESCRIPTORS y volver al número 1
			$value = $min_descrip - 1;
			$sql  = "UPDATE acervo_catalogacion SET ID_DESCRIPTOR=ID_DESCRIPTOR-$value ";
			$sql .= " WHERE ID_BIBLIOTECA=$this->nIDBiblioteca and ID_TITULO=$this->nIDTitulo";
			$this->dbx->SubQuery( $sql );   
		}
	}
	
	function DisplayDataFiles( $tablacompleta=0 )
	{		
		include_language( "gral_files" );
		
		global $LINK_DOWNLOAD_FILE, $LINK_REMOVE_FILE, $MSG_CONFIRM_REMOVAL;
		global $LBL_FILE_NAME, $LBL_FILE_SIZE, $LBL_FILE_TYPE;
		
		$sql  = "SELECT * FROM acervo_archivos ";
		$sql .= " WHERE ID_BIBLIOTECA=$this->nIDBiblioteca and ID_TITULO=$this->nIDTitulo";
		
		$resultqry = $this->dbx->SubQuery( $sql );
	   
		if( $tablacompleta == 1 )
		{
			echo "<table width='100%'>";
			echo "<tr><td class='cuadricula columna columnaEncabezado' width='40%'>$LBL_FILE_NAME</td>";
			echo "    <td class='cuadricula columna columnaEncabezado' width='20%'>$LBL_FILE_SIZE</td>";
			echo "    <td class='cuadricula columna columnaEncabezado' width='20%'>$LBL_FILE_TYPE</td>";
			echo "    <td class='cuadricula columna columnaEncabezado' width='15%'></td>";
			echo "</tr>";
		}
		
		$delete_digital_permission = verificar_privilegio( PRIV_ADD_DIGITAL_FILES );
		
		$num=0;
		while( $row = $this->dbx->FetchRecord( $resultqry ) )
		{ 
			if( $tablacompleta == 1 )
			{
				$size = ($row["FILE_SIZE"] / 1024);
				$size = sprintf("%6.1f", $size );
				
				if( $size > 1024 )
				{
					$size = ($row["FILE_SIZE"] / (1024*1024));
					$size = sprintf("%6.2f", $size );
					$size .= " Mb";
				}
				else
				   $size .= " Kb";

				$url_download_link = "javascript:download_digital_file( \"catalogacion\", $this->nIDTitulo, " . $row["ID_FILENUM"] . ");"; 
				$url_delete_link = "";
				
				if($delete_digital_permission )
					$url_delete_link = "&nbsp;&nbsp;<a href='"."javascript:remove_digital_file( \"$MSG_CONFIRM_REMOVAL\", \"catalogacion\", $this->nIDTitulo, " . $row["ID_FILENUM"] . ");"."'>$LINK_REMOVE_FILE</a>";
				   
				$file = $row["FILE_NAME"];
				   
				echo "<tr>";
				echo "<td class='columna cuadricula'>$file</td>";
				echo "<td class='columna cuadricula'>$size</td>";
				echo "<td class='columna cuadricula'>" . obtener_file_info( $row["FILE_MIMETYPE"] ) . " " . obtener_file_info( $row["FILE_MIMETYPE"], 2 ) . "</td>";
				echo "<td class='columna cuadricula'><a href='$url_download_link' title='Descargar Archivo $file'>$LINK_DOWNLOAD_FILE</a>$url_delete_link</td>";
				echo "</tr>";
			}
			else
			{
				echo "<div class='mini_bullet'>&nbsp;</div>";
				echo "<div style='float:left; width: 85%;'>" . $row["FILE_NAME"] . "</div>";
			}
			
			$num++;
		}
		
		if( $num==0 )
		{
			if( $this->language == 1 )
				echo "Sin archivos anexados";
			else
				echo "No files attached";
		}

		$this->dbx->ReleaseResultset( $resultqry );
		
		if( $tablacompleta == 1 )
		{	
			echo "</table>";
		}
	}
	
	//
	// Carga la definición de Autoridades
	// para la red de bibliotecas
	//
	function CargarAutoridades( $id_red )
	{
		$sql = "SELECT a.ID_CAMPO, a.CODIGO, a.ID_CATEGORIA, b.DESCRIPCION " .
				"FROM cfgautoridades a " . 
				" LEFT JOIN tesauro_categorias b ON (b.ID_RED=a.ID_RED and b.ID_CATEGORIA=a.ID_CATEGORIA) " . 
				"WHERE (a.ID_RED=$id_red ) " .
				"ORDER BY ID_CAMPO, CODIGO ";

		$resultqry = $this->dbx->SubQuery( $sql );

		while( $row = $this->dbx->FetchRecord( $resultqry ) )
		{
			$this->aArrayAuthorities[] = Array( "id_campo" => $row["ID_CAMPO"], 
											    "id_subcampo" => $row["CODIGO"],
											    "id_categoria" => $row["ID_CATEGORIA"],
											    "descrip_categoria" => $row["DESCRIPCION"] );
		}
		
		$this->dbx->ReleaseResultset( $resultqry );
	}
	
	//
	//
	// 
	function BuscarAutoridadXSubCampo( $objSubCampo, $id_red )
	{
		$autoridad = 0;
		$hay = 0;
		
		if( eregi( "{", $objSubCampo->cTesauro ) )
		{
			$resultqry = $this->dbx->SubQuery( "SELECT a.ID_CATEGORIA, a.DESCRIPCION FROM tesauro_categorias a WHERE a.ID_RED=$id_red and a.DESCRIPCION='$objSubCampo->cTesauro'" );

			if( $row = $this->dbx->FetchRecord( $resultqry ) )
			{
				$autoridad = Array( "id_categoria" => $row["ID_CATEGORIA"],
									"descrip_categoria" => $row["DESCRIPCION"],
									"tipo" => "codigo" );
				$hay = 1;
			}
			
			$this->dbx->ReleaseResultset( $resultqry );
		
		}
		else
		{		
			for( $i=0; $i<count($this->aArrayAuthorities); $i++ )
			{
				if( $this->aArrayAuthorities[$i]["id_campo"] == $objSubCampo->cIDCampo and
					$this->aArrayAuthorities[$i]["id_subcampo"] == $objSubCampo->cIDSubCampo )
				{
					$autoridad = Array( "id_categoria" => $this->aArrayAuthorities[$i]["id_categoria"],
										"descrip_categoria" => $this->aArrayAuthorities[$i]["descrip_categoria"],
										"tipo" => "descripcion" );
					$hay = 1;
					break;
				}
			}		
		}
		
		return $autoridad;
	}
	
	// Obtener datos del usuario
	// Creada: 14-ago-2009
	//
	// info = 0  LOGINAME
	// info = 1  NOMBRE USUARIO
	// info = 2  E_MAIL
	//
	//  PENDIENTE: VERIFICAR EN QUÉ PARTE SE USA ESTA RUTINA?? SUSTITUIR POR EL OBJ TUSER
	//

	function usuario_obtenerinfo_from_id( $id_usuario, $info=0 )
	{
		$result = $this->dbx->Open( "SELECT USERNAME, PATERNO, MATERNO, NOMBRE, E_MAIL " .
								   "FROM cfgusuarios WHERE ID_BIBLIOTECA=" . getsessionvar( "id_biblioteca" ) . " and ID_USUARIO=$id_usuario " );
		
		$ret = "";
		
		if( $row = $this->dbx->NextRow() )
		{
			if( $info==0 )
				$ret = $this->dbx->row["USERNAME"];
			if( $info==1 )
				$ret = $this->dbx->row["PATERNO"] . " " . $this->dbx->row["MATERNO"] . " " . $this->dbx->row["NOMBRE"];
			if( $info==2 )
				$ret = $this->dbx->row["E_MAIL"];
		} 
		
		$this->dbx->Close();
		
		return $ret;
	}
	
	// 13-nov-2009
	function PreProcessor( $fields_mask, &$temas )
	{
		$val = "";
		
		$temas = 0;
		
		if( $fields_mask == "001" )
			$val = $this->nIDTitulo;
		else if( strlen($fields_mask) == 3 ) // un campo
			$val = $this->ConcatenarValores( $fields_mask );
		else if( strlen($fields_mask) == 6 ) // campo y subcampo
		{
			$aElementos = split( ":", $fields_mask );
			$val = $this->ConcatenarValores( $aElementos[0], $aElementos[1] );						
		}
		else if( $fields_mask == "650@651@700@710" ) // campos de temas
		{
			$temas = 1;

			// 650
			if( ($cTematizacion_LCSH = $this->ConcatenarValores( "650", "", false, " : ", "", "0" )) != "" )
			{
				$val .= $cTematizacion_LCSH;
			}

			// 651
			if( ($cTematizacion_LCSH = $this->ConcatenarValores( "651", "", false, " : ", "", "0" )) != "" )
			{
				if( $val != "" ) $val .= " -- ";
				$val .= $cTematizacion_LCSH;
			}						

			// INFANTIL
			if( ($cTematizacion_LCSH = $this->ConcatenarValores( "650", "", false, " : ", "", "1" )) != "" )
			{
				if( $val != "" ) $val .= " -- ";
				$val .= $cTematizacion_LCSH;
			}

			// Obtener String del campo 650 cuando el ID2 sea 4 (Fuente no especificada), NO IMPORTA el valor de ID1
			if( ($cTematizacion_Otros = $this->ConcatenarValores( "650", "", false, " : ", "", "4" )) != "" )
			{
				if( $val != "" ) $val.= " -- ";
				$val .= $cTematizacion_Otros;
			}							

			// Obtener String del campo 650 cuando el ID2 sea 7 (Others), NO IMPORTA el valor de ID1
			if( ($cTematizacion_Otros = $this->ConcatenarValores( "650", "", false, " : ", "", "7" ))!= "" )
			{
				if( $val != "" ) $val .= " -- ";
				$val .= $cTematizacion_Otros;
			}		

			//
			// OTHER NAMES
			// 
			if( ($cTematizacion_Otros = $this->ConcatenarValores( "700", "", false, " : ", "", "" ))!= "" )
			{
				if( $val != "" ) $val .= " -- ";
				$val .= $cTematizacion_Otros;
			}		

			if( ($cTematizacion_Otros = $this->ConcatenarValores( "710", "", false, " : ", "", "" ))!= "" )
			{
				if( $val != "" ) $val .= " -- ";
				$val .= $cTematizacion_Otros;
			}
		}
		else if( eregi( '&', $fields_mask ) )
		{
			$aElementos = split( "&", $fields_mask );
			
			for( $j=0; $j<count($aElementos); $j++ )
			{						
				if( $val != "" )
					$val .= " ";

				$val .= $this->ConcatenarValores( $aElementos[$j] );
			}
		}
		
		return $val;
	
	}
	
}



?>