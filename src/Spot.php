<?php
namespace Spot;

class Spot
{

    private $lat;
    private $lon;


    /**
    * Create a new Spot instance.
    *
    *
    * @param double $lat
    * @param double $lon
    */
    public function __construct($lat = null, $lon = null)
    {
        if($lat) {
            $this->lat = $lat;
        } else {
            $this->lat = 0.000000;
        }

        if($lon) {
            $this->lon = $lon;
        } else {
            $this->lon = 0.000000;
        }
    }

    /**
    * Create a Spot instance using strings in the DDº MM.MMM format
    *
    * @param string $lat
    * @param string $lon
    *
    * @return static
    */
    public static function fromDM($lat, $lon)
    {
        $converter = new ConvertsCoordinates;
        $latDeg = $converter->DMtoDeg($lat);
        $lonDeg = $converter->DMtoDeg($lon);

        return new static($latDeg, $lonDeg);
    }

    /**
    * Create a Spot instance using strings in the DDº MM SS.SS format
    *
    * @param string $lat
    * @param string $lon
    *
    * @return static
    */
    public static function fromDMS($lat, $lon)
    {
        $converter = new ConvertsCoordinates;
        $latDeg = $converter->DMStoDeg($lat);
        $lonDeg = $converter->DMStoDeg($lon);

        return new static($latDeg, $lonDeg);
    }


    /**
    * Create a Spot instance by taking the center of a list of other spot-objects
    *
    * @param array $spots - an array of Spot objects
    *
    * @return static
    */
    public static function center($spots)
    {

        $lat = 0.0;
        $lon = 0.0;
        $count = 0;

        if (!count($spots)) {
            return new static();
        }

        foreach ($spots as $spot) {
            if ($spot->hasLocation()) {
                $count++;
                $lat += $spot->latitude();
                $lon += $spot->longitude();
            }
        }

        $lat /= $count;
        $lon /= $count;

        return new static($lat,$lon);
    }

    /**
    * Create a Spot instance using an array of exif-data (from an image)
    *
    * @param array $exif
    *
    * @return static
    */
    public static function fromExif($exif)
    {

        if (! array_key_exists('GPSLatitudeRef', $exif)) {
            return new static();
        }

        $lat = $exif['GPSLatitude'];
        $latref = $exif['GPSLatitudeRef'];

        $latdegrees = $lat[0];
        $parts = explode('/', $latdegrees);
        $latdegrees = $parts[0] / $parts[1];

        $latminutes = $lat[1];
        $parts = explode('/', $latminutes);
        $latminutes = $parts[0] / $parts[1];

        $latseconds = $lat[2];
        $parts = explode('/', $latseconds);
        $latseconds = $parts[0] / $parts[1];

        $lon = $exif['GPSLongitude'];
        $lonref = $exif['GPSLongitudeRef'];

        $londegrees = $lon[0];
        $parts = explode('/', $londegrees);
        $londegrees = $parts[0] / $parts[1];

        $lonminutes = $lon[1];
        $parts = explode('/', $lonminutes);
        $lonminutes = $parts[0] / $parts[1];

        $lonseconds = $lon[2];
        $parts = explode('/', $lonseconds);
        $lonseconds = $parts[0] / $parts[1];

        return static::fromDMS("{$latref}{$latdegrees}° {$latminutes} {$latseconds}","{$lonref}{$londegrees}° {$lonminutes} {$lonseconds}");

    }

    ///////////////////////////////////////////////////////////////////
    ///////////////////////////// GETTERS /////////////////////////////
    ///////////////////////////////////////////////////////////////////

    /**
    * Get the latitude of this Spot
    *
    * @return double
    */
    public function latitude()
    {
        return $this->lat;
    }

    /**
    * Get the longitude of this Spot
    *
    * @return double
    */
    public function longitude()
    {
        return $this->lon;
    }

    public function hasLocation(){
      return ($this->lat OR $this->lon);
    }


    ///////////////////////////////////////////////////////////////////
    /////////////////////////// DIFFERENCES ///////////////////////////
    ///////////////////////////////////////////////////////////////////

    /**
    * Get the distance in meters
    *
    * @param Post $other
    * @param integer $abs the round-precision in decimals
    *
    * @return double
    */
    public function distanceInMetersTo(Spot $other, $precision = 0)
    {
        $lat1 = deg2rad($this->lat);
        $lat2 = deg2rad($other->latitude());
        $lon1 = deg2rad($this->lon);
        $lon2 = deg2rad($other->longitude());

        $r = 6372797; // mean radius of Earth in meters
        $dlat = $lat2 - $lat1;
        $dlng = $lon2 - $lon1;
        $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $meters = $r * $c;

        $meters = round($meters, $precision);
        return $meters;
    }

    /**
    * Get the distance in kilometers
    *
    * @param Post $other
    * @param integer $abs the round-precision in decimals
    *
    * @return double
    */
    public function distanceInKilometersTo(Spot $other, $precision = 0)
    {
        $meters = $this->distanceInMetersTo($other);
        return round($meters/1000, $precision);
    }

    /**
    * Get the direction to another spot in degrees
    *
    * @param Post $other
    * @param integer $abs the round-precision in decimals
    *
    * @return double
    */
    public function getBearingTo(Spot $other, $precision = 0)
    {
        $lat1 = $this->lat;
        $lon1 = $this->lon;
        $lat2 = $other->latitude();
        $lon2 = $other->longitude();

        $dLon = deg2rad($lon2) - deg2rad($lon1);
        //difference in the phi of latitudinal coordinates
        $dPhi = log(tan(deg2rad($lat2) / 2 + pi() / 4) / tan(deg2rad($lat1) / 2 + pi() / 4));
        //we need to recalculate $dLon if it is greater than pi
        if(abs($dLon) > pi()) {
            if($dLon > 0) {
                $dLon = (2 * pi() - $dLon) * -1;
            } else {
                $dLon = 2 * pi() + $dLon;
            }
         }
         //return the angle, normalized
         return (rad2deg(atan2($dLon, $dPhi)) + 360) % 360;
    }

    /**
    * Get the direction to another Spot as a compass bearing
    *
    * @param Post $other
    *
    * @return double
    */
    function getCompassDirectionTo(Spot $other)
    {
        $bearing = $this->getBearingTo($other);
        static $cardinals = array( 'N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW', 'N' );
        return $cardinals[round( $bearing / 45 )];
    }

}