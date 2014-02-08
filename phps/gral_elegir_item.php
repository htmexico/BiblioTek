<?php
	session_start();

	/**********
		
		HISTORIAL DE CAMBIOS
		
		25-Agosto-2009:	Se crea el archivo genérico para buscar items x ID material (CODIGO DE BARRAS, INVENTARIO, etC)
		14-oct-2009:	Se modifica para que este mismo archivo pueda fungir con ambas conductas
						seleccionar ID_MATERIAL y seleeccionar ID_TITULO
		15-oct-2009:	Se agrega la posiblidad de solicitar copias aún en préstamo (SOBRE TODO para devoluciones)
		
	**********/		

	include ("../funcs.inc.php");
	include ("../basic/bd.class.php");
	include ("circulacion.inc.php" );
	include( "../privilegios.inc.php" );
	
	include_language( "global_menus" );
	
	check_usuario_firmado(); 

	include_language( "gral_elegir_item" ); // archivo de idioma

	include ( "../basic/head_handler.php" );  // Coloca un encabezado HTML <head>
	HeadHandler( $LBL_HEADER, "../" );

	$buscaritem 		= read_param( "buscaritem", 0 );
	$txt_id_material 	= read_param( "txt_id_material", "" );
	
	$the_action		    = read_param( "the_action", "", 1 );  // fail if not exist
	$descrip_action	    = "";
	$target_url		    = "";
	$hint			    = "";
	
	// datos del MATERIAL ya ubicado
	$nombre_titulo = "";
	$nombre_autor  = "";
	$icon_tipo_material = "";
	$item_id	   = 0;
	$id_titulo     = 0;
	
	$portitulo	   = 0;
	$mostrar_incluso_items_prestados = 0;
	
	if( $the_action == "devoluciones" )
	{
		$descrip_action = $HINT_FOR_ACTION_DEVS;
		$target_url	    = "circ_devoluciones.php";
		$hint			= $HINT_FOR_ENTERING_ITEMID_4_DEVS;
		$portitulo     = 0;  // 14-oct
		$mostrar_incluso_items_prestados = 1;
		
		verificar_privilegio( PRIV_DEVOLUTIONS, 1 );
	}
	else if( $the_action == "existencias" )
	{
		$descrip_action = $HINT_FOR_ACTION_EXIST;
		$target_url	    = "anls_existencias_paso2.php";
		$hint			= $HINT_FOR_ENTERING_IDTITLE_4_EXIST;
		$portitulo     = 1;  // 14-oct
		
		verificar_privilegio( PRIV_EXISTENCES, 1 );
	}		
	else if( $the_action == "tracking" )
	{
		$descrip_action = $HINT_FOR_ACTION_TRACKING;
		$target_url	    = "circ_rastreo.php";
		$hint			= $HINT_FOR_ENTERING_ITEMID_4_TRACKING;
		$portitulo     = 1; // 14-oct
		
		verificar_privilegio( PRIV_TRACKING, 1 );
	}	
	else
		die( "ACCION NO PREVISTA" );

	$usuario = getsessionvar('usuario');
	$id_biblioteca =getsessionvar('id_biblioteca');

	$ok_titulo = 0;
	
?>

<script type='text/javascript' src='../basic/calend.js'></script>

<SCRIPT type="text/javascript" language="JavaScript">

	function validarTitulo() 
	{
		var url;
		
		if( document.elegir_form.txt_id_material.value=='')
		{
		    alert( "<?php echo $VALIDA_MSG_IDITEM;?>" );
			document.elegir_form.txt_id_material.focus();
			
			return false;
		}
		else
		{
			document.elegir_form.buscaritem.value = "1";
			return true;
		}
	}
	
	function elegirTitulo()
	{
		var nwidth = screen.width;
		var nheight = screen.height; 
		
		window.open( "gral_buscartitulo.php<?php echo ($portitulo==1) ? '' : '?ver_copias=1';?><?php echo ($mostrar_incluso_items_prestados==1) ? '&elegir_prestados=1' : '';?>", "usuarios", "WIDTH=" + (nwidth-200) + ",HEIGHT=" + (nheight-330) + ",TOP=50,LEFT=80,resizable=yes,scrollbars=yes,status=yes" );
		window.status='';
	}
	
	function inicializa_valores_default()
	{	
		prepareInputsForHints();
		
		if( <?php echo ( $buscaritem == 1 ) ? 0 : 1; ?>)
		{
			document.elegir_form.txt_id_material.focus();
		}
	}	

	// Continuar seleccionando un usuario
	function Continuar( val )
	{	
		var error = 0;
		var url = "";
		
		if( validarTitulo() )
		{		
			// CONTINUAR HACIA LA SIGUIENTE 
			<?php 
			
			echo "url += \"" . $target_url . "?\";\n";
			
			if( $portitulo==1) 
			{
				echo "url += \"id_titulo=\"";
			}
			else
			{
				echo "url += \"id_item=\"";
			}
			
			?>
			
			if( val == 0 )
			{
				// buscar el codigo especial 
				val = js_getElementByName_Value( "selected_value" );
				
				if( val == "" )
					val = document.elegir_form.txt_id_material.value;  // ultima opción (p.e. ID_TITULO)
			}
			
			frames.location.href = url + val ;
			
			return true;
		}
		else 
			return false;
	}

	function key_pressed(myfield,e)
	{
		var keycode;
	
		if (window.event) keycode = window.event.keyCode;
		else if (e) keycode = e.which;
		else return true;
	
		if (keycode == 13)
		{
			//validarUsuario();
			
			return retval;
		}
		else
		{
			return true;
		}
	}
	
	function local_blockNonNumbers(obj, e, allowDecimal, allowNegative)
	{
		var keycode;
	
		if (window.event) keycode = window.event.keyCode;
		else if (e) keycode = e.which;
		else return true;
		
		if( keycode == 13 )
			return true;
		else
			return blockNonNumbers(obj, e, allowDecimal, allowNegative)
	}

</SCRIPT>

<STYLE>

	.sp_hint { width: 300px; }
	
	#lbl_container { 
		position: relative;
		display: block;
		width: 45em; 
		height: 40px;
		border: 1px dotted brown; 
		background: transparent;
		margin-top:5px; 
		margin-bottom:5px; 
		padding: 4px;
		left: 13em;
		min-height: 70px;
	}	
	
	#caja_datos {
		width: 120%; 
	}
	
	

</STYLE>

<body id="home" onLoad='javascript:inicializa_valores_default();'>

<?php 
  display_global_nav();  // barra de navegación superior
 ?>

<!-- contenedor principal -->
<div id="contenedor" class="contenedor">

<?php
	display_banner();      // banner   
	display_menu( "../" ); // menu principal
?>  
 
	<div id="bloque_principal">
        
		<div id="contenido_principal">
	        
			<div class="caja_datos" id="caja_datos"> 
				<h2><?php echo (($portitulo==1) ? $LBL_SUBTITLE_V2 : $LBL_SUBTITLE_V1) . " " . $descrip_action; ?></h2>
				<hr>

				<h4><?php echo ($portitulo==1) ? $LBL_CAPTION_V2 : $LBL_CAPTION_V1; ?></h4><br>
					<div id='caja_info' name='caja_info' style='display:none' >
						<strong>&nbsp;</strong>
					</div>
				
					<?php 

						$grupo = "";
						 
						if( $buscaritem == 1 )
						{
							if( $portitulo == 1 )
								$id = 0;
							else
								$id = 2;

							$item = new TItem_Basic( $id_biblioteca, $txt_id_material, $id );

							if( !$item->NOT_FOUND )
							{
								// SI SE ENCONTRÓ
								$ok_titulo = 1;
								
								$nombre_titulo = $item->cTitle;
								$nombre_autor  = $item->cAutor;
								$icon_tipo_material = $item->cIcon;
								$item_id	    = $item->nIDItem;
								$id_titulo		= $item->nIDTitulo;
							}
							else
							{
								echo "<div class=caja_errores>";
								echo " <strong> " . sprintf($VALIDA_MSG_NOT_FOUND, $txt_id_material) . "</strong>";
								echo "</div><br>";
								
								$buscarusuario	=1;
							}

							$item->destroy();

						}
						else
							$buscarusuario=1;		
					   
					 ?>
            
				<form name="elegir_form" id="elegir_form" class="forma_captura" method='post' onSubmit='return validarTitulo();'>
				<input type="hidden" class="hidden" name="the_action" id="the_action" value="<?php echo $the_action;?>">
				<input type="hidden" class="hidden" name="buscaritem" id="buscaritem" value="">
				
				<input type="hidden" class="hidden" name="selected_value" id="selected_value" value="">
				
				<dt>
					<label for="txt_id_usuario"><?php echo ($portitulo==1) ? $LBL_IDTITULO : $LBL_IDITEM; ?></label>
				</dt>
				<dd>				
					<input class="campo_captura" type="text" name="txt_id_material" id="txt_id_material" value="<?php echo $txt_id_material;?>"
  					        <?php
								if( $portitulo==1 )
									echo "onblur=\"extractNumber(this,0,false);\" onkeypress=\"return local_blockNonNumbers(this, event, false, false);\" size='20' "; 
								else
									echo "onkeypress=\"return key_pressed(this,event);\" size='40' ";
							?>
							 style='display:inline;'>
					<input class="boton" type="button" value="?" name="btnUsuario" id="btnUsuario" onClick="javascript:elegirTitulo();">
					<span class="sp_hint"><?php echo $hint;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<?php 

					if( $ok_titulo == 1 )
						echo "<div id='lbl_container' style='visibility:visible;'>";
					else
						echo "<div  id='lbl_container' style='visibility:hidden; display:none;'>";
					
					echo "<div id='lbl_id_title' name='lbl_id_title' style='display:inline;'>";
					
					if( $ok_titulo == 1 )
					{
						echo "<img src='../$icon_tipo_material'>&nbsp;$nombre_titulo<br>$nombre_autor&nbsp;";
					}
					echo "</div>";

						echo "<div style='display:inline; position: relative; top: 5px; margin: 5px; padding: 3px;'>";
						
						if( $portitulo == 1 )
							echo "<input class='boton' type='button' align='center' onClick='javascript:Continuar($id_titulo)' value='$BTN_CONTINUE'>";
						else
							echo "<input class='boton' type='button' align='center' onClick='javascript:Continuar($item_id)' value='$BTN_CONTINUE'>";

						echo "</div>";

					echo "</div>";
				?>
				
				<br>
				
				<div id="buttonarea">
					<input class='boton' type='button' align="center" name='regresa' onClick='javascript:window.history.back()' value="<?php echo $BTN_GOBACK;?>">
				</div>
				
				<br>
			
				</form>
				
			</div> <!-- - caja datos -->	   
			
        </div>  <!-- contenido pricipal -->
		
		<?php  display_copyright(); ?>
		
    </div> <!--bloque principal-->
 </div>  <!-- contenedor pricipal -->

</body>
</html>