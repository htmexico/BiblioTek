<?php
	session_start();
	
	include "funcs.inc.php";

?>

	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

		<title>Welcome to BiblioTEK</title>

	</head>	


	<SCRIPT language="JavaScript" type="text/javascript">

		
		//
		// 
		//
		function init()
		{
			if( navigator.language ) 
			{
				if( navigator.language.substr(0,2) == "es" )
				{
					// idioma español
					document.location.href = "bibliotek_info.php";
				}
			}
			else if( navigator.userLanguage ) 
			{
				if( navigator.userLanguage.substr(0,2) == "es" )
				{
					// idioma español
					document.location.href = "bibliotek_info.php";
				}
			}
			
		}
	
	</SCRIPT>

<body id="home">
<?php
  display_global_nav();
 ?>

<div id="contenedor">

<?php 
   display_banner();
 ?>

<div id="bloque_principal"> <!-- inicia contenido -->
 <div id="contenido_principal">
	
	<div class="caja_con_ligas">

		<p>If you're managing a school library <strong>BiblioTEK</strong> is the perfect solution for you. But also, if you're in charge of a public or special collection library BiblioTEK 
		          simplifies time consuming tasks, reduces administrative overhead and increases library management efficiency. <br><br>
				  For your users <strong>BiblioTEK</strong> has an unique and powerful environment that enables optimal use of library resources either by using them on-site or on-line.</p>
				  
				  <br style='clear:all'>
		
		<div class="lista_elementos_indexada">
		
          <h2><strong>Examples of other appliances</strong></h2>
									
          <ol>
            <li>School libraries (private or public).</li>
            <li>Companies with certifications (i.e. ISO like)</li>
            <li>Museums</li>
            <li>Law firms and Hotels</li>
            <li>Hotels</li>
          </ol>
        </div>
		

	</div><!-- caja_con_ligas -->	

 </div> <!-- contenido_principal -->
 
 <div id="contenido_adicional">
	
	<div class="resaltados" >
		<h2>BiblioTEK is fast and easy to implement!</h2>
		<br>
		<p>
			We can have you up and running without the added expenses of special equipment or technical personnel. <br>
			<br>You just need an ordinary Internet connection the start operations immediately.
		
		</p>

	</div>	
	
  </div> <!-- contenido_adicional -->

</div>
<!-- end div bloque_principal -->

<?php  display_copyright();

 ?>

</div><!-- end div contenedor -->

</body>

</html>