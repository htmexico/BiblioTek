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


--------------------
--------------------

Descargue el manual de instalación con las instrucciones pormenorizadas disponible en:

	http://www.htmex.mx/bibliotek.php


=========
=========

* Instrucciones de instalación

=========
=========

Documentadas desde un servidor Linux Ubuntu 12.04, ingresando como usuario root a través de una terminal SSH
.

1. Instale o verifique la instalación de Apache Web Server 2.4 o superior.
	
	sudo apt-get install apache2
	
2. Instale o verifique la instalación de PHP 5.4 o superior.

	sudo apt-get install php5 libapache2-mod-php5 

3. Instale el servidor de base de datos Firebird

	sudo add-apt-repository ppa:mapopa
	sudo apt-get update
	sudo apt-get install firebird2.5-superclassic firebird2.5-examples firebird2.5-dev
	
	Importante: Se le pedirá asignar un password al usuario SYSDBA, anótela cuidadosamente. p.e. supasswordSYSDBA
	
	* En el archivo firebird.conf comentar la linea RemoteBindAdress = localhost
		# RemoteBindAddress = localhost

4. Instale el soporte de Firebird sobre PHP.

	sudo apt-get install php5-interbase
	php5enmod interbase

5. Instalar la base de datos de BiblioTEK.

	mkdir /opt/datos
	cd /opt/datos
	wget http://www.htmex.mx/descargas/bibliotek_bd.bak
	gbak –R bibliotek_bd.bak biblio.fdb –USER SYSDBA –PASSWORD supasswordSYSDBA
	chown firebird.firebird /opt/datos/biblio.fdb 

6. Cree el directorio /www-bibliotek donde se instalará el código y la página de inicio de BiblioTEK.

	mkdir /www-bibliotek
	mkdir /www-bibliotek/images
	cd /www-bibliotek
	sudo apt-get install git-core  (omita esta instruccion si el comando git ya está instalado)	
	git clone https://github.com/htmexico/BiblioTek.git

7. Configure la página inicial de BiblioTEK.

	cd /www-bibliotek
	cp BiblioTek/APP_CONFIG.php .
	cp BiblioTek/GLOBAL_CONFIG.php .
	cp BiblioTek/index_subdomain.php index.php

	7.1. Editar APP_CONFIG.php y verificar los valores de su instalación, tales como IP y banner (el banner será necesario para su página web inicial).

		<?php
		unset( $ACCESS_CFG );
		
		$ACCESS_CFG = new stdClass();
		
		$ACCESS_CFG->id_biblioteca = 1;
		$ACCESS_CFG->codigo_cuenta = "mi_codigo";  // invente un codigo
		
		$ACCESS_CFG->banner = "images/banner_biblioteca.jpg";
		
		$ACCESS_CFG->http_public_dir = "http://104.236.194.50";  // colocar aquí su IP pública o el dominio o subdominio configurado 
		
		?>

	7.2. Editar GLOBAL_CONFIG.php y verificar los valores de su instalación
	
		<?php
		
		// si tiene un dominio funcionando coloquelo aqui (p.e. http://mibiblioteca.colegioxyz.edu/BiblioTek/)
		$www_dir = "http://104.236.194.50/BiblioTek/";
		
		$app_dir = "/www-bibliotek/BiblioTek/";
		
		?>

8. Configure la respuesta de apache web server al directorio /www-bibliotek.

	cd /etc/apache2/sites-available
	cp 000-default.conf bibliotek.conf
	a2dissite 000-default.conf
	
	8.1 Editar bibliotek.conf para configurar la respuesta http, con los siguientes valores:
	
		DocumentRoot /www-bibliotek/
		
		<Directory /www-bibliotek/>
		   Options -Indexes FollowSymLinks MultiViews
		   AllowOverride None
		   Require all granted
		</Directory>
	
	8.2. Active su archivo bibliotek.conf
	
		a2ensite bibliotek.conf
	
	
9. Verifique toda su instalación.

	service apache2 restart
	service firebird2.5-superclassic restart
	
	Ahora ingrese a través de un navegador y coloque su dirección IP, p.e. http://www.xxx.yyy.zzz, su respuesta debe ser del sistema BiblioTEK posiblemente con
	algunos errores.
	
10. Configure el acceso a la base de datos:

	Editar el archivo /www-bibliotek/BiblioTek/config_db.inc.php
	
	Asegurarse que los parametros db_name correspondan a su base de datos creada en el paso 5 y su password de Firebird anotado en el paso 3.
	
		$CFG->db_host    = 'localhost';
		$CFG->db_name    = '/opt/datos/biblio.fdb;
		$CFG->db_user    = 'SYSDBA';
		$CFG->db_pass    = 'supasswordSYSDBA';

	Ahora su servicio BiblioTEK ya deberá establecer una conexión correcta con la base datos.
	
11. Comprueba la funcionalidad, realizando cualquier ajuste necesario:

	Es posible que deba cerrar el navegador y volver a acceder a su sitio web BiblioTEK para que los cambios surtan efecto.
	Si modifica cualquier elemento a nivel de configuración de apache o php, deba reiniciar el servicio web, así: service apache2 restart.
	
12. Afinando la funcionalidad.

	12.1. Usted requerirá un gráfico de aproximadamente 800 pixeles de ancho por 80 pixeles de alto, que será su banner llámelo banner_biblioteca.jpg y súbalo a /www-bibliotek/images.
	
	12.2. BiblioTek enviará diversos mensajes por correo electrónico, configure una cuenta de salida editando el archivo /www-bibliotek/BiblioTek/email.inc.php, 
		  como a continuación se muestra:

		<?php
		unset($EMAIL);
		
		global $EMAIL;
		
		$EMAIL = new stdClass();
		
		// Usted debe proporcionar estos datos
		$EMAIL->smtp_host    = "mail.yourdomain.edu";
		$EMAIL->smtp_user    = "service@yourdomain.edu";
		$EMAIL->smtp_pass    = "";
		$EMAIL->smtp_port	 = 25;
		
		$EMAIL->image_background = "http://www.yourdomain.edu/images/logoFondoMail.jpg";
		$EMAIL->font_default_css = "font-family:verdana,arial,helvetica; font-size:12px; font-color: black; ";
		
		 ?>

	12.3. Los mensajes de correo electrónico son estilizados usando un gráfico jpg como fondo, Usted deberá diseñarlo y configurarlo en $EMAIL->image_background

	12.4. Para el cálculo correcto de fechas, asegúrese de que el archivo /etc/php5/apache2/php.ini contiene las siguientes líneas con estos valores:
	
		; Default timestamp format.
		ibase.timestampformat = "%Y-%m-%d %H:%M:%S"
		
		; Default date format.
		ibase.dateformat = "%Y-%m-%d"
		
		; Default time format.
		ibase.timeformat = "%H:%M:%S"

	