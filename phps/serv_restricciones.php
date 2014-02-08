<?php
session_start();
/**********

	19-oct-2009     Se crea el archivo de control de restricciones por usuario
	23-oct-2009		Se agrega $db a process_exmail

 **/
 
	include ("../funcs.inc.php");
	include ("../basic/bd.class.php");
	
	include ("circulacion.inc.php");
	
	include_language( "global_menus" ); // agregar en todos los archivos

	check_usuario_firmado(); 
	
	include_language( "serv_restricciones" );

	$nombre_usuario = "";
	$grupo = "";
	
	$items_en_prestamo = 0;
	$sanciones = 0;	
	
	$max_renovaciones  =  0;
	$dias_renovacion   = 0;
	$permite_renovacion_con_retraso = "";

	global $IS_DEBUG;
	
	$IS_DEBUG = 1;
	
	$usuario=getsessionvar('usuario');
	$id_biblioteca =getsessionvar('id_biblioteca');
	$id_usuario = read_param("id_usuario",0,1);  // fail if not exists
	$the_action = read_param( "the_action", "" );
	
	$db = new DB;
	
	$error = 0;
	$error_msg = "";
	
	if( $the_action == "add_restriction" )
	{
		$tipo_restriccion = read_param( "cmb_tiporestriccion", "", 1 );  // fail if not exists
		$txt_fecha_inicio = read_param( "txt_date_starting", "", 1 );  // fail if not exists
		$txt_fecha_fin    = read_param( "txt_date_ending", "", 1 );  // fail if not exists
		$motivo			  = read_param( "txt_description", "", 1 ); // fail
		
		//Ultimo registro de renovaciones
		$db->Open( "SELECT COUNT(*) AS CUANTOS, MAX(ID_RESTRICCION) AS MAXIMO FROM restricciones ".
					"WHERE ID_BIBLIOTECA=$id_biblioteca;" );

		if( $db->NextRow() )
		{
			if( $db->row["CUANTOS"] == 0 )
				$id_restriccion = 1;
			else
				$id_restriccion = $db->row["MAXIMO"] + 1;
		}
		
		$db->Close();				
		
		$txt_fecha_inicio = date_for_database_updates( $txt_fecha_inicio );
		$txt_fecha_fin = date_for_database_updates( $txt_fecha_fin );

		$usuario_registra = getsessionvar( "id_usuario" );
		$now = current_dateandtime();
		
		$db->sql  = "INSERT INTO restricciones ( ID_BIBLIOTECA, ID_RESTRICCION, ID_USUARIO, TIPO_RESTRICCION, FECHA_INICIO, FECHA_FINAL, MOTIVO, STATUS_RESTRICCION, ID_USUARIO_REGISTRA, FECHA_REGISTRO ) ";
		$db->sql .= " VALUES ( $id_biblioteca, $id_restriccion, $id_usuario, $tipo_restriccion, '$txt_fecha_inicio', '$txt_fecha_fin', '$motivo', 'A', $usuario_registra, '$now' );";
		$db->ExecSQL();
				
		require_once("../actions.inc.php");
		agregar_actividad_de_usuario( SERV_USERS_RESTRICTIONS_CREATE, "" );		
		
		require_once( "email_factory.inc.php" );
		
		$mail_sent = 0;
		
		/**if( process_xmail( $db, $id_biblioteca, $id_usuario, EMAIL_RESTRICTION, $id_restriccion ) )
		{
			$mail_sent = 1;
		}	*/	
		
		$db->destroy();
		
		ges_redirect( "serv_restricciones.php?id_usuario=$id_usuario&id_restriction_created=$id_restriccion&mail_sent=$mail_sent;" );
	}
	else if( $the_action == "cancel_restriction" )
	{
		$id_restriccion = read_param( "id_restriction", "", 1 );  // fail if not exists
		
		$usuario_cancela = getsessionvar( "id_usuario" );
		$now = current_dateandtime();
		
		$db->sql  = "UPDATE restricciones SET STATUS_RESTRICCION='C', ID_USUARIO_CANCELA=$usuario_cancela, FECHA_CANCELACION='$now' ";
		$db->sql .= "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_RESTRICCION=$id_restriccion;";
		$db->ExecSQL();
		
		require_once("../actions.inc.php");
		agregar_actividad_de_usuario( SERV_USERS_RESTRICTIONS_CANCEL, "" );		
		
		require_once( "email_factory.inc.php" );		
		
		$mail_sent = 0;
		
		/**if( process_xmail( $db, $id_biblioteca, $id_usuario, EMAIL_RESTRICTION_CANCELLED, $id_restriccion ) )
		{
			$mail_sent = 1;
		}	 **/	
		
		$db->destroy();
		
		ges_redirect( "serv_restricciones.php?id_usuario=$id_usuario&id_restriction_cancelled=$id_restriccion&mail_sent=$mail_sent;" );	
	}
	

	// Draw an html head
	include ("../basic/head_handler.php");	
	HeadHandler( $LBL_OPCION_RESTRICTION, "../" );

?>

<script type='text/javascript' src='../basic/calend.js'></script>

<SCRIPT language="JavaScript">

	function saveRestriction()
	{					
		var val_tiporestriccion = js_getElementByName_Value( "cmb_tiporestriccion" );
		var txt_fecha_inicio = js_getElementByName_Value( "txt_date_starting" );
		var txt_fecha_fin    = js_getElementByName_Value( "txt_date_ending" );
		var txt_descripcion  = js_getElementByName( "txt_description" );
		var error = 0;	
	
		if( txt_descripcion.value == "" )
		{
			alert( "<?php echo $VALIDA_MSG_NODESCRIPTION;?>" );
			txt_descripcion.focus();
			error = 1;
		}
	
		if( error == 0 )
		{
			if( !Validar2Fechas( txt_fecha_inicio, txt_fecha_fin ) )
			{
				alert( "<?php echo $VALIDA_MSG_WRONGDATE;?>" );
				document.agregar_restriccion.txt_date_starting.focus();
				error = 1;			
			}			
			else
			{		
				url = "serv_restricciones.php?the_action=add_restriction&id_usuario=<?php echo $id_usuario;?>&cmb_tiporestriccion=" + val_tiporestriccion + "&txt_fecha_inicio=" + txt_fecha_inicio + "&txt_fecha_fin=" + txt_fecha_fin;
				url += "&txt_descripcion=" + txt_descripcion.value;

				if( confirm("<?php echo $ALERT_WANT_TO_ADD;?>") )
				{
					document.agregar_restriccion.the_action.value = "add_restriction";
					document.agregar_restriccion.action = "serv_restricciones.php";
					document.agregar_restriccion.submit();
				}
				//js_ChangeLocation( url );
			}
		}
	}	
	
	function Add()
	{	
		var objGrayed = js_getElementByName( "popUpBlock" );
		ShowDiv( "popUpBlock" );
		
		objGrayed.style.left = "1px";
		
		if( ShowDiv( "div_enter_restriction" ) )
		{
			var div_enter_restriction = js_getElementByName( "div_enter_restriction" );
			//var div_fecha_sancion = js_getElementByName( "fecha_sancion" );
			//var div_descrip_sancion = js_getElementByName( "descrip_sancion" );
			//var obj_id_sancion = js_getElementByName( "id_sancion" );
			
			div_enter_restriction.style.zIndex = 300; // para que quede arriba de otros
			
		}
		
	}
	
	function closeDiv4Add()
	{
		if( HideDiv( "div_enter_restriction" ) )
		{					
			HideDiv( "popUpBlock" );
		}		
	}
	
	function Cancelar( id_restriccion )
	{
		if( confirm("<?php echo $ALERT_WANT_TO_CANCEL;?>") )
		{
			document.agregar_restriccion.the_action.value = "cancel_restriction";
			document.agregar_restriccion.id_restriction.value = id_restriccion;
			document.agregar_restriccion.action = "serv_restricciones.php";
			document.agregar_restriccion.submit();
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
	
	#info_restricciones
	{
		float: none;
		display: block; 
		position: relative;
		width: 55em; 
		background: transparent;
		padding: 3px;
		overflow: auto;	
	}
	
	#caja_datos {
		float: none;
		width: 140%;
	}
	
	#div_enter_restriction
	{	
		display: none;
		position: absolute;
		background-color: #FCFBD0;
		border: 3px solid gray; 
	
		left: 200px;
		top: 150px;
		width: 500px;
		height: 220px;
		
		font-size: 92%;
	}		
	
</STYLE>

<body id="home">

<div id='popUpBlock' name='popUpBlock'></div>
	
<?php
	display_global_nav();  // barra de navegación superior
?>

<div id="contenedor">

<?php 
		
	display_banner();  // banner
	display_menu('../'); // menu principal		
	
?>

	<!--- INICIA POPUP PARA CUMPLIR SANCIÓN -->
	<div class="groupbox" id="div_enter_restriction" name="div_enter_restriction">
		<div style='float:left; width: 480px;'>
			
			<div>
				<div style='display:inline;'><strong><?php echo $LBL_PLEASE_ENTER_DATA;?></strong></div>
				<div style='display:inline;' name='descrip_sancion' id='descrip_sancion'>&nbsp;</div><br>
			</div>

			<br>
			
			 <form name="agregar_restriccion" id="agregar_restriccion" class="forma_captura" method='POST'>
				<input type=hidden class=hidden id="the_action" name="the_action" value="">
				<input type=hidden class=hidden id="id_usuario" name="id_usuario" value="<?php echo $id_usuario;?>">
				<input type=hidden class=hidden id="id_restriction" name="id_restriction" value="0">

				<dt>
					<label for="cmb_tiporestriccion"><?php echo $LBL_CHOOSE_RESTRICTION;?></label>
				</dt>
				<dd>
					
					<select id='cmb_tiporestriccion' name='cmb_tiporestriccion' class='select_captura' >
						<?php
						
							$db->Open("SELECT TIPO_RESTRICCION, DESCRIPCION FROM cfgrestricciones WHERE ID_BIBLIOTECA=$id_biblioteca; ");
							
							while( $db->NextRow() )
							{
								echo "<option value='" . $db->row["TIPO_RESTRICCION"] . "'>" . $db->row["DESCRIPCION"] . "</option>";
							}
							
							$db->Close();
							
						 ?>
					</select>

				</dd>
				<br>			
						
				<dt>
					<label for="txt_date_starting"><?php echo $LBL_DATE_STARTING;?></label>
				</dt>
				<dd>
					<?php colocar_edit_date( "txt_date_starting", getcurdate_human_format(), 0, "" ); ?>
				</dd>
				
				<dt>
					<label for="txt_date_ending"><?php echo $LBL_DATE_ENDING;?></label>
				</dt>
				<dd>
					<?php colocar_edit_date( "txt_date_ending", getcurdate_human_format(), 0, "" ); ?>
				</dd>			
				<br>
				
				<dt>
					<label for="txt_description"><?php echo $LBL_DESCRIPTION;?></label>
				</dt>
				<dd>
					<input class="campo_captura" type=text name="txt_description" id="txt_description" value="" size=65 maxlength=200>
				</dd>						
				
				<br style='clear:both'>

				<div style='display: inline; position: relative; top: 12px; left: 15px;' >
					<input type="button" class="boton" value="<?php echo $BTN_SAVE;?>" name="btnSavePwd" id="btnSavePwd" onClick="javascript:saveRestriction();">&nbsp;
					<input type="button" class="boton" value="<?php echo $BTN_CLOSEWIN;?>" onClick="javascript:closeDiv4Add();">
				</div>
			
			</form>

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

<?php
	
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
				$grupo 			= $user->NOMBRE_GRUPO;

				//$items_en_prestamo = $user->ObtenerNumItemsPrestados();
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
	  <H2><?php echo $LBL_OPCION_RESTRICTION; ?></H2>
      <HR>
		<h2><?php echo $LBL_TEXTO_RESTRICTION; ?></h2><br>

		<div id="caja_datos_login">
		 <form name="agregar_form" id="agregar_form" class="forma_captura" method='POST'>
			<input type="hidden" class="hidden" id="the_action" name="the_action" value="">
			<input type="hidden" class="hidden" id="id_usuario" name="id_usuario" value="<?php echo $id_usuario;?>">
	
			<label for="txt_id_usuario"><?php echo $LBL_IDUSUARIO_RESTRICTION; ?></label>
          
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
				?>

			</div>
		  
          <br>
		  
		  <label for="txt_id_usuario"><?php echo $LBL_LISTARESTRICCIONES; ?></label>
		  
	          <?php
					echo "<div id='info_restricciones' name='info_restricciones' >\n";

					echo "<table width='100%'>";
				
					$db->Open("SELECT a.ID_RESTRICCION, a.FECHA_INICIO, a.FECHA_FINAL, b.DESCRIPCION, a.MOTIVO " .
							  "FROM restricciones a " . 
							  "  LEFT JOIN cfgrestricciones b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.TIPO_RESTRICCION=a.TIPO_RESTRICCION) " . 
							  "WHERE (a.ID_BIBLIOTECA=$id_biblioteca and a.ID_USUARIO=$id_usuario and a.STATUS_RESTRICCION='A') " );
					
					while($db->NextRow())
					{								
						$id_restriccion = $db->row["ID_RESTRICCION"];
						
						$fecha_inicio = dbdate_to_human_format(  $db->row["FECHA_INICIO"], 0 );
						$fecha_final  = dbdate_to_human_format(  $db->row["FECHA_FINAL"], 0 );
						
						if( $db->numRows == 1 )
						{
							echo "<tr>";
							echo "<td class='columna cuadricula columnaEncabezado'>$LBL_TITLE_DESCRIP</td>" . 
								 "<td class='columna cuadricula columnaEncabezado'>$LBL_TITLE_REASON</td>" . 
								 "<td class='columna cuadricula columnaEncabezado'>$LBL_TITLE_VALID_ON</td>" .
								 "<td class='columna cuadricula columnaEncabezado'>$LBL_TITLE_CANCEL</td>";
							echo "</tr>";
						}
						
						echo "<tr>";
						echo "<td class='columna cuadricula'>" . $db->row["DESCRIPCION"] . "</td>" . 
							 "<td class='columna cuadricula'>" . $db->row["MOTIVO"] . "</td>" . 
							 "<td class='columna cuadricula'> Del día " . $fecha_inicio . " al " . $fecha_final . "</td>" . 
							 "<td class='columna cuadricula'><input type='button' class='boton' value='$BTN_CANCEL_RESTRICTION' onClick='javascript:Cancelar($id_restriccion)'></td>";
						echo "</tr>";						
					} // fin de WHILE	

					
					if( $db->numRows == 0 )
					{
						echo " <strong> $MSG_NO_RESTRICTIONS</strong>";
					} // fin IF $records
				
					$db->Close();
					
					echo "</table>";
					
					echo "</div>";
					
			  ?>
			  
			  <br style='clear:both'>

			  <div name='buttonarea' id='buttonarea'>
			  
				<input type=button class=boton value='<?php echo $BTN_ADD_RESTRICTION;?>' onClick='javascript:Add();'>
			  
			  </div>
			  
			  <br style='clear:both'>
			  
			 </form>		  
		</div>  <!-- caja_datos_login -->

	 </div> <!-- caja_datos -->
  </div>  <!-- contenido principal -->
  <?php  display_copyright(); ?>
</div>  <!-- Bloque principal -->
</body>

</html>
