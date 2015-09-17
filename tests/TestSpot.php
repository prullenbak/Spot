<?php

require __DIR__.'/../vendor/autoload.php';

use Spot\Spot;

class TestSpot extends PHPUnit_Framework_TestCase
{


    public function testCanBeConstructed()
    {
        ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');

        $spot = new Spot();
        $this->assertInstanceOf('Spot\Spot', $spot);

        $spot = Spot::fromDM('N51º 12.123','E004º 14.432');
        $this->assertInstanceOf('Spot\Spot',$spot);


        $spot = Spot::fromDMS('N51° 12 7.38','E4° 14 25.92');
        $this->assertInstanceOf('Spot\Spot',$spot);



        $spot = Spot::fromExif($this->getTestExif());
        $this->assertInstanceOf('Spot\Spot',$spot);

        $this->assertEquals('51.127603',$spot->latitude());
        $this->assertEquals('5.890669',$spot->longitude());


    }

    public function testCalclulateDistance()
    {
        $spot = new Spot();
        $spot2 = new Spot();
        $this->assertEquals(0,$spot->distanceInMetersTo($spot2));

        $sliedrecht = new Spot(51.823358, 4.783979);
        $sliedrecht2 = new Spot(51.824605, 4.775439);

        $this->assertEquals(603,$sliedrecht->distanceInMetersTo($sliedrecht2));
        $this->assertEquals(1,$sliedrecht->distanceInKilometersTo($sliedrecht2));
        $this->assertEquals(0.6,$sliedrecht->distanceInKilometersTo($sliedrecht2,1));


        $newyork = new Spot(40.704354, -74.009550);
        $london = new Spot(51.500920, -0.126765);

        $this->assertEquals(5573,$newyork->distanceInKilometersTo($london));

    }

    public function testCalucateBearing(){
        $sliedrecht = new Spot(51.823358, 4.783979);
        $london = new Spot(51.500920, -0.126765);
        $newyork = new Spot(40.704354, -74.009550);

        $this->assertEquals(263,$sliedrecht->getBearingTo($london,1));
        $this->assertEquals('W',$sliedrecht->getCompassDirectionTo($london));

        $this->assertEquals('E',$newyork->getCompassDirectionTo($london));
    }


    private function getTestExif()
    {
        return [
            'GPSLatitudeRef' => 'N',
            'GPSLatitude' => [
                0 => '51/1',
                1 => '7/1',
                2 => '3937/100'
            ],
            'GPSLongitudeRef' => 'E',
            'GPSLongitude' => [
                0 => '5/1',
                1 => '53/1',
                2 => '2641/100'
            ]
        ];


    }

}
?>