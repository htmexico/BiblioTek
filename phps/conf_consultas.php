<?php
	session_start();
		
	/*******
	  Historial de Cambios
	  
	  14 sep 2009: Se crea el archivo PHP para crear consultas.
	  
     */		
		
	include ("../funcs.inc.php");

	check_usuario_firmado(); 

	include ("../basic/bd.class.php");
	include ("../privilegios.inc.php");
	
	include_language( "global_menus" );
	include_language( "conf_consultas" ); 	// archivo de idioma

	// Coloca un encabezado HTML <head>
	include ("../basic/head_handler.php");	
	HeadHandler( $LBL_TITLE_CAPTION, "../" );
	
	verificar_privilegio( PRIV_CFG_QUERIES, 1 );
	
	$id_biblioteca = getsessionvar( "id_biblioteca" );
	
	$id_consulta_created = read_param( "id_consulta_created", 0 );
	$id_consulta_edited = read_param( "id_consulta_edited", 0 );
	$id_consultas_borradas = read_param( "id_consultas_borradas", 0 );

	$filter = read_param( "filter", "" );
	
?>

<SCRIPT language="JavaScript">

	function crearconsulta()
	{
		var url = "conf_crearconsultas.php";
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "create_consulta", 950, 550 );
	}
	
	function borrarconsultas()
	{
		var sel_consultas = js_getElementByName("sel_consultas");
		
		if( sel_consultas )
		{
			if( sel_consultas.value != "" )
			{
				if( confirm("<?php echo $MSG_CONFIRM_BEFORE_DELETE;?>") )
				{
					var url = "conf_crearconsultas.php?the_action=delete&consultas=" + sel_consultas.value
					
					js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "delete_consulta", 350, 350 );
				}
			}
			else
				alert( "<?php echo $MSG_NO_QUERIES_MARKED_TO_DELETE;?>" );

		}
	}
	
	function editar_consulta( id_consulta )
	{
		var url = "conf_crearconsultas.php?the_action=edit&id_consulta=" + id_consulta;
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "edit_consulta", 950, 550 );
	}
	
	function click_CheckBoxes()
	{
		var aObjects = document.getElementsByTagName("input");
		var sel_consultas = js_getElementByName("sel_consultas");
		var i;
		
		if( sel_consultas )
		{
			sel_consultas.value = "";
			
			for( i = 0; i<aObjects.length; i++ )
				if( aObjects[i].type == "checkbox" )
					if( aObjects[i].checked )
					{
						if( sel_consultas.value == "" )
							sel_consultas.value = "@";
						else
							sel_consultas.value += ":";
						
						sel_consultas.value += aObjects[i].name.substr(4,15);
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

		   <div id="buttonArea" style='float:right'>
			<input class="boton" type="button" value="<?php echo $BTN_CREATE_QRY; ?>" name="btnCrearConsulta" onClick="javascript:crearconsulta();">
			<input class="boton" type="button" value="<?php echo $BTN_DELETE_QRY; ?>" name="btnBorrarConsulta" onClick="javascript:borrarconsultas();">
		   </div> 
		   
		   <?php 
				if( $id_consulta_created != 0 ) 
				{
					echo "<div class=caja_info>";
					echo " <strong>$SAVE_CREATED_DONE</strong>";
					echo "</div>";
				}
			  
				if( $id_consulta_edited != 0 )
				{
					echo "<div class=caja_info>";
					echo " <strong>$SAVE_EDIT_DONE</strong>";
					echo "</div>";
				}
				
				if( $id_consultas_borradas > 0 )
				{
					echo "<div class=caja_info>";
					echo " <strong>$DELETE_DONE</strong>";
					echo "</div>";
				}			
		  
				$db = new DB();
		  
				$db->sql = "SELECT a.* " . 
						   "FROM cfgconsultas_catalogo a " .
						   "WHERE a.ID_BIBLIOTECA=$id_biblioteca ";
						   
				$db->sql .= "ORDER BY a.ID_CONSULTA";

				$db->Open();
				
				$db->SetClassesForDisplay( "hilite_odd", "hilite_even" );
				
				echo "<input type=hidden class=hidden name='sel_consultas' id='sel_consultas' value=''>";
				
				echo "<table width=830px>";
				echo "<tr>" .
					 " <td class='cuadricula columna columnaEncabezado' width='15px'>&nbsp;</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='20px'>$LBL_IDQRY</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='170px'>$LBL_QRYNAME</td>" . 
					 " <td class='cuadricula columna columnaEncabezado' width='150px'>$LBL_QRYCHOICES</td>" . 
					 " <td class='cuadricula columna columnaEncabezado' width='60px' align='center'>$LBL_QRYPAGERECORDS</td>" . 
					 " <td class='cuadricula columna columnaEncabezado' width='55px' align='center'>$LBL_IS_4_ADMINISTRATIVE</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='55px' align='center'>$LBL_IS_4_OPAC</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='55px' align='center'>$LBL_IS_4_READERS</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='50px' align='center'>$LBL_IS_ACTIVE</td>" . 
					 "</tr>";
				
				while( $db->NextRow() ) 
				{ 
					$id_consulta   	 	 = $db->row["ID_CONSULTA"];
					$descrip_consulta    = $db->row["DESCRIPCION"];
					
					// opciones de las consultas
					$descrip = "";
					
					if( $db->row["INCLUIR_PALABRASCLAVE"]=="S" )
						$descrip .= $LBL_INCLUDE_KEYWORDS;

					if( $db->row["INCLUIR_TITULO"]=="S" )
					{
						$descrip .= (($descrip != "") ? ", " : "") . $LBL_INCLUDE_TITLE;
					}
					
					if( $db->row["INCLUIR_AUTOR"]=="S" )
						$descrip .= (($descrip != "") ? ", " : "") . $LBL_INCLUDE_AUTHOR;

					if( $db->row["INCLUIR_MATERIAS"]=="S" )
						$descrip .= (($descrip != "") ? ", " : "") . $LBL_INCLUDE_SUBJECTS;

					if( $db->row["INCLUIR_SIGNATURA_TOPOG"]=="S" )
						$descrip .= (($descrip != "") ? ", " : "") . $LBL_INCLUDE_CALLNUMBER;
						
					if( $db->row["INCLUIR_ISBN"]=="S" )
						$descrip .= (($descrip != "") ? ", " : "") . $LBL_INCLUDE_ISBN;

					if( $db->row["INCLUIR_ISSN"]=="S" )
						$descrip .= (($descrip != "") ? ", " : "") . $LBL_INCLUDE_ISSN;						
										
					$hilite_on = "";
					$hilite_off = "";
					
					if( $db->row["OPAC"] == "S" )
					{
						$hilite_on = "<strong>";
						$hilite_off = "</strong>";					
					}
					
					$rowedited = "";
					
					if( $id_consulta == $id_consulta_edited )
						$rowedited = "trHilited";
						
					$rowedited .= $db->class_for_display;
					
					echo "<tr class='$rowedited' onMouseOver='javascript:this.className=\"hilite_onmouse_enter\";' onMouseOut='javascript:this.className=\"$rowedited\";'>" . 
						 " <td class='cuadricula columna'><input name='chk_$id_consulta' id='chk_$id_consulta' type=checkbox onClick='javascript:click_CheckBoxes();'></td> " .
						 " <td class='cuadricula columna'>$id_consulta</td> " .
						 " <td class='cuadricula columna'>$hilite_on<a href='javascript:editar_consulta($id_consulta);'>" . $descrip_consulta. "</a>$hilite_off</td> " .
						 " <td class='cuadricula columna' align='left'>$descrip</td> " .
						 " <td class='cuadricula columna' align='center'>" . $db->row["REGISTROS_X_PAGINA"] . "</td> " .
						 " <td class='cuadricula columna' align='center'>" . ICON_DisplayYESNO($db->row["ADMINISTRATIVA"],true) . "</td> " .
						 " <td class='cuadricula columna' align='center'>" . ICON_DisplayYESNO($db->row["OPAC"],true) . "</td> " .
						 " <td class='cuadricula columna' align='center'>" . ICON_DisplayYESNO($db->row["LECTORES"],true) . "</td> " .
						 " <td class='cuadricula columna' align='center'>" . ICON_DisplayYESNO($db->row["ACTIVA"],true) . "</td> " .
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