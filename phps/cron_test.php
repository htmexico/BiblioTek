<?php
   SOLO FUE UNA SECCION PARA SOLUCIONAR EL ASUNTO
   DE LA GENERACION AUTOMATICA DE SANCIONES
   
	$running_from_httpd = 0;
	
	$id_biblioteca = 1;
	
	$NO_EMAILS = 1;
	
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
  
    // today
	$cur_dbdate = current_dbdate();
	
	$cur_dbdate_hourzero = $cur_dbdate . " 00:00:01";
	$cur_dbdate_hour2359 = $cur_dbdate . " 23:59:59";
	
	setsessionvar("pais","MEXICO");
	
	$today_in_humanformat = getcurdate_human_format();
	$today_in_unixstyle   = decodedate( $today_in_humanformat, 0 );

	//  **************
	//  PASO 1. PRESTAMOS VENCIDOS
	//  **************
	//  
	// identificar aquellos prestamos
	// que no han sido devueltos en fecha de devolución programada
	// 

	$db->Open( "SELECT a.ID_BIBLIOTECA, a.ID_PRESTAMO, a.FECHA_DEVOLUCION_PROGRAMADA, a.ID_ITEM, b.ID_USUARIO, c.PATERNO, c.MATERNO, c.NOMBRE, c.E_MAIL, d.SANCION_X_RETRASO_DEV " . 
			   " FROM prestamos_det a " . 
		       "  INNER JOIN prestamos_mst b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PRESTAMO=a.ID_PRESTAMO) " .
			   "    INNER JOIN cfgusuarios c ON (c.ID_BIBLIOTECA=a.ID_BIBLIOTECA and c.ID_USUARIO=b.ID_USUARIO) " .  
			   "      INNER JOIN cfgusuarios_grupos d ON (d.ID_BIBLIOTECA=a.ID_BIBLIOTECA and d.ID_GRUPO=c.ID_GRUPO) " . 
			   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.FECHA_DEVOLUCION_PROGRAMADA < '$cur_dbdate_hourzero' and a.STATUS='P' " );

	echo "************" . $CR_NEWLINE;
	echo "PRESTAMOS VENCIDOS que debieron devolverse AYER o ANTES (SANCIONES)" . $CR_NEWLINE ;
	echo $CR_NEWLINE;
		
	$today_time_stamp = mktime( 0, 0, 0, $today_in_unixstyle["m"], $today_in_unixstyle["d"], $today_in_unixstyle["a"] );

	while( $db->NextRow() )
	{ 
		// calcular sanciones automáticamente
		// se asume:
		//   - al menos un día de vencimiento
		//
		$debio_devolverse = $db->row["FECHA_DEVOLUCION_PROGRAMADA"];

		$the_date = dbdate_to_human_format( $debio_devolverse, 0, $month_as_str=0 );
		$debio_devolverse_unixstyle = decodedate( $the_date, 0 );
		
		$debio_time_stamp = mktime( 0, 0, 0, $debio_devolverse_unixstyle["m"], $debio_devolverse_unixstyle["d"], $debio_devolverse_unixstyle["a"] );
		
		$diff_days = $today_time_stamp  - $debio_time_stamp;
		
		$diff_days = (int) ($diff_days / (24 * 60 * 60));
		
		echo " - " . $db->row["FECHA_DEVOLUCION_PROGRAMADA"] . "  [$diff_days retraso] ";
		echo $db->row["PATERNO"] . " " . $db->row["MATERNO"] . " " . $db->row["NOMBRE"] . " => " . $db->row["E_MAIL"];
		
		// datos en variables para manejarlos de manera rápida
		$id_usuario = $db->row["ID_USUARIO"];
		$id_prestamo = $db->row["ID_PRESTAMO"];
		$id_item     = $db->row["ID_ITEM"];
		
		$id_sancion_auto = $db->row["SANCION_X_RETRASO_DEV"];
		
		if( $id_sancion_auto != -1 )
		{
			$modalidad_sancion = "";
			$monto_sancion = 0;
			
			// Open
			$result_sancion = $db->SubQuery( "SELECT * FROM cfgsanciones WHERE ID_BIBLIOTECA=$id_biblioteca and TIPO_SANCION=$id_sancion_auto; " );
			
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

			// Close
			$db->ReleaseResultset( $result_sancion );  		
			
			if( $modalidad_sancion != "" )
			{
				
				//
				// datos que se guardarán / actualizarán en la sanción				
				//
				$fecha_limite = $cur_dbdate;
				$observaciones = "Por devolución vencida de: ";
				
				if( $modalidad_sancion == "F" )
				{
					$qty_sancion = $monto_sancion;
					$qty_total	 = $monto_sancion;
				}				
				else
				{
					// Diario
					$qty_sancion = $monto_sancion;
					$qty_total	 = $diff_days * $monto_sancion;
				}
				
				// saber el título
				$titulo = new TItem_Basic( $id_biblioteca, $id_item, 1, $db );
				
				$observaciones .= $titulo->cTitle_ShortVersion;

				$titulo->destroy();
				unset( $titulo );
				
				//
				// PRIMERO: VERIFICAR QUE EXISTA UNA SANCION PREVIA
				//
				$result_sancion_previamente_registrada = $db->SubQuery( "SELECT ID_SANCION " . 
						" FROM sanciones WHERE ID_BIBLIOTECA=$id_biblioteca and ID_USUARIO=$id_usuario and TIPO_SANCION=$id_sancion_auto " . 
						"  and ID_PRESTAMO=$id_prestamo and ID_ITEM=$id_item; "	);
			
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
						if( $row["CUANTOS"] == 0 )
							$id_new_sancion = 1;
						else
							$id_new_sancion = $row["MAX_ID"] + 1;
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
		
		echo $CR_NEWLINE;

		if( !isset($NO_EMAILS) )
			process_email( $db, $db->row["ID_BIBLIOTECA"], $db->row["ID_USUARIO"], EMAIL_DEVS_ON_DUE, $db->row["ID_PRESTAMO"], $db->row["ID_ITEM"], 0, $mail_over_smtp );

	}

	$db->Close();	
	
	echo $CR_NEWLINE;
	echo $CR_NEWLINE;
	
	echo "************" . $CR_NEWLINE;

?>