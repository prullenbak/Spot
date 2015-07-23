<?php
namespace Spot;

class ConvertsCoordinates
{
    /**
    * Convert a DD MM.MMM formatted string to a standard google-maps style coordinate (DD.DDDDDD)
    *
    * @param string $coord
    *
    * @return double
    */
    public function DMtoDeg($coord)
    {
        $coord = strtolower($coord);
        $coordArray = $this->filterAndExplode($coord);

        $coordDeg = $coordArray[0];
        $coordMin = (double) $coordArray[1];
        $result = $coordDeg+($coordMin/60);
        $result = $this->fixForSouthWest($result, $coord);

        return round($result, 6);
    }

    /**
    * Convert a DD MM SS.SSS formatted string to a standard google-maps style coordinate (DD.DDDDDD)
    *
    * @param string $coord
    *
    * @return double
    */
    public function DMStoDeg($coord)
    {
        $coord = strtolower($coord);
        $coordArray = $this->filterAndExplode($coord);

        $coordDeg = (double) $coordArray[0];
        $coordMin = (double) $coordArray[1];
        $coordSec = (double) $coordArray[2];

        $result = $coordDeg + ($coordMin/60) + ($coordSec/3600);
        $result = $this->fixForSouthWest($result, $coord);

        return round($result, 6);
    }


    private function fixForSouthWest($result, $coord)
    {
        if(strpos($coord, 's') > -1 OR strpos($coord, 'w') > -1) {
            $result = -$result;
        }

        return $result;
    }


    private function filterAndExplode($coord)
    {
        $filteredcoord = str_replace(array('\'', '"'), ' ', $coord);
        $filteredcoord = str_replace(',','.', $filteredcoord);
        $filteredcoord = preg_replace("/[^0-9 .]/", '', $filteredcoord);

        return explode(' ', $filteredcoord);
    }

}

