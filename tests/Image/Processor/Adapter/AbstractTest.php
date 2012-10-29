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
 * @subpackage Adapter_AbstractTest
 * @copyright  Copyright (c) 2012 PHP Image Manipulation & Processing Library
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @since      Sunday, 28 October 2012
 */
/**
 * @see vfsStream
 */
require_once 'vfsStream/vfsStream.php';
/**
 * @see Image_Processor_Adapter_Gd2
 */
require_once 'Image/Processor/Adapter/Abstract.php';
/**
 * Image processor adapter abstract test class.
 *
 * @category   Image
 * @package    Processor
 * @subpackage Adapter_AbstractTest
 * @copyright  Copyright (c) 2012 PHP Image Manipulation & Processing Library
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class Image_Processor_Adapter_AbstractTest extends PHPUnit_Framework_TestCase
{

    public function testDisplay()
    {
        $abstract = $this->getSutMock(array(), array('render'));
        $abstract->expects($this->once())
            ->method('render')
            ->will($this->returnValue('hello'));

        $this->expectOutputString('hello');
        $abstract->display();
    }

    public function testToString()
    {
        $abstract = $this->getSutMock(array(), array('render'));
        $abstract->expects($this->once())
            ->method('render')
            ->will($this->returnValue('hello'));

        $this->expectOutputString('hello');
        echo $abstract;
    }
    /**
     * Tests setting file name with empty file name throws correct exception.
     */
    public function testSetFileNameWithEmptyFileName()
    {
        $abstract = $this->getSutMock();

        $this->setExpectedException(
            'Image_Processor_Adapter_Exception',
            'The file name / location was empty'
        );

        $abstract->setFileName('');
    }

    /**
     * Tests setting file name with unreadable file throws correct exception.
     */
    public function testSetFileNameWithUnreadableFile()
    {
        $fileName = $this->createTempVfsFile(0222); // No-one can read, but all write

        $abstract = $this->getSutMock();

        $this->setExpectedException(
            'Image_Processor_Adapter_Exception',
            "The file name / location '{$fileName}' was not readable"
        );

        $abstract->setFileName($fileName);
    }

    /**
     * Tests getFileName when not set throws exception.
     */
    public function testGetFileNameWhenNotSet()
    {
        $abstract = $this->getSutMock();

        $this->setExpectedException(
            'Image_Processor_Adapter_Exception',
            'The source file location was not set, you must set the location before attempting to use it'
        );

        $abstract->getFileName();
    }

    public function testGetMimeType()
    {
        $mimeType = 'image/jpeg';

        $abstract = $this->getSutMock(array(), array('_imageTypeToMimeType'));
        $abstract->expects($this->exactly(1))
                 ->method('_imageTypeToMimeType')
                 ->will($this->returnValue($mimeType));

        $this->assertEquals($mimeType, $abstract->getMimeType());
    }

    /**
     * Tests set/get file name with existent file.
     */
    public function testSetGetFileName()
    {
        $fileName = $this->createTempVfsFile(0777);

        $abstract = $this->getSutMock(array(), array('_getFileAttributes', '_getImageStatistics'));
        $abstract->expects($this->once())
                 ->method('_getFileAttributes');
        $abstract->expects($this->once())
                 ->method('_getImageStatistics');

        $this->assertInstanceOf(get_class($abstract), $abstract->setFileName($fileName));
        $this->assertEquals($fileName, $abstract->getFileName());
    }

    /**
     * Tests getters and setters.
     */
    public function testOptionsSettersGetters()
    {
        $abstract = $this->getSutMock();

        // Watermark
        $watermarkPosition = null;
        $watermarkImageOpacity = null;
        $watermarkWidth = null;
        $watermarkHeigth = null;

        // Watermark
        $this->assertEquals($watermarkPosition, $abstract->getWatermarkPosition());
        $this->assertEquals($watermarkImageOpacity, $abstract->getWatermarkImageOpacity());
        $this->assertEquals($watermarkWidth, $abstract->getWatermarkWidth());
        $this->assertEquals($watermarkHeigth, $abstract->getWatermarkHeigth());

        $this->assertInstanceOf(
            get_class($abstract),
            $abstract->setWatermarkPosition(Image_Processor_Adapter_Abstract::POSITION_CENTER)
        );
        $this->assertEquals(Image_Processor_Adapter_Abstract::POSITION_CENTER, $abstract->getWatermarkPosition());

        $this->assertInstanceOf(get_class($abstract), $abstract->setWatermarkImageOpacity(11));
        $this->assertEquals(11, $abstract->getWatermarkImageOpacity());

        $this->assertInstanceOf(get_class($abstract), $abstract->setWatermarkWidth(51));
        $this->assertEquals(51, $abstract->getWatermarkWidth());

        $this->assertInstanceOf(get_class($abstract), $abstract->setWatermarkHeigth(52));
        $this->assertEquals(52, $abstract->getWatermarkHeigth());

        // Aspect ratio etc
        $keepAspectRatio  = true;
        $keepFrame        = false;
        $keepTransparency = false;
        $constrainOnly    = true;
        $quality          = 80;
        $backgroundColor  = array(255, 255, 255);

        // Aspect ratio etc
        $this->assertEquals($keepAspectRatio, $abstract->getKeepAspectRatio());
        $this->assertEquals($keepFrame, $abstract->getKeepFrame());
        $this->assertEquals($keepTransparency, $abstract->getKeepTransparency());
        $this->assertEquals($constrainOnly, $abstract->getConstrainOnly());
        $this->assertEquals($quality, $abstract->getQuality());
        $this->assertEquals($backgroundColor, $abstract->getBackgroundColor());

        $this->assertInstanceOf(get_class($abstract), $abstract->setKeepAspectRatio(false));
        $this->assertFalse($abstract->getKeepAspectRatio());

        $this->assertInstanceOf(get_class($abstract), $abstract->setKeepFrame(true));
        $this->assertTrue($abstract->getKeepFrame());

        $this->assertInstanceOf(get_class($abstract), $abstract->setKeepTransparency(true));
        $this->assertTrue($abstract->getKeepTransparency());

        $this->assertInstanceOf(get_class($abstract), $abstract->setConstrainOnly(false));
        $this->assertFalse($abstract->getConstrainOnly());

        $this->assertInstanceOf(get_class($abstract), $abstract->setQuality(51));
        $this->assertEquals(51, $abstract->getQuality());

        // Tests setBackgroundColor and getBackgroundColor; exceptions tested separately.
        $color = array(200, 234, 255);
        $this->assertInstanceOf(get_class($abstract), $abstract->setBackgroundColor($color));
        $this->assertEquals($color, $abstract->getBackgroundColor());

        // Rotate background color test, named incorrectly in my opinion.
        $imageBackgroundColor = 0;
        $this->assertEquals($imageBackgroundColor, $abstract->getImageBackgroundColor());

        $this->assertInstanceOf(get_class($abstract), $abstract->setImageBackgroundColor(255));
        $this->assertEquals(255, $abstract->getImageBackgroundColor());
    }

    /**
     * Tests all of the getSrc* methods.
     */
    public function testGetImageSrcMethods()
    {
        // File / image statistics setters and getters
        $statistics = array(
            '100',
            '150',
            IMAGETYPE_GIF,
        );

        $abstract = $this->getSutMock(array(), array('_getImageSize'));
        $abstract->expects($this->exactly(3))
            ->method('_getImageSize')
            ->will($this->returnValue($statistics));

        $this->assertEquals('100', $abstract->getImageSrcWidth());
        $this->assertEquals('150', $abstract->getImageSrcHeight());
        $this->assertEquals(IMAGETYPE_GIF, $abstract->getImageSrcFileType());

        // File information getters, so setters
        $fileInfo = array('dirname' => 'dir_name1', 'basename' => 'file_name2');
        $abstract = $this->getSutMock(array(), array('_getpathInfo'));
        $abstract->expects($this->exactly(1))
                 ->method('_getpathInfo')
                 ->will($this->returnValue($fileInfo));

        $this->assertEquals('dir_name1', $abstract->getFileSrcPath());
        $this->assertEquals('file_name2', $abstract->getFileSrcName());
    }

    /**
     * Tests the possible exceptions that can be thrown by the setBackgroundColor method.
     */
    public function testSetBackgroundColorExceptions()
    {
        $abstract = $this->getSutMock();

        // Too few array elements.
        try {
            $abstract->setBackgroundColor(array('one', 'two'));
        } catch (InvalidArgumentException $ex) {
            $this->assertEquals(
                'Background colour must be an array with 3 elements containing valid RGB values.',
                $ex->getMessage()
            );
        }

        // Non-RGB compatible array values
        try {
            $abstract->setBackgroundColor(array(255, 255, 256));
        } catch (InvalidArgumentException $ex) {
            $this->assertEquals(
                "Colour must be a valid RGB value. You passed '256'",
                $ex->getMessage()
            );
        }
    }

    /**
     * Test for the {@see getOriginalWidth()} and {@see getOriginalHeight()} methods.
     *
     * @deprecated To be removed when we confirm that they are no longer needed.
     */
    public function testGetOriginalWidthHeightMethods()
    {
        $abstract = $this->getSutMock(array(), array('getImageSrcWidth'));
        $abstract->expects($this->exactly(1))
                 ->method('getImageSrcWidth')
                 ->will($this->returnValue('testing1'));
        $this->assertEquals('testing1', $abstract->getOriginalWidth());

        $abstract = $this->getSutMock(array(), array('getImageSrcHeight'));
        $abstract->expects($this->exactly(1))
                 ->method('getImageSrcHeight')
                 ->will($this->returnValue('testing2'));
        $this->assertEquals('testing2', $abstract->getOriginalHeight());
    }

    /**
     * Reurns a mock object of the abstract class.
     *
     * @param  array $arguments
     * @param  array $mockedMethods
     * @return Image_Processor_Adapter_Abstract
     */
    public function getSutMock(array $arguments = array(), $mockedMethods = array())
    {
        srand(time() . rand(1, 150111));

        return $this->getMockForAbstractClass(
            'Image_Processor_Adapter_Abstract',
            $arguments,
            'Image_Processor_Adapter_Abstract_' . rand(),
            TRUE/* $callOriginalConstructor =  */,
            TRUE/* $callOriginalClone =  */,
            TRUE/* $callAutoload =  */,
            $mockedMethods
        );
    }

    /**
     * Creates a temp file for use in the above unit tests using vfsStream.
     *
     * @param  string $permissions
     * @throws Exception
     * @return string Name of file created
     */
    public function createTempVfsFile($permissions = 0777)
    {
        vfsStream::setup('image_processor_temp');
        $fileName = 'image_processor_php_test_' . rand() . '.tmp';
        $memoryFileName = vfsStream::url('image_processor_temp' . DIRECTORY_SEPARATOR . $fileName);

        $fp = @fopen($memoryFileName, 'w+');
        fwrite($fp, 'temporary_file_contents');
        if (false === $fp) {
            throw new Exception('This test cannot run as we cannot write a file the vfs temporary file.');
        }
        vfsStreamWrapper::getRoot()->getChild($fileName)->chmod($permissions);

        return $memoryFileName;
    }
}