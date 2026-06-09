<?php
// Download and run composer installer
copy('https://getcomposer.org/installer', 'composer-setup.php');
exec('php composer-setup.php');
unlink('composer-setup.php');
echo "Done!";
?>