<?php
	session_start();
		
	/*******
	  Historial de Cambios
	  
	  13 oct 2009: Se crea el archivo PHP para configurar catalogo de restricciones
	  
     */		
		
	include ("../funcs.inc.php");

	check_usuario_firmado(); 

	include ("../basic/bd.class.php");
	include ("../basic/currency.inc.php");
	include ("../privilegios.inc.php");
	
	include_language( "global_menus" );
	include_language( "conf_restricciones" ); 	// archivo de idioma

	// Coloca un encabezado HTML <head>
	include ("../basic/head_handler.php");	
	HeadHandler( $LBL_TITLE_CAPTION, "../" );
	
	verificar_privilegio( PRIV_CFG_RESTRICTIONS, 1 );
	
	$id_biblioteca = getsessionvar( "id_biblioteca" );
	
	$id_restriccion_created = read_param( "id_restriccion_created", 0 );
	$id_restriccion_edited = read_param( "id_restriccion_edited", 0 );
	
	$id_restricciones_borradas = read_param( "id_restricciones_borradas", 0 );

	$filter = read_param( "filter", "" );
	
?>

<SCRIPT language="JavaScript">

	function activateFilter( obj )
	{
		var url = "conf_restricciones.php?filter=" + obj.value;
		
		js_ChangeLocation( url );
	}

	function crearsancion()
	{
		var url = "conf_crearrestricciones.php";
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "create_sanction", 820, 450 );
	}
	
	function borrarsanciones()
	{
		var sel_restricciones = js_getElementByName("sel_restricciones");
		
		if( sel_restricciones )
		{
			if( sel_restricciones.value != "" )
			{
				if( confirm("<?php echo $MSG_CONFIRM_BEFORE_DELETE;?>") )
				{
					var url = "conf_crearrestricciones.php?the_action=delete&restricciones=" + sel_restricciones.value
					
					js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "delete_group", 350, 350 );
				}
			}
			else
				alert( "<?php echo $MSG_NO_GROUPS_MARKED_TO_DELETE;?>" );

		}
	}
	
	function editar_def_restriccion( tipo_restriccion )
	{
		var url = "conf_crearrestricciones.php?the_action=edit&tipo_restriccion=" + tipo_restriccion;
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "edit_persona", 820, 450 );
	}
	
	function click_CheckBoxes()
	{
		var aObjects = document.getElementsByTagName("input");
		var sel_restricciones = js_getElementByName("sel_restricciones");
		var i;
		
		if( sel_restricciones )
		{
			sel_restricciones.value = "";
			
			for( i = 0; i<aObjects.length; i++ )
				if( aObjects[i].type == "checkbox" )
					if( aObjects[i].checked )
					{
						if( sel_restricciones.value == "" )
							sel_restricciones.value = "@";
						else
							sel_restricciones.value += ":";
						
						sel_restricciones.value += aObjects[i].name.substr(4,15);
					}
		}
	}
	
</SCRIPT>

<style type="text/css">

#contenido_principal 
{
	width: 850px;
}

#info_general 
{
	width: 830px;
}

#buttonArea
{
	margin-bottom: 8px;
}

</style>

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
   display_menu( "../" ); 
 ?>
   <div id="bloque_principal" > 
      <div id="contenido_principal">
	   <h1><?php echo $LBL_TITLE_CAPTION;?></h1>
		  
	   <div id="info_general" class="caja_datos">

		   <div style='float:left'>
				<div style='display:inline;'><?php echo $LBL_FILTER;?></div>
				<div style='display:inline;'>
					<select id='cmb_filter' name='cmb_filter' onChange='javascript:activateFilter(this);'>
						<option value=""><?php echo $LBL_NO_FILTER;?></option>";
						<option value="A" <?php echo ($filter=="A") ? "selected" : "";?> ><?php echo $LBL_FILTER_OPT1;?></option>";
						<option value="B" <?php echo ($filter=="B") ? "selected" : "";?> ><?php echo $LBL_FILTER_OPT2;?></option>";
						<option value="C" <?php echo ($filter=="C") ? "selected" : "";?> ><?php echo $LBL_FILTER_OPT3;?></option>";
						<option value="D" <?php echo ($filter=="D") ? "selected" : "";?> ><?php echo $LBL_FILTER_OPT4;?></option>";
					</select>
				</div>
		   </div>
		   
		   <div id="buttonArea" style='float:right'>
			<input class="boton" type="button" value="<?php echo $BTN_CREATE_PERSON; ?>" name="btnCrearPersona" onClick="javascript:crearsancion();">
			<input class="boton" type="button" value="<?php echo $BTN_DELETE_PERSON; ?>" name="btnBorrarPersona" onClick="javascript:borrarsanciones();">
		   </div> 
		   
		   <?php 
				$db = new DB();

				if( $id_restriccion_created != 0 ) 
				{
					echo "<div class=caja_info>";
					echo " <strong>$SAVE_CREATED_DONE</strong>";
					echo "</div>";
				}
			  
				if( $id_restriccion_edited != 0 )
				{
					echo "<div class=caja_info>";
					echo " <strong>$SAVE_EDIT_DONE</strong>";
					echo "</div>";
				}
				
				if( $id_restricciones_borradas > 0 )
				{
					echo "<div class=caja_info>";
					echo " <strong>$DELETE_DONE</strong>";
					echo "</div>";
				}			
		  
				$db->sql = "SELECT a.* " . 
						   "FROM cfgrestricciones a " .
						   "WHERE a.ID_BIBLIOTECA=$id_biblioteca ";
						   
				if( $filter != "" )
				{
					if( $filter == "A" )
						$db->sql .= " and a.IMPEDIR_CONSULTAS='S' ";
					else if( $filter == "B" )
						$db->sql .= " and a.IMPEDIR_RESERVACIONES='S' ";
					else if( $filter == "C" )
						$db->sql .= " and a.IMPEDIR_PRESTAMOS='S' ";
					else if( $filter == "D" )
						$db->sql .= " and a.IMPEDIR_RENOVACIONES='S' ";						
				}
						   
				$db->sql .= "ORDER BY a.TIPO_RESTRICCION";

				$db->Open();
				
				$db->SetClassesForDisplay( "hilite_odd", "hilite_even" );
				
				echo "<input type=hidden class=hidden name='sel_restricciones' id='sel_restricciones' value=''>";
				
				echo "<table width=830px>";
				echo "<tr>" .
					 " <td class='cuadricula columna columnaEncabezado' width='15px'>&nbsp;</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='45px'>$LBL_TIPORESTRICTION</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='280px'>$LBL_DESCRIPTION</td>" . 
					 " <td align='center' class='cuadricula columna columnaEncabezado' width='50px'>$LBL_AVOID_CONSULTS</td>" . 
					 " <td align='center' class='cuadricula columna columnaEncabezado' width='50px'>$LBL_AVOID_RESERVAS</td>" . 
					 " <td align='center' class='cuadricula columna columnaEncabezado' width='50px'>$LBL_AVOID_LOANS</td>" .
					 " <td align='center' class='cuadricula columna columnaEncabezado' width='50px'>$LBL_AVOID_RENEWALS</td>" .
					 "</tr>";
				
				$id_restriccion = 0;
				
				while( $db->NextRow() ) 
				{ 
					$id_restriccion++;
					
					$tipo_restriccion   = $db->row["TIPO_RESTRICCION"];
					$descripcion 	= $db->row["DESCRIPCION"];
					
					$hilite_on = "";
					$hilite_off = "";
					
					$rowedited = "";
					
					if( $id_restriccion == $id_restriccion_edited )
						$rowedited = "trHilited";
						
					$rowedited .= $db->class_for_display;

					echo "<tr class='$rowedited' onMouseOver='javascript:this.className=\"hilite_onmouse_enter\";' onMouseOut='javascript:this.className=\"$rowedited\";'>" . 
						 " <td class='cuadricula columna'><input name='chk_$tipo_restriccion' id='chk_$tipo_restriccion' type=checkbox onClick='javascript:click_CheckBoxes();'></td> " .
						 " <td class='cuadricula columna'>$hilite_on<a href='javascript:editar_def_restriccion(\"$tipo_restriccion\");'>" . $tipo_restriccion. "</a>$hilite_off</td> " .
						 " <td class='cuadricula columna'>$hilite_on<a href='javascript:editar_def_restriccion(\"$tipo_restriccion\");'>" . $descripcion. "</a>$hilite_off</td> " .
						 " <td align='center' class='cuadricula columna'>" . ICON_DisplayYESNO($db->Field("IMPEDIR_CONSULTAS"),true) . "</td> " .
						 " <td align='center' class='cuadricula columna'>" . ICON_DisplayYESNO($db->Field("IMPEDIR_RESERVACIONES"),true) . "</td> " .
						 " <td align='center' class='cuadricula columna'>" . ICON_DisplayYESNO($db->Field("IMPEDIR_PRESTAMOS"),true) . "</td> " .						 
						 " <td align='center' class='cuadricula columna'>" . ICON_DisplayYESNO($db->Field("IMPEDIR_RENOVACIONES"),true) . "</td> " .
						 " </tr>";
				}
				
				$db->FreeResultset();
				
				$db->destroy();
				
				echo "</table>";
		  
		   ?>
		
		
	   </div><!-- - caja datos -->
	   
	</div> <!-- contenido pricipal -->

<?php  display_copyright(); ?>
</div><!--bloque principal-->
 </div><!--bloque contenedor-->
       
</body>
</html>