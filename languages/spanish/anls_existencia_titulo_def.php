<?php
  global $LBL_TO_BE_ASIGNED;
  global $LBL_CATALOGACION, $LBL_CREATE_EXIST_V1, $LBL_CREATE_EXIST_V2;

  global $LBL_ID_ITEM, $LBL_ITEM_LOCATION, $LBL_LOAN_CATEGORY, $LBL_ID_MATERIAL, $LBL_VOL_NUMBER, $LBL_PART_NUMBER, $LBL_SERIE_DATA_ID;
  global $LBL_SERIE_NUMESP, $LBL_SERIE_EPOCH, $LBL_SERIE_ANIO, $LBL_SERIE_MES, $LBL_SERIE_MAINTITLE, $LBL_SERIE_ALTTITLE, $LBL_SERIE_PAPEL_ELECTRONICO, $LBL_ITEMS_PAPER, $LBL_ITEMS_ELECTRONIC;
  global $LBL_DATE_OF_PUBLISH, $LBL_DATE_OF_RECEPTION, $LBL_COST_ADQUISICION, $LBL_ID_ACQUISICION;
  
  global $LBL_CALLNUMBER, $LBL_CALLNUMBER_PREFIX, $LBL_CALLNUMBER_CLASS, $LBL_CALLNUMBER_BOOK, $LBL_ITEM_STATUS, $LBL_ITEM_PHYSICAL_ST;
  
  global $LBL_MATERIAL_ADITIONAL;
  
  global $VALIDA_MSG_2, $VALIDA_MSG_3, $VALIDA_MSG_4;
  
  global $ERROR_UPDATING_SAVING;
  global $ERROR_ID_MATERIAL_ALREADY_IN_DB;

  global $SAVE_CREATED_DONE, $SAVE_DONE, $DELETE_DONE;
  
  global $COMMENTS_ACTIONS_ADD_COPY;
  global $COMMENTS_ACTIONS_EDITED_COPY;
  global $COMMENTS_ACTIONS_DELETE_ITEM;

  global $HINT_ITEM_LOCATION, $HINT_ITEM_CATEGORY, $HINT_ID_ITEM;
  global $HINT_VOL_NUMBER, $HINT_PART_NUMBER, $HINT_SERIE_DATA_ID, $HINT_MAIN_TITLE, $HINT_MAIN_ALTTITLE;
  global $HINT_DATE_PUBLISH, $HINT_DATE_RECEPTION, $HINT_COST_ACQUISICION, $HINT_ID_ACQUISICION;
  global $HINT_CALL_NUMBER;
  
  global $HINT_ITEM_STATUS, $HINT_PHYSICAL_ST, $HINT_MATERIAL_ADITIONAL;
  
  global $HINT_PROBLEM_ON_DELETE, $HINT_SHOW_PROBLEM, $LBL_PROBLEM_ON_DISCARDS, $LBL_PROBLEM_ON_LOANS, $LBL_PROBLEM_ON_RESERVAS, $ERROR_MSG_ON_DELETE_ITEM;  
  
  global $arrayMeses;
  
  // ESPA&ntilde;OL  
  $LBL_CATALOGACION     = "Catalogacion";
  
  $LBL_CREATE_EXIST_V1 = "Definir un nuevo ejemplar";
  $LBL_CREATE_EXIST_V2 = "Editar un ejemplar";	

  $LBL_TO_BE_ASIGNED  = "Por asignar";
  
  $LBL_ID_ITEM		  = "Identificador del Item";
  $LBL_ITEM_LOCATION  = "Ubicaci&oacute;n del Item";
  $LBL_LOAN_CATEGORY  = "Categor&iacute;a para Pr&eacute;stamo";
  $LBL_ID_MATERIAL	  = "ID del Material (N&uacute;mero de Inventario o de Control)";
  
  $LBL_VOL_NUMBER		  = "Volumen";
  $LBL_PART_NUMBER        = "N&uacute;mero de Ejemplar";
  
  $LBL_SERIE_NUMESP = "N&uacute;mero Especial";
  $LBL_SERIE_DATA_ID      = "Otros datos de Identificaci&oacute;n del Ejemplar";
  
  $LBL_SERIE_EPOCH  = "Epoca";
  $LBL_SERIE_ANIO   = "A&ntilde;o";
  $LBL_SERIE_MES    = "Mes";
  $LBL_SERIE_MAINTITLE = "T&iacute;tulo Principal";
  $LBL_SERIE_ALTTITLE = "T&iacute;tulo Secundario";
  $LBL_SERIE_PAPEL_ELECTRONICO = "Papel / Electr&oacute;nico";
  
  $LBL_ITEMS_PAPER = "Papel";
  $LBL_ITEMS_ELECTRONIC = "Electr&oacute;nico";
  
  $LBL_DATE_OF_PUBLISH  = "Fecha de Publicaci&oacute;n";
  $LBL_DATE_OF_RECEPTION  = "Fecha de Recepci&oacute;n";
  $LBL_COST_ADQUISICION   = "Precio de Adquisici&oacute;n";
  $LBL_ID_ACQUISICION     = "N&uacute;mero de Adquisici&oacute;n";
  
  $LBL_CALLNUMBER = "Signatura Topogr&aacute;fica";
  $LBL_CALLNUMBER_PREFIX = "Prefijo";
  $LBL_CALLNUMBER_CLASS  = "Clase";
  $LBL_CALLNUMBER_BOOK 	 = "Libr&iacute;stica";
  
  $LBL_ITEM_STATUS = "Status del Item";
  $LBL_ITEM_PHYSICAL_ST = "Estado F&iacute;sico";
  
  $LBL_MATERIAL_ADITIONAL = "Material Adicional";
  
  /* mensajes de validacion */
  $VALIDA_MSG_2 = "Es necesario colocar un identificador del material";
  $VALIDA_MSG_3 = "";
  $VALIDA_MSG_4 = "";
  
  $ERROR_UPDATING_SAVING = "Error al guardar/modificar datos";
  $ERROR_ID_MATERIAL_ALREADY_IN_DB = "ERROR: El Identificador del material ya existe en la base de datos";
  
  $SAVE_CREATED_DONE = "Se ha creado una existencia del ejemplar.";
  $SAVE_DONE		 = "Se ha modificado la existencia de un ejemplar.";
  $DELETE_DONE		 = "Se eliminaron uno(s) ejemplares(s).";
  
  $COMMENTS_ACTIONS_ADD_COPY    = "Se agreg&oacute; ejemplar";
  $COMMENTS_ACTIONS_EDITED_COPY = "Se modific&oacute; existencia";
  $COMMENTS_ACTIONS_DELETE_ITEM = "Se elimin&oacute; ejemplar";
  
  $HINT_ITEM_LOCATION  = "Elija la ubicaci&oacute;n del item";
  $HINT_ITEM_CATEGORY  = "Elija la categor&iacute;a del pr&eacute;stamo (tomada del Tesauro)";
  $HINT_ID_ITEM		   = "Identificador ÚNICO de cada Item (puede ser el n&uacute;mero de control, inventario o c&oacute;digo de barras)";
  
  $HINT_VOL_NUMBER       = "N&uacute;mero de volumen o a&ntilde;o";
  $HINT_PART_NUMBER      = "N&uacute;mero de pieza o parte o seriaci&oacute;n de un ejemplar";
  $HINT_SERIE_DATA_ID    = "Datos opcionales que identifican un n&uacute;mero de una publicaci&oacute;n peri&oacute;dica";
  $HINT_MAIN_TITLE       = "T&iacute;tulo de portada de la publicaci&oacute;n peri&oacute;dica";
  $HINT_MAIN_ALTTITLE    = "T&iacute;tulo alterno de portada";
  $HINT_DATE_PUBLISH     = "Fecha de publicaci&oacute;n del ejemplar";  

  $HINT_DATE_RECEPTION   = "Fecha de recepci&oacute;n del ejemplar";  
  $HINT_COST_ACQUISICION = "Indique el costo de adquisici&oacute;n o de portada";
  $HINT_ID_ACQUISICION   = "N&uacute;mero de adquisici&oacute;n de esta pieza";
  $HINT_CALL_NUMBER      = "Signatura Topogr&aacute;fica compuesta por un prefijo(opcional), clase (seg&uacute;n la clasificaci&oacute;n a utilizar) y N&uacute;mero de Libr&iacute;stica";
  
  $HINT_ITEM_STATUS    = "Status del ejemplar";
  $HINT_PHYSICAL_ST    = "Estado F&iacute;sico";
  $HINT_MATERIAL_ADITIONAL = "Indique el material que acompa&ntilde;a a la copia (p.e. CD o DVD)";
  
  $HINT_PROBLEM_ON_DELETE   = "Imposible eliminar alguno de los ejemplares marcados";
  $HINT_SHOW_PROBLEM        = "Se encontraron los siguientes problemas al intentar eliminar una(s) copia(s)";
  $LBL_PROBLEM_ON_DISCARDS  = "Informaci&oacute;n en descartes";
  $LBL_PROBLEM_ON_LOANS     = "Informaci&oacute;n en prestamos";
  $LBL_PROBLEM_ON_RESERVAS  = "Informaci&oacute;n en reservaciones";
  $ERROR_MSG_ON_DELETE_ITEM = "%s en ITEM %s";  // Problema X en el ITEM Y
  
  $arrayMeses = Array( "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre" );
  
 ?>