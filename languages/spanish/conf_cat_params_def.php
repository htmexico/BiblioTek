<?php
  global $LBL_CFG_TITLE, $LBL_HEADER, $LBL_HEADER_2;
  
  global $LBL_PUNCTUATION_AUTO;
  global $LBL_MANDATORY_MARC100;
  
  global $HINT_PUNCTUATION_AUTO, $HINT_MANDATORY_MARC100, $HINT_EMAIL_DIRECTOR, $HINT_ADDRESS, $HINT_CITY, $HINT_STATE, $HINT_COUNTRY;
  global $HINT_PHONE, $HINT_SKIN, $HINT_LANGUAGE, $HINT_BANNER;

  global $BTN_CREATE_NEW_AUTHORITY, $BTN_DELETE_AUTHORITY;
  global $LBL_SUBTITLE_AUTHORITIES, $LBL_FIELD, $LBL_SUBFIELD, $LBL_CATHEGORY, $LBL_CONTROL_TYPE, $LBL_CONTROL_BY_CODE, $LBL_CONTROL_BY_TERM, $LBL_IS_STRICT;
  
  global $MSG_WANT_TO_SAVE, $MSG_WANT_TO_EDIT;
  global $ALERT_NO_FIELD_ENTERED, $ALERT_NO_SUBFIELD_ENTERED, $ALERT_FIELD_SUBFIELD_INCORRECT, $ALERT_FIELD_SUBFIELD_ALREADY_EXIST;
  
  global $MSG_AUTH_RECORD_SAVED, $MSG_AUTH_RECORD_EDITED, $MSG_AUTH_RECORDS_DELETED;
  global $SAVE_DONE;
  
  global $NOTES_AT_RIGHT;

  // ESPA&ntilde;OL
  
  $LBL_CFG_TITLE = "Configuraci&oacute;n de par&aacute;metros de catalogaci&oacute;n"; 
  $LBL_HEADER = "Reglas de Catalogaci&oacute;n";	
  $LBL_HEADER_2 = "Autoridades de Materia";
  
  $LBL_PUNCTUATION_AUTO   = "Mostrar Puntuaci&oacute;n Autom&aacute;tica en Fichas";
  $LBL_MANDATORY_MARC100  = "Forzar captura de campo MARC 100";  
  
  $HINT_PUNCTUATION_AUTO   = "Si desea que el sistema autom&aacute;ticamente coloque los signos de puntuaci&oacute;n correspondientes cuando los catalogadores los omitan.";
  $HINT_MANDATORY_MARC100  = "Si desea forzar a que el sistema solicite la informaci&oacute;n del autor en el campo MARC 100.";
  $HINT_EMAIL_DIRECTOR = "Indique el correo electr&oacute;nico del Director.";
  $HINT_ADDRESS	       = "Indica el domicilio de la biblioteca.";
  $HINT_CITY		   = "Indica la ciudad en donde se ubica la biblioteca.";
  $HINT_STATE		   = "Indica el estado donde se ubica la biblioteca.";
  $HINT_COUNTRY		   = "Indique el pa&iacute;s.";
  $HINT_PHONE		   = "Indique el tel&eacute;fono de la biblioteca.";
  $HINT_SKIN		   = "Indique el tema que Ud. prefiera para mostrar a los usuiarios de su biblioteca.";
  $HINT_LANGUAGE       = "Indique el idioma preferido.";
  $HINT_BANNER		   = "Si desea especificar un banner indique el nombre del archivo gr&aacute;fico.";
  
  $BTN_CREATE_NEW_AUTHORITY = "Crear nueva restricci&oacute;n";
  $BTN_DELETE_AUTHORITY = "Borrar restricciones";
  
  $LBL_SUBTITLE_AUTHORITIES = "Defina aqu&iacute; sus autoridades de materia";
  $LBL_FIELD = "Campo";
  $LBL_SUBFIELD = "Sub-Campo";
  $LBL_CATHEGORY = "Categor&iacute;a del Tesauro";
  $LBL_CONTROL_TYPE = "Tipo de Control";
  $LBL_IS_STRICT = "Estricto";
  
  $LBL_CONTROL_BY_CODE = "Por C&oacute;digo";
  $LBL_CONTROL_BY_TERM = "Por T&eacute;rmino";
  
  $MSG_WANT_TO_SAVE = "¿ Desea agregar el registro de Autoridad ?";
  $MSG_WANT_TO_EDIT = "¿ Desea guardar los cambios ?";
  
  $ALERT_NO_FIELD_ENTERED    = "No hay definici&oacute;n del campo";
  $ALERT_NO_SUBFIELD_ENTERED = "No hay definici&oacute;n del subcampo";
  
  $ALERT_FIELD_SUBFIELD_INCORRECT     = "El campo y/o subcampo que ha intentado registrar es incorrecto y no se encuentra en la tabla oficial MARC.";
  $ALERT_FIELD_SUBFIELD_ALREADY_EXIST = "El registro de autoridad para el campo y/o subcampo ya ha sido definido con anterioridad.";			

  $SAVE_DONE = "Sus datos fueron modificados.";
  
  $MSG_AUTH_RECORD_SAVED  = "Se agreg&oacute; un nuevo registro de Autoridad";
  $MSG_AUTH_RECORD_EDITED = "Se modific&oacute; correctamente el registro de Autoridad";
  $MSG_AUTH_RECORDS_DELETED = "Se eliminaron registros de Autoridad";
  
  $NOTES_AT_RIGHT = 
	"<strong>NOTA:</strong> En esta opci&oacute;n Ud. podr&aacute; establecer sus par&aacute;metros o reglas de catalogaci&oacute;n.<br><br>Las Autoridades de Materia le permitir&aacute;n organizar de forma m&aacute;s eficiente sus procesos de catalogaci&oacute;n." . 
	"Consisten en una liga de restricci&oacute;n entre el contenido de un SUBCAMPO MARC y una categor&iacute;a del Tesauro, de esta forma sus usuarios deber&aacute;n apegarse al contenido de ese tesauro y evitar colocar c&oacute;digos o t&eacute;rminos incorrectos.";
	;

?>