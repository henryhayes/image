<?php
/**
 * PHP Image Manipulation & Processing Library
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 *
 * @category   Image
 * @package    Processor
 * @subpackage Adapter_Interface
 * @copyright  Copyright (c) 2012 PHP Image Manipulation & Processing Library
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @since      Thursday, 20 September 2012
 */
/**
 * Image processor adapter abstract class.
 *
 * @category   Image
 * @package    Processor
 * @subpackage Adapter_Interface
 * @copyright  Copyright (c) 2012 PHP Image Manipulation & Processing Library
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
interface Image_Processor_Adapter_Interface
{
    /**
     * Opens the file with the appropriate image type handler.
     *
     * @param string $fileName
     */
    public function open($fileName);

    /**
     * Saves the image to the detination specified with the newName specified.
     *
     * @param string $destination
     * @param string $newName
     */
    public function save($destination=null, $newName=null);

    /**
     * Outputs the image in it's current state to the screen, setting
     * the correct mime-type as a content-type header.
     */
    public function display();

    /**
     * Resizes the current image to the specified width and height.
     *
     * @param int $width
     * @param int $height
     */
    public function resize($width=null, $height=null);

    /**
     * Rotates the image by the specified angle.
     *
     * @param int $angle
     */
    public function rotate($angle);

    /**
     * Crops the image by the specified parameters.
     *
     * @param int $top
     * @param int $left
     * @param int $right
     * @param int $bottom
     */
    public function crop($top=0, $left=0, $right=0, $bottom=0);

    /**
     * Sets the watermark image specified at the location specified.
     *
     * @param string $watermarkImage
     * @param int $positionX
     * @param int $positionY
     * @param int $watermarkImageOpacity
     * @param bool $repeat
     */
    public function watermark($watermarkImage, $positionX=0, $positionY=0,
        $watermarkImageOpacity=30, $repeat=false);

    /**
     * Checks the dependencies for the adapter.
     *
     * @throws Exception
     */
    public function checkDependencies();
}