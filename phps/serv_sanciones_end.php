<?php
	session_start();

	/**********
		
		Archivo PHP de la aplicación BiblioTEK 
		que finaliza el registro de una sanción
		
		Historial de cambios:
	
		03-sep-2009:  Se crea el archivo para concluir la función
		23 oct 2009:  Se agrega $db en process_xemail
		27 oct 2009:  process_exmail se eliminar y se deja para el cron

	**/
 
	include ( "../funcs.inc.php" );
	include ( "../basic/bd.class.php" );
	
	include_language( "global_menus" );
	include_language( "serv_sanciones" ); // archivo de idioma

	check_usuario_firmado(); 
	
	$id_biblioteca = getsessionvar('id_biblioteca');
	$id_usuario = read_param( "id_usuario", 0, 1 ); // fail if not exist
	$id_sancion = read_param( "id_sancion", 0 ); // fail if not exist

	// Draw an html head
	include ("../basic/head_handler.php");
	HeadHandler( "$LBL_HEADER_COMPLETED", "../" );
	
?>

<SCRIPT type='text/javascript' language='JavaScript'>

	function nuevaSancion()
	{
		frames.location.href = "gral_elegir_usuario.php?the_action=sanciones";
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

			require_once( "circulacion.inc.php" );
			$user = new TUser( $id_biblioteca, $id_usuario );
			
			if( $user->NOT_FOUND )
			{
				SYNTAX_JavaScript( 1, 1, "alert( '$ALERT_WRONG_USER_NOT_FOUND' );" );
			}
			else
			{
				$nombre_usuario = $user->NOMBRE_COMPLETO;					
				$grupo = $user->NOMBRE_GRUPO;
				
				$items_en_prestamo = $user->ObtenerNumItemsPrestados();
				$sanciones = $user->ObtenerNumSanciones();
			}			
			
		?>

		<div id="bloque_principal"> 
		
			<div id="contenido_principal"> <br>
				<div class="caja_datos" id="caja_datos"> 
					<H2><?php echo $LBL_HEADER_COMPLETED; ?> </H2>
					<br>

					<form name="agregar_form" id="agregar_form" class="forma_captura">

						<label><?php echo $LBL_USUARIO; ?></label>
						
						<div id="nombre_usuario" name="nombre_usuario">
							<img src="../images/icons/user.gif">&nbsp;
							<div style='float:left'><?php echo "$nombre_usuario<br> $grupo" ; ?>&nbsp;</div>
						</div>
						
						<br style='clear:both;'>
						<br><br>
						
						<label>&nbsp;</label>
						
						<div id='reserva_hint'>
							<?php echo sprintf( $MSG_SANCTIONS_COMPLETED_HINT, $id_sancion, $sanciones ); ?>
						</div>
						
						<br>
						
						<?php
						
							//require_once( "email_factory.inc.php" );		
							
							echo "<label>&nbsp;</label>";

							// process_email ajustado param. $user
							/**if( process_xmail( $user->db, $id_biblioteca, $id_usuario, EMAIL_SANCTIONS, $id_sancion, 0, null, $user ) )
							{
								global $MSG_EMAIL_WAS_SENT;
								include_language( "email", "../" );
								echo sprintf( "$MSG_EMAIL_WAS_SENT", $user->EMAIL );																
							} **/
							
							echo "<br><br>";

							$user->destroy();
						 ?>						
						
						<div id='buttonarea'>
							<input type='button' class='boton' value='<?php echo $BTN_NEW_SANCTION;?>' name='btnCreateNew' id='btnCreateNew' onClick='javascript:nuevaSancion();'>
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
  //$db->destroy();
  ?>

</html>
