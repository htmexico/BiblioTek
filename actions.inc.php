<?php
/*******

	Historial de Cambios
	  
	29 mar 2009: Se crea el archivo.
	11 ago 2009: Se documentan cambios a las acciones de CIRCULACIÓN
	
 */

define( "DEF_ACTION_ADD", "AGREGAR/ADD ");
define( "DEF_ACTION_DEL", "QUITAR/REMOVE ");
define( "DEF_ACTION_EDIT", "EDITAR/EDIT ");

/* Acquisitions */ 
define( "ADQ_REQUISITIONS",  101 );
define( "ADQ_INCOMES", 	 	 104 );
define( "ADQ_CANCELATIONS",  110 ); 
define( "ADQ_DEVOLUTIONS",   112 );

define( "ADQ_INFORMES_ENTRADAS", 120 );

/* Analysis */
define( "ANLS_CATALOGING",   	201 );
define( "ANLS_EXISTENCES",		202 );
define( "ANLS_SUBJECTS_ASSIGN", 203 );

define( "ANLS_COVERS_ASSIGN",   204 );

define( "ANLS_DISCARDS", 206 );
define( "ANLS_DISCARDS_AUTH", 207 );

define( "ANLS_TITLES_SEARCHES", 210 ); // busquedas

define( "ANLS_VIEW_TITLE", 212 );

define( "ANLS_SERIES_CREATE", 215 );
define( "ANLS_SERIES_EDIT", 216 );
define( "ANLS_SERIES_DELETE", 217 );

define( "ANLS_SUSCRIPTS_CREATE", 220 );
define( "ANLS_SUSCRIPTS_EDIT", 221 );
define( "ANLS_SUSCRIPTS_DELETE", 222 );

define( "ANLS_PRINT_BAR_CODES", 240 );
define( "ANLS_PRINT_CATALOGUES", 250 );
define( "ANLS_PRINT_CATALOG_CARDS", 260 );
define( "ANLS_IMPORT_CATALOG", 290 );

/*** CIRCULACION ***/

define( "CIRC_LOANS", 301 );
define( "CIRC_AUTOLOANS", 303 );
define( "CIRC_DEVOLUTIONS", 306 );
define( "CIRC_RENEWALS", 310 );
define( "CIRC_RESERVATIONS", 312 );
define( "CIRC_TRACKING", 315 );

/* Servicios */

define( "SERV_MAINTENANCE_ALERTS", 401 );

define( "SERV_USERS_CREATE", 403 );
define( "SERV_USERS_EDIT", 404 );
define( "SERV_USERS_DELETE", 405 );

define( "SERV_USERS_GROUPS_CREATE", 407 );
define( "SERV_USERS_GROUPS_EDIT", 408 );
define( "SERV_USERS_GROUPS_DELETE", 409 );

define( "SERV_USERS_SANCTIONS", 421 );
define( "SERV_USERS_SANCTIONS_ACOMPLISHED", 423 );

define( "SERV_USERS_RESTRICTIONS_CREATE", 431 );
define( "SERV_USERS_RESTRICTIONS_CANCEL", 432 );

/***

	INSERT INTO cfgacciones VALUES ( 401, "Mantenimiento y Alertas" )

	5.	Estadísticas.
*/

/****

	Configuración.

*/

define( "CFG_CHANGE_LIBRARY_DATA", 601 );
define( "CFG_CHANGE_THESAURUS", 602 );
define( "CFG_CONFIG_TEMPLATES", 603 );

define( "CFG_RESOURCE_CREATE", 606 );
define( "CFG_RESOURCE_EDIT", 607 );
define( "CFG_RESOURCE_DELETE", 608 );

define( "CFG_CONFIG_PERSONS_CREATE", 611 );
define( "CFG_CONFIG_PERSONS_EDIT", 612 );
define( "CFG_CONFIG_PERSONS_DELETE", 613 );

define( "CFG_CONFIG_SANCTIONS_CREATE", 616 );
define( "CFG_CONFIG_SANCTIONS_EDIT", 617 );
define( "CFG_CONFIG_SANCTIONS_DELETE", 618 );

define( "CFG_CONFIG_RESTRICTIONS_CREATE", 620 );
define( "CFG_CONFIG_RESTRICTIONS_EDIT", 621 );
define( "CFG_CONFIG_RESTRICTIONS_DELETE", 622 );

define( "CFG_CHANGE_PARAMS_CATALOG", 624 );

define( "CFG_CHANGE_PARAMS_QUERIES_CREATE", 628 );
define( "CFG_CHANGE_PARAMS_QUERIES_EDIT",   629 );
define( "CFG_CHANGE_PARAMS_QUERIES_DELETE", 630 );

define( "CFG_LOG_USERS_ACTIVITY", 632 );

define( "CFG_CHANGE_LIBRARY", 641 );
define( "CFG_CHANGE_LANGUAGE", 643 );

define( "CFG_USER_LOGGED", 650 );
define( "CFG_USER_CHANGE_PASSWORD", 652 );

/** ACCIONES DE USUARIO 670 - 700 **/
define( "USER_ITEM_ADDED_TO_BIN", 672 );
define( "USER_ITEM_REMOVED_FROM_BIN", 673 );
define( "USER_ITEM_REMOVED_RESERVA", 674 );

?>
