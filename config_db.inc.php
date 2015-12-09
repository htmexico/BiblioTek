<?php
unset($CFG);

global $CFG;

$CFG = new stdClass();

$CFG->db_type    = 'interbase';   // solo interbase
$CFG->db_host    = 'localhost'; 
$CFG->db_name    = '/opt/firebird_data/datos_biblio.fdb';
$CFG->db_user    = 'SYSDBA';
$CFG->db_pass    = '24691218';

 ?>
