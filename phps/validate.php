<?php
	/****
		Valida un título
		
	 **/
	session_start();
  
	include ( "../funcs.inc.php" );
	include ( "../basic/bd.class.php" );  
	include ( "circulacion.inc.php" );
 
	$return_type = read_param( "return_type", "", 1 );
	
	// Draw an html head
	include ("../basic/head_handler.php");
	HeadHandler( "BiblioTEK", "../" );	

	$id_biblioteca = getsessionvar( "id_biblioteca" );
	
?>

<SCRIPT type='text/javascript' language='javascript'>

	function CloseDescripTitle()
	{
		var lbl_id = window.opener.document.getElementsByName( 'lbl_id_title' );
		if( lbl_id.length > 0 )
		{
			// cerrar el parentNode
			//alert( lbl_id[0].parentNode.style.display );
			if( lbl_id[0].parentNode.style.display == 'none' || lbl_id[0].parentNode.style.display == '' || lbl_id[0].parentNode.style.display == 'inline' )
			{
				lbl_id[0].parentNode.style.display = "none";
				lbl_id[0].parentNode.style.visibility = "hidden"
			}
		}	
	}

	function ShowDescripTitle( title, icon )
	{
		var lbl_id = window.opener.document.getElementsByName( 'lbl_id_title' );
		if( lbl_id.length > 0 )
		{
			// aparecer el parentNode en caso de que esté oculto
			if( lbl_id[0].parentNode.style.display == 'none' || lbl_id[0].parentNode.style.display == '' )
			{
				lbl_id[0].parentNode.style.display = "inline";
				lbl_id[0].parentNode.style.visibility = "visible"
			}
			CloseDIVError();
			
			var codeHTML = "";
			
			if( icon != "" )
				codeHTML = "<img src='../" + icon + "'>";
			
			codeHTML += "<bold>" + title +"</bold>";
			
			lbl_id[0].innerHTML = codeHTML;
		}
	
	}

	function CloseDIVError()
	{
		var div_error = window.opener.document.getElementsByName( "div_error_title" );

		if( div_error.length == 0 )
			div_error = window.opener.document.getElementsByName( "div_error" );  // buscar otro div posiblemente para error

		if( div_error.length > 0 )
		{
			// cerrar el DIV
			div_error[0].style.display    = "none";
			div_error[0].style.visibility = "hidden";
		}
	}
	
	function ShowDIVError()
	{
		var div_error = window.opener.document.getElementsByName( "div_error_title" );

		if( div_error.length == 0 )
			div_error = window.opener.document.getElementsByName( "div_error" );  // buscar otro div posiblemente para error

		CloseDescripTitle();
			
		if( div_error.length > 0 )
		{
			// aparecer el DIV
			div_error[0].style.display    = "inline";
			div_error[0].style.visibility = "visible";
		}
	}
	
</SCRIPT>

<BODY>

<?php
	
	if( $return_type == "ID_TITLE" )
	{
		$value = read_param( "value", "", 0 );
		
		$db  = new DB();
		$db->Open(  "SELECT * ".
					"FROM acervo_titulos ".
					"WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$value;"); 
		
		if( $db->NextRow() )
		{
			$item = new TItem_Basic( $id_biblioteca, $db->row["ID_TITULO"] );
						
			$title = $item->cTitle;
			$icon  = $item->cIcon;
			
			unset( $item );
			
			echo "<script language='Javascript'>\n";
			echo "  ShowDescripTitle( '$title', '$icon'  ); ";
			echo "	window.close();";
			echo "</script>\n\n";		
		}
		else
		{
			echo "<script language='Javascript'> ShowDIVError();\n";
			echo "CloseDescripTitle();";
			echo " window.close();";
			echo "</script>\n\n";		
		}
		
		$db->FreeResultset();
		$db->destroy();
		
		
	}

	
 ?>
 
 </BODY>

</html>
