<?php
	session_start();
		
	/*******
	  Historial de Cambios
	  
	  29 mar 2009: Se crea el archivo PHP para editar grupos de usuarios.
	  23 ago 2009: Se afina la opción junto con la edición	  
	  
     */		
		
	include ("../funcs.inc.php");

	check_usuario_firmado(); 

	// Coloca un encabezado HTML <head>
	include ("../basic/head_handler.php");
	include ("../basic/bd.class.php");
	include ("../privilegios.inc.php");
	include_language( "global_menus" );
	include_language( "serv_usuariosgrupos" ); 	// archivo de idioma
	
	HeadHandler( $LBL_TITLE_CAPTION, "../" );
	
	verificar_privilegio( PRIV_USERGROUPS, 1 );
	
	$id_grupo_created=0;
	$id_grupo_edited=0;
	
	$id_grupos_borrados = 0;
	
	if( isset($_GET["id_grupo_created"] ) )
		$id_grupo_created = $_GET["id_grupo_created"];
		
	if( isset($_GET["id_grupo_edited"] ) )
		$id_grupo_edited = $_GET["id_grupo_edited"];
		
	if( isset($_GET["id_grupos_borrados"] ) )
		$id_grupos_borrados = $_GET["id_grupos_borrados"];

?>

<SCRIPT language="JavaScript">

	function creargrupo()
	{
		var url = "serv_creargrupos.php";
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "create_group", 920, 550 );
	}
	
	function borrargrupos()
	{
		var sel_grupos = js_getElementByName("sel_grupos");
		
		if( sel_grupos )
		{
			if( sel_grupos.value != "" )
			{
				if( confirm("<?php echo $MSG_CONFIRM_BEFORE_DELETE;?>") )
				{
					var url = "serv_creargrupos.php?the_action=delete&grupos=" + sel_grupos.value
					
					js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "delete_group", 350, 350 );
				}
			}
			else
				alert( "<?php echo $MSG_NO_GROUPS_MARKED_TO_DELETE;?>" );

		}
			
	}
	
	function editar_grupo( id_grupo )
	{
		var url = "serv_creargrupos.php?the_action=edit&id_grupo=" + id_grupo;
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "edit_group", 920, 550 );
	}
	
	function click_CheckBoxes()
	{
		var aObjects = document.getElementsByTagName("input");
		var sel_grupos = document.getElementsByName("sel_grupos");
		var i;
		
		if( sel_grupos.length > 0 )
		{
			sel_grupos[0].value = "";
			
			for( i = 0; i<aObjects.length; i++ )
				if( aObjects[i].type == "checkbox" )
				{
					if( aObjects[i].checked )
					{
						if( sel_grupos[0].value == "" )
							sel_grupos[0].value = "@";
						else
							sel_grupos[0].value += ":";
						
						sel_grupos[0].value += aObjects[i].name.substr(4,15);
					}
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

		   <div id="buttonArea" align=right>
			<input class="boton" type="button" value="<?php echo $BTN_CREATE_GROUP; ?>" name="btnCrearGrupo" onClick="javascript:creargrupo()"; />
			<input class="boton" type="button" value="<?php echo $BTN_DELETE_GROUP; ?>" name="btnBorrarGrupo" onClick="javascript:borrargrupos()"; />
		   </div> 
		   
		   <?php 
				if( $id_grupo_created != 0 ) 
				{
					echo "<div class=caja_info>";
					echo " <strong>$SAVE_CREATED_DONE</strong>";
					echo "</div>";
				}
			  
				if( $id_grupo_edited != 0 )
				{
					echo "<div class=caja_info>";
					echo " <strong>$SAVE_EDIT_DONE</strong>";
					echo "</div>";
				}
				
				if( $id_grupos_borrados > 0 )
				{
					echo "<div class=caja_info>";
					echo " <strong>$DELETE_DONE</strong>";
					echo "</div>";
				}			
				
				$db = new DB();
			  
				$db->sql = "SELECT * FROM cfgusuarios_grupos " .
						   "WHERE ID_BIBLIOTECA=" . getsessionvar("id_biblioteca") . " " .
						   "ORDER BY ID_GRUPO";
					   
				$db->Open();
				
				$db->SetClassesForDisplay( "hilite_odd", "hilite_even" );
				
				echo "<input type=hidden class=hidden name='sel_grupos' id='sel_grupos' value=''>";
				
				echo "<table width=830px>";
				echo "<tr>" .
					  "<td class='cuadricula columna columnaEncabezado' width=15px>&nbsp;</td>" .
					  "<td class='cuadricula columna columnaEncabezado' width=25px>$LBL_ID_GRUPO</td>" .
					  "<td class='cuadricula columna columnaEncabezado' width=200px>$LBL_NOMBRE_GRUPO</td>" . 
					  "<td class='cuadricula columna columnaEncabezado' width=65px>$LBL_PERMITIR_PRESTAMOS</td>" . 
					  "<td class='cuadricula columna columnaEncabezado' width=70px>$LBL_MAX_DIAS_PRESTAMO</td>" . 
					  "<td class='cuadricula columna columnaEncabezado' width=70px>$LBL_MAX_ITEMS_PRESTADOS</td>" . 
					  "<td class='cuadricula columna columnaEncabezado' width=60px>$LBL_MAX_RESERVACIONES</td>" . 
					  "<td class='cuadricula columna columnaEncabezado' width=60px>$LBL_MAX_RENOVACIONES</td>" .
					  "<td class='cuadricula columna columnaEncabezado' width=60px>$LBL_PERMITIR_COMENTARIOS</td>" .					  
					  "<td class='cuadricula columna columnaEncabezado' width=60px>$LBL_SANCION_ECONOMICA</td>" . 
					  "<td class='cuadricula columna columnaEncabezado' width=55px>$LBL_SANCION_HORAS</td>" . 
					  "<td class='cuadricula columna columnaEncabezado' width=50px>$LBL_SANCION_ESPECIE</td>" . 
					 "</tr>";				
				
				while( $db->NextRow() ) 
				{ 
					$id_grupo 			 = $db->Field("ID_GRUPO");
					$nombre_grupo        = $db->Field("NOMBRE_GRUPO");
					$max_dias_prestamo   = $db->Field("MAX_DIAS_PRESTAMO");
					$max_items_prestados = $db->Field("MAX_ITEMS_PRESTADOS");
					$max_reservaciones   = $db->Field("MAX_RESERVACIONES");
					$max_renovaciones    = $db->Field("MAX_RENOVACIONES");
					
					$hilite_on = "";
					$hilite_off = "";
					
					if( $db->row["USUARIOS_ADMINISTRATIVOS"] == "S" )
					{
						$hilite_on = "<strong>";
						$hilite_off = "</strong>";					
					}
					
					$rowedited = "";
					
					if( $id_grupo == $id_grupo_edited )
						$rowedited = "trHilited";
						
					$rowedited .= $db->class_for_display;
					
					echo "<tr class='$rowedited' onMouseOver='javascript:this.className=\"hilite_onmouse_enter\";' onMouseOut='javascript:this.className=\"$rowedited\";'>" . 
						 " <td class='cuadricula columna'><input name='chk_$id_grupo' id='chk_$id_grupo' type=checkbox onClick='javascript:click_CheckBoxes();'></td> " .
						 " <td class='cuadricula columna'>$id_grupo</td> " .
						 " <td class='cuadricula columna'>$hilite_on<a href='javascript:editar_grupo($id_grupo);'>" . $nombre_grupo. "</a>$hilite_off</td> " .
						 " <td class='cuadricula columna' align='center'>" . ICON_DisplayYESNO($db->row["PERMITIR_PRESTAMOS"],true) . "</td> " .
						 " <td class='cuadricula columna'>$max_dias_prestamo</td> " .
						 " <td class='cuadricula columna'>$max_items_prestados</td> " .
						 " <td class='cuadricula columna'>$max_reservaciones</td> " .
						 " <td class='cuadricula columna'>$max_renovaciones</td> " .
						 " <td class='cuadricula columna' align='center'>" . ICON_DisplayYESNO($db->Field("PERMITIR_COMENTARIOS"),true) . "</td> " .
						 " <td class='cuadricula columna' align='center'>" . ICON_DisplayYESNO($db->Field("MULTA_ECONOMICA_SN"),true) . "</td> " .
						 " <td class='cuadricula columna' align='center'>" . ICON_DisplayYESNO($db->row["MULTA_HORAS_SN"],true) . "</td> " .
						 " <td class='cuadricula columna' align='center'>" . ICON_DisplayYESNO($db->row["MULTA_ESPECIE_SN"],true) . "</td> " .
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