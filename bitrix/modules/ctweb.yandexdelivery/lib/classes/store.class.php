<?
namespace Ctweb\YandexDelivery;

use \Bitrix\Main\Config\Option as Option;
use \Bitrix\Main\Web\Json;

class Store {

    private $id;
    private $active = false;
    private $custom = true;
    private $name = '';
    private $address = '';
    private $description = '';
    private $store_id = null;
    private $point = array();

    public function getId()
    {
        return $this->id;
    }

    public function isCustom() {
        return (bool) $this->custom;
    }

    public function getActive()
    {
        return $this->active;
    }

    public function setActive($active)
    {
        $this->active = $active && $active !== 'N';
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getStoreId()
    {
        return $this->store_id;
    }

    public function setStoreId($store_id)
    {
        $this->store_id = $store_id;
        return $this;
    }

    public function getPoint()
    {
        return $this->point;
    }

    public function setPoint($point)
    {
        $this->point = $point;
        return $this;
    }

    static function GetByID($id)
    {
        $id = intval($id);
        return reset(self::GetList(array(), array('ID' => $id)));
    }


    public function GetList($arOrder = array(), $arFilter = array()) {
        $bModlueCatalog = \Bitrix\Main\Loader::includeModule('catalog');
        $bCatalogStores = $bModlueCatalog && (Option::get('ctweb.yandexdelivery', 'FIELD_USE_CATALOG_STORES', 0));

        if ($bCatalogStores) {
            return self::GetListInner($arOrder, $arFilter);
        } else {
            return self::GetListCustom($arOrder, $arFilter);
        }
    }

    public function GetListInner($arOrder = array(), $arFilter = array()) {
        $resCatalogStores = \CCatalogStore::GetList(
            $arOrder,
            array_merge(['ISSUING_CENTER' => 'Y'], $arFilter),
            false,
            false,
            array()
        );

        $arResult = [];
        while ($st = $resCatalogStores->Fetch()) {
            $store = new self();
            $store->id = intval($st['ID']);
            $store->custom = false;
            $store->setName($st['TITLE'])
                ->setDescription($st['DESCRIPTION'])
                ->setActive($st['ACTIVE'])
                ->setAddress($st['ADDRESS']);

            if (!empty($st['GPS_N']) && !empty($st['GPS_S']))
                $store->setPoint([$st['GPS_N'], $st['GPS_S']]);

            $arResult[$store->id] = $store;
        }

        return $arResult;
    }

    public function GetListCustom($arOrder = array(), $arFilter = array()) {
        global $DB;

        self::verifyFilter($arFilter);

        $sql = "SELECT STORE.* FROM `b_ctweb_yandexdelivery_store` STORE";
        if (!empty($arFilter)) {
            $sql .= ' WHERE ';
            $where = array();
            foreach ($arFilter as $code => $filter) {
                if (is_numeric($filter))
                    $where[] = "$code=$filter";
                elseif (is_string($filter)) {
                    if (in_array(($sign = substr($filter, 0, 1)), array('>', '<'))) {
                        $where[] = "$code $sign ".substr($filter, 1);
                    } else {
                        $where[] = "$code LIKE ('$filter')";
                    }
                }
                elseif (is_array($filter))
                    $where[] = "$code IN ('" . implode("','", $filter) . "')";
            }
            $sql .= implode(' AND ', $where);
        }
        if (empty($arOrder) || !is_array($arOrder)) {
            $arOrder = array('ID' => 'ASC');
        }
        $sql .= " ORDER BY";
        foreach ($arOrder as $code => $order) {
            $sql .= " $code $order,";
        }

        $sql = substr($sql, 0, strlen($sql) - 1) . ";";
        $res = $DB->query($sql);

        $arResult = array();
        while ($st  = $res->Fetch()) {
            $store = new self();
            $store->id = intval($st['ID']);
            $store->setName($st['NAME'])
                ->setDescription($st['DESCRIPTION'])
                ->setAddress($st['ADDRESS'])
                ->setActive($st['ACTIVE'])
                ->setPoint(Json::decode($st['POINT']));

            $arResult[$store->id] = $store;
        }

        return $arResult;
    }

    static function GetNew()
    {
        $bCatalogStores = (Option::get('ctweb.yandexdelivery', 'FIELD_USE_CATALOG_STORES', 0));

        $store = new self();
        $store->id = 0;
        $store->setActive(false);
        $store->setName('');
        $store->setDescription('');
        $store->setAddress('');
        if ($bCatalogStores)
            $store->custom = false;

        return $store;
    }

    function Save() {
        if ($this->isCustom())
            $this->SaveCustom();
        else
            $this->SaveInner();
    }

    function SaveInner() {
        \Bitrix\Main\Loader::includeModule('catalog');

        if (!$this->isCustom()) {
            $arFields = $this->GetFieldsArray();
            $arFields['TITLE'] = $arFields['NAME'];
            $arFields['GPS_N'] = $arFields['POINT'][0];
            $arFields['GPS_S'] = $arFields['POINT'][1];
            unset($arFields['NAME']);
            unset($arFields['POINT']);

            if ($this->id > 0) {
                unset($arFields['ID']);
                \CCatalogStore::Update($this->id, $arFields);
            } else {
                $arFields['ISSUING_CENTER'] = 'Y';

                $this->id = \CCatalogStore::Add($arFields);
            }
        }

        return $this;
    }

    function SaveCustom()
    {
        global $DB;

        $arFields = $this->PrepareFieldsForDB();

        if ($this->id > 0)
        {
            $DB->StartTransaction();
            $changed = $DB->Update(
                "b_ctweb_yandexdelivery_store",
                $arFields,
                "WHERE ID={$this->id}");
        } else {
            $ID = $DB->Insert("b_ctweb_yandexdelivery_store", $arFields);
            $ID = intval($ID);
        }

        if ($changed > 0 || $ID > 0) {
            $DB->Commit();
            if ($ID > 0)
                $this->id = $ID;

            return $this;
        }
        else  {
            $DB->Rollback();

            return false;
        }

    }

    static function DeleteByID($id) {
        global $DB;
        $id = intval($id);
        if ($id > 0) {
            $DB->Query("DELETE FROM b_ctweb_yandexdelivery_store WHERE ID=$id");
        }
    }

    private static function verifyFilter(&$arFields) {
        array_intersect_key($arFields, array_flip(self::getColumns()));
    }

    private static function getColumns() {
        return array(
            'ID',
            'ACTIVE',
            'NAME',
            'ADDRESS',
            'DESCRIPTION',
            'POINT',
        );
    }

    function GetFieldsArray()
    {
        $arResult = array();
        $arResult['ID'] = $this->id;
        $arResult['ACTIVE'] = ($this->active) ? 'Y' : 'N';
        $arResult['NAME'] = $this->name;
        $arResult['ADDRESS'] = $this->address;
        $arResult['DESCRIPTION'] = $this->description;
        $arResult['POINT'] = $this->point;

        return $arResult;
    }

    private function PrepareFieldsForDB() {
        $arFields = array();
        $arFields['ID'] = intval($this->id);
        $arFields['ACTIVE'] = ($this->active) ? "'Y'" : "'N'";
        $arFields['NAME'] = "'".addslashes($this->name)."'";
        $arFields['ADDRESS'] = "'".addslashes($this->address)."'";
        $arFields['DESCRIPTION'] = "'".addslashes($this->description)."'";
        $arFields['POINT'] = "'".Json::encode($this->point)."'";

        return $arFields;
    }
}
?>