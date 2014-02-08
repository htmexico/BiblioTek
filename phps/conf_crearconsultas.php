<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  14 sep 2009: Se crea el archivo PHP para editar/crear/eliminar consultas.
	  07 jun 2011: Pequeño ajuste visual.
	  
     */
		
	include "../funcs.inc.php";	
	
	include_language( "global_menus" );

	check_usuario_firmado(); 

	include_language( "conf_crearconsultas" );		// archivo de idioma

	include("../basic/bd.class.php");
	
	$id_consulta = "$LBL_TO_BE_ASIGNED";
	
	$descripcion = "";
	
	$keywords    = "";
	$title       = "";
	$author      = "";
	$subjects    = "";
	$callnumber  = "";
	$isbn        = "";
	$issn        = "";
	
	$recs_x_page = 5;
	
	// anexos
	$show_digital_attachments     = "";
	$download_digital_attachments = "";
	
	$administrative = "";
	$opac			= "";
	$only_readers   = "";
	
	// view content
	$allow_cardview         = "";
	$allow_view_of_copies   = "";
	$allow_view_of_iteminfo = "";
	
	// view style
	$allow_view_in_marcstyle  = "";
	$allow_view_in_aacr2style = "";
	
	$age_filtered 			 = "";
	$allow_filter_x_material = "";
	
	$active = "S";	
	
	$ordered_by = "AUTHOR";
	
	$the_action = read_param( "the_action", "" );
	
	$error = 0;
	
	$id_biblioteca = getsessionvar("id_biblioteca");
		
	if( $the_action == "create_new" )
	{
		// generar el nuevo ID de la persona
		$id_consulta = 0;
		
		$db = new DB( "SELECT MAX(ID_CONSULTA) AS MAXID, COUNT(*) AS CUANTOS FROM CFGCONSULTAS_CATALOGO WHERE ID_BIBLIOTECA=$id_biblioteca" );
		
		if ($db->NextRow() ) 
			$id_consulta = $db->Field("MAXID") + 1;
			
		$db->FreeResultset();
		
		$descripcion = $_POST["txt_qryname"];
		
		$active		= isset($_POST["chk_active"]) ? "S" : "N";

		$keywords	= isset($_POST["chk_keywords"]) ? "S" : "";
		$title		= isset($_POST["chk_title"]) ? "S" : "";
		$author		= isset($_POST["chk_author"]) ? "S" : "";
		$subjects	= isset($_POST["chk_subjects"]) ? "S" : "";
		$callnumber	= isset($_POST["chk_callnumber"]) ? "S" : "";
		$isbn		= isset($_POST["chk_isbn"]) ? "S" : "";
		$issn		= isset($_POST["chk_issn"]) ? "S" : "";
		
		$recs_x_page = read_param( "cmb_recs_x_page", 5, 1 );
		
		$show_digital_attachments		= isset($_POST["chk_show_digital_attachments"]) ? "S" : "";
		$download_digital_attachments	= isset($_POST["chk_download_digital_attachments"]) ? "S" : "";

		$query_type   = read_param( "cmb_query_type", "", 1 );	
		$opac = "";
		$administrative = "";
		$only_readers = "";
		
		if( $query_type == "OPAC" )    $opac = "S";
		if( $query_type == "ADMIN" )   $administrative = "S";
		if( $query_type == "READERS" ) $only_readers	= "S";
		
		$allow_cardview 	     = isset($_POST["chk_allow_cardview"]) ? "S" : "";
		$allow_view_of_copies  	 = isset($_POST["chk_allow_view_of_copies"]) ? "S" : "";
		$allow_view_of_iteminfo  = isset($_POST["chk_allow_view_of_iteminfo"]) ? "S" : "";
		
		$allow_view_in_marcstyle  = isset($_POST["chk_view_marc_style"]) ? "S" : "";
		$allow_view_in_aacr2style = isset($_POST["chk_view_aacr_style"]) ? "S" : "";
		
		$age_filtered 			 = isset($_POST["chk_age_filtered"]) ? "S" : "";
		$allow_filter_x_material = isset($_POST["chk_show_materialfilter"]) ? "S" : "";
		
		$ordered_by = read_param( "cmb_order_by", "", 1 );	
		
		$db->sql  = "INSERT INTO CFGCONSULTAS_CATALOGO ( ID_BIBLIOTECA, ID_CONSULTA, DESCRIPCION, ACTIVA, INCLUIR_PALABRASCLAVE, INCLUIR_TITULO, ";
		$db->sql .= "   INCLUIR_AUTOR, INCLUIR_MATERIAS, INCLUIR_SIGNATURA_TOPOG, INCLUIR_ISBN, INCLUIR_ISSN, REGISTROS_X_PAGINA, ";
		$db->sql .= "   MOSTRAR_ARCHIVOS_ANEXOS, PERMITIR_DESCARGAR_ANEXOS, ADMINISTRATIVA, OPAC, LECTORES, ";
		$db->sql .= "   PERMITIR_CONSULTAR_TITULOS, MOSTRAR_ITEMS_EXISTENTES, PERMITIR_CONSULTAR_ITEMS, ESTILO_DESPLIEGUE, ";
		$db->sql .= "   MOSTRAR_FICHAS_MARC, MOSTRAR_FICHAS_AACR2, FILTRA_X_GRUPO_EDAD, MOSTRAR_FILTRO_X_MATERIAL, ORDENADOR_POR ) ";
		$db->sql .= " VALUES ( $id_biblioteca, $id_consulta, '$descripcion', '$active', '$keywords', '$title', ";
		$db->sql .= "   '$author', '$subjects', '$callnumber', '$isbn', '$issn', $recs_x_page, ";
		$db->sql .= "   '$show_digital_attachments', '$download_digital_attachments', '$administrative', '$opac', '$only_readers', ";
		$db->sql .= "   '$allow_cardview', '$allow_view_of_copies', '$allow_view_of_iteminfo', '', ";
		$db->sql .= "   '$allow_view_in_marcstyle', '$allow_view_in_aacr2style', '$age_filtered', '$allow_filter_x_material', '$ordered_by' ) ";
		$db->ExecSQL();
		
		require_once( "../actions.inc.php" );
		agregar_actividad_de_usuario( CFG_CHANGE_PARAMS_QUERIES_CREATE, "$ACTION_DESCRIP_CREATE $descripcion" );
		
		$error = 10;
		
		if( !allow_use_of_popups() )
			ges_redirect( "conf_consultas.php?id_consulta_created=$id_consulta" );

	}
	else if( $the_action == "save_changes" )
	{
		$id_consulta = read_param( "id_consulta", "", 1 );
		$descripcion = read_param( "txt_qryname", "", 1 );
		$active		 = isset($_POST["chk_active"]) ? "S" : "N";

		$keywords	 = isset($_POST["chk_keywords"]) ? "S" : "";
		$title		 = isset($_POST["chk_title"]) ? "S" : "";
		$author		 = isset($_POST["chk_author"]) ? "S" : "";
		$subjects	 = isset($_POST["chk_subjects"]) ? "S" : "";
		$callnumber	 = isset($_POST["chk_callnumber"]) ? "S" : "";
		$isbn		 = isset($_POST["chk_isbn"]) ? "S" : "";
		$issn		 = isset($_POST["chk_issn"]) ? "S" : "";
		
		$recs_x_page = read_param( "cmb_recs_x_page", 5, 1 );
		
		$show_digital_attachments		= isset($_POST["chk_show_digital_attachments"]) ? "S" : "";
		$download_digital_attachments	= isset($_POST["chk_download_digital_attachments"]) ? "S" : "";

		$query_type   = read_param( "cmb_query_type", "", 1 );	
		$opac = "";
		$administrative = "";
		$only_readers = "";
		
		if( $query_type == "OPAC" )    $opac = "S";
		if( $query_type == "ADMIN" )   $administrative = "S";
		if( $query_type == "READERS" ) $only_readers	= "S";
		
		$allow_cardview 	     = isset($_POST["chk_allow_cardview"]) ? "S" : "";
		$allow_view_of_copies  	 = isset($_POST["chk_allow_view_of_copies"]) ? "S" : "";
		$allow_view_of_iteminfo  = isset($_POST["chk_allow_view_of_iteminfo"]) ? "S" : "";
		
		$allow_view_in_marcstyle  = isset($_POST["chk_view_marc_style"]) ? "S" : "";
		$allow_view_in_aacr2style = isset($_POST["chk_view_aacr_style"]) ? "S" : "";
		
		$age_filtered 			 = isset($_POST["chk_age_filtered"]) ? "S" : "";
		$allow_filter_x_material = isset($_POST["chk_show_materialfilter"]) ? "S" : "";
		
		$ordered_by = read_param( "cmb_order_by", "", 1 );	

		$db = new DB;
		
		$db->sql  = "UPDATE CFGCONSULTAS_CATALOGO SET DESCRIPCION='$descripcion', ACTIVA='$active', INCLUIR_PALABRASCLAVE='$keywords', INCLUIR_TITULO='$title',";
		$db->sql .= "  INCLUIR_AUTOR='$author', INCLUIR_MATERIAS='$subjects', INCLUIR_SIGNATURA_TOPOG='$callnumber', INCLUIR_ISBN='$isbn', INCLUIR_ISSN='$issn', ";
		$db->sql .= "   REGISTROS_X_PAGINA=$recs_x_page, MOSTRAR_ARCHIVOS_ANEXOS='$show_digital_attachments', PERMITIR_DESCARGAR_ANEXOS='$download_digital_attachments', ";
		$db->sql .= "   ADMINISTRATIVA='$administrative', OPAC='$opac', LECTORES='$only_readers', PERMITIR_CONSULTAR_TITULOS='$allow_cardview',  ";
		$db->sql .= "   MOSTRAR_ITEMS_EXISTENTES='$allow_view_of_copies', PERMITIR_CONSULTAR_ITEMS='$allow_view_of_iteminfo', ESTILO_DESPLIEGUE='', ";
		$db->sql .= "   MOSTRAR_FICHAS_MARC='$allow_view_in_marcstyle', MOSTRAR_FICHAS_AACR2='$allow_view_in_aacr2style', FILTRA_X_GRUPO_EDAD='$age_filtered', ";
		$db->sql .= "   MOSTRAR_FILTRO_X_MATERIAL='$allow_filter_x_material', ORDENADOR_POR='$ordered_by' ";
		$db->sql .= "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_CONSULTA=$id_consulta ";
		$db->ExecSQL();

		require_once( "../actions.inc.php" );
		agregar_actividad_de_usuario( CFG_CHANGE_PARAMS_QUERIES_EDIT, "$ACTION_DESCRIP_EDIT $descripcion" );

		$error = 20;

		if( !allow_use_of_popups() )
			ges_redirect( "conf_consultas.php?id_consulta_edited=$id_consulta" );

	}
	else if( $the_action == "edit" )
	{
		$id_consulta = $_GET["id_consulta"];
		
		$db = new DB( "SELECT * FROM CFGCONSULTAS_CATALOGO WHERE ID_BIBLIOTECA=$id_biblioteca and ID_CONSULTA=$id_consulta" );
		
		if( $db->NextRow() ) 
		{ 
			$descripcion = $db->row["DESCRIPCION"];
			
			$keywords    = $db->row["INCLUIR_PALABRASCLAVE"];
			$title       = $db->row["INCLUIR_TITULO"];
			$author      = $db->row["INCLUIR_AUTOR"];
			$subjects    = $db->row["INCLUIR_MATERIAS"];
			$callnumber  = $db->row["INCLUIR_SIGNATURA_TOPOG"];
			$isbn        = $db->row["INCLUIR_ISBN"];
			$issn        = $db->row["INCLUIR_ISSN"];
			
			$recs_x_page = $db->row["REGISTROS_X_PAGINA"];
			
			// anexos
			$show_digital_attachments     = $db->row["MOSTRAR_ARCHIVOS_ANEXOS"];
			$download_digital_attachments = $db->row["PERMITIR_DESCARGAR_ANEXOS"];
			
			$administrative = $db->row["ADMINISTRATIVA"];
			$opac			= $db->row["OPAC"];
			$only_readers   = $db->row["LECTORES"];
			
			// view content
			$allow_cardview         = $db->row["PERMITIR_CONSULTAR_TITULOS"];
			$allow_view_of_copies   = $db->row["MOSTRAR_ITEMS_EXISTENTES"];
			$allow_view_of_iteminfo = $db->row["PERMITIR_CONSULTAR_ITEMS"];
			
			// view style
			$allow_view_in_marcstyle = $db->row["MOSTRAR_FICHAS_MARC"];
			$allow_view_in_aacr2style = $db->row["MOSTRAR_FICHAS_AACR2"];
			
			$age_filtered 			 = $db->row["FILTRA_X_GRUPO_EDAD"];
			$allow_filter_x_material = $db->row["MOSTRAR_FILTRO_X_MATERIAL"];
			
			$active = $db->row["ACTIVA"];
			
			$the_action = "save_changes";  // acción derivada natural
		}
		
		$db->destroy();
	}
	else if( $the_action == "delete" )
	{
		$consultas = "";
		$consultas_borradas = 0;
		
		if( isset($_GET["consultas"]) )
		{
			$consultas = $_GET["consultas"];
			
			$consultas = str_replace( "@", "ID_CONSULTA=", $consultas ); // 1st ocurrence
			$consultas = str_replace( ":", " or ID_CONSULTA=", $consultas ); // other ocurrences
			
			$db = new DB( "DELETE FROM CFGCONSULTAS_CATALOGO WHERE ID_BIBLIOTECA=$id_biblioteca and ($consultas) " );
			
			$error = 30;
			
			$consultas_borradas = $db->rowsAffected;
			
			$db->Destroy();
			
			require_once( "../actions.inc.php" );
			agregar_actividad_de_usuario( CFG_CHANGE_PARAMS_QUERIES_DELETE, "$ACTION_DESCRIP_DELETE $consultas <$consultas_borradas>" );
		}
		
		if( !allow_use_of_popups() )
			ges_redirect( "conf_consultas.php?id_consultas_borradas=$consultas_borradas" );
	}
	else
	{
		$the_action = "create_new";  // acción por default
	}
	
	/** FIN FUNCIONES DE MODIFICACION/CREACION/BORRADO **/
	
	// Coloca un encabezado HTML <head>
	include "../basic/head_handler.php";
	HeadHandler( ($the_action == "create_new") ? $LBL_HEADER_V1 : $LBL_HEADER_V2, "../");
		
?>

<SCRIPT language="JavaScript">

	function validar( e )
	{
		var error = 0;
		
		if( document.edit_form.txt_qryname.value == "" )		
		{
			error = 1;
			alert( "<?php echo $VALIDA_MSG_NONAME;?>" );
			document.edit_form.txt_qryname.focus();
		}

		if( error == 0 )
		{
			document.edit_form.submit();
			return true;
		}
		else
			return false;
	}
	
	window.onload=function()
	{
		prepareInputsForHints();
		document.edit_form.txt_qryname.focus();
	}	
	
	
</SCRIPT>

<STYLE>

 #caja_datos1 {
   float: left; 
   width: 750px; 
   }
  
  #buttonarea { border: 0px solid red;  }; 
  #btnActualizar { position:absolute; left:0em; } 
  #btnCancelar { position:absolute; left:17em; } 
  
<?php
	if( allow_use_of_popups() )
		echo "#contenedor { width: 900px; margin-top: 10px; } ";
?>  
  
</STYLE>

<body id="home">

<?php
  // barra de navegación superior
  if( !allow_use_of_popups() )
	display_global_nav();  
	
  if( allow_use_of_popups() )
  {
	//
	// cuando POPUPS
	//
	if( $error == 10 )
	{
		SYNTAX_BEGIN_JavaScript();
		echo "alert('$SAVE_CREATED_DONE');";
		echo "window.opener.document.location.reload();";
		echo "window.close();";		
		SYNTAX_CLOSE_JavaScript();
	}
	else if( $error == 20 )
	{
		SYNTAX_BEGIN_JavaScript();
		echo "alert('$SAVE_EDIT_DONE');";
		echo "window.opener.document.location.reload();";
		echo "window.close();";
		SYNTAX_CLOSE_JavaScript();
	}
	else if( $error == 30 )
	{
		SYNTAX_BEGIN_JavaScript();
		echo "alert('$DELETE_DONE');";
		echo "window.opener.document.location.reload();";
		echo "window.close();";
		SYNTAX_CLOSE_JavaScript();
	}		
  }
 ?>

<!-- contenedor principal -->
<div id="contenedor">

<?php 

   if( !allow_use_of_popups() )
   {
	   // banner
	   display_banner();  
	   
	   // menu principal
	   display_menu( "../" ); 
   }

 ?>
 
<div id="bloque_principal"> <!-- inicia contenido -->
 
 <div id="contenido_principal">

	<div class=caja_datos id=caja_datos1 >
		<h2>
		<?php 
			if( $the_action == "create_new" ) echo $LBL_HEADER_V1;
			else							  echo $LBL_HEADER_V2;
		?>
		<HR></h2>
		
		<?php
			if( $error == 2 )
			{
				echo "<div class=caja_errores>";
				echo " <strong>$MSG_ERROR_SAVING_CHANGES</strong>";
				echo "</div>";
			}
			else if( $error == 10 )
			{
				echo "<div class=caja_info>";
				echo " <strong>$SAVE_CREATED_DONE</strong>";
				echo "</div>";
				
				// Update Parent
				SYNTAX_BEGIN_JavaScript();
				echo "window.opener.document.location.reload();";
				SYNTAX_CLOSE_JavaScript();				
			}
			else if( $error == 20 )
			{
				echo "<div class=caja_info>";
				echo " <strong>$SAVE_DONE</strong>";
				echo "</div>";
				
				// Update Parent
				SYNTAX_BEGIN_JavaScript();
				echo "window.opener.document.location.reload();";
				SYNTAX_CLOSE_JavaScript();				
			}

		 ?>

			<form action="conf_crearconsultas.php" method="post" name="edit_form" id="edit_form" class="forma_captura">
			  <input class='hidden' type='hidden' name="the_action" id="the_action" value="<?php echo $the_action;?>">
			  <input class='hidden' type='hidden' name="id_consulta" id="id_consulta" value="<?php echo $id_consulta; ?>">
			  
				<label for="txt_id_consulta"><strong><?php echo $LBL_IDQRY;?></strong></label>
				<span><?php echo $id_consulta;?></span>
				<br><br>
			  		  
				<dt>	
					<label for="txt_qry_name"><strong><?php echo $LBL_QRYNAME;?></strong></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_qryname" id="txt_qryname" style='display:inline;' value="<?php echo $descripcion;?>" size=50>
					&nbsp;&nbsp;&nbsp;
					<input class="checkbox" style='display:inline;' type="checkbox" name="chk_active" id="chk_active" <?php echo (($active=="S") ? "checked" : ""); ?>>&nbsp;&nbsp;<?php echo $LBL_IS_ACTIVE;?>
					<span class="sp_hint"><?php echo $HINT_QUERYNAME;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				<br>
				
				<!-- Palabras Clave -->
				<dt>
					<label for="chk_keywords"><strong><?php echo $LBL_QRY_CHOICES;?></strong></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_keywords" id="chk_keywords" <?php echo (($keywords=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_INCLUDE_KEYWORDS;?></span>
				</dd>

				<!-- Titulo -->
				<dt>					
					<label for="chk_title">&nbsp;</label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_title" id="chk_title" <?php echo (($title=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_INCLUDE_TITLE;?></span>
				</dd>
				
				<!-- Author -->
				<dt>
					<label for="chk_author">&nbsp;</label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_author" id="chk_author" <?php echo (($author=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_INCLUDE_AUTHOR;?></span>
				</dd>
				
				<!-- Materias -->
				<dt>
					<label for="chk_subjects">&nbsp;</label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_subjects" id="chk_subjects" <?php echo (($subjects=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_INCLUDE_SUBJECTS;?></span>
				</dd>
				
				<!-- Signatura Topográfica -->
				<dt>
					<label for="chk_callnumber"></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_callnumber" id="chk_callnumber" <?php echo (($callnumber=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_INCLUDE_CALLNUMBER;?></span>
				</dd>

				<!-- ISBN -->
				<dt>
					<label for="chk_isbn"></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_isbn" id="chk_isbn" <?php echo (($isbn=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_INCLUDE_ISBN;?></span>
				</dd>
				
				<!-- ISSN -->
				<dt>
					<label for="chk_issn"></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_issn" id="chk_issn" <?php echo (($issn=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_INCLUDE_ISSN;?></span>
				</dd>

				<br>

				<!-- RECORDS PER PAGE -->
				<dt>
					<label for="txt_ciudad"><strong><?php echo $LBL_RECS_X_PAGE;?></strong></label>
				</dt>
				<dd>
					<select name='cmb_recs_x_page' id='cmb_recs_x_page' class='select_captura'>
						<option value='5'  <?php echo ($recs_x_page==5) ? "selected": ""; ?> ><?php echo $HINT_PAGE_5;?></option>
						<option value='10' <?php echo ($recs_x_page==10) ? "selected": ""; ?> ><?php echo $HINT_PAGE_10;?></option>
						<option value='15' <?php echo ($recs_x_page==15) ? "selected": ""; ?> ><?php echo $HINT_PAGE_15;?></option>
						<option value='20' <?php echo ($recs_x_page==20) ? "selected": ""; ?> ><?php echo $HINT_PAGE_20;?></option>
					</select>
					<span class="sp_hint"><?php echo $HINT_RECS_X_PAGE;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				<br>
				
				<!-- Digital Content -->
				<dt>
					<label for="chk_show_digital_attachments"><strong><?php echo $LBL_ATTACHMENTS;?></strong></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_show_digital_attachments" id="chk_show_digital_attachments" <?php echo (($show_digital_attachments=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_SHOW_ATTACHMENTS;?></span>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input class="checkbox" type="checkbox" name="chk_download_digital_attachments" id="chk_download_digital_attachments" <?php echo (($download_digital_attachments=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_DOWNLOAD_ATTACHMENTS;?></span>
				</dd>
				<br>
		
				<!-- Type of query -->
				<!-- administratives -->
				<dt>
				<label for="chk_only_4_administratives"><strong><?php echo $LBL_QUERY_TYPE;?></strong></label>
				</dt>
				<dd>
					<select name='cmb_query_type' id='cmb_query_type' class='select_captura''>
						<option value='OPAC' <?php echo ($opac=="S") ? "selected": ""; ?> ><?php echo $LBL_IS_4_OPAC;?></option>
						<option value='ADMIN' <?php echo ($administrative=="S") ? "selected": ""; ?>><?php echo $LBL_IS_4_ADMINISTRATIVE;?></option>
						<option value='READERS' <?php echo ($only_readers=="S") ? "selected": ""; ?> ><?php echo $LBL_IS_4_READERS;?></option>
					</select>
					<span class="sp_hint"><?php echo $HINT_QUERYTYPE;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				<br>

				<!-- allow cards-view -->
				<dt>
					<label for="chk_allow_cardview"></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_allow_cardview" id="chk_allow_cardview" <?php echo (($allow_cardview=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_ALLOW_CARDVIEW;?></span>
				</dd>				
				
				<!-- allow view of copies -->
				<dt>
					<label for="chk_allow_view_of_copies"></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_allow_view_of_copies" id="chk_allow_view_of_copies" <?php echo (($allow_view_of_copies=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_ALLOW_VIEW_OF_COPIES;?></span>
				</dd>

				<!-- allow view of each item information -->
				<dt>
					<label for="chk_allow_view_of_iteminfo"></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_allow_view_of_iteminfo" id="chk_allow_view_of_iteminfo" <?php echo (($allow_view_of_iteminfo=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_ALLOW_VIEW_ITEMINFO;?></span>
				</dd>		
				<br>
				
				<!-- RESULTS -->
				<!-- allow view of each item information -->
				<dt>
					<label for="chk_view_marc_style"><strong><?php echo $LBL_VIEW_STYLE;?></strong></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_view_marc_style" id="chk_view_marc_style" <?php echo (($allow_view_in_marcstyle=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_ALLOW_VIEW_MARCSTYLE;?></span>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input class="checkbox" type="checkbox" name="chk_view_aacr_style" id="chk_view_aacr_style" <?php echo (($allow_view_in_aacr2style=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_ALLOW_VIEW_AACR2STYLE;?></span>
				</dd>						
				<br>
				
				<!-- AGE FILTERED and MATERIAL FILTER -->
				<dt>
					<label for="chk_age_filtered"></label>
				</dt>
				<dd>
					<input class="checkbox" type="checkbox" name="chk_age_filtered" id="chk_age_filtered" <?php echo (($age_filtered=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_AGE_FILTERED;?></span>
					&nbsp;&nbsp;&nbsp;&nbsp;
				<input class="checkbox" type="checkbox" name="chk_show_materialfilter" id="chk_show_materialfilter" <?php echo (($allow_filter_x_material=="S") ? "checked" : ""); ?>>
					&nbsp;<span><?php echo $LBL_ALLOW_MATERIAL_FILTERED;?></span>					
				</dd>
	
				<br>
				
				<!-- ORDERED BY -->
				<dt>
				<label for="cmb_order_by"><strong><?php echo $LBL_ORDERED_BY;?></strong></label>
				</dt>
				<dd>
					<select name='cmb_order_by' id='cmb_order_by'>
						<option value='TITLE' <?php echo ($ordered_by=="TITLE") ? "selected": ""; ?> ><?php echo $LBL_ORDER_BY_TITLE;?></option>
						<option value='AUTHOR' <?php echo ($ordered_by=="AUTHOR") ? "selected": ""; ?> ><?php echo $LBL_ORDER_BY_AUTHOR;?></option>
						<option value='CALLNUMBER' <?php echo ($ordered_by=="CALLNUMBER") ? "selected": ""; ?> ><?php echo $LBL_ORDER_BY_CALLNUMBER;?></option>
						<option value='TITLEEDITION' <?php echo ($ordered_by=="TITLEEDITION") ? "selected": ""; ?> ><?php echo $LBL_ORDER_BY_TITLEANDEDITION;?></option>
					</select>
				</dd>
				<br>				

				<div id="buttonarea">
					<input id="btnGuardar" name="btnGuardar" class="boton" type="button" value="<?php echo $BTN_SAVE;?>"  onClick='javascript:validar();'>&nbsp;
					<input id="btnCancelar" name="btnCancelar" class="boton" type="button" value="<?php echo $BTN_CANCEL;?>" onClick='<?php echo back_function();?>'>
				</div>
				<br> <!-- for IE -->
			  
			</form>
	  
	</div> <!-- caja_datos --> 

 </div> <!-- contenido_principal -->

 <div id="contenido_adicional">
	&nbsp;
  </div> <!-- contenido_adicional -->
  
  <br style='clear:both;'>

</div>
<!-- end div bloque_principal -->

<?php  if( !allow_use_of_popups() ) display_copyright(); ?>

</div><!-- end div contenedor -->

</body>

</html>