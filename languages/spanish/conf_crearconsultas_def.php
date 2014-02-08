<?php
  global $LBL_HEADER_V1, $LBL_HEADER_V2;
  
  global $LBL_TO_BE_ASIGNED;
  
  global $LBL_IDQRY, $LBL_QRYNAME, $LBL_QRYCHOICES, $LBL_QRYPAGERECORDS;
  
  global $LBL_QRY_CHOICES;
  global $LBL_INCLUDE_KEYWORDS, $LBL_INCLUDE_TITLE, $LBL_INCLUDE_AUTHOR, $LBL_INCLUDE_SUBJECTS, $LBL_INCLUDE_CALLNUMBER;
  global $LBL_INCLUDE_ISBN, $LBL_INCLUDE_ISSN;
  
  global $LBL_RECS_X_PAGE, $HINT_PAGE_5, $HINT_PAGE_10, $HINT_PAGE_15, $HINT_PAGE_20;
  
  global $LBL_ATTACHMENTS, $LBL_SHOW_ATTACHMENTS, $LBL_DOWNLOAD_ATTACHMENTS;
  
  global $LBL_QUERY_TYPE, $LBL_IS_4_ADMINISTRATIVE, $LBL_IS_4_OPAC, $LBL_IS_4_READERS;  
  
  global $LBL_ALLOW_CARDVIEW, $LBL_ALLOW_VIEW_OF_COPIES, $LBL_ALLOW_VIEW_ITEMINFO;
  
  global $LBL_VIEW_STYLE, $LBL_ALLOW_VIEW_MARCSTYLE, $LBL_ALLOW_VIEW_AACR2STYLE;
  
  global $LBL_AGE_FILTERED, $LBL_ALLOW_MATERIAL_FILTERED, $LBL_ORDERED_BY;
  global $LBL_ORDER_BY_TITLE, $LBL_ORDER_BY_AUTHOR, $LBL_ORDER_BY_CALLNUMBER, $LBL_ORDER_BY_TITLEANDEDITION;
  
  global $LBL_IS_ACTIVE;
  
  global $VALIDA_MSG_NONAME;
  
  global $SAVE_EDIT_DONE, $SAVE_CREATED_DONE, $DELETE_DONE;
  
  global $MSG_ERROR_SAVING_CHANGES, $MSG_NO_PERSONS_MARKED_TO_DELETE;
  
  // Hints
  global $HINT_QUERYNAME, $HINT_RECS_X_PAGE, $HINT_QUERYTYPE;
  
  global $ACTION_DESCRIP_CREATE, $ACTION_DESCRIP_EDIT, $ACTION_DESCRIP_DELETE;

  // ESPA&ntilde;OL
  
  // PARA EDITAR 
  $LBL_HEADER_V1 = "Agregar una consulta";
  $LBL_HEADER_V2 = "Modificar una consulta";
  
  $LBL_TO_BE_ASIGNED = "[Por asignar]";
  
  $LBL_IDQRY 	        = "ID Consulta";
  $LBL_QRYNAME        = "Nombre de la Consulta";
  $LBL_QRYCHOICES     = "Opciones de Consulta";
  $LBL_QRYPAGERECORDS = "Registros por P&aacute;gina";
  
  $LBL_QRY_CHOICES 		   = "Opciones";
  $LBL_INCLUDE_KEYWORDS    = "Incluir palabras clave";  
  $LBL_INCLUDE_TITLE	   = "Incluir T&iacute;tulo";
  $LBL_INCLUDE_AUTHOR      = "Incluir Autor";
  $LBL_INCLUDE_SUBJECTS    = "Incluir Materias";
  $LBL_INCLUDE_CALLNUMBER  = "Incluir Signatura Topogr&aacute;fica";
  $LBL_INCLUDE_ISBN		   = "Incluir ISBN";
  $LBL_INCLUDE_ISSN		   = "Incluir ISSN";
  
  $LBL_RECS_X_PAGE	= "Registros por P&aacute;gina";
  
  $HINT_PAGE_5  = " 5 Registros";
  $HINT_PAGE_10 = "10 Registros";
  $HINT_PAGE_15 = "15 Registros";
  $HINT_PAGE_20 = "20 Registros";
  
  $LBL_ATTACHMENTS = "Contenido Digital";
  $LBL_SHOW_ATTACHMENTS = "Mostrar Contenido Digital Anexo";
  $LBL_DOWNLOAD_ATTACHMENTS = "Permitir descargas el Contenido Digital";
  
  $LBL_QUERY_TYPE = "Tipo de Consulta";
  $LBL_IS_4_ADMINISTRATIVE  = "Para Administrativos";
  $LBL_IS_4_OPAC  = "Consulta de Acceso P&uacute;blico (OPAC)";
  $LBL_IS_4_READERS = "Solo para usuarios de consulta";
  
  $LBL_ALLOW_CARDVIEW = "Permitir ver fichas de t&iacute;tulos";
  $LBL_ALLOW_VIEW_OF_COPIES = "Permitir ver copias existentes";
  $LBL_ALLOW_VIEW_ITEMINFO  = "Permitir ingresar a la informaci&oacute;n detallada de cada copia";
  
  $LBL_VIEW_STYLE = "Estilo de Visualizaci&oacute;n";
  $LBL_ALLOW_VIEW_MARCSTYLE  = "Permite ver fichas MARC";
  $LBL_ALLOW_VIEW_AACR2STYLE = "Permite ver fichas AACR2";

  $LBL_AGE_FILTERED  = "Mostrar resultados filtrados por rango de edad";
  $LBL_ALLOW_MATERIAL_FILTERED = "Mostrar filtro por tipo de material";

  $LBL_ORDERED_BY = "Consulta ordenada por";

  $LBL_ORDER_BY_TITLE	   = "Por T&iacute;tulo";
  $LBL_ORDER_BY_AUTHOR     = "Por Autor";  
  $LBL_ORDER_BY_CALLNUMBER = "Por Signatura Topogr&aacute;fica";
  $LBL_ORDER_BY_TITLEANDEDITION = "Por T&iacute;tulo y Edici&oacute;n";

  $LBL_IS_ACTIVE  = "Activa ?";

  $VALIDA_MSG_NONAME = "Se necesita contar con el dato de identificaci&oacute;n de la consulta";
  
  $SAVE_EDIT_DONE    = "Los datos de la consulta fueron modificados.";
  $SAVE_CREATED_DONE = "Los datos de la consulta fueron creados.";
  $DELETE_DONE       = "Se eliminaron las consultas.";
  
  $MSG_ERROR_SAVING_CHANGES       = "Error al guardar los cambios";
  $MSG_NO_PERSONS_MARKED_TO_DELETE = "Es necesario marcar uno o m&aacute;s consultas para poder borrarlas";
  
  // HINTS
  $HINT_QUERYNAME   = "Nombre de identificaci&oacute;n que mostrar&aacute; la consulta";
  $HINT_RECS_X_PAGE = "Registros por cada p&aacute;gina del resultado de la consulta";
  $HINT_QUERYTYPE   = "Tipo de consulta (P&uacute;blica, Administrativa o para Usuarios de Lectura)";
  
  $ACTION_DESCRIP_CREATE  = "Se cre&oacute; la definici&oacute;n de la consulta";
  $ACTION_DESCRIP_EDIT    = "Cambios a";
  $ACTION_DESCRIP_DELETE  = "Elimin&oacute; las siguientes consultas:";
  
 ?>