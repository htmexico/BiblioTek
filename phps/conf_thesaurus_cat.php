<?php
	session_start();
		
	/*******
	 Historial de Cambios
		  
		30 mar 2009: Se crea como parte de las funciones de config. de Tesauro
		30 apr 2009: Se modifica ubicación de los DIVS (primero genéricos y luego los de la categoría
					 Se modifica un poco la edición de términos (ahi pendientes ahi)
		
	 */		
		
	include ("../funcs.inc.php");
	include ("../actions.inc.php");
	
	include ("../basic/bd.class.php");
	
	check_usuario_firmado(); 
	
	$id_categoria    = read_param("id_categoria", "" );
	$id_subcategoria = read_param("id_subcategoria", "" );
	
    $descrip_categoria = "";
	$descrip_subcategoria = "";
		
	include_language( "conf_thesaurus" );
	include_language( "global_menus" );	

	// Draw an html head
	include "../basic/head_handler.php";
	HeadHandler( "$LABEL_HEADER_V2", "../" );
	
	$the_action = read_param( "action", "" );
	
	if( $the_action == "delete" )
	{
		$terminos = "";
		$grupos_borrados = 0;
		
		if( isset($_GET["terminos"]) )
		{
			$terminos = $_GET["terminos"];
			
			$terminos = str_replace( "@", "ID_TERMINO=", $terminos ); // 1st ocurrence
			$terminos = str_replace( ":", " or ID_TERMINO=", $terminos ); // other ocurrences
			
			$id_red = getsessionvar("id_red");
			
			$db = new DB();

			$db->ExecSQL( "DELETE FROM tesauro_terminos_categorias WHERE ID_RED=$id_red and ($terminos) " );
			
			$db->ExecSQL( "DELETE FROM tesauro_terminos WHERE ID_RED=$id_red and ($terminos) " );
			$terminos_borrados = $db->rowsAffected;
			
			$db->Destroy();
			
			agregar_actividad_de_usuario( CFG_CHANGE_THESAURUS, "Eliminó términos: $terminos" );
		}
		
		$param_sub = "";
		
		if( $id_subcategoria != "" )
			$param_sub = "&id_subcategoria=$id_subcategoria";
		
		ges_redirect( "conf_thesaurus_cat.php?id_categoria=$id_categoria" . "$param_sub&deleted_terms=$terminos_borrados" ); //=$grupos_borrados" );
	}

?>

<SCRIPT language="JavaScript">
	
	function regresarVista()
	{
		if( "<?php echo $id_subcategoria;?>" != "" )
			js_ChangeLocation( "conf_thesaurus_cat.php?id_categoria=" + <?php echo $id_categoria;?> );
		else
			js_ChangeLocation( "conf_thesaurus.php" );
			
	}
	
	function click_CheckBoxes()
	{
		var aObjects = document.getElementsByTagName("input");
		var sel_terminos = document.getElementsByName("sel_terminos");
		var i;
		
		if( sel_terminos.length > 0 )
		{
			sel_terminos[0].value = "";
			
			for( i = 0; i<aObjects.length; i++ )
				if( aObjects[i].type == "checkbox" )
				{
					if( aObjects[i].checked )
					{
						if( sel_terminos[0].value == "" )
							sel_terminos[0].value = "@";
						else
							sel_terminos[0].value += ":";
						
						sel_terminos[0].value += aObjects[i].name.substr(4,15);
					}
				}
		}
		
	}	
	
	function createNewTerm( id_subcategoria )
	{
		var nwidth = screen.width;
		var nheight = screen.height; 
		var url = "conf_tesauro_termino.php?id_categoria=<?php echo $id_categoria;?>";
		
		if( id_subcategoria != 0 )
			url += "&id_subcategoria=" + id_subcategoria;
		
		window.open( url, "crear_termino", "WIDTH=" + (nwidth-50) + ",HEIGHT=" + (nheight-120) + ",TOP=20,LEFT=30,resizable=yes,scrollbars=yes,status=yes" );
			
		window.status='';		
	}
	
	function editTerm( id_subcategoria, id_termino )
	{
		var url = "conf_tesauro_termino.php?action=edit&id_categoria=<?php echo $id_categoria;?>&id_termino=" + id_termino;
		
		if( id_subcategoria != 0 )
			url += "&id_subcategoria=" + id_subcategoria;
			
		if( <?php echo allow_use_of_popups() ? "1" : "0"; ?> )
		{
			var nwidth = screen.width;
			var nheight = screen.height; 

			window.open(url, "crear_termino", "WIDTH=" + (nwidth-50) + ",HEIGHT=" + (nheight-120) + ",TOP=20,LEFT=30,resizable=yes,scrollbars=yes,status=yes" );
		}
		else
			js_ChangeLocation( url );
			
		window.status='';		
	}
	
	function deleteTerms()
	{
		var sel_terminos = js_getElementByName("sel_terminos");
		
		if( sel_terminos )
		{
			if( sel_terminos.value != "" )
			{
				if( confirm( octal("<?php echo $MSG_CONFIRM_BEFORE_DELETE;?>") ) )
				{
					var url = "conf_thesaurus_cat.php?action=delete&id_categoria=<?php echo $id_categoria; echo ($id_subcategoria!="") ? "&id_subcategoria=$id_subcategoria" : "";  ?>&terminos=" + sel_terminos.value
					
					js_ChangeLocation( url );
				}
			}
			else
				alert( octal("<?php echo $MSG_NO_TERMS_MARKED_TO_DELETE;?>" ) );
		}
	}

</SCRIPT>

<STYLE type="text/css">

  #contenido_adicional
  {
	float: left;
	border: 0px solid green;
	padding: 0px;
  }
  
  #contenido_principal
  {
	float: right;
	border: 0px solid red;
	clear:none;
	padding: 0px;
  }  
  
  #buttonarea { float: right; border: 0px solid red; left:0em;  width: auto; }; 
  #btnActualizar { position:absolute; left:0em; } 
  #btnCancelar { position:absolute; left:17em; } 
  
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
	display_menu( "../" ); 
   
	// open DB object
	$db = new DB();
   
	$db->sql =  "SELECT a.DESCRIPCION " . 
				"FROM tesauro_categorias a " .
				"WHERE a.ID_RED=" . getsessionvar("id_red") . " and a.ID_CATEGORIA=$id_categoria ";
	$db->Open();
	
	if( $db->NextRow() )
	{
		$descrip_categoria = $db->row["DESCRIPCION"];
	}		
	
	$db->FreeResultset();
	
 ?>

<div id="bloque_principal"> <!-- inicia contenido -->

<div id="contenido_adicional">
	<h2><?php echo $descrip_categoria; ?></h2><br>

	<div class="resaltados" >
		<h2><?php echo $LBL_MACRO_TERMS;?></h2>
		<hr>

		<table width=100%>
			
			<?php
			
				if( $id_subcategoria == 0 )
				{
					$padres = Array();
					
					$db->sql = "SELECT a.*, b.TERMINO, b.CODIGO_CORTO, b.DESCRIPCION, b.TERMINO_PADRE " . 
								"FROM tesauro_terminos_categorias a " .
								"   LEFT JOIN tesauro_terminos b ON (b.ID_RED=a.ID_RED and b.ID_TERMINO=a.ID_TERMINO) " .
								"WHERE a.ID_RED=" . getsessionvar("id_red") . " and a.ID_CATEGORIA=$id_categoria and (b.MACROTERMINO='S') " . 
								"ORDER BY b.TERMINO";
						   
					$db->Open();			
					
					$i = 0;
					
					while( $db->NextRow() ) 
					{ 
						$id_termino     = $db->Field("ID_TERMINO");
						$codigo_corto   = $db->Field("CODIGO_CORTO");
						$termino   	    = $db->Field("TERMINO");
						
						$padres[$i++] = Array( $id_termino, $termino );
						
						if( $id_termino == $id_subcategoria ) 
						{
							echo "<tr><td><strong>$termino&nbsp;&raquo;</strongx></td></tr>";		
							$descrip_subcategoria = $termino;
						}
						else
							echo "<tr><td><a href='conf_thesaurus_cat.php?id_categoria=$id_categoria&id_subcategoria=$id_termino'>$termino $codigo_corto</a></td></tr>";		
					}
					
					if( $db->numRows == 0 )
						echo "<tr><td class='column'>$NO_MACROTERMS</td></tr>";
	
					$db->FreeResultset();
				}
				else
				{
					echo "En este nivel del Tesauro no hay macrot&eacute;rminos.";
				}
				
			?>
			
		</table>
	</div>	
	<br>
	<input type=button class=boton value='<?php echo $BTN_GOBACK;?>' onClick='javascript:regresarVista();'>
 
</div> <!-- contenido_adicional -->
 
<div id="contenido_principal">
	 
		<input type='hidden' class='hidden' name='sel_terminos' id='sel_terminos'>
		<?php
			/* MOSTRAR TÉRMINOS GENERICOS DE LA CATEGORIA */
			if( $id_subcategoria == 0 )
			{
				echo "\n<!-- INICIA - DIV DE GENERICOS -->";
				echo "<div class='caja_datos'>";
					  
				echo "<p>$LABEL_THESAURUS_INTRO_GEN</p>";

				echo "<div id=buttonarea>";
				echo "	<input type=button class=boton value='$BTN_CREATE_NEW_TERM' onClick='javascript:createNewTerm(0);'>";
				echo "	<input type=button class=boton value='$BTN_DELETE_TERM' onClick='javascript:deleteTerms();'>";
				echo "</div>";
				
				echo "<br>";
				  
				echo "<table width='100%' border='1'>";
			
				echo "<tr>" .
					  "<td class='cuadricula columna columnaEncabezado' width='5%'>&nbsp;</td>" .
					  "<td class='cuadricula columna columnaEncabezado' width='25%'>$LBL_TERM</td>" .
					  "<td class='cuadricula columna columnaEncabezado' width='15%'>$LBL_CODE</td>" .					   
					  "<td class='cuadricula columna columnaEncabezado' width='20%'>$LBL_USE_INSTEAD</td>" . 
					  "<td class='cuadricula columna columnaEncabezado' width='15%'>$LBL_SOURCE</td>" . 
					  "<td class='cuadricula columna columnaEncabezado' width='20%'>$LBL_SOURCE_NOTES</td>" . 
					 "</tr>";			
			
				// diccionario de terminos
				// se removio del WHERE and (b.TERMINO_PADRE is NULL and b.MACROTERMINO='N')
				//   porque no salían nunca los macroterminos para actualizarse
				$db->sql = 	"SELECT a.*, b.TERMINO, b.CODIGO_CORTO, b.DESCRIPCION, b.TERMINO_PADRE, b.USAR, b.FUENTE_AGENCIA, b.FUENTE_NOTAS, b.OBSOLETO " . 
							"FROM tesauro_terminos_categorias a " .
							"   LEFT JOIN tesauro_terminos b ON (b.ID_RED=a.ID_RED and b.ID_TERMINO=a.ID_TERMINO) " .
							"WHERE a.ID_RED=" . getsessionvar("id_red") . " and a.ID_CATEGORIA=$id_categoria ";
				$db->sql .= "ORDER BY b.CODIGO_CORTO";	   
				$db->Open();
			
				while( $db->NextRow() ) 
				{ 
					$id_termino     = $db->row["ID_TERMINO"];
					$codigo_corto   = $db->row["CODIGO_CORTO"];
					$termino   	    = $db->row["TERMINO"];
					$termino_padre  = $db->row["TERMINO_PADRE"];
					$usar 		    = $db->row["USAR"];
					$fuente_agencia = $db->row["FUENTE_AGENCIA"];
					$fuente_notas	= $db->row["FUENTE_NOTAS"];
					
					$link = "javascript:editTerm(0,$id_termino);";
					
					echo "<tr>" .
						  "<td class='cuadricula columna'><input name='chk_$id_termino' id='chk_$id_termino' type=checkbox onClick='javascript:click_CheckBoxes();'></td> " .
						  "<td class='cuadricula columna'><a href='$link' onMouseOut='javascript:js_Status(\"\"); return true;' onMouseOver='javascript:js_Status(\"Editar\"); return true;'>$termino</a></td>" . 
						  "<td class='cuadricula columna'>$codigo_corto</td>" .						  
						  "<td class='cuadricula columna'>$usar</td>" . 
						  "<td class='cuadricula columna'>$fuente_agencia</td>" . 
						  "<td class='cuadricula columna'>$fuente_notas</td>" . 
						 "</tr>";
				}
				
				if( $db->numRows == 0 )
				{
					echo "<tr>" .
						  "<td class='cuadricula columna' colspan=6>$NO_GENERAL_TERMS</td>" . 
						 "</tr>";
				}
				
				$db->FreeResultset();
				
				echo "</table>";
				echo "</div>";
				echo "<!-- FINALIZA - DIV DE GENERICOS -->";
				
			}
			
	
		   ?>

	<?php 
		
		if( $id_subcategoria != 0 )
		{
			/* MOSTRAR SUBCATEGORIZADOS */
			echo "<br>";
			echo "<!-- INICIA DIV  DE CATEGORIZADOS  -->";
			echo "<div class='caja_datos'>";
			
			echo "<p>$LABEL_THESAURUS_INTRO <strong>$descrip_subcategoria</strong></p>";
			
				echo "<div id=buttonarea>";
				echo "	<input type=button class=boton value='$BTN_CREATE_NEW_TERM' onClick='javascript:createNewTerm($id_subcategoria);'>";
				echo "	<input type=button class=boton value='$BTN_DELETE_TERM' onClick='javascript:deleteTerms();'>";
				echo "</div>";
				
				echo "<br>";			
			
			// diccionario de terminos de la subcategoria
			$db->sql = 	"SELECT a.*, b.TERMINO, b.CODIGO_CORTO, b.DESCRIPCION, b.TERMINO_PADRE, b.USAR, b.FUENTE_AGENCIA, b.FUENTE_NOTAS, b.OBSOLETO " . 
						"FROM tesauro_terminos_categorias a " .
						"   LEFT JOIN tesauro_terminos b ON (b.ID_RED=a.ID_RED and b.ID_TERMINO=a.ID_TERMINO) " .
						"WHERE a.ID_RED=" . getsessionvar("id_red") . " and a.ID_CATEGORIA=$id_categoria and b.TERMINO_PADRE='$id_subcategoria'";
			$db->sql .= "ORDER BY b.CODIGO_CORTO";	   
			$db->Open();				
			
			echo "<table width=100%>";
			echo "<tr>" .
				  "<td class='cuadricula columna columnaEncabezado' width='5%'>&nbsp;</td>" .
				  "<td class='cuadricula columna columnaEncabezado' width=60px>$LBL_CODE</td>" .
				  "<td class='cuadricula columna columnaEncabezado' width=220px>$LBL_TERM</td>" . 
				  "<td class='cuadricula columna columnaEncabezado' width=100px>$LBL_USE_INSTEAD</td>" . 
				  "<td class='cuadricula columna columnaEncabezado' width=120px>$LBL_SOURCE</td>" . 
				  "<td class='cuadricula columna columnaEncabezado' width=120px>$LBL_SOURCE_NOTES</td>" . 
				 "</tr>";			
			
			while( $db->NextRow() ) 
			{ 
				$id_termino     = $db->row["ID_TERMINO"];
				$codigo_corto   = $db->row["CODIGO_CORTO"];
				$termino   	    = $db->row["TERMINO"];
				$termino_padre  = $db->row["TERMINO_PADRE"];
				$usar 		    = $db->row["USAR"];
				$fuente_agencia = $db->row["FUENTE_AGENCIA"];
				$fuente_notas	= $db->row["FUENTE_NOTAS"];
				
				$link = "javascript:editTerm($id_subcategoria, $id_termino);";
				
				echo "<tr>" .
					  "<td class='cuadricula columna'><input name='chk_$id_termino' id='chk_$id_termino' type=checkbox onClick='javascript:click_CheckBoxes();'></td> " .
					  "<td class='cuadricula columna'>$codigo_corto</td>" .
					  "<td class='cuadricula columna'><a href='$link' onMouseOut='javascript:js_Status(\"\"); return true;' onMouseOver='javascript:js_Status(\"Editar\"); return true;'>$termino</a></td>" . 
					  "<td class='cuadricula columna'>$usar</td>" . 
					  "<td class='cuadricula columna'>$fuente_agencia</td>" . 
					  "<td class='cuadricula columna'>$fuente_notas</td>" . 
					 "</tr>";
			}

			echo "</table>";
			
			$db->FreeResultset();
				
			echo "</div><!-- INICIA DIV  DE CATEGORIZADOS  -->";
		
		}
	?>		
</div> <!-- contenido_principal -->

</div>
<!-- end div bloque_principal -->

<?php  

$db->destroy();

display_copyright(); 

?>

</div><!-- end div contenedor -->

</body>

</html>
