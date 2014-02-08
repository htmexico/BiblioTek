<?php
/*******
 Historial de Cambios

 01 sep 2009: Se crea el archivo.
 04 sep 2009: Se agregan privilegios de ANÁLISIS
 28 ene 2010: Se agrega privilegio de CONTENIDOS
 10-dic-2010: Se agrega PRIV_CATALOGING_DELETE
 */

/** PRIVILEGIOS **/
define( "PRIV_CATALOGING", 210 );
define( "PRIV_CATALOGING_DELETE", 211 );
define( "PRIV_ADD_COVERS", 212 );
define( "PRIV_ADD_DIGITAL_FILES", 213 );

define( "PRIV_EXISTENCES", 215 );

define( "PRIV_DISCARDS", 230 );
define( "PRIV_DISCARDS_AUTH", 232 );

define( "PRIV_SERIES", 235 );  			 // 15sep2009
define( "PRIV_SERIES_RECEPTION", 237 );  // 15sep2009

define( "PRIV_CATALOGUE_SEARCH", 240 );
define( "PRIV_VIEW_TITLES_INFO", 242 );

define( "PRIV_PRINT_BARCODES", 250 );
define( "PRIV_PRINT_CATALOGS", 260 );
define( "PRIV_PRINT_CATALOG_CARDS", 270 );

define( "PRIV_LOANS", 310 ); 
define( "PRIV_AUTOLOANS", 320 ); 
define( "PRIV_DEVOLUTIONS", 330 ); 
define( "PRIV_RENEWALS", 340 );
define( "PRIV_RESERVAS", 350 );
define( "PRIV_TRACKING", 360 );

/* estadisticas */
define( "PRIV_EST_GENERAL", 405 ); // ???
define( "PRIV_EST_TITLES_MOST_VIEWED", 409 ); // ???
define( "PRIV_EST_LOANS_ISSUED", 413 ); // ???
define( "PRIV_EST_LOANS_ON_DUE", 415 ); // ???
define( "PRIV_EST_CIRCULATION", 420 ); 
define( "PRIV_EST_SANCTIONS", 430 ); 
define( "PRIV_EST_OPAC", 433 ); // ???
define( "PRIV_EST_CATALOGING", 440 ); // ???

define( "PRIV_USERS", 510 );

define( "PRIV_SANCTIONS", 530 );
define( "PRIV_SANCTIONS_ACOMPLISHED", 540 );
define( "PRIV_RESTRICTIONS", 550 );

define( "PRIV_CONFIGINFOLIBRARY", 900 );
define( "PRIV_CONTENTS", 902 );  		  // 28-ene-2010
define( "PRIV_THESAURUS", 910 );
define( "PRIV_TEMPLATES", 920 );
define( "PRIV_PERSONS", 922 );

define( "PRIV_CFG_SANCTIONS", 930 );
define( "PRIV_CFG_RESTRICTIONS", 935 );

define( "PRIV_CFG_CATALOGING_RULES", 940 );
define( "PRIV_CFG_QUERIES", 950 );
define( "PRIV_CFG_EMAIL_ALERTS", 955 );

define( "PRIV_USERGROUPS", 960 );
define( "PRIV_USERLOG", 970 );
define( "PRIV_CHANGELIBRARY", 980 );
define( "PRIV_CHANGELANGUAGE", 985 );

?>