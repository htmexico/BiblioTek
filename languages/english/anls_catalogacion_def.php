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

  // ENGLISH
  $LBL_CATALOGACION     = "Cataloging";
  
  $LBL_CATALOG_HEADER_1 = "To start cataloging please select a template";
  $LBL_CATALOG_HEADER_2 = "Cataloging a title";	

  $LBL_TEMPLATE		  = "Template";
  
  $LBL_NUMBER_OF_CONTROL = "Record Number";
  $LBL_CREATED_BY	     = "Created by";  
  
  $ID_TITLE_TO_BE_ASIGNED = "[To be asigned]";
  
  $LBL_HEADER		  = "Header";
  $LBL_008_GENERAL    = "Generals";
  $LBL_008_SPECIFICS  = "Specifics";
  
  $LBL_TYPE_OF_RECORD = "Type of Material";
  $LBL_RECORD_STATUS    = "Record Status";
  $LBL_BIBL_LEVEL	    = "Bibliographical Level";
  $LBL_COD_LEVEL	    = "Codification Level";
  $LBL_FORM_OF_CATALOG  = "Catalogation Level";
  
  $LBL_DATE_TYPE_STATUS    = "Type of Date / Publishing Status";
  $LBL_PLACE_OF_PUBLISHING = "Place of Publishing";
  $LBL_LANGUAGE            = "Language";
  $LBL_RECORD_MODIFIED     = "Record Modified";
  $LBL_SOURCE_OF_CATALOG   = "Source of Cataloging";  

  $BTN_ADD_FIELD	  = "Add Field";
  $BTN_SAVE_CHANGES   = "Save Changes";
  $BTN_DELETE_FIELD	  = "Delete Field";
  $BTN_IMPORT_FROM_MARC = "Import MARC 2709";
  $BTN_CLOSE_WINDOW   = "Close Window";
  
  $SAVE_DONE = "Your information was modified.";
  
  $HINT_SUBFIELD_NOTFOUND = "Subfield doesn't exist";
  
  $LBL_IMPORT_HEADER = "Import a Title";
  $LBL_IMPORT_INDICATIONS	= "Choose the filename containing the full record in MARC/ISO2709 format";
  $BTN_IMPORT = "Import Record";  
  
  $MSG_WARNING_BEFORE_CLOSING_WITHOUT_SAVE = "The changes and movements you made will loose. We recomend you  Save before closing this window.";
  
  $MSG_WANT_TO_SAVE_CHANGES = "Are you sure you want to save your changes in the cataloguing database ?";
  $MSG_WANT_TO_CREATE_RECORD = "Are you ready to create this cataloguing record ?";
  
  $MSG_FIELD100_MANDATORY = "The field 100 is mandatory with information related to author.";
  $MSG_NO_FIELDS_AT_ALL   = "At least 1 MARC field is mandatory before you are able to create/modify the record.";  
  
 ?>