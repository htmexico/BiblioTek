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
	  
	  font: bold 80% Arial Narrow; /*TIPO DE FUENTE DE LAS CAJAS DE TEXTO*/
	  font-style:normal;
	} 

	h1, h3, h4, h5 
{
	font-family:Verdana; /*FUENTE DE ENCABEZADOS PRINCIPALES*/
}
	
h1 {
	
	color:#FFFFFF; /*COLOR DE FUENTE DE NOMBRE PRINCIPAL*/
	font-size:17px;
	 }

h2 {

	font-size:70%; /*FUENTE ENCABEZADO NOTAS DESTACADAS */
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
	color:#00FF00;
}

/*COLOR DE LIGAS YA ACCESADAS*/
a:visited {
	color:#FFFFFF;
}

.caja_con_ligas h1 { /*COLOR, TAMA�O ENCABEZADOS DE LIGAS RAPIDAS*/
	font-size:70%;
	letter-spacing:2px;
	font-style:italic;
	color: #009900;
	 }

.info {
	font: bold 115% Arial Narrow; /*TIPO DE FUENTE DE INFO PRINCIPAL*/
	color:#00FF00;
	 } 

div.resaltados {
	overflow:auto;
	border: 2px solid #7FBE31; /*COLOR DE BORDE NOTAS DESTACADAS*/
	padding: 10px;
	font-size: 88%;
	background: url(templates/images/fondo_borde_abs.jpg);
	color: #FFFFFF;
}

div.resaltados h2 
{
	margin:0; 
	color:#FFFF00; /*ENCABEZADO NOTAS DESTACADAS*/
	font: bold 135% Comic sans ms;
}

#contenedor {
	border:1px solid #000000;
	background-color:#FFFFFF;
	background-image:url(templates/images/fondo_abstracto.jpg);
	width:960px;
	margin:0 auto;
	position:relative;
	text-align: left;
	color: #FFFF00; /*color de texto de ligas rapidas vi�etas*/
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
		background-color: #006633;
		height: 3px;
	}

.highlight   
{   
background: #F8DCB8;   
}   
  
  /*ELEMENTOS PARA ENCABEZADOS Y LISTA INDEXADA*/	
	.caja_con_ligas h1
	{
		background: url(templates/images/enc_abs.png) no-repeat left top;
		width: 310px;
		padding:13px;
	}
	
	div.lista_elementos_indexada
	{
	
	background: url(templates/images/idex_abs.png) no-repeat left top;
		
	}
	
	
 /***
  COLOCAR EN SCREEN.CSS
 */
/* The hint to Hide and Show */
.sp_hint {
   	display: none;
    position: absolute;
    right: 10px;
    width: 200px;
    margin-top: -4px;
    border: 1px solid #7FBE31;
    padding: 10px 12px;
	color:#006600;
    /* to fix IE6, I can't just declare a background-color,
    I must do a bg image, too!  So I'm duplicating the pointer.gif
    image, and positioning it so that it doesn't show up
    within the box */
    background: #fff url(../bullets/pointer_abs.gif) no-repeat -10px 5px;
}

/* The pointer image is hadded by using another span */
.sp_hint .hint-pointer {
    
    background: url(templates/bullets/pointer_abs.gif) left top no-repeat;
}

/**VI�ETAS O BULLETS*/	
.mini_bullet {
 
float: left; 
width: 20px; 
height: 13px; 
background: url(templates/bullets/bullet_green.png) left top no-repeat; 
} 	
	
</style>

<div id="contenido_principal">

<h1><img src="templates/iconos/Earth.png" width="40" height="40" /><?php echo getsessionvar("nombreusuario");?></h1>
<p class='info'><?php echo $BIBLIO_TEK_WELCOME_USERS;?></p>
<p class='info'>&nbsp;</p>
<p class='info'>
  <?php
	
	echo "<p><a href=''>Consultas al Cat�logo</a>&nbsp;&nbsp;&nbsp;<a href=''>Reservaciones</a></p>";
	
?>
</p>
<div class='caja_con_ligas'>

	<div class='lista_elementos_indexada' style='width:49%'>
		<h1><span class="lista_elementos_indexada" style="width:49%"></span><img src="templates/iconos/carp_paisaje.png" width="32" height="32" /><?php echo  $RECENT_USED_ITEMS;?></h1>

<?php
	echo "	  <ol>";
	echo "		<li><a class='TMPL_Ligas' href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=1'>[Libro] Don Quijote de la Mancha</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=2'>[DVD] Aprenda Mecanograf�a</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=3'>[Libro] Harry Potter y la Piedra Filosofal</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=4'>[Libro] Filosof�a I</a></li>";
	echo "	  </ol>";
	
	// muestra_items_recien_usados();

?>
	</div>

	<div class='lista_elementos_indexada' style='width:48%'>
		<h1><img src="templates/iconos/fav_paisaje.png" width="32" height="32" /><?php echo $MOST_FRECUENT_SUBJECTS;?></h1>

<?php	
	
	echo "	  <ul>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=1'>Historia</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=2'>US Presidents</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=3'>Paleontolog�a</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=4'>Revoluci�n Mexicana</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=5'>Software.</a></li>";
	echo "	  </ul>";
	
	// muestra_temas_mas_consultados();
?>
	
	</div>
	
	<br clear='both'><br>
	
	<div class='lista_elementos_indexada' style='width:48%'>
		<h1><img src="templates/iconos/Green_Leaf.png" width="32" height="32" /><?php echo $RECENT_CONTRIBUTIONS;?></h1>
	
<?php	
	
	echo "	  <ol>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=1'>Comentario al True Story of Morelos...</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=2'>US Presidents</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=3'>Paleontolog�a</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=4'>Revoluci�n Mexicana</a></li>";
	echo "		<li><a href='http://www.grupoges.com.mx/grupoges_newsletter.php?num=5'>Software.</a></li>";
	echo "	  </ol>";
	
	// muestra_contribuciones();
	
?>

	</div>
	
	<div class='lista_elementos_indexada' style='width:48%'>
		<h1><img src="templates/iconos/flower.png" width="32" height="32" /><?php echo $RECENT_ACTIVITIES;?></h1>

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
		<h2><img src="templates/iconos/lime.png" width="40" height="40" /><?php echo $VIRTUAL_STORAGE_SPACE;?></h2>
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