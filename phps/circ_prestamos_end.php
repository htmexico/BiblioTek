<?php
	session_start();

	/**********
		
		Archivo PHP de la aplicación BiblioTEK 
		que finaliza una acción de préstamo 
		
		Historial de cambios:
	
		07-ago-2009:	Se crea el archivo para concluir la función de préstamos.

		PENDIENTE:  Enviar email		

	**/
 
	include ( "../funcs.inc.php" );
	include ( "../basic/bd.class.php" );
	
	include_language( "global_menus" );
	include_language( "circ_prestamos" );

	check_usuario_firmado(); 

	$db = new DB();
	
	$grupo		    = "";
	$nombre_usuario	= "";
	$paterno	= "";
	$materno	= "";
	
	$bandera_total				=0;
	$bandera_reservado			=1;
	$max_dias					=0;
	$max_items					=0;
	$fechareservacion			=0;
	$fechadevolucion			=0;
	$val_reservacion			=1;
	
	$id_biblioteca = getsessionvar('id_biblioteca');
	$id_usuario = read_param( "id_usuario", 1, 1 ); // fail if not exist
	$id_prestamo = read_param( "id_prestamo", 0, 1 ); // fail if not exist

	// Draw an html head
	include ("../basic/head_handler.php");
	HeadHandler( "$LBL_LOAN_HEADER", "../" );
	
?>

<SCRIPT type='text/javascript' language='JavaScript'>

	function newLoan()
	{
		frames.location.href = "gral_elegir_usuario.php?the_action=prestamos";
	}
	
</SCRIPT>

<STYLE>

	#buttonarea { left: 165px;  } 
	
	#nombre_usuario 
	{ 
		display: inline; 
		position: absolute;
		/*left: 32em; */
		width: 40em; 
		border: 1px dotted green; 
		/*background: #FFF; */
	}
	
	#caja_datos {
		width: 130%; 
		height: 100%;
	}	
	
	#reserva_hint
	{
		font-size: 120%;
		font-weight: bold;
	}

</STYLE>

<BODY id="home">
	
	<?php
		display_global_nav();  // barra de navegación superior
	?>

	
	<div id="contenedor" class="contenedor"> 

		<?php 
			display_banner();  // banner
			display_menu( "../" ); // menu principal

			if( $id_usuario != 0 )
			{
				include( "circulacion.inc.php" );
				$user = new TUser( $id_biblioteca, $id_usuario, $db );
				
				if( $user->NOT_FOUND )
				{
					SYNTAX_JavaScript( 1, 1, "alert( '$ALERT_WRONG_USER_NOT_FOUND' );" );
					
					echo "<br><div class=caja_errores>";
					echo " <strong> $ALERT_WRONG_USER_NOT_FOUND </strong>";
					echo "</div>";				
				}
				else
				{
					$nombre_usuario = $user->NOMBRE_COMPLETO;					
					$grupo = $user->NOMBRE_GRUPO;

					//$items_en_prestamo = $user->ObtenerNumItemsPrestados();
					//$sanciones = $user->ObtenerNumSanciones();
				}

				$user->destroy();
			}
			
		?>

		<div id="bloque_principal"> 
		
			<div id="contenido_principal"> <br>
				<div class="caja_datos" id="caja_datos"> 
					<H2><?php echo $MSG_LOAN_COMPLETED; ?> </H2>
					<br>

					<form name="agregar_form" id="agregar_form" class="forma_captura">

						<label><?php echo $LBL_ID_USER; ?></label>
						
						<div id="nombre_usuario" name="nombre_usuario">
							
							<div style='float:left'><img src="../images/icons/user.gif">&nbsp;<?php echo $nombre_usuario . "<br> $grupo" ; ?>&nbsp;</div>
						</div>
						
						<br style='clear:both;'>
						<br><br>
						
						<label>&nbsp;</label>
						
						<?php
							// ITEMS por préstamo
							$items_checkedout = 0;
							
							$db->Open( "SELECT COUNT(*) AS CUANTOS " .
									   "FROM prestamos_det a ".
									   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_PRESTAMO=$id_prestamo" );
							
							if( $db->NextRow())		
							{
								$items_checkedout = $db->row["CUANTOS"];
							}							
				
							$db->FreeResultset();
						  ?>

						<div id='reserva_hint'>
							<?php echo sprintf( $MSG_LOAN_COMPLETED_HINT, $items_checkedout, $id_prestamo ); ?>
						</div>
						
						<br>
						
						<div id='buttonarea'>
							<input type='button' class='boton' value='<?php echo $BTN_NEW_LOAN;?>' name='btnCreateNew' id='btnCreateNew' onClick='javascript:newLoan();'>
						</div>

						<br>
						<br style='clear:both;'>
						
					</form>
				</div><!-- caja_datos -->
			</div><!-- Contenido principal -->
			<?php  display_copyright();	?>    
		</div><!-- bloque principal -->
	</div>  <!-- contenedor principal -->

</BODY>

<?php
  $db->destroy();
  ?>

</html>
