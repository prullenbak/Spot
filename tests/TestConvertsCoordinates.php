<?php

require __DIR__.'/../vendor/autoload.php';

use Spot\ConvertsCoordinates;

class TestSpot extends PHPUnit_Framework_TestCase
{


    public function testCanBeConstructed()
    {
        ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');

        $converter = new ConvertsCoordinates();
        $this->assertInstanceOf('Spot\ConvertsCoordinates', $converter);


        $this->assertEquals(51.20205,$converter->DMtoDeg('N51º 12.123'));
        $this->assertEquals(4.240533,$converter->DMStoDeg('E4° 14 25.92'));

        $this->assertEquals(37.770660,$converter->DMtoDeg('N37° 46.2396'));
        $this->assertEquals(-25.591239,$converter->DMStoDeg('W25° 35 28.46'));

        $this->assertEquals(-25.591239,$converter->DMStoDeg('W25° 35\'28.46"'));


    }



}
?>