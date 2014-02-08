<?php
	session_start();
/**********
	HISTORIAL DE CAMBIOS
	
	05-Junio-2009	Se crea el archivo serv_sanciones_cum.php
					Se completa informacion de llenado en la forma
					Se crea archivo de lenguaje
	09-Junio-2009	Se obtienen sanciones por cumplir de tabla sanciones, y se logra mostrarlas dependiendo el tipo de sancion.
	15-Junio-2009	Se logra registro de sancion tal cual esta registrada en la base de datos.
	22-Junio-2009	Se crea ventana para modificar el total de sancion por cumplir.
	25-Junio-2009	Se logra registrar el total de la sancion, tal como esta en la tabla o modificado por el usuario.
	
	PENDIENTE:
	
	   Colocar FECHA+HORA en FECHA_CUMPLIDA

**********/
		
	include ("../funcs.inc.php");
	include ("../basic/bd.class.php");

	include_language( "global_menus" );

	include ("circulacion.inc.php" );
	
	check_usuario_firmado(); 

	
	include_language( "serv_sanciones_cump" ); // archivo de idioma

	$id_biblioteca 	= getsessionvar('id_biblioteca');
	$usuario 		= getsessionvar('id_usuario');  // usuario firmado (operador)
	
	// usuario seleccionado en pantalla anterior
	$id_usuario = read_param( "id_usuario", 0, 1 ); // falla si no viene
	$accion = read_param( "the_action", "" ); // falla si no viene
	
	// datos del usuario al que se registrará la sanción
	$grupo = "";
	$nombre_usuario = "";

	$db = new DB;
	
	$error = 0;

	//
	// MOVIMIENTOS A LA BASE DE DATOS
	//
	if ( $accion == "save" )
	{
		$id_sancion = read_param( "id_sancion", "", 1 ); // falla sino
		$fecha_cumplida = read_param( "fecha_cumplida", "", 1 ); // falla sino
		$fecha_cumplida = date_for_database_updates($fecha_cumplida);

		$tipo = read_param( "tipo", "", 1 ); // falla sino
		
		$detalles = read_param( "detalles", "", 1 ); // falla sino
		
		$condona = "";
		
		if( $tipo == 0 )
			$condona = "S";
		
		$db->sql =  "UPDATE  sanciones SET FECHA_CUMPLIDA='$fecha_cumplida', STATUS_SANCION='S', CONDONACION='$condona', DETALLES_CUMPLIMIENTO='$detalles', ID_USUARIO_REGISTRO_CUMP=$usuario ";
		$db->sql .= " WHERE (ID_BIBLIOTECA=$id_biblioteca and ID_SANCION=$id_sancion)";
		$db->ExecSQL();
		
		require_once("../actions.inc.php");
		agregar_actividad_de_usuario( SERV_USERS_SANCTIONS_ACOMPLISHED, "" );
		
		ges_redirect( "serv_sanciones_cump_end.php?id_usuario=$id_usuario&id_sancion=$id_sancion" );

	} // $accion == "save"

	include ( "../basic/head_handler.php" );  // Coloca un encabezado HTML <head>
	HeadHandler( $LBL_HEADER, "../" );
	
?>
<script type='text/javascript' src='../basic/calend.js'></script>

<SCRIPT language='JavaScript'>

	function saveCumplimiento()
	{					
		var txt_tipo_cumplimiento = js_getElementByName_Value( "rad_tipo_cumplimiento" );
		var txt_id_sancion = js_getElementByName_Value( "id_sancion" );
		var txt_fecha_sancion = js_getElementByName( "fecha_sancion" );
		var txt_fecha_registro = js_getElementByName_Value( "fecha_registro" );
		var txt_detalles = js_getElementByName_Value( "detalles" );
			
		if( txt_tipo_cumplimiento==1 && !Validar2Fechas( txt_fecha_sancion.innerHTML, txt_fecha_registro ) )
		{
			alert( "<?php echo $VALIDA_MSG_WRONGDATE;?>" );
			document.agregar_form.fecha_registro.focus();
			error = 1;			
		}			
		else
		{		
			url = "serv_sanciones_cump.php?the_action=save&id_usuario=<?php echo $id_usuario;?>&id_sancion=" + txt_id_sancion + "&tipo=" + txt_tipo_cumplimiento + "&fecha_cumplida=" + txt_fecha_registro;		
			url += "&detalles=" + txt_detalles;

			js_ChangeLocation( url );
		}
	}

	function inicializar()
	{	
		prepareInputsForHints();
	}	
	
	function DisableEnableDate( show_hide )
	{
		if( show_hide == 1 )
			ShowDiv( "div_fecha_cumplimiento" );
		if( show_hide == 0 )
			HideDiv( "div_fecha_cumplimiento" );
	}
	
	function CumpleSancion( id_sancion, fecha_sancion, descrip_sancion, obj )
	{   		
		var objGrayed = js_getElementByName( "popUpBlock" );
		ShowDiv( "popUpBlock" );
		
		objGrayed.style.left = "1px";
		
		if( ShowDiv( "div_cumple_sancion" ) )
		{
			var div_cumple_sancion = js_getElementByName( "div_cumple_sancion" );
			var div_fecha_sancion = js_getElementByName( "fecha_sancion" );
			var div_descrip_sancion = js_getElementByName( "descrip_sancion" );
			var obj_id_sancion = js_getElementByName( "id_sancion" );
			
			div_cumple_sancion.style.zIndex = 300; // para que quede arriba de otros
			
			if( obj.getClientRects ) 
			{			
				var xpos = obj.getClientRects();
				
				if( xpos.length > 0 )
				{
					div_cumple_sancion.style.position = "absolute";
					div_cumple_sancion.style.top  = (parseInt(xpos[0].top) - 40) + "px";
					//div_cumple_sancion.style.left = (xpos[0].left - 250) + "px";
					
					//alert( div_cumple_sancion.style.top );
					
					var new_pwd = js_getElementByName( "condonar" );
					
					if( new_pwd ) new_pwd.focus();
				}
			}

			div_fecha_sancion.innerHTML = fecha_sancion;
			div_descrip_sancion.innerHTML = descrip_sancion ;
			obj_id_sancion.value = id_sancion;
		}
	}	
	
	function closeCumpleSancion()
	{
		if( HideDiv( "div_cumple_sancion" ) )
		{			
			HideDiv( "div_descrip_title" );
			HideDiv( "div_error_title" );
			
			HideDiv( "popUpBlock" );
		}	
	}

</SCRIPT>

<STYLE>

	#popUpBlock
	{
		display: none;
		position: absolute;
		background-color: gray;
		left: 1px;
		
		top: 4px;
		width: 100%;
		min-height: 99%;
		overflow: auto;
		
		filter:alpha(opacity=35);
		-moz-opacity:0.35;
		opacity: 0.35;		
		
		border: 0px solid silver;
		border-bottom; 4px solid gray;
		
		z-Index: 48;
	}

	.sp_hint { width: 300px; }

	#buttonarea { 
		border: 1px solid red;  
	} 
	
	#nombre_usuario { 
		display: inline; 
		position: absolute;
		left: 13em;
		width: 45em; 
		border: 1px dotted green; 
		background: transparent;
		padding: 3px;
	}
	
	#caja_datos {
		width: 130%; 
		height: 140%;
	}
	
	#div_cumple_sancion
	{	
		display: none;
		position: absolute;
		background-color: #FCFBD0;
		border: 3px solid gray; 
	
		left: 400px;
		top: 10px;
		width: 450px;
		height: 160px;
		
		font-size: 92%;
	}	


</STYLE>

<body id="home" onLoad='javascript:inicializar();'>

<div id='popUpBlock' name='popUpBlock'></div>

<?php
  display_global_nav();  // barra de navegación superior
 ?>

<!-- contenedor principal -->
<div id="contenedor" class="contenedor">

<?php 
   display_banner();  // banner   
   display_menu('../'); // menu principal
 ?>
 
	<!--- INICIA POPUP PARA CUMPLIR SANCIÓN -->
	<div class="groupbox" id="div_cumple_sancion" name="div_cumple_sancion">
		<div style='float:left; width: 400px; font-size: 95%;'>
			
			<div>
				<div style='display:inline;'><strong><?php echo $LBL_HEADER_COL_0;?></strong></div>
				<div style='display:inline;' name='descrip_sancion' id='descrip_sancion'>&nbsp;</div><br>
			</div>

			<div style='margin-top: 5px;'>
				<div style='display:inline;'><strong><?php echo $LBL_HEADER_COL_2;?></strong></div>
				<div style='display:inline;' name='fecha_sancion' id='fecha_sancion'>&nbsp;</div>
			</div>
			<br>			
			
			<!-- ID USUARIO cambiando -->
			<input type='hidden' class='hidden' name='id_sancion' id='id_sancion' value=''>						
			
			&nbsp;<input type="radio" class="radio" name="rad_tipo_cumplimiento" value="1" checked onClick='javascript:DisableEnableDate(1);'> Cumplir sanción&nbsp;&nbsp;
				  <input type="radio" class="radio" name="rad_tipo_cumplimiento" value="0" onClick='javascript:DisableEnableDate(0);'> Condonar 
			
			<br><br>
			
			<div id='div_fecha_cumplimiento' name='div_fecha_cumplimiento'>
				<div style='float:left; margin-top:5px; width: 140px; text-align: right;'>&nbsp;&nbsp;<?php echo $LBL_DATE_ACOMPLISH; ?>&nbsp&nbsp</div>
				<div style='float:left; display:inline;'><?php colocar_edit_date( "fecha_registro", getcurdate_human_format(), 0, "" ); ?></div>						
			</div>
			
			<br style='clear:both'>
			
			<div>
				<div style='float:left; margin-top:5px; width: 140px; text-align: right;'>&nbsp;&nbsp;<?php echo $LBL_DETAILS_ACOMPLISH; ?>&nbsp&nbsp</div>
				<div style='float:left; display:inline;'><input type=text name="detalles" id="detalles" value="" size=45 maxlength=200></div>												
			</div>
			
			<br style='clear:both'>

			<div style='display: inline; position: relative; top: 12px; left: 15px;' >
				<input type="button" class="boton" value="<?php echo $BTN_SAVE;?>" name="btnSavePwd" id="btnSavePwd" onClick="javascript:saveCumplimiento();">&nbsp;
				<input type="button" class="boton" value="<?php echo $BTN_CLOSEWIN;?>" onClick="javascript:closeCumpleSancion();">
			</div>

		</div>
		
		<!-- close icon -->
		<div style="float:right; padding:0px; position: relative; top: -10px; margin:0px;">
			<br>
			<a href="javascript:closeCumpleSancion();"><img src="../images/icons/close_button.gif"></a>
		</div><br>
		<!-- close icon -->
		
		<br style='clear:all'>
		
	</div>
	<!--- FIN POPUP PARA CUMPLIR SANCIÓN -->	  				

 
	<div id="bloque_principal"> 
		<div id="contenido_principal">
			<div class="caja_datos" id="caja_datos"> 
				<h1><?php echo $LBL_HEADER; ?></h1>
				<hr>
				<h4><?php echo $LBL_SUB_HEADER; ?></h4><br>
				
				<form name="agregar_form" id="agregar_form" class="forma_captura">
							
				<div id='caja_info' name='caja_info' style='display:none' >
					<strong>&nbsp;</strong>
				</div>
				
				<?php
					//
					// VALIDAR USUARIO
					// 
					$grupo = "";

					if( $id_usuario != 0 )
					{
						require_once( "circulacion.inc.php" );

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
							if( $user->STATUS == "A" )
							{
								$nombre_usuario = $user->NOMBRE_COMPLETO;					
								$grupo = $user->NOMBRE_GRUPO;
								
								$items_actualmente_prestados = $user->ObtenerNumItemsPrestados();
								$sanciones = $user->ObtenerNumSanciones();
								
								$max_renovaciones  = $user->GRUPO_MAX_RENOVACIONES;				
								$dias_renovacion   = $user->GRUPO_DIAS_RENOVACION_DEFAULT;
								
								$admite_sancion_econmica = $user->ADMITE_SANCION_ECONOMICA; 
								$admite_sancion_horas    = $user->ADMITE_SANCION_HORAS; 
								$admite_sancion_especie  = $user->ADMITE_SANCION_ESPECIE; 
								
								if ( ($admite_sancion_econmica == "N") and 
									 ($admite_sancion_horas == "N") and 
									 ($admite_sancion_especie == "N"))  // al grupo no se le registran sanciones. 
								{
									$error = 1;
									$error_message = $VALIDA_MSG_IFGROUP;										
								}								
							}
							else 
							{
								$error = 2;
								$error_message = $VALIDA_MSG_STATUS;
							}
						}

						$user->destroy();
					}				
				
					if( $error == 1 )
					{
						echo "<br><div class=caja_errores>";
						echo " <strong> $VALIDA_MSG_STATUS </strong>";
						echo "</div>";
					}
					else if( $error == 2 )
					{
						echo "<br><div class=caja_errores>";
						echo " <strong> $VALIDA_MSG_IFGROUP </strong>";
						echo "</div>";					
					}

				 ?>
				
				

				<label for="txt_id_usuario"><strong><?php echo $LBL_IDUSUARIO; ?></strong></label>
				
				&nbsp;&nbsp;
				<span id="nombre_usuario" name="nombre_usuario">
					<img src="../images/icons/user.gif">&nbsp;

					<?php 
						echo $nombre_usuario . "<br> $grupo "; 

						if( $items_actualmente_prestados > 0 )
						{
							echo "&nbsp;<img src='../images/icons/warning.gif'>&nbsp;";
							echo sprintf( $HINT_MAX_ITEMS_ALREADY_HAD, $items_actualmente_prestados );
						}

					?>

				</span>						

				<br style='clear:both'> 
				
				<br><br><br>

				<?php

					if( $sanciones > 0 )
					{
						$a='$a';
						$i=0;
						$tipo='';
						$db->Open( "SELECT a.ID_SANCION, a.TIPO_SANCION, a.FECHA_SANCION, a.FECHA_LIMITE, a.MOTIVO, a.MONTO_SANCION, a.OBSERVACIONES, b.DESCRIPCION, b.ECONOMICA_SN, b.ECONOMICA_MONTO_FIJO, ".
								   " b.LABOR_SOCIAL_SN, b.LABOR_SOCIAL_HORAS, ESPECIE_SN " . 
								   " FROM sanciones a ".
								   "  LEFT JOIN cfgsanciones b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.TIPO_SANCION=a.TIPO_SANCION) " .
								  "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_USUARIO=$id_usuario and a.STATUS_SANCION='N' " );
								  
								  //$db->DebugSQL();
					
						echo "<table border=0 width='100%'>"; 
						echo "<tr>";
						echo "<td class='columna columnaEncabezado cuadricula' align='left'>$LBL_HEADER_COL_1</td>";
						echo "<td class='columna columnaEncabezado cuadricula' align='center'>$LBL_HEADER_COL_2<br>(dd/mm/aaaa)</td>";
						echo "<td class='columna columnaEncabezado cuadricula' align='center'>$LBL_HEADER_COL_3<br>(dd/mm/aaaa)</td>";
						echo "<td class='columna columnaEncabezado cuadricula' align='center'>$LBL_HEADER_COL_4<br></td>";
						echo "<td class='columna columnaEncabezado cuadricula' align='center'>$LBL_HEADER_COL_5</td>";
						echo "</tr>";
						$i=1;
					
						while( $db->NextRow() )
						{	
							$id_sancion = $db->Field("ID_SANCION");
						    $fecha_sancion = get_str_datetime($db->Field("FECHA_SANCION"), 0, 0, 0 );
						    $fecha_limite = get_str_datetime($db->Field("FECHA_LIMITE"), 0, 0, 0 );
							$motivo_sancion = "<strong>" . $db->Field("DESCRIPCION") . "</strong> <br>" . $db->Field("MOTIVO");
							$monto = $db->Field("MONTO_SANCION");       
						   								
							if ($db->Field("ECONOMICA_SN") =='S') 
							{
								$resultado= $monto ." ". $LBL_ECONOMICA;
								$bandera_sancion=0;
								
							}
							else if ($db->Field("LABOR_SOCIAL_SN") =='S')
							{
								      $resultado = $monto ." ". $LBL_SOCIAL;
								$bandera_sancion = 0;
							}
							else 
							{
								$resultado = $db->Field("OBSERVACIONES");
								$bandera_sancion = 1;
							}
							
						   $class_hilite = "hilite_odd";
						   
						   if( $db->numRows % 2 == 1 )
							  $class_hilite = "hilite_even";							
							
							echo "<tr class='$class_hilite' onMouseOver='javascript:this.className=\"hilite_onmouse_enter\";' onMouseOut='javascript:this.className=\"$class_hilite\";'>";
							echo " <td class='columna cuadricula' align='left'>$motivo_sancion</td>"; 
							echo " <td class='columna cuadricula' align='center'>$fecha_sancion</td>";
							echo " <td class='columna cuadricula' align='center'>$fecha_limite</td>";
							echo " <td class='columna cuadricula' align='center'>$resultado</td>";
							echo " <td class='columna cuadricula' align='center'>";						
							echo "   <input class='boton' type='button' name='check_$i' onclick='javascript:CumpleSancion( $id_sancion, \"$fecha_sancion\", \"" . $motivo_sancion. "\", this );' value='$BTN_DONE' >";
							echo " </td>";
							echo "</tr>";

							$i++;
							$resultado=" ";
						}    //fin del WHILE
						
						echo "</table>";
						
						$db->Close();
						
					}	  
					else
					{
						echo "<br><div class=caja_info>";
						echo " <strong> $VALIDA_MSG_NOSANCION </strong>";
						echo "</div>";
						
						echo "<SCRIPT LANGUAGE='javascript'>";
						echo "   document.agregar_form.txt_id_usuario.value='';";
						echo "</SCRIPT>";
						
					}// fin IF $sanciones
				?>
				
				</form>
				
			</div> <!-- - caja datos -->	
			
			<br>
			
			
		</div>  <!-- contenido pricipal -->		
		<?php  display_copyright(); ?>
	</div> <!--bloque principal-->
</div>  <!-- contenedor principal -->
       
</body>

</html>