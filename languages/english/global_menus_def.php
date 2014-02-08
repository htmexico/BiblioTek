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
  
  // ENGLISH
  
  $CONNECTED_AS = "Logged as";
  $LBL_LOGOUT = "Logout";
  
  $MENU_OPT_1 = "Main";
  $MENU_OPT_3 = "Acquisitions";
  $MENU_OPT_5 = "Analysis";
  $MENU_OPT_7 = "Circulation";
  $MENU_OPT_9 = "Services";
  $MENU_OPT_11 = "Statistics";
  $MENU_OPT_13 = "Configuration";
  $MENU_OPT_15 = "BiblioTEK";
  
  $MENU_3_ITEM1 = "Material requisitions";
  $MENU_3_ITEM3 = "Entrance / Adcquisitions";
  $MENU_3_ITEM5 = "Cancellations and Devolutions";
  $MENU_3_ITEM7 = "Informs of adcquisitions";
  
  $ANLSMENU_ITEM1 = "Cataloguing";
  $ANLSMENU_ITEM2 = "Assign Subjects";
  $ANLSMENU_ITEM3 = "Existences / Inventory";
  $ANLSMENU_ITEM4 = "Discard copies";
  $ANLSMENU_ITEM5  = "Series ";
  $ANLSMENU_ITEM6  = "Handling of Series";
  $ANLSMENU_ITEM7  = "Search Catalog";  
  $ANLSMENU_ITEM11 = "Print Bar Codes";
  $ANLSMENU_ITEM13 = "Print Catalogs";
  $ANLSMENU_ITEM15 = "Print Catalogue Cards";
  
  $CIRMENU_ITEM1 = "Check-Out";
  $CIRMENU_ITEM3 = "Self Check-Out";
  $CIRMENU_ITEM5 = "Returns";
  $CIRMENU_ITEM6 = "Quick Returns";
  $CIRMENU_ITEM7 = "Renewals";
  $CIRMENU_ITEM9 = "Reservations";
  $CIRMENU_ITEM11 = "Tracking";  
  
  $SRVMENU_ITEM1 = "Maintenance and Alerts";
  $SRVMENU_ITEM3 = "Users";
  $SRVMENU_ITEM7 = "Fines / Sanctions";
  $SRVMENU_ITEM8 = "Sanctions Acumplished";
  $SRVMENU_ITEM9 = "Restrictions";  
  
  $INF_GRAL_STATISTICS 		= "General Statistics by date";
  $INF_DAILY_STATISTICS 	= "Statistics by day (Removed)";
  $INF_MOST_VIEWED_TITLES 	= "Most queried/searched titles";
  $INF_LOANS 				= "Checkout Statistics";
  $INF_LOANS_ON_DUE 		= "Checkout/Loans on due";
  $INF_CIRCULATION_REPTS	= "Circulation Reports";
  $INF_SANCTIONS 			= "Informs of Sanctions & Restrictions";
  $INF_OPAC 				= "Informs of Open Public Consults/Searches (OPAC)";
  $INF_STATISTICS_CATALOG	= "Cataloguing Statistics";  
  
  $CFGMENU_ITEM1 = "Library Settings";
  $CFGMENU_ITEM2 = "Thesaurus";
  $CFGMENU_ITEM3 = "Templates for data entering";
  $CFGMENU_ITEM4 = "Persons or Institutions";
  
  $CFGMENU_ITEM6 = "Sanctions Catalog";     // 12-oct-2009
  $CFGMENU_ITEM7 = "Restrictions Catalog"; // 12-oct-2009  
  
  $CFGMENU_ITEM8 = "Cataloguing Rules / Authorities";  
  $CFGMENU_ITEM9 = "Search Rules for Material";   
  $CFGMENU_ITEM11 = "User Groups / Circulation Rules";
  $CFGMENU_ITEM12 = "Templates for email messaging";
  $CFGMENU_ITEM13 = "User's Activities Log";  
  $CFGMENU_ITEM15 = "Library Switch";
  $CFGMENU_ITEM17 = "Choose Language";
  
  $CFGMENU_CONTENTS = "Content management";  
    
  $BTN_START	 = "Start";
  $BTN_SAVE      = "Save Changes";
  $BTN_CREATENEW = "Create New";
  $BTN_CANCEL    = "Cancel";
  $BTN_GOBACK    = "Go Back";
  $BTN_EXIT      = "Exit";  
  $BTN_CLOSEWIN  = "Close this window";
  $BTN_CONTINUE  = "Continue";
  $BTN_SELECT    = "Select";
  $BTN_APPLY     = "Apply";
  $BTN_PRINT	 = "Print";
  
  $TITLE_CONFIRMATION_NEEDED = "Your conformation is necessary";
  $TITLE_NOTIFICATION_SENT = "Your atention please !! ";
  
  $MSG_CONFIRMATION_ON_ZERO = "Por favor confirme que desea eliminar este(a) ";
  $MSG_CONFIRMATION_ON_MANY = "If you continue this action %s record(s) will be eliminated in a %s.";
  $MSG_CONFIRMATION_NEEDED = "Es necesario marcar la casilla como confirmada";
  $CHK_CONFIRMATION_BOX = "Confirmed";
  
  $MSG_PROCESSING_SOMETHING = "Wait a moment please...";
  
  // MATERIAL STATUS
  $LBL_STATUS_AVAILABLE = "Available";
  $LBL_STATUS_AVAILABLE_ONLY_INTERNAL = "Available (Intenal Use only)";
  $LBL_STATUS_BORROWED 	= "Borrowed";
  $LBL_STATUS_BLOCKED	= "Blocked";
  $LBL_STATUS_RESERVED	= "Reserved";
  $LBL_STATUS_DISABLED	= "Not available (Discarded)";  
  $LBL_STATUS_MISSING   = "Missing";
  
  $LBL_YES = "Yes";
  $LBL_NO = "No";  
  
  $LBL_ACTIVE   = "Active";
  $LBL_INACTIVE = "Inactive";  
  
  $NOTES_COMMENTS	   = "Do you need more Information ?";
  $CONTACT_US	       = "Contact us";
  
  $LINK_MY_FILES 	   = "My files...";
  $LINK_USER_ACTIVITY  = "Log of My Activities";
  $LINK_USER_RESERVAS  = "Make a reservation";
  $LINK_USER_RENEWALS  = "Renew a loan";
  
  $LINK_USER_REMOVE_ITEMS_FROM_BIN = "Remove this title from my personal bin";
  $LINK_USER_REMOVE_RESERVA = "Remove this title from my reservations list";  
  
  $HINT_ITEMS_IN_LOAN_NOW = "have %s items borrowed.";
  $HINT_ITEMS_RESERVED = "%s items reserved.";
  $HINT_USER_SANCTIONS = "%s sanction unacumplished.";
  $HINT_USER_RESTRICTIONS = "%s current restrictions.";  
  
  // USER INTERFACE
  $MSG_NO_LOG_OF_RECENT_ACTIVITIES = "No log of relevant recent activities";
  $MSG_NO_LOG_OF_FREQUENT_THEMES = "No log of themes searched"; 
  $MSG_NO_LOG_OF_RECENT_ISSUED_ITEMS = "No log of themes recently used";  
  $MSG_NO_LOG_OF_CONTRIBUTIONS = "No contributions found";
  
  $MSG_NO_RECORDS_FOUND = "No results were found for this consult or search.";  
  
  $HINT_PLEASE_LEAVE_COMMENT = "Please leave us a comment";
  
  $MSG_NO_IMAGES_AVAIL = "No images available";  
  
  // PRIVILEGIOS
  $MSG_NO_RIGHTS_TITLE = "You don't have the rights or permissions needed for this operation.";
  $MSG_NO_RIGHTS_DETAILS = "The user <strong>%s</strong> doesn't have permissions for this operation (%s).";  
  
  $arrayMeses = Array( "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" );
  
  global $LBL_RECS_X_PAGE;
  $LBL_RECS_X_PAGE = "Show:";  
  
  global $HINT_CHANGES_APPLIED_HERE, $HINT_CHANGES_ALERT;
  $HINT_CHANGES_ALERT = "Upgrade Alert";
  $HINT_CHANGES_APPLIED_HERE = "For your convenience some changes were recently applied here.";  
  
 ?>