<?php
	/*******
	  SERVICIO DE MANTTO Y ALERTAS DIARIAS
	  hace una pasada diaria a las 01:00am.
	  para recoger los eventos y
	  generar los mensajes debidos para
	  MULTAS, SANCIONES, ALERTAS y REPORTES A ADMINISTRADORES.

	  * 18 ago 2009: Se crea el archivo daily_service.php.
		Prueba de funciones básicas

		Win Command Line

		   - C:\php>php "c:/biblioteca/phps/cron_daily_service.php"

		Tareas programadas

		   - C:\ARCHIV~1\MOZILL~2\firefox.exe http://localhost/biblioges/phps/cron_daily_service.php
		  
		* 23 nov 2009: Se modifica para enviar reservaciones disponibles para hoy.

     */

	//$NO_EMAILS = 1;
	 
	$day_of_week = date( "w" );
	
	if( $day_of_week == 0 ) die( "Domingo" );  // Ayer fue sábado
	if( $day_of_week == 1 ) die( "Lunes" ); // Ayer fue domingo
	 
	$running_from_httpd = 0;
	
	if( isset( $_SERVER["HTTP_HOST"] ) )
		$running_from_httpd = 1;
		
	if( $running_from_httpd )
		$CR_NEWLINE = "<br>";
	else
		$CR_NEWLINE = "\n";
	
	$local_base_dir = dirname( $_SERVER['SCRIPT_FILENAME'] );
	if( strpos( $local_base_dir, "/phps" ) != 0 )  $local_base_dir = substr( $local_base_dir, 0, strlen($local_base_dir)-5 );
	if( strpos( $local_base_dir, "/phps/" ) != 0 )  $local_base_dir = substr( $local_base_dir, 0, strlen($local_base_dir)-6 );

	$local_base_dir .= "/";
	
	include $local_base_dir . "funcs.inc.php";
	include $local_base_dir . "basic/bd.class.php";  
	include $local_base_dir . "/phps/circulacion.inc.php";  
	include $local_base_dir . "/phps/email_factory.inc.php";  
  
	$db = new DB();
      
	$cur_dbdate = current_dbdate(); // today
//$cur_dbdate = "28-jul-2011";  PARA DEPURAR
	
	$cur_dbdate_hourzero = $cur_dbdate . " 00:00:01";
	$cur_dbdate_hour2359 = $cur_dbdate . " 23:59:59";
	
	// potencialmente podrán existir varias redes activas
	// pero probaremos con la 1
	// 23abr2011: Se deshabilita para procesar todas en el FOR de abajo
	
	setsessionvar( "pais", "MEXICO" ); // adaptar para que cambie

	$cur_humandate = getcurdate_human_format();
//$cur_humandate = "28/07/2011";  PARA DEPURAR
	
	// calcular la fecha de ayer 
	$yesterday_date = sum_days( $cur_humandate, -1 );
	$db_yesterday_date = date_for_database_updates( $yesterday_date );
	
	$today_in_humanformat = $cur_humandate;
	$today_in_unixstyle   = decodedate( $today_in_humanformat, 0 );	

	// Crea array de empresas
	$array_empresas = Array();
	
	$db->Open( "SELECT a.ID_RED, a.ID_BIBLIOTECA, a.NOMBRE_BIBLIOTECA, a.PAIS, b.DEFAULT_EMAIL_SENDERNAME " . 
				"FROM cfgbiblioteca a " . 
				"   LEFT JOIN cfgbiblioteca_config b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA) " .
				" WHERE a.CUENTA_ACTIVA='S'; " );
	
	while( $db->NextRow(0) )
	{
		$array_empresas[] = Array( "ID_RED" => $db->row["ID_RED"],
									"ID_BIBLIOTECA" => $db->row["ID_BIBLIOTECA"],
									"NOMBRE_BIBLIOTECA" => $db->row["NOMBRE_BIBLIOTECA"],
									"PAIS" => $db->row["PAIS"],
									"EMAIL_SENDER_NAME" => $db->row["DEFAULT_EMAIL_SENDERNAME"] );
	}
	
	$db->Close();	
	
	// create the connection global for sending emails
	//$mail_over_smtp = create_global_bulk_smpt_connection( $db, $id_biblioteca, true );
	$mail_over_smtp = create_global_bulk_smpt_connection( $db, -1, true );
	
	foreach ($array_empresas as $empresa) 
	{
		$id_red = $empresa["ID_RED"];
		$id_biblioteca = $empresa["ID_BIBLIOTECA"];	

		setsessionvar( "pais", $empresa["PAIS"] );	

		$mail_over_smtp->FromName = $empresa["EMAIL_SENDER_NAME"];

		echo "******************<br>";
		echo $empresa["NOMBRE_BIBLIOTECA"] . " " . $mail_over_smtp->FromName . "<br>";
		echo "******************<br>";		

		$eventos_x_informar = 0;
		
		ob_start();

		//  **************
		//  PASO 1. PRESTAMOS VENCIDOS
		//  **************
		//  
		// identificar aquellos prestamos
		// que no han sido devueltos en fecha de devolución programada
		//
		$db->Open( "SELECT a.ID_BIBLIOTECA, a.ID_PRESTAMO, a.FECHA_DEVOLUCION_PROGRAMADA, a.RENOVACION_SN, a.ID_ITEM, b.ID_USUARIO, c.PATERNO, c.MATERNO, c.NOMBRE, c.E_MAIL, d.SANCION_X_RETRASO_DEV " . 
				   " FROM prestamos_det a " . 
				 "  INNER JOIN prestamos_mst b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PRESTAMO=a.ID_PRESTAMO) " .
				   "    INNER JOIN cfgusuarios c ON (c.ID_BIBLIOTECA=a.ID_BIBLIOTECA and c.ID_USUARIO=b.ID_USUARIO) " .  
				   "      INNER JOIN cfgusuarios_grupos d ON (d.ID_BIBLIOTECA=a.ID_BIBLIOTECA and d.ID_GRUPO=c.ID_GRUPO) " . 
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.FECHA_DEVOLUCION_PROGRAMADA < '$cur_dbdate_hourzero' and a.STATUS='P' " );

		echo "************" . $CR_NEWLINE;
		echo "PRESTAMOS VENCIDOS que debieron devolverse AYER o ANTES (SANCIONES)" . $CR_NEWLINE ;	
		
		if( $running_from_httpd == 1) echo $CR_NEWLINE;
			
		$today_time_stamp = mktime( 0, 0, 0, $today_in_unixstyle["m"], $today_in_unixstyle["d"], $today_in_unixstyle["a"] );

		while( $db->NextRow() )
		{ 
			// datos en variables para manejarlos de manera rápida
			$id_usuario = $db->row["ID_USUARIO"];
			$id_prestamo = $db->row["ID_PRESTAMO"];
			$id_item = $db->row["ID_ITEM"];
			
			$titulo = new TItem_Basic( $id_biblioteca, $id_item, 1, $db );
			
			// calcular sanciones automáticamente
			// se asume:
			//   - al menos un día de vencimiento
			//
			$vencido = true;

			$the_date = dbdate_to_human_format( $titulo->ObtenerFechaDevolucion( $id_prestamo ), 1 ); 
			$TIMESTAMP_FechaHoraDEVPROGRAMADA = convert_humandate_to_unixstyle( $the_date );

			if( $db->row["RENOVACION_SN"] == "S" )			
			{
				// si el préstamo fue renovado... entones amerita otra verificación
				$vencido = $today_time_stamp > $TIMESTAMP_FechaHoraDEVPROGRAMADA;

				/*if( $vencido ) echo "VENCIDO";
				else echo "RENOVACION HA ACTUADO";*/
			}

			if( $vencido )
			{
				$aTiempoRetraso = $titulo->ObtenerTiempoRetraso( $TIMESTAMP_FechaHoraDEVPROGRAMADA, $today_time_stamp );
				
				$diff_days = $aTiempoRetraso["tdias"];  // tdias puede llegar a ser CERO, aunque el total de horas sea menor a 24
				
				if ($aTiempoRetraso["hrs"]  > 8)   // Cuando las horas retrasadas es meyor a ocho, aumentar el número de días
				{
					$diff_days++;
				}
				
				echo " - " . $db->row["FECHA_DEVOLUCION_PROGRAMADA"] . "  [$diff_days retraso] ";
				echo $db->row["PATERNO"] . " " . $db->row["MATERNO"] . " " . $db->row["NOMBRE"] . " => " . $db->row["E_MAIL"];

				if( !isset($NO_EMAILS) )
					process_email( $db, $db->row["ID_BIBLIOTECA"], $db->row["ID_USUARIO"], EMAIL_DEVS_ON_DUE, $db->row["ID_PRESTAMO"], $db->row["ID_ITEM"], 0, $mail_over_smtp );

				$eventos_x_informar++;
				
				$id_sancion_auto = $db->row["SANCION_X_RETRASO_DEV"];
				
				if( $id_sancion_auto != -1 )
				{
					$modalidad_sancion = "";
					$monto_sancion = 0;
					
					//
					// Open
					//
					$result_sancion = $db->SubQuery( "SELECT ECONOMICA_SN, ECONOMICA_MONTO_FIJO, ECONOMICA_MONTO_X_DIA FROM cfgsanciones WHERE ID_BIBLIOTECA=$id_biblioteca and TIPO_SANCION=$id_sancion_auto; " );
					
					if( $row = $db->FetchRecord( $result_sancion ) )
					{
						if( $row["ECONOMICA_SN"] == "S" )
						{
							// solo para sanciones economicas
							if( $row["ECONOMICA_MONTO_FIJO"] > 0 )
							{
								// monto fijo (NUNCA VARIA)
								$modalidad_sancion = "F";
								echo " ! $id_sancion_auto";
								$monto_sancion = $row["ECONOMICA_MONTO_FIJO"];
							}
							else if( $row["ECONOMICA_MONTO_X_DIA"] > 0 )
							{
								// monto x dia
								$modalidad_sancion = "D";
								echo " $ $id_sancion_auto";
								$monto_sancion = $row["ECONOMICA_MONTO_X_DIA"];
							}
						}
					}

					$db->ReleaseResultset( $result_sancion );  		
					//
					// Closed
					//
					
					if( $modalidad_sancion != "" and $diff_days > 0 )
					{
						//
						// datos que se guardarán / actualizarán en la sanción
						//
						$fecha_limite  = $cur_dbdate;
						$observaciones = "Por devolución vencida de: ";
						$qty_sancion   = $monto_sancion;
						$qty_total	   = ( $modalidad_sancion == "F" ) ? $monto_sancion : ($diff_days * $monto_sancion);
						
						// $titulo = new TItem_Basic( $id_biblioteca, $id_item, 1, $db );
						// titulo versión corta
						//
						$observaciones .= $titulo->cTitle_ShortVersion;

						//
						// PRIMERO: VERIFICAR QUE EXISTA UNA SANCION PREVIA
						//
						$result_sancion_previamente_registrada = $db->SubQuery( "SELECT ID_SANCION " . 
												" FROM sanciones WHERE ID_BIBLIOTECA=$id_biblioteca and ID_USUARIO=$id_usuario " . 
												"   and TIPO_SANCION=$id_sancion_auto and ID_PRESTAMO=$id_prestamo and ID_ITEM=$id_item; "	);
						
						$existe = 0;
						$id_sancion_previa = 0;
					
						if( $row = $db->FetchRecord( $result_sancion_previamente_registrada ) )
						{
							if( $row["ID_SANCION"] > 0 )
								$existe = 1;

							$id_sancion_previa = $row["ID_SANCION"];
						}
						
						$db->ReleaseResultset( $result_sancion_previamente_registrada );
						
						//
						// PROCEDER A GENERAR LA SANCION
						//
						if( $existe == 0 )
						{
							// obtener el consecutivo
							$result = $db->SubQuery( "SELECT MAX(ID_SANCION) AS MAX_ID, COUNT(*) AS CUANTOS " . 
												     " FROM sanciones WHERE ID_BIBLIOTECA=$id_biblioteca; "	);
						
							$id_new_sancion = -1;
						
							if( $row = $db->FetchRecord( $result ) )
							{
								if( $row["CUANTOS"] == 0 ) $id_new_sancion = 1;
								else 					   $id_new_sancion = $row["MAX_ID"] + 1;
							}
							
							$db->ReleaseResultset( $result );

							//
							// La fecha Limite es el propio día en el que se registra la sanción
							//

							// INSERT it
							$db->ExecCommand( "INSERT INTO sanciones (ID_BIBLIOTECA, ID_SANCION, ID_USUARIO, TIPO_SANCION, ID_PRESTAMO, ID_ITEM, FECHA_SANCION, FECHA_CUMPLIDA, " . 
											"  FECHA_LIMITE, MOTIVO, STATUS_SANCION, MONTO_SANCION, MONTO_TOTAL, OBSERVACIONES, AUTOMATICO ) " . 
											  " VALUES ( $id_biblioteca, $id_new_sancion, $id_usuario, $id_sancion_auto, $id_prestamo, $id_item, '$cur_dbdate_hourzero', NULL, " . 
											  "  '$fecha_limite', 'SANCION GENERADA AUTOMATICAMENTE ($diff_days días de retraso)', 'N', $qty_sancion, $qty_total, '$observaciones', 'S' ); ", 0, 0 );

							// genera el email de la sanción
							if( !isset($NO_EMAILS) )
								process_email( $db, $id_biblioteca, $id_usuario, EMAIL_SANCTIONS, $id_new_sancion, 0, 0, $mail_over_smtp );

						}
						else
						{
							// UPDATE it
							$db->ExecCommand( "UPDATE sanciones SET MOTIVO='SANCION ACTUALIZADA AUTOMATICAMENTE ($diff_days días de retraso)', " . 
											  "    MONTO_SANCION=$qty_sancion, MONTO_TOTAL=$qty_total, OBSERVACIONES='$observaciones' " . 
											  " WHERE ID_BIBLIOTECA=$id_biblioteca and ID_SANCION=$id_sancion_previa; ", 0, 0 );
						}
					}
					
					echo " (sanción auto !!) ";
				}
			
			} // end - if vencido
			 
			$titulo->destroy();
			
			echo $CR_NEWLINE;
		}
		
		if( $db->numRows == 0 )
		    echo "NINGUNO " . $CR_NEWLINE ;
		else
		    echo $CR_NEWLINE;

		$db->Close();	
		
		echo $CR_NEWLINE;
		
		echo "************" . $CR_NEWLINE;
		
		//  **************
		//  PASO 2. PRESTAMOS QUE DEBERÁN DEVOLVERSE EL DIA DE HOY
		//  ************
		//  
		// identificar aquellos prestamos
		// que no han sido devueltos en fecha de devolución programada
		// 
		$db->Open( "SELECT a.ID_BIBLIOTECA, a.ID_PRESTAMO, a.ID_ITEM, b.ID_USUARIO, c.PATERNO, c.MATERNO, c.NOMBRE, c.E_MAIL " . 
				   " FROM prestamos_det a " . 
				 "  INNER JOIN prestamos_mst b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PRESTAMO=a.ID_PRESTAMO) " .
				   "    INNER JOIN cfgusuarios c ON (c.ID_BIBLIOTECA=a.ID_BIBLIOTECA and c.ID_USUARIO=b.ID_USUARIO) " .  
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and (a.FECHA_DEVOLUCION_PROGRAMADA BETWEEN '$cur_dbdate_hourzero' and '$cur_dbdate_hour2359') and a.STATUS='P' " );
		
		echo "PRESTAMOS para devolverse HOY $cur_dbdate" . $CR_NEWLINE ;
		if( $running_from_httpd == 1) echo $CR_NEWLINE;
		
		while( $db->NextRow() )
		{ 
			echo " - " . $db->row["ID_PRESTAMO"] . " " ;
			echo $db->row["PATERNO"] . " " . $db->row["MATERNO"] . " " . $db->row["NOMBRE"] . " => " . $db->row["E_MAIL"] . $CR_NEWLINE;
			
			$eventos_x_informar++;
			
			if( !isset($NO_EMAILS) )
				process_email( $db, $db->row["ID_BIBLIOTECA"], $db->row["ID_USUARIO"], EMAIL_DEVS_FOR_TODAY, $db->row["ID_PRESTAMO"], $db->row["ID_ITEM"], 0, $mail_over_smtp );
		}

		if( $db->numRows == 0 )
		    echo "NINGUNO " . $CR_NEWLINE ;
		else
		    echo $CR_NEWLINE;

		$db->FreeResultset();	
		
		echo $CR_NEWLINE;
		
		//  **************
		//  PASO 3. SANCIONES CUYO VENCIMIENTO HA PASADO
		//
		echo "************" . $CR_NEWLINE;
		echo "3. SANCIONES CUYO VENCIMIENTO HA PASADO SIN CUMPLIRSE" . $CR_NEWLINE ;
		if( $running_from_httpd == 1) echo $CR_NEWLINE;
		
		$db->Open( "SELECT a.ID_BIBLIOTECA, a.ID_SANCION, a.ID_USUARIO, b.PATERNO, b.MATERNO, b.NOMBRE, b.E_MAIL 	" . 
				   " FROM sanciones a " . 
				   "    INNER JOIN cfgusuarios b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_USUARIO=a.ID_USUARIO) " . 
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.STATUS_SANCION='N' and a.FECHA_LIMITE<'$cur_dbdate_hourzero' " );

		while( $db->NextRow() )
		{
			echo " - " . $db->row["ID_SANCION"] . " " ;
			echo $db->row["PATERNO"] . " " . $db->row["MATERNO"] . " " . $db->row["NOMBRE"] . " => " . $db->row["E_MAIL"] . $CR_NEWLINE;
			
			$eventos_x_informar++;
			
			if( !isset($NO_EMAILS) )
				process_email( $db, $db->row["ID_BIBLIOTECA"], $db->row["ID_USUARIO"], EMAIL_SANCTIONS_ON_DUE, $db->row["ID_SANCION"], 0, 0, $mail_over_smtp );
		}
		
		if( $db->numRows == 0 )
		    echo "NINGUNA " . $CR_NEWLINE ;	
		else
		    echo $CR_NEWLINE;		   
		    
		$db->Close();
		
		echo $CR_NEWLINE;
		
		//  **************
		//  PASO 4. RESTRICCIONES CUYO VIGENCIA VENCIÓ AYER
		//
		echo "************" . $CR_NEWLINE;
		echo "4. RESTRICCIONES CUYO VIGENCIA HA PASADO " . $CR_NEWLINE ;
		if( $running_from_httpd == 1 ) echo $CR_NEWLINE;
		
		$db->Open( "SELECT a.ID_BIBLIOTECA, a.ID_RESTRICCION, a.ID_USUARIO, b.PATERNO, b.MATERNO, b.NOMBRE, b.E_MAIL 	" . 
				   " FROM restricciones a " . 
				   "    INNER JOIN cfgusuarios b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_USUARIO=a.ID_USUARIO) " .  			   
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.STATUS_RESTRICCION='A' and a.FECHA_FINAL='$db_yesterday_date'; " ); 
				   // "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.STATUS_RESTRICCION='A' and a.FECHA_FINAL<'$cur_dbdate_hourzero';" );
				   
		while( $db->NextRow() )
		{
			echo " - " . $db->row["ID_RESTRICCION"] . " " ;
			echo $db->row["PATERNO"] . " " . $db->row["MATERNO"] . " " . $db->row["NOMBRE"] . " => " . $db->row["E_MAIL"] . $CR_NEWLINE;
			
			$eventos_x_informar++;
			
			if( !isset($NO_EMAILS) )
				process_email( $db, $db->row["ID_BIBLIOTECA"], $db->row["ID_USUARIO"], EMAIL_RESTRICTION_EXPIRED, $db->row["ID_RESTRICCION"], 0, 0, $mail_over_smtp );
		}
				   
		if( $db->numRows == 0 )
		    echo "NINGUNA " . $CR_NEWLINE ;
				   
		$db->Close();

		echo $CR_NEWLINE;
		
		//  **************
		//  PASO 5. RESERVACIONES ANTES BLOQUEADAS CUYO FECHA DE BLOQUEO EXPIRO
		//
		echo "************" . $CR_NEWLINE;
		echo "5. RESERVACIONES ANTES BLOQUEADAS Y QUE NO FUERON UTILIZADAS " . $CR_NEWLINE;
		if( $running_from_httpd == 1 ) echo $CR_NEWLINE;
		
		$db->Open( "SELECT a.ID_BIBLIOTECA, a.ID_TITULO, a.ID_ITEM, a.BLOQUEADO_PARA_USUARIO, c.PATERNO, c.MATERNO, c.NOMBRE, c.E_MAIL " . 
				   " FROM acervo_copias a " . 
				   "   INNER JOIN cfgusuarios c ON (c.ID_BIBLIOTECA=a.ID_BIBLIOTECA and c.ID_USUARIO=a.BLOQUEADO_PARA_USUARIO) " . 
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.STATUS='B' and a.BLOQUEADO_HASTA<='$db_yesterday_date'; " );
				   
			//	   $db->DebugSQL();

		while( $db->NextRow() )
		{
			echo " - " . $db->row["ID_TITULO"] . " " ;
			echo $db->row["PATERNO"] . " " . $db->row["MATERNO"] . " " . $db->row["NOMBRE"] . " => " . $db->row["E_MAIL"] . $CR_NEWLINE;

			$id_usuario = $db->row["BLOQUEADO_PARA_USUARIO"];
			$id_titulo  = $db->row["ID_TITULO"];
			$id_item    = $db->row["ID_ITEM"];		

			$db->ExecCommand( "UPDATE acervo_copias SET STATUS='D', BLOQUEADO_PARA_USUARIO=NULL, BLOQUEADO_DESDE=NULL, BLOQUEADO_HASTA=NULL " .
							  "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo and ID_ITEM=$id_item; ", 0, 0 );
							  
			// Colocar la reservación como Expirada
			$db->ExecCommand( "UPDATE reservaciones_det SET STATUS_RESERVACION='E' " .
							  "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_USUARIO=id_usuario and ID_TITULO=$id_titulo and ID_ITEM=$id_item and STATUS_RESERVACION='R';", 0, 0 );	
			
			$eventos_x_informar++;
			
			if( !isset($NO_EMAILS) )
				process_email( $db, $db->row["ID_BIBLIOTECA"], $id_usuario, EMAIL_RESERVAS_EXPIRED, $db->row["ID_TITULO"], $db->row["ID_ITEM"], 0, $mail_over_smtp );
		}
		
		if( $db->numRows == 0 )
		    echo "NINGUNA " . $CR_NEWLINE ;			
		else
			echo $CR_NEWLINE;
		
		$db->Close();

		echo $CR_NEWLINE;
		
		//  **************
		//  PASO 6. RESERVACIONES CUYO FECHA ESPECÍFICA FUE ELEGIDA HOY
		//
		echo "************" . $CR_NEWLINE;
		echo "6. RESERVACIONES DE ITEM'S ESPECIFICOS RESERVADAS PARA HOY " . $CR_NEWLINE;
		if( $running_from_httpd == 1 ) echo $CR_NEWLINE;
		
		$db->Open( "SELECT a.ID_BIBLIOTECA, a.ID_RESERVACION, a.ID_TITULO, a.ID_ITEM, b.ID_USUARIO, c.PATERNO, c.MATERNO, c.NOMBRE, c.E_MAIL " . 
				   " FROM reservaciones_det a " . 
				   "   INNER JOIN reservaciones_mst b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_RESERVACION=a.ID_RESERVACION) " . 
				   "    INNER JOIN cfgusuarios c ON (c.ID_BIBLIOTECA=b.ID_BIBLIOTECA and c.ID_USUARIO=b.ID_USUARIO) " . 
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_ITEM<>0 and a.STATUS_RESERVACION='P' and a.TIPO_RESERVACION=1 and a.FECHA_RESERVACION='$cur_dbdate';" ); 

		while( $db->NextRow() )
		{
			echo " - " . $db->row["ID_RESERVACION"] . " " ;
			echo $db->row["PATERNO"] . " " . $db->row["MATERNO"] . " " . $db->row["NOMBRE"] . " => " . $db->row["E_MAIL"] . $CR_NEWLINE;

			$id_titulo = $db->row["ID_TITULO"];
			$id_item_original = $db->row["ID_ITEM"];
			$id_usuario = $db->row["ID_USUARIO"];
			
			$titulo = new TItem_Basic( $id_biblioteca, $db->row["ID_ITEM"], 1, $db );

			$fechas_disponibles = Array();

			// VERIFICAR ITEM DISPONIBLE
			$te = $titulo->VerificarDisponibilidad_X_ITEM( $db->row["ID_ITEM"], "N", 
								$today_in_humanformat, $today_in_humanformat, $fechas_disponibles, 5, 1, $id_usuario );
								
			unset( $fechas_disponibles );

			$titulo->destroy();
			unset( $titulo );

			// el item original está EFECTIVAMENTE disponible
			if( $te ) 
			{
				//
				// Bloquear material STATUS = 'B'
				//
				$block_date = date_for_database_updates( $today_in_humanformat );
				//$block_date = sum_days( $today_in_humanformat, 1 ); // 2 dias

				$db->ExecCommand( "UPDATE acervo_copias SET STATUS='B', BLOQUEADO_PARA_USUARIO=$id_usuario, BLOQUEADO_DESDE='" . date_for_database_updates( $today_in_humanformat ) . "', BLOQUEADO_HASTA='$block_date' " .
								  "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo and ID_ITEM=$id_item_original; ", 0, 0 );
								  
				$db->ExecCommand( "UPDATE reservaciones_det SET STATUS_RESERVACION='R' " .
								  "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_RESERVACION=" . $db->row["ID_RESERVACION"] . " and ID_TITULO=$id_titulo and ID_ITEM=" . $db->row["ID_ITEM"] . "; ", 0, 0 );
				
				$eventos_x_informar++;
				
				if( !isset($NO_EMAILS) )
					process_email( $db, $db->row["ID_BIBLIOTECA"], $id_usuario, EMAIL_RESERVAS_AVAILABLE_FOR_TODAY, $db->row["ID_RESERVACION"], $id_titulo, $id_item_original, $mail_over_smtp );
			}
			else
			{
				//
				// NO DISPONIBLE
				// Buscar en otros del mismo material
				// 

				// Open
				$otros_titulos = $db->SubQuery( "SELECT ID_ITEM FROM acervo_copias WHERE ID_BIBLIOTECA=$ib_biblioteca and ID_TITULO=$id_titulo and STATUS<>'X' and ID_ITEM<>$id_item_original; " ); 
				
				$array_items = Array();
				
				while( $row = $db->FetchRecord( $otros_titulos ) )
				{
					$array_items[] = $row["ID_ITEM"];
				}
				
				// Close
				$db->ReleaseResultset( $otros_titulos );  
				
				$no_disponibles = true;
				
				$id_item_alterno = 0;
				
				//
				// verificar todos los items en el array
				//
				for( $i=0; $i<count($array_items) and $no_disponibles==true; $i++ )
				{
					$fechas_disponibles = Array();
					
					// x ID_ITEM
					$titulo = new TItem_Basic( $id_biblioteca, $array_items[$i], 1, $db );

					// VERIFICAR ITEM DISPONIBLE
					$te = $titulo->VerificarDisponibilidad_X_ITEM( $array_items[$i], "N",
										$today_in_humanformat, $today_in_humanformat, $fechas_disponibles, 5, 1, $id_usuario );
					
					if( $te )
					{
						$no_disponibles = false;
						$id_item_alterno = $array_items[$i];
					}
					
					unset( $fechas_disponibles );
					
					$titulo->destroy();
					unset( $titulo );
				}

				if( $no_disponibles ) 
				{
					$eventos_x_informar++;
					
					if( !isset($NO_EMAILS) )
						process_email( $db, $db->row["ID_BIBLIOTECA"], $id_usuario, EMAIL_RESERVAS_IN_PROBLEM_FOR_TODAY, $db->row["ID_RESERVACION"], $id_titulo, $id_item_original, $mail_over_smtp );
				}
				else
				{
					//
					// SE ENCONTRO UNO DISPONIBLE (Y).
					// W O W !!!
					//
					
					//
					// PENDIENTE: Colocar en usuariosgrupos, un campo para el número de días que se deberá bloquear una copia
					// 
					$block_date = date_for_database_updates( $today_in_humanformat );				
					//$block_date = sum_days( $today_in_humanformat, 1 ); // 2 dias
					
					//
					// Bloquear material STATUS = 'B'
					//
					$db->ExecCommand( "UPDATE acervo_copias SET STATUS='B', BLOQUEADO_PARA_USUARIO=$id_usuario, BLOQUEADO_DESDE='" . date_for_database_updates( $today_in_humanformat ) . "', BLOQUEADO_HASTA='$block_date' " .
									  "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo and ID_ITEM=$id_item_alterno; ", 0, 0 );
					
					$eventos_x_informar++;
					
					if( !isset($NO_EMAILS) )
						process_email( $db, $db->row["ID_BIBLIOTECA"], $id_usuario, EMAIL_RESERVAS_PROBLEM_RESOLVED, $db->row["ID_RESERVACION"], $id_titulo, $id_item_alterno, $mail_over_smtp );
					
				}
					
				unset( $array_items );
			}
		}

		if( $db->numRows == 0 )
		    echo "NINGUNA " . $CR_NEWLINE ;
		else
			echo $CR_NEWLINE;
			
		$db->Close();
		
		echo $CR_NEWLINE;

		//  **************
		//  PASO 7. RESERVACIONES EN LISTA DE ESPERA
		//
		echo "************" . $CR_NEWLINE;
		echo "7. RESERVACIONES DE ITEM'S EN LISTA DE ESPERA " . $CR_NEWLINE;
		if( $running_from_httpd ==  1 ) echo $CR_NEWLINE;
		
		echo "NINGUNA " . $CR_NEWLINE ;			   
		echo $CR_NEWLINE;
		
	//
	// PDTE 
	//
		
		//
		//  FIN
		//		
		$info = ob_get_contents();
		
		ob_end_flush();
		
		if( $eventos_x_informar > 0 )
		{		
			//
			// Informar al webmaster
			//
			$subj = "BiblioTEK daily auto-notification";
			$info = "This is your automatic daily notification from your service BiblioTEK <br><br>" . $info;
			$info .= "<br>Please don't reply to this message. We're not responding at this address.";

			if( getsessionvar( "pais" ) != "USA" and getsessionvar( "pais" ) != "CANADA" ) 
			{
				$subj = "BiblioTEK: Notificación Automática diaria";
				$info = "Este es su notificaci&oacute;n autom&aacute;tica diaria de su servicio BiblioTEK <br><br>" . $info;
				$info .= "<br>Por favor no responda a este mensaje. No es una direcci&oacute;n que admite respuestas.";			
			}
			
			if( !isset($NO_EMAILS) )
				process_email_admin_info( $db, $id_biblioteca, -1, $mail_over_smtp, $subj, $info );

		}
		
	}
	// for global de iteración entre bibliotecas
	
	$db->destroy();	
	
	// Close SMTP Connection
	$mail_over_smtp->SmtpClose();

 ?>