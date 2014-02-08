<?php
	/*******
	  SERVICIO DE MANTTO Y ALERTAS X HORA
	  hace una pasada cada hora
	  para recoger los eventos y
	  generar los mensajes debidos para
	  PRESTAMOS, DEVOLUCIONES, RENOVACIONES
	  
	 * 17 sep 2009: Se crea el archivo hourly_service.php.
				   
		Win Command Line

			- C:\php>php "c:/biblioteca/phps/cron_hourly_service.php"
	   
		Tareas programadas

			- C:\ARCHIV~1\MOZILL~2\firefox.exe http://localhost/biblioges/phps/cron_hourly_service.php
			
	 * 12 oct 2010: Se ajusta para notificaciones masivas de varias bibliotecas
	  
     */

    //$NO_EMAILS = 0;
	 
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
	
	
	// crear the connection
	$mail_over_smtp = create_global_bulk_smpt_connection( $db, -1, false );	
	
	$mail_over_smtp->From = "service@bibliotecaweb.net";
	
	foreach ($array_empresas as $empresa) 
	{
		$id_red = $empresa["ID_RED"];
		$id_biblioteca = $empresa["ID_BIBLIOTECA"];	
	  
		setsessionvar( "pais", $empresa["PAIS"] );
		
		// inicia hora actual - segun el pais
		$cur_db_date_and_time = current_dateandtime(1);

		$aValores =  decodedate( $cur_db_date_and_time, 1 );
		
		$timestamp_hasta = convert_humandate_to_unixstyle( $cur_db_date_and_time );
		
		// retrasar el reloj 60 mins.
		$timestamp_desde = $timestamp_hasta - (60 * 60);
		$timestamp_desde += 1;
		//$timestamp_desde -= (10*60);
		
		$dbdate_desde = encodedate_to_human_format( $timestamp_desde, 1 );
		$dbdate_hasta = encodedate_to_human_format( $timestamp_hasta, 1 );
		
		$dbdate_desde = date_for_database_updates( $dbdate_desde );
		$dbdate_hasta = date_for_database_updates( $dbdate_hasta );
		// fin hora actual - segun el pais
		
		// Inicializar Sender Name
		$mail_over_smtp->FromName = $empresa["EMAIL_SENDER_NAME"];
	
		echo "******************<br>";
		echo $empresa["NOMBRE_BIBLIOTECA"] . " " . $mail_over_smtp->FromName . "<br>";
		echo "******************<br>";
				
		$info = "";
		
		$eventos_x_informar = 0;
		
		ob_start();
		
		//  **************
		//  PASO 1. PRESTAMOS REALIZADOS
		//  ************
		//  
		
		$db->Open( "SELECT a.ID_BIBLIOTECA, a.ID_PRESTAMO, a.FECHA_PRESTAMO, a.ID_USUARIO, b.ID_ITEM, c.PATERNO, c.MATERNO, c.NOMBRE, c.E_MAIL " . 
				   " FROM prestamos_mst a " . 
				   "  INNER JOIN prestamos_det b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PRESTAMO=a.ID_PRESTAMO) " .
				   "    INNER JOIN cfgusuarios c ON (c.ID_BIBLIOTECA=a.ID_BIBLIOTECA and c.ID_USUARIO=a.ID_USUARIO) " .  
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.FECHA_PRESTAMO >= '$dbdate_desde' and a.FECHA_PRESTAMO <='$dbdate_hasta' " );
				   
		echo "************" . $CR_NEWLINE;
		echo "PRESTAMOS que se otorgaron EN EL LAPSO DE MEDIA HORA < hora $dbdate_desde - $dbdate_hasta > " . $CR_NEWLINE ;
		echo $CR_NEWLINE;
				   
		while( $db->NextRow() )
		{ 
			echo " - " . $db->row["FECHA_PRESTAMO"] . "  " ;
			echo $db->row["PATERNO"] . " " . $db->row["MATERNO"] . " " . $db->row["NOMBRE"] . " => " . $db->row["E_MAIL"] . $CR_NEWLINE;

			if( !isset($NO_EMAILS) )
				process_email( $db, $db->row["ID_BIBLIOTECA"], $db->row["ID_USUARIO"], EMAIL_LOANS_DONE, $db->row["ID_PRESTAMO"], $db->row["ID_ITEM"], 0, $mail_over_smtp );				

			$eventos_x_informar++;
		}

		if( $db->numRows == 0 )
			echo "NINGUNO " . $CR_NEWLINE;
		
		$db->Close();	
		
		
		echo $CR_NEWLINE;
		
		echo "************" . $CR_NEWLINE;
		
		//  **************
		//  PASO 2. DEVOLUCIONES REALIZADAS
		//  ************
		//  
		// identificar aquellas devoluciones
		// realizadas en la últ. media hora
		//
		// (ENVIAR EMAIL DE AGRADECIMIENTO Y SOLICITAR RESEÑA)
		// 
		$db->Open( "SELECT a.ID_BIBLIOTECA, a.ID_PRESTAMO, a.FECHA_DEVOLUCION, a.ID_ITEM, b.ID_USUARIO, c.PATERNO, c.MATERNO, c.NOMBRE, c.E_MAIL " . 
				   " FROM prestamos_det a " . 
				   "  INNER JOIN prestamos_mst b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PRESTAMO=a.ID_PRESTAMO) " .
				   "    INNER JOIN cfgusuarios c ON (c.ID_BIBLIOTECA=a.ID_BIBLIOTECA and c.ID_USUARIO=b.ID_USUARIO) " .  
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and (a.FECHA_DEVOLUCION BETWEEN '$dbdate_desde' and '$dbdate_hasta') and a.STATUS='D' " );
		//$db->DebugSQL();
		echo "DEVOLUCIONES realizadas en la última media hora < $dbdate_desde - $dbdate_hasta > " . $CR_NEWLINE ;
		echo $CR_NEWLINE;
		
		while( $db->NextRow() )
		{		
			echo " - " . $db->row["FECHA_DEVOLUCION"] . "  " ;
			echo "Send mail to him/her. " ;
			echo $db->row["PATERNO"] . " " . $db->row["MATERNO"] . " " . $db->row["NOMBRE"] . " => " . $db->row["E_MAIL"] . $CR_NEWLINE;
			
			if( !isset($NO_EMAILS) )
				process_email( $db, $db->row["ID_BIBLIOTECA"], $db->row["ID_USUARIO"], EMAIL_DEVS, $db->row["ID_PRESTAMO"], $db->row["ID_ITEM"], 0, $mail_over_smtp );
			
			$eventos_x_informar++;
		}

		if( $db->numRows == 0 )
			echo "NINGUNA " . $CR_NEWLINE ;

		$db->FreeResultset();	
		
		echo $CR_NEWLINE;
		
		
		//  **************
		//  PASO 3. RESERVACIONES REALIZADAS
		//  ************
		//  
		// identificar aquellas reservaciones
		// que fueron realizadas en la última hora
		// 
		
		$db->Open( "SELECT a.ID_BIBLIOTECA, a.ID_RESERVACION, a.FECHA_RESERVO, a.ID_USUARIO, b.ID_TITULO, b.ID_ITEM, b.TIPO_RESERVACION, b.FECHA_RESERVACION, c.PATERNO, c.MATERNO, c.NOMBRE, c.E_MAIL " . 
				   " FROM reservaciones_mst a " . 
				   "  INNER JOIN reservaciones_det b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_RESERVACION=a.ID_RESERVACION) " .
				   "    INNER JOIN cfgusuarios c ON (c.ID_BIBLIOTECA=a.ID_BIBLIOTECA and c.ID_USUARIO=a.ID_USUARIO) " .  
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.FECHA_RESERVO >= '$dbdate_desde' and a.FECHA_RESERVO <='$dbdate_hasta'; " );

		echo "************" . $CR_NEWLINE;
		echo "RESERVACIONES que se realizaron EN EL LAPSO DE MEDIA HORA < hora $dbdate_desde - $dbdate_hasta > " . $CR_NEWLINE ;
		echo $CR_NEWLINE;
				   
		while( $db->NextRow() )
		{ 
			echo " - # " . $db->row["ID_RESERVACION"]  . " " . $db->row["FECHA_RESERVACION"] . "  " ;
			echo $db->row["PATERNO"] . " " . $db->row["MATERNO"] . " " . $db->row["NOMBRE"] . " => " . $db->row["E_MAIL"] . $CR_NEWLINE;

			if( !isset($NO_EMAILS) )
			{	
				if( $db->row["TIPO_RESERVACION"] == 0 )
					$tipo_email = EMAIL_RESERVAS_ON_WAITING_LIST;
				if( $db->row["TIPO_RESERVACION"] == 1 )
					$tipo_email = EMAIL_RESERVAS_ON_SPECIFIC_DATE;				
				
				process_email( $db, $db->row["ID_BIBLIOTECA"], $db->row["ID_USUARIO"], $tipo_email, $db->row["ID_RESERVACION"], $db->row["ID_TITULO"], 0, $mail_over_smtp );
			}
				
			$eventos_x_informar++;
		}
		
		if( $db->numRows == 0 )
			echo "NINGUNA " . $CR_NEWLINE ;	

		$db->Close();	
		
		echo $CR_NEWLINE;
		
		
		//  **************
		//  PASO 4. SANCIONES ACONTECIDAS EN LA ULTIMA HORA
		//  ************
		//  
		// identificar aquellas sanciones
		// que fueron realizadas en la última hora
		// 
		
		$db->Open( "SELECT a.ID_BIBLIOTECA, a.ID_SANCION, a.ID_USUARIO, a.FECHA_SANCION, b.PATERNO, b.MATERNO, b.NOMBRE, b.E_MAIL " . 
				   " FROM sanciones a " . 
				   "   INNER JOIN cfgusuarios b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_USUARIO=a.ID_USUARIO) " . 
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.STATUS_SANCION='N' and a.FECHA_SANCION >= '$dbdate_desde' and a.FECHA_SANCION <='$dbdate_hasta'; " );

		echo "************" . $CR_NEWLINE;
		echo "SANCIONES que se realizaron EN EL LAPSO DE LA ULTIMA HORA < hora $dbdate_desde - $dbdate_hasta > " . $CR_NEWLINE ;
		echo $CR_NEWLINE;
				   
		while( $db->NextRow() )
		{ 
			echo " - " . $db->row["FECHA_SANCION"] . "  " ;
			echo $db->row["PATERNO"] . " " . $db->row["MATERNO"] . " " . $db->row["NOMBRE"] . " => " . $db->row["E_MAIL"] . $CR_NEWLINE;

			if( !isset($NO_EMAILS) )
				process_email( $db, $db->row["ID_BIBLIOTECA"], $db->row["ID_USUARIO"], EMAIL_SANCTIONS, $db->row["ID_SANCION"], 0, 0, $mail_over_smtp );
				
			$eventos_x_informar++;
		}

		if( $db->numRows == 0 )
			echo "NINGUNA " . $CR_NEWLINE ;	
		
		$db->Close();	
			
		echo $CR_NEWLINE;
		
		//  **************
		//  PASO 5. SANCIONES CUMPLIDAS EN LA ULTIMA HORA
		//  ************
		//  
		// identificar aquellas sanciones
		// que fueron cumplidas en la última hora
		// 
		$db->Open( "SELECT a.ID_BIBLIOTECA, a.ID_SANCION, a.ID_USUARIO, a.FECHA_SANCION, a.FECHA_CUMPLIDA, b.PATERNO, b.MATERNO, b.NOMBRE, b.E_MAIL " . 
				   " FROM sanciones a " . 
				   "   INNER JOIN cfgusuarios b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_USUARIO=a.ID_USUARIO) " . 
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.STATUS_SANCION='S' and a.CONDONACION='N' and a.FECHA_CUMPLIDA >= '$dbdate_desde' and a.FECHA_CUMPLIDA <='$dbdate_hasta'; " );

		echo "************" . $CR_NEWLINE;
		echo "SANCIONES que se cumplieron EN EL LAPSO DE LA ULTIMA HORA < hora $dbdate_desde - $dbdate_hasta > " . $CR_NEWLINE ;
		echo $CR_NEWLINE;
				   
		while( $db->NextRow() )
		{ 
			echo " - " . $db->row["FECHA_SANCION"] . "  " ;
			echo $db->row["PATERNO"] . " " . $db->row["MATERNO"] . " " . $db->row["NOMBRE"] . " => " . $db->row["E_MAIL"] . $CR_NEWLINE;

			if( !isset($NO_EMAILS) )
				process_email( $db, $db->row["ID_BIBLIOTECA"], $db->row["ID_USUARIO"], EMAIL_SANCTIONS_WAS_ACOMPLISHED, $db->row["ID_SANCION"], 0, 0, $mail_over_smtp );
				
			$eventos_x_informar++;
		}

		if( $db->numRows == 0 )
			echo "NINGUNA " . $CR_NEWLINE ;	
		
		$db->Close();	
			
		echo $CR_NEWLINE;	
		
		
		//  **************
		//  PASO 6. SANCIONES CONDONADAS EN LA ULTIMA HORA
		//  ************
		//  
		// identificar aquellas sanciones
		// que fueron cumplidas en la última hora
		// 
		$db->Open( "SELECT a.ID_BIBLIOTECA, a.ID_SANCION, a.ID_USUARIO, a.FECHA_SANCION, a.FECHA_CUMPLIDA, b.PATERNO, b.MATERNO, b.NOMBRE, b.E_MAIL " . 
				   " FROM sanciones a " . 
				   "   INNER JOIN cfgusuarios b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_USUARIO=a.ID_USUARIO) " . 
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.STATUS_SANCION='S' and a.CONDONACION='S' and a.FECHA_CUMPLIDA >= '$dbdate_desde' and a.FECHA_CUMPLIDA <='$dbdate_hasta'; " );

		echo "************" . $CR_NEWLINE;
		echo "SANCIONES que se condonaron EN EL LAPSO DE LA ULTIMA HORA < hora $dbdate_desde - $dbdate_hasta > " . $CR_NEWLINE ;
		echo $CR_NEWLINE;
				   
		while( $db->NextRow() )
		{ 
			echo " - " . $db->row["FECHA_SANCION"] . "  " ;
			echo $db->row["PATERNO"] . " " . $db->row["MATERNO"] . " " . $db->row["NOMBRE"] . " => " . $db->row["E_MAIL"] . $CR_NEWLINE;

			if( !isset($NO_EMAILS) )
				process_email( $db, $db->row["ID_BIBLIOTECA"], $db->row["ID_USUARIO"], EMAIL_SANCTIONS_WAS_CONDONED, $db->row["ID_SANCION"], 0, 0, $mail_over_smtp );
				
			$eventos_x_informar++;
		}

		if( $db->numRows == 0 )
			echo "NINGUNA " . $CR_NEWLINE ;	
		
		$db->Close();	
			
		echo $CR_NEWLINE;		
		
		//  **************
		//  PASO 7. RESTRICCIONES REGISTRADAS EN LA ULTIMA HORA
		//  ************
		//  
		
		$db->Open( "SELECT a.ID_BIBLIOTECA, a.ID_RESTRICCION, a.ID_USUARIO, a.FECHA_REGISTRO, b.PATERNO, b.MATERNO, b.NOMBRE, b.E_MAIL " . 
				   " FROM restricciones a " . 
				   "   INNER JOIN cfgusuarios b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_USUARIO=a.ID_USUARIO) " . 
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.STATUS_RESTRICCION='A' and (a.FECHA_REGISTRO >= '$dbdate_desde' and a.FECHA_REGISTRO <='$dbdate_hasta'); " );

		echo "************" . $CR_NEWLINE;
		echo "RESTRICCIONES que se realizaron EN EL LAPSO DE LA ULTIMA HORA < hora $dbdate_desde - $dbdate_hasta > " . $CR_NEWLINE ;
		echo $CR_NEWLINE;
				   
		while( $db->NextRow() )
		{ 
			echo " - " . $db->row["FECHA_REGISTRO"] . "  " ;
			echo $db->row["PATERNO"] . " " . $db->row["MATERNO"] . " " . $db->row["NOMBRE"] . " => " . $db->row["E_MAIL"] . $CR_NEWLINE;

			if( !isset($NO_EMAILS) )
				process_email( $db, $db->row["ID_BIBLIOTECA"], $db->row["ID_USUARIO"], EMAIL_RESTRICTION, $db->row["ID_RESTRICCION"], 0, 0, $mail_over_smtp );
				
			$eventos_x_informar++;
		}

		if( $db->numRows == 0 )
			echo "NINGUNA " . $CR_NEWLINE ;	
		
		$db->Close();	
		
		echo $CR_NEWLINE;
		echo $CR_NEWLINE;
		
		//  **************
		//  PASO 8. RESTRICCIONES CANCELADAS EN LA ULTIMA HORA
		//  ************
		//  
		
		$db->Open( "SELECT a.ID_BIBLIOTECA, a.ID_RESTRICCION, a.ID_USUARIO, a.FECHA_REGISTRO, a.FECHA_CANCELACION, b.PATERNO, b.MATERNO, b.NOMBRE, b.E_MAIL " . 
				   " FROM restricciones a " . 
				   "   INNER JOIN cfgusuarios b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_USUARIO=a.ID_USUARIO) " . 
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.STATUS_RESTRICCION='C' and a.FECHA_CANCELACION >= '$dbdate_desde' and a.FECHA_CANCELACION <='$dbdate_hasta'; " );

		echo "************" . $CR_NEWLINE;
		echo "RESTRICCIONES que se cancelaron EN EL LAPSO DE LA ULTIMA HORA < hora $dbdate_desde - $dbdate_hasta > " . $CR_NEWLINE ;
		echo $CR_NEWLINE;
				   
		while( $db->NextRow() )
		{ 
			echo " - " . $db->row["FECHA_REGISTRO"] . "  " ;
			echo $db->row["PATERNO"] . " " . $db->row["MATERNO"] . " " . $db->row["NOMBRE"] . " => " . $db->row["E_MAIL"] . $CR_NEWLINE;

			if( !isset($NO_EMAILS) )
				process_email( $db, $db->row["ID_BIBLIOTECA"], $db->row["ID_USUARIO"], EMAIL_RESTRICTION_CANCELLED, $db->row["ID_RESTRICCION"], 0, 0, $mail_over_smtp );
				
			$eventos_x_informar++;
		}

		if( $db->numRows == 0 )
			echo "NINGUNA " . $CR_NEWLINE ;	
		
		$db->Close();	
		
		echo $CR_NEWLINE;
		echo $CR_NEWLINE;
		
		//  **************
		//  PASO 9. COMENTARIOS Y CALIFICACIONES DE MATERIAL REALIZADAS EN LA ULTIMA HORA
		//  ************
		//  	
		$db->Open( "SELECT a.ID_BIBLIOTECA, a.ID_TITULO, a.ID_OPINION, a.ID_USUARIO, a.FECHA_OPINION, b.PATERNO, b.MATERNO, b.NOMBRE, b.E_MAIL " . 
				   " FROM acervo_titulos_califs a " . 
				   "   INNER JOIN cfgusuarios b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_USUARIO=a.ID_USUARIO) " . 
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.FECHA_OPINION >= '$dbdate_desde' and a.FECHA_OPINION <='$dbdate_hasta'; " );

		echo "************" . $CR_NEWLINE;
		echo "COMENTARIOS emitidos EN EL LAPSO DE LA ULTIMA HORA < hora $dbdate_desde - $dbdate_hasta > " . $CR_NEWLINE ;
		echo $CR_NEWLINE;
				   
		while( $db->NextRow() )
		{ 
			echo " - " . $db->row["ID_TITULO"] . " # " . $db->row["ID_OPINION"] . " " . $db->row["FECHA_OPINION"] . " " ;
			echo $db->row["PATERNO"] . " " . $db->row["MATERNO"] . " " . $db->row["NOMBRE"] . " => " . $db->row["E_MAIL"] . $CR_NEWLINE;

			if( !isset($NO_EMAILS) )
				process_email( $db, $db->row["ID_BIBLIOTECA"], $db->row["ID_USUARIO"], EMAIL_COMMENTS_RECEIVED, $db->row["ID_TITULO"], $db->row["ID_OPINION"], 0, $mail_over_smtp );
				
			$eventos_x_informar++;
		}

		if( $db->numRows == 0 )
			echo "NINGUNA " . $CR_NEWLINE ;	
		
		$db->Close();	
		
		echo $CR_NEWLINE;
		echo $CR_NEWLINE;
		
		//  **************
		//  PASO 10. RENOVACIONES REALIZADAS EN LA ULTIMA HORA
		//  ************
		//  	
		$db->Open( "SELECT a.ID_BIBLIOTECA, a.ID_PRESTAMO, a.ID_RENOVACION, a.ID_ITEM, a.FECHA_RENOVACION, b.ID_USUARIO, c.PATERNO, c.MATERNO, c.NOMBRE, c.E_MAIL " . 
				   " FROM renovaciones a " . 
				   "LEFT JOIN prestamos_mst b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PRESTAMO=a.ID_PRESTAMO) " . 
				   "  LEFT JOIN cfgusuarios c ON (c.ID_BIBLIOTECA=b.ID_BIBLIOTECA and c.ID_USUARIO=b.ID_USUARIO) " .
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.FECHA_RENOVACION >= '$dbdate_desde' and a.FECHA_RENOVACION <='$dbdate_hasta'; " );

		echo "************" . $CR_NEWLINE;
		echo "RENOVACIONES realizadas EN EL LAPSO DE LA ULTIMA HORA < hora $dbdate_desde - $dbdate_hasta > " . $CR_NEWLINE ;
		echo $CR_NEWLINE;
				   
		while( $db->NextRow() )
		{ 
			echo " - Item: " . $db->row["ID_ITEM"] . " # Renov. " . $db->row["ID_RENOVACION"] . " " . $db->row["FECHA_RENOVACION"] . " ";
			echo $db->row["PATERNO"] . " " . $db->row["MATERNO"] . " " . $db->row["NOMBRE"] . " => " . $db->row["E_MAIL"] . $CR_NEWLINE;

			if( !isset($NO_EMAILS) )
				process_email( $db, $db->row["ID_BIBLIOTECA"], $db->row["ID_USUARIO"], EMAIL_RENEWALS, $db->row["ID_PRESTAMO"], $db->row["ID_ITEM"], $db->row["ID_RENOVACION"], $mail_over_smtp );
				
			$eventos_x_informar++;
		}

		if( $db->numRows == 0 )
			echo "NINGUNA " . $CR_NEWLINE ;	
		
		$db->Close();	
		
		echo $CR_NEWLINE;
		
		//
		//
		// FIN DE RUTINAS DE NOTIFICACION
		//
		//
		
		$info = ob_get_contents();
		
		ob_end_flush();
		
		if( $eventos_x_informar > 0 )
		{	
			// Informar al webmaster solo cuando hubo eventos que informar
			$info = "Esta es una notificación automatica de su servicio BiblioTEK / This is an automatic notification from your service BiblioTEK <br><br>" . $info;
			$info .= "<br><hr>Por favor no responda a este mensaje. Esta dirección no admite respuestas. / Please don't reply to this message. We're not responding at this address.";
			
			process_email_admin_info( $db, $id_biblioteca, -1, $mail_over_smtp, "BiblioTEK: Auto-Notificación por hora / hourly auto-notification", $info );
		}


	}

	
	$db->destroy();
	
	// Close SMTP Connection
	$mail_over_smtp->SmtpClose();

 ?>