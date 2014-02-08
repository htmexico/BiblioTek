<?php
	session_start();

	/**********

		28-abril-2009	Se crea el archivo.
		30-abril-2009	Se perfeccione la selección de categoría.
		
		PENDIENTE:  
		
		   1) Filtrar o Buscar
		   2) Paginar alfabéticamente
	 
	 **/

	include ("../funcs.inc.php");

	check_usuario_firmado(); 

	// Draw an html head
	include ("../basic/head_handler.php");
	include ("../basic/bd.class.php");
	
	include_language( "global_menus" );
	include_language( "gral_abrirtesauro" );

	HeadHandler( "$LBL_CONSULT_HEADER", "../" );
	
	$id_categoria = read_param( "id_categoria", 0, 1 );	
	$subcategoria = read_param( "subcategoria", 0 );	
	$control = read_param( "control", "", 1 );
	$now = read_param( "now", "", 1 );
	$descrip_termino = read_param( "descrip_termino", 0 );
	
	$db = new DB();
	$db->sql  = "SELECT a.ID_CATEGORIA, a.DESCRIPCION FROM tesauro_categorias a ";
	$db->sql .= "WHERE a.ID_RED=" . getsessionvar("id_red") . " and a.ID_CATEGORIA=$id_categoria";
	$db->Open();
	
	$descrip_categoria = "";
	
	if( $db->NextRow() )
	{	
		$id_categoria      = $db->row["ID_CATEGORIA"];
		$descrip_categoria = $db->row["DESCRIPCION"];
	}
	
	$db->FreeResultset();

?>
	
<script type="text/javascript">

	function OpenSubcategory( id_categoria, id_termino )
	{
		window.location.href = "gral_abrirtesauro.php?id_categoria=" + id_categoria + "&subcategoria=" + id_termino + "&control=<?php echo $control;?>&now=<?php echo $now;?>&descrip_termino=<?php echo $descrip_termino;?>";
	}
	
	function goBack( id_categoria )
	{
		window.location.href = "gral_abrirtesauro.php?id_categoria=" + id_categoria + "&control=<?php echo $control;?>&now=<?php echo $now;?>&descrip_termino=<?php echo $descrip_termino;?>";
	}
	
	function SelectCodigoTermino( codigo )
	{	
		var id_control = window.opener.document.getElementsByName( "<?php echo $control;?>" );
		
		if( id_control.length > 0 )
			id_control[0].value = codigo;
			
		window.close();
	}	
	
	function SelectDescripTermino( descrip_termino )
	{	
		var id_control = window.opener.document.getElementsByName( "<?php echo $control;?>" );
		
		if( id_control.length > 0 )
			id_control[0].value = descrip_termino;
			
		window.close();
	}

</script>

<body id="home">

<br>

<div id="contenedor"> 

<div id="bloque_principal">

  <div id="contenido_principal" style='width: 90%;'>

	<H2><?php echo $descrip_categoria;?></H2><HR>
	
		<?php
		
			if( $id_categoria != "" )
			{
				$db->sql = "SELECT a.*, b.TERMINO, b.CODIGO_CORTO, b.DESCRIPCION, b.TERMINO_PADRE " . 
							"FROM tesauro_terminos_categorias a " .
							"  LEFT JOIN tesauro_terminos b ON (b.ID_RED=a.ID_RED and b.ID_TERMINO=a.ID_TERMINO) " .
							"WHERE a.ID_RED=" . getsessionvar("id_red") . " and a.ID_CATEGORIA=$id_categoria and (b.MACROTERMINO='S') " . 
							"ORDER BY b.TERMINO";
				
				$db->Open();
				
				$newpage = 1;
				
				$num = 0;
				
				while( $db->NextRow() )
				{	
					if( $db->numRows == 1 )
					{
						echo "<div class='caja_datos' id='caja_datos1'>";

						echo $LBL_CONSULT_HEADER;

						echo  "<br><br>";
						echo "<table border='0' width='100%' style='font-size:85%;'>";
					}
					
					if( $newpage == 1 ) 
					{
						if( $num > 0 )
						{
							echo "</tr>";	
						}
						
						$num = 0;
						$newpage = 0;
					
						echo "<tr>";
					}
					
					$prefix = "";
					$sufix = "";
					
					if( $db->row["ID_TERMINO"] == $subcategoria )
					{
						$prefix = "<strong>";
						$sufix = "</strong>";					
					}
					
					echo  "<td><a href='javascript:OpenSubcategory($id_categoria," . $db->row["ID_TERMINO"] . ");'>$prefix" . $db->row["TERMINO"] . "$sufix</a></td>" ;
					
					$num++;
					
					if( $num>4 ) 
					   $newpage = 1;
				}
				
				$db->FreeResultset();			
			
				if( $num > 0 )
				{
					echo "</tr>";
					$newpage = 1;
				}
			
				if( $db->numRows > 0 )
				{
					echo "</table><br>\n";
					echo "</div><br> <!-- caja_datos1 -->\n\n";
				}
			}
		
		 ?>		
	
	<?php
		echo "<br>";
		
		if( $subcategoria == 0 )
			echo "<h3>Términos genéricos</h3><br>";
		else
		{
			$db->sql  = "SELECT a.TERMINO FROM tesauro_terminos a WHERE a.ID_RED=" . getsessionvar("id_red") . " and a.ID_TERMINO=$subcategoria ";
			$db->Open();
			
			if( $db->NextRow() )
				echo "<h3>Términos de la subcategoría: " . $db->row["TERMINO"] . "&nbsp;<a href='javascript:goBack( $id_categoria );'><img src='../images/back.gif'></a></h3><br>";
			else
				echo "<h3>Términos de la subcategoría: $subcategoria </h3><br>";
			
			$db->FreeResultset();
			
		}
			
		echo "<table width=100% border=1>";
		
		echo "<tr>" .
			  "<td class='cuadricula columna columnaEncabezado' width='10%'>Código</td>" .
			  "<td class='cuadricula columna columnaEncabezado' width='25%'>Término</td>" . 
			  "<td class='cuadricula columna columnaEncabezado' width='20%'>Usar</td>" . 
			  "<td class='cuadricula columna columnaEncabezado' width='20%'>Fuente de origen</td>" . 
			  "<td class='cuadricula columna columnaEncabezado' width='20%'>Notas</td>" . 
			 "</tr>";			
		
		// TERMINOS GENERICOS O DE UNA SUBCATEGORIA
		$db->sql = 	"SELECT a.*, b.TERMINO, b.CODIGO_CORTO, b.DESCRIPCION, b.TERMINO_PADRE, b.USAR, b.FUENTE_AGENCIA, b.FUENTE_NOTAS, b.OBSOLETO " . 
					"FROM tesauro_terminos_categorias a " .
					"   LEFT JOIN tesauro_terminos b ON (b.ID_RED=a.ID_RED and b.ID_TERMINO=a.ID_TERMINO) ";
					
		if( $subcategoria == 0 )
			$db->sql .= "WHERE a.ID_RED=" . getsessionvar("id_red") . " and a.ID_CATEGORIA=$id_categoria and (b.TERMINO_PADRE is NULL and b.MACROTERMINO='N') ";
		else
			$db->sql .= "WHERE a.ID_RED=" . getsessionvar("id_red") . " and a.ID_CATEGORIA=$id_categoria and (b.TERMINO_PADRE=$subcategoria) ";
		
		$db->sql .= "ORDER BY b.CODIGO_CORTO";	   
		
		// crear el paginador
		$paginador = new Pager( $db, "A", 18 );
		$paginador->Field_for_Pager = "b.CODIGO_CORTO";
		
		if( isset( $_GET["page"] ) )
			$paginador->page = $_GET["page"];
		
		$paginador->Calculate_Ranges();
		
		$db->SetPage( $paginador->start_from, $paginador->Range, $paginador, "-" );
		
		//$db->DebugSQL();
		$db->Open();
		
		while( $db->NextRow() )
		{
			$link_codigo = "javascript:SelectCodigoTermino( \"" . $db->row["CODIGO_CORTO"] ."\");";
			
			if( $descrip_termino == 0 )				
				$link = $link_codigo;
			else
				$link = "javascript:SelectDescripTermino( \"" . $db->row["TERMINO"] ."\");";
			
			$style = "";
						
			if( $db->row["CODIGO_CORTO"] == $now )
			{
				$style = " style='background-color: #E7F5F7;' "; 
			}
			
			echo "<tr>" .
				  "<td class='cuadricula columna' $style><a href='$link_codigo' onMouseOut='javascript:js_Status(\"\"); return true;' onMouseOver='javascript:js_Status(\"Editar\"); return true;'>" . $db->row["CODIGO_CORTO"] . "</a></td>" . 
				  "<td class='cuadricula columna' $style><a href='$link' onMouseOut='javascript:js_Status(\"\"); return true;' onMouseOver='javascript:js_Status(\"Editar\"); return true;'>" . $db->row["TERMINO"] . "</a></td>" . 
				  "<td class='cuadricula columna' $style>" . $db->row["USAR"] ."</td>" . 
				  "<td class='cuadricula columna' $style>" . $db->row["FUENTE_AGENCIA"] ."</td>" . 
				  "<td class='cuadricula columna' $style>" . $db->row["FUENTE_NOTAS"] ."</td>" . 
				 "</tr>";
		}	
		 
		// TERMINOS DE LA SUB-CATEGORIA (MACROTERMINO)
		
		$db->FreeResultset();
	  
		//print_r( $_SERVER );
	  
		echo "</table>";
		
		$paginador->DrawPages( "" );
	
	 ?>
	
  </div>  <!-- contenido_principal -->
				
  <br>

</div>

<?php  display_copyright(); ?>

</div>
<!-- end div contenedor -->

</body>

</html>