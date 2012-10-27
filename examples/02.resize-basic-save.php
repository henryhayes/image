<?php
require_once('bootstrap.php');

$imageName = IMAGES_PATH . '/sample-2.jpg';

$processor = new Image_Processor();

/**
 * Open the image
 */
$processor->open($imageName);

/**
 * Set the resize options
 */
$processor->setKeepAspectRatio(true); // Maintain aspect ratio?
$processor->setKeepFrame(false); // Make a box, and fit image inside?
$processor->setConstrainOnly(true); // (bool)true Stops image from resizing larger than original?
/**
 * Resize the image
 */
$processor->resize(600, 600);

/**
 * Send the image to output.
 */
$processor->save(TEMP_PATH, 'my_temp_image_file.jpg');
?>
<a href="temp/my_temp_image_file.jpg">temp/my_temp_image_file.jpg</a>