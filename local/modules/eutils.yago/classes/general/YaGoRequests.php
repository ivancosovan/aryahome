<?
class YaGoRequests {

	public function updateRequest($claimId, $noLog)
    {
        CModule::includeModule("eutils.yago");
        $options = YaGo::getOptions();
        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $headers[] = 'Authorization: Bearer '.$options['token'];
        $headers[] = 'Accept-Language: *';

        $ch = curl_init('https://b2b.taxi.yandex.net/b2b/cargo/integration/v2/claims/info?claim_id='.$claimId);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($array, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
         
        $html = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($html, true);
        if($result["code"] && !$noLog)
            file_put_contents($_SERVER["DOCUMENT_ROOT"].'/log/yago_'.date("d.m.Y").'.log', date('[d-m-Y H:i] ') . print_r("Ошибка: url: https://b2b.taxi.yandex.net/b2b/cargo/integration/v2/claims/info?claim_id=".$claimId." answer: ", true) . print_r($html, true) . PHP_EOL, FILE_APPEND | LOCK_EX);
        if($result['id']){
            CModule::includeModule("iblock");
                $res = CIBlock::GetList(Array(), Array('TYPE'=>'eutils_yandex_go','CODE'=>'yandex_go_deliverys'), true);
                while($ar_res = $res->Fetch()){$iblockId = $ar_res['ID'];}
                CAgent::RemoveAgent('YaGoRequests::updateRequest("'.$claimId.'", false);', "eutils.yago");
                $arFilter = array("IBLOCK_ID"=>$iblockId, "=PROPERTY_CLAIM_ID"=>$claimId);
                $res = CIBlockElement::GetList(array("ID"=>"asc"), $arFilter, false, false, array("ID","PROPERTY_CLAIM_ID","PROPERTY_status"));
                while($element = $res->Fetch()){
                    $elemStatus = $element["PROPERTY_STATUS_VALUE"];
                }
            if($result['status'] != $elemStatus){
                if($options['log_on'] == "Y" && !$noLog)
                    file_put_contents($_SERVER["DOCUMENT_ROOT"].'/log/yago_'.date("d.m.Y").'.log', date('[d-m-Y H:i] ') . print_r("url: https://b2b.taxi.yandex.net/b2b/cargo/integration/v2/claims/info?claim_id=".$claimId." answer: ", true) . print_r($html, true) . PHP_EOL, FILE_APPEND | LOCK_EX);
            //обновляем элемент
            YaGo::updateIblock($result['id'],$result);
            }
        }
        //пересоздание агента
        if(
            in_array($result['status'],array("new","estimating","accepted","performer_lookup","performer_draft"))
          ){
            CAgent::RemoveAgent('YaGoRequests::updateRequest("'.$claimId.'", false);', "eutils.yago");
            $rDate = new DateTime('+30 seconds');
            $newTime = $rDate->format('d.m.Y H:i:s');
            CAgent::AddAgent('YaGoRequests::updateRequest("'.$claimId.'", false);', 'eutils.yago', 'N', 30, "", 'Y', $newTime);
        }else if ($result['status'] == "ready_for_approval"){
            if ($options['switch_on'] != "Y")
                return false;
            $headers = array();
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            $headers[] = 'Authorization: Bearer '.$options['token'];
            $headers[] = 'Accept-Language: *';

            $ch = curl_init('https://b2b.taxi.yandex.net/b2b/cargo/integration/v1/claims/accept?claim_id='.$claimId);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array("version"=>$result['version']), JSON_UNESCAPED_UNICODE));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            // curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $html = curl_exec($ch);
            $result = json_decode($html, true);
            curl_close($ch);
            if(
                $result['status'] != $elemStatus &&
                $options['log_on'] == "Y"
                )
                file_put_contents($_SERVER["DOCUMENT_ROOT"].'/log/yago_'.date("d.m.Y").'.log', date('[d-m-Y H:i] ') . print_r("Подтверждение заявки url: https://b2b.taxi.yandex.net/b2b/cargo/integration/v1/claims/accept?claim_id=".$claimId." answer: ", true) . print_r($html, true) . PHP_EOL, FILE_APPEND | LOCK_EX);
            if($result["code"])
                file_put_contents($_SERVER["DOCUMENT_ROOT"].'/log/yago_'.date("d.m.Y").'.log', date('[d-m-Y H:i] ') . print_r("Ошибка: url: https://b2b.taxi.yandex.net/b2b/cargo/integration/v1/claims/accept?claim_id=".$claimId." answer: ", true) . print_r($html, true) . PHP_EOL, FILE_APPEND | LOCK_EX);
            CAgent::RemoveAgent('YaGoRequests::updateRequest("'.$claimId.'", false);', "eutils.yago");
            
            $rDate = new DateTime('+30 seconds');
            $newTime = $rDate->format('d.m.Y H:i:s');
            CAgent::AddAgent('YaGoRequests::updateRequest("'.$claimId.'", false);', 'eutils.yago', 'N', floatval($options["timer"]), "", 'Y', $newTime);
        }else if(
                $result['status'] != $elemStatus && 
                in_array($result['status'],array("estimating_failed","failed","performer_not_found","cancelled_by_taxi"))
                ){
            $options = YaGo::getOptions();
            
            CModule::includeModule("iblock");
            $res = CIBlock::GetList(Array(), Array('TYPE'=>'eutils_yandex_go','CODE'=>'yandex_go_deliverys'), true);
            while($ar_res = $res->Fetch()){$iblockId = $ar_res['ID'];}
            CAgent::RemoveAgent('YaGoRequests::updateRequest("'.$claimId.'", false);', "eutils.yago");
            $arFilter = array("IBLOCK_ID"=>$iblockId, "=PROPERTY_CLAIM_ID"=>$claimId);
            $res = CIBlockElement::GetList(array("ID"=>"asc"), $arFilter, false, false, array("ID","PROPERTY_CLAIM_ID","PROPERTY_ORDER_ID"));
            while($element = $res->Fetch()){
                $itemID = $element["ID"];
                $orderId = $element["PROPERTY_ORDER_ID_VALUE"];
            }
            
//             CIBlockElement::SetPropertyValuesEx($itemID, $iblockId, $PROP);
            
            if ($options['switch_on'] != "Y")
                return false;
            $order = Bitrix\Sale\Order::loadByAccountNumber($orderId);
            $isNew = $order->isNew();
            $arOrderVals = $order->getFields()->getValues();
                
            $propertyCollection = $order->getPropertyCollection();
            foreach ($propertyCollection as $obProp) {
                $arProp = $obProp->getProperty();
                if(in_array($arProp["CODE"], ["EUTILS_YAGO_ISORDERED"])) {
                    $version = $obProp->getValue();
                    if($options["max_reorder"]<=$version)
                        return false;
                    $obProp->setValue($version+1);
                }
            }
            $order->save();
            YaGo::sendRequest($options,$order,$arOrderVals,$propertyCollection);
            
            return false;
        }else if(
                in_array($result['status'],array("returned_finish","cancelled","cancelled_with_payment","cancelled_with_items_on_hands"))
                ){
            CAgent::RemoveAgent('YaGoRequests::updateRequest("'.$claimId.'", false);', "eutils.yago");
            return false;
        }else if(
            $result['status'] == "delivered_finish"
                ){
            //обновляем заказ
            if($options["finish_status"] && $result['status'] != $elemStatus)
                YaGo::updateOrder($result['id']);
            CAgent::RemoveAgent('YaGoRequests::updateRequest("'.$claimId.'", false);', "eutils.yago");
            return false;
        }else{
            CAgent::RemoveAgent('YaGoRequests::updateRequest("'.$claimId.'", false);', "eutils.yago");
            $rDate = new DateTime('+'.$options["timer"].' seconds');
            $newTime = $rDate->format('d.m.Y H:i:s');
            CAgent::AddAgent('YaGoRequests::updateRequest("'.$claimId.'", false);', 'eutils.yago', 'N', floatval($options["timer"]), "", 'Y', $newTime);
        }
    }
    
}
