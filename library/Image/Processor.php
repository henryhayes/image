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
 * @copyright  Copyright (c) 2012 PHP Image Manipulation & Processing Library
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @since      Thursday, 20 September 2012
 */
/**
 * Image processor class.
 *
 * @category   Image
 * @package    Processor
 * @copyright  Copyright (c) 2012 PHP Image Manipulation & Processing Library
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class Image_Processor
{
    /**
     * Contains the adapter object.
     *
     * @var Image_Processor_Adapter_Abstract
     */
    protected $_adapterNamespace = 'Image_Processor_Adapter';

    /**
     * Contains the adapter object.
     *
     * @var Image_Processor_Adapter_Abstract
     */
    protected $_adapter = 'Gd2';

    /**
     * Location of the file name
     *
     * @var string
     */
    protected $_fileName;

    /**
     * Constructor
     *
     * @param Image_Processor_Adapter $adapter. Default value is GD2
     * @param string $fileName
     * @return void
     */
    function __construct()
    {
        $this->init();
    }

    /**
     *
     */
    public function init()
    {

    }

    /**
     * Opens an image and creates image handle
     *
     * @access public
     * @return void
     */
    public function open()
    {
        $this->getAdapter()->checkDependencies();

        if (!file_exists($this->_fileName)) {
            throw new Exception("File '{$this->_fileName}' does not exist");
        }

        $this->getAdapter()->open($this->_fileName);
    }

    /**
     * Display handled image in your browser
     *
     * @access public
     * @return void
     */
    public function display()
    {
        $this->getAdapter()->display();
    }

    /**
     * Save handled image into file
     *
     * @param string $destination. Default value is NULL
     * @param string $newFileName. Default value is NULL
     * @access public
     * @return void
     */
    public function save($destination=null, $newFileName=null)
    {
        $this->getAdapter()->save($destination, $newFileName);
    }

    /**
     * Rotate an image.
     *
     * @param int $angle
     * @access public
     * @return void
     */
    public function rotate($angle)
    {
        $this->getAdapter()->rotate($angle);
    }

    /**
     * Crop an image.
     *
     * @param int $top. Default value is 0
     * @param int $left. Default value is 0
     * @param int $right. Default value is 0
     * @param int $bottom. Default value is 0
     * @access public
     * @return void
     */
    public function crop($top=0, $left=0, $right=0, $bottom=0)
    {
        $this->getAdapter()->crop($top, $left, $right, $bottom);
    }

    /**
     * Resize an image
     *
     * @param int $width
     * @param int $height
     * @access public
     * @return void
     */
    public function resize($width, $height = null)
    {
        $this->getAdapter()->resize($width, $height);
    }

    /**
     * Should aspect ratio be maintained.
     *
     * @param bool $value
     * @return bool|Varien_Image_Adapter_Abstract
     */
    public function keepAspectRatio($value)
    {
        return $this->getAdapter()->keepAspectRatio($value);
    }

    public function keepFrame($value)
    {
        return $this->getAdapter()->keepFrame($value);
    }

    public function keepTransparency($value)
    {
        return $this->getAdapter()->keepTransparency($value);
    }

    public function constrainOnly($value)
    {
        return $this->getAdapter()->constrainOnly($value);
    }

    public function backgroundColor($value)
    {
        return $this->getAdapter()->backgroundColor($value);
    }

    /**
     * Get/set quality, values in percentage from 0 to 100
     *
     * @param int $value
     * @return int
     */
    public function quality($value)
    {
        return $this->getAdapter()->quality($value);
    }

    /**
     * Adds watermark to our image.
     *
     * @param string $watermarkImage. Absolute path to watermark image.
     * @param int $positionX. Watermark X position.
     * @param int $positionY. Watermark Y position.
     * @param int $watermarkImageOpacity. Watermark image opacity.
     * @param bool $repeat. Enable or disable watermark brick.
     * @access public
     * @return void
     */
    public function watermark($watermarkImage, $positionX=0, $positionY=0, $watermarkImageOpacity=30, $repeat=false)
    {
        if (!file_exists($watermarkImage)) {
            throw new Exception("Required file '{$watermarkImage}' does not exists.");
        }
        $this->getAdapter()->watermark($watermarkImage, $positionX, $positionY, $watermarkImageOpacity, $repeat);
    }

    /**
     * Get mime type of handled image
     *
     * @access public
     * @return string
     */
    public function getMimeType()
    {
        return $this->getAdapter()->getMimeType();
    }

    /**
     * Set image background color
     *
     * @param int $color
     * @access public
     * @return void
     */
    public function setImageBackgroundColor($color)
    {
        $this->getAdapter()->setImageBackgroundColor(intval($color));
    }

    /**
     * Set watermark position
     *
     * @param string $position
     * @return Image_Processor
     */
    public function setWatermarkPosition($position)
    {
        $this->getAdapter()->setWatermarkPosition($position);
        return $this;
    }

    /**
     * Set watermark image opacity
     *
     * @param int $imageOpacity
     * @return Image_Processor
     */
    public function setWatermarkImageOpacity($imageOpacity)
    {
        $this->getAdapter()->setWatermarkImageOpacity($imageOpacity);
        return $this;
    }

    /**
     * Set watermark width
     *
     * @param int $width
     * @return Image_Processor
     */
    public function setWatermarkWidth($width)
    {
        $this->getAdapter()->setWatermarkWidth($width);
        return $this;
    }

    /**
     * Set watermark heigth
     *
     * @param int $heigth
     * @return Image_Processor
     */
    public function setWatermarkHeigth($heigth)
    {
        $this->getAdapter()->setWatermarkHeigth($heigth);
        return $this;
    }

    /**
     * Sets the file name of the current image being processed.
     *
     * @param  string $fileName
     * @return Image_Processor
     */
    protected function setFileName($fileName)
    {
        $this->_fileName = $fileName;
        return $this;
    }

    /**
     * Get's the file name of the current image being processed.
     *
     * @return string
     */
    protected function getFileName()
    {
        return $this->_fileName;
    }

    /**
     * Sets the adapter to use.
     *
     * @param Image_Processor_Adapter_Abstract $adapter
     * @return Image_Processor
     */
    protected function setAdapter(Image_Processor_Adapter_Abstract $adapter)
    {
        $this->_adapter = $adapter;
        return $this;
    }

    /**
     * Retrieve image adapter object.
     *
     * @return Image_Processor_Adapter_Abstract
     */
    protected function getAdapter()
    {
        if (!($this->_adapter instanceof Image_Processor_Adapter_Abstract)) {
            if (is_null($this->_adapter)) {
                throw new Image_Processor_Adapter_Exception('Invalid Adapter Specified');
            }
            $class = $this->getAdapterNamespace() . '_' . $this->_adapter;

            if ('Image_Processor_Adapter' == $this->getAdapterNamespace()) {
                $classPath = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
                require_once($classPath);
            }

            $this->_adapter = new $class();
        }

        return $this->_adapter;
    }

    /**
     * Sets the adapter name string.
     *
     * @param string $adapterName
     * @return Image_Processor
     */
    public function setAdapterName($adapterName)
    {
        $this->_adapter = $adapterName;
        return $this;
    }

    /**
     * @see Dfp_Datafeed_Transfer_Interface::setAdapterNamespace()
     * @return Image_Processor
     */
    public function setAdapterNamespace($namespace)
    {
        $this->_adapterNamespace = $namespace;
        return $this;
    }

    /**
     * Retrieve original image width
     *
     * @return int|null
     */
    public function getOriginalWidth()
    {
        return $this->getAdapter()->getOriginalWidth();
    }

    /**
     * Retrieve original image height
     *
     * @return int|null
     */
    public function getOriginalHeight()
    {
        return $this->getAdapter()->getOriginalHeight();
    }

    /**
     * Magic method to replace all the facade methods.
     *
     * @param  string $name
     * @param  string $args
     * @throws BadMethodCallException
     * @return Image_Processor
     */
    public function __call($name, $args)
    {
        if (!method_exists($this->getAdapter(), $name)) {
            throw new BadMethodCallException(
                sprintf("Method '{$name}' does not exist in adapter '%s'", get_class($this->getAdapter()))
            );
        }

        $returnValue = call_user_func_array(array($this->getAdapter(), $name), $args);

        if ('set' == substr($name, 0, 3)) {
            return $this;
        }

        return $returnValue;
    }
}
