BiblioTek
=========

OpenSource Software for School Libraries

Usted puede descargar e instalar libremente esta aplicación que le permitirá sistematizar los siguientes procesos que se 
desarrollan en las bibliotecas escolares.

- Catalogación basado en MARC.
- Analisis (Registro, Descartes, Consultas, etc.)
- Circulación (reservaciones, préstamos, renovaciones, devoluciones, etc.)
- Creación de usuarios internos y beneficios de los recursos bibliográficos.
- Establecimiento de políticas operativas.

Requisitos de Uso:

- Apache 2.x
- PHP 5.2.x
- Servicio de email de salida para aprovechar la mensajería automatizada.
- Firebird 2.1 o superior.

Equipo de cómputo necesario:

- Servidor de Internet Windows o Linux principalmente.
- Este servidor deberá ser un servidor web completamente funcional que le permita tener acceso público y de esta forma tener 
operativo el servicio a sus usuarios.

Instrucciones de instalación
-------------------

Clone el repositorio:

1) Ubíquese en el directorio /www-bibliotek/  Si aún no lo ha creado, hágalo ahora:

	Clone este repositorio github con la sig. instrucción:
	
	git clone https://github.com/htmexico/BiblioTek.git

    * Lo anterior creara un nuevo subdirectorio llamado BiblioTek.... es decir tendremos /www-bibliotek/BiblioTek/

2) Configure todo lo necesario para que Apache webserver direccione su directorio /www-bibliotek, como un dominio
   independiente o como un subdominio.
   
   Para una funcionalidad plena es probable que necesite configurar registros DNS, crear un dominio ante una autoridad o utilizar un panel
   de control tipo Plesk o CPanel.
   
3) Descargue la estructura de la base de datos y genere la base de datos firebird en blanco.   

	Comprueba la correcta funcionalidad de la base de datos y tome nota de la ruta completa.
	
	P.e. /var/lib/firebird-2.5/db/biblioteca.fdb   o   /opt/firebird_data/biblioteca.fdb   
  
4) Configure su aplicación BiblioTEK, a través de archivos de código fuente de parámetros:

Después de clonar este repositorio, utilice un editor de texto para crear los siguientes archivos de configuración:

APP_CONFIG.php

<?php
unset( $ACCESS_CFG );

$ACCESS_CFG = new stdClass();

$ACCESS_CFG->id_biblioteca    = 1;
$ACCESS_CFG->codigo_cuenta = "biblio"; // colocar un codigo de identificacion: funcionalidad pendiente
$ACCESS_CFG->banner = "images/banner_rapido.jpg";

$ACCESS_CFG->http_public_dir = "http://subdominio.mi_biblioteca.edu/";
?>



GLOBAL_CONFIG.php

<?php

$www_dir = "http://bibliotek.yourdomain.edu/";
$app_dir = "/www-bibliotek/BiblioTek/";

?>

Coloque APP_CONFIG.php y GLOBAL_CONFIG.php en el directorio /www-bibliotek/

------------------

Archivo de configuración de base de datos

<?php
unset($CFG);

global $CFG;

$CFG = new stdClass();

$CFG->db_type    = 'interbase';   // solo interbase
$CFG->db_host    = 'localhost'; 
$CFG->db_name    = '/yourpath/biblio.fdb';
$CFG->db_user    = 'SYSDBA';
$CFG->db_pass    = '***password***';

?>
 
 Archivo de configuración de salida de correo electrónico:
 
<?php
unset($EMAIL);

global $EMAIL;

$EMAIL = new stdClass();

$EMAIL->smtp_host    = "mail.yourdomain.edu";
$EMAIL->smtp_user    = "service@yourdomain.edu";
$EMAIL->smtp_pass    = "";
$EMAIL->smtp_port	 = 25;

$EMAIL->image_background = "http://www.yourdomain.edu/images/logoFondoMail.jpg";
$EMAIL->font_default_css = "font-family:verdana,arial,helvetica; font-size:12px; font-color: black; ";

 ?>

Coloque ambos archivos en el directorio /www-bibliotek/BiblioTek/