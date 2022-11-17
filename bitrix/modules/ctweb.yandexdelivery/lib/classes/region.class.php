<?

namespace Ctweb\YandexDelivery;

use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;
use \Bitrix\Catalog\StoreTable;
use \Bitrix\Main\Web\Json;

require_once "geojson.php";

class Region
{
    private $id;
    private $active = false;
    private $name = '';
    private $description = '';
    private $color = '#000';
    private $polygon = null;
    private $price_fixed = 0;
    private $price = 0;
    private $price_free = 0;
    private $price_min = 0;
    private $stores = array();

    public function getActive()
    {
        return ($this->active) ? 'Y' : 'N';
    }

    public function setActive($active)
    {
        $this->active = !($active === 'N' || !$active);
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = trim($name);
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = trim($description);
        return $this;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setColor($color)
    {
        $this->color = trim($color);
        return $this;
    }

    public function getPolygon()
    {
        return $this->polygon;
    }

    public function setPolygon($polygon)
    {
        $this->polygon = $polygon;
        return $this;
    }

    public function createPolygon($coords = array())
    {
        $this->polygon = new Polygon($coords);
        return $this;
    }

    public function getPriceFixed()
    {
        return $this->price_fixed;
    }

    public function setPriceFixed($price_fixed)
    {
        $this->price_fixed = floatval($price_fixed);
        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = floatval($price);
        return $this;
    }

    public function getPriceFree()
    {
        return $this->price_free;
    }

    public function setPriceFree($price_free)
    {
        $this->price_free = floatval($price_free);
        return $this;
    }

    public function getPriceMin()
    {
        return $this->price_min;
    }

    public function setPriceMin($price_min)
    {
        $this->price_min = floatval($price_min);
        return $this;
    }

    public function getStores()
    {
        return $this->stores;
    }

    public function setStores($stores)
    {
        array_map(function ($val) {
            return intval($val);
        }, $stores);
        $stores = (!is_array($stores) || in_array(0, $stores)) ? array() : $stores;
        $this->stores = $stores;
        return $this;
    }

    static function GetByID($id)
    {
        global $DB;
        $id = intval($id);

        $sql = "SELECT * FROM `b_ctweb_yandexdelivery_region` WHERE ID = {$id};";
        $arFields = $DB->query($sql)->Fetch();

        if ($arFields !== false) {
            $region = new self();
            $region->id = intval($arFields['ID']);
            $region->setName($arFields['NAME'])
                ->setDescription($arFields['DESCRIPTION'])
                ->createPolygon(Json::decode($arFields['POINTS']))
                ->setColor($arFields['COLOR'])
                ->setActive($arFields['ACTIVE'])
                ->setPriceFixed($arFields['PRICE_FIXED'])
                ->setPrice($arFields['PRICE'])
                ->setPriceFree($arFields['PRICE_FREE'])
                ->setPriceMin($arFields['PRICE_MIN']);

            $region->LoadStores();

            return $region;
        }
        return null;
    }

    static function GetList($arOrder = array(), $arFilter = array())
    {
        global $DB;

        self::verifyFilter($arFilter);

        $sql = "SELECT REGION.* FROM `b_ctweb_yandexdelivery_region` REGION ";
        if (!empty($arFilter)) {
            $where = array();
	        $join = array();
	        $storeType = (Option::get('ctweb.yandexdelivery', 'FIELD_USE_CATALOG_STORES', 0) && Loader::includeModule('catalog')) ? 'I' : 'C';


	        foreach ($arFilter as $code => $filter) {
                if (mb_strpos($code, 'STORE.') !== false) {
                	if ($storeType === 'C') {
		                $join[] = "JOIN `b_ctweb_yandexdelivery_rs_link` LINK ON REGION.ID=LINK.REGION_ID AND LINK.TYPE='C'";
		                $join[] = "JOIN `b_ctweb_yandexdelivery_store` STORE ON STORE.ID=LINK.STORE_ID AND LINK.TYPE='C'";
		                $table = 'STORE';
		                $code = substr($code, strlen('STORE.'));
	                } else {
		                $join[] = "JOIN `b_ctweb_yandexdelivery_rs_link` LINK ON REGION.ID=LINK.REGION_ID AND LINK.TYPE='I'";
		                $join[] = "JOIN `b_catalog_store` STORE ON STORE.ID=LINK.STORE_ID AND LINK.TYPE='I'";
		                $table = 'STORE';
		                $code = substr($code, strlen('STORE.'));
	                }
                } elseif (mb_strpos($code, 'REGION.') !== false) {
                	$table = 'REGION';
	                $code = substr($code, strlen('REGION.'));

                } else {
	                $table = 'REGION';
                }
                if (is_numeric($filter))
                    $where[] = "$table.$code=$filter";
                elseif (is_string($filter)) {
                    if (in_array(($sign = substr($filter, 0, 1)), array('>', '<'))) {
                        $where[] = "$table.$code $sign ".substr($filter, 1);
                    } else {
                        $where[] = "$table.$code LIKE ('$filter')";
                    }
                }
                elseif (is_array($filter))
                    $where[] = "$table.$code IN ('" . implode("','", $filter) . "')";
            }
	        $sql .= implode(' ', $join);
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        if (empty($arOrder) || !is_array($arOrder)) {
            $arOrder = array('ID' => 'ASC');
        }
        $sql .= " ORDER BY";
        foreach ($arOrder as $code => $order) {
            $sql .= " REGION.$code $order,";
        }


        $sql = substr($sql, 0, strlen($sql) - 1) . ";";

        $res = $DB->query($sql);

        $arResult = array();
        while ($reg  = $res->Fetch()) {
            $region = new self();
            $region->id = intval($reg['ID']);
            $region->setName($reg['NAME'])
                ->setDescription($reg['DESCRIPTION'])
                ->createPolygon(Json::decode($reg['POINTS']))
                ->setColor($reg['COLOR'])
                ->setActive($reg['ACTIVE'])
                ->setPriceFixed($reg['PRICE_FIXED'])
                ->setPrice($reg['PRICE'])
                ->setPriceMin($reg['PRICE_MIN'])
                ->setPriceFree($reg['PRICE_FREE']);

            $region->LoadStores();

            $arResult[$region->id] = $region;
        }

        return $arResult;
    }

    function Save()
    {
        global $DB;

        $arFields = $this->PrepareFieldsForDB();

        if ($this->id > 0)
        {
            $DB->StartTransaction();
            $changed = $DB->Update(
                "b_ctweb_yandexdelivery_region",
                $arFields,
                "WHERE ID={$this->id}");
        } else {
            $ID = $DB->Insert("b_ctweb_yandexdelivery_region", $arFields);
            $ID = intval($ID);
        }

        if ($changed > 0 || $ID > 0) {
            $DB->Commit();
            if ($ID > 0)
                $this->id = $ID;

            $this->SaveStores();

            return $this->getId();
        }
        else  {
            $DB->Rollback();

            return false;
        }

    }

    private function LoadStores() {
        global $DB;

        $type = (Option::get('ctweb.yandexdelivery', 'FIELD_USE_CATALOG_STORES', 0)) ? 'I' : 'C';

        $sql = "SELECT STORE_ID FROM `b_ctweb_yandexdelivery_rs_link` WHERE REGION_ID={$this->id} AND TYPE='{$type}'";
        $res = $DB->Query($sql);

        $this->stores = array();
        while ($store = $res->Fetch()) {
            $this->stores[] = intval($store['STORE_ID']);
        }

        // ���� �� ������� ���������� ��������, �� �������� ��� ������
        if (empty($this->stores)) {
        	if ($type === 'C') { // ��������� ������
		        $sql = "SELECT ID FROM `b_ctweb_yandexdelivery_store` WHERE ACTIVE='Y'";
		        $res = $DB->Query($sql);

		        while ($store = $res->Fetch()) {
			        $this->stores[] = intval($store['ID']);
		        }
	        } else { // ���������� ������
        		if (Loader::includeModule('catalog')) {
        			$res = StoreTable::getList(array(
        				'filter' => array(
        					'ACTIVE' => 'Y'
				        ),
				        'select' => array('ID')
			        ));

			        while ($store = $res->Fetch()) {
				        $this->stores[] = intval($store['ID']);
			        }
		        }
	        }
        }
    }

    private function SaveStores() {
        global $DB;
        $stores = $this->stores;

        $type = (Option::get('ctweb.yandexdelivery', 'FIELD_USE_CATALOG_STORES', 0)) ? 'I' : 'C';


        $sql = "DELETE FROM `b_ctweb_yandexdelivery_rs_link` WHERE REGION_ID={$this->id} AND TYPE='{$type}'";
        $DB->Query($sql);

        if (!empty($stores)) {
            $sql = "INSERT INTO `b_ctweb_yandexdelivery_rs_link` (REGION_ID, STORE_ID, TYPE) VALUES ";
            $values = array();
            foreach ($stores as $store) {
                $values[] = "({$this->id}, {$store}, '$type')";
            }
            $sql .= implode(',', $values) . ';';
            $DB->Query($sql);
        }
    }

    private function PrepareFieldsForDB() {
        $arFields = array();
        $arFields['ID'] = intval($this->id);
        $arFields['ACTIVE'] = ($this->active) ? "'Y'" : "'N'";
        $arFields['NAME'] = "'".addslashes($this->name)."'";
        $arFields['DESCRIPTION'] = "'".addslashes($this->description)."'";
        $arFields['COLOR'] = "'".$this->color."'";
        if ($this->polygon)
            $arFields['POINTS'] = "'".Json::encode($this->polygon->getMapCoords())."'";
        else
            $arFields['POINTS'] = "'".Json::encode((new Polygon)->getMapCoords())."'";
        $arFields['PRICE_FIXED'] = $this->price_fixed;
        $arFields['PRICE'] = $this->price;
        $arFields['PRICE_FREE'] = $this->price_free;
        $arFields['PRICE_MIN'] = $this->price_min;

        return $arFields;
    }

    function GetFieldsArray()
    {
        $arResult = array();
        $arResult['ID'] = $this->id;
        $arResult['ACTIVE'] = ($this->active) ? 'Y' : 'N';
        $arResult['NAME'] = $this->name;
        $arResult['DESCRIPTION'] = $this->description;
        $arResult['COLOR'] = $this->color;
        $arResult['POINTS'] = $this->polygon->getMapCoords();
        $arResult['PRICE_FIXED'] = $this->price_fixed;
        $arResult['PRICE'] = $this->price;
        $arResult['PRICE_FREE'] = $this->price_free;
        $arResult['PRICE_MIN'] = $this->price_min;
        $arResult['STORES'] = $this->stores;

        return $arResult;
    }

    static function GetNew()
    {
        $color = '#';
        for ($i = 0; $i < 6; $i++) {
            $color .= str_split('0123456789ABCDEF')[rand(0, 15)];
        }

        $region = new self();
        $region->setActive(false);
        $region->setName('');
        $region->setPriceFixed(0);
        $region->setPrice(0);
        $region->setPriceFree(-1);
        $region->setPriceMin(0);
        $region->setColor($color);
        $region->setStores(array());
        $region->createPolygon();
        $region->id = 0;

        return $region;
    }

    static function DeleteByID($id) {
        global $DB;
        $id = intval($id);
        if ($id > 0) {
            $DB->Query("DELETE FROM b_ctweb_yandexdelivery_region WHERE ID=$id");
            $DB->Query("DELETE FROM b_ctweb_yandexdelivery_rs_link WHERE REGION_ID=$id");
        }
    }

    private static function verifyFilter(&$arFields) {
        array_intersect_key($arFields, array_flip(self::getColumns()));
    }
    private static function verifySelect(&$arFields) {
        array_intersect($arFields, self::getColumns());
    }
    private static function getColumns() {
        return array(
            'ID',
            'ACTIVE',
            'NAME',
            'DESCRIPTION',
            'COLOR',
            'POINTS',
            'PRICE_FIXED',
            'PRICE',
            'PRICE_FREE',
            'PRICE_MIN',
            'STORE.ID',
        );
    }

}

?>