<?php
	session_start();

	/**********
		
		Archivo PHP de la aplicación BiblioTEK 
		que finaliza una acción de préstamo 
		
		Historial de cambios:
	
		27-ago-2009:	Se crea el archivo para concluir la función de renovación.
		23-oct-2009:	Se ajusta $db para process_exmail
		27 oct 2009:  process_email se eliminar y se deja para el cron
		
	**/
 
	include ( "../funcs.inc.php" );
	include ( "../basic/bd.class.php" );
	
	include ( "circulacion.inc.php" );
	
	include_language( "global_menus" );
	include_language( "circ_renovaciones" );

	check_usuario_firmado(); 

	$db = new DB();

	$id_biblioteca = getsessionvar('id_biblioteca');

	$id_usuario  = read_param( "id_usuario", 0, 1 ); // fail if not exist
	$id_prestamo = read_param( "id_prestamo", 0, 1 ); // fail if not exist
	$id_item     = read_param( "id_item", 0, 1 ); // fail if not exist
	$id_renovacion = read_param( "id_renovacion", 0, 1 ); // fail if not exist

	// Draw an html head
	include ("../basic/head_handler.php");
	HeadHandler( "$LBL_OPCION_RENOVACION", "../" );
	
	$nombre_usuario		= "";
	$grupo			    = "";
	$items_en_prestamo  = 0;	
	
?>

<SCRIPT type='text/javascript' language='JavaScript'>

	function newLoan()
	{
		frames.location.href = "gral_elegir_usuario.php?the_action=renovaciones";
	}
	
</SCRIPT>

<STYLE>

	#buttonarea { left: 165px;  } 
	
	#nombre_usuario 
	{ 
		float: none;
		display: block; 
		position: absolute;
		left: 13em;
		width: 40em; 
		border: 1px dotted green; 
		overflow: auto;
	}
	
	#descrip_item 
	{ 
		display: inline; 
		position: absolute;
		left:  13em;
		width: 40em; 		
		overflow: auto;
		border: 1px dotted green; 
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
				$items_en_prestamo = $user->ObtenerNumItemsPrestados();
				$sanciones = $user->ObtenerNumSanciones();
			}			
			
		?>

		<div id="bloque_principal"> 
		
			<div id="contenido_principal"> <br>
				<div class="caja_datos" id="caja_datos"> 
					<H2><?php echo $MSG_RENEWAL_COMPLETED; ?> </H2>
					<br>

					<form name="agregar_form" id="agregar_form" class="forma_captura">

						<label><strong><?php echo $LBL_IDUSUARIO_RENOVACION; ?></strong></label>
						
						<div id="nombre_usuario" name="nombre_usuario">
							<div style='float:left'><img src="../images/icons/user.gif">&nbsp;<?php echo $user->NOMBRE_COMPLETO . "<br> $user->NOMBRE_GRUPO" ; ?>&nbsp;</div>
						</div>
						
						<br style='clear:both;'>
						<br><br>
						
						<label><strong><?php echo $LBL_MATERIAL_PRESTADO;?></strong></label>
						
						<div id="descrip_item" name="descrip_item">
						
						<?php
						
						  $titulo = new TItem_Basic( $id_biblioteca, $id_item, 1, $db );
						  
						  echo "<img src='../" . $titulo->cIcon . "'> [" . $titulo->Material_ShortCode() . "]&nbsp;" . $titulo->item_id_material . "<br>";
						  echo $titulo->cTitle . "<br>";
						  
						  $fecha_devolucion = get_str_datetime( $titulo->ObtenerFechaDevolucion( $id_prestamo ), 1, 0 );
						  
						 ?>
						 
						 </div>
						 
						 <br style='clear:both;'>
						 <br><br>

						 <label>&nbsp;</label>
						 <div id='reserva_hint'>
							<?php echo sprintf( $MSG_NEW_DEUDATE, $fecha_devolucion ); ?>
						 </div>
						
						<br>
						
						<?php
						
							require_once( "email_factory.inc.php" );		
							
							echo "<label>&nbsp;</label>";							

							// process_email ajustado param. $user
				/***			if( process_xmail( $db, $id_biblioteca, $id_usuario, EMAIL_RENEWALS, $id_prestamo, $id_item, null, $user ) )
							{
								global $MSG_EMAIL_WAS_SENT;
								include_language( "email", "../" );
								echo "<img src='../images/icons/email.gif'>&nbsp;" . sprintf( "$MSG_EMAIL_WAS_SENT", $user->EMAIL );
							}  **/
							
							echo "<br><br>";
						  
						  $user->destroy();	
						  $titulo->destroy();
						
						 ?>
						
						<div id='buttonarea'>
							<input type='button' class='boton' value='<?php echo $BTN_NEW_RENEWAL;?>' name='btnCreateNew' id='btnCreateNew' onClick='javascript:newLoan();'>
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
