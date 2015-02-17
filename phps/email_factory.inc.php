<?php

  /****
    EMAIL Factory
	
	Desde este archivo se espera generar toda la mensajería x evento
	
	Creado:  03-sep-2009
	
		09-oct-2009: process_email, se reutiliza un objeto TUser
		21-oct-2009: Se hace email de sanciones
		23-oct-2009: Se hace email de prestamos y de reservaciones
		23-nov-2009: Se adapta envío de email para reservaciones disponibles para hoy.
		24-nov-2009: Se agrega parámetro auxiliar $id_data_aux3 en process_email().
		21-nov-2010: Se agrega notificación EMAIL_ORG_INFO_UPDATED
	
   */
   
   define( "EMAIL_ORG_INFO_UPDATED", 1  );   
   
   define( "EMAIL_LOANS_DONE", 10  );   
   
   define( "EMAIL_DEVS", 13 );
   define( "EMAIL_DEVS_FOR_TODAY", 14  );
   define( "EMAIL_DEVS_ON_DUE", 15  );   
   
   define( "EMAIL_RENEWALS", 17 );

   define( "EMAIL_RESERVAS_ON_WAITING_LIST", 20 );
   define( "EMAIL_RESERVAS_ON_SPECIFIC_DATE", 21 );
   define( "EMAIL_RESERVAS_CANCELED", 22 );
   define( "EMAIL_RESERVAS_AVAILABLE_FOR_TODAY", 25 );
   define( "EMAIL_RESERVAS_IN_PROBLEM_FOR_TODAY", 26 );
   define( "EMAIL_RESERVAS_PROBLEM_SOLVED", 27 );
   define( "EMAIL_RESERVAS_EXPIRED", 29 );
   
   define( "EMAIL_SANCTIONS", 30 );   
   define( "EMAIL_SANCTIONS_ON_DUE", 32 );
   define( "EMAIL_SANCTIONS_WAS_ACOMPLISHED", 34 );   
   define( "EMAIL_SANCTIONS_WAS_CONDONED", 35 );   
   
   define( "EMAIL_RESTRICTION", 40 );   
   define( "EMAIL_RESTRICTION_CANCELLED", 41 );   
   define( "EMAIL_RESTRICTION_EXPIRED", 42 );   
   
   define( "EMAIL_COMMENTS_RECEIVED", 52 );
   
   define( "EMAIL_USER_CREATION", 80 );
   define( "EMAIL_USER_CONFIRMATION", 81 );
   define( "EMAIL_USER_PASSWORD_RESETED", 82 );
   define( "EMAIL_USER_PASSWORD_CHANGED", 83 );
    
   function create_global_bulk_smpt_connection( $dbx, $id_biblioteca, $init_default_sender=false )
   {
		global $EMAIL;
		
		require_once ( "../basic/PHPMailer/class.phpmailer.php" );
		require_once ( "../email.inc.php" );		

		$mail_over_smtp = new PHPMailer();

		// enables SMTP debug information (for testing)
		$mail_over_smtp->SMTPDebug = 0;
		
		if( isset($EMAIL->smtp_debug) )
			$mail_over_smtp->SMTPDebug = 1;
		
		$mail_over_smtp->PluginDir = "../basic/PHPMailer/";
		$mail_over_smtp->IsSMTP(); //$mail_over_smtp->Mailer = "smtp";

		$mail_over_smtp->SMTPAuth = true;
		$mail_over_smtp->SMTPKeepAlive = true;
		
		$mail_over_smtp->Host     = $EMAIL->smtp_host;
		$mail_over_smtp->Port     = $EMAIL->smtp_port;
		$mail_over_smtp->Username = $EMAIL->smtp_user;
		$mail_over_smtp->Password = $EMAIL->smtp_pass;			

		$mail_over_smtp->Timeout=30; // 10 o 30	
		
		if( $init_default_sender )
		{
			$sql = "SELECT a.NOMBRE_BIBLIOTECA, b.DEFAULT_EMAIL_SENDERNAME, b.DEFAULT_EMAIL_RESPONDER " .
					 " FROM cfgbiblioteca a " .
					 "   LEFT JOIN cfgbiblioteca_config b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA) " .
					 "WHERE (a.ID_BIBLIOTECA=$id_biblioteca)";
			
			$result = $dbx->SubQuery( $sql );
			
			if( $row = $dbx->FetchRecord( $result, 0 ) )
			{
				$mail_over_smtp->From 	  = "service@bibliotecaweb.net"; // This adddress as default, so you'd not be mark as spammer
				$mail_over_smtp->FromName = $row["DEFAULT_EMAIL_SENDERNAME"];
			}
			else
			{
				$mail_over_smtp->From = "service@bibliotecaweb.net";
				$mail_over_smtp->FromName = "Biblioteca Default / Default Library";
			}	
			
			$dbx->ReleaseResultset( $result );
		}

		return $mail_over_smtp;
   }

    //
	// esta función crea un obj TUser sino se reutiliza alguno con $user_already_created
	//
	//	24nov2009: se agrega parámetro auxiliar $id_data_aux3
	//
	//
	function process_email( $dbx, $id_biblioteca, $id_usuario, $type, $id_data_aux1=0, $id_data_aux2=0, $id_data_aux3=0, $global_smtp_connection=null, $user_already_created = NULL )
	{
		require_once( "circulacion.inc.php" );
		require_once( "../funcs.inc.php" );
		
		$subject = "";
		
		if( $user_already_created != NULL )
			$user = $user_already_created;
		else
			$user = new TUser( $id_biblioteca, $id_usuario, $dbx );

		$ret = 0;

		if( $user->EMAIL != "" )
		{
			$subject = "";
			$body = "";
			
			$sql = "SELECT a.ID_CATEGORIA, a.CUSTOM_SUBJECT, a.CUSTOM_BODY_CONTENT " .
				   "FROM email_config a ".
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_CATEGORIA=$type ";
			
			$result = $dbx->SubQuery( $sql );

			if( $row = $dbx->FetchRecord( $result, 0 ) )
			{
				$subject	= $row["CUSTOM_SUBJECT"];
				$body		= $dbx->GetBLOB( $row["CUSTOM_BODY_CONTENT"] );
			}
			
			$dbx->ReleaseResultset( $result );			
			
			//
			// Subject and body content
			//

			if( $type == EMAIL_ORG_INFO_UPDATED )
			{
				$body = transform_text( $body, 
					Array( Array( "<%USER_NAME%>", $user->USERNAME ),
						    Array( "<%USER_FULLNAME%>", $user->NOMBRE_COMPLETO ), 
						    Array( "<%LIBRARY_NAME%>", $user->LIBRARY_NAME ), 
						    Array( "<%LIBRARY_BIBLIOTEK_URL%>", "'".$user->LIBRARY_BIBLIOTEK_URL."'" ) ) );						
			}
			else if( $type == EMAIL_LOANS_DONE )
			{
				// Préstamos Realizados
				if( $user->GRUPO_NOTIFICA_EMAIL_PRESTAMO == "S" ) 
				{
					// id_data_aux1 = id_prestamo
					$sql = "SELECT b.FECHA_PRESTAMO, a.FECHA_DEVOLUCION_PROGRAMADA ";
					$sql .= " FROM prestamos_det a LEFT JOIN prestamos_mst b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PRESTAMO=a.ID_PRESTAMO)  " . 
							"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_PRESTAMO=$id_data_aux1 and a.ID_ITEM=$id_data_aux2 ";

					$result = $dbx->SubQuery( $sql );

					$fecha_prestamo    = "";
					$fecha_devolucion  = "";

					if( $row = $dbx->FetchRecord( $result ) )
					{
						$fecha_prestamo	 	= dbdate_to_human_format( $row["FECHA_PRESTAMO"], 1, 1, 0 );
						$fecha_devolucion	= dbdate_to_human_format( $row["FECHA_DEVOLUCION_PROGRAMADA"], 1, 1, 0 );
					} 
					
					$dbx->ReleaseResultset( $result );

					// id_data_aux2 = id_item
					$titulo = new TItem_Basic( $id_biblioteca, $id_data_aux2, 1, $dbx );

					$body = transform_text( $body, 
									Array( Array( "<%USER_NAME%>", $user->NOMBRE_COMPLETO ), 
										   Array( "<%LIBRARY_NAME%>", $titulo->cNombreBiblioteca ), 
										   Array( "<%CHECKOUT_DATE%>", $fecha_prestamo ),
										   Array( "<%DEVOLUTION_DATE%>", $fecha_devolucion ),
										   Array( "<%ID_ITEM%>", $titulo->item_id_material ),
										   Array( "<%TITLE%>", $titulo->cTitle ),
										   Array( "<%AUTHOR%>", $titulo->cAutor) ) );

					$titulo->destroy();
				}
			}
			else if( $type == EMAIL_DEVS )
			{
				if( $user->GRUPO_NOTIFICA_EMAIL_DEVOLUCIONES == "S" ) 
				{
					// id_data_aux1 = id_prestamo
					// id_data_aux2 = id_item prestado
					$sql = "SELECT b.FECHA_PRESTAMO, a.FECHA_DEVOLUCION, a.FECHA_DEVOLUCION_PROGRAMADA ";
					$sql .= " FROM prestamos_det a LEFT JOIN prestamos_mst b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PRESTAMO=a.ID_PRESTAMO)  " . 
							"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_PRESTAMO=$id_data_aux1 and a.ID_ITEM=$id_data_aux2 ";

					$result = $dbx->SubQuery( $sql );

					$fecha_prestamo    = "";
					$fecha_devolucion_prog  = "";
					$fecha_y_hora_dev = "";

					if( $row = $dbx->FetchRecord( $result ) )
					{
						$fecha_prestamo	 	= dbdate_to_human_format( $row["FECHA_PRESTAMO"], 1, 1, 0 );
						$fecha_devolucion_prog	= dbdate_to_human_format( $row["FECHA_DEVOLUCION_PROGRAMADA"], 1, 1, 0 );
						$fecha_y_hora_dev = dbdate_to_human_format( $row["FECHA_DEVOLUCION"], 1, 1, 0 );
					} 
					
					$dbx->ReleaseResultset( $result );

					// id_data_aux2 = id_item
					$titulo = new TItem_Basic( $id_biblioteca, $id_data_aux2, 1, $dbx );
					
					$fecha_devolucion_prog = $titulo->ObtenerFechaDevolucion( $id_data_aux1 );

					$body = transform_text( $body, 
									Array( Array( "<%USER_NAME%>", $user->NOMBRE_COMPLETO ), 
										   Array( "<%LIBRARY_NAME%>", $titulo->cNombreBiblioteca ), 
										   Array( "<%CHECKOUT_DATE%>", $fecha_prestamo ),
										   Array( "<%DEVOLUTION_DATE%>", $fecha_devolucion_prog ),
										   Array( "<%DEVOLUTION_CONFIRMED_TIME%>", $fecha_y_hora_dev ),
										   Array( "<%ID_ITEM%>", $titulo->item_id_material ),
										   Array( "<%TITLE%>", $titulo->cTitle ),
										   Array( "<%AUTHOR%>", $titulo->cAutor) ) );

					$titulo->destroy();
				}			
			}
			else if( $type == EMAIL_DEVS_FOR_TODAY )
			{
				//
				// Prestamos que deberán ser devueltos HOY
				//
				if( $user->GRUPO_NOTIFICA_EMAIL_RETRASO_DEV == "S" )
				{
					$sql = "SELECT b.FECHA_PRESTAMO, a.FECHA_DEVOLUCION_PROGRAMADA ";
					$sql .= " FROM prestamos_det a LEFT JOIN prestamos_mst b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PRESTAMO=a.ID_PRESTAMO)  " . 
							"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_PRESTAMO=$id_data_aux1 and a.ID_ITEM=$id_data_aux2 ";
					
					$result = $dbx->SubQuery( $sql );
					
					$fecha_prestamo    = "";
					$fecha_devolucion  = "";
					$hora_devolucion   = "";
					
					if( $row = $dbx->FetchRecord( $result ) )
					{
						$fecha_prestamo	 	= dbdate_to_human_format( $row["FECHA_PRESTAMO"], 0, 1, 0 );
						$fecha_devolucion	= dbdate_to_human_format( $row["FECHA_DEVOLUCION_PROGRAMADA"], 0, 1, 0 );						
					} 
					
					$dbx->ReleaseResultset( $result );

					// id_data_aux2 = id_item
					$titulo = new TItem_Basic( $id_biblioteca, $id_data_aux2, 1, $dbx );
					
					$fecha_devolucion = $titulo->ObtenerFechaDevolucion( $id_data_aux1 );
					$hora_devolucion	= get_str_onlytime( $fecha_devolucion, 0 );

					$body = transform_text( $body, 
									Array( Array( "<%USER_NAME%>", $user->NOMBRE_COMPLETO ), 
										   Array( "<%LIBRARY_NAME%>", $titulo->cNombreBiblioteca ), 
										   Array( "<%CHECKOUT_DATE%>", $fecha_prestamo ),
										   Array( "<%DEVOLUTION_DATE%>", $fecha_devolucion ),
										   Array( "<%DEVOLUTION_TIME%>", $hora_devolucion ),
										   Array( "<%ID_ITEM%>", $titulo->item_id_material ),
										   Array( "<%TITLE%>", $titulo->cTitle ),
										   Array( "<%AUTHOR%>", $titulo->cAutor) ) );

					$titulo->destroy();
				}		
			}			
			else if( $type == EMAIL_DEVS_ON_DUE )
			{
				//
				// Prestamos Retrasados en devolución
				//
				if( $user->GRUPO_NOTIFICA_EMAIL_RETRASO_DEV == "S" )
				{
					$sql = "SELECT b.FECHA_PRESTAMO, a.FECHA_DEVOLUCION_PROGRAMADA ";
					$sql .= " FROM prestamos_det a LEFT JOIN prestamos_mst b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PRESTAMO=a.ID_PRESTAMO)  " . 
							"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_PRESTAMO=$id_data_aux1 and a.ID_ITEM=$id_data_aux2 ";
					
					$result = $dbx->SubQuery( $sql );
					
					$fecha_prestamo    = "";
					$fecha_devolucion  = "";
					$hora_devolucion   = "";
					
					if( $row = $dbx->FetchRecord( $result ) )
					{
						$fecha_prestamo	 	= dbdate_to_human_format( $row["FECHA_PRESTAMO"], 0, 1, 0 );
						$fecha_devolucion	= dbdate_to_human_format( $row["FECHA_DEVOLUCION_PROGRAMADA"], 0, 1, 0 );
					} 
					
					$dbx->ReleaseResultset( $result );

					// id_data_aux2 = id_item
					$titulo = new TItem_Basic( $id_biblioteca, $id_data_aux2, 1, $dbx );
					
					$fecha_devolucion = $titulo->ObtenerFechaDevolucion( $id_data_aux1 );
					$hora_devolucion	= get_str_onlytime( $fecha_devolucion, 0 );

					$body = transform_text( $body, 
									Array( Array( "<%USER_NAME%>", $user->NOMBRE_COMPLETO ), 
										   Array( "<%LIBRARY_NAME%>", $titulo->cNombreBiblioteca ), 
										   Array( "<%CHECKOUT_DATE%>", $fecha_prestamo ),
										   Array( "<%DEVOLUTION_DATE%>", $fecha_devolucion ),
										   Array( "<%DEVOLUTION_TIME%>", $hora_devolucion ),
										   Array( "<%ID_ITEM%>", $titulo->item_id_material ),
										   Array( "<%TITLE%>", $titulo->cTitle ),
										   Array( "<%AUTHOR%>", $titulo->cAutor) ) );

					$titulo->destroy();
				}
			}
			else if( $type == EMAIL_RENEWALS )
			{
				// RENOVACIONES DE PRESTAMOS
				if( $user->GRUPO_NOTIFICA_EMAIL_RENOVA == "S" )
				{
					// id_data_aux1 = id_prestamo
					// id_data_aux2 = id_item
					$titulo = new TItem_Basic( $id_biblioteca, $id_data_aux2, 1, $dbx );

					$body = transform_text( $body, 
									Array( Array( "<%USER_NAME%>", $user->NOMBRE_COMPLETO ), 
										   Array( "<%LIBRARY_NAME%>", $titulo->cNombreBiblioteca ), 
										   Array( "<%DEVOLUTION_DATE%>", $titulo->ObtenerFechaDevolucion($id_data_aux1) ),
										   Array( "<%TITLE%>", $titulo->cTitle ),
										   Array( "<%AUTHOR%>", $titulo->cAutor) ) );

					$titulo->destroy();
				}
			}
			else if( $type == EMAIL_RESERVAS_ON_WAITING_LIST or $type == EMAIL_RESERVAS_ON_SPECIFIC_DATE )
			{
				//
				//  RESERVATIONS 
				//
				if( $user->GRUPO_NOTIFICA_EMAIL_RESERVA == "S" )
				{
					// id_data_aux1 = id_reservacion
					// id_data_aux2 = id_titulo
					
					$id_item = 0;
					
					$sql = "SELECT a.ID_BIBLIOTECA, a.ID_RESERVACION, b.FECHA_RESERVO, a.ID_TITULO, a.ID_ITEM, a.TIPO_RESERVACION, a.FECHA_RESERVACION " . 
							" FROM reservaciones_det a " . 
							"  INNER JOIN reservaciones_mst b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_RESERVACION=a.ID_RESERVACION) " .
							"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_RESERVACION=$id_data_aux1 and a.ID_TITULO=$id_data_aux2;";
					
					$result = $dbx->SubQuery( $sql );
					
					$fecha_reservo   = "";
					$fecha_reservacion = "";
					$id_tiporeservacion = -1;
					
					if( $row = $dbx->FetchRecord( $result ) )
					{
						$id_item = $row["ID_ITEM"];
						
						// $str_dbdate, $includetime=0, $month_as_str=0, $quitar_palabra_auxiliar = 1 )
						$fecha_reservo	 	= dbdate_to_human_format( $row["FECHA_RESERVO"], 1, 1, 0 ); // incluye hora
						$fecha_reservacion	= dbdate_to_human_format( $row["FECHA_RESERVACION"], 0, 0, 0 );
						
						$id_tiporeservacion = $row["TIPO_RESERVACION"];
					} 
					
					$dbx->ReleaseResultset( $result );

					// id_data_aux1 = id_reservacion
					// id_data_aux2 = id_titulo
					if( $id_tiporeservacion == 0 and $type == EMAIL_RESERVAS_ON_WAITING_LIST )
					{
						// no es un item específico; en lista de espera
						$titulo = new TItem_Basic( $id_biblioteca, $id_data_aux2, 0, $dbx );  // 0 = X TITULO

					}
					else
					{
						// item específico; en fecha específica
						$titulo = new TItem_Basic( $id_biblioteca, $id_item, 1, $dbx );  // 1 = X ITEM

					}

					$body = transform_text( $body, 
									Array( Array( "<%USER_NAME%>", $user->NOMBRE_COMPLETO ), 
										   Array( "<%LIBRARY_NAME%>", $titulo->cNombreBiblioteca ), 
										   Array( "<%RESERVATION_DATE%>", $fecha_reservo ),
										   Array( "<%RESERVATED_FOR%>", $fecha_reservacion ),
										   Array( "<%TITLE%>", $titulo->cTitle ),
										   Array( "<%AUTHOR%>", $titulo->cAutor) ) );

					$titulo->destroy();
				}
			}		
			else if( $type == EMAIL_RESERVAS_CANCELED )
			{
				// Reservaciones Canceladas
				echo "Pendiente de implementar";
			}
			else if( $type == EMAIL_RESERVAS_AVAILABLE_FOR_TODAY )
			{
				//
				//  RESERVATIONS DE ITEMS ESPECÍFICOS QUE ESTARÁN DISPONIBLES PARA HOY
				//
				
				if( $user->GRUPO_NOTIFICA_EMAIL_RESERVA == "S" )
				{
					// id_data_aux1 = id_reservacion
					// id_data_aux2 = id_titulo
					// id_data_aux3 = ID_ITEM
					$id_item = $id_data_aux3;
					
					$sql = "SELECT a.ID_BIBLIOTECA, a.ID_RESERVACION, b.FECHA_RESERVO, a.ID_TITULO, a.ID_ITEM, a.TIPO_RESERVACION, a.FECHA_RESERVACION " . 
							" FROM reservaciones_det a " . 
							"  INNER JOIN reservaciones_mst b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_RESERVACION=a.ID_RESERVACION) " .
							"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_RESERVACION=$id_data_aux1 and a.ID_TITULO=$id_data_aux2;";

					$result = $dbx->SubQuery( $sql );

					$fecha_reservo   = "";
					$fecha_reservacion = "";
					$id_tiporeservacion = -1;
					
					if( $row = $dbx->FetchRecord( $result ) )
					{
						// $str_dbdate, $includetime=0, $month_as_str=0, $quitar_palabra_auxiliar = 1 )
						$fecha_reservo	 	= dbdate_to_human_format( $row["FECHA_RESERVO"], 1, 1, 0 ); // incluye hora
						$fecha_reservacion	= dbdate_to_human_format( $row["FECHA_RESERVACION"], 0, 0, 0 );

						$id_tiporeservacion = $row["TIPO_RESERVACION"];
					} 
					
					$dbx->ReleaseResultset( $result );

					//
					// Siempre se sabrá en id_data_aux3 el ID_ITEM
					//
					$titulo = new TItem_Basic( $id_biblioteca, $id_item, 1, $dbx );  // 1 = X ITEM

					$body = transform_text( $body, 
									Array( Array( "<%USER_NAME%>", $user->NOMBRE_COMPLETO ), 
										   Array( "<%LIBRARY_NAME%>", $titulo->cNombreBiblioteca ), 
										   Array( "<%RESERVATION_DATE%>", $fecha_reservo ),
										   Array( "<%RESERVATED_FOR%>", $fecha_reservacion ),
										   Array( "<%ID_ITEM%>", $titulo->item_id_material ),
										   Array( "<%CALL_NUMBER%>", $titulo->CallNumber() ),										   
										   Array( "<%TITLE%>", $titulo->cTitle ),
										   Array( "<%AUTHOR%>", $titulo->cAutor ) ) );

					$titulo->destroy();
				}
			}					
			else if( $type == EMAIL_RESERVAS_IN_PROBLEM_FOR_TODAY )
			{
				// Reservaciones que deberían estar disponibles hoy
				// PERO HAY UN PROBLEMA DE DISPONIBILIDAD DEL ITEM

				if( $user->GRUPO_NOTIFICA_EMAIL_RESERVA == "S" )
				{
					// id_data_aux1 = id_reservacion
					// id_data_aux2 = id_titulo
					// id_data_aux3 = ID_ITEM original
					
					$id_item = $id_data_aux3;
					
					$sql = "SELECT a.ID_BIBLIOTECA, a.ID_RESERVACION, b.FECHA_RESERVO, a.ID_TITULO, a.ID_ITEM, a.TIPO_RESERVACION, a.FECHA_RESERVACION " . 
							" FROM reservaciones_det a " . 
							"  INNER JOIN reservaciones_mst b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_RESERVACION=a.ID_RESERVACION) " .
							"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_RESERVACION=$id_data_aux1 and a.ID_TITULO=$id_data_aux2;";
					
					$result = $dbx->SubQuery( $sql );
					
					$fecha_reservo   = "";
					$fecha_reservacion = "";
					$id_tiporeservacion = -1;
					
					if( $row = $dbx->FetchRecord( $result ) )
					{
						// $str_dbdate, $includetime=0, $month_as_str=0, $quitar_palabra_auxiliar = 1 )
						$fecha_reservo	 	= dbdate_to_human_format( $row["FECHA_RESERVO"], 1, 1, 0 ); // incluye hora
						$fecha_reservacion	= dbdate_to_human_format( $row["FECHA_RESERVACION"], 0, 0, 0 );
						
						$id_tiporeservacion = $row["TIPO_RESERVACION"];
					} 
					
					$dbx->ReleaseResultset( $result );

					// item específico; en fecha específica
					$titulo = new TItem_Basic( $id_biblioteca, $id_item, 1, $dbx );  // 1 = X ITEM

					$problem = "";
					
					$fecha_hoy = getcurdate_human_format();
					
					$body = transform_text( $body, 
									Array( Array( "<%USER_NAME%>", $user->NOMBRE_COMPLETO ), 
										   Array( "<%LIBRARY_NAME%>", $titulo->cNombreBiblioteca ), 
										   Array( "<%RESERVATION_DATE%>", $fecha_reservo ),
										   Array( "<%RESERVATED_FOR%>", $fecha_reservacion ),
										   Array( "<%TODAY%>", $fecha_hoy ),
										   Array( "<%PROBLEM_DESCRIPTION%>", $problem ),
										   Array( "<%ID_ITEM%>", $titulo->item_id_material ),
										   Array( "<%CALL_NUMBER%>", $titulo->CallNumber() ),										   
										   Array( "<%TITLE%>", $titulo->cTitle ),
										   Array( "<%AUTHOR%>", $titulo->cAutor) ) );

					$titulo->destroy();
				}

			}			
			else if( $type == EMAIL_RESERVAS_PROBLEM_SOLVED )
			{
				//
				//  RESERVATIONS DE ITEMS ESPECÍFICOS QUE FUERON REEMPLAZADOS POR OTRO ITEM
				//  ORIGINALMENTE HUBO UN PROBLEMA DE DISPONIBILIDAD
				//
				if( $user->GRUPO_NOTIFICA_EMAIL_RESERVA == "S" )
				{
					// id_data_aux1 = id_reservacion
					// id_data_aux2 = id_titulo
					// id_data_aux3  = id_item
					
					$id_item = $id_data_aux3;  // nuevo item elegido por el sistema
					
					$sql = "SELECT a.ID_BIBLIOTECA, a.ID_RESERVACION, b.FECHA_RESERVO, a.ID_TITULO, a.ID_ITEM, a.TIPO_RESERVACION, a.FECHA_RESERVACION " . 
							" FROM reservaciones_det a " . 
							"  INNER JOIN reservaciones_mst b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_RESERVACION=a.ID_RESERVACION) " .
							"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_RESERVACION=$id_data_aux1 and a.ID_TITULO=$id_data_aux2;";

					$result = $dbx->SubQuery( $sql );

					$fecha_reservo   = "";
					$fecha_reservacion = "";
					$id_tiporeservacion = -1;
					
					if( $row = $dbx->FetchRecord( $result ) )
					{
						// $str_dbdate, $includetime=0, $month_as_str=0, $quitar_palabra_auxiliar = 1 )
						$fecha_reservo	 	= dbdate_to_human_format( $row["FECHA_RESERVO"], 1, 1, 0 ); // incluye hora
						$fecha_reservacion	= dbdate_to_human_format( $row["FECHA_RESERVACION"], 0, 0, 0 );

						$id_tiporeservacion = $row["TIPO_RESERVACION"];
					} 
					
					$dbx->ReleaseResultset( $result );

					//
					// Siempre se sabrá en id_data_aux3 el ID_ITEM
					//
					$titulo = new TItem_Basic( $id_biblioteca, $id_item, 1, $dbx );  // 1 = X ITEM

					$body = transform_text( $body, 
									Array( Array( "<%USER_NAME%>", $user->NOMBRE_COMPLETO ), 
										   Array( "<%LIBRARY_NAME%>", $titulo->cNombreBiblioteca ), 
										   Array( "<%RESERVATION_DATE%>", $fecha_reservo ),
										   Array( "<%RESERVATED_FOR%>", $fecha_reservacion ),
										   Array( "<%ID_ITEM%>", $titulo->item_id_material ),
										   Array( "<%CALL_NUMBER%>", $titulo->CallNumber() ),										   
										   Array( "<%TITLE%>", $titulo->cTitle ),
										   Array( "<%AUTHOR%>", $titulo->cAutor ) ) );

					$titulo->destroy();
				}
			}			
			else if( $type == EMAIL_RESERVAS_EXPIRED )
			{
				//
				//  RESERVATIONS DE ITEMS BLOQUEADOS HAN EXPIRADO
				//
				
				if( $user->GRUPO_NOTIFICA_EMAIL_RESERVA == "S" )
				{
					// id_data_aux1 = id_titulo
					// id_data_aux2 = ID_ITEM
					$id_titulo = $id_data_aux1;
					$id_item   = $id_data_aux2;
					
					$sql = "SELECT a.ID_BIBLIOTECA, a.ID_TITULO, a.ID_ITEM, a.BLOQUEADO_DESDE, a.BLOQUEADO_HASTA " . 
							" FROM acervo_copias a " . 
							"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TITULO=$id_titulo and a.ID_ITEM=$id_item;";

					$result = $dbx->SubQuery( $sql );

					$fecha_bloqueo  = "";
					$fecha_expires  = "";
					
					if( $row = $dbx->FetchRecord( $result ) )
					{
						$fecha_bloqueo  = dbdate_to_human_format( $row["BLOQUEADO_DESDE"], 0, 0, 0 ); // incluye hora
						$fecha_expires	= dbdate_to_human_format( $row["BLOQUEADO_HASTA"], 0, 0, 0 );
					} 
					
					$dbx->ReleaseResultset( $result );

					$fecha_hoy = getcurdate_human_format();
					
					//
					// Siempre se sabrá en id_data_aux3 el ID_ITEM
					//
					$titulo = new TItem_Basic( $id_biblioteca, $id_item, 1, $dbx );  // 1 = X ITEM

					$body = transform_text( $body, 
									Array( Array( "<%USER_NAME%>", $user->NOMBRE_COMPLETO ), 
										   Array( "<%LIBRARY_NAME%>", $titulo->cNombreBiblioteca ), 
										   Array( "<%BLOCKED_DATE%>", $fecha_bloqueo ),
										   Array( "<%BLOCKED_UNTIL%>", $fecha_expires ),
										   Array( "<%TODAY%>", $fecha_hoy ),
										   Array( "<%ID_ITEM%>", $titulo->item_id_material ),
										   Array( "<%CALL_NUMBER%>", $titulo->CallNumber() ),
										   Array( "<%TITLE%>", $titulo->cTitle ),
										   Array( "<%AUTHOR%>", $titulo->cAutor ) ) );

					$titulo->destroy();
				}				
			}
			else if( $type == EMAIL_SANCTIONS )
			{
				// SANCIONES REGISTRADAS
				if( $user->GRUPO_NOTIFICA_EMAIL_SANCIONES == "S" )
				{								
					$sql = "SELECT a.FECHA_SANCION, a.FECHA_LIMITE, a.MOTIVO, a.STATUS_SANCION, a.MONTO_SANCION, a.OBSERVACIONES, b.DESCRIPCION AS DESCRIP_SANCION ";
					$sql .= " FROM sanciones a LEFT JOIN cfgsanciones b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.TIPO_SANCION=a.TIPO_SANCION)  " . 
							"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_SANCION=$id_data_aux1";
					
					$result = $dbx->SubQuery( $sql );
					
					$descrip_sancion = "";
					$fecha_sancion   = "";
					$fecha_limite    = "";
					$motivo          = "";
					$status          = "";
					$monto_sancion   = 0;
					$obs_sancion     = "";
					
					if( $row = $dbx->FetchRecord( $result ) )
					{
						$descrip_sancion = $row["DESCRIP_SANCION"];
						$fecha_sancion	 = dbdate_to_human_format( $row["FECHA_SANCION"], 0, 1, 1 );
						$fecha_limite	 = dbdate_to_human_format( $row["FECHA_LIMITE"], 0, 1, 1 );
						$motivo 		 = $row["MOTIVO"];
						$status 		 = $row["STATUS_SANCION"];
						$monto_sancion   = $row["MONTO_SANCION"];
						$obs_sancion 	 = $row["OBSERVACIONES"];
					} 
					
					$dbx->ReleaseResultset( $result );
					
					$body = transform_text( $body, 
									Array( Array( "<%USER_FULLNAME%>", $user->NOMBRE_COMPLETO ), 
										   Array( "<%USERNAME%>", $user->USERNAME ),
										   Array( "<%LIBRARY_NAME%>", $user->LIBRARY_NAME ), 
										   Array( "<%LIBRARY_BIBLIOTEK_URL%>", $user->LIBRARY_BIBLIOTEK_URL ),
										   Array( "<%SANCTION_DESCRIPTION%>", $descrip_sancion ),
										   Array( "<%SANCTION_DATE_RECORDED%>", $fecha_sancion ),
										   Array( "<%SANCTION_DATE_LIMIT%>", $fecha_limite ),
										   Array( "<%SANCTION_REASON%>", $motivo ),
										   Array( "<%SANCTION_STATUS%>", $status ),
										   Array( "<%SANCTION_AMOUNT%>", $monto_sancion ),
										   Array( "<%SANCTION_EXTRA_DETAILS%>", $obs_sancion ) ) );
				}

			}
			else if( $type == EMAIL_SANCTIONS_ON_DUE )
			{
				// SANCION INCUMPLIDA
				if( $user->GRUPO_NOTIFICA_EMAIL_SANCIONES == "S" )
				{
					$sql = "SELECT a.FECHA_SANCION, a.FECHA_LIMITE, a.MOTIVO, a.STATUS_SANCION, a.MONTO_SANCION, a.OBSERVACIONES, b.DESCRIPCION AS DESCRIP_SANCION ";
					$sql .= " FROM sanciones a LEFT JOIN cfgsanciones b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.TIPO_SANCION=a.TIPO_SANCION)  " . 
							"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_SANCION=$id_data_aux1";
					
					$result = $dbx->SubQuery( $sql );
					
					$descrip_sancion = "";
					$fecha_sancion   = "";
					$fecha_limite    = "";
					$motivo          = "";
					$status          = "";
					$monto_sancion   = 0;
					$obs_sancion     = "";
					
					if( $row = $dbx->FetchRecord( $result ) )
					{
						$descrip_sancion = $row["DESCRIP_SANCION"];
						$fecha_sancion	 = dbdate_to_human_format( $row["FECHA_SANCION"], 0, 1, 1 );
						$fecha_limite	 = dbdate_to_human_format( $row["FECHA_LIMITE"], 0, 1, 1 );
						$motivo 		 = $row["MOTIVO"];
						$status 		 = $row["STATUS_SANCION"];
						$monto_sancion   = $row["MONTO_SANCION"];
						$obs_sancion 	 = $row["OBSERVACIONES"];
					} 
					
					$dbx->ReleaseResultset( $result );

					$body = transform_text( $body, 
									Array( Array( "<%USER_FULLNAME%>", $user->NOMBRE_COMPLETO ), 
										   Array( "<%USERNAME%>", $user->USERNAME ),
										   Array( "<%LIBRARY_NAME%>", $user->LIBRARY_NAME ), 
										   Array( "<%LIBRARY_BIBLIOTEK_URL%>", $user->LIBRARY_BIBLIOTEK_URL ),
										   Array( "<%SANCTION_DESCRIPTION%>", $descrip_sancion ),
										   Array( "<%SANCTION_DATE_RECORDED%>", $fecha_sancion ),
										   Array( "<%SANCTION_DATE_LIMIT%>", $fecha_limite ),
										   Array( "<%SANCTION_REASON%>", $motivo ),
										   Array( "<%SANCTION_STATUS%>", $status ),
										   Array( "<%SANCTION_AMOUNT%>", $monto_sancion ),
										   Array( "<%SANCTION_EXTRA_DETAILS%>", $obs_sancion ) ) );
				}
			}			
			else if( $type == EMAIL_SANCTIONS_WAS_ACOMPLISHED or $type == EMAIL_SANCTIONS_WAS_CONDONED )
			{
				//
				// SANCION CUMPLIDA o SANCION CONDONADA
				//
				if( $user->GRUPO_NOTIFICA_EMAIL_SANCIONES == "S" )
				{								
					// se trae, adicionalmente, el nombre del usuario que registró el cumplimiento
					$sql = "SELECT a.FECHA_SANCION, a.FECHA_LIMITE, a.MOTIVO, a.STATUS_SANCION, a.MONTO_SANCION, a.OBSERVACIONES, a.FECHA_CUMPLIDA, a.CONDONACION, " .
						   " a.DETALLES_CUMPLIMIENTO, b.DESCRIPCION AS DESCRIP_SANCION, c.PATERNO, c.MATERNO, c.NOMBRE  ";
					$sql .= " FROM sanciones a " . 
							"  LEFT JOIN cfgsanciones b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.TIPO_SANCION=a.TIPO_SANCION)  " . 
							"    LEFT JOIN cfgusuarios c ON (c.ID_BIBLIOTECA=a.ID_BIBLIOTECA and c.ID_USUARIO=a.ID_USUARIO_REGISTRO_CUMP) " .
							"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_SANCION=$id_data_aux1";
					
					$result = $dbx->SubQuery( $sql );
					
					$descrip_sancion = "";
					$fecha_sancion   = "";
					$fecha_limite    = "";
					$fecha_cumplimiento = "";
					$detalles_cumplimiento = "";
					$motivo          = "";
					$status          = "";
					$monto_sancion   = 0;
					$obs_sancion     = "";
					
					$condonada = "";
					$usuario_registro_cump = "";
					
					if( $row = $dbx->FetchRecord( $result ) )
					{
						$descrip_sancion = $row["DESCRIP_SANCION"];
						$fecha_sancion	 = dbdate_to_human_format( $row["FECHA_SANCION"], 0, 1, 1 );
						$fecha_limite	 = dbdate_to_human_format( $row["FECHA_LIMITE"], 0, 1, 1 );
						$motivo 		 = $row["MOTIVO"];
						$status 		 = $row["STATUS_SANCION"];
						$monto_sancion   = $row["MONTO_SANCION"];
						$obs_sancion 	 = $row["OBSERVACIONES"];
						
						$condonada 		 = $row["CONDONACION"];
						
						$fecha_cumplimiento   = dbdate_to_human_format( $row["FECHA_CUMPLIDA"], 0, 1, 1 );;
						$detalles_cumplimiento = $row["DETALLES_CUMPLIMIENTO"];
						
						$usuario_registro_cump = $row["PATERNO"] . " " . $row["MATERNO"] . " " . $row["NOMBRE"];
					} 
					
					$dbx->ReleaseResultset( $result );
										
					$body = transform_text( $body, 
									Array(	Array( "<%USER_FULLNAME%>", $user->NOMBRE_COMPLETO ), 
											Array( "<%USERNAME%>", $user->USERNAME ),
											Array( "<%LIBRARY_NAME%>", $user->LIBRARY_NAME ), 
											Array( "<%LIBRARY_BIBLIOTEK_URL%>", $user->LIBRARY_BIBLIOTEK_URL ),
											Array( "<%SANCTION_DESCRIPTION%>", $descrip_sancion ),
											Array( "<%SANCTION_DATE_RECORDED%>", $fecha_sancion ),
											Array( "<%SANCTION_DATE_LIMIT%>", $fecha_limite ),
											Array( "<%SANCTION_DATE_ACOMPLISHED%>", $fecha_cumplimiento ),
											Array( "<%SANCTION_REASON%>", $motivo ),
											Array( "<%SANCTION_STATUS%>", $status ),
											Array( "<%SANCTION_AMOUNT%>", $monto_sancion ),
											Array( "<%SANCTION_EXTRA_DETAILS%>", $obs_sancion ),
											Array( "<%SANCTION_ACOMPLISHED_DETAILS%>", $detalles_cumplimiento ),
											Array( ($condonada == "S") ?  "<%SANCTION_USER_CONDONATED%>" : "<%SANCTION_USER_RECORDED%>", $usuario_registro_cump ) ) );
				}				
			}
			else if( $type == EMAIL_RESTRICTION )
			{
				if( $user->GRUPO_NOTIFICA_EMAIL_RESTRICCIONES == "S" )
				{								
					$sql = "SELECT a.TIPO_RESTRICCION, b.DESCRIPCION AS DESCRIP_RESTRICCION, a.FECHA_INICIO, a.FECHA_FINAL, a.MOTIVO, a.STATUS_RESTRICCION ";
					$sql .= " FROM restricciones a LEFT JOIN cfgrestricciones b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.TIPO_RESTRICCION=a.TIPO_RESTRICCION)  " . 
							"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_RESTRICCION=$id_data_aux1";
					
					$result = $dbx->SubQuery( $sql );
					
					$descrip_restriccion = "";
					$fecha_inicial   = "";
					$fecha_final     = "";
					$descripcion     = "";
					$status			 = "";
					
					if( $row = $dbx->FetchRecord( $result ) )
					{
						$descrip_restriccion = $row["DESCRIP_RESTRICCION"];
						$fecha_inicial	 = dbdate_to_human_format( $row["FECHA_INICIO"], 0, 1, 1 );
						$fecha_final	 = dbdate_to_human_format( $row["FECHA_FINAL"], 0, 1, 1 );
						$descripcion 	 = $row["MOTIVO"];
						$status 		 = $row["STATUS_RESTRICCION"];
					} 
					
					$dbx->ReleaseResultset( $result );
					
					$body = transform_text( $body, 
									Array( Array( "<%USER_FULLNAME%>", $user->NOMBRE_COMPLETO ), 
										   Array( "<%USERNAME%>", $user->USERNAME ),
										   Array( "<%LIBRARY_NAME%>", $user->LIBRARY_NAME ), 
										   Array( "<%LIBRARY_BIBLIOTEK_URL%>", $user->LIBRARY_BIBLIOTEK_URL ),
										   Array( "<%RESTRICTION_DESCRIPTION%>", $descrip_restriccion ),
										   Array( "<%RESTRICTION_DATE_BEGIN%>", $fecha_inicial ),
										   Array( "<%RESTRICTION_DATE_END%>", $fecha_final ),
										   Array( "<%RESTRICTION_REASON%>", $descripcion ),
										   Array( "<%RESTRICTION_STATUS%>", $status ) ) );
				}
			}
			else if( $type == EMAIL_RESTRICTION_CANCELLED )
			{
				if( $user->GRUPO_NOTIFICA_EMAIL_RESTRICCIONES == "S" )
				{								
					$sql = "SELECT a.TIPO_RESTRICCION, b.DESCRIPCION AS DESCRIP_RESTRICCION, a.FECHA_INICIO, a.FECHA_FINAL, a.MOTIVO, a.STATUS_RESTRICCION ";
					$sql .= " FROM restricciones a LEFT JOIN cfgrestricciones b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.TIPO_RESTRICCION=a.TIPO_RESTRICCION)  " . 
							"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_RESTRICCION=$id_data_aux1";
					
					$result = $dbx->SubQuery( $sql );
					
					$descrip_restriccion = "";
					$fecha_inicial   = "";
					$fecha_final     = "";
					$descripcion     = "";
					$status			 = "";
					
					if( $row = $dbx->FetchRecord( $result ) )
					{
						$descrip_restriccion = $row["DESCRIP_RESTRICCION"];
						$fecha_inicial	 	 = dbdate_to_human_format( $row["FECHA_INICIO"], 0, 1, 1 );
						$fecha_final	 	 = dbdate_to_human_format( $row["FECHA_FINAL"], 0, 1, 1 );
						$descripcion 	 	 = $row["MOTIVO"];
						$status 		 	 = $row["STATUS_RESTRICCION"];
					}
					
					$dbx->ReleaseResultset( $result );
					
					$body = transform_text( $body, 
									Array( Array( "<%USER_FULLNAME%>", $user->NOMBRE_COMPLETO ), 
										   Array( "<%USERNAME%>", $user->USERNAME ),
										   Array( "<%LIBRARY_NAME%>", $user->LIBRARY_NAME ), 
										   Array( "<%LIBRARY_BIBLIOTEK_URL%>", $user->LIBRARY_BIBLIOTEK_URL ),
										   Array( "<%RESTRICTION_DESCRIPTION%>", $descrip_restriccion ),
										   Array( "<%RESTRICTION_DATE_BEGIN%>", $fecha_inicial ),
										   Array( "<%RESTRICTION_DATE_END%>", $fecha_final ),
										   Array( "<%RESTRICTION_REASON%>", $descripcion ),
										   Array( "<%RESTRICTION_STATUS%>", $status ) ) );
				}				
			}
			else if( $type == EMAIL_RESTRICTION_EXPIRED )
			{

				if( $user->GRUPO_NOTIFICA_EMAIL_RESTRICCIONES == "S" )
				{								
					$sql = "SELECT a.TIPO_RESTRICCION, b.DESCRIPCION AS DESCRIP_RESTRICCION, a.FECHA_INICIO, a.FECHA_FINAL, a.MOTIVO, a.STATUS_RESTRICCION ";
					$sql .= " FROM restricciones a LEFT JOIN cfgrestricciones b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.TIPO_RESTRICCION=a.TIPO_RESTRICCION)  " . 
							"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_RESTRICCION=$id_data_aux1";
					
					$result = $dbx->SubQuery( $sql );
					
					$descrip_restriccion = "";
					$fecha_inicial   = "";
					$fecha_final     = "";
					$descripcion     = "";
					$status			 = "";
					
					if( $row = $dbx->FetchRecord( $result ) )
					{
						$descrip_restriccion = $row["DESCRIP_RESTRICCION"];
						$fecha_inicial	 = dbdate_to_human_format( $row["FECHA_INICIO"], 0, 1, 1 );
						$fecha_final	 = dbdate_to_human_format( $row["FECHA_FINAL"], 0, 1, 1 );
						$descripcion 	 = $row["MOTIVO"];
						$status 		 = $row["STATUS_RESTRICCION"];
					} 
					
					$dbx->ReleaseResultset( $result );
					
					$body = transform_text( $body, 
									Array( Array( "<%USER_FULLNAME%>", $user->NOMBRE_COMPLETO ), 
										   Array( "<%USERNAME%>", $user->USERNAME ),
										   Array( "<%LIBRARY_NAME%>", $user->LIBRARY_NAME ), 
										   Array( "<%LIBRARY_BIBLIOTEK_URL%>", $user->LIBRARY_BIBLIOTEK_URL ),
										   Array( "<%RESTRICTION_DESCRIPTION%>", $descrip_restriccion ),
										   Array( "<%RESTRICTION_DATE_BEGIN%>", $fecha_inicial ),
										   Array( "<%RESTRICTION_DATE_END%>", $fecha_final ),
										   Array( "<%RESTRICTION_REASON%>", $descripcion ),
										   Array( "<%RESTRICTION_STATUS%>", $status ) ) );
				}

			}
			else if( $type == EMAIL_COMMENTS_RECEIVED )
			{
				if( 1 ) // $user->GRUPO_NOTIFICA_EMAIL_RENOVA == "S"
				{
					// id_data_aux1 = ID_TITULO
					// id_data_aux2   = ID_OPINION

					$fecha_comentario = "";
					$nota_recibida	  = "";
					$nota_as_stars    = "";
					$resumen = "";
					$opinion = "";
					
					$sql = "SELECT a.ID_BIBLIOTECA, a.ID_TITULO, a.ID_OPINION, a.CALIFICACION, a.COMENTARIO, a.OPINION, a.EXPERTO, a.FECHA_OPINION " .
						   "FROM acervo_titulos_califs a ".
						   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TITULO=$id_data_aux1 and a.ID_OPINION=$id_data_aux2;";
					
					$result = $dbx->SubQuery( $sql );

					if( $row = $dbx->FetchRecord( $result ) )
					{
						$fecha_comentario	= $row["FECHA_OPINION"];
						$nota_recibida		= $row["CALIFICACION"];;
						$resumen		    = $row["COMENTARIO"];
						$opinion	     	= $dbx->GetBLOB( $row["OPINION"] );
					}
					
					$dbx->ReleaseResultset( $result );								
					
					$titulo = new TItem_Basic( $id_biblioteca, $id_data_aux1, 0, $dbx );

					$body = transform_text( $body, 
									Array( Array( "<%USER_NAME%>", $user->NOMBRE_COMPLETO ), 
										   Array( "<%LIBRARY_NAME%>", $titulo->cNombreBiblioteca ), 
										   Array( "<%COMMENT_DATE%>", $fecha_comentario ),
										   Array( "<%NOTE_VALUE_RECEIVED%>", $nota_recibida ),
										   Array( "<%NOTE_GRAPH_RECEIVED%>", $nota_as_stars ),
										   Array( "<%COMMENT_SUMMARY%>", $resumen ),
										   Array( "<%OPINION%>", $opinion ),
										   Array( "<%TITLE%>", $titulo->cTitle ),
										   Array( "<%AUTHOR%>", $titulo->cAutor) ) );
										   
					$titulo->destroy();
				}				
			}
			else if( $type == EMAIL_USER_CREATION )
			{
				$body = transform_text( $body, 
					Array( Array( "<%USER_FULLNAME%>", $user->NOMBRE_COMPLETO ), 
						   Array( "<%USERNAME%>", $user->USERNAME ),
						   Array( "<%PASSWORD%>", $id_data_aux1 ),
						   Array( "<%LIBRARY_NAME%>", $user->LIBRARY_NAME ), 
						   Array( "<%LIBRARY_BIBLIOTEK_URL%>", "'".$user->LIBRARY_BIBLIOTEK_URL."'" ) ) );
			}
			else if ( $type == EMAIL_USER_CONFIRMATION )
			{
				$body = transform_text( $body, 
					Array( Array( "<%USER_FULLNAME%>", $user->NOMBRE_COMPLETO ), 
						   Array( "<%USERNAME%>", $user->USERNAME ),
						   Array( "<%PASSWORD%>", $id_data_aux1 ),
						   Array( "<%LIBRARY_NAME%>", $user->LIBRARY_NAME ), 
						   Array( "<%LIBRARY_BIBLIOTEK_URL%>", "'".$user->LIBRARY_BIBLIOTEK_URL."'" ) ) );			
			}
			else if ( $type == EMAIL_USER_PASSWORD_RESETED )
			{
				$body = transform_text( $body, 
					Array( Array( "<%USER_FULLNAME%>", $user->NOMBRE_COMPLETO ), 
						   Array( "<%USERNAME%>", $user->USERNAME ),
						   Array( "<%PASSWORD%>", $id_data_aux1 ),
						   Array( "<%LIBRARY_NAME%>", $user->LIBRARY_NAME ), 
						   Array( "<%LIBRARY_BIBLIOTEK_URL%>", "'".$user->LIBRARY_BIBLIOTEK_URL."'" ) ) );			
			}
			else if( $type == EMAIL_USER_PASSWORD_CHANGED )
			{
				$body = transform_text( $body, 
					Array( Array( "<%USER_FULLNAME%>", $user->NOMBRE_COMPLETO ), 
						   Array( "<%USERNAME%>", $user->USERNAME ),
						   Array( "<%PASSWORD%>", $id_data_aux1 ),
						   Array( "<%LIBRARY_NAME%>", $user->LIBRARY_NAME ), 
						   Array( "<%LIBRARY_BIBLIOTEK_URL%>", $user->LIBRARY_BIBLIOTEK_URL ) ) );
			}
			
			if( $body != "" )
			{
				if( $global_smtp_connection != NULL )
				{
					$user->bulkMailConnection = $global_smtp_connection;
				}
				
				$ret = $user->Send_EMAIL_Message_TO_USER( $subject, $body );
			}
		}
		
		if( $user_already_created == NULL )
			$user->destroy();
		
		return $ret;
	}
	

	function process_email_admin_info( $dbx, $id_biblioteca, $id_admin_usuario, $global_smtp_connection, $subj="", $info="" )
	{
		require_once( "circulacion.inc.php" );
		
		$user = new TUser( $id_biblioteca, $id_admin_usuario, $dbx );
		
		$user->bulkMailConnection = $global_smtp_connection;
			
		$user->Send_EMAIL_Message_TO_USER( $subj, $info );		
		
		$user->destroy();
	}

 ?>