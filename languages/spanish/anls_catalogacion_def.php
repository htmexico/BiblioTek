<?php
  global $LBL_CATALOGACION, $LBL_CATALOG_HEADER_1, $LBL_CATALOG_HEADER_2;
  
  global $LBL_TEMPLATE, $LBL_NUMBER_OF_CONTROL, $LBL_CREATED_BY;
  
  global $ID_TITLE_TO_BE_ASIGNED;
  
  global $LBL_HEADER, $LBL_008_GENERAL, $LBL_008_SPECIFICS;
  
  global $LBL_TYPE_OF_RECORD, $LBL_RECORD_STATUS, $LBL_BIBL_LEVEL, $LBL_COD_LEVEL, $LBL_FORM_OF_CATALOG;
  
  global $LBL_DATE_TYPE_STATUS, $LBL_PLACE_OF_PUBLISHING, $LBL_LANGUAGE, $LBL_RECORD_MODIFIED, $LBL_SOURCE_OF_CATALOG;
  
  global $BTN_ADD_FIELD, $BTN_DELETE_FIELD, $BTN_SAVE_CHANGES, $BTN_IMPORT_FROM_MARC, $BTN_CLOSE_WINDOW;
  
  global $SAVE_DONE;
  
  global $HINT_SUBFIELD_NOTFOUND;
  
  global $LBL_IMPORT_HEADER, $LBL_IMPORT_INDICATIONS, $BTN_IMPORT;
  
  global $MSG_WARNING_BEFORE_CLOSING_WITHOUT_SAVE, $MSG_WANT_TO_SAVE_CHANGES, $MSG_WANT_TO_CREATE_RECORD;
  
  global $MSG_FIELD100_MANDATORY, $MSG_NO_FIELDS_AT_ALL;

  // ESPA&ntilde;OL
  $LBL_CATALOGACION     = "Catalogaci&oacute;n";
  
  $LBL_CATALOG_HEADER_1 = "Elija una plantilla";
  $LBL_CATALOG_HEADER_2 = "Catalogaci&oacute;n de un ejemplar";	

  $LBL_TEMPLATE		  = "Plantilla";
 
  $LBL_NUMBER_OF_CONTROL = "No. de Control";
  $LBL_CREATED_BY	     = "Creado por";
  
  $ID_TITLE_TO_BE_ASIGNED = "[Por asignar]";
 
  $LBL_HEADER		  = "Cabecera"; 
  $LBL_008_GENERAL	  = "Secci&oacute;n General";
  $LBL_008_SPECIFICS  = "Espec&iacute;ficos";
  
  $LBL_TYPE_OF_RECORD   = "Tipo de Registro";
  $LBL_RECORD_STATUS    = "Estado del Registro";
  $LBL_BIBL_LEVEL	    = "Nivel Bibliogr&aacute;fico";
  $LBL_COD_LEVEL	    = "Nivel de Codificaci&oacute;n";
  $LBL_FORM_OF_CATALOG  = "Forma de Catalogaci&oacute;n";  
  
  $LBL_DATE_TYPE_STATUS    = "Tipo de Fecha / Status de la Publicaci&oacute;n";
  $LBL_PLACE_OF_PUBLISHING = "Lugar de Publicaci&oacute;n";
  $LBL_LANGUAGE            = "Idioma";
  $LBL_RECORD_MODIFIED     = "Registro Modificado";
  $LBL_SOURCE_OF_CATALOG   = "Fuente de Catalogaci&oacute;n";    

  $BTN_ADD_FIELD	  = "Agregar Campo";
  $BTN_SAVE_CHANGES   = "Guardar Cambios";
  $BTN_DELETE_FIELD	  = "Quitar [Sub]Campo";
  $BTN_IMPORT_FROM_MARC = "Importar MARC 2709";
  $BTN_CLOSE_WINDOW   = "Cerrar Ventana";

  $SAVE_DONE = "Sus datos fueron modificados.";
  
  $HINT_SUBFIELD_NOTFOUND = "Subcampo NO existe";
  
  $LBL_IMPORT_HEADER = "Importar T&iacute;tulo";
  $LBL_IMPORT_INDICATIONS	= "Elija el nombre del archivo que contiene el registro completo MARC/ISO2709";
  $BTN_IMPORT = "Importar Registro";  
  
  $MSG_WARNING_BEFORE_CLOSING_WITHOUT_SAVE = "Los cambios y movimientos que realiz&oacute; se perdar&aacute;n. Le recomendamos Guardar antes de cerrar la ventana.";
  
  $MSG_WANT_TO_SAVE_CHANGES = "¿ Est&aacute; seguro de guardar sus cambios en el registro de catalogaci&oacute;n ?";
  $MSG_WANT_TO_CREATE_RECORD = "¿ Est&aacute; listo para crear el registro de catalogaci&oacute;n ?";
  
  $MSG_FIELD100_MANDATORY = "Es necesario el campo 100 con informaci&oacute;n relativa al autor.";
  $MSG_NO_FIELDS_AT_ALL   = "Es necesario al menos un campo MARC para guardar/modificar el registro.";
 ?>