<?php
	session_start();

	/**********
		
		Archivo PHP de la aplicación BiblioTEK 
		que finaliza el registro de una sanción
		
		Historial de cambios:
	
		03-sep-2009:  Se crea el archivo para concluir la función
		23 oct 2009:  Se agrega $db en process_email

	**/
 
	include ( "../funcs.inc.php" );
	include ( "../basic/bd.class.php" );
	
	include_language( "global_menus" );
	include_language( "serv_sanciones_cump" ); // archivo de idioma

	check_usuario_firmado(); 
	
	$id_biblioteca = getsessionvar('id_biblioteca');
	$id_usuario = read_param( "id_usuario", 0, 1 ); // fail if not exist
	$id_sancion = read_param( "id_sancion", 0 ); // fail if not exist

	// Draw an html head
	include ("../basic/head_handler.php");
	HeadHandler( "$LBL_HEADER_COMPLETED", "../" );
	
?>

<SCRIPT type='text/javascript' language='JavaScript'>

	function nuevoCumplimiento()
	{
		frames.location.href = "gral_elegir_usuario.php?the_action=sanciones_cumplidas";
	}
	
</SCRIPT>

<STYLE>

	#buttonarea { left: 165px;  } 
	
	#nombre_usuario 
	{ 
		display: inline; 
		position: absolute;
		width: 40em; 
		border: 1px dotted green; 
		/*background: #FFF; */
	}
	
	#caja_datos {
		width: 130%; 
		height: 100%;
	}	
	
	#sanction_hint
	{
		display: inline;
		position: absolute;
		font-size: 110%;
		font-weight: bold;
		left: 11em;
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
				
				$user->destroy();
			}			
			
		?>

		<div id="bloque_principal"> 
		
			<div id="contenido_principal"> <br>
				<div class="caja_datos" id="caja_datos"> 
					<H2><?php echo $LBL_HEADER_COMPLETED; ?> </H2>
					<br>

					<form name="agregar_form" id="agregar_form" class="forma_captura">

						<label><?php echo $LBL_IDUSUARIO; ?></label>
						
						<div id="nombre_usuario" name="nombre_usuario">							
							<div style='float:left'><img src="../images/icons/user.gif">&nbsp;<?php echo "$nombre_usuario<br> $grupo" ; ?>&nbsp;</div>
						</div>
						
						<br style='clear:both;'>
						<br><br>
						
						<div id='sanction_hint'>
							<?php echo sprintf( $MSG_SANCTIONS_ACOMP_COMPLETED_HINT, $id_sancion, $sanciones ); ?>
						
						<br>
						
							<?php
								//require_once( "email_factory.inc.php" );		

								echo "<label>&nbsp;</label>";

							/**	if( process_exmail( $user->db, $id_biblioteca, $id_usuario, EMAIL_SANCTIONS_WAS_ACOMPLISHED, $id_sancion, 0, null, $user ) )
								{
									global $MSG_EMAIL_WAS_SENT;
									include_language( "email", "../" );
									echo "<br>";
									echo "<img src='../images/icons/email.gif'>&nbsp;" . sprintf( "$MSG_EMAIL_WAS_SENT", $user->EMAIL );
								}*/

								$user->destroy();
							 ?>		

						</div>						 

						<br><br>
						
						<br style='clear:both'>
						<br><br><br>
						
						<div id='buttonarea'>
							<input type='button' class='boton' value='<?php echo $BTN_NEW_SANCTION;?>' name='btnCreateNew' id='btnCreateNew' onClick='javascript:nuevoCumplimiento();'>
						</div>

						<br style='clear:both;'><br>
						
					</form>
				</div><!-- caja_datos -->
			</div><!-- Contenido principal -->
			<?php  display_copyright();	?>    
		</div><!-- bloque principal -->
	</div>  <!-- contenedor principal -->

</BODY>

</html>
