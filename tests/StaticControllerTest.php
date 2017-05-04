<?php

namespace SierraKomodo\INIController\Tests;

use PHPUnit\Framework\TestCase;
use SierraKomodo\INIController\StaticController;


/**
 * @coversDefaultClass \SierraKomodo\INIController\StaticController
 */
class StaticControllerTest extends TestCase
{
    protected $fileName = __DIR__ . DIRECTORY_SEPARATOR . "test.ini";
    
    
    protected function TearDown()
    {
        if (file_exists($this->fileName)) {
            unlink($this->fileName);
        }
    }
    
    
    /**
     * @covers ::set
     */
    public function testSetMakesValidNewFile()
    {
        StaticController::set($this->fileName, 'Section1', 'KeyA', 'String', true);
        $data = array(
            'Section1' => array(
                'KeyA' => 'String',
            )
        );
        
        $this->assertFileExists($this->fileName);
        $this->assertEquals($data, parse_ini_file($this->fileName, true));
    }
    
    
    /**
     * @covers ::set
     */
    public function testSetAddsNewKey()
    {
        StaticController::set($this->fileName, 'Section1', 'KeyA', 'String', true);
        StaticController::set($this->fileName, 'Section1', 'KeyB', 'Rope');
        $data = array(
            'Section1' => array(
                'KeyA' => 'String',
                'KeyB' => 'Rope',
            )
        );
        
        $this->assertEquals($data, parse_ini_file($this->fileName, true));
    }
    
    
    /**
     * @covers ::set
     */
    public function testSetAddsNewSection()
    {
        StaticController::set($this->fileName, 'Section1', 'KeyA', 'String', true);
        StaticController::set($this->fileName, 'Section1', 'KeyB', 'Rope');
        StaticController::set($this->fileName, 'Section2', 'Key1', 'Alpha');
        StaticController::set($this->fileName, 'Section2', 'Key2', 'Bravo');
        $data = array(
            'Section1' => array(
                'KeyA' => 'String',
                'KeyB' => 'Rope',
            ),
            'Section2' => array(
                'Key1' => 'Alpha',
                'Key2' => 'Bravo',
            )
        );
        
        $this->assertEquals($data, parse_ini_file($this->fileName, true));
    }
    
    
    /**
     * @covers ::set
     */
    public function testSetModifiesExistingKey()
    {
        StaticController::set($this->fileName, 'Section1', 'KeyA', 'String', true);
        StaticController::set($this->fileName, 'Section1', 'KeyB', 'Rope');
        StaticController::set($this->fileName, 'Section1', 'KeyB', 'Thread');
        $data = array(
            'Section1' => array(
                'KeyA' => 'String',
                'KeyB' => 'Thread',
            ),
        );
        
        $this->assertEquals($data, parse_ini_file($this->fileName, true));
    }
    
    
    /**
     * @covers ::fetchFile
     */
    public function testFetchFile()
    {
        StaticController::set($this->fileName, 'Section1', 'KeyA', 'String', true);
        StaticController::set($this->fileName, 'Section1', 'KeyB', 'Rope');
        StaticController::set($this->fileName, 'Section2', 'Key1', 'Alpha');
        StaticController::set($this->fileName, 'Section2', 'Key2', 'Bravo');
        $data = array(
            'Section1' => array(
                'KeyA' => 'String',
                'KeyB' => 'Rope',
            ),
            'Section2' => array(
                'Key1' => 'Alpha',
                'Key2' => 'Bravo',
            )
        );
        
        $this->assertEquals($data, StaticController::fetchFile($this->fileName));
    }
    
    
    /**
     * @covers ::fetchSection
     */
    public function testFetchSection()
    {
        StaticController::set($this->fileName, 'Section1', 'KeyA', 'String', true);
        StaticController::set($this->fileName, 'Section1', 'KeyB', 'Rope');
        StaticController::set($this->fileName, 'Section2', 'Key1', 'Alpha');
        StaticController::set($this->fileName, 'Section2', 'Key2', 'Bravo');
        $data = array(
            'Section1' => array(
                'KeyA' => 'String',
                'KeyB' => 'Rope',
            ),
            'Section2' => array(
                'Key1' => 'Alpha',
                'Key2' => 'Bravo',
            )
        );
        
        $this->assertEquals($data['Section2'], StaticController::fetchSection($this->fileName, 'Section2'));
        $this->assertEquals($data['Section1'], StaticController::fetchSection($this->fileName, 'Section1'));
    }
    
    
    /**
     * @covers ::fetchKey
     */
    public function testFetchKey()
    {
        StaticController::set($this->fileName, 'Section1', 'KeyA', 'String', true);
        StaticController::set($this->fileName, 'Section1', 'KeyB', 'Rope');
        StaticController::set($this->fileName, 'Section2', 'Key1', 'Alpha');
        StaticController::set($this->fileName, 'Section2', 'Key2', 'Bravo');
        $data = array(
            'Section1' => array(
                'KeyA' => 'String',
                'KeyB' => 'Rope',
            ),
            'Section2' => array(
                'Key1' => 'Alpha',
                'Key2' => 'Bravo',
            )
        );
        
        $this->assertEquals('Rope', StaticController::fetchKey($this->fileName, 'Section1', 'KeyB'));
        $this->assertEquals('Alpha', StaticController::fetchKey($this->fileName, 'Section2', 'Key1'));
    }
    
    
    /**
     * @covers ::delete
     */
    public function testDeleteDeletesKeys()
    {
        StaticController::set($this->fileName, 'Section1', 'KeyA', 'String', true);
        StaticController::set($this->fileName, 'Section1', 'KeyB', 'Rope');
        StaticController::set($this->fileName, 'Section2', 'Key1', 'Alpha');
        StaticController::set($this->fileName, 'Section2', 'Key2', 'Bravo');
        StaticController::delete($this->fileName, 'Section1', 'KeyA');
        StaticController::delete($this->fileName, 'Section2', 'Key2');
        $data = array(
            'Section1' => array(
                'KeyB' => 'Rope',
            ),
            'Section2' => array(
                'Key1' => 'Alpha',
            )
        );
        
        $this->assertEquals($data, parse_ini_file($this->fileName, true));
    }
    
    
    /**
     * @covers ::delete
     */
    public function testDeleteDeletesSections()
    {
        StaticController::set($this->fileName, 'Section1', 'KeyA', 'String', true);
        StaticController::set($this->fileName, 'Section1', 'KeyB', 'Rope');
        StaticController::set($this->fileName, 'Section2', 'Key1', 'Alpha');
        StaticController::set($this->fileName, 'Section2', 'Key2', 'Bravo');
        StaticController::delete($this->fileName, 'Section1');
        $data = array(
            'Section2' => array(
                'Key1' => 'Alpha',
                'Key2' => 'Bravo',
            )
        );
    
        $this->assertEquals($data, parse_ini_file($this->fileName, true));
    }
}