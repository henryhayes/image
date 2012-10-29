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
 * @package    ProcessorTest
 * @copyright  Copyright (c) 2012 PHP Image Manipulation & Processing Library
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @since      Sunday, 28 October 2012
 */
/**
 * @see Image_Processor
 */
require_once 'Image/Processor.php';
/**
 * Image processor test class.
 *
 * @category   Image
 * @package    ProcessorTest
 * @copyright  Copyright (c) 2012 PHP Image Manipulation & Processing Library
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class Image_ProcessorTest extends PHPUnit_Framework_TestCase
{
    public function testOpen()
    {
        $fileName = 'non-existent-file-name.fle';

        $adapter = $this->getMock('Image_Processor_Adapter_Interface');
        $adapter->expects($this->exactly(1))
                ->method('open')
                ->with($this->equalTo($fileName));

        $sut = Image_Processor::getInstance();
        $sut->setAdapter($adapter);
        $this->assertInstanceOf('Image_Processor', $sut->open($fileName));
    }

    public function testSave()
    {
        $destination = '/my/destination';
        $newFileName = 'file.ext';

        $adapter = $this->getMock('Image_Processor_Adapter_Interface');
        $adapter->expects($this->exactly(1))
                ->method('save')
                ->with($this->equalTo($destination), $this->equalTo($newFileName));

        $sut = Image_Processor::getInstance();
        $sut->setAdapter($adapter);
        $this->assertInstanceOf('Image_Processor', $sut->save($destination, $newFileName));
    }

    public function testDisplay()
    {
        $adapter = $this->getMock('Image_Processor_Adapter_Interface');
        $adapter->expects($this->exactly(1))
                ->method('display');

        $sut = Image_Processor::getInstance();
        $sut->setAdapter($adapter);
        $this->assertInstanceOf('Image_Processor', $sut->display());
    }
    public function testRotate()
    {
        $angle = 20;

        $adapter = $this->getMock('Image_Processor_Adapter_Interface');
        $adapter->expects($this->exactly(1))
                ->method('rotate')
                ->with($this->equalTo($angle));

        $sut = Image_Processor::getInstance();
        $sut->setAdapter($adapter);
        $this->assertInstanceOf('Image_Processor', $sut->rotate($angle));
    }

    public function testCrop()
    {
        $top=10;
        $left=15;
        $right=11;
        $bottom=30;

        $adapter = $this->getMock('Image_Processor_Adapter_Interface');
        $adapter->expects($this->exactly(1))
                ->method('crop')
                ->with($this->equalTo($top), $this->equalTo($left), $this->equalTo($right), $this->equalTo($bottom));

        $sut = Image_Processor::getInstance();
        $sut->setAdapter($adapter);
        $this->assertInstanceOf('Image_Processor', $sut->crop($top, $left, $right, $bottom));
    }

    public function testResize()
    {
        $width = 400;
        $height = 300;

        $adapter = $this->getMock('Image_Processor_Adapter_Interface');
        $adapter->expects($this->exactly(1))
                ->method('resize')
                ->with($this->equalTo($width), $this->equalTo($height));

        $sut = Image_Processor::getInstance();
        $sut->setAdapter($adapter);
        $this->assertInstanceOf('Image_Processor', $sut->resize($width, $height));
    }

    public function testGetMimeType()
    {
        $adapter = $this->getMock('Image_Processor_Adapter_Interface');
        $adapter->expects($this->exactly(1))
                ->method('getMimeType')
                ->will($this->returnValue('testing'));

        $sut = Image_Processor::getInstance();
        $sut->setAdapter($adapter);
        $this->assertEquals('testing', $sut->getMimeType());
    }
}