<?php

namespace SierraKomodo\INIController\Tests;

use PHPUnit\Framework\TestCase;
use SierraKomodo\INIController\INILib;


/**
 * @coversDefaultClass \SierraKomodo\INIController\INILib
 */
class INILibTest extends TestCase
{
    protected $INILib;
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
    
    
    public function testConstructInstantiatesObject()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $file = new \SplFileObject($this->fileNamePrebuilt);
        
        $this->INILib = new INILib($file);
        self::assertInstanceOf(INILib::class, $this->INILib);
    }
    
    
    public function testParseINIData()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $file = new \SplFileObject($this->fileNamePrebuilt);
        
        $this->INILib = new INILib($file);
        self::assertEquals($this->filePrebuiltArray, $this->INILib->dataArray());
    }
}
