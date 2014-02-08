<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  20 abr 2009: Se crea el archivo PHP para descartar items/ejemplares del acervo
	  21 abr 2009: Se complementa el guardado de archivos.
	  
	  PENDIENTE:
	  
	  Verificar privilegio para que el usuario pueda autorizar
				   
     */
		
	include "../funcs.inc.php";
	include "../privilegios.inc.php";
	include "../basic/bd.class.php";
	
	include_language( "global_menus" );

	check_usuario_firmado(); 

	include_language( "anls_descarta_titulos" );		// archivo de idioma

	/** INICIO - FUNCIONES DE MODIFICACION/CREACION/BORRADO **/
	
	$id_descarte = "$LBL_TO_BE_ASIGNED";
		
	$action = "";
	
	$error = 0;
	
	if( isset( $_POST["action"] ) )
		$action = $_POST["action"];
	if( isset( $_GET["action"] ) )
		$action = $_GET["action"];
		
	if( isset( $_POST["id_titulo"] ) )
		$id_titulo = $_POST["id_titulo"];
	else if( isset( $_GET["id_titulo"] ) )
		$id_titulo = $_GET["id_titulo"];
	
	// vienen los items que deben ser descartados
	$items = "";
	if( isset( $_POST["items"] ) )
		$items = $_POST["items"];
	else if( isset( $_GET["items"] ) )
		$items = $_GET["items"];

	$id_red = getsessionvar("id_red");
	$id_biblioteca = getsessionvar("id_biblioteca");
	
	$user_can_auth = verificar_privilegio( PRIV_DISCARDS_AUTH );

	if( $action == "create_new" )
	{
		// generar el nuevo ID del descarte (ejemplar)
		$id_descarte = 0;

		$db = new DB( "SELECT MAX(ID_DESCARTE) AS MAXID FROM descartes_mst WHERE ID_BIBLIOTECA=$id_biblioteca" );

		if($db->NextRow())
			$id_descarte  = $db->Field("MAXID") + 1;

		$db->FreeResultset();

		$id_usuario_registra = $_POST["id_usuario_registra"];
		$motivo_descarte  	 = $_POST["txt_motivo_descarte"];
		
		$txt_details = $_POST["txt_details"];
		
		$array_items = split( "@", $items );
		$array_details = split( "@", $txt_details );

		$fecha_descarte = date_for_database_updates( $_POST["fecha_descarte"] );

		$extra_line1 = "";
		$extra_line2 = "";
		
		if( isset($_POST["auth_now"]) )
		{
			// cuando este campo existe
			// se debe autorizar de inmediato el descarte
			$fecha_autoriza 		= date_for_database_updates( $_POST["fecha_autoriza"] );
			$id_usuario_autoriza	= $_POST["id_usuario_autoriza"];
			
			$extra_line1 = ", FECHA_AUTORIZA, USUARIO_AUTORIZA";
			$extra_line2 = ", '$fecha_autoriza', '$id_usuario_autoriza'";
			
		}
		
		// Insertar registro MASTER
		$db->sql  = "INSERT INTO descartes_mst ( ID_BIBLIOTECA, ID_DESCARTE, FECHA_REGISTRA, USUARIO_REGISTRA, MOTIVO_DESCARTE $extra_line1) ";
		$db->sql .= "VALUES ( $id_biblioteca, $id_descarte, '$fecha_descarte', '$id_usuario_registra', '$motivo_descarte' $extra_line2) ";
		
		$db->ExecSQL();

		for( $i = 0; $i < count($array_items); $i++ )
		{
			$id_item = $array_items[ $i ];

			if( $id_item[0] == "@" )
			    $id_item = substr( $id_item, 1, 10 );

			$obs = $array_details[ $i ];
			
			$db->sql  = "INSERT INTO descartes_det ( ID_BIBLIOTECA, ID_DESCARTE, ID_ITEM, OBSERVACIONES ) ";
			$db->sql .= "                   VALUES ( $id_biblioteca, $id_descarte, $id_item, '$obs' ) ";
			$db->ExecSQL();

			$set_status = "B";
			
			if( isset($_POST["auth_now"]) )
				$set_status = "X";  // Descartar AHORA MISMO
			
			$db->sql  = "UPDATE acervo_copias SET STATUS = '$set_status' ";
			$db->sql .= "WHERE ( ID_BIBLIOTECA = $id_biblioteca and ID_ITEM = $id_item ) ";
			$db->ExecSQL();

			include "../actions.inc.php";
			
			if( isset($_POST["auth_now"]) )
				agregar_actividad_de_usuario( ANLS_DISCARDS, "Se registró/autorizó descarte No. $id_descarte.", $id_item );
			else
				agregar_actividad_de_usuario( ANLS_DISCARDS, "Se registró descarte No. $id_descarte.", $id_item );
		}
		
		if( isset($_POST["auth_now"]) )
			$error = 20;
		else
			$error = 10;
		
		$action = "edit";
		
		if( !allow_use_of_popups() )
			ges_redirect( "anls_existencias_paso2.php?id_titulo=$id_titulo" );

	}
	else if( $action == "autoriza" )
	{
		$id_descarte = $_POST["id_descarte"];

		$fecha_autoriza 		= date_for_database_updates( $_POST["fecha_autoriza"] );
		$id_usuario_autoriza	= $_POST["id_usuario_autoriza"];
		
		$db = new DB();
		// Modifica registro MASTER
		$db->sql  = "UPDATE descartes_mst SET FECHA_REGISTRA='$fecha_autoriza', USUARIO_AUTORIZA='$id_usuario_autoriza' ";
		$db->sql .= "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_DESCARTE=$id_descarte";
		$db->ExecSQL();
		
		// UPDATE adaptado a subqueries
		// funciona OK para Firebird / probar en otras 
		$db->ExecSQL( "UPDATE acervo_copias SET STATUS='X' WHERE ID_BIBLIOTECA=$id_biblioteca and ID_ITEM = (SELECT ID_ITEM FROM descartes_det WHERE ID_BIBLIOTECA=$id_biblioteca and ID_DESCARTE=$id_descarte) ");
		
		$error = 20;
		
		if( !allow_use_of_popups() )
			ges_redirect( "anls_descartes.php" );

	}
	else if( $action == "view" )
	{
		$id_descarte = $_GET["id_descarte"];

		$txt_usuario		= "";
		$txt_motivation 	= "";
		$txt_fecharegistra 	= "";
		
		$db = new DB();
		$db->sql  = "SELECT a.*, b.*, c.USERNAME, d.USERNAME AS UNAME_USUARIO_AUTORIZA FROM descartes_mst a " .
					"  LEFT JOIN descartes_det b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_DESCARTE=a.ID_DESCARTE) " . 
					"    LEFT JOIN cfgusuarios c ON (c.ID_BIBLIOTECA=a.ID_BIBLIOTECA and c.ID_USUARIO=a.USUARIO_REGISTRA) " .
					"      LEFT JOIN cfgusuarios d ON (c.ID_BIBLIOTECA=a.ID_BIBLIOTECA and c.ID_USUARIO=a.USUARIO_AUTORIZA) ";
		$db->sql .= "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_DESCARTE=$id_descarte";
		$db->Open();
		
		while( $db->NextRow() )
		{	
			$txt_id_usuario = $db->row["USUARIO_REGISTRA"];
			$txt_usuario    = $db->row["USERNAME"];
			$txt_motivation = $db->row["MOTIVO_DESCARTE"];
			$txt_fecharegistra = get_str_datetime( $db->row["FECHA_REGISTRA"], 0 );
			
			if( $items == "" )
			   $items = "@" . $db->row["ID_ITEM"];
			   
			$txt_fechaautoriza = get_str_datetime( $db->row["FECHA_AUTORIZA"], 0 );
			
			if( $txt_fechaautoriza == "" )
				$txt_fechaautoriza = strftime("%d/%m/%Y");
				
			$txt_id_usuario_autoriza = $db->row["USUARIO_AUTORIZA"];
			$txt_usuario_autoriza = $db->row["UNAME_USUARIO_AUTORIZA"];

			if( $db->numRows == 1 )
			{
				// ACCIONES SOLO DEL PRIMER REGISTRO
				
				// ya autorizado SOLO CONSULTAR
				if( $txt_id_usuario_autoriza != 0 )
					$action = "view_only";  // ya autorizado
				
				// inicializar datos del usuario que autorizará
				if( $txt_usuario_autoriza == "" )
				{
					$txt_id_usuario_autoriza	= getsessionvar( "id_usuario" );
					$txt_usuario_autoriza 		= getsessionvar( "usuario" );
				}
			}
			
		}
		
		if( $action == "view" and !$user_can_auth )
			$action = "";
		
		$db->FreeResultset();
	}
	else
	{
		$action = "create_new";  // acción por default
		
		$txt_usuario 	= getsessionvar( "usuario" );
		$txt_id_usuario = getsessionvar( "id_usuario" );
		$txt_motivation = "";
		$txt_fecharegistra = strftime("%d/%m/%Y");
		$txt_fechaautoriza = strftime("%d/%m/%Y");
		
		$txt_id_usuario_autoriza = getsessionvar( "id_usuario" );
		$txt_usuario_autoriza = getsessionvar( "usuario" );
	}

	$array_items = str_replace( "@", "a.ID_ITEM=", $items ); 			 // 1st ocurrence
	$array_items = str_replace( ":", " or a.ID_ITEM=", $array_items ); // other ocurrences
	
	/** FIN FUNCIONES DE MODIFICACION/CREACION/BORRADO **/
	
	// Coloca un encabezado HTML <head>
	include "../basic/head_handler.php";
	HeadHandler( ($action == "create_new") ? $LBL_CREATE_EXIST_V1 : $LBL_CREATE_EXIST_V2, "../");
		
?>

<SCRIPT type='text/javascript' src='../basic/calend.js'></SCRIPT>

<SCRIPT language="JavaScript">

	function validar( e )
	{
		var error = 0;
		
		if( !EsFechaValida( document.edit_form.fecha_descarte ) )
		{
			error = 1;
			alert( "<?php echo $VALIDA_MSG_1;?>" );
			document.edit_form.fecha_descarte.focus();
		}

		if( error == 0 )
		{
			var total = parseInt(document.edit_form.num_items.value);
			
			if( total == 0 )
			{
				error = 1;
				alert( "<?php echo $VALIDA_MSG_2;?>" );			
				
				return false;
			}
			else
			{			
				var txt = "";
				var items = "";
				var obj;
				var obj2
				
				for( var i=0; i<=total; i++ )
				{
					obj = document.getElementsByName("details_item_"+(i+1));
					obj2 = document.getElementsByName("id_item_"+(i+1));
					
					if( obj.length > 0 )
					{ 
						if( txt != "" )
							txt += "@";
							
						txt += obj[0].value; 
					}
					
					if( obj2.length > 0 )
					{ 
						if( items != "" )
							items += "@";

						items += obj2[0].value; 
					}
				}
				
				document.edit_form.items.value = items;
				document.edit_form.txt_details.value = txt;
				
				document.edit_form.submit();
				return true;
			
			}
		}
		else
			return false;
	}
	
	function autorizar()
	{
		/*js_ChangeLocation( "anls_descarta_titulos.php?id_descarte=<?php echo $id_descarte;?>&action=autoriza" );*/
		document.edit_form.action.value = "autoriza";
		document.edit_form.submit();
	}
	
</SCRIPT>

<STYLE>

 #contenido_principal 
 {
   float: left;
   width: 760px; 
 }
   
 #contenido_adicional 
 {
   float: left;
   margin-left: 5px;
   width: 150px; 
 }   
  
 #buttonarea { border: 0px solid red;  }
 #btnAutorizar { position:relative; left:6.5em; } 
 #btnCancelar { position:absolute; left:17em; } 
  
form.forma_captura label {
   width: 12em;
}

</STYLE>

<body id="home">

<?php

	// barra de navegación superior
	if( !allow_use_of_popups() )
		display_global_nav(); 
	else
		echo "<br>";

?>

<!-- contenedor principal -->
<div id="contenedor">

<?php 

	if( !allow_use_of_popups() )
	{
		// banner
		display_banner();  
	   
		// menu principal
		display_menu('../');
	}
	
	$db = new DB();
	
	if( $error == 10 )
	{
		echo "<SCRIPT LANGUAGE='javascript'>";
		echo "   alert('$SAVE_CREATED_DONE');";
		echo "   window.opener.document.location.reload();";
		echo "   window.close();";
		echo "</SCRIPT>";
	}	
	else if( $error == 20 )
	{
		echo "<SCRIPT LANGUAGE='javascript'>";
		echo "   alert('$SAVE_AUTH_DONE');";
		echo "   window.opener.document.location.reload();";
		echo "   window.close();";
		echo "</SCRIPT>";
	}	

 ?>
 
<div id="bloque_principal"> <!-- inicia contenido -->
 
 <div id="contenido_principal">

	<div class=caja_datos id=caja_datos1 >
		<h2>
		<?php 
		
			if( $action == "create_new" )
				echo $LBL_CREATE_EXIST_V1;
			else if( $action == "view" )
				echo $LBL_CREATE_EXIST_V3;
			else if( $action == "view_only" )
				echo $LBL_CREATE_EXIST_V4;				
			else
				echo $LBL_CREATE_EXIST_V2;
			
		?>
		
		<HR></h2><br>
		
		<?php
			if( $error == 2 )
			{
				echo "<div class=caja_errores>";
				echo " <strong>$ERROR_UPDATING_SAVING.</strong>";
				echo "</div>";
			}
		 ?>		

			<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post" name="edit_form" id="edit_form" class="forma_captura">
			  <input class='hidden' type='hidden' name=action id=action value="<?php echo $action;?>">
			  <input class='hidden' type='hidden' name="id_descarte" id="id_descarte" value="<?php echo $id_descarte; ?>">
			  
			  <input class='hidden' type='hidden' name="items" id="items" value="<?php echo $items; ?>">
			  <input class='hidden' type='hidden' name="txt_details" id="txt_details" value="">
			  
			  <input class='hidden' type='hidden' name='id_usuario_registra' id='id_usuario_registra' value="<?php echo $txt_id_usuario;?>">
			  <input class='hidden' type='hidden' name='id_usuario_autoriza' id='id_usuario_autoriza' value="<?php echo $txt_id_usuario_autoriza;?>">

				<label><?php echo $LBL_ID_ITEM;?></label>
				<span class="span_captura"><strong><?php echo "[" . $id_descarte . "]";?></strong></span>
				<br>

				<label for="fecha_descarte"><?php echo $LBL_FECHA;?></label>
				<?php colocar_edit_date( "fecha_descarte", $txt_fecharegistra, ($action=="view" or $action=="view_only" ? 1 : 0), "" ); ?>
				<br>

				<label for="txt_id_usuario"><?php echo $LBL_USUARIO_REGISTRA;?></label>
				<input class="campo_captura" type="text" disabled name="txt_id_usuario" id="txt_id_usuario" value="<?php echo $txt_usuario;;?>" size=10 maxlength=10>


				<label for="txt_motivo_descarte"><?php echo $LBL_MOTIVATION;?></label>
				<input <?php echo ($action=="view" or $action=="view_only") ? "disabled" : ""; ?> class="campo_captura" type="text" name="txt_motivo_descarte" id="txt_motivo_descarte" value="<?php echo $txt_motivation;?>" size='70' maxlength='250'>
				<br>
				
				<?php
				
					if( $user_can_auth )
					{
						// mostrar nuevos campos
						echo "<br>";
						echo "<label for='fecha_prestamo'>$LBL_FECHA_AUTORIZA</label>";
						colocar_edit_date( "fecha_autoriza", $txt_fechaautoriza, ($action=="view_only" ? 1 : 0), "" );
						
						if( $action == "create_new" )
						{  
							echo "<span style='position: relative; left:30px; width: 200px;'>$NOTES_AUTORIZACIONES_1</span>"; 
							echo "<input type='hidden' class='hidden' id='auth_now' name='auth_now' value='YES'>";
						}
						
						echo "<br>";
						
						if( $action=="view_only" ) echo "<br>";
						
						echo "<label for='txt_max_renovaciones'>$LBL_USUARIO_AUTORIZA</label>";
						echo "<input class='campo_captura' type='text' disabled name='txt_id_usuario_auth' id='txt_id_usuario_auth' value='$txt_usuario_autoriza' size=10 maxlength=10>";
						echo "<br>";
						echo "<br>";
					}
				 
				 ?>

				<label for="txt_id_material"><strong><?php echo $LBL_MATERIAL;?></strong></label><br><br>

					<table style='border: 1px dotted black;'>

					<tr>
						<td class='cuadricula columna columnaEncabezado' width='150px'><?php echo $LBL_ID_MATERIAL;?></td>
						<td class='cuadricula columna columnaEncabezado' width='250px'><?php echo $LBL_LOCATION;?></td>
						<td class='cuadricula columna columnaEncabezado' width='250px'><?php echo $LBL_PHYSICAL_ST;?></td>
						<td class='cuadricula columna columnaEncabezado' width='350px'><?php echo $LBL_DETAILS;?></td>
					</tr>
					
					<?php				
						// Estado físico del item (12)
						
						$db->sql =  "SELECT a.*, b.DESCRIPCION AS DESCRIP_UBICACION, b.NOTAS_UBICACION" . ($action=="view" ? ", c.OBSERVACIONES " : " ");
						$db->sql .= "FROM acervo_copias a " .
									"  LEFT JOIN cfgubicaciones b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_UBICACION=a.ID_UBICACION) ";
									
						if( $action == "view" )
						{
							$db->sql .= " LEFT JOIN descartes_det c ON (c.ID_BIBLIOTECA=a.ID_BIBLIOTECA and c.ID_DESCARTE=$id_descarte and c.ID_ITEM=a.ID_ITEM) ";
						}
						$db->sql .= "WHERE a.ID_BIBLIOTECA=$id_biblioteca and $array_items";
						$db->Open();

						$num = 0;
						
						require_once "tesauro.php";
						
						while( $db->NextRow() )
						{
							$status = $db->Field("STATUS");
							$str_phys_st = tesauro_obtener_descrip_termino( $db, $db->Field("ESTADO_FISICO") );
							
							// Solo disponibles
							if( $status == "D" or $action == "view")
							{
								$num++;
								
								$obs = "";
								
								if( $action == "view" )
								{
									$obs = $db->Field("OBSERVACIONES");
								}
								
								echo "\n<!-- ID_ITEM " . $db->Field("ID_ITEM") . "-->\n";
								echo "<tr>\n<td class='columna cuadricula'>" . $db->Field("ID_MATERIAL") . "<input type=hidden class=hidden id='id_item_$num' name='id_item_$num' value='" . $db->Field("ID_ITEM"). "'></td>\n".
										 "<td class='columna cuadricula'>" . $db->Field("DESCRIP_UBICACION") ."</td>\n" . 
										 "<td class='columna cuadricula'>" . $str_phys_st ."</td>\n" . 
										 "<td class='columna cuadricula'>\n<input " . (($action=="view") ? "disabled" : "") . " type=text class='campo_captura' size='40' maxlength='250' id='details_item_$db->numRows' name='details_item_" . $num . "' value='$obs'></td>\n" . 
									 "</tr>\n";
							}
							else
							{
								if( $status == "X" )
									$status = $VALIDA_MSG_3;
								else
									$status = $VALIDA_MSG_4;
									
								echo "<tr><td class='cuadricula columnaDeshabilitado columna'>" . $db->Field("ID_MATERIAL") . "</td>".
									 "<td class='columna columnaDeshabilitado cuadricula'>" . $db->Field("DESCRIP_UBICACION") ."</td>" . 
									 "<td class='columna columnaDeshabilitado cuadricula'>$str_phys_st</td>" . 
									 "<td class='columna columnaDeshabilitado cuadricula'>$status</td>" . 
									 "</tr>";
							}
						}

						$db->Close();
					?>	

					</table>
					
				<!-- almacena la cantidad de items mostrados -->
				<input type=hidden class=hidden id='num_items' name='num_items' value='<?php echo $num;?>'>
				
				<br>
				<br>

			  <div id="buttonarea">
				<?php 
				
					if( $action == "create_new" )
						echo "<input id='btnGuardar' name='btnGuardar' class='boton' type='button' value='$BTN_SAVE' onClick='javascript:validar();'>";
					else
					{

						if( $user_can_auth and $action != "view_only" )
						{ 	echo "<input id='btnAutorizar' name='btnAutorizar' class='boton' type='button' value='&nbsp;&nbsp;$BTN_AUTH&nbsp;&nbsp;' onClick='javascript:autorizar();'>"; }
					}
				
				 ?>
				
				&nbsp;
				<input id="btnCancelar" name="btnCancelar" class="boton" type="button" value="&nbsp;&nbsp;<?php echo $BTN_CANCEL;?>&nbsp;&nbsp;" onClick='<?php echo back_function();?>'>
			  </div>
			  
			  <br> <!-- for IE -->
			  <br>
			  
			</form>
	  
	</div> <!-- caja_datos --> 

 </div> <!-- contenido_principal -->

 <div id="contenido_adicional">
	<?php echo $NOTES_HELP_INSIDE;?>
	<br>
  </div> <!-- contenido_adicional -->
<br><br>
</div>
<br>
<!-- end div bloque_principal -->

<?php  display_copyright(); ?>

</div><!-- end div contenedor -->

</body>

</html>