<?php
  global $CONNECTED_AS, $LBL_LOGOUT;
  global $MENU_OPT_1, $MENU_OPT_3, $MENU_OPT_5, $MENU_OPT_7, $MENU_OPT_9, $MENU_OPT_11, $MENU_OPT_13, $MENU_OPT_15;
  
  global $MENU_3_ITEM1, $MENU_3_ITEM3, $MENU_3_ITEM5, $MENU_3_ITEM7;
  
  global $ANLSMENU_ITEM1, $ANLSMENU_ITEM2, $ANLSMENU_ITEM3, $ANLSMENU_ITEM4, $ANLSMENU_ITEM5, $ANLSMENU_ITEM6, $ANLSMENU_ITEM7, $ANLSMENU_ITEM11, $ANLSMENU_ITEM13, $ANLSMENU_ITEM15;
  global $CIRMENU_ITEM1, $CIRMENU_ITEM3, $CIRMENU_ITEM5, $CIRMENU_ITEM6, $CIRMENU_ITEM7, $CIRMENU_ITEM9, $CIRMENU_ITEM11;
  global $SRVMENU_ITEM1, $SRVMENU_ITEM3, $SRVMENU_ITEM7, $SRVMENU_ITEM8, $SRVMENU_ITEM9;
  global $INF_GRAL_STATISTICS, $INF_DAILY_STATISTICS, $INF_MOST_VIEWED_TITLES, $INF_LOANS, $INF_LOANS_ON_DUE, $INF_CIRCULATION_REPTS, $INF_SANCTIONS, $INF_OPAC, $INF_STATISTICS_CATALOG;  
  global $CFGMENU_ITEM1, $CFGMENU_ITEM2, $CFGMENU_ITEM3, $CFGMENU_ITEM4, $CFGMENU_ITEM6, $CFGMENU_ITEM7, $CFGMENU_ITEM8, $CFGMENU_ITEM9, $CFGMENU_ITEM11, $CFGMENU_ITEM12, $CFGMENU_ITEM13, $CFGMENU_ITEM15, $CFGMENU_ITEM17;
  global $CFGMENU_CONTENTS;
  
  global $BTN_START, $BTN_SAVE, $BTN_CREATENEW, $BTN_CANCEL, $BTN_GOBACK, $BTN_EXIT, $BTN_CLOSEWIN, $BTN_CONTINUE, $BTN_SELECT, $BTN_APPLY, $BTN_PRINT;
  global $TITLE_CONFIRMATION_NEEDED, $TITLE_NOTIFICATION_SENT, $MSG_CONFIRMATION_ON_ZERO, $MSG_CONFIRMATION_ON_MANY, $MSG_CONFIRMATION_NEEDED, $CHK_CONFIRMATION_BOX;
  
  global $MSG_PROCESSING_SOMETHING;
  
  global $LBL_STATUS_AVAILABLE, $LBL_STATUS_AVAILABLE_ONLY_INTERNAL, $LBL_STATUS_BORROWED, $LBL_STATUS_BLOCKED, $LBL_STATUS_RESERVED, $LBL_STATUS_DISABLED, $LBL_STATUS_MISSING;
  
  global $LBL_YES, $LBL_NO, $LBL_ACTIVE, $LBL_INACTIVE;

  global $NOTES_COMMENTS, $CONTACT_US;
  global $LINK_MY_FILES, $LINK_USER_ACTIVITY, $LINK_USER_RESERVAS, $LINK_USER_RENEWALS;
  global $LINK_USER_REMOVE_ITEMS_FROM_BIN, $LINK_USER_REMOVE_RESERVA;
  
  global $HINT_ITEMS_IN_LOAN_NOW, $HINT_ITEMS_RESERVED, $HINT_USER_SANCTIONS, $HINT_USER_RESTRICTIONS;

  global $MSG_NO_LOG_OF_RECENT_ACTIVITIES, $MSG_NO_LOG_OF_FREQUENT_THEMES, $MSG_NO_LOG_OF_RECENT_ISSUED_ITEMS, $MSG_NO_LOG_OF_CONTRIBUTIONS, $MSG_NO_RECORDS_FOUND, $MSG_NO_IMAGES_AVAIL;
  
  global $HINT_PLEASE_LEAVE_COMMENT;

  global $MSG_NO_RIGHTS_TITLE, $MSG_NO_RIGHTS_DETAILS;

  global $arrayMeses;

  // ESPA&ntilde;OL
  
  $CONNECTED_AS = "Conectado como";
  $LBL_LOGOUT = "Salir";
  
  $MENU_OPT_1 = "Principal";
  $MENU_OPT_3 = "Adquisiciones";
  $MENU_OPT_5 = "An&aacute;lisis";
  $MENU_OPT_7 = "Circulaci&oacute;n";
  $MENU_OPT_9 = "Servicios";
  $MENU_OPT_11 = "Informes";
  $MENU_OPT_13 = "Configuraci&oacute;n";
  $MENU_OPT_15 = "Ayuda";
  
  $MENU_3_ITEM1 = "Solicitudes de Material";
  $MENU_3_ITEM3 = "Entradas / Adquisiciones";
  $MENU_3_ITEM5 = "Cancelaciones y Devoluciones";
  $MENU_3_ITEM7 = "Informe de Adquisiciones";
  
  $ANLSMENU_ITEM1  = "Catalogaci&oacute;n";
  $ANLSMENU_ITEM2  = "Tematizaci&oacute;n";
  $ANLSMENU_ITEM3  = "Existencias / Inventario";
  $ANLSMENU_ITEM4  = "Descartes / Expurgo";
  $ANLSMENU_ITEM5  = "Publicaciones Peri&oacute;dicas";
  $ANLSMENU_ITEM6  = "Recepci&oacute;n de Publicaciones Peri&oacute;dicas";
  $ANLSMENU_ITEM7  = "Consulta al Cat&aacute;logo de T&iacute;tulos";
  $ANLSMENU_ITEM11 = "Impresi&oacute;n de C&oacute;digo de Barras";
  $ANLSMENU_ITEM13 = "Impresi&oacute;n de Cat&aacute;logos";  
  $ANLSMENU_ITEM15 = "Impresi&oacute;n de Fichas Catalogr&aacute;ficas";
  
  $CIRMENU_ITEM1 = "Pr&eacute;stamos";
  $CIRMENU_ITEM3 = "Auto Pr&eacute;stamos";
  $CIRMENU_ITEM5 = "Devoluciones";
  $CIRMENU_ITEM6 = "Devoluciones R&aacute;pidas";
  $CIRMENU_ITEM7 = "Renovaciones";
  $CIRMENU_ITEM9 = "Reservaciones";
  $CIRMENU_ITEM11 = "Rastreo";

  $SRVMENU_ITEM1 = "Mantenimiento y Alertas";
  $SRVMENU_ITEM3 = "Cuentas de Usuarios";  
  $SRVMENU_ITEM7 = "Registro de Sanciones";
  $SRVMENU_ITEM8 = "Cumplimiento de Sanciones";
  $SRVMENU_ITEM9 = "Restricciones";
  
  $INF_GRAL_STATISTICS 		= "Estad&iacute;stica General por fechas";
  $INF_DAILY_STATISTICS 	= "Estad&iacute;stica por d&iacute;a (Eliminada)";
  $INF_MOST_VIEWED_TITLES 	= "T&iacute;tulos m&aacute;s consultados";
  $INF_LOANS 				= "Estad&iacute;sticas de Pr&eacute;stamos";
  $INF_LOANS_ON_DUE 		= "Pr&eacute;stamos Vencidos";
  $INF_CIRCULATION_REPTS	= "Reportes de Circulaci&oacute;n";
  $INF_SANCTIONS 			= "Informe de Sanciones y Restricciones";
  $INF_OPAC 				= "Informe de Consultas en L&iacute;nea (OPAC)";
  $INF_STATISTICS_CATALOG	= "Estad&iacute;sticas de Catalogaci&oacute;n";
  
  $CFGMENU_ITEM1 = "Datos de la Entidad Usuaria";
  $CFGMENU_ITEM2 = "Tesauro";
  $CFGMENU_ITEM3 = "Plantillas para Captura";
  $CFGMENU_ITEM4 = "Personas o Instituciones";  
  
  $CFGMENU_ITEM6 = "Catalogo de Sanciones";     // 12-oct-2009
  $CFGMENU_ITEM7 = "Catalogo de Restricciones"; // 12-oct-2009
  
  $CFGMENU_ITEM8 = "Reglas de Catalogaci&oacute;n / Autoridades";  
  $CFGMENU_ITEM9 = "Reglas para Consultas de T&iacute;tulos";   
  $CFGMENU_ITEM11 = "Grupos de Usuarios / Reglas de Circulaci&oacute;n";
  $CFGMENU_ITEM12 = "Plantillas de Alertas por Correo Electr&oacute;nico";
  $CFGMENU_ITEM13 = "Registro de Actividades de Usuarios";  
  $CFGMENU_ITEM15 = "Cambiar de Biblioteca";
  $CFGMENU_ITEM17 = "Cambiar Idioma";
  
  $CFGMENU_CONTENTS = "Administraci&oacute;n de Contenidos";
  
  $BTN_START	 = "Iniciar";
  $BTN_SAVE      = "Guardar Cambios";
  $BTN_CREATENEW = "Crear Nuevo";
  $BTN_CANCEL    = "Cancelar";
  $BTN_GOBACK    = "Regresar";
  $BTN_EXIT      = "Salir";
  $BTN_CLOSEWIN  = "Cerrar esta ventana";
  $BTN_CONTINUE  = "Continuar";
  $BTN_SELECT    = "Seleccionar";
  $BTN_APPLY     = "Aplicar";
  $BTN_PRINT	 = "Imprimir";
  
  $TITLE_CONFIRMATION_NEEDED = "Se requiere confirmaci&oacute;n";
  $TITLE_NOTIFICATION_SENT = "Atenci&oacute;n !! ";
  
  $MSG_CONFIRMATION_ON_ZERO  = "Por favor confirme que desea eliminar este(a) ";
  $MSG_CONFIRMATION_ON_MANY  = "Al confirmar esta acci&oacute;n se eliminar&aacute;n %s registro(s) de un(a) %s.";
  $MSG_CONFIRMATION_NEEDED   = "Es necesario marcar la casilla como confirmada";
  $CHK_CONFIRMATION_BOX 	 = "Confirmada";
  
  $MSG_PROCESSING_SOMETHING = "Espere un momento por favor...";
  
  // ESTADO DEL MATERIAL
  $LBL_STATUS_AVAILABLE 			  = "Disponible";
  $LBL_STATUS_AVAILABLE_ONLY_INTERNAL = "Disponible (Uso Interno)";
  $LBL_STATUS_BORROWED 	= "Prestado";
  $LBL_STATUS_BLOCKED	= "Bloqueado";
  $LBL_STATUS_RESERVED	= "Reservado";
  $LBL_STATUS_DISABLED	= "Baja (descartado)";
  $LBL_STATUS_MISSING   = "Faltante";
  
  $LBL_YES = "Si";
  $LBL_NO = "No";
  
  $LBL_ACTIVE   = "Activo";
  $LBL_INACTIVE = "Inactivo";
  
  $NOTES_COMMENTS 	   = "Informes y Comentarios ?";
  $CONTACT_US	       = "Cont&aacute;ctenos";
  
  $LINK_MY_FILES 	   = "Mis archivos...";
  $LINK_USER_ACTIVITY  = "Historial de Mis actividades";
  $LINK_USER_RESERVAS  = "Realizar una Reservaci&oacute;n";
  $LINK_USER_RENEWALS  = "Renovar un pr&eacute;stamo";
  
  $LINK_USER_REMOVE_ITEMS_FROM_BIN = "Quitar este t&iacute;tulo de mi bandeja personal";
  $LINK_USER_REMOVE_RESERVA = "Quitar este t&iacute;tulo de mis reservaciones";
  
  $HINT_ITEMS_IN_LOAN_NOW = "tiene %s items prestados.";
  $HINT_ITEMS_RESERVED = "%s items reservados.";
  $HINT_USER_SANCTIONS = "%s sanciones sin cumplir.";
  $HINT_USER_RESTRICTIONS = "%s restricciones vigentes.";
  
  // INTERFACE DE USUARIO
  $MSG_NO_LOG_OF_RECENT_ACTIVITIES = "No hay registro de actividades recientes relevantes"; 
  $MSG_NO_LOG_OF_FREQUENT_THEMES = "No hay registro de temas consultados";  
  $MSG_NO_LOG_OF_RECENT_ISSUED_ITEMS = "No hay registro de t&iacute;tulos recientemente usados";  
  $MSG_NO_LOG_OF_CONTRIBUTIONS = "No hay registro de contribuciones";
  
  $MSG_NO_RECORDS_FOUND = "No se encontraron resultados para esta consulta o b&uacute;squeda.";
  
  $HINT_PLEASE_LEAVE_COMMENT = "Por favor d&eacute;jenos un comentario";
  
  $MSG_NO_IMAGES_AVAIL = "No hay im&aacute;genes disponibles";
  
  // PRIVILEGIOS
  $MSG_NO_RIGHTS_TITLE = "Usted no tiene permiso para realizar esta operaci&oacute;n.";
  $MSG_NO_RIGHTS_DETAILS = "El usuario <strong>%s</strong> no tiene permisos para efectuar esta operaci&oacute;n (%s).";
  
  $arrayMeses = Array( "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre" );
  
  global $LBL_RECS_X_PAGE;
  $LBL_RECS_X_PAGE = "Mostrar:";
  
  global $HINT_CHANGES_APPLIED_HERE, $HINT_CHANGES_ALERT;
  $HINT_CHANGES_ALERT = "Aviso de Cambios";
  $HINT_CHANGES_APPLIED_HERE = "Para su conveniencia se aplicaron cambios recientemente aquí.";
  
 ?>