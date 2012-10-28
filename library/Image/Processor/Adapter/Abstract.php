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
 * @subpackage Adapter_Abstract
 * @copyright  Copyright (c) 2012 PHP Image Manipulation & Processing Library
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @since      Thursday, 20 September 2012
 */
/**
 * @see Image_Processor_Adapter_Interface
 */
require_once('Image/Processor/Adapter/Interface.php');
/**
 * @see Image_Processor_Adapter_Exception
 */
require_once('Image/Processor/Adapter/Exception.php');
/**
 * Image processor adapter abstract class.
 *
 * @category   Image
 * @package    Processor
 * @subpackage Adapter_Abstract
 * @copyright  Copyright (c) 2012 PHP Image Manipulation & Processing Library
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
abstract class Image_Processor_Adapter_Abstract implements Image_Processor_Adapter_Interface
{
    /**
     * An array of required extensions to make this adapter work.
     *
     * @var array
     */
    protected $_requiredExtensions = array();

    /**
     * Image background colour - used for rotate.
     *
     * @var int
     */
    protected $_imageBackgroundColor = 0;

    /**#@+
     * Position constant
     *
     * @var string
     */
    const POSITION_TOP_LEFT     = 'top-left';
    const POSITION_TOP_RIGHT    = 'top-right';
    const POSITION_BOTTOM_LEFT  = 'bottom-left';
    const POSITION_BOTTOM_RIGHT = 'bottom-right';
    const POSITION_STRETCH      = 'stretch';
    const POSITION_TILE         = 'tile';
    const POSITION_CENTER       = 'center';
    /**#@-*/

    /**#@+
     * Protected property
     *
     * @property string
     */
    protected $_fileName;
    protected $_fileMimeType;
    protected $_fileSrcName;
    protected $_fileSrcPath;
    protected $_imageHandler;
    protected $_imageSrcWidth;
    protected $_imageSrcHeight;
    protected $_fileType;
    protected $_watermarkPosition;
    protected $_watermarkImageOpacity;
    protected $_watermarkWidth;
    protected $_watermarkHeigth;
    protected $_keepAspectRatio  = true; // Maintain aspect ratio?
    protected $_keepFrame        = false; // Make a box, and fit image inside?
    protected $_keepTransparency = false;
    protected $_constrainOnly    = true; // (bool)true Stops image from resizing larger than original?
    protected $_quality          = 80;
    protected $_backgroundColor  = array(255, 255, 255);
    /**#@-*/

    public function __construct()
    {
        //
    }

    public function getMimeType()
    {
        if (is_null($this->_fileMimeType)) {
            $this->_fileMimeType = $this->_imageTypeToMimeType();
        }

        return $this->_fileMimeType;
    }

    /**
     * Sets the location of the file to be used for processing.
     *
     * @param  string $fileName
     * @throws Image_Processor_Adapter_Exception
     * @return Image_Processor_Adapter_Abstract
     */
    public function setFileName($fileName)
    {
        if (empty($fileName)) {
            throw new Image_Processor_Adapter_Exception('The file name / location was empty');
        }

        @clearstatcache();
        if (!@is_readable($fileName)) {
            throw new Image_Processor_Adapter_Exception(
                "The file name / location '{$fileName}' was not readable"
            );
        }

        $this->_fileName = $fileName;

        $this->_getFileAttributes();
        $this->_getImageStatistics();

        return $this;
    }

    /**
     * Get's the file name location.
     *
     * @throws Image_Processor_Adapter_Exception
     * @return string
     */
    public function getFileName()
    {
        if (is_null($this->_fileName)) {
            throw new Image_Processor_Adapter_Exception(
                'The source file location was not set, you must set the location before attempting to use it'
            );
        }

        return $this->_fileName;
    }

    /**
     * Retrieve Original Image Width
     *
     * @return int|null
     * @deprecated Please use {@see Image_Processor_Adapter_Abstract::getImageSrcWidth()}
     */
    public function getOriginalWidth()
    {
        return $this->getImageSrcWidth();
    }

    /**
     * Retrieve Original Image Height
     *
     * @return int|null
     * @deprecated Please use {@see Image_Processor_Adapter_Abstract::getImageSrcHeight()}
     */
    public function getOriginalHeight()
    {
        return $this->getImageSrcHeight();
    }

    /**#@+
     * Property setter
     *
     * @param  mixed
     * @return Image_Processor_Adapter_Abstract
     */
    public function setWatermarkPosition($position)
    {
        $this->_watermarkPosition = $position;
        return $this;
    }

    public function setWatermarkImageOpacity($imageOpacity)
    {
        $this->_watermarkImageOpacity = $imageOpacity;
        return $this;
    }

    public function setWatermarkWidth($width)
    {
        $this->_watermarkWidth = $width;
        return $this;
    }

    public function setWatermarkHeigth($heigth)
    {
        $this->_watermarkHeigth = $heigth;
        return $this;
    }

    public function setImageBackgroundColor($color)
    {
        $this->_imageBackgroundColor = $color;
        return $this;
    }

    public function setKeepAspectRatio($value)
    {
        $this->_keepAspectRatio = (bool)$value;
        return $this;
    }

    public function setKeepFrame($value)
    {
        $this->_keepFrame = (bool)$value;
        return $this;
    }

    public function setKeepTransparency($value)
    {
        $this->_keepTransparency = (bool)$value;
        return $this;
    }

    public function setConstrainOnly($value)
    {
        $this->_constrainOnly = (bool)$value;
        return $this;
    }

    public function setQuality($value)
    {
        $this->_quality = (int)$value;
        return $this;
    }
    /**#@-*/

    /**
     * Sets the background colour RGB value using a 3 element array.
     *
     * @param  array $value
     * @throws InvalidArgumentException
     * @return Image_Processor_Adapter_Abstract
     */
    public function setBackgroundColor(array $value)
    {
        if (3 !== count($value)) {
            throw new InvalidArgumentException(
                'Background colour must be an array with 3 elements containing valid RGB values.'
            );
        }
        foreach ($value as $color) {
            if ((!is_integer($color)) || ($color < 0) || ($color > 255)) {
                throw new InvalidArgumentException(
                    "Colour must be a valid RGB value. You passed '{$color}'"
                );
            }
        }
        $this->_backgroundColor = $value;
        return $this;
    }

    /**#@+
     * Property getter
     *
     * @return string
     */
    public function getWatermarkPosition()
    {
        return $this->_watermarkPosition;
    }

    public function getWatermarkImageOpacity()
    {
        return $this->_watermarkImageOpacity;
    }

    public function getWatermarkWidth()
    {
        return $this->_watermarkWidth;
    }

    public function getWatermarkHeigth()
    {
        return $this->_watermarkHeigth;
    }

    public function getImageBackgroundColor()
    {
        return $this->_imageBackgroundColor;
    }

    public function getKeepAspectRatio()
    {
        return $this->_keepAspectRatio;
    }

    public function getKeepFrame()
    {
        return $this->_keepFrame;
    }

    public function getKeepTransparency()
    {
        return $this->_keepTransparency;
    }

    public function getConstrainOnly()
    {
        return $this->_constrainOnly;
    }

    public function getQuality()
    {
        return $this->_quality;
    }

    public function getBackgroundColor()
    {
        return $this->_backgroundColor;
    }

    public function getImageSrcWidth()
    {
        $this->_getImageStatistics();
        return $this->_imageSrcWidth;
    }

    public function getImageSrcHeight()
    {
        $this->_getImageStatistics();
        return $this->_imageSrcHeight;
    }

    public function getImageSrcFileType()
    {
        $this->_getImageStatistics();
        return $this->_fileType;
    }

    public function getFileSrcPath()
    {
        $this->_getFileAttributes();
        return $this->_fileSrcPath;
    }

    public function getFileSrcName()
    {
        $this->_getFileAttributes();
        return $this->_fileSrcName;
    }
    /**#@-*/

    /**
     * Gets the image statistics and sets them into their respective properties.
     *
     * @return void
     */
    protected function _getImageStatistics()
    {
        // This function does not require the GD image library.
        list($this->_imageSrcWidth, $this->_imageSrcHeight, $this->_fileType) = $this->_getImageSize();
    }

    /**
     * Gets the image file attributes and sets them into their respective properties.
     *
     * @return void
     */
    protected function _getFileAttributes()
    {
        if (is_null($this->_fileSrcPath) || is_null($this->_fileSrcName)) {

            $pathinfo = $this->_getpathInfo();

            $this->_fileSrcPath = $pathinfo['dirname'];
            $this->_fileSrcName = $pathinfo['basename'];
        }
    }

    // @codeCoverageIgnoreStart

    /**
     * This method checks pependencies based on the {@see $this->_requiredExtensions} array.
     *
     * @return Image_Processor_Adapter_Abstract
     */
    public function checkDependencies()
    {
        foreach ($this->_requiredExtensions as $value) {
            if (!extension_loaded($value)) {
                throw new Image_Processor_Adapter_Exception("Required PHP extension '{$value}' was not loaded");
            }
        }

        return $this;
    }

    /**
     * Proxies directly to the pathinfo php function.
     *
     * @return array 2 elements required, dirname and basename
     */
    protected function _getpathInfo()
    {
        return pathinfo($this->getFileName());
    }

    /**
     * Proxies directly to getimagesize php function. Does not require gd extension.
     *
     * @return array 3 elements
     */
    protected function _getImageSize()
    {
        return getimagesize($this->getFileName());
    }

    protected function _imageTypeToMimeType()
    {
        return image_type_to_mime_type($this->getImageSrcFileType());
    }

    // @codeCoverageIgnoreEnd
}