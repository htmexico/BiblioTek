<?php
	session_start();

	/*******
	  Historial de Cambios

	  11 abr 2009: Se crea el archivo PHP para manejar las existencias (copias) de títulos.

     */		

	include( "../funcs.inc.php" ); 

	check_usuario_firmado(); 

	include( "../basic/bd.class.php" );
	
	include_language( "global_menus" );
	include_language( "anls_existencias" ); 	// archivo de idioma

	$id_biblioteca = getsessionvar("id_biblioteca");	
	
	$id_titulo = 0;
	$id_item_created=0;
	$id_item_edited=0;
	$id_items_deleted = 0;
	
	if( isset($_GET["id_titulo"] ) )
		$id_titulo = $_GET["id_titulo"];
	else
	   die( "Error en la llamada" );

	if( isset($_GET["id_item_created"] ) )
		$id_item_created = $_GET["id_item_created"];		

	if( isset($_GET["id_item_edited"] ) )
		$id_item_edited = $_GET["id_item_edited"];

	if( isset($_GET["id_items_deleted"] ) )
		$id_items_deleted = $_GET["id_items_deleted"];	

	$db = new DB();
		
	require_once "marc.php";
	require_once "circulacion.inc.php";
	$marc_record = new record_MARC21( $id_biblioteca, $db );
	$item = new TItem_Basic( $id_biblioteca, $id_titulo, 0 );
	
	if( $item->NOT_FOUND )
	{
		$item->destroy();
		$db->destroy();

		ges_redirect( "anls_existencias.php?error_title=$id_titulo" );
	}

	$portada = $item->cCover;
	$contraportada = $item->cBackCover;

    $marc_titulo = "<img src='../" . $item->cIcon . "'> [" . $item->Material_ShortCode() . "] ". $item->cTitle;
	$marc_autor = $item->cAutor;

	// Coloca un encabezado HTML <head>
	include ("../basic/head_handler.php");
	HeadHandler( "$LBL_EXISTENCES_V2 $item->cTitle", "../" );	

	$item->destroy(); 		

?>

<SCRIPT language="JavaScript">

	function create_existence_item()
	{
		var nwidth = screen.width;
		var nheight = screen.height; 
		var url = "anls_existencia_titulo.php?id_titulo=<?php echo $id_titulo;?>";

		if( <?php echo allow_use_of_popups() ? "1" : "0"; ?> )
		{
			js_ProcessActionURL( 1, url, "edit_item", "900px", "600px" );

			//( nType, url, dialog_name, dialog_width, dialog_height )
			//var ret = showModalDialog( url, "edit_item", "dialogLeft:100px;dialogWidth:900px;dialogHeight:500px;center:yes;status:no;" );
			//window.open( url, "edit_item", "WIDTH=900px,HEIGHT=500px,TOP=100px,LEFT=50px,resizable=yes,scrollbars=yes,status=yes" );
			
			//if( ret != null )
			//{
			//	js_ChangeLocation( "anls_existencias_paso2.php?id_titulo=<?php echo $id_titulo;?>" );
			//}
			
		}
		else
			js_ChangeLocation( url );

		window.status='';
	}
	
	function delete_items()
	{
		var nwidth = screen.width;
		var nheight = screen.height; 

		var sel_items = document.getElementsByName("sel_items");
		var url = "anls_existencia_titulo.php?the_action=delete&id_titulo=<?php echo $id_titulo;?>&items=" + sel_items[0].value;

		if( sel_items.length > 0 )
		{
			if( sel_items[0].value != "" )
			{
				if( <?php echo allow_use_of_popups() ? "1" : "0"; ?> )
					window.open( url, "delete_items", "WIDTH=" + (nwidth-50) + ",HEIGHT=" + (nheight-120) + ",TOP=20,LEFT=30,resizable=yes,scrollbars=yes,status=yes" );
				else
					js_ChangeLocation( url );
			}
			else
				alert( "<?php echo $MSG_NO_MARKED_ITEMS;?>" );
		}
	}
	
	function discard_items()
	{
		var nwidth = screen.width;
		var nheight = screen.height; 

		var sel_items = document.getElementsByName("sel_items");
		var url = "anls_descarta_titulos.php?action=discard&id_titulo=<?php echo $id_titulo;?>&items=" + sel_items[0].value;

		if( sel_items.length > 0 )
		{
			if( sel_items[0].value != "" )
			{
				if( <?php echo allow_use_of_popups() ? "1" : "0"; ?> )
					window.open( url, "discard_items", "WIDTH=" + (nwidth-50) + ",HEIGHT=" + (nheight-120) + ",TOP=20,LEFT=30,resizable=yes,scrollbars=yes,status=yes" );
				else
					js_ChangeLocation( url );
			}
			else
				alert( "<?php echo $MSG_NO_MARKED_ITEMS;?>" );
		}
	}
	
	
	function click_CheckBoxes()
	{
		var aObjects = document.getElementsByTagName("input");
		var sel_items = document.getElementsByName("sel_items");
		var i;
		
		if( sel_items.length > 0 )
		{
			sel_items[0].value = "";
			
			for( i = 0; i<aObjects.length; i++ )
				if( aObjects[i].type == "checkbox" )
				{
					if( aObjects[i].checked )
					{
						if( sel_items[0].value == "" )
							sel_items[0].value = "@";
						else
							sel_items[0].value += ":";
						
						sel_items[0].value += aObjects[i].name.substr(4,15);
					}
				}
		}
		
	}
	
	// editar una existencia
	function edit_Existence( id_item )
	{
		var nwidth = screen.width;
		var nheight = screen.height; 
		var url = "anls_existencia_titulo.php?id_titulo=<?php echo $id_titulo;?>&the_action=edit&id_item=" + id_item;
		
		js_ProcessActionURL( 1, url, "edit_item", 800, 500 );

		window.status='';
	}
	

	
</SCRIPT>

<style type="text/css">

#contenido_principal 
{
	display: inline;
	float: left;
	width: 82%;
}

#contenido_adicional
{
	display: inline;
	float: right;
	width: 15%;
}

#info_ejemplares
{
	overflow: auto;
	width: 99%;
}

#info_portadas
{
	float: right;
	overflow: auto;
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
	display_menu('../'); 
   
 ?>
   <div id="bloque_principal"> 

	 <div id="contenido_principal">
		<h1><?php echo $LBL_EXISTENCES_V2;?></h1>

		<div style='float:left; '>
			<a href='gral_vertitulo.php?id_titulo=<?php echo $id_titulo;?>'><?php echo $marc_titulo; ?></a><br>
			<?php echo $marc_autor; ?><br><br>
		</div>
				
		<br style='clear:both;'>
		  
	   <div id="info_ejemplares" class="caja_datos">

		   <div id="buttonArea" align=right>
			<input class="boton" type="button" value="<?php echo $BTN_CREATE_ITEM; ?>" name="btnCrear" onClick="javascript:create_existence_item()"; />
			<input class="boton" type="button" value="<?php echo $BTN_DELETE_ITEM; ?>" name="btnBorrar" onClick="javascript:delete_items()"; />
			<input class="boton" type="button" value="<?php echo $BTN_DISCARD_ITEM; ?>" name="btnDiscard" onClick="javascript:discard_items()"; />
		   </div> 
		   
		   <?php 
				if( $id_item_created != 0 ) 
				{
					echo "<div class=caja_info>";
					echo " <strong>$SAVE_CREATED_DONE</strong>";
					echo "</div>";
				}
			  
				if( $id_item_edited != 0 )
				{
					echo "<div class=caja_info>";
					echo " <strong>$SAVE_DONE</strong>";
					echo "</div>";
				}
				
				if( $id_items_deleted > 0 )
				{
					echo "<div class=caja_info>";
					echo " <strong>$DELETED_DONE ($id_items_deleted)</strong>";
					echo "</div>";
				}
				
				$db->sql = "SELECT a.*, d.DESCRIPCION AS DESCRIP_CATEGORIA_PRESTAMO, e.DESCRIPCION AS DESCRIP_UBICACION, e.NOTAS_UBICACION " . 
						   "FROM acervo_copias a " . 
						   "  LEFT JOIN cfgbiblioteca b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA) " .
						   "    LEFT JOIN tesauro_terminos_categorias c ON (c.ID_RED=b.ID_RED and c.ID_CATEGORIA=6 and c.ID_TERMINO=a.CATEGORIA_PRESTAMO)" .
						   "     LEFT JOIN tesauro_terminos d ON (d.ID_RED=b.ID_RED and d.ID_TERMINO=a.CATEGORIA_PRESTAMO) " .
						   "            LEFT JOIN cfgubicaciones e ON (e.ID_BIBLIOTECA=a.ID_BIBLIOTECA and e.ID_UBICACION=a.ID_UBICACION) " .
						   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TITULO=$id_titulo " .
						   "ORDER BY a.ID_ITEM ";
				//$db->DebugSQL();
				$db->Open();
				
				echo "<input type=hidden class=hidden name='sel_items' id='sel_items' value=''>";
				
				echo "<table width='100%'>";
				
				while( $db->NextRow() ) 
				{ 
					if( $db->numRows == 1 )
					{
						echo "<tr>" .
							  "<td class='cuadricula columna columnaEncabezado' width=15px>&nbsp;</td>" .
							  "<td class='cuadricula columna columnaEncabezado' width=55px>$LBL_ID_ITEM</td>" .
							  "<td class='cuadricula columna columnaEncabezado' width=150px'>$LBL_ID_MATERIAL</td>" . 
							  "<td class='cuadricula columna columnaEncabezado' width=220px>$LBL_LOAN_CATEGORY</td>" . 
							  "<td class='cuadricula columna columnaEncabezado' width=80px>$LBL_ACQUISICION</td>" . 
							  "<td class='cuadricula columna columnaEncabezado' width=120px>$LBL_STATUS</td>" . 
							  "<td class='cuadricula columna columnaEncabezado' width=120px>$LBL_LOCATION</td>" . 
							 "</tr>";
					}
					
					$id_item = $db->Field("ID_ITEM");
					$id_titulo = $db->Field("ID_TITULO");
					
					$id_material = $db->Field("ID_MATERIAL");
					$categoria_prestamo = $db->Field("DESCRIP_CATEGORIA_PRESTAMO");
					
					$id_adquisicion = $db->Field("ID_ADQUISICION");
					
					$status = $db->Field("STATUS");					
					$status = $marc_record->GetItemStatus($status);
									
					$rowedited = "";
					
					if( $id_item == $id_item_edited )
						$rowedited = "class='trHilited' ";
					
					echo "<tr $rowedited>" . 
						 " <td class='cuadricula columna'><input name='chk_$id_item' id='chk_$id_item' type=checkbox onClick='javascript:click_CheckBoxes();'></td> " .
						 " <td class='cuadricula columna'>$id_item</td> " .
						 " <td class='cuadricula columna'><a href='javascript:edit_Existence($id_item);'>" . $id_material. "</a></td> " .
						 " <td class='cuadricula columna'>$categoria_prestamo</td> " .
						 " <td class='cuadricula columna'>$id_adquisicion</td> " .
						 " <td class='cuadricula columna'>$status</td> " .
						 " <td class='cuadricula columna'>" . $db->Field('DESCRIP_UBICACION'). "</td> " .
						 " </tr>";
				}
				
				$db->Close();
				
				echo "</table>";
				
				if( $db->numRows == 0 )
				{
					echo $MSG_NO_COPIES_FOUND;
				}				
		   ?>
		
	   </div><!-- - caja datos -->
	   
	</div> <!-- contenido pricipal -->
		
  <div id="contenido_adicional">
		<div id='info_portadas'>
			<?php 
				if( $portada != NULL )
				{
					$width1 = 70;
					
					if( $contraportada == NULL )
					  $width1 = 130;
					
					echo "<img name='portada' src='../phps/image.php?id_biblioteca=$id_biblioteca&id_titulo=$id_titulo&tipoimagen=PORTADA' width='$width1'\>";
				}

				if( $contraportada != NULL )
				{
					echo "<img name='contraportada' src='../phps/image.php?id_biblioteca=$id_biblioteca&id_titulo=$id_titulo&tipoimagen=CONTRAPORTADA' width='70'\>";
				}

				if( $portada == NULL and $contraportada == NULL )
					echo "<span>Sin imagenes disponibles</span>";
			  
			?>
		</div>	
  </div>  <!-- contenido_adicional -->
  
  <?php  display_copyright(); ?>

</div><!--bloque principal-->

</div><!--bloque contenedor-->

<?php

	$marc_record->destroy();
	
	$db->destroy();	
?>
       
</body>
</html>