<?php

namespace SierraKomodo\INILib\Tests;

use PHPUnit\Framework\TestCase;
use SierraKomodo\INILib\INILib;

/**
 * @coversDefaultClass \SierraKomodo\INIController\src
 */
class INILibTest extends TestCase
{
    protected $INILib;
    protected $fileNamePrebuilt  = __DIR__ . DIRECTORY_SEPARATOR . "test_prebuilt.ini";
    protected $fileNameFake      = __DIR__ . DIRECTORY_SEPARATOR . "test_fake.ini";
    protected $fileNameEmpty     = __DIR__ . DIRECTORY_SEPARATOR . "test_empty.ini";
    protected $filePrebuiltContents;
    protected $filePrebuiltArray = array(
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
    
    
    protected function SetUp()
    {
        $this->filePrebuiltContents = str_replace("\r\n", PHP_EOL, <<<INI
[Section1]
Key1=Value1
Key2=Value2
Key3=Value3

[Section2]
KeyA=1
KeyB=2
KeyC=3


INI
        );
    }
    
    
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
    
    
    public function testGenerateFileContent()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $file = new \SplFileObject($this->fileNamePrebuilt);
        
        $this->INILib = new INILib($file);
        
        self::assertEquals($this->filePrebuiltContents, $this->INILib->generateFileContent());
    }
    
    
    public function testSetKeyChangesExistingEntry()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $file                          = new \SplFileObject($this->fileNamePrebuilt);
        $testArray                     = $this->filePrebuiltArray;
        $testArray['Section1']['Key2'] = 'Apple';
        $testArray['Section2']['KeyA'] = 'Orange';
        
        $this->INILib = new INILib($file);
        $this->INILib->setKey('Section1', 'Key2', 'Apple');
        $this->INILib->setKey('Section2', 'KeyA', 'Orange');
        
        self::assertEquals($testArray, $this->INILib->dataArray());
    }
    
    
    public function testSetKeyAddsNewEntry()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $file                          = new \SplFileObject($this->fileNamePrebuilt);
        $testArray                     = $this->filePrebuiltArray;
        $testArray['Section3']['Key2'] = 'Apple';
        $testArray['Section3']['KeyA'] = 'Orange';
        
        $this->INILib = new INILib($file);
        $this->INILib->setKey('Section3', 'Key2', 'Apple');
        $this->INILib->setKey('Section3', 'KeyA', 'Orange');
        
        self::assertEquals($testArray, $this->INILib->dataArray());
    }
    
    
    public function testSetKeyStripsWhitespace()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $file                          = new \SplFileObject($this->fileNamePrebuilt);
        $testArray                     = $this->filePrebuiltArray;
        $testArray['Section3']['Key2'] = 'Apple';
        
        $this->INILib = new INILib($file);
        $this->INILib->setKey('  Section3 ', "\tKey2\r", "Apple\r\n");
        
        self::assertEquals($testArray, $this->INILib->dataArray());
    }
    
    
    public function testFetchEntry()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $file      = new \SplFileObject($this->fileNamePrebuilt);
        $testArray = $this->filePrebuiltArray;
        
        $this->INILib = new INILib($file);
        
        self::assertEquals($testArray['Section1']['Key2'], $this->INILib->fetchEntry('Section1', 'Key2'));
        self::assertEquals($testArray['Section2']['KeyC'], $this->INILib->fetchEntry('Section2', 'KeyC'));
    }
    
    
    public function testFetchEntryReturnsNullForEmptyKey()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $file      = new \SplFileObject($this->fileNamePrebuilt);
        $testArray = $this->filePrebuiltArray;
        
        $this->INILib = new INILib($file);
        
        self::assertEquals(null, $this->INILib->fetchEntry('Section3', 'Foo'));
    }
    
    
    public function testDeleteKey()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $file      = new \SplFileObject($this->fileNamePrebuilt);
        $testArray = $this->filePrebuiltArray;
        unset($testArray['Section2']['KeyB']);
        
        $this->INILib = new INILib($file);
        $this->INILib->deleteKey('Section2', 'KeyB');
        $this->INILib->deleteKey('Section3', 'NonExistantKey');
        
        self::assertEquals($testArray, $this->INILib->dataArray());
    }
    
    
    public function testSaveData()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $file           = new \SplFileObject($this->fileNamePrebuilt, 'r+');
        $expectedString = str_replace("\r\n", PHP_EOL, <<<INI
[Section1]
Key1=Value1
Key2=Value2
Key3=Value3

[Section2]
KeyA=1
KeyB=2
KeyC=3

[Section3]
Foo=Bar


INI
        );
        
        $this->INILib = new INILib($file);
        $this->INILib->setKey('Section3', 'Foo', 'Bar');
        $this->INILib->saveData();
        
        $fileContent = str_replace("\r\n", PHP_EOL, file_get_contents($this->fileNamePrebuilt));
        self::assertEquals($expectedString, $fileContent);
    }
}
