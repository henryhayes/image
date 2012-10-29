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
 * @see Image_Processor_Adapter_Interface
 */
require_once('Image/Processor/Adapter/Interface.php');
/**
 * Image processor class.
 *
 * @category   Image
 * @package    Processor
 * @copyright  Copyright (c) 2012 PHP Image Manipulation & Processing Library
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class Image_Processor implements Image_Processor_Adapter_Interface
{
    /**
     * Contains the adapter object.
     *
     * @var string
     */
    protected $_adapterNamespace = 'Image_Processor_Adapter';

    /**
     * Contains the adapter object.
     *
     * @var Image_Processor_Adapter_Abstract
     */
    protected $_adapter = 'Gd2';

    /**
     * Static constructor, returns a new instance of this class.
     *
     * @return Image_Processor
     */
    public static function getInstance()
    {
        return new static();
    }

    /**
     * Opens an image and creates image handle
     *
     * @return Image_Processor
     */
    public function open($fileName = null)
    {
        $this->getAdapter()->open($fileName);
        return $this;
    }

    /**
     * Save handled image into file
     *
     * @param  string $destination. Default value is NULL
     * @param  string $newFileName. Default value is NULL
     * @return Image_Processor
     */
    public function save($destination = null, $newFileName = null)
    {
        $this->getAdapter()->save($destination, $newFileName);
        return $this;
    }

    /**
     * Display handled image in your browser
     *
     * @return php://stdout
     */
    public function display()
    {
        $this->getAdapter()->display();
    }

    /**
     * Returns the image as a string ready to be printed to the screen.
     *
     * @return string
     */
    public function render()
    {
        return $this->getAdapter()->render();
    }

    /**
     * Proxies directly to the render method.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getAdapter()->__toString();
    }

    /**
     * Rotate an image.
     *
     * @param  int $angle
     * @return Image_Processor
     */
    public function rotate($angle)
    {
        $this->getAdapter()->rotate($angle);
        return $this;
    }

    /**
     * Crop an image.
     *
     * @param  int $top. Default value is 0
     * @param  int $left. Default value is 0
     * @param  int $right. Default value is 0
     * @param  int $bottom. Default value is 0
     * @return Image_Processor
     */
    public function crop($top=0, $left=0, $right=0, $bottom=0)
    {
        $this->getAdapter()->crop($top, $left, $right, $bottom);
        return $this;
    }

    /**
     * Resize an image.
     *
     * @param  int $width
     * @param  int $height
     * @return Image_Processor
     */
    public function resize($width, $height = null)
    {
        $this->getAdapter()->resize($width, $height);
        return $this;
    }

    /**
     * Adds watermark to our image.
     *
     * @param  string $watermarkImage. Absolute path to watermark image.
     * @param  int $positionX. Watermark X position.
     * @param  int $positionY. Watermark Y position.
     * @param  int $watermarkImageOpacity. Watermark image opacity.
     * @param  bool $repeat. Enable or disable watermark brick.
     * @return Image_Processor
     */
    public function watermark($watermarkImage, $positionX=0, $positionY=0, $watermarkImageOpacity=30, $repeat=false)
    {
        if (!file_exists($watermarkImage)) {
            throw new Exception("Required file '{$watermarkImage}' does not exists.");
        }
        $this->getAdapter()->watermark($watermarkImage, $positionX, $positionY, $watermarkImageOpacity, $repeat);

        return $this;
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
     * Sets the adapter to use.
     *
     * @param  Image_Processor_Adapter_Interface $adapter
     * @return Image_Processor
     */
    public function setAdapter(Image_Processor_Adapter_Interface $adapter)
    {
        $this->_adapter = $adapter;
        return $this;
    }

    /**
     * Retrieve image adapter object.
     *
     * @return Image_Processor_Adapter_Interface
     */
    protected function getAdapter()
    {
        if (!($this->_adapter instanceof Image_Processor_Adapter_Interface)) {
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
     * @param  string $adapterName
     * @return Image_Processor
     */
    public function setAdapterName($adapterName)
    {
        $this->_adapter = $adapterName;
        return $this;
    }

    /**
     * Sets the adapter namespace.
     *
     * @param  string $namespace
     * @return Image_Processor
     */
    public function setAdapterNamespace($namespace)
    {
        $this->_adapterNamespace = $namespace;
        return $this;
    }

    /**
     * Returns the adapters namespace as a string.
     *
     * @return string
     */
    public function getAdapterNamespace()
    {
        return $this->_adapterNamespace;
    }

    /**
     * Magic method to replace all the facade methods.
     *
     * @param  string $name
     * @param  array  $args
     * @throws BadMethodCallException
     * @return Image_Processor
     */
    public function __call($method, array $args)
    {
        if (!method_exists($this->getAdapter(), $method)) {
            throw new BadMethodCallException(
                sprintf("Method '{$method}' does not exist in adapter '%s'", get_class($this->getAdapter()))
            );
        }

        $returnValue = call_user_func_array(array($this->getAdapter(), $method), $args);

        if ('set' == substr($method, 0, 3)) {
            return $this;
        }

        return $returnValue;
    }
}
