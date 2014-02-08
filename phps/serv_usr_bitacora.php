<?php
	session_start();
	
	/*******
	  - Bitacora de actividades por usuario.
	  - Inicio 09 mayo de 2009.
	  
	  PENDIENTE: - checar que en el query de busqueda se incluya el ID_BIBLIOTECA.
				 - colocar un checkbox que indique si mostrar solo admvos o también alumnos
	*/
	
	include "../funcs.inc.php";
	include "../actions.inc.php";
	include "../basic/bd.class.php";
	include "circulacion.inc.php";
	
	include_language( "global_menus" );
	include_language( "serv_usr_bitacora" ); // archivo de idioma
	
	$pag_ant = read_param( "pagina", 1 );
	
	check_usuario_firmado();
	
	$id_biblioteca = getsessionvar( "id_biblioteca" );
	
	$consultar		= read_param( "consultar", False );
	$page			= read_param( "page", 1 );
	$id_usuario 	= read_param( "id_usuario", 1 );
	$initial_letter = read_param( "initial_letter", "A" );
	$ordered_by 	= read_param( "ordered_by", "id" );
	$fecha_desde 	= read_param( "fecha_desde", getcurdate_human_format() );
	$fecha_hasta 	= read_param( "fecha_hasta", getcurdate_human_format() );
	$id_accion 		= read_param( "id_accion", "-1" );
	$donde			= read_param( "donde", "-1" );
	
	include "../basic/head_handler.php"; // Coloca un encabezado HTML <head>	
	HeadHandler( "$LBL_HEADER_V1", "../" );
	
	include( "../privilegios.inc.php" );
	verificar_privilegio( PRIV_USERLOG, 1, 1 );		 // ultimo 1 = PERMITIR ACCESO A LECTORES
	
	$liga_de_origen = read_param( "liga_origen", "" );
	
	if( $liga_de_origen == "" )
	{
		if( isset($_SERVER["HTTP_REFERER"] ) )
		{
			$liga_de_origen = $_SERVER["HTTP_REFERER"];
			
			$pos = strpos( $liga_de_origen, $_SERVER["SERVER_NAME"] );
			
			$liga_de_origen = substr( $liga_de_origen, $pos+strlen($_SERVER["SERVER_NAME"]), 255 );
			//$liga_de_origen = "'.." . $liga_de_origen . "'";
			$liga_de_origen = ".." . $liga_de_origen;
		}
			
	}
		
?>

<script type="text/javascript" src="../basic/calend.js"></script>

<SCRIPT type="text/javascript" language="JavaScript">

	function go()
	{
		var fecha_desde = js_getElementByName_Value( "fecha_desde" );
		var fecha_hasta = js_getElementByName_Value( "fecha_hasta" );
		var id_usuario = js_getElementByName_Value( "cmb_usuarios" );
		var id_accion = js_getElementByName_Value( "cmb_acciones" );
		
//		verificar fechas 
		
		var params = "fecha_desde=" + fecha_desde + "&fecha_hasta=" + fecha_hasta + "&id_usuario=" + id_usuario +
					 "&id_accion=" + id_accion + "&consultar=true" ;
					 
		<?php
			echo "params += \"&liga_origen=$liga_de_origen\";";
	?>	 
		location.href = "serv_usr_bitacora.php?" + params;
	}
	
	window.onload=function()
	{
		prepareInputsForHints();
	}	
	
</SCRIPT>

<STYLE>
	
	#contenido_principal 
	{
		width: 80%; 
	}
	
	#cmb_usuarios
	{
		 width:320px;
	}
	
	#cmb_acciones
	{
		 width:320px;
	}	

</STYLE>

<body id="home">

<?php
  // barra de navegación superior
  display_global_nav();  
 ?>

<!-- contenedor principal -->
<div id="contenedor">

<?php 
   // banner
   display_banner();  
   
   // menu principal
   display_menu( "../" ); // menu principal
 ?>
 
<div id="bloque_principal"> <!-- inicia contenido -->
	<div id="contenido_principal">
 		<div class=caja_datos id=caja_datos1>
			
	<?php
			
		$db = new DB();		
			
		echo "<h2>$LBL_HEADER_V1</h2>";
		echo "<HR><br>";
			
		$params = "serv_usr_bitacora.php?fecha_desde=".$fecha_desde."&fecha_hasta=".$fecha_hasta."&id_usuario=".$id_usuario.
				  "&id_accion=".$id_accion."&consultar=".$consultar;
				  
		$params .= "&liga_origen=$liga_de_origen";				  

	?>
		
		<form action='serv_usr_bitacora.php' method='post' name='bit_form' id='bit_form' class='forma_captura'>
		<?php
			echo "<input class='hidden' type='hidden' name='id_usuario' id='id_usuario' value='$id_usuario' >";
		?>
		
		<!-- DESDE -->
		<dt>
			<label for='fecha_desde'><strong><?php echo $LBL_DESDE;?></strong></label>
		</dt>
		<dd>
			<?php
				colocar_edit_date( "fecha_desde", "$fecha_desde", 0, "" );
			?>

			<span class="sp_hint"><?php echo $HINT_DATE_FROM;?><span class="hint-pointer">&nbsp;</span></span>
		</dd>
		
		<!-- HASTA -->
		<dt>
			<label for='fecha_hasta'><strong><?php echo $LBL_HASTA;?></strong></label>
		</dt>
		<dd>
			<?php
			  colocar_edit_date( "fecha_hasta", "$fecha_hasta", 0, "" );
			 ?>
			
			<span class="sp_hint"><?php echo $HINT_DATE_TO;?><span class="hint-pointer">&nbsp;</span></span>
		</dd>
		<br>
		
		<!-- SELECT para USUARIO -->
		<dt>
			<label for='cmb_usuarios'><strong><?php echo $LBL_ID_USUARIO;?></strong></label>
		</dt>
		
		<dd>
					
		<?php
			
			if( getsessionvar( "empleado" ) == "S" )
			{
				echo "<select name='cmb_usuarios' id='cmb_usuarios' class='select_captura'>";
				$db->Open( "SELECT ID_USUARIO, PATERNO, MATERNO, NOMBRE " . 
						   "FROM cfgusuarios " . 
						   "WHERE ID_BIBLIOTECA=$id_biblioteca " );
			
				while( $db->NextRow() )
				{ 
					$str_selected = ""; 
					
					if ( $id_usuario == $db->row["ID_USUARIO"] )
						$str_selected = "SELECTED";
					
					echo "<option value='" . $db->row["ID_USUARIO"] . "' $str_selected >" . $db->row['NOMBRE']." ".$db->row['PATERNO']." ".$db->row['MATERNO'] . "</option>";
				}
					
				$db->Close();
				
				echo "</select>";
			}
			else
			{
				echo getsessionvar( "nombreusuario" );
				echo "<input type='hidden' class='hidden' name='cmb_usuarios' id='cmb_usuarios' value='" . getsessionvar("id_usuario") . "'>";
			}
						
		?>
			

			<span class="sp_hint"><?php echo $HINT_SELECT_USER;?><span class="hint-pointer">&nbsp;</span></span>
		</dd>
		
		<br>
		
		<!-- SELECT para ACCION -->
		<dt>
			<label for='cmb_acciones'><strong><?php echo $LBL_ACTIVIDAD;?></strong></label>
		</dt>					
		<dd>
		
			<select name='cmb_acciones' id='cmb_acciones' class='select_captura'>
				<option value='-1'>- Todas -</option>
			<?php
			
				$db->Open( "SELECT ID_ACCION, DESCRIPCION " . 
						   "FROM cfgacciones ORDER BY ID_ACCION " );
			
				while( $db->NextRow() )
				{ 
					$str_selected = ""; 

					if ( $id_accion == $db->row["ID_ACCION"] )
						$str_selected = "SELECTED";				

					echo "<option value='" . $db->row['ID_ACCION'] . "' $str_selected>" . $db->row['DESCRIPCION'] . "</option>";						
				}
				
				$db->Close();
			?>
			</select>

			<span class="sp_hint"><?php echo $HINT_SELECT_ACCION;?><span class="hint-pointer">&nbsp;</span></span>

		</dd>

		<br>
		
		<div id="buttonarea">
			<input id="btnConsulta" name="btnConsulta" class="boton" type="button" value="Consultar" onClick="javascript:go()">	
			
			<?php
			
				$liga_de_origen = "'$liga_de_origen'";
				
				echo '<input id="btnRegresar" name="btnRegresar" class="boton" type="button" value="Regresar" onClick="location.href=' . $liga_de_origen . '">';
			
			?>
		</div>
		
		</form>
		
		<br>
		<br style='clear:both;'>
			
			<?php
			
			if( $consultar ) 
			{
				//$db = new DB();		
					
				$fecha_desde = date_for_database_updates($fecha_desde) . " 00:00:00";  // desde las 00:00
				$fecha_hasta = date_for_database_updates($fecha_hasta) . " 23:59:59";  // hasta las 11:59pm		  
				
				if ( $id_accion != -1 ) 
					$donde =" WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_ACCION = '$id_accion' and a.ID_USUARIO = '$id_usuario' and (a.FECHA BETWEEN '$fecha_desde' and '$fecha_hasta') ";
					
				if ( $id_accion == -1) 
					$donde=" WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_USUARIO=$id_usuario and (a.FECHA BETWEEN '$fecha_desde' and '$fecha_hasta') ";
					
				$db->sql = "SELECT a.FECHA, a.OBSERVACIONES, a.ID_ITEM, a.ID_TITULO, b.DESCRIPCION ".
						   "FROM usuarios_bitacora_eventos a " . 
						   " LEFT JOIN cfgacciones b ON (b.ID_ACCION = a.ID_ACCION) " . 
						   $donde . 
						   " ORDER BY a.ID_USUARIO, a.FECHA ";
					
				//$db->DebugSQL();
				
				$paginador = new Pager( $db, "N", 10 );
					
				if( isset( $_GET["page"] ) )
					$paginador->page = $_GET["page"];
				
				//if( $ordered_by == "id" )
				//{
					$paginador->Calculate_Ranges();
					$db->SetPage( $paginador->start_from, $paginador->Range, $paginador );
				//}
					
				$db->Open();
					
				echo "<table width='95%' style='margin-left:2%;'>";
					
				while( $db->NextRow() )
				{
					if( $db->numRows == 1 )
					{
						echo "<tr>" .
							 " <td class='cuadricula columna columnaEncabezado' width='30%'>$LBL_ACTIVIDAD</td>" .
							 " <td class='cuadricula columna columnaEncabezado' width='30%'>$LBL_FECHA</td>" . 
							 " <td class='cuadricula columna columnaEncabezado' width='40%'>$LBL_OBSERV</td>" .
							"</tr>";							
					}

					$accion = $db->row["DESCRIPCION"];
					$fecha  = get_str_datetime( $db->row["FECHA"], 1, 1, 1 );
					$observ = $db->row["OBSERVACIONES"];
					
					if( $db->row["ID_TITULO"] != 0 )
					{
						$item = new TItem_Basic( $id_biblioteca, $db->row["ID_TITULO"], 0, $db );
						
						$observ .= " <a href='gral_vertitulo.php?id_titulo=" . $db->row["ID_TITULO"] . "'>" . $item->cTitle_ShortVersion . "</a>";
						
						$item->destroy();
					}
					
				   $class_hilite = "hilite_odd";
				   
				   if( $db->numRows % 2 == 1 )
					  $class_hilite = "hilite_even";					
						
					echo "<tr class='$class_hilite' onMouseOver='javascript:this.className=\"hilite_onmouse_enter\";' onMouseOut='javascript:this.className=\"$class_hilite\";'>" . 
						 "<td class='cuadricula columna'>$accion</td> " .
						 "<td class='cuadricula columna'>$fecha</td>" .					
						 "<td class='cuadricula columna'>$observ</td> " .					
						 "</tr>";   	
					
				}//fin while
					
				echo "</table>";
				
				$paginador->DrawPages( $params );
				
				if ( $db->numRows == 0 ) 
				{
					echo "<div class=caja_errores>";
					echo " <center><strong>$MSG_NO_RECORDS_FOUND</strong></center>";
					echo "</div>";				 
				}	
				
				$db->FreeResultset();
				
			} // fin if
		
		$db->destroy();
			
		?>
			
		<br>
			
		</div> <!-- caja datos --> 
 	</div> <!-- contenido_principal -->
		
 	<div id="contenido_adicional">
		
 	</div> <!-- contenido_adicional -->
	
	<br style='clear:both;'>
		
</div>
<!-- end div bloque_principal -->

<?php  display_copyright(); ?>

</div><!-- end div contenedor -->

</body>

</html>