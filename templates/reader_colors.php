<style>
	
	/* SE USAN LOS MISMOS NOMBRES DE CLASE QUE EN SCREEN.CSS 
	   PARA CONSERVAR EL ESTILO BASICO... SOLAMENTE SE AGREGAN
	   ALGUNAS PROPIEDADES ESPECIFICAS PARA DARLE EL LOOK DESEADO AL TEMA.
	 */
	body 
	{
	background-color:#fff;
	padding-bottom: 5px;
	color: #fff;
	}
	
	#banner h1 {
		
	margin:0;
	width:960px;
	height:100px;
	background-repeat:no-repeat; 
}

	.caja_con_ligas DIV
	{
		font-size: 17px;
	}

	.caja_con_ligas li a 
	{ 
	  
	  font: bold 75% comic sans ms;
	  font-style:italic;
	} 

	h1, h3, h4, h5 
{
	font-family:Arial Narrow;
}


h1 {
	
	color:#CC66CC;
	 }

h2 {

	font-size:110%; /*FUENTE ENCABEZADO NOTAS DESTACADAS */
	margin-bottom:5px;
	margin-top:1em; 
	line-height:1.1em; }

/*************
	NAV  
*************/

#global-nav {
			text-align:right;
			width:960px;
			margin:0 auto;
			padding:8px 0 4px;
			color:#CC9900; }/*COLOR DE BARRAS Y NOMBRE DE USUARIO DE CUENTA*/

#global-nav a {

			font-size:80%;
			font-family:Verdana, Arial, sans-serif; 
			color:#CC9900; } /*COLOR TEXTO DE ENCABEZADOS O LIGAS DE PAG. PRINCIPAL*/

/*COLOR DE LIGAS*/
a:link {
	color:#36D700;
}

/*COLOR DE LIGAS YA ACCESADAS*/
a:visited {
	color:#FFCC00;
}

.caja_con_ligas h1 { /*ENCABEZADOS DE LIGAS RAPIDAS*/

	font-size:102%;
	color:#CC66CC; 
	letter-spacing:2px;
	font-style:italic;
	 }

.info {
	font: bold 110% comic sans ms;
	font-style: italic;
	color:#33CC00;
	 } 

div.resaltados {
	overflow:auto;
	border: 3px solid #A6FFA6; /*COLOR DE BORDE NOTAS DESTACADAS*/
	padding: 10px;
	font-size: 88%;
	color: #993399;
	background: url(templates/images/fondo_borde_colors.jpg);
}

div.resaltados h2 
{
	margin:0; 
	color:#FF6666; /*ENCABEZADO NOTAS DESTACADAS*/
	font-size: 15px;
	font-family: Comic Sans MS;
}

/*BULLETS*/
.mini_bullet
{
	float: left;
	width: 20px;
    height: 13px;
	background: url(default/mini_bullet.png) left top no-repeat;
}

#contenedor {
	border:1px solid #000000;
	background-color:#FFFFFF;
	background-image:url(templates/images/fondo_colors.jpg);
	width:960px;
	margin:0 auto;
	position:relative;
	text-align: left;
	color: #0099FF; /*color de texto de ligas rapidas viñetas*/
	
}

/* menus y submenus */
/* barra del menu (con opciones principales) */
/* menu principal */
#sddm
{	margin: 0;
	padding: 0;
	z-index: 30;
	}

#sddm li
{	margin: 0;
	padding: 0;
	list-style: none;
	float: left;
}

#sddm ul 
{
	margin:0; 
	padding:0;
	list-style:none; 
}

#sddm li a
{	display: block;
	padding: 4px 10px;
	width: auto;
	color: #FFF;
	text-align: center;
	border-top:1px solid #A88E51;
	border-left:1px solid #A88E51;	
	border-right:1px solid #F2F8BE;
	text-decoration: none}

#sddm li a:hover
{	background: #49A3FF;
	padding: -4px -2px -2px -2px;
	background-color:#FCF247;
	text-decoration: underline;
	border-top:1px solid #000;
	border-left:1px solid #000;
	border-right:1px solid #000;	
	color: #000;
}

/* submenus */
	#sddm div
	{	position: absolute;
		visibility: hidden;
		margin: 0;
		padding: 0;
		background-color: #E8E364;
		border: 1px solid #000; /*6B6A5A;*/
		font-size:90%;
	}

	#sddm div a
	{	position: relative;
		display: block;
		margin: 0;
		padding: 5px 10px;
		width: auto;
		white-space: nowrap;
		text-align: left;
		text-decoration: none;
		background-color: #F1F0D0;
		color: #000; 
		font-size: 11px;
		border: 0px solid black;
	}

	#sddm div a:hover
	{	
		border: 0px solid black;
		background-color:#FCF247;
	}

	#sddm div img 
	{	
		background: url(../images/separador_menus.gif); 
		background-color: #F1F0D0;
		height: 3px;
	}

.highlight   
{   
background: #F8DCB8;   
}   
 
.caja_con_ligas h1
	{
		background: url(templates/images/enc_colors.png) no-repeat left top;
		width: 310px;
		padding:10px;
	}
 /****
  COLOCAR EN SCREEN.CSS
 */
/* The hint to Hide and Show */
.sp_hint {
   	display: none;
    position: absolute;
    right: 10px;
    width: 200px;
    margin-top: -4px;
    border: 1px solid #CC00CC;
    padding: 10px 12px;
	color: #CC00CC;
	/* to fix IE6, I can't just declare a background-color,
    I must do a bg image, too!  So I'm duplicating the pointer.gif
    image, and positioning it so that it doesn't show up
    within the box */
    background: #E9BBFF url(../bullets/pointer_colors.gif) no-repeat -10px 5px;
}

/* The pointer image is hadded by using another span */
.sp_hint .hint-pointer {
    background: url(templates/bullets/pointer_colors.gif) left top no-repeat;
}

/*VIÑETAS O BULLETS*/	
.mini_bullet {
 
float: left; 
width: 20px; 
height: 13px; 
background: url(templates/bullets/bullet_green.png) left top no-repeat; 
} 	


/*
#navlist 
{ 
margin-left: 0; 
padding-left: 0; 
list-style: none; 
}

#navlist li 
{ 
padding-left: 10px; 
background-image: url(templates/bullets/pointer_pink.gif); 
background-repeat: no-repeat; 
background-position: 0 .5em; 
} */


div.caja_datos #vinetas 
{
	width:52%;
	float:left; 
	padding:20px 0.7em 10; 
	color: #CCCF8;
}	

div.caja_datos #vinetas ul 
{
	list-style:square;
	list-style-image:url(../../templates/bullets/bullet_bob.png);
	list-style-type:disc;
	/*padding-left: 10px; 
	background-image: url(templates/bullets/pointer_pink.gif); 
	background-repeat: no-repeat;*/ 
	
}

div.caja_datos #vinetas li {
	font-size:100%; 
	margin:0; 
	line-height:1.2em; 
	padding-left: 10px; 
	}
	
</style>

<div id="contenido_principal">

<h1><img src="templates/iconos/Red_Butterfly.png" width="40" height="40" /><?php echo getsessionvar("nombreusuario");?></h1>
<p class='info'><?php echo $BIBLIO_TEK_WELCOME_USERS;?></p>

<?php
	require_once( "phps/opac.php" );

	global $db;
	
	$id_consulta = obtener_consulta_default_x_usuario( $db, getsessionvar("id_biblioteca") );

 	if( $id_consulta != 0 )
	{	echo "<br>";
		muestra_consulta( $db, getsessionvar("id_biblioteca"), $id_consulta );
	}

	//echo "<p>&nbsp;<a href=''>Reservaciones</a></p>";

?>


<div class='caja_con_ligas'>

	<div class='lista_elementos_indexada' style='width:49%'>
		<h1><span class="lista_elementos_indexada" style="width:49%"></span><img src="templates/iconos/Shared.png" width="32" height="32" /><?php echo  $RECENT_USED_ITEMS;?></h1>

<?php
	echo "	  <ol>";
	echo "		<li><a class='TMPL_Ligas' href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=1'>[Libro] Don Quijote de la Mancha</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=2'>[DVD] Aprenda Mecanografía</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=3'>[Libro] Harry Potter y la Piedra Filosofal</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=4'>[Libro] Filosofía I</a></li>";
	echo "	  </ol>";
	
	// muestra_items_recien_usados();

?>
	</div>

	<div class='lista_elementos_indexada' style='width:48%'>
		<h1><img src="templates/iconos/Envolopes.png" width="32" height="32" /><?php echo $MOST_FRECUENT_SUBJECTS;?></h1>

<?php	
	
	echo "	  <ul>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=1'>Historia</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=2'>US Presidents</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=3'>Paleontología</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=4'>Revolución Mexicana</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=5'>Software.</a></li>";
	echo "	  </ul>";
	
	// muestra_temas_mas_consultados();
?>
	
	</div>
	
	<br clear='both'><br>
	
	<div class='lista_elementos_indexada' style='width:48%'>
		<h1><img src="templates/iconos/Glass_box.png" width="32" height="32" /><?php echo $RECENT_CONTRIBUTIONS;?></h1>
	
<?php	
	
	echo "	  <ol>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=1'>Comentario al True Story of Morelos...</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=2'>US Presidents</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=3'>Paleontología</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=4'>Revolución Mexicana</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=5'>Software.</a></li>";
	echo "	  </ol>";
	
	// muestra_contribuciones();
	
?>

	</div>
	
	<div class='lista_elementos_indexada' style='width:48%'>
		<h1><img src="templates/iconos/Charts.png" width="32" height="32" /><?php echo $RECENT_ACTIVITIES;?></h1>

        <?php	
	echo "	  <ul>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=1'>Jan/2009 Writting Contest</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=5'>Mar/2009 Share your a Story.</a></li>";
	echo "	  </ul>";
	
	// muestra_ultimas_actividades_pedagogicas();
?>

	</div>

</div><!-- caja_con_ligas -->

</div> <!-- contenido_principal -->

<div id='contenido_adicional'>

	<div class='resaltados'>
		<h2><img src="templates/iconos/notebook.png" width="40" height="40" /><?php echo $VIRTUAL_STORAGE_SPACE;?></h2>
		<br>
<?php

	include_once "phps/bandeja.inc.php";
	paperbin_show_themes_for_user();
	echo "<br>";
	paperbin_show_contents();
	echo "<br>";
	
	echo "	<a href=''>Mis archivos...</a><br><br>";
	
	echo "	<a href=''>Historial de Actividades...</a><br><br>";
			
?>

  </div>

</div> <!-- contenido_adicional -->	