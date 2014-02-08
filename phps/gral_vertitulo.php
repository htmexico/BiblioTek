<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  19 mar 2009: Se crea el archivo PHP
	  22 mar 2009: Se perfecciona la interface y la consulta como tal
	  24 mar 2009: Se agrega la capacidad de mostrar portadas
	  18 abr 2009: Se agrega la capacidad de mostrar las copias del material
	  08 may 2009: Se agrega visualización con modalidad Ficha AACR2
	  22 jun 2009: Se permite desplegar archivos digitales
	  11 ago 2009: Se detectan dos secciones repetivas Tematizacion y Términos Temáticos
	  28 oct 2009: Permite agregar opinión.
	  05 nov 2009: Se perfecciona agregar y desplegar opinión.
	  
     */
		
	include "../funcs.inc.php";
	include_once "../basic/bd.class.php";
	include_language( "global_menus" );

	if( issetsessionvar("id_lib") )
		$id_biblioteca = getsessionvar("id_lib");
	else
	{
		check_usuario_firmado();
		
		$id_biblioteca = getsessionvar("id_biblioteca");
	}

	include_language( "gral_vertitulo" );
	
	$search = "";

	$id_consulta = read_param( "id_consulta", 0 );  // para la consulta
	$id_titulo = read_param( "id_titulo", 0, 1 );  // para el ID_TITULO
	
	$marc = read_param( "marc", 0, 0 );  	// para ver en formato MARC
	$aacr2 = read_param( "aacr2", 0, 0 );	// para ver en formato ficha AACR2
	$search = read_param( "search", "", 0 );
	
	$the_action = read_param( "the_action", "" );
	
	$db = new DB();
	
	$comments_allowed = true;  /* pendiente validar */
	$comment_from_user_already_exists = false;
	
	$id_usuario = getsessionvar( "id_usuario" );
	
	$db->Open( "SELECT COUNT(*) AS CUANTOS FROM acervo_titulos_califs WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo and ID_USUARIO=$id_usuario;" );
	
	if( $db->NextRow() )
	{
		if( $db->row["CUANTOS"] > 0 )
			$comment_from_user_already_exists = true;
	}
	
	$db->Close();
	
	if( $the_action == "save_comments" and !$comment_from_user_already_exists )
	{
		$id_opinion = 0;
		
		$db->Open( "SELECT COUNT(*) AS CUANTOS, MAX(ID_OPINION) AS IDMAX FROM acervo_titulos_califs WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo;" );
		
		if( $db->NextRow() )
		{
			if( $db->row["CUANTOS"] == 0 )
				$id_opinion = 1;
			else
				$id_opinion = $db->row["IDMAX"] + 1;
		}
		
		$db->Close();

		$curdate = current_dateandtime();

		$calificacion = $_POST[ "txt_qualif_value" ];

		$comentario = $_POST[ "txt_comments" ];
		$opinion = $_POST[ "txt_summary" ];

		$experto = "N";

		if(	issetsessionvar( "empleado") )
			$experto = getsessionvar( "empleado" );
		
		$db->ExecSQL( " INSERT INTO acervo_titulos_califs ( ID_BIBLIOTECA, ID_TITULO, ID_OPINION, CALIFICACION, COMENTARIO, OPINION, ID_USUARIO, EXPERTO, FECHA_OPINION ) " .
					  " VALUES ( $id_biblioteca, $id_titulo, $id_opinion, $calificacion, '$comentario', '$opinion', $id_usuario, '$experto', '$curdate' ) " );
		
		$comment_from_user_already_exists = true; // now it exists
		
	}
	else if( $the_action == "remove" )
	{
		require_once( "../privilegios.inc.php" );
		
		if( verificar_privilegio( PRIV_CATALOGING_DELETE ) )  // verificar atributos / permisos
		{
			// display_stop;
			$califs = 0;
			$copias = 0;
			$prestamos = 0;
			$catalogacion = 0;
			
			// copias
			$db->Open( "SELECT COUNT(*) AS CUANTOS FROM acervo_copias WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo;" );			
			if( $db->NextRow() ) { $copias = $db->row["CUANTOS"]; }			
			$db->Close();

			// calificaciones
			$db->Open( "SELECT COUNT(*) AS CUANTOS FROM acervo_titulos_califs WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo;" );
			if( $db->NextRow() ) { $califs = $db->row["CUANTOS"]; }
			$db->Close();

			// prestamos
			$db->Open( "SELECT COUNT(*) AS CUANTOS FROM acervo_copias a " .
						   "  INNER JOIN prestamos_det b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_ITEM=a.ID_ITEM) " .
							"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TITULO=$id_titulo; " );
			
			if( $db->NextRow() ) { $prestamos = $db->row["CUANTOS"]; }
			$db->Close();			
			
			//echo "Copias: " . $copias . "<br>";
			//echo "Califs: " . $califs . "<br>";
			//echo "Prestamos: " . $prestamos . "<br>";
			
			if( $copias == 0 and $prestamos == 0 )
			{
				ask_user_confirmation( $MSG_DELETE, "Título", "SELECT COUNT(*) AS CUANTOS FROM acervo_titulos_califs WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo;", "gral_vertitulo.php?id_titulo=$id_titulo&the_action=remove_confirmed" );
				die("");
			}
			else
			{
				$str_msg = "";
				
				if( $copias > 0 ) 
					$str_msg .= "$copias Copia(s)";
					
				if( $prestamos > 0 ) 
					$str_msg .= "$prestamos Préstamo(s)";					
				
				display_stop_message( $LBL_DELETE_HEADER, "$MSG_ERROR_DELETING: $str_msg", "gral_vertitulo.php?id_titulo=$id_titulo" );
				die("");
			}
			
		}
		
	}
	else if( $the_action == "remove_confirmed" )
	{
		require_once( "../privilegios.inc.php" );
		
		if( verificar_privilegio( PRIV_CATALOGING_DELETE ) )  // verificar atributos / permisos
		{	
				// Borrar el cotenido de: CATALOGACION, TITULOS, CALIFS_
				$db->ExecSQL( "DELETE FROM acervo_titulos_califs WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo; " );
				$db->ExecSQL( "DELETE FROM acervo_catalogacion WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo; " );
				$db->ExecSQL( "DELETE FROM acervo_archivos WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo; " );		
				$db->ExecSQL( "DELETE FROM acervo_titulos WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo; " );				
				
				ges_redirect( "anls_consultatitulos.php" );
		}
	}
	
	// Coloca un encabezado HTML <head>
	include "../basic/head_handler.php";
	HeadHandler( $LBL_CONSULT_HEADER, "../");
	
?>

<SCRIPT type="text/javascript" language="JavaScript">

	function backToSearch()
	{
		js_ChangeLocation( "gral_vertitulo.php?marc=1&id_titulo=<?php echo $id_titulo;?>" );
	}

	function gotoMARC()
	{
		js_ChangeLocation( "gral_vertitulo.php?marc=1&id_titulo=<?php echo $id_titulo;?>" );
	}
	
	function gotoLABELS()
	{
		js_ChangeLocation( "gral_vertitulo.php?marc=0&id_titulo=<?php echo $id_titulo;?>" );
	}
	
	function gotoAACR2()
	{
		js_ChangeLocation( "gral_vertitulo.php?aacr2=1&id_titulo=<?php echo $id_titulo;?>" );
	}

	function borrarTitulo()
	{
		js_ChangeLocation( "gral_vertitulo.php?the_action=remove&id_titulo=<?php echo $id_titulo;?>" );
	}
	
	function verCatalogacion( id_titulo )
	{
		var nwidth = screen.width;
		var nheight = screen.height; 
		
		js_ProcessActionURL( 1, "anls_catalogacion_paso2.php?id_titulo=" + id_titulo, "ver_catalog", (nwidth-50), (nheight-120) );
		
		//window.open("anls_catalogacion_paso2.php?id_titulo=" + id_titulo, "catalogacion", "WIDTH=" + (nwidth-50) + ",HEIGHT=" + (nheight-120) + ",TOP=20,LEFT=30,resizable=yes,scrollbars=yes,status=yes" );
			
		window.status='';
	}
	
	function ver_Item( id_item, id_serie )
	{
		var nwidth = 850;
		var nheight = 550; 
		var url = "anls_existencia_titulo.php?the_action=edit&id_titulo=<?php echo $id_titulo;?>&id_item=" + id_item;
		
		if( id_serie != 0 )
		{
			url += "&is_series=1";
			nheight = 750;
		}
		
		js_ProcessActionURL( 1, url, "ver_item", nwidth, nheight );
	}	
	
	function addFrontPage( id_titulo )
	{
		var nwidth = screen.width;
		var nheight = screen.height; 
		
		window.open("anls_catalogacion_frontpage.php?id_titulo=" + id_titulo, "frontpage", "WIDTH=" + (nwidth-50) + ",HEIGHT=" + (nheight-120) + ",TOP=20,LEFT=30,resizable=yes,scrollbars=yes,status=yes" );
			
		window.status='';
	}
	
	function addFile( id_titulo )
	{
		var nwidth = screen.width;
		var nheight = screen.height; 
		
		var ret = showModalDialog( "anls_catalogacion_files.php?id_titulo=" + id_titulo, "", "dialogLeft:9å0px;dialogWidth:790px;dialogHeight:600px;center:yes;status:no;" );
			
		window.status='';	
	}
	
	function printAACR2()
	{
		var url = "gral_imprime_ficha.php?id_titulo=<?php echo $id_titulo;?>&aacr2=1";
		var nwidth = 850;
		var nheight = 550; 

		js_ProcessActionURL( 1, url, "card_AACR2", nwidth, nheight );
	}
	
	function printMARC()
	{
		var url = "gral_imprime_ficha.php?id_titulo=<?php echo $id_titulo;?>&marc=1";
		var nwidth = 850;
		var nheight = 550; 

		js_ProcessActionURL( 1, url, "card_MARC", nwidth, nheight );
	}	
	
	function printLABELS()
	{
		var url = "gral_imprime_ficha.php?id_titulo=<?php echo $id_titulo;?>&labels=1";
		var nwidth = 850;
		var nheight = 550; 

		js_ProcessActionURL( 1, url, "card_LABELS", nwidth, nheight );
	}	
	
	function saveComments()
	{
		var error = 0;
		
		if( document.post_comment.txt_comments.value == "" )
		{
			alert( "<?php echo $MSG_ERROR_NO_COMMENT;?>" );
			error = 1;
		}
		
		if( error == 0 )
		{
			if( document.post_comment.txt_summary.value == "" )
			{
				alert( "<?php echo $MSG_ERROR_NO_SUMMARY;?>" );
				error = 1;
			}
			
			if( error == 0 )
			{
				if( document.post_comment.txt_qualif_value.value == 0 )
				{
					alert( "<?php echo $MSG_ERROR_NO_RATED;?>" );
					error = 1;
				}
			}
		}		
		
		if( error == 0 )
		{
			if( confirm("<?php echo $MSG_SAVE_COMMENTS;?>") )
			{
				//js_ChangeLocation( "gral_vertitulo.php?the_action=save_comments&<?php echo $_SERVER["QUERY_STRING"];?>" );
				document.post_comment.submit();
			}
		}
	}
	
	function qualify_on( num_obj_clicked )
	{
		var obj1 = js_getElementByName( "value_qualif_one" );
		var obj2 = js_getElementByName( "value_qualif_two" );
		var obj3 = js_getElementByName( "value_qualif_three" );
		var obj4 = js_getElementByName( "value_qualif_four" );
		var obj5 = js_getElementByName( "value_qualif_five" );
		var show_final_value = js_getElementByName( "final_value_qualif" );
		var qualif_value = js_getElementByName( "txt_qualif_value" );
		var array_img = new Array(obj1,obj2,obj3,obj4,obj5);
		
		var obj;
		
		if( num_obj_clicked == 1 ) obj = obj1;
		if( num_obj_clicked == 2 ) obj = obj2;
		if( num_obj_clicked == 3 ) obj = obj3;
		if( num_obj_clicked == 4 ) obj = obj4;
		if( num_obj_clicked == 5 ) obj = obj5;
		
		var calif = 0;
		
		if( obj.innerHTML.indexOf('star_empty') > 0 )
		{
			obj.innerHTML = "<img src='../images/icons/star_half.png'>";
			calif = 0.5;
		}
		else if( obj.innerHTML.indexOf('star_half') > 0 )
		{
			obj.innerHTML = "<img src='../images/icons/star_full.png'>";
			calif = 1;
		}
		else
			obj.innerHTML = "<img src='../images/icons/star_empty.png'>";
			
		// marcar las estrellas vacías
		for( var i=0; i<array_img.length; i++ )
		{
			if( (i+1) < num_obj_clicked )
			{
				array_img[i].innerHTML = "<img src='../images/icons/star_full.png'>";
				calif++;
			}
			else if( (i+1) > num_obj_clicked )
				array_img[i].innerHTML = "<img src='../images/icons/star_empty.png'>";
		}
		
		show_final_value.innerHTML = calif;
		qualif_value.value = calif;
	}
	
</SCRIPT>

<STYLE>

  #contenido_principal
  {
	float: left;
	width: 80%;
  }
  
  #contenido_adicional
  {
	float: left;
	width: 18%;
  }  
  
  #caja_datos_principales
  {
    width: 97%; 
	margin-bottom: 10px;
	overflow: auto;
  }  
   
  #caja_info_ejemplares
  {
    width: 97%; 
	margin-bottom: 10px;
  }
  
  #zona_comentarios  
  {
	width: 77%;
	margin: 10px;
  }
   
  #buttonarea
  {
	left: 40px;
	width: 90%;
  }
     
  .label_info
  {
	padding-top: 1px; /* mover al CSS */
	padding-bottom: 1px; /* mover al CSS */
  }
  
  .data_info
  {
	padding: 1px; /* mover al CSS */
	/*border-top: 1px dotted silver;*/
	padding-left: 3px;
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
	display_menu( "../" ); 

	require_once "marc.php";
   
	$marc_record = new record_MARC21( $id_biblioteca, $db );
	
	$marc_record->ImprimeEncabezado(1);
	
	$marc_record->InicializarRegistroMARC21_DesdeBD_Titulo( $id_titulo );

	$portada = NULL;
	$contraportada = NULL;
	
	require_once( "../privilegios.inc.php" );
	
	$priv_catalogacion = verificar_privilegio( PRIV_CATALOGING );

	$mostrar_fichas_marc = 0;
	$mostrar_fichas_aacr2 = 0;	
	
	if( $id_consulta != 0 )
	{
		// verificar parámetros de consulta
		$db->Open( "SELECT MOSTRAR_FICHAS_MARC, MOSTRAR_FICHAS_AACR2 FROM cfgconsultas_catalogo " .	
				   " WHERE ID_BIBLIOTECA=$id_biblioteca and ID_CONSULTA=$id_consulta and ACTIVA='S' ");
				   
		if( $db->NextRow() )
		{		
			$mostrar_fichas_marc = $db->row["MOSTRAR_FICHAS_MARC"] == "S" ? 1 : 0;
			$mostrar_fichas_aacr2 = $db->row["MOSTRAR_FICHAS_AACR2"] == "S" ? 1 : 0;
		}
		
		$db->Close();	
	}
	else
	{
		if( $priv_catalogacion )  // verificar atributos / permisos
		{
			$mostrar_fichas_marc = 1;
			$mostrar_fichas_aacr2 = 1;
		}
	}
	
	$db->sql  = "SELECT a.PORTADA, a.CONTRAPORTADA FROM acervo_titulos a " ;
	$db->sql .= "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TITULO=$id_titulo";
	
	$db->Open();
	
	if( $db->NextRow() )
	{	
		$portada 	   = $db->row["PORTADA"];
		$contraportada = $db->row["CONTRAPORTADA"];
	}
	
	$db->FreeResultset();
	
	if( issetsessionvar("biblio_firmado") )
	{
		if( getsessionvar("biblio_firmado") == "SI" )
		{
			require_once("../actions.inc.php");
			agregar_actividad_de_usuario( ANLS_VIEW_TITLE, "", 0, $id_titulo );
		}
	}

?>				
 
<div id="bloque_principal"> <!-- inicia contenido -->
 
 <div id="contenido_principal">

	<div class=caja_datos id="caja_datos_principales"> 
		<h2><?php echo $LBL_CONSULT_HEADER;?> </h2><br>
		
		<?php
		
			if( $aacr2 == 1 )
			{
				$marc_record->ImprimeFichaAACR2();
			}
			else if( $marc == 1 )
			{
				// entradas MARC
				$marc_record->ImprimeFichaMARC();				
			}
			else
			{
				$marc_record->ImprimeFichaEtiquetas();	
				
			}

		?>
		
	</div> <!-- caja_datos --> 
	
	<?php 
	
		if( $marc == 0 and $aacr2 == 0 )
		{			
			echo "<div id='caja_info_ejemplares' class='caja_datos'>";
				
			// mostrar links a archivos anexados
			if( $marc_record->digital == "S" )
			{
				// mostrar archivos anexos
				// show attached files
				echo "<h2>$LBL_CARD_FILES</h2><br>";
				
				$marc_record->DisplayDataFiles(1);
			}
			else
			{			
				// mostrar datos referentes a las copias existentes
				// show info related to copies 
				$db->sql = "SELECT a.*, e.DESCRIPCION AS DESCRIP_UBICACION, e.NOTAS_UBICACION " . 
						   "FROM acervo_copias a " . 
						   "  LEFT JOIN cfgbiblioteca b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA) " .
						   "    LEFT JOIN tesauro_terminos_categorias c ON (c.ID_RED=b.ID_RED and c.ID_CATEGORIA=6 and c.ID_TERMINO=a.CATEGORIA_PRESTAMO)" .
						   "       LEFT JOIN cfgubicaciones e ON (e.ID_BIBLIOTECA=a.ID_BIBLIOTECA and e.ID_UBICACION=a.ID_UBICACION) " .
						   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TITULO=$id_titulo " .
						   "ORDER BY a.ID_ITEM ";
				$db->Open();
				echo "";
				echo "<table width=99%>";
				
				while( $db->NextRow() )
				{	
					$id_item = $db->Field("ID_ITEM");
					$id_material = $db->Field("ID_MATERIAL");
					
					$acciones = "";
					$veritem = $id_material;
					//$veritem = "<a href='javascript:ver_Item($id_item,$marc_record->nIDSerie);'>" . $id_material. "</a>";
					
					if( $db->numRows == 0 )	
					{
						echo "<tr><td class='columna columnaEncabezado' colspan='5'><strong>$$LBL_CURRENT_EXISTENCES</strong></td></tr>";
						echo "<tr>" .
							  "<td class='cuadricula columna columnaEncabezado' width=100px>Acciones</td>" .
							  "<td class='cuadricula columna columnaEncabezado' width=150px>$LBL_ID_MATERIAL</td>" .
							  "<td class='cuadricula columna columnaEncabezado' width=210px>$LBL_LOCATION</td>" . 
							  "<td class='cuadricula columna columnaEncabezado' width=150px>$LBL_CALL_NUMBER</td>" . 
							  "<td class='cuadricula columna columnaEncabezado' width=130px>$LBL_STATUS</td>" . 
							 "</tr>";					
					}
					
					if( issetsessionvar("empleado") )
					{
						if( getsessionvar("empleado") == "S" )
						{
							$veritem = "<a href='javascript:ver_Item($id_item,$marc_record->nIDSerie);'>" . $id_material. "</a>";
						}
						
						if( getsessionvar("empleado") != "S" )
						{
							if( $db->Field("STATUS") == "D" )
							{
								// PENDIENTE
								// VERIFICAR QUE EL USUARIO
								// PERTENEZCA A UN GRUPO QUE PUEDA OBTENER UN PRÉSTAMO A DOMICILIO
								$acciones = "<a href='circ_bandeja.php?id_titulo=$id_titulo&accion=1'><img src='../images/bandeja.png'></a>";
							}
						}
					}
					
					if( $marc_record->nIDSerie != 0 )
					{
						$cMes = "";
						
						if( $db->row["SERIES_MES"] >= 1 and $db->row["SERIES_MES"] <= 12 )
							$cMes = substr( $arrayMeses[ $db->row["SERIES_MES"]-1 ],0,3);

						$call_number = $cMes . " / " . $db->row["SERIES_ANIO"];
						
						$call_number = "No. " . $db->row["NUMERO_PARTE"] . "; &nbsp;&nbsp;" . $call_number;
					}
					else
						$call_number = $marc_record->GetCallNumber( $db->Field("SIGNATURA_PREFIJO"), $db->Field("SIGNATURA_CLASE"), $db->Field("SIGNATURA_LIBRISTICA") );

					echo "<tr>" . 
						 " <td align='center' class='cuadricula columna'>$acciones</td> " .
						 " <td class='cuadricula columna'>$veritem</td> " .
						 " <td class='cuadricula columna'>" . $db->row["DESCRIP_UBICACION"]. "</td> " .
						 " <td class='cuadricula columna'>" . $call_number . "</td> " .
						 " <td class='cuadricula columna'>" . $marc_record->GetItemStatus( $db->row["STATUS"] ) . "</td> " .
						 " </tr>";
				} // end-while
				
				if( $db->numRows == 0 )	
				{
					echo "<tr><td class='columna' colspan='5'><strong>$LBL_NO_EXISTENCES</strong></td></tr>";
				}
				
				$db->FreeResultset();

				echo "</table>";			
			}
					
			echo "</div>";
		}
		
	?>

 </div> <!-- contenido_principal -->
 
 <div id="contenido_adicional">

	<?php 
	  
		if( $portada != NULL )
		{
			$width1 = 70;
			
			if( $contraportada == NULL )
			  $width1 = 130;
			
			echo "<img name='portada' src='../phps/image.php?id_biblioteca=$id_biblioteca&id_titulo=$id_titulo&tipoimagen=PORTADA' width='$width1'> &nbsp;";
		}

		if( $contraportada != NULL )
		{
			echo "<img name='contraportada' src='../phps/image.php?id_biblioteca=$id_biblioteca&id_titulo=$id_titulo&tipoimagen=CONTRAPORTADA' width='70'>";			
		}

		if( verificar_privilegio( PRIV_ADD_COVERS ) )
		{
			if( $portada == NULL and $contraportada == NULL )
			{
				echo "<span>$LBL_NO_IMAGES</span><br><br>";
				echo "<input class='boton' type='button' onClick='javascript:addFrontPage($id_titulo)' value='$BTN_ADD_FRONTPAGE'>";
			}
			else
			{
				echo "<br><input class='boton' type='button' onClick='javascript:addFrontPage($id_titulo)' value='$BTN_EDIT_FRONTPAGE'>";
			}
		}

	?>
 </div>

<?php
	$marc_record->clear_record();
	$marc_record->destroy();
	
	
?>

</div>
<!-- end div bloque_principal -->

<!-- <br style='clear:both;'> -->

<div id="buttonarea">
	<input class="boton" type='button' onClick="javascript:window.history.back();" value="<?php echo $BTN_GOBACK;?>">
	
	<?php 
		if( $marc == 0 and $mostrar_fichas_marc==1 )
		{ 
			echo "<input class='boton' type='button' onClick='javascript:gotoMARC();' value='$BTN_VIEW_AS_MARC'>"; 
		}		
		
		if( $aacr2 == 0 and $mostrar_fichas_aacr2==1 )
		{ 
			echo "<input class='boton' type='button' onClick='javascript:gotoAACR2();' value='$BTN_VIEW_AS_AACR2'>"; 
		}

		if( $marc==1 or $aacr2 == 1 )
			echo "<input class='boton' type='button' onClick='javascript:gotoLABELS();' value='$BTN_VIEW_AS_LABELS'>";
		
		if( $aacr2 == 1 )
			echo "<input class='boton' type='button' onClick='javascript:printAACR2();' value='$BTN_PRINT AACR'>"; 
		else if( $marc == 1 )
			echo "<input class='boton' type='button' onClick='javascript:printMARC();' value='$BTN_PRINT MARC' >";
		else if( $marc == 0 and $aacr2 == 0 )
			echo "<input class='boton' type='button' onClick='javascript:printLABELS();' value='$BTN_PRINT' >";			
		
		if( verificar_privilegio( PRIV_CATALOGING ) )  // verificar atributos / permisos
		{
			echo "&nbsp;&nbsp;&nbsp;<input class='boton' type='button' onClick='javascript:verCatalogacion($id_titulo)' value='$BTN_VIEW_CATALOGING' />";
		}

		if( verificar_privilegio( PRIV_ADD_DIGITAL_FILES )  )
		{
			echo "<input class='boton' type='button' onClick='javascript:addFile($id_titulo)' value='$BTN_ADD_FILE'>";
		}
		
		// if( $id_biblioteca == 1 ) PARA DEPURACION
		{
			if( verificar_privilegio( PRIV_CATALOGING_DELETE ) )  // verificar atributos / permisos
			{
				echo "<input class='boton' type='button' onClick='javascript:borrarTitulo($id_titulo)' value='Borrar Título'>";
			}
		}
		
	 ?>
</div>			  

<?php  

	//if( getsessionvar( "empleado" ) != "S" )
	//{
		echo "<a name='comentarios'></a>";
		echo "<br style='clear:both;'>";
		echo "<div class='caja_datos' id='zona_comentarios'>";
		echo "<h2>$LBL_COMMENTS</h2><br>";
		
		$sql =  "SELECT a.*, b.USERNAME, b.PATERNO, b.MATERNO, b.NOMBRE, c.NOMBRE_GRUPO " .	
				"FROM acervo_titulos_califs a " .
				" LEFT JOIN cfgusuarios b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_USUARIO=a.ID_USUARIO) " .
				"  LEFT JOIN cfgusuarios_grupos c ON (c.ID_BIBLIOTECA=b.ID_BIBLIOTECA and c.ID_GRUPO=b.ID_GRUPO) " .
				"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TITULO=$id_titulo ";

		$db->Open( $sql );

		while ( $db->NextRow() )
		{
			echo "<div class='caja_comentarios'>";
			$fullname = $db->row["NOMBRE"] .  " " . $db->row["PATERNO"] . " " . $db->row["MATERNO"];
			echo "<div class='perfil_usuario'>";
			echo "<h1>$LBL_USER_BY $fullname</h1><br>";
			echo "<h1>" . $db->row["NOMBRE_GRUPO"] . "</h1><br>";
			
			echo "<h3>" . dbdate_to_human_format( $db->row["FECHA_OPINION"], 1, 1 ) . "</h3>";
			
			echo "<br><br>";

			$str_calif = "";
			
			for( $j=1; $j<=((int) $db->row["CALIFICACION"]); $j++ )
			{
				$str_calif .= "<img src='../images/icons/star_full.png'>";
			}
			
			if( $db->row["CALIFICACION"] > (int) $db->row["CALIFICACION"] )
			{
				$str_calif .= "<img src='../images/icons/star_half.png'>";
			}
			
			echo "<h2>$LBL_USER_RATE</h2><br>";
			echo $str_calif;

			echo "<br><br>";
			echo "<a href='serv_comentariosusuario.php?id_usuario=" . $db->row["ID_USUARIO"] . "'>$HINT_SEE_USER_COMMENTS</a>";
			
			echo "</div>";  // perfil_usuario
			
			echo "<div class='detalles'>";
			
			$opinion = $db->GetBLOB( $db->row["OPINION"] );
			$opinion = str_replace( "\r\n", "<br>", $opinion );
			
			echo "<h1>" . $db->row["COMENTARIO"] . "</h1><br>" ;
			echo "$opinion<br><br>";
			
			echo "</div>";
			
			echo "</div>";
			
			echo "<br style='clear:both;'><br>";
		}
		
		if( $db->numRows == 0 )
		{
			echo "<h3>$HINT_NO_COMMENTS</h3><br>";
		}
		
		// Pedir comentario
		if( !$comment_from_user_already_exists )
		{
			echo "<hr>";
			
			include( "circulacion.inc.php" );
			$user = new TUser( $id_biblioteca, getsessionvar("id_usuario"), $db );
		
			require_once( "../templates/template_comments.php" );
			
			$user->destroy();
		}
		
		echo "</div>";  // caja comentarios
		
	//}

	$db->destroy();	
	
display_copyright(); 
?>

</div><!-- end div contenedor -->

</body>

</html>