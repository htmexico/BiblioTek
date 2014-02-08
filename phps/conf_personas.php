<?php
	session_start();
		
	/*******
	  Historial de Cambios
	  
	  07 sep 2009: Se crea el archivo PHP para editar personas.
	  
     */		
		
	include ("../funcs.inc.php");

	check_usuario_firmado(); 

	include ("../basic/bd.class.php");
	include ("../privilegios.inc.php");
	
	include_language( "global_menus" );
	include_language( "conf_personas" ); 	// archivo de idioma

	// Coloca un encabezado HTML <head>
	include ("../basic/head_handler.php");	
	HeadHandler( $LBL_TITLE_CAPTION, "../" );
	
	verificar_privilegio( PRIV_PERSONS, 1 );
	
	$id_biblioteca = getsessionvar( "id_biblioteca" );
	
	$id_persona_created = read_param( "id_persona_created", 0 );
	$id_persona_edited = read_param( "id_persona_edited", 0 );
	
	$id_personas_borradas = read_param( "id_personas_borradas", 0 );

	$filter = read_param( "filter", "" );
	
?>

<SCRIPT language="JavaScript">

	function activateFilter( obj )
	{
		var url = "conf_personas.php?filter=" + obj.value;
		
		js_ChangeLocation( url );
	}

	function crearpersona()
	{
		var url = "conf_crearpersonas.php";
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "create_persona", 920, 550 );
	}
	
	function borrarpersonas()
	{
		var sel_personas = js_getElementByName("sel_personas");
		
		if( sel_personas )
		{
			if( sel_personas.value != "" )
			{
				if( confirm("<?php echo $MSG_CONFIRM_BEFORE_DELETE;?>") )
				{
					var url = "conf_crearpersonas.php?the_action=delete&personas=" + sel_personas.value
					
					js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "delete_group", 350, 350 );
				}
			}
			else
				alert( "<?php echo $MSG_NO_GROUPS_MARKED_TO_DELETE;?>" );

		}
	}
	
	function editar_persona( id_persona )
	{
		var url = "conf_crearpersonas.php?the_action=edit&id_persona=" + id_persona;
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "edit_persona", 920, 550 );
	}
	
	function click_CheckBoxes()
	{
		var aObjects = document.getElementsByTagName("input");
		var sel_personas = js_getElementByName("sel_personas");
		var i;
		
		if( sel_personas )
		{
			sel_personas.value = "";
			
			for( i = 0; i<aObjects.length; i++ )
				if( aObjects[i].type == "checkbox" )
					if( aObjects[i].checked )
					{
						if( sel_personas.value == "" )
							sel_personas.value = "@";
						else
							sel_personas.value += ":";
						
						sel_personas.value += aObjects[i].name.substr(4,15);
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
					
					<?php	
						$db = new DB( "SELECT ID_TIPOPERSONA, DESCRIPCION, DESCRIPCION_ENG, DESCRIPCION_POR FROM cfgtipospersona ORDER BY ID_TIPOPERSONA " );
						
						while( $db->NextRow() )
						{ 
							$str_selected = ($filter==$db->row['ID_TIPOPERSONA']) ? "selected" : "";
							
							echo "<option value='" . $db->row['ID_TIPOPERSONA'] . "' $str_selected>" . 
								get_translation( $db->row["DESCRIPCION"], $db->row["DESCRIPCION_ENG"], $db->row["DESCRIPCION_POR"] ) . " &nbsp;&nbsp;&nbsp;</option>";
						}
						
						$db->Close();
					?>

					</select>
				</div>
		   </div>
		   
		   <div id="buttonArea" style='float:right'>
			<input class="boton" type="button" value="<?php echo $BTN_CREATE_PERSON; ?>" name="btnCrearPersona" onClick="javascript:crearpersona();">
			<input class="boton" type="button" value="<?php echo $BTN_DELETE_PERSON; ?>" name="btnBorrarPersona" onClick="javascript:borrarpersonas();">
		   </div> 
		   
		   <?php 
				if( $id_persona_created != 0 ) 
				{
					echo "<div class=caja_info>";
					echo " <strong>$SAVE_CREATED_DONE</strong>";
					echo "</div>";
				}
			  
				if( $id_persona_edited != 0 )
				{
					echo "<div class=caja_info>";
					echo " <strong>$SAVE_EDIT_DONE</strong>";
					echo "</div>";
				}
				
				if( $id_personas_borradas > 0 )
				{
					echo "<div class=caja_info>";
					echo " <strong>$DELETE_DONE</strong>";
					echo "</div>";
				}			
		  
				$db->sql = "SELECT a.*, b.PROVEEDOR, b.BIBLIOTECA, b.DESCRIPCION, b.DESCRIPCION_ENG, b.DESCRIPCION_POR " . 
						   "FROM personas a " .
						   "  LEFT JOIN cfgtipospersona b ON (b.ID_TIPOPERSONA=a.ID_TIPOPERSONA) " .
						   "WHERE ID_BIBLIOTECA=$id_biblioteca ";
						   
				if( $filter != "" )
				{
					$db->sql .= " and a.ID_TIPOPERSONA='$filter' ";
				}
						   
				$db->sql .= "ORDER BY a.ID_PERSONA";

				$db->Open();
				
				$db->SetClassesForDisplay( "hilite_odd", "hilite_even" );
				
				echo "<input type=hidden class=hidden name='sel_personas' id='sel_personas' value=''>";
				
				echo "<table width=830px>";
				echo "<tr>" .
					 " <td class='cuadricula columna columnaEncabezado' width='15px'>&nbsp;</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='25px'>$LBL_IDPERSONA</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='70px'>$LBL_TYPEPERSON</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='150px'>$LBL_FULLNAME</td>" . 
					 " <td class='cuadricula columna columnaEncabezado' width='80px'>$LBL_ADDRESS</td>" . 
					 " <td class='cuadricula columna columnaEncabezado' width='70px'>$LBL_CITY</td>" . 
					 " <td class='cuadricula columna columnaEncabezado' width='60px'>$LBL_PROVINCE</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='80px'>$LBL_CONTACT</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='40px'>$LBL_SUPPLIER</td>" . 
					 " <td class='cuadricula columna columnaEncabezado' width='40px'>$LBL_LIBRARY</td>" . 					 
					 "</tr>";
				
				while( $db->NextRow() ) 
				{ 
					$id_persona   	 	 = $db->row["ID_PERSONA"];
					$descrip_tipopersona = get_translation( $db->row["DESCRIPCION"], $db->row["DESCRIPCION_ENG"], $db->row["DESCRIPCION_POR"] );
					
					$nombre = $db->row["APELLIDOS"] . " " . $db->row["NOMBRES"];
					
					$domicilio = $db->row["DOMICILIO"];
					$ciudad    = $db->row["CIUDAD"];
					$provincia = $db->row["PROVINCIA"];
					$pais      = $db->row["PAIS"];
					$contacto  = $db->row["CONTACTO"];
					
					$hilite_on = "";
					$hilite_off = "";
					
					if( $db->row["BIBLIOTECA"] == "S" )
					{
						$hilite_on = "<strong>";
						$hilite_off = "</strong>";					
					}
					
					$rowedited = "";
					
					if( $id_persona == $id_persona_edited )
						$rowedited = "trHilited";
						
					$rowedited .= $db->class_for_display;
					
					echo "<tr class='$rowedited' onMouseOver='javascript:this.className=\"hilite_onmouse_enter\";' onMouseOut='javascript:this.className=\"$rowedited\";'>" . 
						 " <td class='cuadricula columna'><input name='chk_$id_persona' id='chk_$id_persona' type=checkbox onClick='javascript:click_CheckBoxes();'></td> " .
						 " <td class='cuadricula columna'>$id_persona</td> " .
						 " <td class='cuadricula columna'>$hilite_on<a href='javascript:editar_persona($id_persona);'>" . $descrip_tipopersona. "</a>$hilite_off</td> " .
						 " <td class='cuadricula columna' align='left'>$nombre</td> " .
						 " <td class='cuadricula columna'>$domicilio</td> " .
						 " <td class='cuadricula columna'>$ciudad</td> " .
						 " <td class='cuadricula columna'>$provincia</td> " .
						 " <td class='cuadricula columna'>$contacto</td> " .
						 " <td class='cuadricula columna'>" . ICON_DisplayYESNO($db->Field("PROVEEDOR"),true) . "</td> " .
						 " <td class='cuadricula columna' align='center'>" . ICON_DisplayYESNO($db->Field("BIBLIOTECA"),true) . "</td> " .
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