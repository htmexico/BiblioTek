<?php

if(extension_loaded('mysql')) {
echo 'mysql is loaded';
} else {
echo 'mysql is not loaded!';
}
  echo phpversion();
  
  echo "<br>";
  
  echo phpinfo();

  ?>
