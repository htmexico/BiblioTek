<?php
	/*******
	  Contiene clases orientadas a compartir codigo común entre funciones relacionadas a la circulación
	  Contents classes oriented for sharing common code between functions related to circulation processes.
	  
	  Historial de Cambios / Changes History
	  
	  02 jun 2009: Se crea el archivo PHP
	  10 ago 2009: Se agrega la propiedad "cAutor" MARC = 100
	  18 ago 2009: Se depurar caracteres finales de la propiedad Titulo y TItulo Corto en constructor TItem_Basic().
	  
	  25 ago 2009: Se prevee la utilización de la clase TUser.. se inicia implementación
	  09 sep 2009: Se agrega TItem_Basic::ObtenerUsuarioRegistro.
	  18 sep 2009: Se agrega ObtenerTiempoRetraso
	  
	  07 oct 2009: Se colocaron SubQueries
	  09 oct 2009: Permite al obj TUser reutilizar un TDB
	  
	  19 nov 2009 y 24nov2009: Se modifica función VerificarDisponibilidad_X_ITEM
	  
	  25mar2011: Verificación de restricciones por usuario
	  
     */
	
	//
	// Proporciona datos básicos para cualquier ITEM
	// Handles basic data for items
	//
	
	class TItem_Basic
	{
		var $nIDBiblioteca;
		var $cNombreBiblioteca;
		var $nIDTitulo;
		var $nIDItem;
		var $cTitle;
		var $cTitle_ShortVersion;
		var $cIcon;
		var $cCover;
		var $cBackCover;
		
		var $cStatus;
		
		var $Bloqueado_para_ID_Usuario;
		var $Bloqueado_desde;
		var $Bloqueado_hasta;
		
		var $nIDSerie;
		
		var $cItem_Type;
		var $cDescrip_Item_Type_SP;
		var $cDescrip_Item_Type_ENG;
		var $cDescrip_Item_Type_PORT;
		
		var $cItem_Short_Code_SP;
		var $cItem_Short_Code_ENG;
		var $cItem_Short_Code_PORT;
		
		var $cAutor;
		
		var $item_id_material;
		var $item_prefix;
		var $item_class_no;
		var $item_book_no;
		
		var $usuario_registro;
		
		var $NOT_FOUND;
		
		var $db_not_to_destroy;
		var $db;
		
		//
		// el 3er parámetro debe ser 1 (UNO) si el
		// segundo parámetro es ID_ITEM en lugar de ID_TITULO
		//
		//  $item_type : 0    $id_titulo_item es ID_TITULO
		//  $item_type : 1    $id_titulo_item es ID_ITEM 
		//  $item_type : 2    $id_titulo_item es ID_MATERIAL
		//
		function TItem_Basic( $id_biblioteca=0, $id_titulo_item, $is_item=0, $db_obj_already_create=null )
		{
			if( $id_biblioteca == 0 )
			   $id_biblioteca = getsessionvar("id_biblioteca");

			$this->nIDBiblioteca     = $id_biblioteca;
			$this->cNombreBiblioteca = "";
			$this->nIDItem		 	 = 0;
			$this->nIDTitulo         = 0;

			$this->item_id_material = "";
			$this->item_prefix   = "";
			$this->item_class_no = "";
			$this->item_book_no  = "";

			$this->cStatus = "";

			$this->Bloqueado_para_ID_Usuario = 0;
			$this->Bloqueado_desde = "";
			$this->Bloqueado_hasta = "";			

			if( $is_item == 0 )
				$this->nIDTitulo = $id_titulo_item;
			else if( $is_item == 1 )
				$this->nIDItem   = $id_titulo_item;
			else if( $is_item == 2 )
				$this->item_id_material = $id_titulo_item;

			require_once getsessionvar("local_base_dir") . "basic/bd.class.php";

			$this->cTitle				= "";
			$this->cTitle_ShortVersion	= "";
			
			$this->cIcon = "";
			$this->cCover = "";
			$this->cBackCover = "";
			
			$this->nIDSerie = 0;
			
			$this->cItem_Type  	           = "";
			$this->cDescrip_Item_Type_SP   = "";
			$this->cDescrip_Item_Type_ENG  = "";
			$this->cDescrip_Item_Type_PORT = "";
		
			$this->cItem_Short_Code_SP   = "";
			$this->cItem_Short_Code_ENG  = "";
			$this->cItem_Short_Code_PORT = "";
			
			$this->usuario_registro = "";
			
			$this->cAutor = "";
			
			$this->NOT_FOUND = false;
			
			$this->db_not_to_destroy = false;  // almost always, should be destroy
			
			if( $db_obj_already_create != NULL )
			{
				$this->db_not_to_destroy = true;  	 // just in this case, NOT TO DESTROY at the end
				$this->db = $db_obj_already_create;  // point to a previously created
				
			}
			else
				$this->db = new DB();  
			
			if( $is_item == 1 )
			{
				$resultset = $this->db->SubQuery( "SELECT a.ID_TITULO, a.ID_MATERIAL, a.SIGNATURA_PREFIJO, a.SIGNATURA_CLASE, a.SIGNATURA_LIBRISTICA, a.STATUS, a.BLOQUEADO_PARA_USUARIO, a.BLOQUEADO_DESDE, a.BLOQUEADO_HASTA, b.NOMBRE_BIBLIOTECA " .
												  "FROM acervo_copias a " .
												  "  LEFT JOIN cfgbiblioteca b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA) " .
												  "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_ITEM=$this->nIDItem;" );
				
				if( $row = $this->db->FetchRecord( $resultset ) )
				{ 
					$this->cNombreBiblioteca = $row["NOMBRE_BIBLIOTECA"]; 
					
					$this->nIDTitulo        = $row["ID_TITULO"]; 
					$this->item_id_material = $row["ID_MATERIAL"];
					$this->item_prefix      = $row["SIGNATURA_PREFIJO"];
					$this->item_class_no    = $row["SIGNATURA_CLASE"];
					$this->item_book_no     = $row["SIGNATURA_LIBRISTICA"];
					$this->cStatus 		    = $row["STATUS"];
					
					$this->Bloqueado_para_ID_Usuario = $row["BLOQUEADO_PARA_USUARIO"];
					$this->Bloqueado_desde 			 = $row["BLOQUEADO_DESDE"];
					$this->Bloqueado_hasta 			 = $row["BLOQUEADO_HASTA"];
				}
				else
					$this->NOT_FOUND = true;
				
				$this->db->ReleaseResultset( $resultset );
			}			
			else if( $is_item == 2 )
			{
				$resultset = $this->db->SubQuery( "SELECT a.ID_TITULO, a.ID_ITEM, a.ID_MATERIAL, a.SIGNATURA_PREFIJO, a.SIGNATURA_CLASE, a.SIGNATURA_LIBRISTICA, a.STATUS, a.BLOQUEADO_PARA_USUARIO, a.BLOQUEADO_DESDE, a.BLOQUEADO_HASTA, b.NOMBRE_BIBLIOTECA " .
												 "FROM acervo_copias a " .
												 "  LEFT JOIN cfgbiblioteca b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA) " .
												 "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_MATERIAL='$id_titulo_item' " );
				
				if( $row = $this->db->FetchRecord( $resultset ) )
				{ 
					$this->cNombreBiblioteca = $row["NOMBRE_BIBLIOTECA"]; 
					
					$this->nIDTitulo     = $row["ID_TITULO"]; 
					$this->nIDItem       = $row["ID_ITEM"]; 
					$this->item_prefix   = $row["SIGNATURA_PREFIJO"];
					$this->item_class_no = $row["SIGNATURA_CLASE"];
					$this->item_book_no  = $row["SIGNATURA_LIBRISTICA"];
					$this->cStatus 		 = $row["STATUS"];
					
					$this->Bloqueado_para_ID_Usuario = $row["BLOQUEADO_PARA_USUARIO"];
					$this->Bloqueado_desde 			 = $row["BLOQUEADO_DESDE"];
					$this->Bloqueado_hasta 			 = $row["BLOQUEADO_HASTA"];					
				}
				else
					$this->NOT_FOUND = true;
				
				$this->db->ReleaseResultset( $resultset );
			}			
			
			if( !$this->NOT_FOUND )
			{
				// Datos PRIMARIOS
				$resultset = $this->db->SubQuery( 
										"SELECT a.ID_TIPOMATERIAL, a.ID_SERIE, a.STATUS, a.USUARIO_REGISTRO, a.PORTADA, a.CONTRAPORTADA, b.DESCRIPCION, b.DESCRIPCION_ENG, b.DESCRIPCION_PORT, b.ICONO, b.CODIGO_MARC, b.CODIGO_MARC_ENG, b.CODIGO_MARC_PORT " .
										 "FROM acervo_titulos a " .
										 "    LEFT JOIN marc_material b ON (b.ID_TIPOMATERIAL=a.ID_TIPOMATERIAL)" .
										 " WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TITULO=$this->nIDTitulo" );

				if( $row = $this->db->FetchRecord( $resultset ) )
				{ 
					$this->nIDSerie = $row["ID_SERIE"];
					$this->cIcon    = $row["ICONO"];
					$this->cCover   = $row["PORTADA"];
					$this->cBackCover = $row["CONTRAPORTADA"];
					
					$this->cItem_Type 				= $row["ID_TIPOMATERIAL"];
					$this->cDescrip_Item_Type_SP  	= $row["DESCRIPCION"];
					$this->cDescrip_Item_Type_ENG 	= $row["DESCRIPCION_ENG"];
					$this->cDescrip_Item_Type_PORT	= $row["DESCRIPCION_PORT"];

					$this->cItem_Short_Code_SP		= $row["CODIGO_MARC"];
					$this->cItem_Short_Code_ENG		= $row["CODIGO_MARC_ENG"];
					$this->cItem_Short_Code_PORT	= $row["CODIGO_MARC_PORT"];
					
					$this->usuario_registro 		= $row["USUARIO_REGISTRO"];
				}
				else
					$this->NOT_FOUND = true;

				$this->db->ReleaseResultset( $resultset );
				
				// DATOS OBTENIDOS DE PROPIEDADES
				// MARC
				
				// TITULO
				$resultset = $this->db->SubQuery(  "SELECT a.*, b.CONECTOR_AACR " .
							     "FROM acervo_catalogacion a " .
							     "  LEFT JOIN marc_codigo21 b ON (b.ID_CAMPO=a.ID_CAMPO and b.CODIGO=a.CODIGO and b.NIVEL_MARC=9)" .
							     " WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TITULO=$this->nIDTitulo and a.ID_CAMPO='245' and a.CODIGO<>'' " );

				while( $row = $this->db->FetchRecord( $resultset ) )
				{ 
					// siguiente campo por concatenar
					//echo "[" . $this->cTitle_ShortVersion . "]";
					
					if( $row["CONECTOR_AACR"] != "" and $this->cTitle != "" )
					{	
						$cLastChar = substr( $this->cTitle, strlen($this->cTitle)-1, 1 );
						$is_lastchar_a_connector = $cLastChar == "/" or $cLastChar == ":" or $cLastChar == ";";
						
						// si el último caracter fuese un conector que el usuario catalogó
						// aunque sea incorrecto debe permanecer
						
						if( $cLastChar != $row["CONECTOR_AACR"] and !$is_lastchar_a_connector ) 
							$this->cTitle .= " " . $row["CONECTOR_AACR"] . " ";
						else 
							$this->cTitle .= " ";
							
						if( $row["CODIGO"] != "\$c" )
						{
							$cLastChar = substr( $this->cTitle_ShortVersion, strlen($this->cTitle_ShortVersion)-1, 1 );
							$is_lastchar_a_connector = $cLastChar == "/" or $cLastChar == ":" or $cLastChar == ";";

							if( $cLastChar != $row["CONECTOR_AACR"] and !$is_lastchar_a_connector ) 
								$this->cTitle_ShortVersion .= " " . $row["CONECTOR_AACR"] . " ";
							else 
								$this->cTitle_ShortVersion .= " ";
						}
					}
				
					$this->cTitle .= $row["VALOR"];
					
					if( $row["CODIGO"] != "\$c" )
						$this->cTitle_ShortVersion .= $row["VALOR"];

				}
				
				// eliminar últimos caracteres "BASURA"
				$this->cTitle_ShortVersion = trim( $this->cTitle_ShortVersion );
				$last_char = substr( $this->cTitle_ShortVersion, strlen($this->cTitle_ShortVersion)-1, 1 );
				
				if( $last_char=="/" or $last_char==":" or $last_char==";" )
				{ $this->cTitle_ShortVersion = trim(substr( $this->cTitle_ShortVersion, 0, strlen($this->cTitle_ShortVersion)-1 )); }
				
				// Titulo largo
				$this->cTitle = trim( $this->cTitle );
				$last_char = substr( $this->cTitle, strlen($this->cTitle)-1, 1 );
				
				if( $last_char=="/" or $last_char==":" or $last_char==";" )
				{ $this->cTitle = trim(substr( $this->cTitle, 0, strlen($this->cTitle)-1 )); }
				
				$this->db->ReleaseResultset( $resultset );
				
				// AUTOR
				$resultset = $this->db->SubQuery( "SELECT a.*, b.CONECTOR_AACR " .
												   "FROM acervo_catalogacion a " .
												   "LEFT JOIN marc_codigo21 b ON (b.ID_CAMPO=a.ID_CAMPO and b.CODIGO=a.CODIGO and b.NIVEL_MARC=9)" .
												   " WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TITULO=$this->nIDTitulo and a.ID_CAMPO='100' and a.CODIGO<>'' " );
				
				while( $row = $this->db->FetchRecord( $resultset ) )
				{ 
					if( $row["CONECTOR_AACR"] != "" and $thiscAutor != "" )
					{	
						if( substr( $this->cAutor, strlen($this->cAutor)-1, 1 ) != $row["CONECTOR_AACR"] ) 
							$this->cAutor .= " " . $row["CONECTOR_AACR"] . " ";
						else 
							$this->cAutor .= " ";

					}
				
					$this->cAutor .= $row["VALOR"];
				}

				$this->db->ReleaseResultset( $resultset );
			}
		}
		
		function Material_ShortCode()
		{
			// PENDIENTE: colocar get_translation
			if( getsessionvar("language") == 1 )
				return $this->cItem_Short_Code_SP;
			else if( getsessionvar("language") == 2 )
				return $this->cItem_Short_Code_ENG;
		}
		
		function CallNumber()
		{
			if( $this->item_prefix == "" and $this->item_class_no == "" and $this->item_book_no == "" )
				return "N/A";
			else
				return trim( $this->item_prefix . " " . $this->item_class_no . " " . $this->item_book_no );
		}
		
		//
		// 09-dic-2009: Solo disponibles para préstamo
		//
		function ObtenerNumeroCopias( $solo_disponibles_para_prestamos='N' )
		{	
			$ret = 0;
			
			if( $solo_disponibles_para_prestamos == "N" )
			{
				$query = "SELECT COUNT(*) AS CUANTOS FROM acervo_copias a" . 	
						 " WHERE a.ID_BIBLIOTECA=$this->nIDBiblioteca and a.ID_TITULO=$this->nIDTitulo and a.STATUS<>'X'; ";
			}
			else
			{
				// Excluir las copias DESCARTADAS, las BLOQUEADAS y AQUELLAS SOLO DE USO INTERNO
				$query = "SELECT COUNT(*) AS CUANTOS FROM acervo_copias a" . 	
						 " WHERE a.ID_BIBLIOTECA=$this->nIDBiblioteca and a.ID_TITULO=$this->nIDTitulo and (a.STATUS<>'X' and a.STATUS<>'I' and a.STATUS<>'B'); ";
			}

			$resultset = $this->db->SubQuery( $query ); 
					   
			if( $row = $this->db->FetchRecord( $resultset ) )
			{
				$ret = $row["CUANTOS"];
			}
			
			$this->db->ReleaseResultset( $resultset );

			return $ret;
		}
		
		//
		//  tipo = 1 USERNAME
		//  tipo = 2 PATERNO + MATERNO + NOMBRE
		//
		function ObtenerUsuarioRegistro( $tipo )
		{
			$ret = "";
			
			if( $this->usuario_registro != "" )
			{
				$resultset = $this->db->SubQuery( "SELECT USERNAME, PATERNO, MATERNO, NOMBRE FROM cfgusuarios WHERE ID_BIBLIOTECA=$this->nIDBiblioteca and ID_USUARIO=$this->usuario_registro " );
				
				if( $row = $this->db->FetchRecord( $resultset ) )
				{
					if( $tipo == 1 )
						$ret = $row["USERNAME"];
					else
						$ret = $row["NOMBRE"] . " " . $row["PATERNO"] . " " . $row["NOMBRE"];
				}
				
				$this->db->ReleaseResultset( $resultset );
			}
			
			return $ret;
		}
		
		//
		// Esta función analizará la disponiblidad de TODAS LAS COPIAS de un TITULO
		// Devuelve el ID_ITEM de la copia que esté disponible
		//
		// Devuelve -1 no hay copias que analizar
		// Devuelve 0 cuando ninguna copia está disponible
		//
		function VerificarDisponibilidad_X_TITULO( $verificar_fecha_actual, $fecha_desde, $fecha_hasta, &$fechas_disponibles )
		{
			$ret = -1;
			$array_copias = Array();
			
			// saber cuales copias se analizarán
			$resultset = $this->db->SubQuery( "SELECT a.ID_ITEM FROM acervo_copias a " .
											" WHERE a.ID_BIBLIOTECA=$this->nIDBiblioteca and a.ID_TITULO=$this->nIDTitulo and a.STATUS<>'X'; " );

			while( $row = $this->db->FetchRecord( $resultset ) )
			{ 
				$array_copias[] = $row["ID_ITEM"];
			}
			
			$this->db->ReleaseResultset( $resultset );

			if( count($array_copias) == 0 ) 
				return $ret;
			else
			{
				for( $i = 0; $i < count($array_copias); $i++ )
				{
					// primer argumento es el ID_ITEM
					if( $this->VerificarDisponibilidad_X_ITEM( $array_copias[$i], $verificar_fecha_actual, $fecha_desde, $fecha_hasta, $fechas_disponibles ) == 1 )
					{
						$ret = $array_copias[$i];
						break;
					}
				}
				
				if( $ret == -1 ) $ret = 0;
			}
			
			unset( $array_copias );
			
			return $ret;
		}
		
		//
		// Esta función analizará la disponiblidad de 1 COPIA
		//
		//  devuelve 0 - Si el ITEM no está disponible en esas fechas    
		//  devuelve  en el array   fechas_disponibles las fechas en que estuviera disponibles en el rango solicitado
		//
		//  type = 1 (Devuelve 1/0 con base en disponibilidad)
		//  type = 2 (Devuelve array de análisis)
		//
		//  19nov2009 - Se agrega parámetro renova_for_this_user, para verificar una disponibilidad ESPECÍFICA PARA ALGÚN USUARIO
		//  23nov2009 - Se agrega parámetro reserva_for_this_user, PENDIENTE = prestamo_for_this_user
		//
		function VerificarDisponibilidad_X_ITEM( $id_item, $verificar_fecha_actual, $fecha_desde, $fecha_hasta, &$fechas_disponibles, $offset=15, $type=1, 
			$reserva_for_this_user=0, $prestamo_for_this_user=0, $renova_for_this_user=0 )
		{
			$ret = 0;

			$aValoresFechaDesde = decodedate( $fecha_desde, 1 );
			$aValoresFechaHasta = decodedate( $fecha_hasta, 1 );
			
			// fecha actual
			$date_stamp_from = mktime( $aValoresFechaDesde["hrs"], $aValoresFechaDesde["mins"], $aValoresFechaDesde["secs"], $aValoresFechaDesde["m"], $aValoresFechaDesde["d"], $aValoresFechaDesde["a"] );
			$date_stamp_from_sp = $date_stamp_from;
			
			$date_stamp_from -= ($offset*(24 * 60 * 60));
			
			$date_stamp_to = mktime( $aValoresFechaHasta["hrs"], $aValoresFechaHasta["mins"], $aValoresFechaHasta["secs"], $aValoresFechaHasta["m"], $aValoresFechaHasta["d"], $aValoresFechaHasta["a"] );
			$date_stamp_to_sp =  $date_stamp_to;
			
			$date_stamp_to += ($offset*(24 * 60 * 60));
			
			$array_fechas = array();
			$array_fechas_human = array();
			
			// llenar el array con fechas
			for( $fecha = $date_stamp_from; $fecha <= $date_stamp_to; $fecha += (24 * 60 * 60) )
			{
				//echo "[" . encodedate_to_human_format( $fecha, 0 ) . "]";
				//echo "<br><br>";				
				$tmpstr = encodedate_to_human_format( $fecha );
				
				$array_fechas[] = Array( "date" => $tmpstr, 
										 "disponible" => 1, 
										 "no_reserva" => -1,
										 "fecha_reserva" => "",
										 "id_usuario_reserva" => -1, 
										 "nombre_usuario_reserva" => "",
										 "no_prestamo" => -1, 
										 "fecha_prestamo" => "",
										 "fecha_devolucion_programada" => "",
										 "prestamo_vencido" => "",
										 "id_usuario_prestamo" => -1, 
										 "nombre_usuario_prestamo" => "" );
				
				if( $fecha >= $date_stamp_from_sp and $fecha <= $date_stamp_to_sp )
				{
					$array_fechas_human[] = $tmpstr;
				}
			}

			//
			// analizar todas las fechas
			// y verificar las reservaciones por ITEM específico (TIPO_RESERVACION=1)
			//
			for( $i=0; $i<count($array_fechas); $i++ )
			{
				if( $array_fechas[$i]["disponible"] == 1 or 
					($array_fechas[$i]["prestamo_vencido"] == "S" ))
				{
					$the_date = date_for_database_updates( $array_fechas[$i]["date"] );
					
					$resultset = $this->db->SubQuery( 
							"SELECT a.ID_RESERVACION, a.FECHA_RESERVACION, a.STATUS_RESERVACION, d.MAX_DIAS_PRESTAMO, c.ID_USUARIO, c.PATERNO, c.MATERNO, c.NOMBRE " . 
							 "FROM reservaciones_det a " .
							 "   LEFT JOIN reservaciones_mst b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_RESERVACION=a.ID_RESERVACION) " .
							 "     LEFT JOIN cfgusuarios c ON (c.ID_BIBLIOTECA=b.ID_BIBLIOTECA and c.ID_USUARIO=b.ID_USUARIO) " .
							 "         LEFT JOIN cfgusuarios_grupos d ON (d.ID_BIBLIOTECA=c.ID_BIBLIOTECA and d.ID_GRUPO=c.ID_GRUPO) " .
							 " WHERE a.ID_BIBLIOTECA=$this->nIDBiblioteca and a.ID_TITULO=$this->nIDTitulo and a.ID_ITEM=$id_item and a.FECHA_RESERVACION='$the_date' and " . 
							 "       a.TIPO_RESERVACION=1 and (a.STATUS_RESERVACION='E' or a.STATUS_RESERVACION='P' or a.STATUS_RESERVACION='R');" );
							 
					/* echo "SELECT a.ID_RESERVACION, a.FECHA_RESERVACION, a.STATUS_RESERVACION, d.MAX_DIAS_PRESTAMO, c.ID_USUARIO, c.PATERNO, c.MATERNO, c.NOMBRE " . 
							 "FROM reservaciones_det a " .
							 "   LEFT JOIN reservaciones_mst b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_RESERVACION=a.ID_RESERVACION) " .
							 "     LEFT JOIN cfgusuarios c ON (c.ID_BIBLIOTECA=b.ID_BIBLIOTECA and c.ID_USUARIO=b.ID_USUARIO) " .
							 "         LEFT JOIN cfgusuarios_grupos d ON (d.ID_BIBLIOTECA=c.ID_BIBLIOTECA and d.ID_GRUPO=c.ID_GRUPO) " .
							 " WHERE a.ID_BIBLIOTECA=$this->nIDBiblioteca and a.ID_TITULO=$this->nIDTitulo and a.ID_ITEM=$id_item and a.FECHA_RESERVACION='$the_date' and " . 
							 "       a.TIPO_RESERVACION=1 and (a.STATUS_RESERVACION='E' or a.STATUS_RESERVACION='P' or a.STATUS_RESERVACION='R');";
					 echo "<br><br>";
					*/		 
					if( ($row = $this->db->FetchRecord($resultset)) )
					{
						//echo "SI_RESERVADO {" . $array_fechas[$i]["date"] . "}";
						//$array_fechas[$i]["disponible"] = 0;
						$array_fechas[$i]["no_reserva"] = $row["ID_RESERVACION"];
						$array_fechas[$i]["fecha_reserva"] = get_str_datetime( $row["FECHA_RESERVACION"], 0, 0 );
						$array_fechas[$i]["id_usuario_reserva"] = $row["ID_USUARIO"];
						$array_fechas[$i]["nombre_usuario_reserva"] = $row["NOMBRE"] . " " . $row["PATERNO"]; // $row["ID_USUARIO"];

						if( $row["ID_USUARIO"] == $reserva_for_this_user or ($row["STATUS_RESERVACION"]=="R" and $row["ID_USUARIO"] == $prestamo_for_this_user ) )
							$array_fechas[$i]["disponible"]  =  1;  // porque es el mismo usuario de la reserva
						else
						{
							$array_fechas[$i]["disponible"]  = 0;
						}
						
						$aValores_the_date = decodedate( $array_fechas[$i]["date"] );
						
						for( $j=1; $j<$row["MAX_DIAS_PRESTAMO"]; $j++ )
						{
							$date_stamp = mktime( 0, 0, 0, $aValores_the_date["m"], $aValores_the_date["d"], $aValores_the_date["a"] );
							$date_stamp += ($j*(24 * 60 * 60)); // fecha en timestamp
							
							$array_fechas[$i + $j]["date"] = encodedate_to_human_format( $date_stamp );
							//$array_fechas[$i + $j]["disponible"] = 0;
							$array_fechas[$i + $j]["no_reserva"] = $row["ID_RESERVACION"];						
							$array_fechas[$i + $j]["fecha_reserva"] = get_str_datetime( $row["FECHA_RESERVACION"], 0, 0 );
							$array_fechas[$i + $j]["id_usuario_reserva"] = $row["ID_USUARIO"];
							$array_fechas[$i + $j]["nombre_usuario_reserva"] = $row["NOMBRE"] . " " . $row["PATERNO"]; // $row["ID_USUARIO"];	
							
							if( $row["ID_USUARIO"] == $reserva_for_this_user or ($row["STATUS_RESERVACION"]=="R" and $row["ID_USUARIO"] == $prestamo_for_this_user ) )
							{
								$array_fechas[$i+$j]["disponible"] = 1;  // porque es el mismo usuario de la reserva 
							}
							else
							{
								$array_fechas[$i+$j]["disponible"] = 0;
							}
							
						}
					}
					
					$this->db->ReleaseResultset( $resultset );
									
					//
					// VERIFICAR EN PRESTAMOS
					// solo si no está reservado
					//
					
					if( $array_fechas[$i]["no_prestamo"] == -1 )
					{
						// for debugging
						//echo " {NO RESERVADO} ";
						
						// NO! está reservado
						// verificar los préstamos de ese item con status NO DEVUELTO
						$resultset = $this->db->SubQuery( 
										 "SELECT a.ID_PRESTAMO, a.STATUS, b.FECHA_PRESTAMO, a.FECHA_DEVOLUCION_PROGRAMADA, a.FECHA_DEVOLUCION, a.RENOVACION_SN, " . 
										 "       c.ID_USUARIO, c.PATERNO, c.MATERNO, c.NOMBRE, d.MAX_DIAS_PRESTAMO, d.MAX_RENOVACIONES, d.DIAS_RENOVACION_DEFAULT " .
										 "FROM prestamos_det a " .
										 "   LEFT JOIN prestamos_mst b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PRESTAMO=a.ID_PRESTAMO) " .
										 "     LEFT JOIN cfgusuarios c ON (c.ID_BIBLIOTECA=b.ID_BIBLIOTECA and c.ID_USUARIO=b.ID_USUARIO) " .
										 "         LEFT JOIN cfgusuarios_grupos d ON (d.ID_BIBLIOTECA=c.ID_BIBLIOTECA and d.ID_GRUPO=c.ID_GRUPO) " .										 
										 " WHERE a.ID_BIBLIOTECA=$this->nIDBiblioteca and a.ID_ITEM=$id_item and a.STATUS<>'D' and " . 
										 "   ('$the_date' BETWEEN b.FECHA_PRESTAMO and a.FECHA_DEVOLUCION_PROGRAMADA) " );
										 
						while( ($row = $this->db->FetchRecord($resultset)) )
						{
							// decodedate() debe recibir una fecha en HUMAN format>
							$aValores_desde = decodedate( get_str_datetime( $row["FECHA_PRESTAMO"], 0, 0 ) );
							
							// VERIFICAR STATUS DEL PRESTAMO
							if( $row["STATUS"] == "P" )
							{
								// ITEM Está pendiente de devolver en la fecha programada
								// 27ago2009 - Se incorporan valores de hrs, mins y secs
								//echo "PENDIENTE DE DEVOLVER... ";
								
								$aValores_hasta = decodedate( get_str_datetime( $row["FECHA_DEVOLUCION_PROGRAMADA"], 1, 0, 1 ), 1 );  
							}
							else if( $row["STATUS"] == "S" )
							{
								// SANCION por devolución fuera de tiempo
								// 27ago2009 - se incorporan valores de hrs, mins y secs
								$aValores_hasta = decodedate( getcurdate_human_format(), 1);  // Está pendiente de devolver en la fecha programada
																							  // se toma la fecha HASTA - como el día de hoy - FECHA MAXIMA
							}
							else if( $row["STATUS"] == "D" )
							{
								// ITEM Devuelto
								// 21-oct-2009
								$aValores_hasta = decodedate( get_str_datetime( $row["FECHA_DEVOLUCION"], 1) );  // Fue devuelto
							}							
							
							$nIDPrestamo      = $row["ID_PRESTAMO"];
							$id_usuario_prestamo = $row["ID_USUARIO"];
							$nombre_usuario   = $row["NOMBRE"] . " " . $row["PATERNO"] ;
							$fecha_devol_prog = get_str_datetime( $row["FECHA_DEVOLUCION_PROGRAMADA"], 0, 0 );
							$fecha_prestamo   = get_str_datetime( $row["FECHA_PRESTAMO"], 0, 0 );
							$status 		  = $row["STATUS"];
							
							$renovaciones_efectuadas_sobre_item = 0;
							$max_renovaciones_posibles = $row["MAX_RENOVACIONES"];
							$dias_renovacion_default   = $row["DIAS_RENOVACION_DEFAULT"];
							
							//
							// SI HA SIDO RENOVADO
							// VERIFICAR SOLO EN ITEMS Pendientes de DEVOLVER
							//
							if( $row["RENOVACION_SN"] == "S" and $status != "D" )
							{
								// obtener APLAZAMIENTO en entrega por una posible renovación
								$result_renovs = db_query( "SELECT COUNT(*) AS CUANTOS, MAX(NUEVA_FECHA_DEVOLUCION) AS NEW_FECHA_DEV ".
															"FROM renovaciones WHERE ID_BIBLIOTECA=$this->nIDBiblioteca and".
														    "   ID_PRESTAMO=" . $row["ID_PRESTAMO"] . " and ID_ITEM=$id_item" );

								 if( $sp_row = db_fetch_row( $result_renovs ) )
								 {
									$renovaciones_efectuadas_sobre_item = $sp_row["CUANTOS"];
									
									if( $sp_row["CUANTOS"] > 0 )
									{
										$aValores_hasta = decodedate( get_str_datetime( $sp_row["NEW_FECHA_DEV"], 1, 0, 1 ), 1 );  
									}
								 }
								 
								 free_dbquery( $result_renovs );
							}

							// fechas en formato CPU / Unix Style
							$date_stamp_desde  = mktime( 0, 0, 0, $aValores_desde["m"], $aValores_desde["d"], $aValores_desde["a"] );
							$date_stamp_hasta  = mktime( $aValores_hasta["hrs"], $aValores_hasta["mins"], $aValores_hasta["secs"], 
														 $aValores_hasta["m"], $aValores_hasta["d"], $aValores_hasta["a"] );
							
							// hacer un loop día por día, para todos los que abarca el préstamo o el lapso de fechas
							for( $fecha=$date_stamp_desde; $fecha<=$date_stamp_hasta; $fecha += (24 * 60 * 60) )
							{
								//echo "<br>" . encodedate_to_human_format( $fecha ) . "<br>";

								// ubicar la fecha en el array de fechas
								for( $x=0; $x<count($array_fechas); $x++ )
								{
									if( $array_fechas[$x]["date"] == encodedate_to_human_format( $fecha ) )
									{
										$array_fechas[$x]["disponible"]  = 0;
										$array_fechas[$x]["no_prestamo"] = $nIDPrestamo;
										$array_fechas[$x]["prestamo_vencido"] = "N";  // no está vencido... está dentro del lapso
										$array_fechas[$x]["fecha_prestamo"] = $fecha_prestamo;
										$array_fechas[$x]["fecha_devolucion_programada"] = $fecha_devol_prog;
										$array_fechas[$x]["nombre_usuario_prestamo"] = $nombre_usuario; // $row["ID_USUARIO"];				
										$array_fechas[$x]["id_usuario_prestamo"] = $id_usuario_prestamo;
									}
								}
							}					

							if( $status == "P" )
							{
								// Está Prestado y pendiente de devolución
								// SE deberá llenar como NO DISPONIBLE, las fechas que siguen al día de vencimiento NORMAL
								// hasta la máxima posible fecha de renovación
								//
								$dias_de_posible_renovacion = ($max_renovaciones_posibles - $renovaciones_efectuadas_sobre_item) * $dias_renovacion_default;
								
								$fecha_renovacion_maxima = $date_stamp_hasta + ($dias_de_posible_renovacion*(24 * 60 * 60)); 
								
								// CONTAR A PARTIR DEL SIGUIENTE DÍA
								$date_stamp_hasta += (24 * 60 * 60);
								
								for( $fecha=$date_stamp_hasta; $fecha<=$fecha_renovacion_maxima; $fecha += (24 * 60 * 60) )
								{
									// ubicar la fecha en el array de fechas
									for( $x=0; $x<count($array_fechas); $x++ )
									{
										if( $array_fechas[$x]["date"] == encodedate_to_human_format( $fecha ) )
										{
											$array_fechas[$x]["no_prestamo"] = $nIDPrestamo;
											$array_fechas[$x]["prestamo_vencido"] = "S";
											$array_fechas[$x]["fecha_prestamo"] = $fecha_prestamo;
											$array_fechas[$x]["fecha_devolucion_programada"] = ""; // $fecha_devol_prog;
											$array_fechas[$x]["nombre_usuario_prestamo"] = $nombre_usuario;

											if( $id_usuario_prestamo == $renova_for_this_user )
											{
												$array_fechas[$x]["disponible"]  =  1;  // porque es el mismo usuario del prestamo
												$array_fechas[$x]["fecha_devolucion_programada"] = "";
											}
											else
												$array_fechas[$x]["disponible"]  = 0;
											
										}
									}
								}

							}
							
						}
						
						$this->db->ReleaseResultset( $resultset );
					}
				}

				//print_r( $array_fechas[$i] );
				//echo $array_fechas[$i]["date"] . "<br><br>";
			}
			
			//
			// verificar disponibilidad en todo el rango de fechas que el usuario SOLICITA
			//
			$todos_libres = 1;
			
			for( $i=0; $i<count($array_fechas_human); $i++ )
			{
				//echo $array_fechas_human[$i] . "    &nbsp; ";
				
				// verificar el status del flag DISPONIBLE derivado del análisis anterior
				for( $j=0; $j<count($array_fechas); $j++ )
				{
					if( $array_fechas[$j]["date"] == $array_fechas_human[$i] )
					{
						if( $array_fechas[$j]["disponible"] == 1 ) 
							$fechas_disponibles[] = Array( $id_item, $array_fechas[$j]["date"] );
							
						if( $array_fechas[$j]["disponible"] <> 1 )
						{
							$todos_libres = 0;
						}
					}
				}
			}
			
			if( $type == 1 )
			{
				if( $todos_libres == 1 )
					$ret = 1;
					
				unset( $array_fechas );  // destruir el array de análisis
				
				return $ret;
			}
			else			
			{
				return $array_fechas;
			}
		}
		
		function VerificaNumeroRenovaciones( $id_prestamo )
		{
			$reservaciones_anteriores = 0;
			
			$resultset = $this->db->SubQuery( "SELECT COUNT(*) AS CUANTOS ".
							  "FROM renovaciones ".
							   "WHERE ID_BIBLIOTECA=$this->nIDBiblioteca and ID_PRESTAMO=$id_prestamo and ID_ITEM=$this->nIDItem;" );
	
			$reservaciones_anteriores = 0;
	
			if( ($row = $this->db->FetchRecord($resultset)) )
			{
				$reservaciones_anteriores = $row["CUANTOS"];
			}
			
			$this->db->ReleaseResultset( $resultset );
			
			return $reservaciones_anteriores;
		}

		function ObtenerHistorialRenovaciones( $id_prestamo )
		{		
			$resultset = $this->db->SubQuery( "SELECT ID_RENOVACION, FECHA_RENOVACION, NUEVA_FECHA_DEVOLUCION ".
											 "FROM renovaciones ".
											 "WHERE ID_BIBLIOTECA=$this->nIDBiblioteca and ID_PRESTAMO=$id_prestamo and ID_ITEM=$this->nIDItem;" );
	
			$info_renovaciones = Array();
	
			while( ($row = $this->db->FetchRecord($resultset)) )
			{
				$info_renovaciones[] = Array( "id_renovacion" => $row["ID_RENOVACION"], 
											  "fecha_renovacion" => $row["FECHA_RENOVACION"], 
											  "nueva_fecha_devolucion" => dbdate_to_human_format($row["NUEVA_FECHA_DEVOLUCION"], 1) );
			}
			
			$this->db->ReleaseResultset( $resultset );
			
			return $info_renovaciones;
		}
		
		//
		// Devuelve la fecha de devolución de un ITEM prestamo
		// en formato DB date
		//
		function ObtenerFechaDevolucion( $id_prestamo )
		{		
			$hay_renovacion = false;
			
			$ret = "";
			
			$resultset = $this->db->SubQuery( "SELECT FECHA_DEVOLUCION_PROGRAMADA, RENOVACION_SN ".
												   "FROM prestamos_det " .
												   "WHERE ID_BIBLIOTECA=$this->nIDBiblioteca and ID_PRESTAMO=$id_prestamo and ID_ITEM=$this->nIDItem;" );
			
			if( ($row = $this->db->FetchRecord($resultset)) )
			{
				if( $row["RENOVACION_SN"] == "S" )
					$hay_renovacion = true;
					
				$ret = $row["FECHA_DEVOLUCION_PROGRAMADA"]; 
			}
			
			$this->db->ReleaseResultset( $resultset );

			// Verificar renovaciones
			if( $hay_renovacion )
			{
				$resultset = $this->db->SubQuery( "SELECT COUNT(*) AS CUANTOS, MAX(NUEVA_FECHA_DEVOLUCION) AS NEW_FECHA_DEV ".
														"FROM renovaciones WHERE ID_BIBLIOTECA=$this->nIDBiblioteca and ".
														"  ID_PRESTAMO=$id_prestamo and ID_ITEM=$this->nIDItem;");
		
				if( ($row = $this->db->FetchRecord($resultset)) )
				{
					if( $row["CUANTOS"] > 0 )
					{
						$ret = $row["NEW_FECHA_DEV"];  
					}
				}
				
				$this->db->ReleaseResultset( $resultset );
			}
			
			return $ret;
		}				
		
		// Calcula el tiempo de retraso entre
		// la fecha de devolución programada y una fecha que se presume actual
		function ObtenerTiempoRetraso( $TIMESTAMP_FechaHoraDEVPROGRAMADA, $TIMESTAMP_FechaHoraACTUAL )
		{
			$tmp = $TIMESTAMP_FechaHoraACTUAL - $TIMESTAMP_FechaHoraDEVPROGRAMADA;
			
			$result = array( 'secs' => 0, 
							 'mins' => 0, 
							 'hrs' => 0, 
							 'dias' => 0, 
							 'meses' => 0, 
							 'semanas' => 0,
							 'tsecs' => 0, 
							 'tmins' => 0, 
							 'thrs' => 0, 
							 'tdias' => 0,
							 'tsemanas' => 0);
							 
			if($TIMESTAMP_FechaHoraDEVPROGRAMADA > $TIMESTAMP_FechaHoraACTUAL) 
			{ 
				// NO HAY RETRASO
				return $result;
			} 
			else 
			{ 
				$tmp = $TIMESTAMP_FechaHoraACTUAL - $TIMESTAMP_FechaHoraDEVPROGRAMADA; 
			} 

			$seconds = $tmp; 

			// Relative //////// 
			$result['semanas'] = floor($tmp/604800); 
			$tmp -= $result['semanas'] * 604800; 

			$result['dias'] = floor($tmp/86400); 
			$tmp -= $result['dias'] * 86400; 

			$result['hrs'] = floor($tmp/3600); 
			$tmp -= $result['hrs'] * 3600; 

			$result['mins'] = floor($tmp/60); 
			$tmp -= $result['mins'] * 60; 

			$result['secs'] = $tmp; 
			 
			// Total /////////// 
			$result['tsemanas'] = floor($seconds/604800); 
			$result['tdias'] = floor($seconds/86400); 
			$result['thrs'] = floor($seconds/3600); 
			$result['tmins'] = floor($seconds/60); 
			$result['tsecs'] = $seconds; 
		
			return $result;
		}
		
		function ObtenerSerie_Info( &$id_colection, &$descrip_colection, &$date_1st_recept, &$frequency, &$descrip_frequency )
		{
			$frequency = "";
			$descrip_frequency = "";
			
			if( $this->nIDSerie != 0 )
			{
				$resultset = $this->db->SubQuery( "SELECT a.ID_COLECCION, a.ID_PERIODICIDAD, a.FECHA_PRIMERA_RECEPCION, b.CODIGO_CORTO, b.TERMINO ".
								 "FROM series a ".
								 "     LEFT JOIN tesauro_terminos b ON (b.ID_RED=(SELECT ID_RED FROM cfgbiblioteca WHERE ID_BIBLIOTECA=a.ID_BIBLIOTECA) and b.ID_TERMINO=a.ID_PERIODICIDAD) " .
								 "WHERE ID_BIBLIOTECA=$this->nIDBiblioteca and ID_SERIE=$this->nIDSerie;");
				
				if( ($row = $this->db->FetchRecord($resultset)) )
				{
					$id_colection = $row["ID_COLECCION"];

					$frequency = $row["CODIGO_CORTO"];
					
					$descrip_frequency = $row["TERMINO"];
					
					$date_1st_recept = $row["FECHA_PRIMERA_RECEPCION"];
				}
				
				$this->db->ReleaseResultset( $resultset );
			}
			
			return $frequency;
		}
		
		function ObtenerSerie_PrediccionPeriodo( $anio, $mes, $date_1st_recep, $first_recep_saved, $first_num_saved, $frecuencia, $monthNames )
		{
			$arrayNumeros = Array();
			
			// Unix Style
			$unix_style_val_1st_recep = convert_humandate_to_unixstyle( dbdate_to_human_format( $date_1st_recep ) );
			$unix_style_val_1st_saved = 0;
			
			$no_filter = "N";
			
			if( $anio == 0 )
			{
				$anio = date( "Y", $unix_style_val_1st_recep );
				$no_filter = "S";
			}
			
			$day_of_week = date( "w", $unix_style_val_1st_recep );
			
			if( $first_recep_saved != "" )
			{
				$unix_style_val_1st_saved = convert_humandate_to_unixstyle( dbdate_to_human_format( $first_recep_saved ) );
				$day_of_week = date( "w", $unix_style_val_1st_saved );
			}

			if( $mes == 0 )
			{
				$mes_desde = 1;
				$mes_hasta = 12;
			}
			else
			{
				$mes_desde = $mes;
				$mes_hasta = $mes;
			}

			$limit_from = mktime( 0, 0, 0, $mes_desde, 1, $anio );
			
			$limit_down = mktime( 0, 0, 0, $mes_hasta+1, 1, $anio );
			$limit_down -= (24 * 60 * 60);
			
			if( $no_filter == "S" )
			{
				$limit_down = time();				
			}


			if( $frecuencia == "D" or $frecuencia == "C" or ($frecuencia == "S" or $frecuencia =="W")) // Each 14 days
			{
				/* 
				 WEEKLY OR BY EACH 14 DAYS
				 **/
				$days_to_skip = 14;
				$limit = 45;
				
				if($frecuencia == "D" )
				{
					$days_to_skip = 1;
					$limit = 365;
				}
				else if($frecuencia == "S" or $frecuencia =="W")
				{
					$days_to_skip = 7;
					$limit = 10;
				}
				
				$first_num = 0;
				
				if( $unix_style_val_1st_saved != 0 )
				{
					// calcular fecha inicial basado en la fecha de primera recepción de un fascículo dentro del periodo
					$val_date = $unix_style_val_1st_saved;

					$backward = true;
					$first_num = $first_num_saved;
					
					while( $backward )
					{
						if( $val_date - ($days_to_skip*(24 * 60 * 60)) < $limit_from )
							$backward = false;
						else
						{
							$val_date -= ($days_to_skip*(24 * 60 * 60));
							$first_num = $first_num - 1;
						}
					}
				}
				else
				{
					// suponer fecha inicial, basada en la fecha de primera recepción de fascículos
					// aun cuando esta fecha fuera de otro año
					$val_date = mktime( 0, 0, 0, $mes_desde, 1, $anio );
					
					while( date( "w", $val_date ) != $day_of_week )
					{
						$val_date += (1*(24 * 60 * 60));
					}	

					$first_num	= 1;
				}
						
				for( $i=1; $i<=$limit; $i++ )
				{
					$val_date_end = $val_date + (($days_to_skip-1)*(24 * 60 * 60));
					
					$arrayNumeros[] = Array( "info_month" => $val_date, 
												"id_item" => "", "id_material" => "", 
												   "loan_category" => "", 
													 "num_date" => encodedate_to_human_format($val_date),
													   "num_date_end" => encodedate_to_human_format($val_date_end),
													    "num_part" => $first_num, "num_year" => date( "Y", $val_date ), "num_month" => date("m",$val_date), "num_special" => "",
														  "title" => "", "subtitle" => "", "price" => "", "status" => "" );

					if( $val_date + ($days_to_skip*(24 * 60 * 60)) > $limit_down )
						break;
					else					
						$val_date += ($days_to_skip*(24 * 60 * 60));
						
					$first_num++;
				}
			}
			else if( $frecuencia == "M" ) // Monthly
			{			
				/* 
				 MONTHLY
				 **/
				$first_num = 0;
				
				if( $unix_style_val_1st_saved != 0 )
				{
					// calcular fecha inicial basado en la fecha de primera recepción de un fascículo dentro del periodo
					$val_date = $unix_style_val_1st_saved;
					
					$backward = true;
					$first_num = $first_num_saved;
					
					while( $backward )
					{
						$m = date( "m", $val_date );
						
						if( $m == 1 )
							$month_before = mktime( 0, 0, 0, 12, date( "d", $val_date ), date( "Y", $val_date )-1 );
						else
							$month_before = mktime( 0, 0, 0, $m-1, date( "d", $val_date ), date( "Y", $val_date ) );
							
						if( $month_before < $limit_from )
							$backward = false;
						else
						{
							$val_date = $month_before;
							$first_num = $first_num - 1;
						}
					}
				}
				else
				{
					// suponer fecha inicial, basada en la fecha de primera recepción de fascículos
					// aun cuando esta fecha fuera de otro año
					$val_date = mktime( 0, 0, 0, $mes_desde, date( "d", $unix_style_val_1st_recep ), $anio );

					$first_num	= 1;
				}			
			
				$break_skips = false;
			
				for( $i=0; !$break_skips and $i<=500; $i++ )
				{
					$d = date( "d", $val_date );
					$m = date( "m", $val_date );
					$a = date( "Y", $val_date );
					
					if( $m == 12 )
						$next_month = mktime( 0, 0, 0, 1, $d, $a+1 );
					else
						$next_month = mktime( 0, 0, 0, $m+1, $d, $a );
						
					if( !$break_skips )
					{
						$month_num = date( "m", $val_date );
								
						$arrayNumeros[] = Array( "info_month" => $monthNames[$month_num-1], 
													"id_item" => "", "id_material" => "", 
													   "loan_category" => "", 
														 "num_date" => encodedate_to_human_format($val_date),
														   "num_date_end" => "",
														   "num_part" => $first_num, "num_year" => date( "Y", $val_date ), "num_month" => $month_num, "num_special" => "",
															 "title" => "", "subtitle" => "", "price" => "", "status" => "" );
						$val_date = $next_month;
						
						if( $val_date > $limit_down )
						{
							$break_skips = true;
						}						
									
						$first_num++;
					}
				}
			}
			
			return $arrayNumeros;
		}
		
		
		function ObtenerSerie_BuscarElemento( $arrayNumeros, $frequency, $db_row )
		{
			$ret = -1;
			
			for( $i=0; $i<count($arrayNumeros); $i++ and $ret==-1 )
			{
				$bFound = false;
					  
				if( $frequency == "M" )
				{
					//$bFound = $arrayNumeros[$i]["num_part"] == $db_row["NUMERO_PARTE"];
					$bFound = ( $arrayNumeros[$i]["num_year"] == $db_row["SERIES_ANIO"] and
								(int) $arrayNumeros[$i]["num_month"] == (int) $db_row["SERIES_MES"] );
				}
				else if( $frequency == "C" )
				{
					$bFound = $arrayNumeros[$i]["num_part"] == $db_row["NUMERO_PARTE"];
				}
				else if( $frequency == "W" or $frequency == "S")
				{
					$bFound = $arrayNumeros[$i]["num_part"] == $db_row["NUMERO_PARTE"];
				}				
					  
				if( $bFound ) 
				{
					$ret=$i;
					break;
				}
			}
			
			return $ret;
		}
		
		
		function destroy()
		{
			if( !$this->db_not_to_destroy ) 
				$this->db->destroy();
		}

	}
	
	//
	//
	//
	class TUser
	{
		var $nIDBiblioteca;
		var $nIDUser;
		
		public $bulkMailConnection;
		
		protected $self = array();
		
		/* magic methods */
		public function __get( $name = null ) {
			return $this->self[$name];
		}		

		public function __set( $name = null, $value ) {
			return $this->self[$name] = $value;
		}

		var $db;
		var $db_not_to_destroy;
		
		var $NOT_FOUND;
		
		//
		function TUser( $id_biblioteca=0, $id_usuario, $db_obj_already_create=null )
		{
			$this->nIDBiblioteca = $id_biblioteca;
			$this->nIDUser = $id_usuario;
						
			$this->bulkMailConnection = null;

			require_once getsessionvar("local_base_dir") . "basic/bd.class.php";
			
			$this->NOT_FOUND = false;
			
			/* props referentes a prestamos */
			$this->GRUPO_MAX_ITEMS_PRESTADOS = 0;
			$this->GRUPO_MAX_DIAS_PRESTAMO   = 0;		
			$this->GRUPO_PERMITIRPRESTAMOS_CON_RETRASOS = "";
			$this->GRUPO_PERMITIRPRESTAMOS_CON_SANCIONES = "";

			/* props referentes a renovaciones */
			$this->GRUPO_MAX_RENOVACIONES 			  = 0;
			$this->GRUPO_DIAS_RENOVACION_DEFAULT      = 0;
			$this->GRUPO_PERMITIRRENOVA_CON_RETRASO   = "";
			$this->GRUPO_PERMITIRRENOVA_CON_SANCIONES = "";
			
			/* props referentes a reservaciones */
			$this->GRUPO_MAX_RESERVACIONES 			   = 0;
			$this->GRUPO_PERMITIRRESERVA_CON_SANCIONES = "";
			
			/* variables Buffer */
			$this->ALREADY_COUNTED_LOANS     = NULL;
			$this->ALREADY_COUNTED_SANCTIONS = NULL;
			$this->ALREADY_COUNTED_BLOCKED_ITEMS = NULL;
			$this->ALREADY_COUNTED_RESERVAS = NULL;
			$this->ALREADY_COUNTED_RESTRICTIONS = NULL;
			
			$this->EXIST_RESTRICTION_RESERVA = false;
			$this->EXIST_RESTRICTION_PRESTAMO = false;
			$this->EXIST_RESTRICTION_RENOVACION = false;
			$this->EXIST_RESTRICTION_CONSULTA = false;
			
			$this->db_not_to_destroy = false;  // almost always, should be destroyed
			
			if( $db_obj_already_create != NULL )
			{
				$this->db_not_to_destroy = true;  	 // just in this case, NOT TO DESTROY at the end
				$this->db = $db_obj_already_create;  // point to a previously created
			}
			else
				$this->db = new DB();  
					
			$translate_to_html = 1;
			
			if( $id_usuario == -1 )
			{
				$this->ID_USUARIO = -1;
				
				$this->USERNAME = "webmaster";
				
				$this->NOMBRE	= "Administrador";
				$this->PATERNO	= "del Servicio";
				$this->MATERNO	= "BiblioTEK";
				
				$this->LIBRARY_NAME = "Default INFO";
				$this->NOMBRE_COMPLETO = $this->NOMBRE . " " . $this->PATERNO . " " . $this->MATERNO;
				
				$this->EMAIL			= "jlopez@grupoges.com.mx";
				$this->EMAIL_ALTERNO	= "jlopez@grupoges.com.mx";
				
				$translate_to_html = 0; // Generalmente es para emails
				
				//return true;
			}
				
			// Abrir el SELECT
			$resultset = $this->db->SubQuery( " SELECT a.ID_BIBLIOTECA, a.ID_USUARIO, a.USERNAME, a.PATERNO, a.MATERNO, a.NOMBRE, a.ID_GRUPO, a.STATUS, a.E_MAIL, a.E_MAIL_ALTERNO, ".
							 "   b.NOMBRE_GRUPO, b.MAX_ITEMS_PRESTADOS, b.MAX_DIAS_PRESTAMO, b.PERMITIR_PREST_CON_RETRASOS, b.PERMITIR_PREST_CON_SANCIONES, b.MAX_RENOVACIONES, b.DIAS_RENOVACION_DEFAULT, b.PERMITIR_RENOV_CON_RETRASO, b.PERMITIR_RENOV_CON_SANCIONES, " .
							 "    b.MAX_RESERVACIONES, b.PERMITIR_RESERV_CON_SANCIONES, b.NOTIFICA_EMAIL_RESERVA, b.NOTIFICA_EMAIL_PRESTAMO, b.NOTIFICA_EMAIL_RENOVA, b.NOTIFICA_EMAIL_RETRASO_DEV, b.NOTIFICA_EMAIL_DEVOLUCIONES, b.NOTIFICA_EMAIL_RESTRICCIONES, b.NOTIFICA_EMAIL_SANCIONES, " .
							 "     b.MULTA_ECONOMICA_SN, b.MULTA_HORAS_SN, b.MULTA_ESPECIE_SN, " .
							 "      c.NOMBRE_BIBLIOTECA, c.EMAIL_DIRECTOR, c.BIBLIOTEK_URL " .
							 "FROM cfgusuarios a ".
							 "  LEFT JOIN cfgusuarios_grupos b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_GRUPO=a.ID_GRUPO) " .
							 "   LEFT JOIN cfgbiblioteca c ON (c.ID_BIBLIOTECA=a.ID_BIBLIOTECA) " .
							 "WHERE a.ID_BIBLIOTECA=$this->nIDBiblioteca and (a.ID_USUARIO=$this->nIDUser" . (($id_usuario == -1) ? " or a.ADMINISTRADOR='S'" :"") . ")" );
							
			if( $row = $this->db->FetchRecord( $resultset, $translate_to_html ) )
			{			
				$this->ID_USUARIO = $row["ID_USUARIO"];
				$this->STATUS   = $row["STATUS"];
				
				$this->USERNAME = $row["USERNAME"];
				
				$this->NOMBRE	= $row["NOMBRE"];
				$this->PATERNO	= $row["PATERNO"];
				$this->MATERNO	= $row["MATERNO"];
				
				$this->LIBRARY_NAME = $row["NOMBRE_BIBLIOTECA"];
				$this->LIBRARY_BIBLIOTEK_URL = $row["BIBLIOTEK_URL"];  // URL asignada por GES para Bibliotek,
																	   // puede ser un subdominio o un dominio real dirigido a BIBLIOTEK
				
				$this->NOMBRE_COMPLETO = $row["NOMBRE"] . " " . $row["PATERNO"] . " " . $row["MATERNO"];
				
				$this->EMAIL			= $row["E_MAIL"];
				$this->EMAIL_ALTERNO	= $row["E_MAIL_ALTERNO"];

				$this->ID_GRUPO	    = $row["ID_GRUPO"];
				$this->NOMBRE_GRUPO = $row["NOMBRE_GRUPO"];

				// check outs
				$this->GRUPO_MAX_ITEMS_PRESTADOS = $row["MAX_ITEMS_PRESTADOS"];
				$this->GRUPO_MAX_DIAS_PRESTAMO   = $row["MAX_DIAS_PRESTAMO"];
				$this->GRUPO_PERMITIRPRESTAMOS_CON_RETRASOS  = $row["PERMITIR_PREST_CON_RETRASOS"];
				$this->GRUPO_PERMITIRPRESTAMOS_CON_SANCIONES = $row["PERMITIR_PREST_CON_SANCIONES"];
				//$this->GRUPO_PERMITIRPRESTAMOS_CON_RESTRICCIONES = $row["PERMITIR_PREST_CON_RESTRIC"];
				
				// renewals
				$this->GRUPO_MAX_RENOVACIONES 			   = $row["MAX_RENOVACIONES"];
				$this->GRUPO_DIAS_RENOVACION_DEFAULT       = $row["DIAS_RENOVACION_DEFAULT"];
				$this->GRUPO_PERMITIRRENOVA_CON_RETRASO    = $row["PERMITIR_RENOV_CON_RETRASO"];
				$this->GRUPO_PERMITIRRENOVA_CON_SANCIONES  = $row["PERMITIR_RENOV_CON_SANCIONES"];
				
				// reservas
				$this->GRUPO_MAX_RESERVACIONES			   = $row["MAX_RESERVACIONES"];
				//$this->GRUPO_PERMITIRRESERVA_CON_RESTRIC   = $row["PERMITIR_RESERV_CON_RESTRIC"];
				$this->GRUPO_PERMITIRRESERVA_CON_SANCIONES = $row["PERMITIR_RESERV_CON_SANCIONES"];
				
				// email notifications			
				$this->GRUPO_NOTIFICA_EMAIL_RESERVA 		= $row["NOTIFICA_EMAIL_RESERVA"];
				$this->GRUPO_NOTIFICA_EMAIL_PRESTAMO     	= $row["NOTIFICA_EMAIL_PRESTAMO"];
				$this->GRUPO_NOTIFICA_EMAIL_RENOVA 			= $row["NOTIFICA_EMAIL_RENOVA"];
				$this->GRUPO_NOTIFICA_EMAIL_RETRASO_DEV 	= $row["NOTIFICA_EMAIL_RETRASO_DEV"];
				$this->GRUPO_NOTIFICA_EMAIL_DEVOLUCIONES 	= $row["NOTIFICA_EMAIL_DEVOLUCIONES"];
				$this->GRUPO_NOTIFICA_EMAIL_RESTRICCIONES	= $row["NOTIFICA_EMAIL_RESTRICCIONES"];
				$this->GRUPO_NOTIFICA_EMAIL_SANCIONES 		= $row["NOTIFICA_EMAIL_SANCIONES"];

				$this->ADMITE_SANCION_ECONOMICA  = $row["MULTA_ECONOMICA_SN"];
				$this->ADMITE_SANCION_HORAS      = $row["MULTA_HORAS_SN"];
				$this->ADMITE_SANCION_ESPECIE    = $row["MULTA_ESPECIE_SN"];
				
				if( $id_usuario == -1 ) 
				{
					
				}
				
			}
			else
			{
				$this->NOT_FOUND = true;
			}							
		
			$this->db->ReleaseResultset( $resultset );

		}

		function ObtenerNumItemsPrestados()
		{
			if( $this->ALREADY_COUNTED_LOANS == NULL )
			{
				//
				// Buscar los préstamos que el usuario tenga
				//
				$resultset = $this->db->SubQuery( "SELECT COUNT(*) AS CUANTOS " .
												   " FROM prestamos_mst a " .
												   "	 LEFT JOIN prestamos_det b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PRESTAMO=a.ID_PRESTAMO) " .
												   "WHERE (a.ID_BIBLIOTECA=$this->nIDBiblioteca and a.ID_USUARIO=$this->nIDUser) and (b.STATUS='P');" );

				$ret = 0;

				if( ($row = $this->db->FetchRecord($resultset)) )
				{
					$ret = $row["CUANTOS"];
				}

				$this->db->ReleaseResultset( $resultset );
			
				$this->ALREADY_COUNTED_LOANS = $ret;
			}
			else
				$ret = $this->ALREADY_COUNTED_LOANS;

			return $ret;
		}

		//
		// 25nov2009:  Obtiene los items que están bloqueados para el usuario
		// 
		function ObtenerNumItemsBloqueados()
		{
			if( $this->ALREADY_COUNTED_BLOCKED_ITEMS == NULL )
			{
				//
				// Buscar los items bloqueados de un usuario y que estén dentro de la fecha actual
				//
				$resultset = $this->db->SubQuery( "SELECT COUNT(*) AS CUANTOS " .
												   " FROM acervo_copias a " .
												   "WHERE (a.ID_BIBLIOTECA=$this->nIDBiblioteca and a.BLOQUEADO_PARA_USUARIO=$this->nIDUser and a.STATUS='B');" );

				$ret = 0;

				if( ($row = $this->db->FetchRecord($resultset)) )
				{
					$ret = $row["CUANTOS"];
				}

				$this->db->ReleaseResultset( $resultset );

				$this->ALREADY_COUNTED_BLOCKED_ITEMS = $ret;
			}
			else
				$ret = $this->ALREADY_COUNTED_BLOCKED_ITEMS; 
			
			return $ret;			
		}
		
		//
		// Obtiene sanciones incumplidas
		//
		function ObtenerNumSanciones()
		{
			if( $this->ALREADY_COUNTED_SANCTIONS == NULL )
			{
				//
				// Buscar las sanciones que el usuario tenga
				//
				$resultset = $this->db->SubQuery( "SELECT COUNT(*) AS CUANTOS " .
												  " FROM sanciones a " .
												  "WHERE (a.ID_BIBLIOTECA=$this->nIDBiblioteca and a.ID_USUARIO=$this->nIDUser and a.STATUS_SANCION='N');" );

				$ret = 0;

				if( ($row = $this->db->FetchRecord($resultset)) )
				{
					$ret =  $row["CUANTOS"];
				}

				$this->db->ReleaseResultset( $resultset );

				$this->ALREADY_COUNTED_SANCTIONS = $ret;
			}
			else
				$ret = $this->ALREADY_COUNTED_SANCTIONS;

			return $ret;
		}

		//
		// Obtiene restricciones en vigor
		//
		//  Datos sobre el parametro 
		//
		//  nTipo   1 - Para impedir Consultas
		//	        2 - Para impedir Reservaciones
		//	        3 - Para impedir Préstamos
		//	        4 - Para impedir Renovaciones
		//
		function ObtenerNumRestricciones( $nTipo )
		{			
			if( $this->ALREADY_COUNTED_RESTRICTIONS == NULL )
			{
				$this->ALREADY_COUNTED_RESTRICTIONS = NULL;
								
				$sql  = "SELECT a.TIPO_RESTRICCION, b.IMPEDIR_CONSULTAS, b.IMPEDIR_RESERVACIONES, b.IMPEDIR_PRESTAMOS, b.IMPEDIR_RENOVACIONES " . 
						" FROM RESTRICCIONES a ";
				$sql .= " INNER JOIN cfgrestricciones b ON (b.ID_BIBLIOTECA = a.ID_BIBLIOTECA and b.TIPO_RESTRICCION=a.TIPO_RESTRICCION ";
				
				//if( $this->nIDBiblioteca == 1 )
				//	echo $sql;
				
				//require_once( "../funcs.inc.php" );
				$curdate = current_dbdate();

				if( $nTipo == 1 )
					$sql .= " and b.IMPEDIR_CONSULTAS = 'S' ) ";
				else if( $nTipo == 2 )
					$sql .= " and b.IMPEDIR_RESERVACIONES = 'S' ) ";
				else if( $nTipo == 3 )
					$sql .= " and b.IMPEDIR_PRESTAMOS = 'S' ) ";
				else if( $nTipo == 4 )
					$sql .= " and b.IMPEDIR_RENOVACIONES = 'S' ) ";
				else
					$sql .= " ) "; // cerrar el paréntesis

				$sql .= "WHERE a.ID_BIBLIOTECA=$this->nIDBiblioteca and a.ID_USUARIO=$this->nIDUser and ('$curdate' BETWEEN a.FECHA_INICIO and a.FECHA_FINAL) and a.STATUS_RESTRICCION='A' ";

				$resultset = $this->db->SubQuery( $sql );

				$ret = 0;

				while( $row = $this->db->FetchRecord($resultset) ) 
				{
					//$ret =  $row["CUANTOS"];
					
					if( $row["IMPEDIR_CONSULTAS"] == "S" ) $this->EXIST_RESTRICTION_CONSULTA = true;
					if( $row["IMPEDIR_RESERVACIONES"] == "S" ) $this->EXIST_RESTRICTION_RESERVA = true;
					if( $row["IMPEDIR_RENOVACIONES"] == "S" ) $this->EXIST_RESTRICTION_RENOVACION = true;
					if( $row["IMPEDIR_PRESTAMOS"] == "S" ) $this->EXIST_RESTRICTION_PRESTAMO = true;
					
					$ret++;
				}

				$this->db->ReleaseResultset( $resultset );
				
				$this->ALREADY_COUNTED_RESTRICTIONS = $ret;
			
			}
			else
				$ret = $this->ALREADY_COUNTED_RESTRICTIONS;			

			return $ret;				
		}
		
		function ObtenerNumItemsReservados()
		{		
			if( $this->ALREADY_COUNTED_RESERVAS == NULL )
			{
				//
				// Buscar las reservaciones que el usuario tenga
				//
				//
				// 10-dic-2009
				// Se quita del WHERE el b.STATUS_RESERVACION='E'  // porque ya no está pendiente
				$resultset = $this->db->SubQuery( "SELECT COUNT(*) AS CUANTOS " .
								 " FROM reservaciones_mst a " .
								 "	 LEFT JOIN reservaciones_det b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_RESERVACION=a.ID_RESERVACION) " .
								 " WHERE (a.ID_BIBLIOTECA=$this->nIDBiblioteca and a.ID_USUARIO=$this->nIDUser) and (b.STATUS_RESERVACION='P' or b.STATUS_RESERVACION='R');" );

				$ret = 0;
						   
				if( ($row = $this->db->FetchRecord($resultset)) )
				{
					$ret =  $row["CUANTOS"];
				}

				$this->db->ReleaseResultset( $resultset );

				$this->ALREADY_COUNTED_RESERVAS = $ret;
			}
			else
				$ret = $this->ALREADY_COUNTED_RESERVAS;
			
			return $ret;
		}
		
		function Send_EMAIL_Message_TO_USER( $subject, $htmlBody, $alternate_body="", $mute=1, $additional_link="" )
		{
			require_once ( "../basic/PHPMailer/class.phpmailer.php" );
			require_once ( "../email.inc.php" );
			
			global $EMAIL;
			
			if( $this->bulkMailConnection == null )
			{		
				// NO Global parameter; then create a new connection
				// it seems to be an alone email
				$mail = new PHPMailer(true);

				// enables SMTP debug information (for testing)
				$mail->SMTPDebug  = 0;             
				
				if( isset($EMAIL->smtp_debug) )
					$mail_over_smtp->SMTPDebug = 1;
				        	
				$mail->PluginDir = "../basic/PHPMailer/";								
				$mail->IsSMTP(); //$mail->Mailer = "smtp";

				$mail->SMTPAuth = true;
				$mail->Host     = $EMAIL->smtp_host;
				$mail->Username = $EMAIL->smtp_user;
				$mail->Password = $EMAIL->smtp_pass;			
				$mail->Port     = $EMAIL->smtp_port;

				$mail->Timeout=30; // 10 o 30

				$res = $this->db->SubQuery( "SELECT a.NOMBRE_BIBLIOTECA, b.DEFAULT_EMAIL_SENDERNAME, b.DEFAULT_EMAIL_RESPONDER " .
											" FROM cfgbiblioteca a " .
											"  LEFT JOIN cfgbiblioteca_config b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA) " .
											"WHERE (a.ID_BIBLIOTECA=" . $this->nIDBiblioteca . ")" );			

				// No traducir a HTML el resultado de este Open
				if( ($row = $this->db->FetchRecord($res,0)) )
				{
					$mail->From = "service@bibliotecaweb.net"; // This adddress as default, so you'd not be mark as spammer
					$mail->FromName = $row["DEFAULT_EMAIL_SENDERNAME"];
				}			
				else
				{
					$mail->From = "service@bibliotecaweb.net";
					$mail->FromName = "Biblioteca Default / Default Library";					
				}

				$this->db->FreeResultset( $res );
			}
			else
			{
				// it seems to be a masive email sending
				$mail = $this->bulkMailConnection;
			}		

			$nombre_ajustado = $this->NOMBRE_COMPLETO;

			$nombre_ajustado = str_replace( "á", "a", $nombre_ajustado );
			$nombre_ajustado = str_replace( "é", "e", $nombre_ajustado );
			$nombre_ajustado = str_replace( "í", "i", $nombre_ajustado );
			$nombre_ajustado = str_replace( "ó", "o", $nombre_ajustado );
			$nombre_ajustado = str_replace( "ú", "u", $nombre_ajustado );

			$mail->Subject = $subject;
			$mail->AddAddress( $this->EMAIL, $nombre_ajustado );

			if( $this->EMAIL_ALTERNO != "" ) 
				$mail->AddAddress( $this->EMAIL_ALTERNO, $nombre_ajustado );

			/**
			if( $this->EMAIL != "jlopez@grupoges.com.mx" )  // SOLO PARA DEPURAR
				$mail->AddAddress( "jlopez@escolarhitech.com.mx", "BiblioTEK WebMaster");  // SOLO PARA DEPURAR
			**/
			//$mail->AddCC("jlopez@escolarhitech.com.mx");  // SOLO PARA PRUEBAS y en Win32

			if( $additional_link != "" )
			{
				 $htmlBody .= "<br><br><a href='$additional_link'>Para mayor referencia haga 'clic' en este link: $additional_link </a><br>";	
			}

			$htmlBody .= "<br><HR><div style='display: inline; font-size: 9px; color: #666666;'>Disclaimer: Este e-mail es de interés solo para los individuos mencionados en el mismo. Por lo anterior, no podrá distribuirse ni difundirse bajo ninguna circunstancia. Si Usted no es alguno de los destinatarios y este correo le ha llegado por equivocación se le pide borrarlo inmediatamente.</div>";

			$htmlBody = "<body style='$EMAIL->font_default_css; background: url($EMAIL->image_background) no-repeat right top;'>$htmlBody</body>";
			
			$mail->Body = $htmlBody;
			$mail->isHTML(true);
			$mail->AltBody = $alternate_body;
			
			//$mail->AddAttachment("images/foto.jpg", "foto.jpg");
			
			$ret = 0;
			
			try 
			{
				if( $mail->Send() )
				{
					$ret = 1;
					// echo "Email enviado a ";
				}
				else
				{
					echo $mail->ErrorInfo . "<br>";
				}			
			} 
			catch (phpmailerException $e) 
			{
			    echo $e->errorMessage(); //Pretty error messages from PHPMailer
				echo "<br>";
			} 
			catch (Exception $e) 
			{
			    echo $e->getMessage(); // Boring error messages from anything else!
			}
			
			// Clear all addresses and attachments for next loop
			$mail->ClearAddresses();
			$mail->ClearAttachments();			
			
			return $ret;
		}
		
		function destroy()
		{
			if( !$this->db_not_to_destroy ) 
				$this->db->destroy();
			
			unset( $self );
		}		
	
	}

 ?>