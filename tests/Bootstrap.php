<?php
define('BASE_PATH', dirname(dirname(realpath(__FILE__))));
define('LIBRARY_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'library');
set_include_path(implode(PATH_SEPARATOR, array(get_include_path(), LIBRARY_PATH)));