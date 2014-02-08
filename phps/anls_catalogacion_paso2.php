<?php
	session_start();

	/*******
	  Historial de Cambios
	  
	  Permite editar un registro MARC
	  
	  03 abr 2009: Se crea el archivo catalogacion.php.
	  23 abr 2009: Se colocan los elementos generales del campo 008.
	  24 abr 2009: Se inicializan los elementos específicos del campo 008.
	  27 abr 2009: Se intenta registrar un movimiento.
	  29 abr 2009: Se abre tesauro
	  01 may 2009: Se genera automáticamente el campo 008, según lo que se tiene en pantalla.
	  09 may 2009: Se ajusta la importación ISO 2709, cuando vienen campos MARC duplicados.
	  25 may 2009: Se trabaja en el auto-ajuste de los campos de captura.
	  23 sep 2009: Se agregan datos por default cuando no se elije plantilla
				   Se perfecciona el asunto de catalogar una serie.	
	  07 oct 2009: Se agrega un vínculo para indicar cuando se importa un campo que no existe o no está vigente (está obsoleto)
	  10 nov 2009: Trabajos de afinación durante el guardado y elección de campos por agregar.
	  07 mar 2012: Al agregar un campo o subcampo... volvia a leer la plantilla duplicando campos l: 1189
	  
	  PENDIENTE:

	     - DUPLICAR CAMPOS o subcampos VERIFICANDO CUALES SON NR o R
		 - VERIFICAR QUE EN LOS ESPECIFICOS DEL CAMPO 008, SI SON 4 CARACTERES se llenen los 4... o
		    al menos que se complementen con #.
		 - Reordenar subcampos

     */
		
	include "../funcs.inc.php";
	include "../basic/bd.class.php";
	
	include_language( "global_menus" );
	
	check_usuario_firmado(); 

	// Coloca un encabezado HTML <head>
	include "../basic/head_handler.php";
	
	include_language( "anls_catalogacion" );	
	
	$id_biblioteca = getsessionvar("id_biblioteca");

	$the_action    = read_param( "the_action", "create_new" );
	$middle_action = read_param( "middle_action", "" ); 
	$id_plantilla  = read_param( "id_plantilla", "0" );
	
	$id_titulo     = read_param( "id_titulo", "0" );
	$id_serie  	   = read_param( "id_serie", "0" );
	
	$auto_select   = read_param( "auto_select", "0" );
	
	$db = new DB();
	
	if( $auto_select == 1 )
	{
		// buscar la plantilla de publicaciones periodicas
		if( $id_plantilla == 0 )
		{
			//
			$db->Open( "SELECT a.ID_PLANTILLA FROM cfgplantillas_nombres a " .
					    " LEFT JOIN marc_material b ON (b.ID_TIPOMATERIAL=a.ID_TIPOMATERIAL) " .
					   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TIPO='CAT' and b.PUBLICACION_SERIADA='S'; " );

			if( $db->NextRow() )
			{ 	
				$id_plantilla = $db->Field("ID_PLANTILLA"); 
			}

			$db->FreeResultset();			
		}
	}
	
	$usuario_creo 	  = getsessionvar("id_usuario");
	$fecha_registro = "";
	
	$usuario_modifico   = 0;
	$fecha_modificacion = "";
	
	$usuario_catalogo   = "";
	$fecha_catalogacion = "";
	
	include "catalog.inc.php";
	load_getpost_vars();

	$nombre_plantilla = "";
	
	if( $id_plantilla != 0 )
	{
		$db->Open( "SELECT NOMBRE_PLANTILLA FROM cfgplantillas_nombres WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PLANTILLA=$id_plantilla" );

		if( $db->NextRow() )
		{ 
			$nombre_plantilla = $db->row["NOMBRE_PLANTILLA"]; 
		}

		$db->Close();		
	}
	
	if( $id_serie != 0 and $id_plantilla==0 and $id_titulo==0)
	{
		$db->Open( "SELECT NOMBRE FROM series WHERE ID_BIBLIOTECA=$id_biblioteca and ID_SERIE=$id_serie; " );

		if( $db->NextRow() )
		{ 	
			$nombre_serie = $db->Field("NOMBRE"); 
		}

		$db->Close();	
	}
	
	HeadHandler( $LBL_CATALOG_HEADER_2 . ": " . $nombre_plantilla, "../");
	
	$contenido = "";
	
	if( isset($_POST["uploadfile"]))
		if( is_uploaded_file($_FILES['userfile']['tmp_name']) ) 
		{	  
			$nombre_archivo = $_FILES['userfile']['tmp_name'];
			
			$gestor = fopen($nombre_archivo, "rb");
			$contenido = fread($gestor, filesize($nombre_archivo));
			fclose($gestor);
		} 
	
	if( $id_titulo != 0 )
	{
		$the_action = "edit";
	}

?>

<script type="text/javascript" language="JavaScript">

	var last_tiporegistro = "";
	
	var cur_div = null;
	var cur_field_editing = null;
	
	var aField008_elements = new Array();
	var aMARCFields = new Array();
	
	// Flags que se utilizan para mostrar una alerta
	// antes de salir o _POST o _GET o simplemente refrescar los datos de la catalogacion
	var modified = false;	
	var updating_item_descriptors = false;
	var saving_record = false;
	var click_on_a_link = false;
	
	function see_field_info( url )
	{
		js_ProcessActionURL( 1, url, "info_field", "700", "500" );
	}

	function Hilite( the_div )
	{
		if( the_div != cur_div ) 
			the_div.style.backgroundColor = "#E7F5F7";
	}

	function UnHilite( the_div )
	{
		if( cur_div != the_div ) 
		{ the_div.style.background = ""; }
	}

	function SearchFields( fieldname, like_behavior )
	{
		var ret = -1;
		var len = fieldname.length;
		
		for( var i=0; i<aMARCFields.length; i++ )
		{
			if( like_behavior == 0 )
			{
				if( aMARCFields[i] == fieldname )
				{
					ret = i;
					break;
				}
			}
			else
			{
				if( aMARCFields[i].substr(0,len) == fieldname )
				{
					ret = i;
					break;
				}
			}
		}

		return ret;
	}
	
	//
	// Esta función permite eliminar un elemento de captura
	// puede ser un subcampo
	//
	function DeleteElement()
	{
		if( cur_field_editing != null )
		{			
			var parent = cur_field_editing.parentNode;
			var div_master = parent.parentNode;  // div que contiene a todos los INPUT de cada campo MARC
			
			var base_field_component;
			
			parent.removeChild(cur_field_editing);	// quita el INPUT / TEXTAREA

			div_master.removeChild(parent);   // remover el DIV contenedor de cur_field_editing
			
			// obtener base field component name de
			// los primeros 7 caracteres del nombre del campo
			// + más otros 3 caracteres del UNIQUE ID del campo en el array
			base_field_component = cur_field_editing.name.substr( 0, 10 ); 
			
			var prev_name = "";
			
			var pos = SearchFields( cur_field_editing.name, 0 ); // busqueda exacta
			
			if( pos != -1 )
			{ 
				aMARCFields.splice( pos, 1 );

				if( pos < aMARCFields.length )
				{
					prev_name = aMARCFields[pos]; // obtiene el siguiente control
					
					if( prev_name.substr( 0, 3 ) != "txt" )
					{
						if( pos < aMARCFields.length )
							prev_name = aMARCFields[pos+1];
					}
				}
			}
			
			cur_field_editing = null;  // pointer resets to NIL
			
			// verificar que haya un checkbox en el INPUT.checkbox correspondiente
			// al popup de agregar campos
			var chk_field_id = js_getElementByName( "chk_fld_" + base_field_component.substr(4,3) );
			
			if( chk_field_id.disabled );
			{
				chk_field_id.disabled = false;  // volver a habilitar
				
				//alert( "lbl_fld_" + base_field_component.substr(4,3) );
				
				var lbl_field_id = js_getElementByName( "lbl_fld_" + base_field_component.substr(4,3) );
				
				///alert( lbl_field_id );  it comes in NIL in IE 8.0 
				
				if( lbl_field_id )
				{ lbl_field_id.style.color = null; }
			}
			
			// verificar cuantos txt_??? quedan de este campo
			// si no queda ninguna entonces borrar todo rastro
			var how_many_left = SearchFields( base_field_component, 1 ); // busqueda LIKE %
			
			if( how_many_left == -1 )
			{
				var div_of_field = div_master.parentNode;  // subir al DIV de mayor rango por cada campo
				
				// eliminar el DIV completo de mayor rango del campo
				div_of_field.parentNode.removeChild( div_of_field );
				
				// eliminar elemento de array del tipo new_???
				var cControl_New_Name = "new_"+base_field_component.substr(4,10);
				
				if( cControl_New_Name.substr( cControl_New_Name.length-1, 1 ) == "@" )
					cControl_New_Name = cControl_New_Name.substr( 0, cControl_New_Name.length-1 ); // recorta
				
				var pos = SearchFields( cControl_New_Name, 1 ); // busqueda aproximada

				if( pos != -1 )
				{
					aMARCFields.splice( pos, 1 );
				}	
			}
			
			// colocar el foco de edición
			if( prev_name != "" )
			{
				var new_obj = js_getElementByName( prev_name );
				
				if( new_obj ) 
					new_obj.focus();
			}		
		}
	}
	
	function set_focus_div( control, hint_4_text )
	{
		if( cur_div != null ) 
			cur_div.style.background = "";
		
		cur_div = control.parentNode.parentNode;
		cur_div.style.backgroundColor = "#EFF9BE";
		
		cur_field_editing = control;
		
		var status_div = js_getElementByName( "status_div" );
		
		if( status_div )
		{		
			status_div.innerHTML = hint_4_text;
			status_div.style.visibility = "visible";
			status_div.style.display = "inline";
			window.status = hint_4_text;
		}
	}
	
	function reloj() 
	{
		var scrolledY;
		var status_div  = js_getElementByName("status_div");
		var buttons_bar  = js_getElementByName("buttonBar");
		
		var div_4_block =  js_getElementByName("popUp");
		
		if( div_4_block.style.visibility == "visible" ) return;
		
		if( self.pageYOffset ) 
		{
		    scrolledY = self.pageYOffset;
		} 
		else if( document.documentElement && document.documentElement.scrollTop ) 
		 {
		    scrolledY = document.documentElement.scrollTop;
		 } 
		else if( document.body ) 
		 {
		    scrolledY = document.body.scrollTop;
		 }
		
		status_div.style.top = scrolledY + 10 + "px";
		status_div.style.zIndex = 1;		
		
		buttons_bar.style.position = "relative";
		buttons_bar.style.top = scrolledY + "px";
		buttons_bar.style.zIndex = 30;		
	}

	// Se cambia el tipo de material
	// Se conservan los datos que se están editando
	function change_tiporegistro()
	{
		var tmp_val = js_getElementByName_Value( "cmbTipoRegistro" ) ;
		
		if( tmp_val != last_tiporegistro )
		{
			document.marc.id_tipomaterial.value       = js_getElementByName_Value( "id_tipomaterial" );  // campo de control
			
			document.marc.id_tiporegistro.value       = js_getElementByName_Value( "cmbTipoRegistro" );			
			document.marc.id_estadoregistro.value     = js_getElementByName_Value( "cmbEstadoRegistro" );
			document.marc.id_nivelbibliografico.value = js_getElementByName_Value( "cmbNivelBibliografico" );
			document.marc.id_nivelcodificacion.value  = js_getElementByName_Value( "cmbNivelCodificacion" );
			document.marc.id_formacatalogacion.value  = js_getElementByName_Value( "cmbFormaCatalogacion" );

			document.marc.field_008.value = Generate_Field008();				
			document.marc.marc_fields.value = Generate_FieldList();
			
			document.marc.middle_action.value = "reload";
			document.marc.action = "anls_catalogacion_paso2.php";
			
			updating_item_descriptors = true;

			document.marc.method = "POST";
			document.marc.target = "_self";
			document.marc.submit();
		}
	}

	// muestra el popup según su ID
	function showPopupMenu( id )
	{
		var divID = js_getElementByName( "popupMenu" + id );

		if( divID )
		{ 
			mopen_popup( divID ); 
		}
	}
		
	function showPopupMenu_and_Relocate( id, alink )
	{
		var divID = js_getElementByName( "popupMenu" + id );
		
		alert( alink.title );
		
		if( divID )
		{ 
			mopen_popup( divID ); 
		}
	}	
	
	function setValue( name, val, hint )
	{
		var obj = js_getElementByName( name );
		
		if( obj )
		{
			obj.value = val;
			obj.title = hint;
			
			if( obj.name == "cmbTipoRegistro" )
			{ change_tiporegistro(); }		
		}
	}
	
	function ShowPopupDIV( name )
	{
		var divID = js_getElementByName( name );
		
		if( divID )
		{
			divID.style.display = "inline";
			divID.style.visibility = "visible";

			divID.style.left = ((screen.width - 700) / 2) - 50;
		}
	}
	
	function closePopupDIV( name )
	{
		var divID = js_getElementByName( name );
		
		if( divID )
		{
			divID.style.display = "none";
			divID.style.visibility = "hidden";
		}
	}
	
	function SetMarcHINT( hint )
	{
		var divID;
		
		if( divID = js_getElementByName("marc_hint") )
		{
			if( hint == "" ) hint = "&nbsp;";
			
			divID.innerHTML = hint;
		}
	}	

	function importar_registro()
	{	
		if( document.import_file.userfile.value == "" )
			alert( "Para continuar debe seleccionar un archivo para subir." );
		else
		{
			document.import_file.method = "POST";
			document.import_file.target = "_self";
			document.import_file.submit();
		}
	}
	
	function showPopupMenuTesauro( id_categoria, input_name, codigo_o_termino )
	{
		var nwidth = screen.width;
		var nheight = screen.height; 
		var url = "gral_abrirtesauro.php?id_categoria=" + id_categoria + "&control=" + input_name;
		
		var id_control = js_getElementByName( input_name );
		
		if( id_control )
		{
			url = url + "&now=" + id_control.value;
		}
		
		if( codigo_o_termino != 0 )
			url = url + "&descrip_termino=1";
			
		window.open( url, "Popup_Tesauro", "WIDTH=" + (nwidth-250) + ",HEIGHT=" + (nheight-220) + ",TOP=20,LEFT=30,resizable=yes,scrollbars=yes,status=yes" );
	}
	
	function add_subfields( id_campo )
	{	
		var input_aux_params = document.getElementsByName( "aux_params" ); 
		var nwidth = screen.width;
		var nheight = screen.height; 
		
		input_aux_params[0].value = id_campo + ":";	  // dos puntos es un separador
													  // es un TRUCO para que el primer elemento del array sea el codigo del campo

		// 1st parm 1 = Dialog
		js_ProcessActionURL( 1, "gral_elegir_subcampo.php?id_campo=" + id_campo, "subfields", nwidth-300, 600 );
		
	}
	
	// Será llamada desde gral_elegir_subcampos.php
	function nuevosSubCampos() 
	{
		var input_aux_params = document.getElementsByName( "aux_params" ); 
		
		if( input_aux_params.length > 0 )
		{
			document.marc.id_tipomaterial.value       = js_getElementByName_Value( "id_tipomaterial" );
			
			document.marc.id_tiporegistro.value       = js_getElementByName_Value( "cmbTipoRegistro" );
			document.marc.id_estadoregistro.value     = js_getElementByName_Value( "cmbEstadoRegistro" );
			document.marc.id_nivelbibliografico.value = js_getElementByName_Value( "cmbNivelBibliografico" );
			document.marc.id_nivelcodificacion.value  = js_getElementByName_Value( "cmbNivelCodificacion" );
			document.marc.id_formacatalogacion.value  = js_getElementByName_Value( "cmbFormaCatalogacion" );

			document.marc.field_008.value = Generate_Field008();				
			document.marc.marc_fields.value = Generate_FieldList();
			//document.marc.newFields.value = detectMarkedFields();  
			
			document.marc.action = "anls_catalogacion_paso2.php";

			updating_item_descriptors = true;
			
			document.marc.middle_action.value = "reload";
			document.marc.method = "POST";
			document.marc.target = "_self";
			document.marc.submit();			
		}
	}	
	
	function padr( string, maxlen, fillchar )
	{
		var len = string.length;
		
		if ( len > maxlen )
		   return string.substr(0,maxlen);
		else
		{
			for( var i=len; i<maxlen; i++ )
			{ string += fillchar; }

		   return string;
		}
	}
	
	//
	// Genera el campo 8 de acuerdo
	// a los esquemas MARC
	//
	// Computes field 008 acording to MARC standards
	//
	function Generate_Field008()
	{
		var ret = "aaaaaa&????!!!!@@@                 %%%rf";
		//var ret = "aaaaaa&????!!!!@@@.................%%%rf";
//				     0123456789012345678901234567890123456789
//				               1         2         3
		// primero letras
		ret = ret.replace( "aaaaaa", padr(js_getElementByName_Value( "txt008_FechaRegistro" ),6," ") );
		ret = ret.replace( "r", padr(js_getElementByName_Value( "cmb008_RegistroModificado" ),1," ") );
		ret = ret.replace( "f", padr(js_getElementByName_Value( "cmb008_FuenteCatalogacion" ),1," ") );

		// luego códigos
		ret = ret.replace( "&", padr(js_getElementByName_Value("cmb008_TipoFechaSt"),1," ") );
		ret = ret.replace( "????", padr(js_getElementByName_Value("txt008_Fecha1"),4," ") );
		ret = ret.replace( "!!!!", padr(js_getElementByName_Value("txt008_Fecha2"),4," ") );
		ret = ret.replace( "@@@", padr(js_getElementByName_Value("txt008_LugarPublicacion"),3," ") );
		ret = ret.replace( "%%%", padr(js_getElementByName_Value("txt008_Idioma"),3," ") );
		
		//
		// campos específicos (por tipo de material)
		//
		if( aField008_elements.length > 0 )
		{
			var obj_name;
			var tmp_str;
			var tmp_val;
			var aPositions;
			var count;
			
			for( var i=0; i<aField008_elements.length; i++ )
			{
				obj_name = aField008_elements[i].substr( 7, 200 );
				tmp_val = js_getElementByName_Value( aField008_elements[i] );
				aPositions = obj_name.split("_TO_");
				
				tmp_str = ret; 
				
				if( aPositions.length == 1 )
				{
					if( tmp_val == "" ) tmp_val = "#";
					
					ret  = tmp_str.substr( 0, parseInt(aPositions[0]) ) + tmp_val;
					ret += tmp_str.substr( parseInt(aPositions[0])+1, 255 );
				}
				else if( aPositions.length == 2 )
				{
					count = parseInt(aPositions[1])-parseInt(aPositions[0]);
					
					if( tmp_val == "" ) 
					{ tmp_val = " "; }
					
					ret  = tmp_str.substr( 0, parseInt(aPositions[0]) ) + tmp_val;
					ret += tmp_str.substr( parseInt(aPositions[0])+1+count, 255 );
				}
			}
		}
		
		if( ret.length > 40 )
			alert( "MUY LARGO campo 008 " + ret.length );
			
		return ret;
	}
	
	//
	// Genera la lista codificadas de campos que se pasarán en el POST para
	//   crear, editar o agregar nuevos campos
	//
	function Generate_FieldList()
	{
		var list = "";
		var posID1, posID2;
		var obj_name;
		var obj;
		var pos;
		
		var cID1;
		var cID2;
		
		for( var i=0; i<aMARCFields.length; i++ )
		{
			field_def = aMARCFields[i];
			
			//alert( field_def );
			
			if( list != "" )
				list += ","; 
			list += field_def;
			
			posID1=field_def.indexOf("X1",0);
			posID2=field_def.indexOf("X2",0);
			
			// carga el contenido de los INDICADORES
			// en el contenedor creado para cada campo que los
			// necesite
			if( posID1 != -1 || posID2 != -1 )
			{
				obj_name = field_def.substr(4,100);
				pos = obj_name.indexOf(" ",0);
				obj_name = obj_name.substr(0,pos);
				
				obj = document.getElementsByName( "XX1_" + obj_name );
				
				if( obj.length > 0 )
				{			
					obj[0].value = "";
					
					cID1 = js_getElementByName_Value( "id1_" + obj_name );
					cID2 = js_getElementByName_Value( "id2_" + obj_name );
					
					// Identificador 1
					if( posID1 !=-1 && cID1 != "" )
						obj[0].value = "X1=" + cID1;
						
					// Identificador 2
					if( posID2 !=-1 && cID2 != "" )
					{
						if( obj[0].value != "" )
							obj[0].value += "&";
						obj[0].value += "X2=" + cID2;
					}
				}
			}
		}
		
		return list;
	}
	
	function SaveChanges( obligatorio_marc_100 )
	{
		
		if( aMARCFields.length >= 1 )
		{			
			var error = 0;
			
			if( obligatorio_marc_100 == "S" )
			{
				var pos = SearchFields( "txt_100", 1 );
				
				if( pos == -1 ) 
				{
					alert( "<?php echo $MSG_FIELD100_MANDATORY;?>" );
					error = 1;
				}
			}
			
			if( error == 0 )
			{			
				if( confirm( octal("<?php echo ($the_action=="edit") ? $MSG_WANT_TO_SAVE_CHANGES : $MSG_WANT_TO_CREATE_RECORD;?>" ) ) )
				{
					document.marc.id_tipomaterial.value       = js_getElementByName_Value( "id_tipomaterial" );
					
					document.marc.id_tiporegistro.value       = js_getElementByName_Value( "cmbTipoRegistro" );
					document.marc.id_estadoregistro.value     = js_getElementByName_Value( "cmbEstadoRegistro" );
					document.marc.id_nivelbibliografico.value = js_getElementByName_Value( "cmbNivelBibliografico" );
					document.marc.id_nivelcodificacion.value  = js_getElementByName_Value( "cmbNivelCodificacion" );
					document.marc.id_formacatalogacion.value  = js_getElementByName_Value( "cmbFormaCatalogacion" );

					document.marc.field_008.value = Generate_Field008();				
					document.marc.marc_fields.value = Generate_FieldList();

					//document.marc.the_action = "create_new";
					document.marc.action = "anls_catalogacion_save.php";
					
					saving_record = true;
				
					document.marc.submit();
				}
			}
		}
		else
			alert( "<?php echo $MSG_NO_FIELDS_AT_ALL;?>" );
	}
	
	function validaInfoEntered( e )
	{
		var size_rows = "15";

		if( e.value.length > 70 )
		{
			var size_rows = e.value.length / 70;
			
			size_rows = parseInt(size_rows);
			
			if( ( e.value.length % 70 ) > 0 )
				size_rows++;

			size_rows = parseInt(size_rows) * 15;			
		}

		var str_size = size_rows + "px";
		
		if( e.style.height != str_size )
			e.style.height = str_size; 
			
		modified = true;
	}

	function showDialogIndicadores( e, campo, input_name, id )
	{
		var nwidth = screen.width;
		var nheight = screen.height; 
		var id_control = js_getElementByName( input_name );
		var url = "gral_indicadores.php?campo='" + campo + "'&control=" + input_name + "&id=" + id;
		
		if( id_control )
		{  
			url = url + "&val=" + id_control.value; 
		}
		
		var ret = showModalDialog( url, "", "dialogLeft:100px;dialogWidth:760px;dialogHeight:500px;center:yes;status:no;" );
		
		if( ret != null && id_control )
		{
			id_control.value = ret;
		}
	}

	//
	// Esta función genera la lista de campos seleccionados para Agregar
	//
	function detectMarkedFields()
	{
		var aFields = js_getElementsByClassName( "checkbox", js_getElementByName("popup_campos") );
		var chkObj;
		var ret = "";
		
		for( var i=0; i<aFields.length; i++ )
		{
			chkObj = aFields[i];
							
			if((chkObj.name.substr(0,8) == "chk_fld_") && (chkObj.checked))
			{
				if( ret != "" )
					ret += ",";

				ret += chkObj.name.substr(8,10);
			}
		}
		
		return ret;
	}

	function addFieldsCatalogacion()
	{
		document.marc.id_tipomaterial.value       = js_getElementByName_Value( "id_tipomaterial" );
		
		document.marc.id_tiporegistro.value       = js_getElementByName_Value( "cmbTipoRegistro" );
		document.marc.id_estadoregistro.value     = js_getElementByName_Value( "cmbEstadoRegistro" );
		document.marc.id_nivelbibliografico.value = js_getElementByName_Value( "cmbNivelBibliografico" );
		document.marc.id_nivelcodificacion.value  = js_getElementByName_Value( "cmbNivelCodificacion" );
		document.marc.id_formacatalogacion.value  = js_getElementByName_Value( "cmbFormaCatalogacion" );
		
		document.marc.field_008.value = Generate_Field008();				
		document.marc.marc_fields.value = Generate_FieldList();
		
		document.marc.newFields.value = detectMarkedFields();
		
		document.marc.action = "anls_catalogacion_paso2.php";
		
		updating_item_descriptors = true;

		document.marc.middle_action.value = "reload";
		document.marc.method = "POST";
		document.marc.target = "_self";
		document.marc.submit();
	}

	function ShowImportDialog()
	{
		var obj = js_getElementByName( "popUp" );
		ShowPopupDIV( "popUp" );
		
		obj.style.left = "1px";
		
		ShowPopupDIV( "popup_importar" );
	}

	function CloseImportDialog()
	{
		closePopupDIV( "popUp" );
		closePopupDIV( "popup_importar" );
		
	}
	
	function whereClick( e )
	{
		var targ;
		
		if( !e )
			var e=window.event;

		if (e.target)
		  {
		  targ=e.target;
		  }
		else if (e.srcElement)
		  {
		  targ=e.srcElement;
		  }
		if (targ.nodeType==3) // defeat Safari bug
		  {
		  targ = targ.parentNode;
		  }
		
		var tname;
		tname=targ.tagName;
		
		click_on_a_link = false;
		
		if( tname != "DIV" && tname != "INPUT" && tname != "TEXTAREA" )
		{
			click_on_a_link = true;
			//alert("DIV You clicked on a (surely <A>) " + tname + " element.");
		}
	}

	
	 function VerifyBeforeClose( evt )
	 {
	  
		if (!evt)
		  {
			var evt=window.event;
		  }
	  
		  if( modified && (!updating_item_descriptors && !saving_record))
		  {
				if ( !click_on_a_link )
				{
				  var message = "<?php echo $MSG_WARNING_BEFORE_CLOSING_WITHOUT_SAVE;?>";
		
				  if (evt) 
				  {
					evt.returnValue = message;
				  }

				  return message;
				}
		  }
	}
	
	function CloseWin()
	{
			window.close();
	}

</script>

<STYLE type="text/css">

	#popUp
	{
		display: none;
		position: absolute;
		background-color: gray;
		left: 1px;
		
		top: 4px;
		width: 100%;
		height: 200%;
		
		filter:alpha(opacity=35);
		-moz-opacity:0.35;
		opacity: 0.35;		
		
		border: 3px solid silver;
		border-bottom; 4px solid gray;
		
		z-Index: 48;
	}


  #datos_generales 
  {
	font-size: 90%;
  }
  
 #contenido_adicional {
   float: left; 
   clear: both;
   width: 770px;
  }
   
 #marc_editor 
 {
 }
  
 #caja_datos1
 {
	width: 950px;
 }
 
 #buttonBar
 {
	border-left: 2px gray dotted;
 }
  
 #popup_campos
 {
	position: absolute;
	visibility: hidden;
	display: none;
	top:100px; 
	left: 100px;

	border: 2px solid black;

	width: 700px;
	height: 500px;
	z-Index: 1;

	padding: 10px;
	background: white;
	color: black;
 }
 
 
 #popup_importar
 {
	position: absolute;
	display: none;

	top:100px; 
	left: 100px;
	
	border: 2px solid black;
	
	width: 680px;
	height: 250px;
	z-Index: 100;
	color: black;
	
	padding: 10px;
	
	text-align: left;
	
	background: white;
 }
 
 div #cabecera
 {  
    float: left; 
	width: 300px; 
 }

 div #generales_008
 {
	float: left; 
	width: 300px; 
 }
 
 div #especiales_008
 {
	float: left; 
	width: 400px; 
 }
 
 /* edición*/ 
 div.panelCampo
 {
	clear: both;
	position: relative;
	width: auto;
	overflow: auto;  /* auto ajuste de altura */
	border: 0px solid green;
	padding-top: 2px;
	padding-bottom: 2px;
 }

 div.espacioPanelIzquierdo /* area de id del campo e indicadores o espacio de izquierdo de los otros subcampos */
 {
	clear: both;
	float: left;
	width: 70px;
 } 
 
	 div.IDCAMPO
	 {
		float: left;
		width: 35px;
		font-weight: bold;
		font-size: 90%;
	 }
	   
	 INPUT.botonID
	 {
		width: 16px;
		height: 21px;
		font-family:Arial;
		font-size: 10px;
		position: relative;
	 }
 
 div.espacioPanelDerecho
 {
	float: right;
	width: 92%;
	font-size: 90%;
	padding-top: 2px;
	padding-bottom: 0px;
 } 

	 DIV.IDSUBCAMPO
	 {
		position: relative;
		float: left;
		width: 20px;
		font-weight: bold;
		font-size: 95%;
		padding-top: 1px;
	 }

	 DIV.DESCRIP_SUBCAMPO
	 {
		float: left;
		text-align: left;
		padding-left: 5px;
		margin-right: 2px;
		width: 250px;
		padding-top: 1px;
	 }

  TD.TDBorderLeft
  {
	border-left: 2px solid silver;
  }
	 
  DIV.contenedor_tabs
  {
	font-size: 90%;
  }
  
	TD.columna
	{
	   border-bottom: 1px dotted silver;
	}	  
	
	input.campo_captura 
	{
	   margin-bottom: 2px;
	}
	
	#status_div
	{
		display: inline;
		position: absolute;
		background: #FFD;
		left: 10px;
		height: 30px;
		width: auto;
		text-color: black;
		padding:5px;
		
		top: 4px;
		color: #000;
		text-align: left;
		
		border: 1px solid silver;
		
		z-Index: 50;
		
		display: none;
	}

TEXTAREA.texto_captura
{
   overflow: hidden; 
   display: inline; 
}
	 
</STYLE>

<body id="home" onClick="javascript:whereClick(event);" onbeforeunload="javascript:VerifyBeforeClose(event);">

<div id='popUp' name='popUp'></div>

	<!-- INICIA popup_importar -->
		<div class='groupbox' id='popup_importar' name='popup_importar' >
			<form enctype="multipart/form-data" action="anls_catalogacion_paso2.php" name="import_file">
				<h2><?php echo $LBL_IMPORT_HEADER;?></h2><br>

					  <div style='border:0px solid red;'>
						<span style='width:250px; text-align:right; border: 0px solid black;'>&nbsp;<?php echo $LBL_IMPORT_INDICATIONS;?>&nbsp;&nbsp;&nbsp;</span><br><br>
						
						<label for='userfile'><strong>Nombre del Archivo</strong></label><br><br>
						<input name="userfile" type="file" maxLength=80 size=80 value="">
						 
						&nbsp;
						<br><br>
						<input class="boton" type="button" value="<?php echo $BTN_IMPORT; ?>" name="btnBuscar" id="btnBuscar" onClick="javascript:importar_registro();">&nbsp;
						<input class="boton" type="button" value="<?php echo $BTN_CANCEL; ?>" name="btnClose" id="btnClose" onClick='javascript:CloseImportDialog();'><br>

					  </div>

				<br>		
				<input type="hidden" class="hidden" name="MAX_FILE_SIZE" value="500000">
				<input type="hidden" class="hidden" name="uploadfile" value="YES" >	
				<input type="hidden" class="hidden" name='id_plantilla' id='id_plantilla' value='<?php echo $id_plantilla;?>'>
			</form>
		
		</div> <!-- popup_importar -->
<br>

<div id='status_div' name='status_div'>&nbsp;</div>

<!-- contenedor principal -->
<div id="contenedor" style="width:95%">

<?php 

	require_once "marc.php";
   
	$marc_record = new record_MARC21( $id_biblioteca, $db, getsessionvar("language") );
	
	$cValor008 = "";
	
	if( $id_titulo != 0 )
	{
		$marc_record->InicializarRegistroMARC21_DesdeBD_Titulo( $id_titulo, $db );
		
		$id_serie = $marc_record->nIDSerie;
		
		$fecha_registro     = $marc_record->fecha_registro;
		
		$usuario_creo = $marc_record->usuario_registro;
		$usuario_creo = $marc_record->usuario_obtenerinfo_from_id( $usuario_creo );
		
		$usuario_modifico   = $marc_record->usuario_modifico;
		
		if( $usuario_modifico != 0 )
			$usuario_modifico = $marc_record->usuario_obtenerinfo_from_id( $usuario_modifico );
		$fecha_modificacion = $marc_record->fecha_modificacion;		
		
		$usuario_catalogo   = $marc_record->usuario_catalogo;
		$fecha_catalogacion = $marc_record->fecha_catalogacion;
		
		if( ($objCampo = $marc_record->BuscarCampo( "008" )) != NULL )
			$cValor008 = $objCampo->cValor;
	}
	
	//
	// SE INICIALIZA la PLANTILLA
	//
	if( $id_plantilla != 0 and $middle_action != "reload" ) // 07mar2012: Se agrego verificacion del middle_action
 	{
		$marc_record->InicializarRegistroMARC21_DesdePlantilla( $id_plantilla );
		
		if( ($objCampo = $marc_record->BuscarCampo( "008" )) != NULL )
			$cValor008 = $objCampo->cValor;
			
		$usuario_creo = $marc_record->usuario_obtenerinfo_from_id( $usuario_creo );
		
		//echo "[" . $cValor008;
		
		if( isset($nombre_serie) and $the_action == "create_new" )
		{
			if( ($the_field = $marc_record->BuscarCampo( "245" )) != NULL )
			{
				$subfield = $the_field->BuscarSubcampo( '$a' );
				
				if( $subfield != NULL )
					$subfield->cValor = $nombre_serie;
			}
		}
	}
	
	//
	// SIN INFO DE PLANTILLA Y SIN INFO DE TITULO
	//
	if( ($id_plantilla == 0 and $id_titulo == 0) and ($middle_action != "reload") )
	{
		$objCampo = $marc_record->AgregarCampo( "100", true, true, false );
		$objCampo = $marc_record->AgregarCampo( "050", true, true, false );
		$objCampo = $marc_record->AgregarCampo( "082", true, true, false );
		$objCampo = $marc_record->AgregarCampo( "245", true, true, false );
		$objCampo = $marc_record->AgregarCampo( "300", true, true, false );
		
		if( $id_serie != 0 )
			$objCampo = $marc_record->AgregarCampo( "410", true, true, false );
			
		$objCampo = $marc_record->AgregarCampo( "500", true, true, false );
		$objCampo = $marc_record->AgregarCampo( "650", true, true, false );
	}

	$str_campo008 = Array("");

	//
	// SI LA ACCION es agregar el id_titulo siempre sera "POR ASIGNAR..."
	//
	if( $the_action == "create_new" )
	{
		$id_titulo = $ID_TITLE_TO_BE_ASIGNED;
	}
	
	if( $contenido != "" )
	{
		SYNTAX_JavaScript( 1, 1, "modified=true;" );
		
		// se está importando de un archivo .MRC
		require_once "iso2709.inc.php";

		$ISO2709_record = new iso2709_record( $contenido, AUTO_UPDATE );
		
		//
		// NOTA IMPORTANTE:
		// El ID de los campos se calculará
		// con base en un FLUSH MD5 de todos los subcampos que contengan
		// concatenado el valor de cada subcampo, sin $CODIGO DE SUBCAMPO y SIN CARACTER P/ CONCATENAR
		
		// campos que están en la plantilla
		for( $i=0; $i<$marc_record->ContarCampos(); $i++ )
		{
			$objCampo = $marc_record->ObtenerCampoMARC($i);
			
			//echo "<br>PLANTILLA -- " . $objCampo->cIDCampo . "  &nbsp; *  ";
			
			if( $objCampo->cIDCampo != "$$$" )
			{							
				// eliminar subcampos para que puedan ser cargados en el orden que viene el registo MRC
				foreach($objCampo->subcampos as $value) 
				{ $value->destroy();}
				$objCampo->subcampos = Array();
				// fin elimina

				$objCampo->Cargar_SubCampos_ISO2709( $ISO2709_record, $objCampo->cIDCampo, 0 );   // ult. param 1 = elimina los subcampos en BLANCO				
			
				 //if( $objCampo->cIDCampo == "020" )
				 //{					
					//echo " DOS CAMPOS AL FINAL ". $objCampo->ObtenerSubCampo(0)->cIDSubCampo . " -- " ;
					//echo $objCampo->ObtenerSubCampo(1)->cIDSubCampo . " <br>";
				// }
			
				// El ID de se calcula del MD5 DE CONCATENAR LOS SUBCAMPOS
				$objCampo->ID = md5( $objCampo->ConcatenarValores("", false) );  // el elemento UNICO del ID será un valor MD5
				
				//echo " Valor $objCampo->cIDCampo: " . $objCampo->ConcatenarValores(" ", true) . "<br>"; 
			}
			
		}

		// barrer todos los campos
		for( $i=0; $i<$ISO2709_record->get_fields_count(); $i++ )
		{
			$agregar_campo = true;
			
			$ISO_id_campo = $ISO2709_record->field_name( $i );
			
			if( ($the_field = $marc_record->BuscarCampo( $ISO_id_campo )) != NULL )
			{
			   // crear un campo TEMPORAL EN MEMORIA para verificar que sea el mismo campo
			   $new_field = new TMARC21_Campo( $ISO_id_campo, NULL );
			   $new_field->marc_Parent = $marc_record; // habilitar el parent - 07-oct-2009
			   
			   $new_field->Cargar_SubCampos_ISO2709( $ISO2709_record, $i, 1 );			   
			   // El ID de se calcula del MD5 DE CONCATENAR LOS SUBCAMPOS
			   $new_field->ID = md5( $new_field->ConcatenarValores("", false) );
			   			   
				//echo "EN PLANTILLA -- " . $ISO_id_campo . $new_field->ConcatenarValores(" ", true) .  "<br>";
						   
			   // SI LOS VALORES NO COINCIDEN ES UN NUEVO CAMPO "DUPLICADO"
			   if( $new_field->ID != $the_field->ID )
			   {
				 //echo "<br>  * ANEXOS CAMPO DUPLICADO x -- " . $ISO_id_campo . " ";
				 //echo " (" . $new_field->ConcatenarValores("", true) . ")  [" . $the_field->ConcatenarValores("", true)  .  $the_field->ContarSubcampos() .  "]  ";
				 //echo "<br>";
				 
				 $agregar_campo = true;
			   }
			   else			   
				 $agregar_campo = false;
				 
			   unset( $new_field );
			}
			
			if( $agregar_campo )
			{
				$objCampo = $marc_record->AgregarCampo( $ISO_id_campo, true, true, true );

				if( $objCampo != NULL )
				{  
					$objCampo->Cargar_SubCampos_ISO2709( $ISO2709_record, $i, 1 ); 
					$objCampo->ID = md5( $objCampo->ConcatenarValores("", true) );  // el elemento UNICO del ID será un valor MD5
					
					//echo "AGREGADO -- " . $ISO_id_campo . $objCampo->ConcatenarValores(" ", true) .  "<br>";
				}
			}
		}
		
		// Determinar cuales campos serán borrados
		$aCamposXBorrar = Array();
		
		for( $i=0; $i<$marc_record->ContarCampos(); $i++ )
		{
			$objCampo = $marc_record->ObtenerCampoMARC($i);
			
			if( !$objCampo->bCampoControl )
				if( $objCampo->ContarSubcampos() == 0 )
				{
					$aCamposXBorrar[] = $objCampo;
				}
		}
		
		for( $i=0; $i<count($aCamposXBorrar); $i++ )
		{		
			$marc_record->EliminarCampo( $aCamposXBorrar[$i] );
		}
		
		$str_campo008 = $ISO2709_record->get_value_by_index( "008" );

		if( count($str_campo008) > 0 )
		{
			$marc_record->InicializarValoresCampo008( $str_campo008[0] );
			$cValor008 = $str_campo008[0];
		}

		$marc_record->SetTipoRegistro( $ISO2709_record->inner_guide["dt"] );
		$marc_record->SetEstadoRegistro( $ISO2709_record->inner_guide["rs"] );
		$marc_record->SetNivelBibliografico( $ISO2709_record->inner_guide["bl"] );
		$marc_record->SetNivelCodificacion( $ISO2709_record->inner_guide["el"] );
		$marc_record->SetFormaCatalogacion( $ISO2709_record->inner_guide["ru"] );
	}
	else
	{
		/* inicializar parametros que ya vienen */
		if( $id_tipomaterial != "" )
			$marc_record->SetTipoMaterial( $id_tipomaterial );		
		
		if( $id_tiporegistro != "" )
			$marc_record->SetTipoRegistro( $id_tiporegistro );

		if( $id_estadoregistro != "" )
			$marc_record->SetEstadoRegistro( $id_estadoregistro );

		if( $id_nivelbibliografico != "" )
			$marc_record->SetNivelBibliografico( $id_nivelbibliografico );

		if( $id_nivelcodificacion != "" )
			$marc_record->SetNivelCodificacion( $id_nivelcodificacion );

		if( $id_formacatalogacion != "" )
			$marc_record->SetFormaCatalogacion( $id_formacatalogacion );
	
		//
		// CUANDO SE AGREGAN CAMPOS
		//
		if( $middle_action == "reload" )  // $middle_action == "add_new_fields" 
		{
			SYNTAX_JavaScript( 1, 1, "modified=true;" );
			
			$marc_record->AgregarCampo( "$$$", false, true );
				
			$marc_record->nIDSerie = $id_serie;
			
			if( $the_action == "edit" )
			{ 
				$id_titulo = read_param( "id_titulo_editing", 0 );
				
				$usuario_creo = read_param( "id_usuario_creo", 0 );
				$usuario_modifico = read_param( "id_usuario_modifico", 0 );
				$usuario_catalogo = read_param( "id_usuario_catalogo", 0 );
				
				if( $usuario_creo != 0 )
				{
					$fecha_registro = read_param( "fecha_registro", "" );
					
					$marc_record->usuario_registro = $usuario_creo;
					$marc_record->fecha_registro = $fecha_registro;  // solo para despliegue
				}
							
				if( $usuario_modifico != 0 )
				{
					$marc_record->usuario_modifico = $usuario_modifico;
					$fecha_modificacion = read_param( "fecha_modificacion", "" );
				}
				
				if( $usuario_catalogo != 0 )
				{
					$marc_record->usuario_catalogo = $usuario_catalogo;
					$fecha_catalogacion = read_param( "fecha_catalogacion", "" );
				}
			}
			else if( $the_action == "create_new" )
			{
				$usuario_creo 	  = getsessionvar("id_usuario");
				$marc_record->usuario_registro = $usuario_creo;
			}
			
			if( $marc_record->usuario_registro ) 
				$usuario_creo = $marc_record->usuario_obtenerinfo_from_id( $marc_record->usuario_registro );
			
			if( $marc_record->usuario_modifico ) 
				$usuario_modifico = $marc_record->usuario_obtenerinfo_from_id( $marc_record->usuario_modifico );
			
			if( $marc_record->usuario_catalogo ) 
				$usuario_catalogo = $marc_record->usuario_obtenerinfo_from_id( $marc_record->usuario_catalogo );
			
			$cValor008 = read_param( "field_008", "" );
			
			// CAMPO 008
			$objCampo = $marc_record->AgregarCampo( "008", false, true );
			$objCampo->cValor = $cValor008;
			
			$marc_record->InicializarValoresCampo008( $cValor008 );
			
			$marc_fields = $_POST["marc_fields"];
			$marc_fields = split( ",", $marc_fields );
			
			// VERIFICAR nuevos campos
			if( isset( $_POST["newFields"] ) )
			{
				$newFields = $_POST["newFields"];
				
				if( $newFields != "" )
				{
					$new_fields = split( ",", $newFields );
					
					for( $ij=0; $ij<count($new_fields); $ij++ )
					{
						$marc_fields[] = "new_" . $new_fields[$ij] . "_" . (count($marc_fields)+1) . "_!";
					}				
				}
			}
			
			// VERIFICAR nuevos subcampos
			if( isset( $_POST["aux_params"] ) )
			{
				$str_subfields = $_POST["aux_params"] ;
				$str_subfields = str_replace( "&idsubcampo=", "", $str_subfields );
				
				$new_sub_fields = split( ":", $str_subfields );
			}
			else
				$new_sub_fields = Array( "" );
				
			//print_r( $new_sub_fields );

			$last_updating_field = "";

			foreach( $marc_fields as $element )
			{
				$type_element = substr( $element, 0, 3 );
				$extra_element = substr( $element, 4, 100 );

				$id_campo = substr( $extra_element, 0, 3 );
				$id_elemento_campo = trim(substr( $extra_element, 0, 50 ));

				if( $id_campo != $last_updating_field )
				{
					//
					// este bloque sirve para validar la llegada de un nuevo campo
					//    y se ha pensado especificamente para acomodar los nuevos SUBCAMPOS
					//    que el usuario elija ... y que queden al final
					//
					if( $objCampo != NULL )
					{
						if( $new_sub_fields[0] == $objCampo->cIDCampo )
						{
							//echo "CAMPO $id_campo !!!  ";
							
							for( $xyz = 1;   $xyz < count($new_sub_fields); $xyz++ )
							{
								$objCampo->AgregarSubCampo( $new_sub_fields[$xyz], "", "", "", "", "" );
							}
						}
					}

					$last_updating_field = $id_campo; 
				}

				$id1 = 0;
				$id2 = 0;

				if( $pos = strpos( $id_elemento_campo, "X" ) )
				{
					if( strpos( $id_elemento_campo, "X1" ) ) $id1 = 1;						
					if( strpos( $id_elemento_campo, "X2" ) ) $id2 = 1;					
					
					$id_elemento_campo = trim(substr( $id_elemento_campo, 0, $pos-1 ));
				}

				$valor = "";

				$cIDs = "";
				$cValID1 = "";
				$cValID2 = "";

				$id_subcampo = "";

				$objCampo = NULL;

				if( $type_element == "new" )  // nuevo campo - valor directo
				{
					if( $id_campo == "$$$" )
					{ 
						// se deja para el final PARA REALIZAR EL CALCULO DE DIRECTORIO Y TAMAÑO
						// probablemente no venga
					}
					else if( $id_campo == "008" )
					{
						// se deja para el final
						// probablemente no venga
					}
					else 
					{
						$valor = read_param( "txt_" . $id_elemento_campo, "" );  // verificar si trae valor directo

						if( $id1==1 or $id2==1)
						{
							// se trata de un campo que debería traer INDICADORES						
							// verificar si trae valor directo						
							if( ($cIDs = read_param( "XX1_" . $id_elemento_campo, "" )) != "" )
							{
								$array_of_ids = split( "&", $cIDs ); // cuantos indicadores vienen
								
								foreach( $array_of_ids as $id )
								{
									list($whatid, $whatval) = split( "=", $id );
									
									if( $whatid == "X1" )
										$cValID1 = $whatval;
									
									if( $whatid=="X2" )
										$cValID2 = $whatval;									
								}
								// foreach
							}
						}

						$objCampo = $marc_record->AgregarCampo( $id_campo, true, true );
						$objCampo->cValor = $valor;
						$objCampo->ID	  = $id_elemento_campo;

						if( !($pos = strpos( $extra_element, "!" )) )
							$objCampo->subcampos = Array();  // solo borrar los subcampos en aquellos campos
															 // que el usuario ya estaba editando
															 // ESTO evita que en los nuevos campos se elimine el primer SUBCAMPO
															 


						if( $cValID1 != "" and $objCampo->objID1 != NULL) $objCampo->AgregarIdentificador( 1, "", "", $cValID1 );						
						if( $cValID2 != "" and $objCampo->objID2 != NULL) $objCampo->AgregarIdentificador( 2, "", "", $cValID2 );
					}
					// end -if

				}
				else if( $type_element == "txt" )  // subcampo
				{
					$valor = trim($_POST[ "txt_$id_elemento_campo" ]);
					
					if( isset($_POST[ "txt_$id_elemento_campo" ]) ) // $valor != "" )
					{
						list($id_elemento_campo, $index_subcampo, $id_subcampo) = split( "@", $id_elemento_campo );
						
						$objCampo = $marc_record->BuscarCampoMARC_X_ID( $id_elemento_campo );
						
						if( $objCampo == NULL ) 	
						   die( "($id_elemento_campo) FIELD NOT FOUND" );
						else
						{
							$objSubCampo = $objCampo->AgregarSubCampo( $id_subcampo, "", "", "", "", "" );
							$objSubCampo->cValor = $valor;
							$objSubCampo->FixValue();
						}
					}
				}

			}
			// foreach

			// AGREGAR POSIBLES SUBCAMPOS
			//   DEL ULTIMO CAMPO EDITANDO
			// 
			if( $objCampo != NULL )
			{
				if( $new_sub_fields[0] == $objCampo->cIDCampo )
				{
					for( $xyz = 1;   $xyz < count($new_sub_fields); $xyz++ )
					{
						$objCampo->AgregarSubCampo( $new_sub_fields[$xyz], "", "", "", "", "" );
					}
				}
			}
			
		}
	}

	SYNTAX_JavaScript( 1, 1, " last_tiporegistro = '$marc_record->FTipoRegistro'; " );
	
	// Es necesario generar la cabecera
	// según los parámetros dados aquí
	// para seleccionar los elementos específicos del campo 008
	$marc_record->GenerarCabecera();
	
	$marc_record->CargarAutoridades( getsessionvar("id_red") );		
	
?>
 
	<!-- INICIA popup_campos -->
	<div class='groupbox' id='popup_campos' name='popup_campos'> 
		<div style='float:right;'><a href='javascript:closePopupDIV( "popup_campos" )'><img src='../images/icons/close_button.gif'></a></div>
		<h2>Agregar campos MARC para captura</h2>
		<div id="marc_hint" name="marc_hint">&nbsp;</div><br>
		
		<UL class=tabset_tabs style="left:3px; text-align: left;font-size:90%;" name='tabMARCFields' id='tabMARCFields' >
			<LI><a name="tab1_link" href='javascript:changeTab( "popup_campos", "tab1" );' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Codificación Básica");'>01x-04x</a></LI>
			<LI><a name="tab2_link" href='javascript:changeTab( "popup_campos","tab2" );' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Clasificacion");'>05x-08x</a></LI>
			<LI><a name="tab3_link" href='javascript:changeTab( "popup_campos","tab3" );' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Asiento Principal");'>100x</a></LI>
			<LI><a name="tab4_link" href='javascript:changeTab( "popup_campos","tab4" );' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Codificación Básica");'>20x-24x</a></LI>
			<LI><a name="tab5_link" href='javascript:changeTab( "popup_campos","tab5");' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Edición, Impresión, etc.");'>25x-27x</a></LI>
			<LI><a name="tab6_link" href='javascript:changeTab( "popup_campos","tab6");' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Descripción Física");'>3xx</a></LI>
			<LI><a name="tab7_link" href='javascript:changeTab( "popup_campos","tab7");' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Mención de Serie");'>4xx</a></LI>
			<LI><a name="tab8_link" href='javascript:changeTab( "popup_campos","tab8");' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Notas");'>5xx</a></LI>
			<LI><a name="tab9_link" href='javascript:changeTab( "popup_campos","tab9");' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Materias");'>6xx</a></LI>
			<LI><a name="tab10_link" href='javascript:changeTab( "popup_campos","tab10");' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Asientos Secundarios");'>7xx</a></LI>
			<LI><a name="tab11_link" href='javascript:changeTab( "popup_campos","tab11");' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Asientos Secundarios de Serie");'>8xx</a></LI>
		</UL>	
	
		<DIV id="tab1" name="tab1" class="contenedor_tabs" style='height: 370px'><?php insertMARCFields( $db, "010", "049"); ?></DIV>
		<DIV id="tab2" name="tab2" class="contenedor_tabs" style="display:none; height: 370px;"><?php insertMARCFields( $db, "050", "089"); ?></DIV>		
		<DIV id="tab3" name="tab3" class="contenedor_tabs" style="display:none; height: 370px;"><?php insertMARCFields( $db, "100", "199"); ?></DIV>		
		<DIV id="tab4" name="tab4" class="contenedor_tabs" style="display:none; height: 370px;"><?php insertMARCFields( $db, "200", "249"); ?></DIV>		
		<DIV id="tab5" name="tab5" class="contenedor_tabs" style="display:none; height: 370px;"><?php insertMARCFields( $db, "250", "279"); ?></DIV>
		<DIV id="tab6" name="tab6" class="contenedor_tabs" style="display:none; height: 370px;"><?php insertMARCFields( $db, "300", "399");?></DIV>
		<DIV id="tab7" name="tab7" class="contenedor_tabs" style="display:none; height: 370px;"><?php insertMARCFields( $db, "400", "499");?></DIV>
		<DIV id="tab8" name="tab8" class="contenedor_tabs" style="display:none; height: 370px;"><?php insertMARCFields( $db, "500", "599");?></DIV>
		<DIV id="tab9" name="tab9" class="contenedor_tabs" style="display:none; height: 370px;"><?php insertMARCFields( $db, "600", "699");?></DIV>
		<DIV id="tab10" name="tab10" class="contenedor_tabs" style="display:none; height: 370px;"><?php insertMARCFields( $db, "700", "799");?></DIV>
		<DIV id="tab11" name="tab11" class="contenedor_tabs" style="display:none; height: 370px;"><?php insertMARCFields( $db, "800", "899");?></DIV>		

		<div id=buttonArea style="position: absolute; top: 480px; left: 200px; ">
			<input type=button class=boton value="<?php echo $BTN_CONTINUE;?>" onClick="javascript:addFieldsCatalogacion();">
			<input type=button class=boton value="<?php echo $BTN_CLOSE_WINDOW;?>" onClick='javascript:closePopupDIV( "popup_campos" );'>			
		</div>

	</div>  
	<!-- FIN DE POPUP DE CAMPOS -->
	
	<script type="text/javascript" language="JavaScript">
		SetTabLinkActive( "tab1_link" );
		
		timer = setInterval("reloj()", 1000);
		
	</script>

<div id="bloque_principal"> <!-- inicia contenido -->
 
<!-- <P>
	<APPLET codebase="../CatalogApplet/build/classes" code="editor/MARC_Editor.class" width='100%' height='500'>
	</APPLET>
</P> -->

  <div id="datos_generales">
	<div class="resaltados" style='overflow:hidden'>

		<table width='100%'>
			<tr>
				<td width="40%">
					<strong><font size=+0>
					<?php 
					
						if( $id_plantilla != 0 )
							echo $LBL_TEMPLATE . ": " . $nombre_plantilla; 
						else
							echo $LBL_CATALOG_HEADER_2;
					 ?>
					</font></strong>
				</td>
				<td width="10%"><?php echo $LBL_NUMBER_OF_CONTROL;?></td>
				<td width="10%"><?php echo $id_titulo;?></td>
				<td width="10%"><?php echo $LBL_CREATED_BY;?></td>
				<td width="10%"><?php echo $usuario_creo;?></td>
				<td width="10%"><?php echo $fecha_registro;?></td>
			</tr>
			<tr>
				<td width="10%"></td>
				<td width="10%"></td>
				<td width="10%"></td>
				<td width="10%"><?php if( $usuario_modifico != "" and $usuario_modifico != "0") echo "Modificado por";?> </td>
				<td width="10%"><?php if( $usuario_modifico != "" and $usuario_modifico != "0") echo $usuario_modifico;?></td>
				<td width="10%"><?php echo $fecha_modificacion;?></td>
			</tr>			
		</table>
		
		<br>
		
		<!--- -->
		<!-- INICIA CABECERA -->
		<!--- -->
		<div id='cabecera' class='groupbox' style='min-height: 145px; width:320px;'>
			<h2><?php echo $LBL_HEADER;?></h2>
			
			<!-- OJO: NO COLOCAR STYLE DE POSITION -->
			<table border='0'>
				<tr>
					<td class='columna' width="90px" align='right'><?php echo $LBL_TYPE_OF_RECORD;?></td>
					<td class='columna TDBorderLeft' width="50px">
						<input name='cmbTipoRegistro' id='cmbTipoRegistro' type="button" value='<?php echo $marc_record->FTipoRegistro;?>' style='width:25px'>
						<?php
							colocar_popup( 1, $db, $LBL_TYPE_OF_RECORD, "$$$", "06", "cmbTipoRegistro" );
						 ?>
					</td>
					<td class='columna' width="100px" align='right'><?php echo $LBL_COD_LEVEL;?></td>
					<td class='columna TDBorderLeft' width="50px">
						<input name='cmbNivelCodificacion' id='cmbNivelCodificacion' type=button value='<?php echo $marc_record->FNivelCodificacion;?>' style='width:25px'>
						<?php
							colocar_popup( 4, $db, $LBL_COD_LEVEL, "$$$", "17", "cmbNivelCodificacion" );
						 ?>
					</td>
				</tr>
				
				<tr>
					<td class='columna' align='right'><?php echo $LBL_RECORD_STATUS;?></td>
					<td class='columna TDBorderLeft' >
						<input name='cmbEstadoRegistro' id='cmbEstadoRegistro' type=button value='<?php echo $marc_record->FEstadoRegistro;?>' style='width:25px'>
						<?php
							colocar_popup( 2, $db, $LBL_RECORD_STATUS, "$$$", "05", "cmbEstadoRegistro" );
						 ?>
					</td>
					<td class='columna' align='right'><?php echo $LBL_FORM_OF_CATALOG;?></td>
					<td class='columna TDBorderLeft' >
						<input name='cmbFormaCatalogacion' id='cmbFormaCatalogacion' type=button value='<?php echo $marc_record->FFormaCatalogacion;?>' style='width:25px'>
						<?php
							colocar_popup( 5, $db, $LBL_FORM_OF_CATALOG, "$$$", "18", "cmbFormaCatalogacion" );
						 ?>									
					</td>
				</tr>			

				<tr>
					<td class='columna'align='right'><?php echo $LBL_BIBL_LEVEL;?>&nbsp;</td>
					<td class='columna TDBorderLeft'>
						<input name="cmbNivelBibliografico" id="cmbNivelBibliografico" type="button" value='<?php echo $marc_record->FNivelBibliografico;?>' style='width:25px'>
						<?php
							colocar_popup( 3, $db, $LBL_BIBL_LEVEL, "$$$", "07", "cmbNivelBibliografico" );
						 ?>
					</td>
					<td>&nbsp;</td>					
					<td>&nbsp;</td>
				</tr>			
				
			</table>
			<br><br style='clear:both;'>
		</div>
		<!--- -->
		<!-- fin cabecera -->
		<!--- -->

		<!--- -->		
		<!-- INICIA datos generales 008 -->		
		<!--- -->		
		<div id='generales_008' class='groupbox'  style='min-height: 145px; width:320px;'>
			<h2>008 - <?php echo $LBL_008_GENERAL;?></h2>
			
			<!--- fecha en que se guarda el registro -->
			<input type='hidden' class='hidden' name='txt008_FechaRegistro' id='txt008_FechaRegistro' value="<?php echo strftime("%y%m%d"); ?>"><br>
			
			<table width='100%' border='0'>
				<tr>
					<td width="50%" class='columna' align='right'>[06] <?php echo $LBL_DATE_TYPE_STATUS;?></td>
					<td width="50%" class='columna TDBorderLeft'>
						&nbsp;<input name="cmb008_TipoFechaSt" id="cmb008_TipoFechaSt" type="button" value='<?php echo $marc_record->F008_TipoFechaEstadoPub;?>' style='width:25px'>

						<?php
							colocar_popup( 7, $db, $LBL_DATE_TYPE_STATUS, "008", "06", "cmb008_TipoFechaSt" );
						 ?>
						<input type='text' maxlength='4' name='txt008_Fecha1' id='txt008_Fecha1' size='4' value='<?php echo $marc_record->F008_Fecha_1;?>'>&nbsp;
						<input type='text' maxlength='4' name='txt008_Fecha2' id='txt008_Fecha2' size='4' value='<?php echo $marc_record->F008_Fecha_2;?>'>
					</td>
				</tr>
				
				<!-- TESAURO {MARC Code List for Countries} -->
				<tr>
					<td class='columna' align='right'>[15..17] <?php echo $LBL_PLACE_OF_PUBLISHING;?></td>
					<td class='columna TDBorderLeft'>
						&nbsp;<input name='txt008_LugarPublicacion' id='txt008_LugarPublicacion' type='text' maxlength='4' size='4' value='<?php echo $marc_record->F008_LugarPublicacion;?>'>

					<?php
						colocar_popup( 0, $db, $LBL_PLACE_OF_PUBLISHING, "{64}", "", "txt008_LugarPublicacion" );
					 ?>	

					</td>
				</tr>
				
				<!-- TESAURO {MARC Code List for Languages} -->
				<tr>
					<td class='columna' align='right'>[35..37] <?php echo $LBL_LANGUAGE;?></td>
					<td class='columna TDBorderLeft'>
						&nbsp;<input name='txt008_Idioma' id='txt008_Idioma' type='text'  maxlength='4' size='4' value='<?php echo $marc_record->F008_Idioma;?>'>
						<?php
							colocar_popup( 0, $db, $LBL_LANGUAGE, "{62}", "", "txt008_Idioma" );
						 ?>						
					</td>

				</tr>

				<tr>
					<td class='columna' align='right'>[38] <?php echo $LBL_RECORD_MODIFIED;?></td>
					<td class='columna TDBorderLeft'>
						&nbsp;<input name='cmb008_RegistroModificado' id='cmb008_RegistroModificado' type="button" value='<?php echo $marc_record->F008_RegistroModificado;?>' style='width:25px'>

						<?php
							colocar_popup( 10, $db, $LBL_RECORD_MODIFIED, "008", "38", "cmb008_RegistroModificado" );
						 ?>
					</td>
				</tr>

				<tr>
					<td class='columna' align='right'>[39] <?php echo $LBL_SOURCE_OF_CATALOG;?></td>
					<td class='columna TDBorderLeft'>
						&nbsp;<input name='cmb008_FuenteCatalogacion' id='cmb008_FuenteCatalogacion' type="button" value='<?php echo $marc_record->F008_FuenteCatalogacion;?>' style='width:25px'>

						<?php
							colocar_popup( 11, $db, $LBL_SOURCE_OF_CATALOG, "008", "39", "cmb008_FuenteCatalogacion" );
						 ?>
					
					</td>
				</tr>
				
			</table>				
		</div>
		<!--- -->			
		<!-- fin datos generales -->
		<!--- -->	

		<!--- -->			
		<!-- INICIA datos especiales 008 -->
		<!--- -->			
		<div id='especiales_008' class='groupbox' >
			<h2> 008 - <?php echo $LBL_008_SPECIFICS;?> </h2>
			<br>
			<?php 
				$array_elements = inicializar_008_Specials( $db, $marc_record, $cValor008 ); 
				
				SYNTAX_BEGIN_JavaScript();
				
				// COLOCAR LOS ELEMENTOS QUE SE MANDARAN EN EL POST
				foreach( $array_elements as $element )
				{ echo "  aField008_elements.push('" . $element[0] . "');\n"; }

				SYNTAX_CLOSE_JavaScript();

			?>
		</div>
		<!--- -->			
		<!-- FIN datos especiales -->
		<!--- -->			

		<br style='clear:both'>

	</div>

  </div> <!-- contenido_adicional -->  

 <br>
 
 <!-- FORMA/DIV DE EDICION -->
 
 <form id='marc' name='marc' method="POST" class='forma_captura'>
  
  <input type='hidden' class='hidden' name='id_plantilla' id='id_plantilla' value='<?php echo $id_plantilla;?>'>
  <input type='hidden' class='hidden' name='the_action' id='the_action' value="<?php echo $the_action;?>"> 
  <input type='hidden' class='hidden' name='middle_action' id='middle_action' value=""> 
  <input type='hidden' class='hidden' name='id_serie' id='id_serie' value="<?php echo $id_serie;?>"> 
  
  <?php

	if( $the_action == "edit" )
	{
		echo "<!-- DATOS de APOYO para EDICION -->\n";
		echo "<input type='hidden' class='hidden' name='id_titulo_editing' id='id_titulo_editing' value='$id_titulo'>\n";
		
		echo "<input type='hidden' class='hidden' name='id_usuario_creo' id='id_usuario_creo' value='" . $marc_record->usuario_registro . "'>\n";
		echo "<input type='hidden' class='hidden' name='fecha_registro' id='fecha_registro' value='" . $marc_record->fecha_registro . "'>\n";
		
		echo "<input type='hidden' class='hidden' name='id_usuario_modifico' id='id_usuario_modifico' value='" . $marc_record->usuario_modifico . "'>\n";		
		echo "<input type='hidden' class='hidden' name='fecha_modificacion' id='fecha_modificacion' value='" . $marc_record->fecha_modificacion . "'>\n";
		
		echo "<input type='hidden' class='hidden' name='id_usuario_catalogo' id='id_usuario_catalogo' value='" . $marc_record->usuario_catalogo . "'>\n";
	}
	
   ?>
  
  <!-- PARA AGREGAR CAMPOS -->
  <input type="hidden" class="hidden" value="" name='newFields' id='newFields'>
  
  <!-- PARA CONTROLAR LA PROPIEDAD TIPO MATERIAL de acervo_titulos -->
  <input type='hidden' class='hidden' name='id_tipomaterial' id='id_tipomaterial' value="<?php echo $marc_record->cTipoMaterial;?>"> 
  
  <!-- PARA AGREGAR SUBCAMPOS -->
  <input type='hidden' class='hidden' name='aux_params' id='aux_params' value="">
 
 <!-- CABECERA -->
  
  <input type='hidden' class='hidden' name='id_tiporegistro' id='id_tiporegistro' value=""> 
  <input type='hidden' class='hidden' name='id_estadoregistro' id='id_estadoregistro' value=""> 
  <input type='hidden' class='hidden' name='id_nivelbibliografico' id='id_nivelbibliografico' value=""> 
  <input type='hidden' class='hidden' name='id_nivelcodificacion' id='id_nivelcodificacion' value=""> 
  <input type='hidden' class='hidden' name='id_formacatalogacion' id='id_formacatalogacion' value="">   
  
 <!-- CAMPO 008 -->
  <input type='hidden' class='hidden' name='field_008' id='field_008' value="">    
 
 <!-- INICIA MARC EDITOR -->
 <div id="marc_editor">

	<div style="float: right;" id="buttonBar" name="buttonBar">
	  <input class='boton' type='button' value='<?php echo $BTN_ADD_FIELD;?>' onClick='javascript:ShowPopupDIV("popup_campos");' style='width:170px'><br>
	  <input class='boton' type='button' value='<?php echo $BTN_DELETE_FIELD;?>' onClick='javascript:DeleteElement();' style='width:170px'><br><br>
	  <input class='boton' type='button' value='<?php echo $BTN_SAVE_CHANGES;?>' onClick='javascript:SaveChanges("<?php echo $marc_record->bObligatorio_MARC100==true ? "S" : "N";?>");' style='height:35px; width: 170px;'><br><br>
	  <input class='boton' type='button' value='<?php echo $BTN_IMPORT_FROM_MARC;?>' onClick='javascript:ShowImportDialog();' style='width:170px'> 
	  <br><br>
	  <input class='boton' type='button' value='<?php echo $BTN_CLOSE_WINDOW;?>' onClick='javascript:CloseWin();' style='height:35px; width:170px'>  
	</div>

	<div class='caja_datos' id='caja_datos1' style='float: left;'>

		<?php	
			SYNTAX_JavaScript( 1, 1, "aMARCFields.push('new_$$$');" );  // LEADER
			
			$marc_fields = "";

			$total_campos = $marc_record->ContarCampos();
			
			for( $i=0; $i<$total_campos; $i++ )
			{
				$objCampo = $marc_record->ObtenerCampoMARC($i);
				
				if( $objCampo->cIDCampo == "$$$" or $objCampo->cIDCampo == "###" or 
				    $objCampo->cIDCampo == "001" or
					$objCampo->cIDCampo == "005" or
				    $objCampo->cIDCampo == "008" )
				   continue;
				   
				$bNoSubfields = false;
				
				echo "\n\n<!-- BEGIN " .  $objCampo->cIDCampo . "-->\n\n";
				   
				// inicia DIV campo
				if( $i==$total_campos-1 )
					echo "<DIV id='div_$objCampo->cIDCampo' class='panelCampo' onMouseOver='javascript:Hilite(this)' onMouseOut='javascript:UnHilite(this)'>";
				else
					echo "<DIV id='div_$objCampo->cIDCampo' class='panelCampo' style='display: block; border-bottom: 1px solid gray;' onMouseOver='javascript:Hilite(this)' onMouseOut='javascript:UnHilite(this)'>";

				if( $objCampo->bCampoControl )
				{
					// CAMPOS DE CONTROL
					echo "<DIV class='espacioPanelIzquierdo'>";
					echo " <DIV class='IDCAMPO'>" . $objCampo->cIDCampo . " </DIV>";
					echo "</DIV>";
					
					$controlname = "txt_" . $objCampo->cIDCampo . "_$i";
					
					$descrip_campo = $objCampo->ObtenerDescripcion();
					
					echo "<DIV class='espacioPanelDerecho'>";
					
						echo "<DIV style='overflow: auto; padding-top: 1px; padding-bottom: 1px; '>"; 
					
						// descripción en BOLD
						echo "<LABEL for='$controlname'><div class='DESCRIP_SUBCAMPO'><strong>$descrip_campo</strong></div></LABEL>";
						echo "<INPUT name='$controlname' id='$controlname' type='text' class='campo_captura' onFocus='javacript:set_focus_div(this,\"$objCampo->cIDCampo $descrip_campo\");' value='" . $objCampo->cValor. "' size='50'>";
						
						echo "</DIV>";
						
					echo "</DIV>";
	
					SYNTAX_JavaScript( 1, 1, "aMARCFields.push('new_$objCampo->cIDCampo" . "_$i');" );
				}
				else
				{									
					// CAMPOS "NORMALES" QUE PUEDEN INCLUIR AUXILIARES Y
					// UNO O MAS SUBCAMPOS
					echo "<DIV class='espacioPanelIzquierdo'>";
					echo "<DIV class='IDCAMPO' title='" . $objCampo->cIDCampo . " " . $objCampo->ObtenerDescripcion() . "'>" . $objCampo->cIDCampo;
					
					if( $objCampo->notFound ) 
						echo "<img src='../images/icons/warning.gif'>";
					
					//if( $objCampo->cUrl != "" )
						echo "<br><a href='javascript:see_field_info(\"$objCampo->cUrl\");' title='See on-line reference $objCampo->cUrl'><img src='../images/icons/icon_url.png'></a>";

					echo "<a href='javascript:add_subfields(\"$objCampo->cIDCampo\");' title='Add Subfields'><img src='../images/icons/new_field_btn.gif'></a>";

					echo "</DIV>\n";
					
					$cID1 = "";
					$cID2 = "";
					
					// Espacio del primer identificador

					if( $objCampo->objID1 != NULL and $objCampo->notFound==false )
					{ 
						$controlname = "id1_" . $objCampo->cIDCampo . "_$i";
						$cID1 = "X1";

						if( $objCampo->objID1->cValor == '' )
							$objCampo->objID1->cValor = "#";						
						
						echo "<INPUT name='$controlname' id='$controlname' class='botonID' type='button' value='" . $objCampo->objID1->cValor . "' onClick=\"javascript:showDialogIndicadores(this,'$objCampo->cIDCampo', '$controlname', 1);\">";
					}

					// Espacio del segundo identificador
					if( $objCampo->objID2 != NULL and $objCampo->notFound==false )
					{ 
						$controlname = "id2_" . $objCampo->cIDCampo . "_$i";
						$cID2 = "X2";

						if( $objCampo->objID2->cValor == '' )
							$objCampo->objID2->cValor = "#";

						echo "<INPUT name='$controlname' id='$controlname' class='botonID' type='button' value='" . $objCampo->objID2->cValor . "' onClick=\"javascript:showDialogIndicadores(this,'$objCampo->cIDCampo', '$controlname', 2);\">";
					}

					if( $objCampo->notFound )
					{
						echo "<img src='../images/icons/warning.gif'>Field Error";
					}
					
					echo "</DIV>"; // espacioPanelIzquierdo
					
					SYNTAX_JavaScript( 1, 1, "aMARCFields.push( 'new_" . $objCampo->cIDCampo . "_$i" . " $cID1 $cID2' );" );
					
					//echo "new_" . $objCampo->cIDCampo . "_$i" . " $cID1 $cID2";
					
					// AGREGAR un contenedor para los X1 y X2 del campo
					if( $objCampo->objID1 != NULL or $objCampo->objID2 != NULL )
					{
						$controlname = "XX1_" . $objCampo->cIDCampo . "_$i";
						echo "<INPUT name='$controlname' id='$controlname' type='hidden' class='hidden'>";
					}

					$subcampos = $objCampo->ContarSubcampos();
					
					if( $subcampos > 0 )
					{	
						echo "\n<DIV class='espacioPanelDerecho'>";
						
						for( $j=0; $j<$subcampos; $j++ )
						{
							$objSubCampo =  $objCampo->ObtenerSubCampo($j);
							$objSubCampo->ObtenerID( $i );

							$controlname = "txt_" . $objSubCampo->cID;
							
							// subfield ID
							echo "\n\n<DIV style='float:left; padding-top: 1px; padding-bottom: 1px; " . ($j<$subcampos-1 ? "border-bottom: 1px dotted gray; '" : "" ) . "'>"; 
							
							// OPEN SPAN + LABEL
							echo "<DIV class='IDSUBCAMPO'>$objSubCampo->cIDSubCampo</DIV>" .
								 "<div class='DESCRIP_SUBCAMPO'>";

							if( $objSubCampo->notFound ) 
								echo "&nbsp;<div class='caja_errores' style='display:inline;'><img src='../images/icons/warning.gif'>&nbsp;$HINT_SUBFIELD_NOTFOUND</div>";
								 
							echo $objSubCampo->ObtenerDescripcion();
								 
							echo "</div>";	// CLOSE DIV

							//
							// NOTA:  Buscar si hay un registro definido de AUTORIDADES (por ID_RED)
							//        o LA AUTORIDAD MARC21 por DEFAULT (tipo {MARC registro de PAISES}
							//
							$autoridad = $marc_record->BuscarAutoridadXSubCampo( $objSubCampo, getsessionvar("id_red") );
						
							$size_txt = ($autoridad!=0) ? 50 : 75;

							$size_rows = 15;

							if( strlen( $objSubCampo->cValor ) > 70 ) 
							{
								$size_rows = (integer) strlen( $objSubCampo->cValor ) / 70;
								
								if( (strlen( $objSubCampo->cValor ) % 70 ) > 0 )
									$size_rows++;
																
								$size_rows = (integer) $size_rows * 15;
							}

							$descrip_subcampo = $objCampo->cIDCampo . " " . $objCampo->ObtenerDescripcion() . " <br>" . $objSubCampo->cIDSubCampo . " " . $objSubCampo->ObtenerDescripcion();

							echo "<TEXTAREA name='$controlname' id='$controlname' class='texto_captura' onFocus='javacript:set_focus_div(this,\"$descrip_subcampo\");' cols='$size_txt' style='height:" . $size_rows . "px;' onkeypress='javascript:validaInfoEntered(this);'>";
							echo $objSubCampo->cValor;
							echo "</TEXTAREA>";

							if( $objSubCampo->notFound ) 
								echo "&nbsp;<div class='caja_errores' style='display:inline;'><img src='../images/icons/warning.gif'>&nbsp;$HINT_SUBFIELD_NOTFOUND</div>";
							
							if( $autoridad!=0 )
							{  
								if( $autoridad["tipo"] == "codigo" )
									colocar_popup( 0, $db, $objSubCampo->ObtenerDescripcion(), "{" . $autoridad["id_categoria"] . "}", "", $controlname );
								else if( $autoridad["tipo"] == "descripcion" )
									colocar_popup( 0, $db, $objSubCampo->ObtenerDescripcion(), "<" . $autoridad["id_categoria"] . ">", "", $controlname );
							}
							
							echo "</DIV>";  // remove <br>
							
							// Agregar subcampo a array de MARC fields - subfields
							SYNTAX_JavaScript( 1, 1, "aMARCFields.push('$controlname');");
						}
						
						echo "</DIV>\n";  // espacio panel derecho
					}
					
				}
				
				echo "</DIV> <!-- END " . $objCampo->cIDCampo . " -->\n\n"; 			
				// termina DIV campo
			}
			
			$marc_record->destroy();
					
		 ?>			 
			  
	</div>  <!-- caja_datos --> 
	
 </div> 
 
 <!-- SE LLENA POR CODIGO -->
 <input type="hidden" class="hidden" name="marc_fields" id="marc_fields" value="">
 
 <!--- TERMINA MARC EDITOR -->
 </form>

</div>
<!-- end div bloque_principal -->

<?php  

	display_copyright(); 

	$db->destroy();
   
?>

</div><!-- end div contenedor -->

</body>	

</html>