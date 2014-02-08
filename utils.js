//
// Funciones Varias (BiblioTEK)
// @Autor: JELS
// 

function Array_Search( aArray, value )
{
	for( var i=0; i<aArray.length; i++ )
	{
		if( aArray[i] == value )
			return i;
	}
	
	return -1;
	
}

//
// Esta función permite redirigir el contenido de una ventana
// generalmente a usarse en lugar de SUBMIT pasando _GETs
function js_ChangeLocation( link )
{
	document.location.href = link;
}

//
// El objeto debe ser referenciado por document.form.objName 
// para que devuelva un grupo
//
function js_getRadioValue( oRadio )
{
	var i;
	var res = "";
	
	for (i=0;i<oRadio.length; i++ )
	{
		if ( oRadio[i].checked )
			res = oRadio[i].value;
		
	}

	return( res );
}

function js_Status( cMess )
{
	window.status = cMess;
	return true;
}


var cur_div=0;

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

function download_digital_file( type, id_titulo, id_filenum )
{
	var url = "download.php?type=catalogacion&id_titulo=" + id_titulo + "&id_filenum=" + id_filenum;
	var nwidth = screen.width;
	var nheight = screen.height; 
		
	window.open( url, "download", "WIDTH=" + (nwidth-250) + ",HEIGHT=" + (nheight-320) + ",TOP=100,LEFT=100,resizable=yes,scrollbars=yes,status=yes" );	
}

function remove_digital_file( msg, type, id_titulo, id_filenum, caller_page )
{
	
	if( confirm(msg))
	{
		var url = "download.php?type=catalogacion&id_titulo=" + id_titulo + "&id_filenum=" + id_filenum + "&sp_action=remove";
		
		if( caller_page )
			url = url + "&caller=" + caller_page; 
		
		var nwidth = screen.width;
		var nheight = screen.height; 
	
		document.location.href = url;
	}
	
}

function HideDiv( div_name )
{
	var divx = document.getElementsByName( div_name );
	
	if( divx.length > 0 )
	{
		// asegurar que el DIV se oculte
		divx[0].style.display = "none";
		divx[0].style.visibility = "hidden";
		
		return true;
	}
	else
		return false;
	
}

function ShowDiv( div_name )
{
	var divx = document.getElementsByName( div_name );
	
	if( divx.length > 0 )
	{
		// asegurar que el DIV se vea
		if( divx[0].style.display == "none" || divx[0].style.display == "" )
		{			
			divx[0].style.display = "inline";
			divx[0].style.visibility = "visible";
		}
		
		if( div_name == "popUpBlock" ) 
		{
			divx[0].style.left = "1px";
			divx[0].style.height = document.body.clientHeight + "px";
			divx[0].style.width = document.body.clientWidth + "px";
		}		
		
		return true;
	}
	else
		return false;
	
}

function ScrollTop_ViewPort()
{
	var scrolledY = 0;
	
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
	 return scrolledY;
}

function ShowPopupDIV( name )
{
	var divID = js_getElementByName( name );
	
	if( divID )
	{
		/*divID.style.display = "inline";
		divID.style.visibility = "visible";
		
		var width_ox = divID.offsetWidth;
		
		divID.style.left = (((screen.width - width_ox) / 2) - 50) + "px";
		
		if( self.pageYOffset ) 
		{
		    var scrolledY = self.pageYOffset;
		} 
		else if( document.documentElement && document.documentElement.scrollTop ) 
		 {
		    var scrolledY = document.documentElement.scrollTop;
		 } 
		else if( document.body ) 
		 {
		    var scrolledY = document.body.scrollTop;
		 }
		
		var pos_Y = ((screen.height - divID.offsetHeight) / 2) - 150;
		
		divID.style.top = (scrolledY + pos_Y) + "px";
		divID.style.zIndex = 2000; // para que quede arriba de otros
		
		return true;*/
		
		var the_width = window.innerWidth ? window.innerWidth : screen.width;
		var the_height = window.innerHeight ? window.innerHeight : screen.height;
		
		divID.style.display = "inline";
		divID.style.visibility = "visible";
		
		var calc_left = ( (the_width / 2) - (divID.offsetWidth / 2) );
			
		divID.style.left = calc_left + "px";
		
		if( calc_left < 0 ) divID.style.left = "20px";

		var pos_Y = ((the_height / 2) - (divID.offsetHeight/2));
		
		if( ScrollTop_ViewPort() + pos_Y < 0 ) 
			divID.style.top = "10px";
		else
			divID.style.top = (ScrollTop_ViewPort() + pos_Y) + "px";		
		
	}
	else
		return false;
}


function ResizeFullHeight( div_name )
{
	var divx = document.getElementsByName( div_name );
	
	if( divx.length > 0 )
	{
		// asegurar que el DIV se vea
		if( document.body.clientHeight )
		{
			var full_height = document.body.clientHeight;		
			divx[0].style.top =  "1px";
			divx[0].style.height =  full_height + "px";
		}
		
		return true;
	}
	else
		return false;

}

function js_getElementByName( elementName )
{
	var the_element = document.getElementsByName( elementName );
	
	if( the_element.length > 0 )
	{
		return the_element[0];
	}	
	else
		return 0;
}


function js_getElementByName_Value( elementName )
{
	var val = document.getElementsByName( elementName );
	
	if( val.length > 0 )
	{
		if( val[0].type == "checkbox" )
			return val[0].checked;
		else if( val[0].type == "radio" )
		{
			// iteraccion in the group
			var res = "";
			
			for (i=0;i<val.length; i++ )
			{
				if ( val[i].checked )
					res = val[i].value;
			}			
			
			return res;
		}
		else if( val[0].type == "select-one" )
		{
			var res = "";
			
			for( i = 0; i < val[0].options.length; i++ )
			{
				if ( val[0].options[i].selected )
				{
					res = val[0].options[i].value;
					break;
				}
			}			
			
			return res;		
		}
		else		
			return val[0].value;
		
	}
	else
		return "";
}

//
// 18 nov 2009 
// Probar
//
function js_setElementByName_InitValue( elementName, valueToSet )
{
	var val = document.getElementsByName( elementName );
	
	if( val.length > 0 )
	{
		if( val[0].type == "checkbox" )
		{
			if( valueToSet == 1 )
				val[0].checked = true;
			else if( valueToSet == "Y" )
				val[0].checked = true;				
			else if( valueToSet == "S" )
				val[0].checked = true;
			else
				val[0].checked = false;
		}
		else if( val[0].type == "radio" )
		{
			// iteraccion in the group
			var found = 0;
			
			for (i=0;i<val.length; i++ )
			{
				if ( val[i].value == valueToSet )
				{
					val[i].checked = true;
					found = 1;
				}
			}			
		}
		else if( val[0].type == "select-one" )
		{
			var found = 0;
			
			for( i = 0; i < val[0].options.length; i++ )
			{
				if ( val[0].options[i].value == valueToSet )
				{
					val[0].options[i].selected = true;
					found = 1;
				}
			}			
			
			if( found == 0 )
			{
				if( val[0].options.length > 1 )
					val[0].options[0].selected = true;
			}
		}
		else		
			val[0].value = valueToSet;
		
	}
}


//
// 23 ago 2009
// Process an Action either on a Dialog or by redirecting
//
//  nType:   1 - Dialog,    2 - Redirecting
//   All parameters are mandatory
//
function js_ProcessActionURL( nType, url, dialog_name, dialog_width, dialog_height )
{
	if( nType == 1 )
	{
		var nTop = (screen.height - (dialog_height)) / 2;
		var nLeft = (screen.width - (dialog_width)) / 2;
		
		nTop = nTop - 20;
		nLeft = nLeft - 15;
		
		if( navigator.appName == "Microsoft Internet Explorer" )
		{
			var ret = window.open( url, dialog_name, 'width=' + dialog_width + 'px,height=' + dialog_height + 'px,top=' + nTop + ',left=' + nLeft + "resizable=no,scrollbars=yes,status=yes" );
		}
		else
		{		
			var ret = showModalDialog( url, dialog_name, "dialogTop:"+nTop+"px;dialogLeft:"+nLeft+"px;dialogWidth:"+dialog_width+"px;dialogHeight:"+dialog_height+"px;center:yes;status:no;scrollbars:yes;" );
			
			if( ret != null )
			{
				 document.location.reload();
			}
			
			return ret;
		}
		
	}
	else
	{
		js_ChangeLocation( url );
		
		return true;
	}

}

function isValidURL(url)
{
    var RegExp = /^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/;
    if(RegExp.test(url)){
        return true;
    }else{
        return false;
    }
}

function isValidEmail(email)
{
    if( email == "" ) return true;
	else
	{
		var RegExp = /^((([a-z]|[0-9]|!|#|$|%|&|'|\*|\+|\-|\/|=|\?|\^|_|`|\{|\||\}|~)+(\.([a-z]|[0-9]|!|#|$|%|&|'|\*|\+|\-|\/|=|\?|\^|_|`|\{|\||\}|~)+)*)@((((([a-z]|[0-9])([a-z]|[0-9]|\-){0,61}([a-z]|[0-9])\.))*([a-z]|[0-9])([a-z]|[0-9]|\-){0,61}([a-z]|[0-9])\.)[\w]{2,4}|(((([0-9]){1,3}\.){3}([0-9]){1,3}))|(\[((([0-9]){1,3}\.){3}([0-9]){1,3})\])))$/
		if(RegExp.test(email)){
			return true;
		}else{
			return false;
		}
	}
} 

// version: beta
// created: 2005-08-30
// updated: 2005-08-31
// mredkj.com
//
// http://www.mredkj.com/tutorials/validate2.html
//
function extractNumber(obj, decimalPlaces, allowNegative)
{
	var temp = obj.value;
	
	// avoid changing things if already formatted correctly
	var reg0Str = '[0-9]*';
	if (decimalPlaces > 0) {
		reg0Str += '\\.?[0-9]{0,' + decimalPlaces + '}';
	} else if (decimalPlaces < 0) {
		reg0Str += '\\.?[0-9]*';
	}
	reg0Str = allowNegative ? '^-?' + reg0Str : '^' + reg0Str;
	reg0Str = reg0Str + '$';
	var reg0 = new RegExp(reg0Str);
	if (reg0.test(temp)) return true;

	// first replace all non numbers
	var reg1Str = '[^0-9' + (decimalPlaces != 0 ? '.' : '') + (allowNegative ? '-' : '') + ']';
	var reg1 = new RegExp(reg1Str, 'g');
	temp = temp.replace(reg1, '');

	if (allowNegative) {
		// replace extra negative
		var hasNegative = temp.length > 0 && temp.charAt(0) == '-';
		var reg2 = /-/g;
		temp = temp.replace(reg2, '');
		if (hasNegative) temp = '-' + temp;
	}
	
	if (decimalPlaces != 0) {
		var reg3 = /\./g;
		var reg3Array = reg3.exec(temp);
		if (reg3Array != null) {
			// keep only first occurrence of .
			//  and the number of places specified by decimalPlaces or the entire string if decimalPlaces < 0
			var reg3Right = temp.substring(reg3Array.index + reg3Array[0].length);
			reg3Right = reg3Right.replace(reg3, '');
			reg3Right = decimalPlaces > 0 ? reg3Right.substring(0, decimalPlaces) : reg3Right;
			temp = temp.substring(0,reg3Array.index) + '.' + reg3Right;
		}
	}
	
	obj.value = temp;
}

// http://www.mredkj.com/tutorials/validate2.html

function blockNonNumbers(obj, e, allowDecimal, allowNegative)
{
	var key;
	var isCtrl = false;
	var keychar;
	var reg;
		
	if(window.event) {
		key = e.keyCode;
		isCtrl = window.event.ctrlKey
	}
	else if(e.which) {
		key = e.which;
		isCtrl = e.ctrlKey;
	}
	
	if (isNaN(key)) return true;
	
	keychar = String.fromCharCode(key);
	
	// check for backspace or delete, or if Ctrl was pressed
	if (key == 8 || isCtrl)
	{
		return true;
	}

	reg = /\d/;
	var isFirstN = allowNegative ? keychar == '-' && obj.value.indexOf('-') == -1 : false;
	var isFirstD = allowDecimal ? keychar == '.' && obj.value.indexOf('.') == -1 : false;
	
	return isFirstN || isFirstD || reg.test(keychar);
}

//
// Ejecuta el método buscar 
// para las opciones de consulta a catálogo
//
	function search_catalog( id_lib, id_consulta, path_target, extra_params )
	{
		var error = 0;		
		var obj_searchBy = js_getElementByName( "search_By" );
		var obj_searchFor = js_getElementByName( "search_For" );
			
		if( obj_searchBy.value == "" )
		{
			error = 1;
			alert( octal("Indique alguna forma de b&uacute;squeda / Select a search by method") );
		}	
		
		if( error == 0 )
		{
			if( window.search_ajax_catalog ) 
			{
				search_ajax_catalog( id_lib, id_consulta, path_target, extra_params );
				return false;
			}
			else
			{
			
				if( obj_searchBy.value == "BY_ID" )
				{
					// 
					if( isNaN(obj_searchFor.value) )
					{
						alert( octal("Para hacer esta b&uacute;squeda debes especificar un valor num&eacute;rico. No se admiten caracteres.") );
						
						obj_searchFor.focus();
						error = 1;
						return false;
					}
				}			
			
				if( obj_searchFor.value == "" )		
				{
					error = 1;
					alert( octal("Teclee sus palabras de b&uacute;squeda") );
					
					obj_searchFor.focus();
					
					return false;
				}
			
				js_ChangeLocation( path_target + "?id_biblioteca=" + id_lib +"&id_consulta=" + id_consulta + "&action=go&type=" + obj_searchBy.value + "&search=" + obj_searchFor.value + "&" + extra_params );		
				return true;
			}
		}
		else
			return false;
	}

		// Sirve para hacer un submit cuando se oprime la tecla enter
		function submit_search( myfield, e, id_lib, id_consulta, path_target, extra_params )
		{
			var keycode;
		
			if (window.event) keycode = window.event.keyCode;
			else if (e) keycode = e.which;
			else return true;
		
			if (keycode == 13)
			{
				var retval = search_catalog( id_lib, id_consulta, path_target, extra_params );
				
				return retval;	
			}
			else
				return true;
		}
		
		// búsquedas rápidas
		function quick_search( term, search_by )
		{
			var obj = js_getElementByName( "search_For" );
			
			obj.value = term;
			
			var oSelect = js_getElementByName( "search_By" );
			
			if( oSelect )
			{
				for (i=0;i<oSelect.length; i++ )
				{
					if( oSelect[i].value == "BY_KEYWORDS" && search_by == "KEYWORDS" )
						oSelect.value = "BY_KEYWORDS";
					else if( oSelect[i].value == "BY_TITLE" && search_by == "TITLE" )
						oSelect.value = "BY_TITLE";					
					else if( oSelect[i].value == "BY_AUTHOR" && search_by == "AUTHOR" )
						oSelect.value = "BY_AUTHOR";										
					else if( oSelect[i].value == "BY_SUBJECT" && search_by == "SUBJECT" )
						oSelect.value = "BY_SUBJECT";						
					else if( oSelect[i].value == "BY_CALLNUM" && search_by == "CALLNUM" )
						oSelect.value = "BY_CALLNUM";
					else if( oSelect[i].value == "BY_ISBN" && search_by == "ISBN" )
						oSelect.value = "BY_ISBN";																
					else if( oSelect[i].value == "BY_ISSN" && search_by == "ISSN" )
						oSelect.value = "BY_ISSN";
				}		
				
				if( oSelect.value == "" )
					oSelect.value = "BY_KEYWORDS";
					
				var hintForSearch = js_getElementByName( "hintEvalForSearch" );
				
				eval( hintForSearch.value );
			
			}
		}
	
// 
// IExplorer no soporta document.getElementsByClassName
// 10nov2009
//
function js_getElementsByClassName( strclassName, parentElement ) 
{
	if( document.getElementsByClassName )
	{
		return document.getElementsByClassName( strclassName );
	}
	else
	{
		var children = parentElement.getElementsByTagName('*');		
		var elements = [], child;
		
		for (var i = 0, length = children.length; i < length; i++) 
		{
		  child = children[i];
		  
		  if( child.className == strclassName )
			  elements.push( child );
		}
		
		return elements;
	}
  
}

function doNothing()
{
}

function octal( str )
{
	str = str.replace( "¿", "\277" );
	str = str.replace( "&iquest;", "\277" );	
	
	str = str.replace( "ñ", "\361" );
	str = str.replace( "&ntilde;", "\361" );		
	
	str = str.replace( "á", "\341" );
	str = str.replace( "&aacute;", "\341" );
	
	str = str.replace( "é", "\351" );
	str = str.replace( "&eacute;", "\351" );
	
	str = str.replace( "í", "\355" );
	str = str.replace( "&iacute;", "\355" );
	
	str = str.replace( "ó", "\363" );
	str = str.replace( "&oacute;", "\363" );
	
	str = str.replace( "ú", "\372" );
	str = str.replace( "&uacute;", "\372" );
	
	return str;
}
