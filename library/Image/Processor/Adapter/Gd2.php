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
 * @subpackage Adapter_Gd2
 * @copyright  Copyright (c) 2012 PHP Image Manipulation & Processing Library
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @since      Thursday, 20 September 2012
 */
/**
 * @see Image_Processor_Adapter_Abstract
 */
require_once('Image/Processor/Adapter/Abstract.php');
/**
 * Image processor adapter Gd2 class.
 *
 * @category   Image
 * @package    Processor
 * @subpackage Adapter_Gd2
 * @copyright  Copyright (c) 2012 PHP Image Manipulation & Processing Library
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class Image_Processor_Adapter_Gd2 extends Image_Processor_Adapter_Abstract
{
    /**
     * An array of required extensions to make this adapter work.
     *
     * @var unknown_type
     */
    protected $_requiredExtensions = array('gd');

    /**
     * Array of image callback functions depending on image type.
     *
     * @var array
     */
    private static $_callbacks = array(
        IMAGETYPE_GIF  => array('output' => 'imagegif',  'create' => 'imagecreatefromgif'),
        IMAGETYPE_JPEG => array('output' => 'imagejpeg', 'create' => 'imagecreatefromjpeg'),
        IMAGETYPE_PNG  => array('output' => 'imagepng',  'create' => 'imagecreatefrompng'),
        IMAGETYPE_XBM  => array('output' => 'imagexbm',  'create' => 'imagecreatefromxbm'),
        IMAGETYPE_WBMP => array('output' => 'imagewbmp', 'create' => 'imagecreatefromwbmp'),
    );

    /**
     * Whether image was resized or not
     *
     * @var bool
     */
    protected $_resized = false;

    /**
     * Opens the file with the appropriate image type handler.
     *
     * @param  string $fileName
     * @return Image_Processor_Adapter_Interface
     */
    public function open($fileName = null)
    {
        $this->setFileName($fileName);
        return $this;
    }

    /**
     * Sets the image handler.
     *
     * @param  gd $imageHandler
     * @throws UnexpectedValueException
     * @return Image_Processor_Adapter_Gd2
     */
    public function setImageHandler($imageHandler)
    {
        if (!is_resource($imageHandler)) {
            throw new UnexpectedValueException('Image handler passed was not a resource');
        }

        $resourceType = @get_resource_type($imageHandler);
        if ('gd' != $resourceType) {
            throw new UnexpectedValueException(
                "Image handler was not correct type, 'gd' expected, '{$resourceType}' provided"
            );
        }

        $this->_imageHandler = $imageHandler;

        return $this;
    }

    public function getImageHandler()
    {
        if (!is_resource($this->_imageHandler)) {
            $this->_imageHandler = call_user_func($this->_getCallback('create'), $this->getFileName());
        }

        return $this->_imageHandler;
    }

    /**
     * Saves the new, processed or resized file.
     *
     * @return void
     */
    public function save($destination=null, $newName=null)
    {
        $fileName = (!isset($destination)) ? $this->getFileName() : $destination;

        if (isset($destination) && isset($newName)) {
            $fileName = $destination . "/" . $newName;
        } elseif (isset($destination) && !isset($newName)) {
            $info = pathinfo($destination);
            $fileName = $destination;
            $destination = $info['dirname'];
        } elseif (!isset($destination) && isset($newName)) {
            $fileName = $this->getFileSrcPath() . "/" . $newName;
        } else {
            $fileName = $this->getFileSrcPath() . $this->getFileSrcName();
        }

        $destinationDir = (isset($destination)) ? $destination : $this->getFileSrcPath();

        if (!is_writable($destinationDir)) {

            @chmod($destinationDir, 0777);

            $mkdirResult = @mkdir($destinationDir, 0777, true);
            if ($mkdirResult) {
                @chmod($destinationDir, 0777);
            }

            // If still is not wrtiteable...
            if (!is_writable($destinationDir)) {
                throw new Image_Processor_Adapter_Exception(
                    "Unable to write file into directory '{$destinationDir}' - access forbidden"
                );
            }
        }

        if (!$this->_resized) {
            // keep alpha transparency
            $isAlpha     = false;
            $isTrueColor = false;
            $this->_getTransparency($this->getImageHandler(), $this->getImageSrcFileType(), $isAlpha, $isTrueColor);
            if ($isAlpha) {
                if ($isTrueColor) {
                    $newImage = imagecreatetruecolor($this->getImageSrcWidth(), $this->getImageSrcHeight());
                } else {
                    $newImage = imagecreate($this->getImageSrcWidth(), $this->getImageSrcHeight());
                }
                $this->_fillBackgroundColor($newImage);
                imagecopy(
                    $newImage,
                    $this->getImageHandler(),
                    0, 0,
                    0, 0,
                    $this->getImageSrcWidth(), $this->getImageSrcHeight()
                );
                $this->setImageHandler($newImage);
            }
        }

        $functionParameters = array();
        $functionParameters[] = $this->getImageHandler();
        $functionParameters[] = $fileName;

        // set quality param for JPG file type
        if (!is_null($this->getQuality()) && $this->getImageSrcFileType() == IMAGETYPE_JPEG) {
            $functionParameters[] = $this->getQuality();
        }

        // set quality param for PNG file type
        if (!is_null($this->getQuality()) && $this->getImageSrcFileType() == IMAGETYPE_PNG) {
            $quality = round(($this->getQuality() / 100) * 10);
            if ($quality < 1) {
                $quality = 1;
            } elseif ($quality > 10) {
                $quality = 10;
            }
            $quality = 10 - $quality;
            $functionParameters[] = $quality;
        }

        call_user_func_array($this->_getCallback('output'), $functionParameters);
    }

    public function display()
    {
        header("Content-type: " . $this->getMimeType());
        call_user_func($this->_getCallback('output'), $this->getImageHandler());
    }

    /**
     * Obtain function name, basing on image type and callback type
     *
     * @param string $callbackType
     * @param int    $fileType
     * @return string
     * @throws Image_Processor_Adapter_Exception
     */
    private function _getCallback($callbackType, $fileType = null, $unsupportedText = 'Unsupported image format')
    {
        if (null === $fileType) {
            $fileType = $this->getImageSrcFileType();
        }
        if (empty(self::$_callbacks[$fileType])) {
            throw new Image_Processor_Adapter_Exception($unsupportedText);
        }
        if (empty(self::$_callbacks[$fileType][$callbackType])) {
            throw new Image_Processor_Adapter_Exception('Callback not found');
        }
        return self::$_callbacks[$fileType][$callbackType];
    }

    private function _fillBackgroundColor(&$imageResourceTo)
    {
        // try to keep transparency, if any
        if ($this->_keepTransparency) {
            $isAlpha = false;
            $transparentIndex = $this->_getTransparency(
                $this->getImageHandler(), $this->getImageSrcFileType(), $isAlpha
            );
            try {
                // fill truecolor png with alpha transparency
                if ($isAlpha) {

                    if (!imagealphablending($imageResourceTo, false)) {
                        throw new Image_Processor_Adapter_Exception(
                            'Failed to set alpha blending for PNG image.'
                        );
                    }
                    $transparentAlphaColor = imagecolorallocatealpha($imageResourceTo, 0, 0, 0, 127);
                    if (false === $transparentAlphaColor) {
                        throw new Image_Processor_Adapter_Exception(
                            'Failed to allocate alpha transparency for PNG image.'
                        );
                    }
                    if (!imagefill($imageResourceTo, 0, 0, $transparentAlphaColor)) {
                        throw new Image_Processor_Adapter_Exception(
                            'Failed to fill PNG image with alpha transparency.'
                        );
                    }
                    if (!imagesavealpha($imageResourceTo, true)) {
                        throw new Image_Processor_Adapter_Exception(
                            'Failed to save alpha transparency into PNG image.'
                        );
                    }

                    return $transparentAlphaColor;

                } elseif (false !== $transparentIndex) {
                    // fill image with indexed non-alpha transparency
                    $transparentColor = false;
                    if ($transparentIndex >=0 && $transparentIndex <= imagecolorstotal($this->getImageHandler())) {
                        list($r, $g, $b)  = array_values(
                            imagecolorsforindex($this->getImageHandler(), $transparentIndex)
                        );
                        $transparentColor = imagecolorallocate($imageResourceTo, $r, $g, $b);
                    }
                    if (false === $transparentColor) {
                        throw new Image_Processor_Adapter_Exception('Failed to allocate transparent color for image.');
                    }
                    if (!imagefill($imageResourceTo, 0, 0, $transparentColor)) {
                        throw new Image_Processor_Adapter_Exception('Failed to fill image with transparency.');
                    }
                    imagecolortransparent($imageResourceTo, $transparentColor);

                    return $transparentColor;
                }
            }
            catch (Image_Processor_Adapter_Exception $e) {
                // fallback to default background color
            }
        }
        list($r, $g, $b) = $this->getBackgroundColor();
        $color = imagecolorallocate($imageResourceTo, $r, $g, $b);
        if (!imagefill($imageResourceTo, 0, 0, $color)) {
            throw new Image_Processor_Adapter_Exception("Failed to fill image background with color {$r} {$g} {$b}.");
        }

        return $color;
    }

    /**
     * Gives true for a PNG with alpha, false otherwise
     *
     * @param string $fileName
     * @return boolean
     */
    public function checkAlpha($fileName)
    {
        return ((ord(file_get_contents($fileName, false, null, 25, 1)) & 6) & 4) == 4;
    }

    /**
     * Returns transparency.
     *
     * @param resource $imageResource
     * @param string   $fileType
     * @param bool     $isAlpha
     * @param bool     $isTrueColor
     * @return boolean | int
     */
    private function _getTransparency($imageResource, $fileType, &$isAlpha = false, &$isTrueColor = false)
    {
        $isAlpha     = false;
        $isTrueColor = false;
        // assume that transparency is supported by gif/png only
        if ((IMAGETYPE_GIF === $fileType) || (IMAGETYPE_PNG === $fileType)) {
            // check for specific transparent color
            $transparentIndex = imagecolortransparent($imageResource);
            if ($transparentIndex >= 0) {
                return $transparentIndex;
            } elseif (IMAGETYPE_PNG === $fileType) {
                // assume that truecolor PNG has transparency
                $isAlpha     = $this->checkAlpha($this->getFileName());
                $isTrueColor = true;
                return $transparentIndex; // -1
            }
        }
        if (IMAGETYPE_JPEG === $fileType) {
            $isTrueColor = true;
        }
        return false;
    }

    /**
     * Change the image size
     *
     * @param  int $frameWidth
     * @param  int $frameHeight
     * @return Image_Processor_Adapter_Gd2
     */
    public function resize($frameWidth = null, $frameHeight = null)
    {
        if (empty($frameWidth) && empty($frameHeight)) {
            throw new Image_Processor_Adapter_Exception('Invalid image dimensions.');
        }

        // calculate lacking dimension
        if (!$this->getKeepFrame()) {
            if (null === $frameWidth) {
                $frameWidth = round($frameHeight * ($this->getImageSrcWidth() / $this->getImageSrcHeight()));
            } elseif (null === $frameHeight) {
                $frameHeight = round($frameWidth * ($this->getImageSrcHeight() / $this->getImageSrcWidth()));
            }
        } else {
            if (null === $frameWidth) {
                $frameWidth = $frameHeight;
            } elseif (null === $frameHeight) {
                $frameHeight = $frameWidth;
            }
        }

        // define coordinates of image inside new frame
        $srcX = 0;
        $srcY = 0;
        $dstX = 0;
        $dstY = 0;
        $dstWidth  = $frameWidth;
        $dstHeight = $frameHeight;
        if ($this->getKeepAspectRatio()) {
            // do not make picture bigger, than it is, if required
            if ($this->getConstrainOnly()) {
                if (($frameWidth >= $this->getImageSrcWidth()) && ($frameHeight >= $this->getImageSrcHeight())) {
                    $dstWidth  = $this->getImageSrcWidth();
                    $dstHeight = $this->getImageSrcHeight();
                }
            }
            // keep aspect ratio
            if ($this->getImageSrcWidth() / $this->getImageSrcHeight() >= $frameWidth / $frameHeight) {
                $dstHeight = round(($dstWidth / $this->getImageSrcWidth()) * $this->getImageSrcHeight());
            } else {
                $dstWidth = round(($dstHeight / $this->getImageSrcHeight()) * $this->getImageSrcWidth());
            }
        }
        // define position in center (TODO: add positions option)
        $dstY = round(($frameHeight - $dstHeight) / 2);
        $dstX = round(($frameWidth - $dstWidth) / 2);

        // get rid of frame (fallback to zero position coordinates)
        if (!$this->getKeepFrame()) {
            $frameWidth  = $dstWidth;
            $frameHeight = $dstHeight;
            $dstY = 0;
            $dstX = 0;
        }

        // create new image
        $isAlpha     = false;
        $isTrueColor = false;
        $this->_getTransparency($this->getImageHandler(), $this->getImageSrcFileType(), $isAlpha, $isTrueColor);
        if ($isTrueColor) {
            $newImage = imagecreatetruecolor($frameWidth, $frameHeight);
        } else {
            $newImage = imagecreate($frameWidth, $frameHeight);
        }

        // fill new image with required color
        $this->_fillBackgroundColor($newImage);

        // resample source image and copy it into new frame
        imagecopyresampled(
            $newImage,
            $this->getImageHandler(),
            $dstX, $dstY,
            $srcX, $srcY,
            $dstWidth, $dstHeight,
            $this->getImageSrcWidth(), $this->getImageSrcHeight()
        );
        $this->setImageHandler($newImage);
        $this->refreshImageDimensions();
        $this->_resized = true;

        return $this;
    }

    /**
     * Rotattes the image by the angle.
     *
     * @param  int $angle
     * @return Image_Processor_Adapter_Gd2
     */
    public function rotate($angle)
    {
        $this->_imageHandler = imagerotate($this->getImageHandler(), $angle, $this->getImageBackgroundColor());
        $this->refreshImageDimensions();
        return $this;
    }

    public function watermark($watermarkImage, $positionX=0, $positionY=0, $watermarkImageOpacity=30, $repeat=false)
    {
        list($watermarkSrcWidth, $watermarkSrcHeight, $watermarkFileType, ) = getimagesize($watermarkImage);
        $this->_getFileAttributes();
        $watermark = call_user_func(
            $this->_getCallback(
                'create',
                $watermarkFileType,
                'Unsupported watermark image format.'
            ),
            $watermarkImage
        );

        $merged = false;

        if ($this->getWatermarkWidth() &&
            $this->getWatermarkHeigth() &&
            ($this->getWatermarkPosition() != self::POSITION_STRETCH)
        ) {
            $newWatermark = imagecreatetruecolor($this->getWatermarkWidth(), $this->getWatermarkHeigth());
            imagealphablending($newWatermark, false);
            $col = imagecolorallocate($newWatermark, 255, 255, 255);
            imagecolortransparent($newWatermark, $col);
            imagefilledrectangle($newWatermark, 0, 0, $this->getWatermarkWidth(), $this->getWatermarkHeigth(), $col);
            imagealphablending($newWatermark, true);
            imageSaveAlpha($newWatermark, true);
            imagecopyresampled(
                $newWatermark,
                $watermark,
                0, 0, 0, 0,
                $this->getWatermarkWidth(), $this->getWatermarkHeigth(),
                imagesx($watermark), imagesy($watermark)
            );
            $watermark = $newWatermark;
        }

        if ($this->getWatermarkPosition() == self::POSITION_TILE) {
            $repeat = true;
        } elseif ($this->getWatermarkPosition() == self::POSITION_STRETCH) {

            $newWatermark = imagecreatetruecolor($this->getImageSrcWidth(), $this->getImageSrcHeight());
            imagealphablending($newWatermark, false);
            $col = imagecolorallocate($newWatermark, 255, 255, 255);
            imagecolortransparent($newWatermark, $col);
            imagefilledrectangle($newWatermark, 0, 0, $this->getImageSrcWidth(), $this->getImageSrcHeight(), $col);
            imagealphablending($newWatermark, true);
            imageSaveAlpha($newWatermark, true);
            imagecopyresampled(
                $newWatermark,
                $watermark,
                0, 0, 0, 0,
                $this->getImageSrcWidth(), $this->getImageSrcHeight(),
                imagesx($watermark), imagesy($watermark)
            );
            $watermark = $newWatermark;

        } elseif ($this->getWatermarkPosition() == self::POSITION_CENTER) {
            $positionX = ($this->getImageSrcWidth()/2 - imagesx($watermark)/2);
            $positionY = ($this->getImageSrcHeight()/2 - imagesy($watermark)/2);
            imagecopymerge(
                $this->getImageHandler(),
                $watermark,
                $positionX, $positionY,
                0, 0,
                imagesx($watermark), imagesy($watermark),
                $this->getWatermarkImageOpacity()
            );
        } elseif ($this->getWatermarkPosition() == self::POSITION_TOP_RIGHT) {
            $positionX = ($this->getImageSrcWidth() - imagesx($watermark));
            imagecopymerge(
                $this->getImageHandler(),
                $watermark,
                $positionX, $positionY,
                0, 0,
                imagesx($watermark), imagesy($watermark),
                $this->getWatermarkImageOpacity()
            );
        } elseif ($this->getWatermarkPosition() == self::POSITION_TOP_LEFT) {
            imagecopymerge(
                $this->getImageHandler(),
                $watermark,
                $positionX, $positionY,
                0, 0,
                imagesx($watermark), imagesy($watermark),
                $this->getWatermarkImageOpacity()
            );
        } elseif ($this->getWatermarkPosition() == self::POSITION_BOTTOM_RIGHT) {
            $positionX = ($this->getImageSrcWidth() - imagesx($watermark));
            $positionY = ($this->getImageSrcHeight() - imagesy($watermark));
            imagecopymerge(
                $this->getImageHandler(),
                $watermark,
                $positionX, $positionY,
                0, 0,
                imagesx($watermark), imagesy($watermark),
                $this->getWatermarkImageOpacity()
            );
        } elseif ($this->getWatermarkPosition() == self::POSITION_BOTTOM_LEFT) {
            $positionY = ($this->getImageSrcHeight() - imagesy($watermark));
            imagecopymerge(
                $this->getImageHandler(),
                $watermark,
                $positionX, $positionY,
                0, 0,
                imagesx($watermark), imagesy($watermark),
                $this->getWatermarkImageOpacity()
            );
        }

        if ($repeat === false && $merged === false) {
            imagecopymerge(
                $this->getImageHandler(),
                $watermark,
                $positionX, $positionY,
                0, 0,
                imagesx($watermark), imagesy($watermark),
                $this->getWatermarkImageOpacity()
            );
        } else {
            $offsetX = $positionX;
            $offsetY = $positionY;
            while ($offsetY <= ($this->getImageSrcHeight()+imagesy($watermark))) {
                while ($offsetX <= ($this->getImageSrcWidth()+imagesx($watermark))) {
                    imagecopymerge(
                        $this->getImageHandler(),
                        $watermark,
                        $offsetX, $offsetY,
                        0, 0,
                        imagesx($watermark), imagesy($watermark),
                        $this->getWatermarkImageOpacity()
                    );
                    $offsetX += imagesx($watermark);
                }
                $offsetX = $positionX;
                $offsetY += imagesy($watermark);
            }
        }

        imagedestroy($watermark);
        $this->refreshImageDimensions();
    }

    public function crop($top=0, $left=0, $right=0, $bottom=0)
    {
        if ($left == 0 && $top == 0 && $right == 0 && $bottom == 0) {
            return;
        }

        $newWidth = $this->getImageSrcWidth() - $left - $right;
        $newHeight = $this->getImageSrcHeight() - $top - $bottom;

        $canvas = imagecreatetruecolor($newWidth, $newHeight);

        if ($this->getImageSrcFileType() == IMAGETYPE_PNG) {
            $this->_saveAlpha($canvas);
        }

        imagecopyresampled(
            $canvas,
            $this->getImageHandler(),
            0, 0, $left, $top,
            $newWidth, $newHeight,
            $newWidth, $newHeight
        );

        $this->setImageHandler($canvas);
        $this->refreshImageDimensions();
    }

    private function refreshImageDimensions()
    {
        $this->_imageSrcWidth = imagesx($this->getImageHandler());
        $this->_imageSrcHeight = imagesy($this->getImageHandler());
    }

    /* function __destruct()
    {
        @imagedestroy($this->getImageHandler());
    } */

    /*
     * Fixes saving PNG alpha channel
     */
    private function _saveAlpha($imageHandler)
    {
        $background = imagecolorallocate($imageHandler, 0, 0, 0);
        ImageColorTransparent($imageHandler, $background);
        imagealphablending($imageHandler, false);
        imagesavealpha($imageHandler, true);
    }
}
