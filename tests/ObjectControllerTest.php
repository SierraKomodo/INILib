<?php

namespace SierraKomodo\INIController\Tests;

use PHPUnit\Framework\TestCase;
use SierraKomodo\INIController\ObjectController;


class ObjectControllerTest extends TestCase
{
    protected $controller;
    protected $fileName             = __DIR__ . DIRECTORY_SEPARATOR . "test.ini";
    protected $fileName2            = __DIR__ . DIRECTORY_SEPARATOR . "test2.ini";
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
        if (file_exists($this->fileName)) {
            unlink($this->fileName);
        }
        if (file_exists($this->fileName2)) {
            unlink($this->fileName2);
        }
    }
    
    
    public function testLoad()
    {
        file_put_contents($this->fileName, $this->filePrebuiltContents);
        
        $this->controller = new ObjectController();
        $this->assertEquals(true, $this->controller->load($this->fileName));
    }
    
    
    public function testFetchFile()
    {
        file_put_contents($this->fileName, $this->filePrebuiltContents);
        
        $this->controller = new ObjectController($this->fileName);
        $this->assertEquals($this->filePrebuiltArray, $this->controller->fetchFile());
    }
    
    
    public function testFetchSection()
    {
        file_put_contents($this->fileName, $this->filePrebuiltContents);
        
        $this->controller = new ObjectController($this->fileName);
        $this->assertEquals($this->filePrebuiltArray['Section2'], $this->controller->fetchSection('Section2'));
    }
    
    
    public function testFetchKey()
    {
        file_put_contents($this->fileName, $this->filePrebuiltContents);
        
        $this->controller = new ObjectController($this->fileName);
        $this->assertEquals($this->filePrebuiltArray['Section2']['KeyB'], $this->controller->fetchKey('Section2', 'KeyB'));
    }
    
    
    public function testSetUpdatesExistingKey()
    {
        file_put_contents($this->fileName, $this->filePrebuiltContents);
        
        $this->controller = new ObjectController($this->fileName);
        $this->controller->set('Section1', 'Key2', 'Apple');
        $this->assertEquals('Apple', $this->controller->fetchKey('Section1', 'Key2', 'Apple'));
    }
    
    
    public function testSetCreatesNewKey()
    {
        file_put_contents($this->fileName, $this->filePrebuiltContents);
        
        $this->controller = new ObjectController($this->fileName);
        $this->controller->set('Section1', 'Fruit', 'Apple');
        $this->assertEquals('Apple', $this->controller->fetchKey('Section1', 'Fruit', 'Apple'));
    }
    
    
    public function testSetCreatesNewSection()
    {
        file_put_contents($this->fileName, $this->filePrebuiltContents);
        
        $this->controller = new ObjectController($this->fileName);
        $this->controller->set('Food', 'Fruit', 'Apple');
        $this->assertEquals('Apple', $this->controller->fetchKey('Food', 'Fruit', 'Apple'));
    }
    
    
    public function testLoadReloadsUnchangedFile()
    {
        file_put_contents($this->fileName, $this->filePrebuiltContents);
        
        $this->controller = new ObjectController($this->fileName);
        $this->controller->set('Food', 'Fruit', 'Apple');
        $this->controller->load();
        $this->assertEquals($this->filePrebuiltArray, $this->controller->fetchFile());
    }
    
    
    public function testSaveSameFile()
    {
        file_put_contents($this->fileName, $this->filePrebuiltContents);
    
        $this->controller = new ObjectController($this->fileName);
        $this->controller->set('Food', 'Fruit', 'Apple');
        $this->controller->save();
        
        $array = $this->filePrebuiltArray;
        $array['Food']['Fruit'] = 'Apple';
        $this->assertEquals($array, parse_ini_file($this->fileName, true));
        
        $this->controller->load();
        $this->assertEquals($array, $this->controller->fetchFile());
    }
    
    
    public function testSaveNewFile()
    {
        file_put_contents($this->fileName, $this->filePrebuiltContents);
    
        $this->controller = new ObjectController($this->fileName);
        $this->controller->set('Food', 'Fruit', 'Apple');
        $this->controller->save($this->fileName2);
    
        $array = $this->filePrebuiltArray;
        $array['Food']['Fruit'] = 'Apple';
        $this->assertEquals($array, parse_ini_file($this->fileName2, true));
    
        $this->controller->load($this->fileName);
        $this->assertEquals($this->filePrebuiltArray, $this->controller->fetchFile());
        
        $this->controller->load($this->fileName2);
        $this->assertEquals($array, $this->controller->fetchFile());
    }
    
    
    public function testDeleteKey()
    {
        file_put_contents($this->fileName, $this->filePrebuiltContents);
        $array = $this->filePrebuiltArray;
        unset($array['Section1']['Key2']);
    
        $this->controller = new ObjectController($this->fileName);
        $this->controller->delete('Section1', 'Key2');
        $this->assertEquals($array, $this->controller->fetchFile());
    }
    
    
    public function testDeleteSection()
    {
        file_put_contents($this->fileName, $this->filePrebuiltContents);
        $array = $this->filePrebuiltArray;
        unset($array['Section2']);
    
        $this->controller = new ObjectController($this->fileName);
        $this->controller->delete('Section2');
        $this->assertEquals($array, $this->controller->fetchFile());
    }
}