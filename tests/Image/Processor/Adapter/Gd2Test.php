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
 * @subpackage Adapter_Gd2Test
 * @copyright  Copyright (c) 2012 PHP Image Manipulation & Processing Library
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @since      Saturday, 27 October 2012
 */
/**
 * @see vfsStream
 */
require_once 'vfsStream/vfsStream.php';
/**
 * @see Image_Processor_Adapter_Gd2
 */
require_once 'Image/Processor/Adapter/Gd2.php';
/**
 * Image processor adapter Gd2 test class.
 *
 * @category   Image
 * @package    Processor
 * @subpackage Adapter_Gd2Test
 * @copyright  Copyright (c) 2012 PHP Image Manipulation & Processing Library
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class Image_Processor_Adapter_Gd2Test extends PHPUnit_Framework_TestCase
{
    /**
     * Tests the open method proxies to the setFileName() method.
     */
    public function testOpen()
    {
        $fileNameString = 'some-testing-string';

        $sut = $this->getMock('Image_Processor_Adapter_Gd2', array('setFileName'));
        $sut->expects($this->once())
            ->method('setFileName')
            ->with($this->equalTo($fileNameString));

        $sut->open($fileNameString);
    }

    /**
     * Tests that setImageHandler with non-resource parameter
     * the correct exception is thrown with the correct message.
     */
    public function testSetImageHandlerWithoutResource()
    {
        $adapter = new Image_Processor_Adapter_Gd2();

        $this->setExpectedException(
            'UnexpectedValueException',
            'Image handler passed was not a resource'
        );

        $adapter->setImageHandler('not a resource');
    }

    /**
     * Tests that when we call setImageHandler() with non 'gd' resource
     * the correct exception is thrown with the correct message.
     */
    public function testSetImageHandlerWithIncorrectResource()
    {
        $sut = new Image_Processor_Adapter_Gd2();

        $this->setExpectedException(
            'UnexpectedValueException',
            "Image handler was not correct type, 'gd' expected, 'stream' provided"
        );

        $fp = @fopen(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'image_processor_php_test.tmp', 'w+');
        if (false === $fp) {
            $this->fail('This test cannot run as we cannot write a file to the system temp dir.');
        }
        $sut->setImageHandler($fp);
    }

    /**
     * Tests that the set/get image handler methods work as expected with a 'gd' resource.
     */
    public function testSetGetImageHandler()
    {
        $sut = new Image_Processor_Adapter_Gd2();

        $ih = imagecreatetruecolor(1, 1);
        $this->assertInstanceOf(get_class($sut), $sut->setImageHandler($ih));
        $this->assertSame($ih, $sut->getImageHandler());
    }

    /**
     * Tests that the full integration of the resize method works as expected.
     *
     * @dataProvider resizeDataProvider
     */
    public function testResizeIntegration($resizeX, $resizeY, $keepFrame, $keepAspect,
        $constrainOnly, $resultX, $resultY)
    {

        $tempImage = $this->createTemporaryImage();
        list($origWidth, $origHeight, $origFileType) = getimagesize($tempImage);
        if ($origWidth != 400 || $origHeight != 300 || $origFileType != IMAGETYPE_JPEG) {
            $this->fail('The temporay image was not what was expected. This test cannot continue.');
        }

        /**
         * Creates box, keeps aspect ratio and
         */
        $sut = new Image_Processor_Adapter_Gd2();
        $sut->setFileName($tempImage);
        $sut->setKeepFrame($keepFrame); // Make a box.
        $sut->setKeepAspectRatio($keepAspect); // Maintain aspect ratio -ignores height
        $sut->setConstrainOnly($constrainOnly); // Do not make image smaller.
        $sut->resize($resizeX, $resizeY);
        $gd = $sut->getImageHandler();

        $this->assertEquals($resultX, imagesx($gd));
        $this->assertEquals($resultY, imagesy($gd));

    }

    public function resizeDataProvider()
    {
        return array(
            array(200, 100, true, true, true, 200, 100), // Make a box
            array(200, 300, false, false, false, 200, 300), // Don't constrain
            array(300, 300, false, true, false, 300, 225), // Constrain
        );
    }

    public function createTemporaryImage()
    {
        $tmpDir = sys_get_temp_dir();
        $fileName = $tmpDir . DIRECTORY_SEPARATOR . 'image_project_unit_test_' . time() . rand() . '.jpg';
        $gd = imagecreatetruecolor(400, 300);
        imagefill($gd, 50, 50, 1);

        imagejpeg($gd, $fileName, 100);
        imagedestroy($gd);

        return $fileName;
    }
}