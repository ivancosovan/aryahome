<?
namespace Ctweb\YandexDelivery;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Localization\Loc;
use Svg\Tag\Polygon;

Loc::loadMessages(__FILE__);

require_once "geojson.php";

class CAdminFormCustom extends \CAdminForm
{
    function AddRegionField($id, $label, &$obRegion)
    {
        $id = $id.'['.$obRegion->getID().']';
        $html = $obRegion->getPolygon()->getSVG(100, 50, $obRegion->getColor());

        $opacity = $obRegion->getActive() ? 'opacity: 1;' : 'opacity: 0.5;';
        $color = 'color: '.$obRegion->getColor().';';

        $this->tabs[$this->tabIndex]["FIELDS"][$id] = array(
            "id" => $id,
            "required" => false,
            "content" => $label,
            "html" => '<td width="40%" style="'.$color.' '.$opacity.'">' . $this->GetCustomLabelHTML($id, $label) . '</td><td style="'.$opacity.'">' . $html . "<span class='cw-settings-btn' onclick='ShowYandexDeliveryRegionEdit(".$obRegion->getId().");'>".Loc::getMessage('CW_YD_BTN_SETTINGS')."</span>" . '</td>',
        );
    }

    function AddStoreField($id, $label, &$obStore)
    {
        $id = $id.'['.$obStore->getId().']';
        $html = "<span>{$obStore->getAddress()}</span>";

        $opacity = $obStore->getActive() ? 'opacity: 1;' : 'opacity: 0.5;';

        $this->tabs[$this->tabIndex]["FIELDS"][$id] = array(
            "id" => $id,
            "required" => false,
            "content" => $label,
            "html" => '<td width="40%" style="'.$opacity.'">' . $this->GetCustomLabelHTML($id, $label) . '</td><td style="'.$opacity.'">' . $html . "<span class='cw-settings-btn' onclick='ShowYandexDeliveryStoreEdit(".$obStore->getId().")'>".Loc::getMessage('CW_YD_BTN_SETTINGS')."</span>" . '</td>',
            "hidden" => '<input type="hidden" name="' . $id . '" value="">',
        );
    }

    function AddButton($id, $label, $isSubmit = false, $arParams = null) {
        if (is_array($arParams) && $arParams['TYPE']==='FILE') {
            $type = 'file';
            if (is_array($arParams['ACCEPTS']))
                $accepts = implode(',', $arParams['ACCEPTS']);
            if ($arParams['ONCHANGE'])
                $onchange = $arParams['ONCHANGE'];
            $html = "<input";
            $html .= " type='{$type}'";
            $html .= " id='{$id}'";
            $html .= " name='{$id}'";
            if (!empty($accepts))
                $html .= " accept='{$accepts}'";
            if (!empty($onchange))
                $html .= " onchange='{$onchange}'";
            $html .= ">";
        } else {
            $type = $isSubmit ? 'submit' : 'button';
            if ($arParams['ONCLICK'])
                $onclick = $arParams['ONCLICK'];
            $html = "<input type='{$type}' id='{$id}' name='{$id}' value='$label' onclick='$onclick'>";
        }

        $this->tabs[$this->tabIndex]["FIELDS"][$id] = array(
            "id" => $id,
            "required" => false,
            "content" => $label,
            "html" => '<td width="40%">'.$label.'</td><td>' . $html . '</td>',
            "hidden" => '<input type="hidden" name="' . $id . '" value="' . $label . '">',
        );
    }
}
?>