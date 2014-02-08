<?php
	session_start();

	/**********
		
		Archivo PHP de la aplicación BiblioTEK 
		que finaliza las devoluciones de items
		
		Historial de cambios:
	
		25-ago-2009:	Se crea el archivo para concluir la función de devolción

	**/
 
	include ( "../funcs.inc.php" );
	include ( "../basic/bd.class.php" );
	
	include_language( "global_menus" );
	include_language( "circ_devoluciones" );

	check_usuario_firmado(); 

	$nombre_usuario = "";
	$grupo = "";
	
	$items_en_prestamo = 0;
	$sanciones = 0;
	
	$id_biblioteca = getsessionvar( "id_biblioteca" );
	$id_usuario = read_param( "id_usuario", 0, 1 ); // fail if not exist
	
	$quick = read_param( "quick", 0 );

	// Draw an html head
	include ("../basic/head_handler.php");
	HeadHandler( $LBL_HEADER, "../" );
	
?>

<SCRIPT type='text/javascript' language='JavaScript'>

	function nuevaDevolucion()
	{
		var url = "gral_elegir_usuario.php?the_action=devoluciones";
		
		<?php 
		
		  if( $quick == 1 )
				echo "url = 'gral_elegir_item.php?the_action=devoluciones';";
		
		?>

		frames.location.href = url;
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
	
	#info_hint
	{
		display: block;
		font-size: 120%;
		font-weight: bold;
		position: relative;		
		left: 11em;
		width: 500px;
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
					<H2><?php echo $HINT_DEV_COMPLETED; ?> </H2>
					<br>

					<form name="agregar_form" id="agregar_form" class="forma_captura">

						<label><strong><?php echo $LBL_USER; ?></strong></label>
						
						<div id="nombre_usuario" name="nombre_usuario">
							
							<div style='float:left'><img src="../images/icons/user.gif">&nbsp;<?php echo "$nombre_usuario  <br> $grupo" ; ?>&nbsp;</div>
						</div>
						
						<br style='clear:both;'>
						<br><br>
						
						<div id='info_hint'>
							<?php echo $MSG_DEV_SAVED; ?>
							<br><br>
							<?php 
								if( $items_en_prestamo == 0 )
									echo $HINT_NO_LOANS_FOR_USER;
								else
									echo sprintf( $HINT_DISPLAY_LOANS_FOR_USER, $items_en_prestamo ); 
									
								if( $sanciones > 0 )
									echo "<br>" . sprintf( $HINT_DISPLAY_SANCTIONS_FOR_USER, $sanciones ); 
									
							?>
						</div>
						
						<br>
						
						<div id='buttonarea'>
							<input type='button' class='boton' value='<?php echo $BTN_NEW_DEV;?>' name='btnCreateNew' id='btnCreateNew' onClick='javascript:nuevaDevolucion();'>
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

</html>
