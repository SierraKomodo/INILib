<?php

namespace SierraKomodo\INIController\Tests;

use PHPUnit\Framework\TestCase;
use SierraKomodo\INIController\Controller;


/**
 * @coversDefaultClass \SierraKomodo\INIController\Controller
 */
class ControllerTest extends TestCase
{
    protected $controller;
    protected $fileNamePrebuilt     = __DIR__ . DIRECTORY_SEPARATOR . "test_prebuilt.ini";
    protected $fileNameFake         = __DIR__ . DIRECTORY_SEPARATOR . "test_fake.ini";
    protected $fileNameEmpty        = __DIR__ . DIRECTORY_SEPARATOR . "test_empty.ini";
    protected $filePrebuiltContents = <<<INI
[Section1]
Key1=Value1
Key2=Value2
Key3=Value3

[Section2]
KeyA=1
KeyB=2
KeyC=3


INI;
    protected $filePrebuiltArray    = array(
        'Section1' => array(
            'Key1' => 'Value1',
            'Key2' => 'Value2',
            'Key3' => 'Value3',
        ),
        'Section2' => array(
            'KeyA' => '1',
            'KeyB' => '2',
            'KeyC' => '3',
        )
    );
    
    
    protected function TearDown()
    {
        if (file_exists($this->fileNamePrebuilt)) {
            unlink($this->fileNamePrebuilt);
        }
        if (file_exists($this->fileNameEmpty)) {
            unlink($this->fileNameEmpty);
        }
        if (file_exists($this->fileNameFake)) {
            unlink($this->fileNameFake);
        }
    }
    
    
    /* TEST generateFile() */
    /**
     * @covers ::generateFile
     */
    public function testGenerateFileMakesValidConversion()
    {
        $this->controller = new TestController();
        
        $this->controller->fileArray = $this->filePrebuiltArray;
        $this->controller->generateFile();
        
        $this->assertEquals($this->filePrebuiltContents, $this->controller->fileContent);
    }
    
    
    /* TEST readFile() */
    /**
     * @covers ::readFile
     */
    public function testReadFileFirstRunRequiresFilename()
    {
        $this->controller = new TestController();
        
        $this->expectException(\BadFunctionCallException::class);
        $this->controller->readFile();
    }
    
    
    /**
     * @covers ::readFile
     */
    public function testReadFileSetsClassProperties()
    {
        $this->controller = new TestController();
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        
        $this->controller->readFile($this->fileNamePrebuilt);
        $this->assertTrue(!empty($this->controller->file));
        $this->assertTrue(!empty($this->controller->fileArray));
    }
    
    
    /**
     * @covers ::readFile
     */
    public function testReadFileCannotLoadNonExistentFile()
    {
        $this->controller = new TestController();
        
        $this->expectException(\RuntimeException::class);
        $this->controller->readFile($this->fileNameFake);
    }
    
    
    /**
     * @covers ::readFile
     */
    public function testReadFileCanLoadEmptyFile()
    {
        $this->controller = new TestController();
        file_put_contents($this->fileNameEmpty, null);
        
        $this->assertTrue($this->controller->readFile($this->fileNameEmpty));
        $this->assertEquals(array(), $this->controller->fileArray);
    }
    
    
    /**
     * @covers ::readFile
     */
    public function testReadFileCanLoadFilledFile()
    {
        $this->controller = new TestController();
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        
        $this->assertTrue($this->controller->readFile($this->fileNamePrebuilt));
        $this->assertEquals($this->filePrebuiltArray, $this->controller->fileArray);
    }
    
    
    /* TEST writeFile() */
    /**
     * @covers ::writeFile
     */
    public function testWriteFileFirstRunRequiresFilename()
    {
        $this->controller = new TestController();
        
        $this->expectException(\BadFunctionCallException::class);
        $this->controller->writeFile();
    }
    
    
    /**
     * @covers ::writeFile
     */
    public function testWriteFileCreatesValidFile()
    {
        $this->controller = new TestController();
        
        $this->controller->fileArray = $this->filePrebuiltArray;
        $this->controller->writeFile($this->fileNamePrebuilt);
        
        $this->assertFileExists($this->fileNamePrebuilt);
        $this->assertEquals($this->filePrebuiltContents, file_get_contents($this->fileNamePrebuilt));
        $this->assertEquals($this->filePrebuiltArray, parse_ini_file($this->fileNamePrebuilt, true));
    }
}


class TestController extends Controller
{
    public $file = '', $fileArray = array(), $fileContent = '';
    
    
    public function readFile(string $parFile = null): bool
    {
        return parent::readFile($parFile);
    }
    
    
    public function writeFile(string $parFile = null): bool
    {
        return parent::writeFile($parFile);
    }
    
    
    public function generateFile(): bool
    {
        return parent::generateFile();
    }
}