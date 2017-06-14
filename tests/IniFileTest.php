<?php

namespace SierraKomodo\INILib\Tests;

use PHPUnit\Framework\TestCase;
use SierraKomodo\INILib\IniFile;

/**
 * @coversDefaultClass \SierraKomodo\INILib\IniFile
 */
class IniFileTest extends TestCase
{
    protected $iniFile;
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
        
        $this->iniFile = new IniFile($file);
        
        self::assertInstanceOf(IniFile::class, $this->iniFile);
    }
    
    
    public function testParseINIData()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $file = new \SplFileObject($this->fileNamePrebuilt);
        
        $this->iniFile = new IniFile($file);
        
        self::assertEquals($this->filePrebuiltArray, $this->iniFile->dataArray());
    }
    
    
    public function testGenerateFileContent()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $file = new \SplFileObject($this->fileNamePrebuilt);
        
        $this->iniFile = new IniFile($file);
        
        self::assertEquals($this->filePrebuiltContents, $this->iniFile->generateFileContent());
    }
    
    
    public function testSetKeyChangesExistingEntry()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $file                          = new \SplFileObject($this->fileNamePrebuilt);
        $testArray                     = $this->filePrebuiltArray;
        $testArray['Section1']['Key2'] = 'Apple';
        $testArray['Section2']['KeyA'] = 'Orange';
        
        $this->iniFile = new IniFile($file);
        $this->iniFile->setKey('Section1', 'Key2', 'Apple');
        $this->iniFile->setKey('Section2', 'KeyA', 'Orange');
        
        self::assertEquals($testArray, $this->iniFile->dataArray());
    }
    
    
    public function testSetKeyAddsNewEntry()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $file                          = new \SplFileObject($this->fileNamePrebuilt);
        $testArray                     = $this->filePrebuiltArray;
        $testArray['Section3']['Key2'] = 'Apple';
        $testArray['Section3']['KeyA'] = 'Orange';
        
        $this->iniFile = new IniFile($file);
        $this->iniFile->setKey('Section3', 'Key2', 'Apple');
        $this->iniFile->setKey('Section3', 'KeyA', 'Orange');
        
        self::assertEquals($testArray, $this->iniFile->dataArray());
    }
    
    
    public function testSetKeyStripsWhitespace()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $file                          = new \SplFileObject($this->fileNamePrebuilt);
        $testArray                     = $this->filePrebuiltArray;
        $testArray['Section3']['Key2'] = 'Apple';
        
        $this->iniFile = new IniFile($file);
        $this->iniFile->setKey('  Section3 ', "\tKey2\r", "Apple\r\n");
        
        self::assertEquals($testArray, $this->iniFile->dataArray());
    }
    
    
    public function testFetchEntry()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $file      = new \SplFileObject($this->fileNamePrebuilt);
        $testArray = $this->filePrebuiltArray;
        
        $this->iniFile = new IniFile($file);
        
        self::assertEquals($testArray['Section1']['Key2'], $this->iniFile->fetchEntry('Section1', 'Key2'));
        self::assertEquals($testArray['Section2']['KeyC'], $this->iniFile->fetchEntry('Section2', 'KeyC'));
    }
    
    
    public function testFetchEntryReturnsNullForEmptyKey()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $file      = new \SplFileObject($this->fileNamePrebuilt);
        $testArray = $this->filePrebuiltArray;
        
        $this->iniFile = new IniFile($file);
        
        self::assertEquals(null, $this->iniFile->fetchEntry('Section3', 'Foo'));
    }
    
    
    public function testDeleteKey()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $file      = new \SplFileObject($this->fileNamePrebuilt);
        $testArray = $this->filePrebuiltArray;
        unset($testArray['Section2']['KeyB']);
        
        $this->iniFile = new IniFile($file);
        $this->iniFile->deleteKey('Section2', 'KeyB');
        $this->iniFile->deleteKey('Section3', 'NonExistantKey');
        
        self::assertEquals($testArray, $this->iniFile->dataArray());
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
        
        $this->iniFile = new IniFile($file);
        $this->iniFile->setKey('Section3', 'Foo', 'Bar');
        $this->iniFile->saveData();
        
        $fileContent = str_replace("\r\n", PHP_EOL, file_get_contents($this->fileNamePrebuilt));
        self::assertEquals($expectedString, $fileContent);
    }
}
