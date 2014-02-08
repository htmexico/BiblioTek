<?php
	session_start();
		
	/*******
	  Historial de Cambios
	  
	  12 oct 2009: Se crea el archivo PHP para configurar catalogo de sanciones
	  
     */		
		
	include ("../funcs.inc.php");

	check_usuario_firmado(); 

	include ("../basic/bd.class.php");
	include ("../basic/currency.inc.php");
	include ("../privilegios.inc.php");
	
	include_language( "global_menus" );
	include_language( "conf_sanciones" ); 	// archivo de idioma

	// Coloca un encabezado HTML <head>
	include ("../basic/head_handler.php");	
	HeadHandler( $LBL_TITLE_CAPTION, "../" );
	
	verificar_privilegio( PRIV_CFG_SANCTIONS, 1 );
	
	$id_biblioteca = getsessionvar( "id_biblioteca" );
	
	$id_sancion_created = read_param( "id_sancion_created", 0 );
	$id_sancion_edited = read_param( "id_sancion_edited", 0 );
	
	$id_sanciones_borradas = read_param( "id_sanciones_borradas", 0 );

	$filter = read_param( "filter", "" );
	
?>

<SCRIPT language="JavaScript">

	function activateFilter( obj )
	{
		var url = "conf_sanciones.php?filter=" + obj.value;
		
		js_ChangeLocation( url );
	}

	function crearsancion()
	{
		var url = "conf_crearsanciones.php";
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "create_sanction", 920, 550 );
	}
	
	function borrarsanciones()
	{
		var sel_sanciones = js_getElementByName("sel_sanciones");
		
		if( sel_sanciones )
		{
			if( sel_sanciones.value != "" )
			{
				if( confirm("<?php echo $MSG_CONFIRM_BEFORE_DELETE;?>") )
				{
					var url = "conf_crearsanciones.php?the_action=delete&sanciones=" + sel_sanciones.value
					
					js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "delete_group", 350, 350 );
				}
			}
			else
				alert( "<?php echo $MSG_NO_GROUPS_MARKED_TO_DELETE;?>" );

		}
	}
	
	function editar_def_sancion( tipo_sancion )
	{
		var url = "conf_crearsanciones.php?the_action=edit&tipo_sancion=" + tipo_sancion;
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "edit_persona", 920, 550 );
	}
	
	function click_CheckBoxes()
	{
		var aObjects = document.getElementsByTagName("input");
		var sel_sanciones = js_getElementByName("sel_sanciones");
		var i;
		
		if( sel_sanciones )
		{
			sel_sanciones.value = "";
			
			for( i = 0; i<aObjects.length; i++ )
				if( aObjects[i].type == "checkbox" )
					if( aObjects[i].checked )
					{
						if( sel_sanciones.value == "" )
							sel_sanciones.value = "@";
						else
							sel_sanciones.value += ":";
						
						sel_sanciones.value += aObjects[i].name.substr(4,15);
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
						<option value="E" <?php echo ($filter=="E") ? "selected" : "";?> ><?php echo $LBL_FILTER_OPT1;?></option>";
						<option value="S" <?php echo ($filter=="S") ? "selected" : "";?> ><?php echo $LBL_FILTER_OPT2;?></option>";
						<option value="M" <?php echo ($filter=="M") ? "selected" : "";?> ><?php echo $LBL_FILTER_OPT3;?></option>";
					</select>
				</div>
		   </div>
		   
		   <div id="buttonArea" style='float:right'>
			<input class="boton" type="button" value="<?php echo $BTN_CREATE_PERSON; ?>" name="btnCrearPersona" onClick="javascript:crearsancion();">
			<input class="boton" type="button" value="<?php echo $BTN_DELETE_PERSON; ?>" name="btnBorrarPersona" onClick="javascript:borrarsanciones();">
		   </div> 
		   
		   <?php 
				$db = new DB();

				if( $id_sancion_created != 0 ) 
				{
					echo "<div class=caja_info>";
					echo " <strong>$SAVE_CREATED_DONE</strong>";
					echo "</div>";
				}
			  
				if( $id_sancion_edited != 0 )
				{
					echo "<div class=caja_info>";
					echo " <strong>$SAVE_EDIT_DONE</strong>";
					echo "</div>";
				}
				
				if( $id_sanciones_borradas > 0 )
				{
					echo "<div class=caja_info>";
					echo " <strong>$DELETE_DONE</strong>";
					echo "</div>";
				}			
		  
				$db->sql = "SELECT a.* " . 
						   "FROM cfgsanciones a " .
						   "WHERE a.ID_BIBLIOTECA=$id_biblioteca ";
						   
				if( $filter != "" )
				{
					if( $filter == "E" )
						$db->sql .= " and a.ECONOMICA_SN='S' ";
					else if( $filter == "S" )
						$db->sql .= " and a.LABOR_SOCIAL_SN='S' ";
					else if( $filter == "M" )
						$db->sql .= " and a.ESPECIE_SN='S' ";
				}
						   
				$db->sql .= "ORDER BY a.TIPO_SANCION";

				$db->Open();
				
				$db->SetClassesForDisplay( "hilite_odd", "hilite_even" );
				
				echo "<input type=hidden class=hidden name='sel_sanciones' id='sel_sanciones' value=''>";
				
				echo "<table width=830px>";
				echo "<tr>" .
					 " <td class='cuadricula columna columnaEncabezado' width='15px'>&nbsp;</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='25px'>$LBL_TIPOSANCTION</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='300px'>$LBL_DESCRIPTION</td>" . 
					 " <td align='center' class='cuadricula columna columnaEncabezado' width='60px'>$LBL_MONETARY</td>" . 
					 " <td align='center' class='cuadricula columna columnaEncabezado' width='60px'>$LBL_MONETARY_FIXED</td>" . 
					 " <td align='center' class='cuadricula columna columnaEncabezado' width='60px'>$LBL_MONETARY_VAR</td>" .
					 " <td align='center' class='cuadricula columna columnaEncabezado' width='60px'>$LBL_SOCIAL_WORK</td>" .
					 " <td align='center' class='cuadricula columna columnaEncabezado' width='60px'>$LBL_SOCIAL_WORK_HRS</td>" . 
					 " <td align='center' class='cuadricula columna columnaEncabezado' width='60px'>$LBL_MATERIAL</td>" . 
					 "</tr>";
				
				$id_sancion = 0;
				
				while( $db->NextRow() ) 
				{ 
					$id_sancion++;
					
					$tipo_sancion   = $db->row["TIPO_SANCION"];
					$descripcion 	= $db->row["DESCRIPCION"];
					
					$hilite_on = "";
					$hilite_off = "";
					
					if( $db->row["ECONOMICA_SN"] == "S" )
					{
						$hilite_on = "<strong>";
						$hilite_off = "</strong>";					
					}
					
					$rowedited = "";
					
					if( $id_sancion == $id_sancion_edited )
						$rowedited = "trHilited";
						
					$rowedited .= $db->class_for_display;
					
					$monto_fijo 	= "";
					$monto_x_dia	= "";
					
					if( $db->row["ECONOMICA_MONTO_FIJO"] != 0 )
						$monto_fijo 	= currency( $db->row["ECONOMICA_MONTO_FIJO"] ) ;

					if( $db->row["ECONOMICA_MONTO_X_DIA"] != 0 )
						$monto_x_dia 	= currency( $db->row["ECONOMICA_MONTO_X_DIA"] ) ;
					
					$horas_labor_social = "";
					
					if( $db->row["LABOR_SOCIAL_HORAS"] != 0 )
						$horas_labor_social = $db->row["LABOR_SOCIAL_HORAS"];
						
					echo "<tr class='$rowedited' onMouseOver='javascript:this.className=\"hilite_onmouse_enter\";' onMouseOut='javascript:this.className=\"$rowedited\";'>" . 
						 " <td class='cuadricula columna'><input name='chk_$tipo_sancion' id='chk_$tipo_sancion' type=checkbox onClick='javascript:click_CheckBoxes();'></td> " .
						 " <td class='cuadricula columna'>$hilite_on<a href='javascript:editar_def_sancion(\"$tipo_sancion\");'>" . $tipo_sancion. "</a>$hilite_off</td> " .
						 " <td class='cuadricula columna'>$hilite_on<a href='javascript:editar_def_sancion(\"$tipo_sancion\");'>" . $descripcion. "</a>$hilite_off</td> " .
						 " <td align='center' class='cuadricula columna'>" . ICON_DisplayYESNO($db->Field("ECONOMICA_SN"),true) . "</td> " .
						 " <td align='center' class='cuadricula columna'>$monto_fijo</td> " .
						 " <td align='center' class='cuadricula columna'>$monto_x_dia</td> " .
						 " <td align='center' class='cuadricula columna'>" . ICON_DisplayYESNO($db->Field("LABOR_SOCIAL_SN"),true) . "</td> " .
						 " <td align='center' class='cuadricula columna'>$horas_labor_social</td> " .
						 " <td align='center' class='cuadricula columna'>" . ICON_DisplayYESNO($db->Field("ESPECIE_SN"),true) . "</td> " .
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