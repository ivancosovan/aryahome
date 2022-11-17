<?
namespace Ctweb\YandexDelivery;

class Polygon {
    private $_mapCoords = array();
    private $_pixelCoords = array();

    function __construct($coords = array()) {
        if (!empty($coords)) {
            $this->setCoords($coords);
        }
    }

    function setCoords($coords) {
        $this->_mapCoords = $coords;
        $this->_pixelCoords = array_map(function ($point) {
            return $this->convertGeoToPixel($point[0], $point[1]);
        }, $this->_mapCoords);
    }

    function convertGeoToPixel($lat, $lon){
        $mapWidth = 400;
        $mapHeight = 260;

        $mapLonLeft = -180;
        $mapLonRight = 180;
        $mapLonDelta = $mapLonRight - $mapLonLeft;

        $mapLatBottom = -56.1700;
        $mapLatBottomDegree = $mapLatBottom * M_PI / 180;

        $x = ($lon - $mapLonLeft) * ($mapWidth / $mapLonDelta);

        $lat = $lat * M_PI / 180;
        $worldMapWidth = (($mapWidth / $mapLonDelta) * 360) / (2 * M_PI);
        $mapOffsetY = ($worldMapWidth / 2 * log((1 + sin($mapLatBottomDegree)) / (1 - sin($mapLatBottomDegree))));
        $y = $mapHeight - (($worldMapWidth / 2 * log((1 + sin($lat)) / (1 - sin($lat)))) - $mapOffsetY);

        return array($x, $y);
    }

    function getMapCoords() {
        return $this->_mapCoords;
    }

    function getPixelCoords() {
        return $this->_pixelCoords;
    }

    function getSVG($w, $h, $color = '#000') {
        $coords = $this->getPixelCoords();

        $minX = $maxX = null;
        $minY = $maxY = null;
        foreach ($coords as $i=>$point) {
            $minX = ($minX) ? min($point[0], $minX) : $point[0];
            $maxX = ($maxX) ? max($point[0], $maxX) : $point[0];
            $minY = ($minY) ? min($point[1], $minY) : $point[1];
            $maxY = ($maxY) ? max($point[1], $maxY) : $point[1];
        }

        $polyW = $maxX - $minX;
        $polyH = $maxY - $minY;
        $rectW = $w;
        $rectH = $h;

        foreach ($coords as $i=>$point) {
            $vec = array(($point[0]-$minX), ($point[1]-$minY));
            $coords[$i] = $vec;
        }



        $coords = array_map(function( $val ) {
            return join(',', $val);
        }, $coords);

        $coords = join(' ', $coords);

        $html = '<svg height="'.$rectH.'" width="'.$rectW.'" viewBox="0 0 '.$polyW.' '.$polyH.'">';
        $html .= '<polygon points="';
        $html .= $coords;
        $html .= '" style="fill:'.$color.';stroke:purple;stroke-width:0" />';
        $html .= '</svg>';

        return $html;
    }
}

class GeoJSON {
    private $data = array();
    private $polygons = array();

    function __construct($jsonString) {
        $parsed = json_decode($jsonString, true);
        if ($parsed) {
            $this->data = $parsed;

            $this->findPolygons($this->data);
        } else
            return null;
    }

    private function findPolygons($data) {
        if (is_array($data) && key_exists('type', $data) && key_exists('coordinates', $data)) {
            switch (strtolower($data['type'])) {
                case 'polygon':
                    foreach ($data['coordinates'] as $coords) {
                        if (!empty($coords) && is_array($coords)) {
                            $this->polygons[] = new \Ctweb\YandexDelivery\Polygon(array_map(function ($value) { return array_reverse($value); }, $coords));
                        }
                    }
                    break;
                case 'multipolygon':
                    foreach ($data['coordinates'] as $coords) {
                        if (!empty($coords) && is_array($coords)) {
                            foreach ($coords as $c) {
                                $this->polygons[] = new \Ctweb\YandexDelivery\Polygon(array_map(function ($value) { return array_reverse($value); }, $c));
                            }
                        }
                    }
                    break;
            }
        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->findPolygons($value);
            }
        }
    }

    function getPolygons() {
        return $this->polygons;
    }


}
?>