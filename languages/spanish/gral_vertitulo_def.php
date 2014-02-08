<?php  
  global $LBL_CONSULT_HEADER, $LBL_DELETE_HEADER, $LBL_PRINT_CARD, $LBL_CARD_LIBRARYNAME;

  global $LBL_CARD_NO_CONTROL, $LBL_CARD_AUTHOR, $LBL_CARD_TITLE, $LBL_CARD_ADDED_TITLE, $LBL_CARD_PUBLISHING, $LBL_CARD_PUBLISHER_NUMBER, $LBL_CARD_EDITION;
  global $LBL_CARD_DESCRIPTION, $LBL_CARD_SERIES, $LBL_CARD_CALLNUMBER, $LBL_CARD_ADDED_ENTRY, $LBL_CARD_OTHER_TITLES;
  global $LBL_CARD_SUMMARY, $LBL_CARD_NOTES, $LBL_CARD_TARGET_AUDIENCE, $LBL_CARD_FILES, $LBL_CARD_OTHERNAME, $LBL_CARD_LANGUAGE, $LBL_CARD_THEMES;

  global $LBL_CARD_CLASIF_LC, $LBL_CARD_CLASIF_DEWEY;
  global $LBL_CARD_PERFORMER, $LBL_CARD_PRODUCTION_CREDITS;

  global $LBL_STATUS, $LBL_LOCATION;

  global $LBL_CURRENT_EXISTENCES, $LBL_NO_IMAGES, $LBL_NO_EXISTENCES;

  global $BTN_ADD_FRONTPAGE, $BTN_EDIT_FRONTPAGE, $BTN_VIEW_AS_MARC, $BTN_VIEW_AS_AACR2, $BTN_VIEW_CATALOGING, $BTN_VIEW_AS_LABELS;
  global $BTN_ADD_FILE, $HINT_NO_COMMENTS, $BTN_SAVE_COMMENTS, $MSG_SAVE_COMMENTS;
  
  global $LBL_COMMENTS_PER_USER, $LBL_COMMENTS, $LBL_COMMENTS_WRITE_REVIEW;
  
  /* usados en el template */
  global $LBL_COMMENTS_YOURNAME, $LBL_COMMENTS_EMAIL, $LBL_COMMENTS_SUMMARY, $LBL_COMMENTS_COMMENTS, $LBL_COMMENTS_RATE;
  global $LBL_USER_BY, $LBL_USER_RATE, $HINT_SEE_USER_COMMENTS;
  
  global $MSG_ERROR_NO_COMMENT, $MSG_ERROR_NO_SUMMARY, $MSG_ERROR_NO_RATED;  

  global $SAVE_DONE;
  
  global $MSG_DELETE, $MSG_ERROR_DELETING;
  
  // ESPA&ntilde;OL
  $LBL_CONSULT_HEADER 	= "Ver un T&iacute;tulo";
  $LBL_DELETE_HEADER   = "Eliminar un T&iacute;tulo";
  $LBL_PRINT_CARD     	= "Imprimir una ficha";

  $LBL_CARD_LIBRARYNAME = "Instituci&oacute;n";
  
  /* columnas titulos */
  $LBL_CARD_NO_CONTROL	     = "No. de Control";
  $LBL_CARD_AUTHOR  	 	 = "Autor";
  $LBL_CARD_TITLE  	     	 = "T&iacute;tulo";  
  $LBL_CARD_ADDED_TITLE  	 = "T&iacute;tulo Agregado";  
  $LBL_CARD_PUBLISHING   	 = "Publicaci&oacute;n";
  $LBL_CARD_PUBLISHER_NUMBER = "N&uacute;mero del Editor";
  $LBL_CARD_EDITION      	 = "Edici&oacute;n";

  $LBL_CARD_DESCRIPTION 	= "Descripci&oacute;n";
  $LBL_CARD_SERIES       	= "Serie";
  $LBL_CARD_CALLNUMBER   	= "Signatura Topogr&aacute;fica Local";
  $LBL_CARD_ADDED_ENTRY  	= "Asiento Secundario";
  
  $LBL_CARD_OTHER_TITLES 	= "Otros T&iacute;tulos:";
  
  $LBL_CARD_CLASIF_LC    	= "Clasif. LC:";
  $LBL_CARD_CLASIF_DEWEY 	= "Clasif. Dewey:";
  
  $LBL_CARD_SUMMARY      	= "Resumen";
  $LBL_CARD_NOTES        	= "Notas";
  $LBL_CARD_TARGET_AUDIENCE = "Nota de Audiencia";
  
  $LBL_CARD_OTHERNAME 		= "Otros Nombres Personales";
  $LBL_CARD_LANGUAGE  		= "Idioma";
  
  $LBL_CARD_PERFORMER 			= "Int&eacute;rprete";
  $LBL_CARD_PRODUCTION_CREDITS 	= "Cr&eacute;ditos de la Producci&oacute;n";
  
  $LBL_CARD_FILES				= "Archivos Digitales";
  
  $LBL_CARD_THEMES 				= "Temas";

  $LBL_CALL_NUMBER 		= "Signatura Topogr&aacute;fica";
  $LBL_STATUS			= "Status del Item";
  $LBL_LOCATION			= "Ubicaci&oacute;n";  
  
  $BTN_ADD_FRONTPAGE    = "Agregar Portada";
  $BTN_EDIT_FRONTPAGE   = "Editar Portada";
  $BTN_VIEW_AS_MARC     = "Ver como MARC";
  $BTN_VIEW_AS_AACR2    = "Ver como AACR2";
  $BTN_VIEW_CATALOGING  = "Ver Catalogaci&oacute;n";
  $BTN_VIEW_AS_LABELS   = "Ver Etiquetas";

  $LBL_CURRENT_EXISTENCES = "Ejemplares Existentes"; 
  
  $LBL_NO_IMAGES      = "Sin im&aacute;genes disponibles";
  $LBL_NO_EXISTENCES  = "SIN ejemplares EXISTENTES ";

  $BTN_ADD_FILE = "Subir archivos";
  
  $HINT_NO_COMMENTS = "No hay comentarios hasta el momento";
  
  $BTN_SAVE_COMMENTS = "Guardar comentarios";
  
  $MSG_SAVE_COMMENTS = "Ahora se guardar&aacute;n sus comentarios. ¿Est&aacute; seguro de continuar?";
  
  $LBL_COMMENTS_PER_USER = "Comentarios de un usuario";
  $LBL_COMMENTS = "Comentarios acerca de este material";
  $LBL_COMMENTS_WRITE_REVIEW = "Tienes una opini&oacute;n de este material? Escr&iacute;bela y deja que otros se enteren";
  
  $LBL_COMMENTS_YOURNAME = "Tu nombre";
  $LBL_COMMENTS_EMAIL	 = "Correo Electr&oacute;nico";
  $LBL_COMMENTS_COMMENTS = "Comentario";
  $LBL_COMMENTS_SUMMARY  = "Opini&oacute;n o Resumen";
  $LBL_COMMENTS_RATE     = "Calificaci&oacute;n";
  
  $LBL_USER_BY = "Por: ";
  $LBL_USER_RATE = "Calificaci&oacute;n Otorgada";
  
  $HINT_SEE_USER_COMMENTS = "Ver todos los comentarios de este usuario";
  
  $MSG_ERROR_NO_COMMENT = "Debes indicar un comentario";
  $MSG_ERROR_NO_SUMMARY = "Debes indicar brevemente tu opini&oacute;n o un resumen";
  $MSG_ERROR_NO_RATED   = "Por favor asigna una calificaci&oacute;n al material";
  
  $SAVE_DONE = "Sus datos fueron modificados.";
  
  $MSG_DELETE = "¿ En realidad desea eliminar este t&iacute;tulo ?";
  $MSG_ERROR_DELETING = "No se pudo eliminar el título. Se encontraron los siguientes problemas";
  
 ?>