<?php

 /***
   Estadisticas
   
   09-oct-2009: estadisticas_obtener_info_prestamos
		        estadisticas_catalogacion
	19 nov 2009: Sanciones.
	19 nov 2009: Restricciones.
   
   */

 require_once( "../basic/bd.class.php" );  
   
 function estadisticas_obtener_titulos( $dbx, $id_biblioteca )
 {
	$array = Array();
	
	$dbx->sql =	"SELECT a.ID_TIPOMATERIAL, b.DESCRIPCION, b.DESCRIPCION_ENG, b.DESCRIPCION_PORT, b.ICONO, COUNT(*) AS TITULOS " .
				"FROM acervo_titulos a " .
				"   LEFT JOIN marc_material b ON (b.ID_TIPOMATERIAL=a.ID_TIPOMATERIAL) " .
				"WHERE a.ID_BIBLIOTECA = $id_biblioteca " .
				"GROUP BY a.ID_TIPOMATERIAL, b.DESCRIPCION, b.DESCRIPCION_ENG, b.DESCRIPCION_PORT, b.ICONO	";
	$dbx->Open();
	
	$array_info_titulos = Array();
	
	while( $dbx->NextRow() ) 
	{
			$array[] = Array( "id_tipomaterial" => $dbx->row["ID_TIPOMATERIAL"], 
							  "descripcion" => get_translation( $dbx->row["DESCRIPCION"], $dbx->row["DESCRIPCION_ENG"], $dbx->row["DESCRIPCION_PORT"] ),
							  "cuantos" => $dbx->row["TITULOS"],
							  "icono" => $dbx->row["ICONO"] );
	}
	
	$dbx->Close();    
	
	return $array;
 }
 
 function estadisticas_obtener_info_copias_x_material( $dbx, $id_biblioteca, $id_tipomaterial, &$num_archivos )
 {
	global $LBL_STATUS_AVAILABLE, $LBL_STATUS_BLOCKED, $LBL_STATUS_BORROWED, $LBL_STATUS_MISSING, $LBL_STATUS_DISABLED;
	
	$dbx->Open( "SELECT b.STATUS, COUNT(*) AS COPIAS " .
			   "FROM acervo_titulos a " .
			   "  INNER JOIN acervo_copias b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_TITULO=a.ID_TITULO) " .
			   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TIPOMATERIAL='$id_tipomaterial' " .
			   "GROUP BY b.STATUS " );
	
	$num_copias = 0;
	$info_copias = Array();
	
	while( $dbx->NextRow() )
	{
		$num_copias += $dbx->row["COPIAS"];
		
		$str_status = "";
		
		if( $dbx->row["STATUS"] == "D" ) 
		   $str_status = $LBL_STATUS_AVAILABLE;
		else if( $dbx->row["STATUS"] == "B" ) 
		   $str_status = $LBL_STATUS_BLOCKED;
		else if( $dbx->row["STATUS"] == "P" ) 
		   $str_status = $LBL_STATUS_BORROWED;
		else if( $dbx->row["STATUS"] == "F" ) 
		   $str_status = $LBL_STATUS_MISSING;		   
		else if( $dbx->row["STATUS"] == "X" ) 
		   $str_status = $LBL_STATUS_DISABLED;					
		
		$info_copias[] = Array( "status" => $dbx->row["STATUS"],
								"copias" => $dbx->row["COPIAS"],
								"descrip_str" => $str_status . " " . $dbx->row["COPIAS"] 	);
	}
			   
	$dbx->Close(); 
	
	$num_archivos = 0;
	
		// OBTENER ESTADISTICA DE ARCHIVOS ANEXADOS
		$dbx->Open( "SELECT COUNT(*) AS NUM_FILES ".
				   "FROM acervo_titulos a " .
				   "INNER JOIN acervo_archivos b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_TITULO=a.ID_TITULO) " .
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TIPOMATERIAL='$id_tipomaterial'; " );

		if( $dbx->NextRow() )
		{
			$num_archivos = $dbx->row["NUM_FILES"];
		}
				   
		$dbx->Close();			
	
	return $info_copias;
	
 }

 // Info de Usuarios
 function estadisticas_obtener_info_usuarios( $dbx, $id_biblioteca, &$total_usuarios, &$empleados )
 {
	// OBTENER ESTADISTICA DE ARCHIVOS ANEXADOS
	$dbx->Open( "SELECT a.ID_GRUPO, b.NOMBRE_GRUPO, b.USUARIOS_ADMINISTRATIVOS, COUNT(*) AS NUM_USERS " .
				"FROM cfgusuarios a ".
   				"   LEFT JOIN cfgusuarios_grupos b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_GRUPO=a.ID_GRUPO) ".
				"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.STATUS='A' ".
				"GROUP BY a.ID_GRUPO, b.NOMBRE_GRUPO, b.USUARIOS_ADMINISTRATIVOS " );

	$total_usuarios = 0;
	$empleados = 0;
	
	$info_usuarios = Array();
				
	while( $dbx->NextRow() )
	{
		if( $dbx->row["USUARIOS_ADMINISTRATIVOS"] == "S" )
			$empleados = $dbx->row["NUM_USERS"];
		
		$total_usuarios += $dbx->row["NUM_USERS"];
		
		$info_usuarios[] = Array( "grupo" => $dbx->row["NOMBRE_GRUPO"],
								  "administrativos" => $dbx->row["USUARIOS_ADMINISTRATIVOS"],
								  "usuarios" => $dbx->row["NUM_USERS"] );		
	}
			   
	$dbx->Close();		 
	
	return $info_usuarios;
 }

 //
 // 09-oct-2009: Información de préstamos
 //
 function estadisticas_obtener_info_prestamos( $dbx, $id_biblioteca, $desde, $hasta, &$total_prestamos )
 {
	// OBTENER ESTADISTICA DE ARCHIVOS ANEXADOS
	$dbx->Open( "SELECT d.ID_TIPOMATERIAL, e.ICONO, e.DESCRIPCION, e.DESCRIPCION_ENG, e.DESCRIPCION_PORT, COUNT(*) AS CUANTOS " .
				"FROM prestamos_mst a " .
				"  LEFT JOIN prestamos_det b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PRESTAMO=a.ID_PRESTAMO) " .
				"     LEFT JOIN acervo_copias c ON (c.ID_BIBLIOTECA=b.ID_BIBLIOTECA and c.ID_ITEM=b.ID_ITEM) " .
				"        LEFT JOIN acervo_titulos d ON (d.ID_BIBLIOTECA=c.ID_BIBLIOTECA and d.ID_TITULO=c.ID_TITULO) " .
				"           INNER JOIN marc_material e ON (e.ID_TIPOMATERIAL=d.ID_TIPOMATERIAL) " .
				"WHERE a.ID_BIBLIOTECA=$id_biblioteca and (a.FECHA_PRESTAMO BETWEEN '$desde' and '$hasta') " .
				"GROUP BY d.ID_TIPOMATERIAL, e.ICONO, e.DESCRIPCION, e.DESCRIPCION_ENG, e.DESCRIPCION_PORT" );

	$total_prestamos = 0;

	$info_prestamos = Array();

	while( $dbx->NextRow() )
	{
		$total_prestamos += $dbx->row["CUANTOS"];

		$info_prestamos[] = Array( "id_tipomaterial" => $dbx->row["ID_TIPOMATERIAL"],
								   "descripcion" => get_translation( $dbx->row["DESCRIPCION"], $dbx->row["DESCRIPCION_ENG"], $dbx->row["DESCRIPCION_PORT"] ),
								   "icono" => $dbx->row["ICONO"],
								   "prestamos" => $dbx->row["CUANTOS"] );
	}

	$dbx->Close();		 

	return $info_prestamos;
 }
 
 //
 // 09-oct-2009: Información de préstamos
 //
 function estadisticas_obtener_info_prestamos_x_usuario( $dbx, $id_biblioteca, $desde, $hasta, &$total_prestamos )
 {
	// OBTENER ESTADISTICA DE ARCHIVOS ANEXADOS
	$dbx->Open( "SELECT d.ID_GRUPO, d.NOMBRE_GRUPO, COUNT(*) AS CUANTOS " .
				" FROM prestamos_mst a " .
				"  LEFT JOIN prestamos_det b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PRESTAMO=a.ID_PRESTAMO) " .  
				"    LEFT JOIN cfgusuarios e ON (e.ID_BIBLIOTECA=a.ID_BIBLIOTECA and e.ID_USUARIO=a.ID_USUARIO) " .
				"      LEFT JOIN cfgusuarios_grupos d ON (d.ID_BIBLIOTECA=a.ID_BIBLIOTECA and d.ID_GRUPO=e.ID_GRUPO) " .
				"WHERE a.ID_BIBLIOTECA=$id_biblioteca and (a.FECHA_PRESTAMO BETWEEN '$desde' and '$hasta') " .
				"GROUP BY d.ID_GRUPO, d.NOMBRE_GRUPO;" );

	$total_prestamos = 0;

	$info_prestamos = Array();

	while( $dbx->NextRow() )
	{
		$total_prestamos += $dbx->row["CUANTOS"];

		$info_prestamos[] = Array( "id_grupo" => $dbx->row["ID_GRUPO"],
								   "nombre_grupo" => $dbx->row["NOMBRE_GRUPO"],
								   "icono_grupo" => "",  // pendiente implementar
								   "prestamos" => $dbx->row["CUANTOS"] );
	}

	$dbx->Close();		 

	return $info_prestamos;
 }
 
 
 //
 // 13-oct-2009: Información de devoluciones
 //
 function estadisticas_obtener_info_devoluciones( $dbx, $id_biblioteca, $desde, $hasta, &$total_devs )
 {
	// OBTENER ESTADISTICA DE ARCHIVOS ANEXADOS
	$dbx->Open( "SELECT d.ID_TIPOMATERIAL, e.ICONO, e.DESCRIPCION, e.DESCRIPCION_ENG, e.DESCRIPCION_PORT, COUNT(*) AS CUANTOS " .
				"FROM prestamos_det a " .
				"  LEFT JOIN prestamos_mst b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PRESTAMO=a.ID_PRESTAMO) " .
				"     LEFT JOIN acervo_copias c ON (c.ID_BIBLIOTECA=b.ID_BIBLIOTECA and c.ID_ITEM=a.ID_ITEM) " .
				"        LEFT JOIN acervo_titulos d ON (d.ID_BIBLIOTECA=c.ID_BIBLIOTECA and d.ID_TITULO=c.ID_TITULO) " .
				"           LEFT JOIN marc_material e ON (e.ID_TIPOMATERIAL=d.ID_TIPOMATERIAL) " .
				"WHERE a.ID_BIBLIOTECA=$id_biblioteca and (a.FECHA_DEVOLUCION BETWEEN '$desde' and '$hasta') and a.STATUS='D' " .
				"GROUP BY d.ID_TIPOMATERIAL, e.ICONO, e.DESCRIPCION, e.DESCRIPCION_ENG, e.DESCRIPCION_PORT" );
				
	$total_devs = 0;

	$info_devols = Array();

	while( $dbx->NextRow() )
	{
		$total_devs += $dbx->row["CUANTOS"];

		$info_devols[] = Array( "id_tipomaterial" => $dbx->row["ID_TIPOMATERIAL"],
								"descripcion" => get_translation( $dbx->row["DESCRIPCION"], $dbx->row["DESCRIPCION_ENG"], $dbx->row["DESCRIPCION_PORT"] ),
								"icono" => $dbx->row["ICONO"],
								"devoluciones" => $dbx->row["CUANTOS"] );
	}

	$dbx->Close();		 

	return $info_devols;
 } 
 
 //
 // Títulos más consultados
 //
 function estadisticas_titulos_mas_consultados( $dbx, $id_biblioteca, $desde, $hasta, &$info_titulos, $base=10 )
 {
	// OBTENER ESTADISTICA DE ARCHIVOS ANEXADOS
	$dbx->Open( "SELECT ID_TIPOMATERIAL, ICONO, DESCRIPCION, DESCRIPCION_ENG, DESCRIPCION_PORT, CODIGO_MARC, CODIGO_MARC_ENG, CODIGO_MARC_PORT, ID_TITULO, CONSULTAS ".
			    "FROM BIBLIO_MOST_VIEWED_TITLES ( $id_biblioteca, '$desde', '$hasta', $base )  " .
				"ORDER BY ID_TIPOMATERIAL, CONSULTAS DESC " );

	$total_titulos = 0;
	
	while( $dbx->NextRow() )
	{	
		$info_titulos[] = Array(  "id_tipomaterial" =>  $dbx->row["ID_TIPOMATERIAL"],
								  "icono" =>  $dbx->row["ICONO"],
								  "description" => get_translation( $dbx->row["DESCRIPCION"], $dbx->row["DESCRIPCION_ENG"], $dbx->row["DESCRIPCION_PORT"] ),
								  "marc_code" => get_translation( $dbx->row["CODIGO_MARC"], $dbx->row["CODIGO_MARC_ENG"], $dbx->row["CODIGO_MARC_PORT"] ),
								  "id_titulo" => $dbx->row["ID_TITULO"],
								  "titulo" => "",
								  "consultas" => $dbx->row["CONSULTAS"] );	
		
		$total_titulos ++;
	}								  
			   
	$dbx->Close();
	
	return $total_titulos;	
 }
 
 //
 // Temas más consultados
 //
 function estadisticas_temas_mas_consultados( $dbx, $id_biblioteca, $desde, $hasta, &$info_temas )
 {
	// OBTENER ESTADISTICA DE ARCHIVOS ANEXADOS
	$dbx->Open( "SELECT ID_TIPOMATERIAL, ICONO, DESCRIPCION, DESCRIPCION_ENG, DESCRIPCION_PORT, CODIGO_MARC, CODIGO_MARC_ENG, CODIGO_MARC_PORT, ID_TITULO, CONSULTAS ".
			    "FROM BIBLIO_MOST_VIEWED_TITLES( 1, '$desde', '$hasta', 1  )  " .
				"ORDER BY ID_TIPOMATERIAL, CONSULTAS DESC " );

	$total_temas = 0;
	
	$info_temas = Array();
	
	require_once( "marc.php" );
	
	while( $dbx->NextRow() )
	{	
		//$item = new TItem_Basic( $id_biblioteca, $array_info_titulos[$i]["id_titulo"], 0, $db );
		$marc_record = new record_MARC21( $id_biblioteca, $dbx );
		
		$marc_record->InicializarRegistroMARC21_DesdeBD_Titulo( $dbx->row["ID_TITULO"] );
		
		$objCampo = $marc_record->BuscarCampo( "650" );
		
		if( $objCampo != NULL )
		{
			for( $i=0; $i<=count($objCampo->subcampos)-1; $i++ )
			{
				$xObjSubCampo = $objCampo->subcampos[$i];
				
				if( $xObjSubCampo != NULL )
					if( $xObjSubCampo->cIDSubCampo == '$a' )
					{
						$tema = trim($xObjSubCampo->cValor);
						
						if( $tema != "" )
						{
							$ndx = -1;
							
							for( $j=0; $j<count($info_temas); $j++ )
							{
								if( $info_temas[$j]["theme"] == $tema )
								{
									$info_temas[$j]["views"] += $dbx->row["CONSULTAS"];
									$ndx = $j;
								}
							}
							
							if( $ndx == -1 )
							{
								$info_temas[] = Array(  "id_tipomaterial" => $dbx->row["ID_TIPOMATERIAL"],
														"icon" =>  $dbx->row["ICONO"],
														"description" => get_translation( $dbx->row["DESCRIPCION"], $dbx->row["DESCRIPCION_ENG"], $dbx->row["DESCRIPCION_PORT"] ),
														"marc_code" => get_translation( $dbx->row["CODIGO_MARC"], $dbx->row["CODIGO_MARC_ENG"], $dbx->row["CODIGO_MARC_PORT"] ),
														"theme" => $tema,
														"views" => $dbx->row["CONSULTAS"] );
							}
						}
						//echo $tema . "<br>";
					}
			}
						
		}
		
		$marc_record->destroy();
		unset( $marc_record );
		
		$total_temas ++;
	}								  

	$dbx->Close();

	return $total_temas;
 } 
 
 
 //
 // Autores más consultados
 //
 function estadisticas_autores_mas_consultados( $dbx, $id_biblioteca, $desde, $hasta, &$info_autores )
 {
	// OBTENER ESTADISTICA DE ARCHIVOS ANEXADOS
	$dbx->Open( "SELECT ID_TIPOMATERIAL, ICONO, DESCRIPCION, DESCRIPCION_ENG, DESCRIPCION_PORT, CODIGO_MARC, CODIGO_MARC_ENG, CODIGO_MARC_PORT, ID_TITULO, CONSULTAS ".
			    "FROM BIBLIO_MOST_VIEWED_TITLES( 1, '$desde', '$hasta', 1  )  " .
				"ORDER BY ID_TIPOMATERIAL, CONSULTAS DESC " );

	$total_autores = 0;
	
	require_once( "marc.php" );
	
	while( $dbx->NextRow() )
	{	
		//$item = new TItem_Basic( $id_biblioteca, $array_info_titulos[$i]["id_titulo"], 0, $db );
		$marc_record = new record_MARC21( $id_biblioteca, $dbx );		
		$marc_record->InicializarRegistroMARC21_DesdeBD_Titulo( $dbx->row["ID_TITULO"] );
		
		$objCampo = $marc_record->BuscarCampo( "100" );
		
		if( $objCampo != NULL )
		{
			for( $i=0; $i<=count($objCampo->subcampos)-1; $i++ )
			{
				$xObjSubCampo = $objCampo->subcampos[$i];
				
				if( $xObjSubCampo != NULL )
					if( $xObjSubCampo->cIDSubCampo == '$a' )
					{
						$author = trim($xObjSubCampo->cValor);
						
						if( $author != "" )
						{						
							$ndx = -1;
							
							for( $j=0; $j<count($info_autores); $j++ )
							{
								if( $info_autores[$j]["author"] == $author )
								{
									$info_autores[$j]["views"] += $dbx->row["CONSULTAS"];
									$ndx = $j;
								}
							}
							
							if( $ndx == -1 )
							{
								$info_autores[] = Array(  "id_tipomaterial" => $dbx->row["ID_TIPOMATERIAL"],
														"icon" =>  $dbx->row["ICONO"],
														"description" => get_translation( $dbx->row["DESCRIPCION"], $dbx->row["DESCRIPCION_ENG"], $dbx->row["DESCRIPCION_PORT"] ),
														"marc_code" => get_translation( $dbx->row["CODIGO_MARC"], $dbx->row["CODIGO_MARC_ENG"], $dbx->row["CODIGO_MARC_PORT"] ),
														"author" => $author,
														"views" => $dbx->row["CONSULTAS"] );
							}
						}

					}
			}
						
		}
		
		$marc_record->destroy();
		unset( $marc_record );
		
		$total_autores ++;
	}								  

	$dbx->Close();

	return $total_autores;

 }

 //
 //  09oct2009
 //
 function estadisticas_catalogacion( $dbx, $id_biblioteca, $desde, $hasta, &$titulos_catalogados )
 {
 
	// OBTENER ESTADISTICA DE TITULOS CATALOGADOS
	$dbx->Open( "SELECT a.ID_TIPOMATERIAL, b.ICONO, b.DESCRIPCION, b.DESCRIPCION_ENG, b.DESCRIPCION_PORT, COUNT(*) AS CUANTOS " .
				"FROM acervo_titulos a " .
				"  LEFT JOIN marc_material b ON (b.ID_TIPOMATERIAL=a.ID_TIPOMATERIAL) " .
				"WHERE a.ID_BIBLIOTECA=$id_biblioteca and (a.FECHA_REGISTRO BETWEEN '$desde' and '$hasta') " .
				"GROUP BY a.ID_TIPOMATERIAL, b.ICONO, b.DESCRIPCION, b.DESCRIPCION_ENG, b.DESCRIPCION_PORT; " );

	$titulos_catalogados = 0;
	
	$info_catalogation = Array();

	while( $dbx->NextRow() )
	{
		$titulos_catalogados += $dbx->row["CUANTOS"];
		
		$info_catalogation[] = Array( "id_tipomaterial" => $dbx->row["ID_TIPOMATERIAL"],
								   "descripcion" => get_translation( $dbx->row["DESCRIPCION"], $dbx->row["DESCRIPCION_ENG"], $dbx->row["DESCRIPCION_PORT"] ),
								   "icono" => $dbx->row["ICONO"],
								   "titulos" => $dbx->row["CUANTOS"] );
	}
			   
	$dbx->Close();		 
	
	return $info_catalogation; 
 
 }
 
 //
 //  09oct2009
 //
 function estadisticas_informes_sanciones( $dbx, $id_biblioteca, $desde, $hasta, &$sanciones_registradas )
 {
 
	// OBTENER ESTADISTICA DE TITULOS CATALOGADOS
	$dbx->Open( "SELECT a.ID_SANCION, a.ID_USUARIO, a.TIPO_SANCION, a.FECHA_SANCION, a.FECHA_LIMITE, a.MOTIVO, a.STATUS_SANCION, a.FECHA_CUMPLIDA, a.CONDONACION, " . 
			    "  b.DESCRIPCION, c.USERNAME, c.PATERNO, c.MATERNO, c.NOMBRE, d.NOMBRE_GRUPO " .
				"FROM sanciones a " .
				"  LEFT JOIN cfgsanciones b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.TIPO_SANCION=a.TIPO_SANCION) " .
				"    LEFT JOIN cfgusuarios c ON (c.ID_BIBLIOTECA=a.ID_BIBLIOTECA and c.ID_USUARIO=a.ID_USUARIO) " . 
				"      LEFT JOIN cfgusuarios_grupos d ON (d.ID_BIBLIOTECA=a.ID_BIBLIOTECA and d.ID_GRUPO=c.ID_GRUPO) " . 
				"WHERE a.ID_BIBLIOTECA=$id_biblioteca and (a.FECHA_SANCION BETWEEN '$desde' and '$hasta') " .
				"ORDER BY a.FECHA_SANCION, b.DESCRIPCION ; " );

	$sanciones_registradas = 0;
	
	$info_sanciones = Array();

	while( $dbx->NextRow() )
	{
		$sanciones_registradas++;
		
		$cumplida = "N";
		
		if( $dbx->row["STATUS_SANCION"] == "S" )
			$cumplida = "S";
		
		$info_sanciones[] = Array( "id_sancion" => $dbx->row["ID_SANCION"],
								   "tipo_sancion" => $dbx->row["TIPO_SANCION"],
								   "descripcion" => $dbx->row["DESCRIPCION"],
								   "id_usuario" => $dbx->row["ID_USUARIO"],								   
								   "fecha_sancion" => $dbx->row["FECHA_SANCION"],
								   "fecha_limite" => $dbx->row["FECHA_LIMITE"],
								   "cumplida" => $cumplida,
								   "condonada" => $dbx->row["CONDONACION"],
								   "fecha_cumplida" => $dbx->row["FECHA_CUMPLIDA"],
								   "motivo" => $dbx->row["MOTIVO"],
								   "usuario" => $dbx->row["NOMBRE"] . " " . $dbx->row["PATERNO"],
								   "usuario_grupo" => $dbx->row["NOMBRE_GRUPO"] );
	}
			   
	$dbx->Close();		 
	
	return $info_sanciones; 
 
 }
 
 //
 //  09oct2009
 //  RESTRICCIONES
 //
 function estadisticas_informes_restricciones( $dbx, $id_biblioteca, $desde, $hasta, &$restricciones_registradas )
 {
 
	// OBTENER ESTADISTICA DE TITULOS CATALOGADOS
	$dbx->Open( "SELECT a.ID_RESTRICCION, a.ID_USUARIO, a.TIPO_RESTRICCION, a.FECHA_REGISTRO, a.FECHA_INICIO, a.FECHA_FINAL, a.MOTIVO, a.STATUS_RESTRICCION, a.FECHA_CANCELACION, " . 
			    "  b.DESCRIPCION, c.USERNAME, c.PATERNO, c.MATERNO, c.NOMBRE, d.NOMBRE_GRUPO " .
				"FROM restricciones a " .
				"  LEFT JOIN cfgrestricciones b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.TIPO_RESTRICCION=a.TIPO_RESTRICCION) " .
				"    LEFT JOIN cfgusuarios c ON (c.ID_BIBLIOTECA=a.ID_BIBLIOTECA and c.ID_USUARIO=a.ID_USUARIO) " . 
				"      LEFT JOIN cfgusuarios_grupos d ON (d.ID_BIBLIOTECA=a.ID_BIBLIOTECA and d.ID_GRUPO=c.ID_GRUPO) " . 
				"WHERE a.ID_BIBLIOTECA=$id_biblioteca and (a.FECHA_INICIO BETWEEN '$desde' and '$hasta') " .
				"ORDER BY a.FECHA_INICIO, b.DESCRIPCION ; " );

	$restricciones_registradas = 0;
	
	$info_restricciones = Array();

	while( $dbx->NextRow() )
	{
		$restricciones_registradas++;
		
		$cancelada = "N";
		
		if( $dbx->row["STATUS_RESTRICCION"] == "C" )
			$cancelada = "S";
		
		$info_restricciones[] = Array( "id_restriccion" => $dbx->row["ID_RESTRICCION"],
								   "tipo_restriccion" => $dbx->row["TIPO_RESTRICCION"],
								   "descripcion" => $dbx->row["DESCRIPCION"],
								   "id_usuario" => $dbx->row["ID_USUARIO"],								   
								   "fecha_registro" => $dbx->row["FECHA_REGISTRO"],
								   "fecha_inicio" => $dbx->row["FECHA_INICIO"],
								   "fecha_final" => $dbx->row["FECHA_FINAL"],
								   "cancelada" => $cancelada,
								   "fecha_cancelacion" => $dbx->row["FECHA_CANCELACION"],
								   "motivo" => $dbx->row["MOTIVO"],
								   "usuario" => $dbx->row["NOMBRE"] . " " . $dbx->row["PATERNO"],
								   "usuario_grupo" => $dbx->row["NOMBRE_GRUPO"] );
	}
			   
	$dbx->Close();		 
	
	return $info_restricciones; 
 } 
 
 // 19 nov 2009
 function estadisticas_obtener_info_renovaciones( $dbx, $id_biblioteca, $desde, $hasta, &$total_renovas )
 {
	// OBTENER ESTADISTICA DE ARCHIVOS ANEXADOS
	$dbx->Open( "SELECT d.ID_TIPOMATERIAL, e.ICONO, e.DESCRIPCION, e.DESCRIPCION_ENG, e.DESCRIPCION_PORT, COUNT(*) AS CUANTOS " .
				"FROM renovaciones a " .
				"     LEFT JOIN acervo_copias c ON (c.ID_BIBLIOTECA=a.ID_BIBLIOTECA and c.ID_ITEM=a.ID_ITEM) " .
				"        LEFT JOIN acervo_titulos d ON (d.ID_BIBLIOTECA=c.ID_BIBLIOTECA and d.ID_TITULO=c.ID_TITULO) " .
				"           LEFT JOIN marc_material e ON (e.ID_TIPOMATERIAL=d.ID_TIPOMATERIAL) " .
				"WHERE a.ID_BIBLIOTECA=$id_biblioteca and (a.FECHA_RENOVACION BETWEEN '$desde' and '$hasta') " .
				"GROUP BY d.ID_TIPOMATERIAL, e.ICONO, e.DESCRIPCION, e.DESCRIPCION_ENG, e.DESCRIPCION_PORT" );
				
	$total_renovas = 0;
	
	$info_renewals = Array();

	while( $dbx->NextRow() )
	{
		$total_renovas += $dbx->row["CUANTOS"];

		$info_renewals[] = Array( "id_tipomaterial" => $dbx->row["ID_TIPOMATERIAL"],
								"descripcion" => get_translation( $dbx->row["DESCRIPCION"], $dbx->row["DESCRIPCION_ENG"], $dbx->row["DESCRIPCION_PORT"] ),
								"icono" => $dbx->row["ICONO"],
								"renovaciones" => $dbx->row["CUANTOS"] );
	}

	$dbx->Close();		 

	return $info_renewals;
 } 
 
 
 // OPAC
 //
 // Temas más consultados
 // 
 // 
 //
 function estadisticas_consulta_opac( $dbx, $id_biblioteca, $desde, $hasta, &$info_terminos )
 {
	// OBTENER ESTADISTICA DE ARCHIVOS ANEXADOS
	$dbx->Open( "SELECT CAUX1, BUSQUEDA, COUNT(*) AS CUANTOS ".
				    "FROM opac_bitacora WHERE ID_BIBLIOTECA=$id_biblioteca and (FECHA BETWEEN '$desde' and '$hasta') ". 
					"GROUP BY CAUX1, BUSQUEDA" );

	$total_terminos = 0;
	
	$info_terminos = Array();
	
	require_once( "marc.php" );
	
	while( $dbx->NextRow() )
	{	
		$info_terminos[] = Array( "tipobusqueda"=>$dbx->row["CAUX1"], 
									"termino"=>$dbx->row["BUSQUEDA"], 
									"consultas"=>$dbx->row["CUANTOS"] );
		$total_terminos ++;
	}								  

	$dbx->Close();

	return $total_terminos;
 } 
 
 
?>