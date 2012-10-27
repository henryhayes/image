<?php
define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);
define('BASE_PATH', dirname(dirname(__FILE__)));
define('LIBRARY_PATH', BASE_PATH . DS . 'library');
define('IMAGES_PATH', BASE_PATH . DS .  'examples' . DS . 'images');
define('TEMP_PATH', BASE_PATH . DS .  'examples' . DS . 'temp');
set_include_path(get_include_path() . PS . LIBRARY_PATH);

require_once ('Image/Processor.php');