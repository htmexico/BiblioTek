<?php
	session_start();

	/*******
	  Historial de Cambios
	  
	  21 abr 2009: Se crea el archivo PHP para descartar items/ejemplares del acervo
	  22 abr 2009: Se perfecciona el despliegue de consulta
	  
	  PENDIENTES:
	  
	     - Colocar filtros para cuando haya muchos descartes, puedan ser filtrados
		 p.e. por fecha o por usuario
		 
     */
		
	include ("../funcs.inc.php");
	include "../actions.inc.php";
	include "../privilegios.inc.php";
	include "../basic/bd.class.php";
	
	include_language( "global_menus" );

	check_usuario_firmado(); 
	
	include ("../basic/head_handler.php");  // Coloca un encabezado HTML <head>
	
	include_language( "anls_descartes" ); // archivo de idioma
	
	HeadHandler( "$LBL_DESCARTES_HEADER", "../" );
	
	$id_usuario_created=0;
	
	if( isset($_GET["id_usuario_created"] ) ) $id_usuario_created = $_GET["id_usuario_created"];
	
	$filter = read_param( "filter", "", 0 );

	verificar_privilegio( PRIV_DISCARDS, 1 );
?>


<SCRIPT language="JavaScript">

	
	function filtrar_descartes( obj )
	{
		var url = "anls_descartes.php?filter=" + obj.value;
		
		js_ChangeLocation( url );
	}
	
	function registrousuarios()
	{
		js_ChangeLocation( "serv_registrousuarios.php" );
	}
	
	function auth_descarte( id_descarte )
	{
		var url = "anls_descarta_titulos.php?id_descarte=" + id_descarte + "&action=view";
		
		if( <?php echo allow_use_of_popups() ? "1" : "0"; ?> )
		{
			var nwidth = screen.width;
			var nheight = screen.height;
			 
			window.open( url, "autoriza_descarte", "WIDTH=" + (nwidth-50) + ",HEIGHT=" + (nheight-120) + ",TOP=20,LEFT=30,resizable=yes,scrollbars=yes,status=yes" );
		}
		else
		{	
			js_ChangeLocation( url );
		}
		
	}
	
</SCRIPT>

<style>

#info_general 
{
	width: 840px;
}

</style>

<body id="home">

<?php
  
  display_global_nav();  // barra de navegación superior
  
  $db = new DB();
  
 ?>

<!-- contenedor principal -->
<div id="contenedor">

<?php 
   
   display_banner();  // banner   
   display_menu('../'); // menu principal
   
 ?>
   <div id="bloque_principal"> 
      <div id="contenido_principal">
		<h1><?php echo $LBL_DESCARTES_HEADER;?></h1>

		<div id="info_general" class="caja_datos">
	   
		<div style='float:left'>
			<div style='display:inline'><?php echo $LBL_FILTER;?></div>
			<div style='display:inline'>
				<SELECT name="cmb_filtrar" id="cmb_filtrar" onChange='javascript:filtrar_descartes( this );'>
					<OPTION value='*'><?php echo $LBL_ALL;?></OPTION>
					<OPTION value='ONLY_AUTH' <?php echo ($filter=="ONLY_AUTH") ? "SELECTED" : "";?>><?php echo $LBL_ONLY_AUTH;?></OPTION>
					<OPTION value='ONLY_PNDT' <?php echo ($filter=="ONLY_PNDT") ? "SELECTED" : "";?>><?php echo $LBL_ONLY_PDNT;?></OPTION>
				</SELECT>
			</div>
		</div> 		
		
		<div style='float:right; display:none;'>
			<input class="boton" type="button" value="<?php echo $BTN_FILTER_BY_DATE;?>" name="btnNuevoUsuario" onClick="javascript:filtrar_descartes();">
		</div> 

		<br><br>
	   
	   <?php 
			if( $id_usuario_created != 0 ) 
			{
				echo "<div class=caja_info>";
				echo " <strong>$SAVE_CREATED_DONE</strong>";
				echo "</div>";
			}
		  
			$db->sql = "SELECT a.*, b.PATERNO, b.MATERNO, b.NOMBRE, b.USERNAME " . 
					   " FROM descartes_mst a " . 
					   "  LEFT JOIN cfgusuarios b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_USUARIO=a.USUARIO_REGISTRA) ";
					   
			if( $filter == "ONLY_AUTH" )
				$db->sql .= "WHERE a.USUARIO_AUTORIZA <> 0";
			else if( $filter == "ONLY_PNDT" )
				$db->sql .= "WHERE (a.USUARIO_AUTORIZA = 0) or (a.USUARIO_AUTORIZA is NULL)";
					   
			$db->sql .= "ORDER BY a.FECHA_REGISTRA, a.ID_DESCARTE ";
					   
			// crear el paginador		
			$paginador = new Pager( $db, "N", 4 );			

			if( isset( $_GET["page"] ) )
				$paginador->page = $_GET["page"];
			
			$paginador->Calculate_Ranges();
			$db->SetPage( $paginador->start_from, $paginador->Range, $paginador );  // se agrega $paginador				
					   
			$paginador->Language( getsessionvar("language") );
			$db->Open();
						
			echo "<table width=100%>";
			echo "<tr>" .
				  "<td class='cuadricula columna columnaEncabezado' width='50px'>$LBL_ID_DISCARD</td>" .
				  "<td class='cuadricula columna columnaEncabezado' width=100px'>$LBL_DATE_REQUEST</td>" . 
				  "<td class='cuadricula columna columnaEncabezado' width=130px>$LBL_USER_REQUEST</td>" . 				  
				  "<td class='cuadricula columna columnaEncabezado' width=230px>$LBL_TITLE_NAME_MOTIVATION</td>" .
				  "<td class='cuadricula columna columnaEncabezado' width=270px>$LBL_STATUS</td>" . 
				 "</tr>";	
										
			while( $db->NextRow() )
			{
				$id_descarte		= $db->row["ID_DESCARTE"];
				$fecha_registra	    = get_str_datetime( $db->row["FECHA_REGISTRA"], 0 );
				$usuario			= $db->row["USERNAME"];
				$motivo_descarte    = $db->row["MOTIVO_DESCARTE"];
				
				$status = "";
				$detalles = "<a class='icon_link' href='javascript:auth_descarte($id_descarte);'><img src='../images/see_details.png'>";
				$botones = "";
				
				if( $db->row["USUARIO_AUTORIZA"] == 0 )
				{
					$status = "Pendiente de Autorizar...";
					$botones = "<input type=button onClick='javascript:auth_descarte($id_descarte);' class=boton value='Autorizar...'>";
				}
				else
				{
					$status = "Autorizado";
				}
				
				$class_hilite = "hilite_odd";

				if( $db->numRows % 2 == 1 )
					$class_hilite = "hilite_even";				
				
				echo "<tr class='$class_hilite' onMouseOver='javascript:this.className=\"hilite_onmouse_enter\";' onMouseOut='javascript:this.className=\"$class_hilite\";'>" . 
					 "<td align='left' class='cuadricula columna'>&nbsp;$detalles&nbsp;$id_descarte</a></td> " .
					 "<td class='cuadricula columna'>$fecha_registra</td> " .					
					 "<td class='cuadricula columna'>$usuario</td> " .
					 "<td class='cuadricula columna'>$motivo_descarte</td> " .
					 "<td class='cuadricula columna'>$status&nbsp;$botones " . 
					 "</td> " .		
					" </tr>";   			
			}//fin while
			
			echo "</table>";			
			//a partir de aqui viene la paginacion
			
			$paginador->DrawPages();		
		?>		
		
       </div> <!-- - caja datos -->	   
      </div>  <!-- contenido pricipal -->	
		
<?php  display_copyright(); ?>

   </div> <!--bloque principal-->
</div>    <!--bloque contenedor-->
       
</body>
</html>