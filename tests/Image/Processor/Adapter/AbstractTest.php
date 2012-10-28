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
    public function testSetFileNameWithUnreadabelFile()
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