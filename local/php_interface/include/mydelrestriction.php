<?
use Bitrix\Sale\Delivery\Restrictions;
use Bitrix\Sale\Internals\Entity;

class MyDeliveryRestriction extends Restrictions\Base
{
    public static function getClassTitle()
    {
        return 'по времени суток';
    }

    public static function getClassDescription()
    {
        return 'доставка будет выводится только в указанном диапазоне времени суток';
    }

public static function check($timeday, array $restrictionParams, $deliveryId = 0)
{
    if ($timeday < (int) $restrictionParams['MIN_TIMEDAY']
        || $timeday >= (int) $restrictionParams['MAX_TIMEDAY'])
        return false;

    return true;
}
protected static function extractParams(Entity $shipment)
{
    $res = date("H");
    return !empty($res) ? intval($res) : 0;
}
public static function getParamsStructure($entityId = 0)
    {
        return array(
            "MIN_TIMEDAY" => array(
                'TYPE' => 'NUMBER',
                'DEFAULT' => "1",
                'LABEL' => 'Минимально время'
            ),
            "MAX_TIMEDAY" => array(
                'TYPE' => 'NUMBER',
                'DEFAULT' => "23",
                'LABEL' => 'Максимальное время'
            )
        );
    }
}
