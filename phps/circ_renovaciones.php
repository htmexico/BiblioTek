<?php
session_start();
/**********

	28-enero-2009	Se crea el archivo circulacion.php
	22-Abril-2009	Se crean interfaz grafica para renovaciones.
	23-Abril-2009	Se crea validacion para obtener items prestados.
	24-Abril-2009	Se crea funcion para validar el item que se renovara.
	27-Abril-2009	Se cambian checkbox por botones para verificar renovacion.
	28-Abril-2009	Se crea validacion de fecha de prestamo y fecha de renovacion para determinar si se renovara el item prestado.
	30-Abril-2009	Se crea Query Insert en tabla Renovaciones.
	06-Mayo-2009	Se crean validaciones para restricciones y sanciones en renovaciones.
	08-Mayo-2009	Se agregan variables de lenguaje.
	19-oct-2009     Se hacen cambios de validaciones y despliegue; además se considera la verificación de restricciones.

 **/
	include ("../funcs.inc.php");
	include ("../basic/bd.class.php");
	
	include ("circulacion.inc.php");
	
	include_language( "global_menus" ); // agregar en todos los archivos

	check_usuario_firmado(); 
	
	include_language( "circ_renovaciones" );

	$nombre_usuario = "";
	$grupo = "";
	
	$items_en_prestamo = 0;
	$sanciones = 0;	
	
	$max_renovaciones  =  0;
	$dias_renovacion   = 0;
	$permite_renovacion_con_retraso = "";
	
	$id_biblioteca =getsessionvar('id_biblioteca');
	$id_usuario = read_param("id_usuario",0,1);  // fail if not exists
	$the_action = read_param( "the_action", "" );
	
	if( getsessionvar( "empleado" ) != "S" )
	{
		// no es un empleado
		// verificacion de aseguramiento
		if( $id_usuario != getsessionvar("id_usuario") )
			die( "Llamada incorrecta" );
	}	
	
	$db = new DB;
	
	$error = 0;
	$error_msg = "";
	
	if( $the_action == "save_renewal" )
	{
		$id_prestamo = read_param( "id_prestamo", 0, 1 );  // fail if not exists
		$id_item     = read_param( "id_item", 0, 1 );  // fail if not exists
		$fecha_original = read_param( "fecha_original", "", 1 );  // fail if not exists
		$fecha_renovacion = read_param( "fecha_renovacion", "", 1 );  // fail if not exists

		$fecha_hora_renov = read_param( "fecha_hora_renov", "", 1 );  // fail if not exists
		
		//
		// verificar en segunda instancia si el item sigue disponible
		// el proceso pudo haber sido retardado por el usuario
		// y no podemos asegurar hasta este momento
		//
		
		$titulo = new TItem_Basic( $id_biblioteca, $id_item, 1 );

		$fechas_disponibles = Array();
		
		$fecha_original = sum_days( $fecha_original, 1, 1 );

		$te = $titulo->VerificarDisponibilidad_X_ITEM( $id_item, 
			          "N", $fecha_original, $fecha_renovacion, $fechas_disponibles, 5, 1, 0, 0, $id_usuario );
			
		unset( $fechas_disponibles );
		$titulo->destroy();
		
		if( $te ) //fecha de renovacion esta dentro de los parametro de fecha de entrega
		{	
			$bandera_insert=1;
		}
		else 
		{
			SYNTAX_JavaScript( 1, 1, "alert( '$MSG_ITEM_NOT_AVAIL_ANYMORE' );" );
			$bandera_insert=0;
		}
		
		if($bandera_insert==1)
		{
			//
			// Ver Cuantas renovaciones se han hecho 
			// de este préstamo
			//
			$renovaciones_anteriores = $titulo->VerificaNumeroRenovaciones( $id_prestamo );
	
			$db->Close();
			
			$user = new TUser( $id_biblioteca, $id_usuario );
			
			// PDTE - Verificar RENOVACIONES PREVIAS
			if( $user->GRUPO_MAX_RENOVACIONES == 0 )
			{
				$error = 3; // Usuario no puede renovar
				
				SYNTAX_JavaScript( 1, 1, "alert( '$MSG_USER_CANT_RENEW' );" );
			}			
			// Verificar RENOVACIONES PREVIAS
			if( $renovaciones_anteriores >= $user->GRUPO_MAX_RENOVACIONES )			
			{
				$error = 4; // Usuario ha alcanzado limite de renovaciones por material
				
				SYNTAX_JavaScript( 1, 1, "alert( '" . sprintf($MSG_IMPOSIBLE_RENEWAL_2, $renovaciones_anteriores) . "' );" );
			}			
			// PDTE - Verificar Restricciones
			if( $user->ObtenerNumRestricciones(4) > 0 )
			{				
				$error = 5; // El usuario tiene restricciones en vigor
				
				SYNTAX_JavaScript( 1, 1, "alert( '$MST_USER_HAS_RESTRICTIONS' );" );
			}
			// Verificar Sanciones incumplidas
			if( $user->GRUPO_PERMITIRRENOVA_CON_SANCIONES == "N" )
			{
				if( $user->ObtenerNumSanciones() > 0 )
				{				
					$error = 6; // El usuario tiene sanciones incumplidas
					
					SYNTAX_JavaScript( 1, 1, "alert( '$MSG_USER_HAS_SANCTIONS' );" );
				}
			}
			
			$user->destroy();
			unset( $user );			

			if( $error == 0 )
			{
				//Ultimo registro de renovaciones
				$db->Open( "SELECT COUNT(ID_RENOVACION) AS CUANTOS, MAX(ID_RENOVACION) AS MAXIMO FROM RENOVACIONES ".
							"WHERE ID_BIBLIOTECA=$id_biblioteca;" );
		
				if( $db->NextRow() )
				{
					if( $db->row["CUANTOS"] == 0 )
						$renovacion = 1;
					else
						$renovacion = $db->row["MAXIMO"] + 1;
				}
				
				$db->Close();				
				
				$fecha_hora_renov = date_for_database_updates( $fecha_hora_renov );
				$fecha_renovacion = date_for_database_updates( $fecha_renovacion );
			
				$db->sql  = "INSERT INTO renovaciones ( ID_BIBLIOTECA, ID_RENOVACION, ID_PRESTAMO, ID_ITEM, FECHA_RENOVACION, NUEVA_FECHA_DEVOLUCION ) ";
				$db->sql .= " VALUES ( $id_biblioteca, $renovacion, $id_prestamo, $id_item, '$fecha_hora_renov', '$fecha_renovacion' );";

				// Un trigger colocará S en prestamos_det.RENOVACION_SN.
				$db->ExecSQL();
				
				$db->destroy();
				
				$error = 20;  // OK
				
				require_once("../actions.inc.php");
				agregar_actividad_de_usuario( CIRC_RENEWALS, "", $id_item );		
				
				ges_redirect( "circ_renovaciones_end.php?id_usuario=$id_usuario&id_prestamo=$id_prestamo&id_item=$id_item&id_renovacion=$renovacion" );				
			}
		} 
	}

	// Draw an html head
	include ("../basic/head_handler.php");	
	HeadHandler( $LBL_OPCION_RENOVACION, "../" );

?>
<script type='text/javascript' src='../calend/calend.js'></script>

<SCRIPT language="JavaScript">

	function newOne()
	{
		location.href = "gral_elegir_usuario.php?the_action=renovaciones";
	}

	function execute_renewal( id_prestamo, id_item, fecha_dev_programada, fecha_renovacion )
	{
		//alert( fecha_dev_programada );
		
		if( confirm("<?php echo $MSG_RENEWAL_CONFIRM;?>") )
		{
			document.agregar_form.action = "circ_renovaciones.php";
			document.agregar_form.the_action.value       = "save_renewal";
			document.agregar_form.id_prestamo.value      = id_prestamo;
			document.agregar_form.id_item.value          = id_item;
			document.agregar_form.fecha_original.value   = fecha_dev_programada;
			document.agregar_form.fecha_renovacion.value = fecha_renovacion;

			document.agregar_form.submit();
		}

	}
	
</SCRIPT>

<STYLE>

	#buttonarea { border: 1px solid red;  } 
	
	#nombre_usuario { 
		float: none;
		display: block; 
		position: relative;
		width: 45em; 
		border: 1px dotted green; 
		background: transparent;
		padding: 3px;
		overflow: auto;
	}	
	
	#caja_datos {
		float: none;
		width: 140%;
	}
	
</STYLE>

  <LINK href="../css/screen.css" type="text/css" rel="stylesheet">

<body id="home">
	
	<?php
		display_global_nav();  // barra de navegación superior
	?>

<div id="contenedor">
<?php 
		
	display_banner();  // banner
	display_menu('../'); // menu principal		
	
	//
	// VALIDAR USUARIO
	// 

	if( $id_usuario != 0 )
	{
		$user = new TUser( $id_biblioteca, $id_usuario );
		
		if( $user->NOT_FOUND )
		{
			SYNTAX_JavaScript( 1, 1, "alert( '$ALERT_WRONG_USER_NOT_FOUND' );" );
			
			echo "<br><div class=caja_errores>";
			echo " <strong> $ALERT_WRONG_USER_NOT_FOUND </strong>";
			echo "</div>";				
		}
		else
		{
			if( $user->STATUS == "A" )
			{
				$nombre_usuario = $user->NOMBRE_COMPLETO;					
				$grupo = $user->NOMBRE_GRUPO;
				
				$items_en_prestamo = $user->ObtenerNumItemsPrestados();
				$sanciones = $user->ObtenerNumSanciones();
				
				$max_renovaciones  = $user->GRUPO_MAX_RENOVACIONES;				
				$dias_renovacion   = $user->GRUPO_DIAS_RENOVACION_DEFAULT;
				$permite_renovacion_con_retraso   = $user->GRUPO_PERMITIRRENOVA_CON_RETRASO;
				$permite_renovacion_con_sanciones = $user->GRUPO_PERMITIRRENOVA_CON_SANCIONES;
				
				$imposible_por_sanciones = false;
				$imposible_por_restricciones = false;
				
				if( $max_renovaciones == 0 )
				{
					$error = 3; // Usuario no puede renovar
					$error_msg = $MSG_USER_CANT_RENEW;
					
				}
				else
				{
					if( $max_renovaciones > 0 and $dias_renovacion <= 0 )
					{
						$error = 3;
						$error_msg = $MSG_ERROR_ON_RENEWAL_CONFIG;
					}
				}
				
				// Codigo 4 = Verificar Restricciones
				if( $user->ObtenerNumRestricciones( 4 ) > 0 )
				{				
					$error = 5;   // El usuario tiene restricciones en vigor contra las renovaciones
					$error_msg = $MSG_USER_HAS_RESTRICTIONS;
					
					$imposible_por_restricciones = true;
					
					//SYNTAX_JavaScript( 1, 1, "alert( '$MST_USER_HAS_RESTRICTIONS' );" );
				}				
				// Verificar Sanciones incumplidas
				if( $permite_renovacion_con_sanciones == "N" )
				{
					if( $user->ObtenerNumSanciones() > 0 )
					{				
						$error = 6; // El usuario tiene sanciones incumplidas
						$error_msg .= $MSG_USER_HAS_SANCTIONS;
						
						$imposible_por_sanciones = true;
						
						//SYNTAX_JavaScript( 1, 1, "alert( '$MSG_USER_HAS_SANCTIONS' );" );
					}
				}

			}
			else 
			{
				echo "<br><div class=caja_info>";
				echo " <strong> $VALIDA_MSG_3 </strong>";
				echo "</div>";
			}
		}
		
		$user->destroy();
	}		

 ?>
 
  <div id="bloque_principal"> 
    <div id="contenido_principal">

	 <div class=caja_datos id=caja_datos>
	  <H2><?php echo $LBL_OPCION_RENOVACION; ?></H2>
      <HR>
		<h2><?php echo $LBL_TEXTO_RENOVACION; ?></h2>
	
				<?php 
				
					if( $error != 0 )
					{
						echo "<br><div class='caja_errores'>";
						echo " &nbsp;" . (( $imposible_por_restricciones or $imposible_por_sanciones ) ? "<img src='../images/icons/user_blocked.gif'>" : "" ) . "&nbsp;&nbsp;<strong>$error_msg</strong>";
						echo "</div>";
					}


				 ?>		
		 <br>
		 <form name="agregar_form" id="agregar_form" class="forma_captura" method='POST'>
			<input type="hidden" class="hidden" id="the_action" name="the_action" value="">
			<input type="hidden" class="hidden" id="id_usuario" name="id_usuario" value="<?php echo $id_usuario;?>">
	
			<label for="txt_id_usuario"><?php echo $LBL_IDUSUARIO_RENOVACION; ?></label>
          
			<!--valida si el boton a btnUsuario_id a sido activado-->
			<div id="nombre_usuario" name="nombre_usuario">
				<img src="../images/icons/user.gif">&nbsp;

				<?php 
					echo "<strong>" . $nombre_usuario . " </strong><br> $grupo "; 

					if( $items_en_prestamo > 0 )
					{
						echo "&nbsp;<br><img src='../images/icons/warning.gif'>&nbsp;";
						echo sprintf( $HINT_ITEMS_ALREADY_HAD, $items_en_prestamo );
					}
					if( $error <> 0 )
					{
						echo "&nbsp;<br><img src='../images/icons/warning.gif'>&nbsp;$error_msg";
					}
					
					$cur_datetime = current_dateandtime(1);
				?>

			</div>
		  
         <br>
		 
		<?php if( !$imposible_por_restricciones ) { ?>
		<dt>
          <label for="fecha_devolucion"><?php echo $LBL_FECHA_RENOVACION; ?></label>
		</dt>
		<dd><strong>
			<div style='font-size: 140%;'>
			  <?php echo $cur_datetime; ?>
			  <input type='hidden' class='hidden' value='<?php echo $cur_datetime;?>' id='fecha_hora_renov' name='fecha_hora_renov'>
			</div>
		  </strong>
		</dd>		  
		<br>
		<?php } ?>
		  

	          <?php
					if ($items_en_prestamo > 0)
					{
						$db->Open("SELECT a.ID_BIBLIOTECA, a.ID_PRESTAMO, a.ID_USUARIO, b.ID_ITEM, b.FECHA_DEVOLUCION_PROGRAMADA, b.RENOVACION_SN " .
								  "FROM prestamos_mst a " . 
								  "  LEFT JOIN prestamos_det b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PRESTAMO=a.ID_PRESTAMO) " .
								  "WHERE (a.ID_BIBLIOTECA=" . $id_biblioteca." and a.ID_USUARIO=$id_usuario) and (b.STATUS='P') " );

						echo "<table border=0 width='100%' bordercolor='#969696' >"; 
						echo "<tr bgcolor='#CCCCCC'>";
						echo "<td class='cuadricula columna columnaEncabezado' align='center' width=15%>$LBL_MATERIAL_COVER</td>";
						echo "<td class='cuadricula columna columnaEncabezado' align='center' width=55%>$LBL_MATERIAL_PRESTADO</td>";
						echo "<td class='cuadricula columna columnaEncabezado' align='center' width=25%>$LBL_FECHA_ENTREGA</td>";
						echo "</tr>";
						
						$TIMESTAMP_FechaHoraACTUAL = convert_humandate_to_unixstyle( $cur_datetime );
						
						while($db->NextRow())
						{								
							$id_prestamo=$db->row["ID_PRESTAMO"];

							$titulo = new TItem_Basic( $id_biblioteca, $db->row["ID_ITEM"], 1 );
							
							$renovaciones_anteriores = $titulo->VerificaNumeroRenovaciones( $id_prestamo );

							echo "<tr>";
							
							// portada
							echo "<td class='cuadricula columna' align='center' style='padding: 5px;'>";						
							if( $titulo->cCover != NULL )
							{
								echo "<img src='" . getsessionvar("http_base_dir") ."phps/image.php?id_biblioteca=$id_biblioteca&id_titulo=$titulo->nIDTitulo&tipoimagen=PORTADA' width='80'><br>";
							}
								
							echo "</td>";
							
							//
							//
							//  OBTENER FECHA DE DEVOLUCION PROGRAMADA
							//   CONSIDERANDO INCLUSO LAS RENOVACIONES
							//
							
							//
							$fecha_dev_prog = dbdate_to_human_format( $db->row["FECHA_DEVOLUCION_PROGRAMADA"], 1 );
							
							if( $db->row["RENOVACION_SN"] == "S" )
							{
								$array_renovaciones = $titulo->ObtenerHistorialRenovaciones( $db->row["ID_PRESTAMO"] );
								
								// con esto se obtendrá una FECHA_DEVOLUCION_PROGRAMADA ajustada según las renovaciones
								if( count($array_renovaciones) > 0 )
									for( $xyz=0; $xyz<count($array_renovaciones); $xyz++ )
									{
										$fecha_dev_prog = $array_renovaciones[$xyz]["nueva_fecha_devolucion"];
									}
							}
							else
								$array_renovaciones = Array(); // por default								
							
							$flag_posible = true;
							
							// info del item
							echo "<td class='cuadricula columna'>";
							
							// primero manda los posibles mensajes de error
							
							$item_retrasado = false;
							
							// 
							// INICIO VERIFICA RETRASO
							//
							$TIMESTAMP_FechaHoraDEVPROGRAMADA = convert_humandate_to_unixstyle( $fecha_dev_prog );
							// verificar fecha-dev-programada vs. fecha-hora actual
							if( $TIMESTAMP_FechaHoraACTUAL > $TIMESTAMP_FechaHoraDEVPROGRAMADA )
							{
								$item_retrasado = true;
								
								echo "<div class='caja_errores'>\n";
								echo "<img src='../images/icons/warning.gif'>&nbsp;$MSG_ITEM_DELAYED ";
								echo "</div>\n";
								
								if( $permite_renovacion_con_retraso == "N" and $max_renovaciones > 0 )
								{
									$flag_posible = false;
									echo "<div class='caja_errores'>\n";
									echo "<img src='../images/icons/warning.gif'>&nbsp;$MSG_IMPOSIBLE_RENEWAL_ONDUE";
									echo "</div>\n";
								}
							}
							//
							// FIN 
							// 								
															
							if( $imposible_por_sanciones == true or $imposible_por_restricciones == true)
							{
								// SI Existe IMPOSIBILIDAD por sanciones o por RESTRICCIONES
								$flag_posible = false;
								
								echo "<div class='caja_errores'>\n";
								echo "<img src='../images/icons/warning.gif'>&nbsp;$HINT_IMPOSSIBLE_RENEWAL.";
								
								if( $imposible_por_sanciones )
								{
									echo "&nbsp;$MSG_USER_HAS_SANCTIONS";
								}
								if( $imposible_por_restricciones )
								{
									echo "&nbsp;$MSG_USER_HAS_RESTRICTIONS";
								}																	
								echo "</div>";
							}
							
							//
							//	DESPLEGAR INFORMACION 
							//	DEL ITEM
							//
							echo "<strong><i>$titulo->item_id_material</i></strong><br>";
							echo "<strong>$titulo->cTitle</strong><br>";					
							echo $titulo->cAutor . "<br>";
							echo "$LBL_DEV_DUEDATE " . dbdate_to_human_format( $db->row["FECHA_DEVOLUCION_PROGRAMADA"], 1, 0, 0 ) . "<br>";																	
								
							if( count($array_renovaciones) > 0 )
							{
								// desplegar renovaciones
								for( $xyz=0; $xyz<count($array_renovaciones); $xyz++ )
								{
									echo $LBL_RENEWED . " " . ($xyz+1) . ": " . $array_renovaciones[$xyz]["nueva_fecha_devolucion"];
								}
							}
							
							echo "</td>";	//depliege de titulo
								
							echo "<td class='columna cuadricula' align='center'>";
								
							if( $max_renovaciones == 0 )
							{
								$flag_posible = false;
								
								echo "<br><div class='caja_errores'>";
								echo " <strong>$MSG_USER_CANT_RENEW</strong>";
								echo "</div><br>";
							}
							else
							{
								$renovaciones_anteriores = count($array_renovaciones);
								
								if( $renovaciones_anteriores >= $max_renovaciones )
								{
									echo "<br><div class='caja_errores'>";
									echo " <strong>" . sprintf($MSG_IMPOSIBLE_RENEWAL_2, $renovaciones_anteriores) . "</strong>";
									echo "</div><br>";										
									
									$flag_posible = false;
								}
							}

							unset( $array_renovaciones );

							if( $flag_posible )
							{
								// SI SE PUEDE RENOVAR
								//
								$fecha_dev = $fecha_dev_prog;
								
								// 
								if( $item_retrasado )
								{
									$fecha_dev = $cur_datetime;
								}

								$fecha_max_devol = sum_days( $fecha_dev, $dias_renovacion, 1 );
								
								echo "$LBL_CHOOSE_A_DATE<br>";
								
								$fechas_disponibles = Array();

								$te = $titulo->VerificarDisponibilidad_X_ITEM( $db->row["ID_ITEM"], 
									    "N", $fecha_dev, $fecha_max_devol, $fechas_disponibles, 5, 1, 0, 0, $id_usuario );
																
								for( $i=1; $i <= $dias_renovacion; $i++ )
								{										
									$new_fecha_devol = sum_days( $fecha_dev, $i, 0 );
									
									$item_available = 0;
									$tipo_bloqueo = 0;
									
									for( $ij=0; $ij<count($fechas_disponibles); $ij++ )
									{
										if( $fechas_disponibles[$ij][1] == $new_fecha_devol ) 
											$item_available = 1;
									}	

									$new_fecha_devol_tmp = sum_days( $fecha_dev, $i, 1 );
									
									if( $item_available == 0 )
									{
										// PDTE: Colocar el status exacto, PRESTAMO o RESERVA
										//       $MSG_BUSY_ITEM da un mensaje genérico 
										$descrip_error = "$MSG_BUSY_ITEM";
										
										echo "<div style='display:inline; position: relative; top: 3px;'><img src='../images/icons/no.png'>&nbsp;</div>" . 
											 "<div style='display:inline; border-bottom:1px dotted black'>$i - " . $new_fecha_devol_tmp . "<img src='../images/icons/warning.gif' title='$descrip_error'></div><br>"; 
									}
									else
										echo "<div style='display:inline; position: relative; top: 3px;'><img src='../images/icons/yes.png'>$i&nbsp;-</div>" . 
											 "<input class='boton' type='button' name='check$i' onclick='javascript:execute_renewal($id_prestamo," . $db->row["ID_ITEM"] . ", \"$fecha_dev\", \"$new_fecha_devol_tmp\")' value='$new_fecha_devol_tmp'><br>";
								}
								
								if( count($fechas_disponibles) == 0 )
								{
									echo "<br><div class='caja_errores'>";
									echo " <strong>$MSG_IMPOSIBLE_RENEWAL_3</strong>";
									echo "</div>";
								}

								unset( $fechas_disponibles );
							}
							else
								echo "<img src='../images/icons/warning.gif'>&nbsp;$HINT_IMPOSSIBLE_RENEWAL";

							echo "</td>";
							echo "</tr>";
							
							$titulo->destroy();
						} // fin de WHILE	
						
						echo "</table>";
					}
					else
					{
						echo "<br><div class='caja_info' style='left:15em; width: 50%;' >";
						echo " <strong>$MSG_NO_LOANS</strong>";
						echo "</div>";
					} // fin IF $records
					
			  ?>

				<input class="hidden" type="hidden" name="id_prestamo" id="id_prestamo" value="">
				<input class="hidden" type="hidden" name="id_item" id="id_item"  value="">
				<input class="hidden" type="hidden" name="fecha_original" id="fecha_original" value="">
				<input class="hidden" type="hidden" name="fecha_renovacion" id="fecha_renovacion" value="">

				<?php
					if( $error != 0 )
					{
						echo "<br>";

						echo "<div style='margin-left: 13em;'>";
						echo " <div style='float:left'>";
						echo "   <input type='button' class='boton' value='$BTN_CHANGE_USER' name='btnNewOne' id='btnNewOne' onClick='javascript:newOne();'>";
						echo " </div>";
						echo "</div>";
						
						echo "<br>";
					}
					
				?>		

				<br>				
			  
			 </form>		  


	 </div> <!-- caja_datos -->
  </div>  <!-- contenido principal -->
  <?php  display_copyright(); ?>
</div>  <!-- Bloque principal -->
</body>

</html>
