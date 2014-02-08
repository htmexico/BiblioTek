// Copyright 2006-2007 javascript-array.com
var timeout	= 500;
var closetimer	= 0;
var ddmenuitem	= 0;

var sp_hint = 0;

// open hidden layer
function mopen(id)
{	
	if( sp_hint )
	{
	   sp_hint.style.visibility = 'hidden';
	   sp_hint.style.display = "none";
	}
	
	// cancel close timer
	mcancelclosetime();

	// close old layer
	if(ddmenuitem) 
	{
	   ddmenuitem.style.visibility = 'hidden';
	   ddmenuitem.style.display = "none";
	}

	// get new layer and show it
	ddmenuitem = document.getElementById(id);
	ddmenuitem.style.visibility = 'visible';
	ddmenuitem.style.display = "inline";
	ddmenuitem.style.zIndex = 1;
}
// close showed layer
function mclose()
{
	if(ddmenuitem) 
	{
	   ddmenuitem.style.visibility = 'hidden';
	   ddmenuitem.style.display = "none";
	}
}

// go close timer
function mclosetime()
{
	closetimer = window.setTimeout(mclose, timeout);
}

// cancel close timer
function mcancelclosetime()
{
	if(closetimer)
	{
		window.clearTimeout(closetimer);
		closetimer = null;
	}
}

/** just for  popup's */

function mopen_popup( divx )
{	
	// cancel close timer
	mcancelclosetime();

	// close old layer
	if(ddmenuitem) 
	{
	   ddmenuitem.style.visibility = 'hidden';
	   ddmenuitem.style.display = "none";
	}

	// get new layer and show it
	divx.style.visibility = "visible";
	divx.style.display = "inline";
	divx.style.zIndex = 50;
	
	ddmenuitem = divx;

}

var tabLinkActive = null;

/** TABS **/
function SetTabLinkActive( linkName )
{
	var link = document.getElementsByName( linkName );

	if( tabLinkActive ) 
	{
		tabLinkActive.className = "";
	}
	
	if( link.length > 0 )
	{
		tabLinkActive = link[0];
		
		tabLinkActive.className = "active";
	}

}

function changeTab( parentControl, tabNameForSelecting )
{
	var parent = document.getElementsByName(parentControl);
	
	var divs = parent[0].childNodes;
	
	for( var i = 0; i < divs.length; i++ )
	{
		if( divs[i].tagName == "DIV" )
		{
			if( divs[i].className == "contenedor_tabs" )
			{
				if( divs[i].name == tabNameForSelecting || divs[i].id == tabNameForSelecting )
				{
					divs[i].style.display = "block";
					divs[i].style.visibility = "visible";
				}
				else
				{
					divs[i].style.display = "none";
					divs[i].style.visibility = "hidden";
				}
			}
		}
	}
	
	SetTabLinkActive( tabNameForSelecting + "_link" );

}

function doNothing()
{

}

// close layer when click-out
document.onclick = mclose; 


// Permite mostrar un DIV con la pequaña
// flecha 
function showSP_Hint( div_name )
{
	var the_div = document.getElementsByName(div_name);
	
	if( the_div.length > 0 )
	{
		mopen_popup( the_div[0] );
	}

}

function closeSP_Hint( div_name )
{
	mclosetime();
}

function ShowClose_Hint_SP( e, show_close )
{
	var parentDD = e.parentNode;
	
	do
	{
		if( parentDD.tagName == "DD" )
			break;
		parentDD = parentDD.parentNode;
	} while (parentDD);
	
	if (parentDD)
	{	
		var aSpans = parentDD.getElementsByTagName("span");
		
		//alert( e.name + " " + aSpans.length );
		
		for( var i = 0; i<aSpans.length; i++ )
		{
			if( aSpans[i].className == "sp_hint" )
			{
				if( show_close==1 )
				{
					//alert( aSpans[i].id );
					aSpans[i].style.visibility = "visible";
					aSpans[i].style.display = "inline";  // show
					aSpans[i].style.zIndex = 1;
					
					sp_hint = aSpans[i];
				}
				else
				{
					//alert( "STOPS" );
					aSpans[i].style.visibility = 'hidden';
					aSpans[i].style.display = "none";  // cerrar
				}
			}
		}
	}

}

//
// Permite colocar HINTS (letreros de ayuda)
// al lado de los campos INPUT class=campo_captura y de todos SELECT 
// para que esto funcione tanto los INPUT como SELECT deben estar anidados
// en etiquetas <DT><label>...</label></DT><DD> <SPAN class=sp_hint>AQUI</SPAN> </DD>
// Se ayuda de la función superior: ShowClose_Hint_SP()
//
function prepareInputsForHints() 
{
	var inputs = document.getElementsByTagName("input");
	var bOk;
	
	for (var i=0; i<inputs.length; i++)
	{
		if( inputs[i].className == "campo_captura" )
		{
			bOk = false;
			
			// test to see if the hint span exists first
			if (inputs[i].parentNode.getElementsByTagName("span")[0]) 
			{
				// CASO NORMAL
				//alert( inputs[i].parentNode.tagName );
				//alert( inputs[i].parentNode.getElementsByTagName("span")[0].className );
				bOk = true;
			}
			
			if (inputs[i].parentNode.parentNode )
			{
				if (inputs[i].parentNode.parentNode.getElementsByTagName("span")[0])
				{
					// CASO DE ANIDAMIENTO ADICIONAL
					bOk = true;
				}
			}

			if( bOk )
			{
				// the span exists!  on focus, show the hint
				inputs[i].onfocus = function () 
				{
					ShowClose_Hint_SP( this, 1 );
				}
				// when the cursor moves away from the field, hide the hint
				inputs[i].onblur = function () 
				{
					ShowClose_Hint_SP( this, 0 );
				}
			}
		}
	}
	// repeat the same tests as above for selects
	var selects = document.getElementsByTagName("select");
	
	for (var k=0; k<selects.length; k++)
	{
		if (selects[k].parentNode.getElementsByTagName("span")[0]) 
		{
			selects[k].onfocus = function () {
				ShowClose_Hint_SP( this, 1 );
			}
			selects[k].onblur = function () {
				ShowClose_Hint_SP( this, 0 );
			}
		}
	}
}
