<?php 
use \Bitrix\Main\EventManager;
use \Bitrix\Main\Event;
use \Bitrix\Main\Entity;
use \Bitrix\Sale\Order;
use \Bitrix\Sale\Payment;
use \Bitrix\Sale\PaySystem\Manager;
use \Bitrix\Sale\Shipment;
use \Bitrix\Sale\Helpers\Admin\Blocks\OrderBasketShipment;
use \Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

CModule::AddAutoloadClasses('', // не указываем имя модуля
    [
        // ключ - имя класса, значение - путь относительно корня сайта к файлу с классом
        'saleEvents'        => '/local/php_interface/lib/arya/saleEvents.php',
        'SberbankPay'       => '/local/php_interface/lib/arya/SberbankPay.php'
    ]
);

//Добавление outlets в выгрузку yandex
use Bitrix\Main;
use Yandex\Market;
$eventManager = Main\EventManager::getInstance();

//ограничения службы доставки
$eventManager->addEventHandler('sale', 'onSaleDeliveryRestrictionsClassNamesBuildList', ['saleEvents', 'myDeliveryFunction']);

$eventManager->addEventHandler('yandex.market', 'onExportOfferWriteData', function(Main\Event $event) {

   $tagResultList = $event->getParameter('TAG_RESULT_LIST');
   $elementList = $event->getParameter('ELEMENT_LIST');
        $context = $event->getParameter('CONTEXT');
   
   
   if ($context["SETUP_ID"] == 3) // Здесь должен быть ID прайс-листа
   {
      foreach ($tagResultList as $elementId => $tagResult)
      {
         if ($tagResult->isSuccess())
         {
            $tagNode = $tagResult->getXmlElement(); 
            
            $ar_res = CCatalogProduct::GetByID($elementId);
            
               $outlets = $tagNode->addChild('outlets');
               $outlet = $outlets->addChild('outlet');
               $outlet->addAttribute('id', 0);
               $outlet->addAttribute('instock', $ar_res["QUANTITY"]);
               
               $tagResult->invalidateXmlContents();
         }
      }
      
   } 
   
   
});

//изменение статуса заказа
AddEventHandler("sale", "OnSaleStatusOrder", ['saleEvents', 'OnSaleStatusOrder']);

$inst = EventManager::getInstance();
$inst-> addEventHandler('sale', 'OnBeforeCollectionDeleteItem', 'saveInfo');
$inst-> addEventHandler('sale', 'OnSaleOrderBeforeSaved', 'reverseInfo');
//Небольшая прослойка, возвращает доступные поля
/**
 * @param array $arValues
 * @param array $allowedFields
 * @return array $result
 */
function checkFields( $arValues, $allowedFields) {
	$result = array();
	foreach ( $arValues as $key => $value ) {
		if ( in_array( $key,$allowedFields ) && !in_array($key, array('ACCOUNT_NUMBER')) ) {
			$result[$key] = $value;
		}
	}
	return $result;
}
function saveInfo(\Bitrix\Main\Event $event ) {
   /**
    * @var \Bitrix\Sale\Shipment|\Bitrix\Sale\Payment $entity
    */
   if ( $_SESSION['BX_CML2_EXPORT'] ) {
   	$entity = $event->getParameter('ENTITY');
   	if ( $entity instanceof Shipment ) {
   		if ( !is_array( $_SESSION['BX_CML2_EXPORT']['DELETED_SHIPMENTS'] )  )
   			$_SESSION['BX_CML2_EXPORT']['DELETED_SHIPMENTS'] = array();
   		if ( !$entity->isSystem() )
   			$_SESSION['BX_CML2_EXPORT']['DELETED_SHIPMENTS'][] = checkFields( $entity->getFields()->getValues(), Shipment::getAvailableFields() );
   	}
   	if ( $entity instanceof Payment ) {
   		if ( !is_array( $_SESSION['BX_CML2_EXPORT']['DELETED_PAYMENTS'] )  )
   			$_SESSION['BX_CML2_EXPORT']['DELETED_PAYMENTS'] = array();
   		$_SESSION['BX_CML2_EXPORT']['DELETED_PAYMENTS'][] = checkFields( $entity->getFields()->getValues(), Payment::getAvailableFields() );
   	}
   }
   else {
   	return;
   }
}
function reverseInfo(\Bitrix\Main\Event $event ) {
   /**
    * @var \Bitrix\Sale\Order $order
    * @var \Bitrix\Sale\ShipmentCollection $shipmentCollection
    * @var \Bitrix\Sale\Shipment $shipment
    * @var \Bitrix\Sale\PaymentCollection $paymentCollection
    * @var \Bitrix\Sale\Payment $payment
    * @var \Bitrix\Sale\PropertyValue $somePropValue
    * **/
   if ( $_SESSION['BX_CML2_EXPORT'] ) {
   	$order = $event->getParameter("ENTITY");
   	if ( $_SESSION['BX_CML2_EXPORT']['DELETED_SHIPMENTS'] ) {
		         //Вернем отгрузки
   		$shipmentCollection = $order->getShipmentCollection();
   		$systemShipmentItemCollection = $shipmentCollection->getSystemShipment()->getShipmentItemCollection();
   		$products = array();
   		$basket = $order->getBasket();
   		if ($basket)
   		{
   			/** @var \Bitrix\Sale\BasketItem $product */
   			$basketItems = $basket->getBasketItems();
   			foreach ($basketItems as $product)
   			{
   				$systemShipmentItem = $systemShipmentItemCollection->getItemByBasketCode($product->getBasketCode());
   				if ($product->isBundleChild() || !$systemShipmentItem || $systemShipmentItem->getQuantity() <= 0)
   					continue;
   				$products[] = array(
   					'AMOUNT' => $product->getQuantity(),
   					'BASKET_CODE' => $product->getBasketCode()
   				);
   			}
   		}
   		/** @var \Bitrix\Sale\Shipment $obShipment */
   		/** @var array $shipmentFields */
   		foreach ( $_SESSION['BX_CML2_EXPORT']['DELETED_SHIPMENTS'] as $shipmentFields ) {
   			$fg = true;
   			foreach( $shipmentCollection as $obShipment ) {
   				if ($obShipment->isSystem())
   					continue;
   				$usedFields = checkFields($obShipment->getFields()->getValues(), Shipment::getAvailableFields() );
   				if ( count( array_diff_assoc( $shipmentFields, $usedFields) ) == 0 )
   					$fg = false;
				 //доставка с такими полями уже есть
   			}
   			if ( $fg ) {
   				$shipment = $shipmentCollection->createItem();
   				$shipment->setFields( $shipmentFields );
   				OrderBasketShipment::updateData($order, $shipment, $products);
   			}
   		}
   		unset( $_SESSION['BX_CML2_EXPORT']['DELETED_SHIPMENTS'] );
   	}
   	if ( $_SESSION['BX_CML2_EXPORT']['DELETED_PAYMENTS'] ) {
		         //Вернем оплаты
   		$paymentCollection = $order->getPaymentCollection();
   		/** @var \Bitrix\Sale\Payment $obPayment */
   		/** @var array $paymentFields */
   		foreach ( $_SESSION['BX_CML2_EXPORT']['DELETED_PAYMENTS'] as $paymentFields ) {
   			$fg = true;
   			foreach( $paymentCollection as $obPayment ) {
   				$usedFields = checkFields( $obPayment->getFields()->getValues(), Payment::getAvailableFields() );
   				if ( count( array_diff_assoc( $paymentFields, $usedFields) ) == 0 )
   					$fg = false;
				 //такая оплата уже есть
   			}
   			if ( $fg ) {
   				$payment = $paymentCollection->createItem();
   				$payment->setFields( $paymentFields );
   			}
   		}
   		unset( $_SESSION['BX_CML2_EXPORT']['DELETED_PAYMENTS'] );
   	}
	      //Проверим сумму заказа
   	$paymentCollection = $order->getPaymentCollection();
   	if ( ($sumP = $paymentCollection->getSum() ) != ($sumO = $order->getPrice() ) ) {
   		$diff = $sumO - $sumP;
   		$innerPayID = Manager::getInnerPaySystemId();
   		foreach ( $paymentCollection as $payment ) {
   			if ( $payment->getPaymentSystemId() != $innerPayID) {
   				$newVal = floatval($payment->getField("SUM")) + floatval($diff);
   				$payment->setField("SUM", $newVal);
   			}
   		}
   	}
   }
}

//Добавляем класс обработчика в автозагрузку и навешиваем его на событие BeforeIndex модуля поиск.
AddEventHandler("search", "BeforeIndex", Array("DeleteProductsInSearch", "BeforeIndexHandler"));
class DeleteProductsInSearch
{
    // создаем обработчик события "BeforeIndex"
	function BeforeIndexHandler($arFields)
	{
		CModule::IncludeModule("iblock");
		CModule::IncludeModule("catalog");

		if ($arFields['MODULE_ID'] == 'iblock' && $arFields['PARAM2'] == 3) {

			$catalogFields = CCatalogProduct::GetByID($arFields['ITEM_ID']);

			if ($catalogFields['QUANTITY'] <= 0){
				$arFields['BODY'] = '';
				$arFields['TITLE'] = '';
				$arFields['TAGS'] = '';
			}

			// $arFields["PARAMS"]["iblock_section"] = array();
			// //Получаем разделы привязки элемента (их может быть несколько)
			// $rsSections = CIBlockElement::GetElementGroups($arFields["ITEM_ID"], true);
			// while($arSection = $rsSections->Fetch())
			// {
			// 	//Сохраняем в поисковый индекс
			// 	$arFields["PARAMS"]["iblock_section"][] = $arSection["ID"];
			// }

		}
		return $arFields;
	}

}

//Почта SMTP
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/wsrubi.smtp/classes/general/wsrubismtp.php");

// регистрируем обработчик события "OnAfterUserRegister" - Купон после регистрации и регистрацию в бонус плюс
AddEventHandler("main", "OnAfterUserRegister", Array("OnAfterUserRegisterAddCoupon", "OnAfterUserRegisterHandler"));
class OnAfterUserRegisterAddCoupon
{
	function OnAfterUserRegisterHandler(&$arFields)
	{

		$TOKEN = base64_encode('2F38482A-467F-4342-94BD-8E9FC27E329C');

		$values = [];
		$rsUser = CUser::GetList($by, $order,
			array(
				"ID" => $arFields["ID"],
			),
			array(
				"SELECT" => array(
					"UF_*",
					"personal_birthday"
				),
			)
		);
		if($arUser = $rsUser->Fetch())
		{
			foreach($arUser as $key=>$value){
				$values[$key] = $value;
			}
		}

        // if ($values["PERSONAL_GENDER"] == 'F') {
        // 	$values["PERSONAL_GENDER"] = 0;
        // }elseif($values["PERSONAL_GENDER"] == 'M'){
        // 	$values["PERSONAL_GENDER"] = 1;
        // }

		$data = [
			'phone' => $values["UF_BXMAKER_AUPHONE"],
			'fn' => $arFields["NAME"],
			"ln" => $arFields["LAST_NAME"],
			"mn" => $values["SECOND_NAME"],
			"sex" => $values["PERSONAL_GENDER"],
			"email" => $arFields["EMAIL"],
			"birthDay" => $values["PERSONAL_BIRTHDAY"]
		];

		$opts = array(
			'http' => array(
				'method' => "GET",
				'header' => "Authorization: ApiKey " . $TOKEN . "\r\n"
			)
		);

		$context = stream_context_create($opts);

	    // Открываем файл с помощью установленных выше HTTP-заголовков
		$file = file_get_contents('https://bonusplus.pro/api/customer?phone=' . $data['phone'], false, $context);


		$status_line = $http_response_header[0];

		preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);

		$status = $match[1];

		if ( ($status == '412') && ($values["UF_BXMAKER_AUPHONE"] != '70000000000') ) {

			$opts = array(
				'http' =>
				array(
					'method'  => 'POST',
					'header' => "Content-type: application/json\r\n" .
					"Accept: application/json\r\n" .
					"Authorization: ApiKey " . $TOKEN . "\r\n",
					'content' => json_encode($data),
					'ignore_errors' => false
				)
			);
			$context  = stream_context_create($opts);

			$result = file_get_contents('https://bonusplus.pro/api/customer', false, $context);
			if ($result === FALSE) {
				return 'Error';
			}

			$status_line = $http_response_header[0];

			preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);

			$status = $match[1];

		}

		$addDb = \Bitrix\Sale\Internals\DiscountCouponTable::add(array(
			'DISCOUNT_ID' => 6,
			'COUPON'      => $arFields["LOGIN"],
		    'TYPE'        => \Bitrix\Sale\Internals\DiscountCouponTable::TYPE_ONE_ORDER, //или TYPE_MULTI_ORDER
		    'MAX_USE'     => 1,
		    'DESCRIPTION' => 'Купон для первого заказа',
		));

		if ($addDb->isSuccess()) {
			return $coupon;
		}

	}

	// Функция получения данных о пользователе
	// На входе: телефон и токен
	// На выходе: в случае наличия пользователя - данные о пользователе в виде массива
	// в случае отсутствия пользователя - false
	// в случае ошибки - статус код
	function get_user($phone, $TOKEN){
		$opts = array(
			'http' => array(
				'method' => "GET",
				'header' => "Authorization: ApiKey " . $TOKEN . "\r\n"
			)
		);

		$context = stream_context_create($opts);

	    // Открываем файл с помощью установленных выше HTTP-заголовков
		$file = file_get_contents('https://bonusplus.pro/api/customer?phone=' . $phone, false, $context);


		$status_line = $http_response_header[0];

		preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);

		$status = $match[1];


		if ($status == "200") {
			return json_decode($file);
		}

		if ($status == "412") {
			return false;
		}

		if ($status !== "200") {
			return "Error, status code " . $status;
		}
	}


	// Функция создания пользователя
	// На входе: массив данных пользователя и токен
	// На выходе: в случае наличия пользователя с таким номером - строка "Телефон уже есть в бонус плюс"
	// в случае отсутствия пользователя - создание пользователя и возврат данных о нем
	// в случае ошибки - статус код
	function create_user($data, $TOKEN)
	{
		if($this->get_user($data['phone'], $TOKEN) != false){
			return "Телефон уже есть в бонус плюс";
		}
		$opts = array(
			'http' =>
			array(
				'method'  => 'POST',
				'header' => "Content-type: application/json\r\n" .
				"Accept: application/json\r\n" .
				"Authorization: ApiKey " . $TOKEN . "\r\n",
				'content' => json_encode($data),
				'ignore_errors' => false
			)
		);
		$context  = stream_context_create($opts);

		$result = file_get_contents('https://bonusplus.pro/api/customer', false, $context);
		if ($result === FALSE) {
			return 'Error';
		}

		$status_line = $http_response_header[0];

		preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);

		$status = $match[1];


		if ($status == "200") {
			return json_decode($result);
		}

		if ($status == "412") {
			return false;
		}

		if ($status !== "200") {
			return "Error, status code " . $status;
		}
	}

}

// //Бесплатная доставка IML
// AddEventHandler('iml.v1', 'onCalculate', 'changeIMLTerms');
// function changeIMLTerms(&$arResult, $profile, $arConfig, $arOrder){	

// 	if ($arOrder['PRICE'] > 4999) {
// 		$arResult[VALUE] = 0;}

// 	// if($arOrder['LOCATION_TO'] == "0000073738"){
// 	// 	if ($arOrder['PRICE'] > 4999) {
// 	// 	$arResult[VALUE] = 0;}
// 	// }
// 	return $arResult;
// }
// //Бесплатная доставка IML - конец

//Доп поля заказа для смс
$eventManager = \Bitrix\Main\EventManager::getInstance(); //подписываем обработчик на событие 
$eventManager->addEventHandler("bxmaker.smsnotice", "OnPreparedOrderData", array(
	'CBXmakerEventHandler', 'bxmaker_smsnotice__OnPreparedOrderData'
)); 

Class CBXmakerEventHandler { 

    // обоработчик ---- 
	public function bxmaker_smsnotice__OnPreparedOrderData(\Bitrix\Main\Event $event)
	{
		$arOrderData = $event->getParameters();

		if (CModule::IncludeModule("sale")){

			$arFilter = Array("ID" => $arOrderData[ORDER_ID]);
			$rsSales = CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), $arFilter, array("RESPONSIBLE_ID"));
			while ($arSales = $rsSales->Fetch())
			{
				$RESPONSIBLE_ID = $arSales[RESPONSIBLE_ID];
			}
		}

		$rsUser = CUser::GetByID($RESPONSIBLE_ID);
		$arUser = $rsUser->Fetch();

		if (!empty($arUser[NAME])) {
			$arOrderData['MANAGER_NAME'] = $arUser[NAME];
		}else{
			$arOrderData['MANAGER_NAME'] = ' ';
		}

		if ((!empty($arUser[LAST_NAME])) || ($arUser[LAST_NAME] == 'Прозорскова')){
			$arOrderData['MANAGER_LAST_NAME'] = $arUser[LAST_NAME];
		}else{
			$arOrderData['MANAGER_LAST_NAME'] = ' ';
		}

		if (!empty($arUser[PERSONAL_MOBILE])) {
			$arOrderData['MANAGER_MOBILE'] = $arUser[PERSONAL_MOBILE].';';
		}else{
			$arOrderData['MANAGER_MOBILE'] = ' ';
		}

		$result = new \Bitrix\Main\EventResult(Bitrix\Main\EventResult::SUCCESS, $arOrderData);

		return $result;
	} 
}
//Доп поля заказа для смс - конец

//Модификация отправки сообщений на почту о заказе
AddEventHandler("sale", "OnOrderStatusSendEmail", "OnOrderStatus");  // Добавление переменных в почтовый шаблон (ИМ при отгрузке, состав заказа в таблице.)
function OnOrderStatus($ID, &$eventName, &$arFields, $numberStatus)  {
    $arOrder = CSaleOrder::GetByID($ID); //параметры заказа   можно вытащить статус заказа, к котому херачить этот обработчик!
      if ($arOrder['STATUS_ID'] == O): //только для статуса заказа = O, т.е. отгружен.
	   $db_props = CSaleOrderPropsValue::GetOrderProps($ID); // свойства заказа 
	   while ($arProps = $db_props->Fetch()) {
	   	$Svoystva['PROP_'.$arProps['CODE']] = $arProps['NAME'];
	   	$Svoystva['PROP_VALUE_'.$arProps['CODE']] = $arProps['VALUE'];
	   }
	   $dbBasketItems = CSaleBasket::GetList(
	   	array(
	   		"NAME" => "ASC",
	   		"ID" => "ASC"
	   	),
	   	array(
	   		"ORDER_ID" => $ID,
	   	),
	   	false,
	   	false,
	   	array("PRODUCT_ID", "ID", "NAME", "QUANTITY", "PRICE", "CURRENCY")
	   );
	   $zapyataya = '<table border="0" cellspacing="0">';
	   $arFields['TOVAR'][0] = substr($zapyataya, 0, strlen($zapyataya) - 1) . '.';
	while ($arIt = $dbBasketItems->Fetch())  // клеим ID и цену всех товаров в корзине
	{
		$arFields['ORDER_USER'] = $arIt['USER_ID'];
		$intElementID = $arIt['PRODUCT_ID'];
		$mxResult = CCatalogSku::GetProductInfo(
			$intElementID
		);
		if (is_array($mxResult))
		{
			echo 'ID товара = '.$mxResult['ID'];
		}
		else
		{
			ShowError('Это не торговое предложение');
			$mxResult['ID'] = $arIt['PRODUCT_ID'];
		}
		$res = CIBlockElement::GetByID($mxResult['ID']);
		if ($ar_res = $res->GetNext()) {
			$arFile = CFile::GetFileArray($ar_res["DETAIL_PICTURE"]);
				$tovar['NAME'] = $arIt['NAME']; //имя товара 
	   			$tovar['PRICE'] = round($arIt['PRICE'], 2); //цена товара 
	        	$tovar['QUANTITY'] = sprintf("%.0f" . "", ($arIt['QUANTITY'])); //кол-о товара 
	   			$tovar['SUMM'] = sprintf("%.2f" . "", ($arIt['PRICE'] * $arIt['QUANTITY'])); //сумма товара
	   			$Ubral_zapyatie = '<tr>' .'<td width="110"><img src='.SITE_SERVER_NAME.$arFile[SRC].' style="width: 100px;"></td>' .'<td width="260">'. $tovar['NAME'] .'</td>' .'<td width="70" style="text-align:center;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:helvetica, "helvetica neue", arial, verdana, sans-serif;line-height:24px;color:#333333;">'. $tovar['PRICE'] . 'p.' .'</p></td>' . '<td width="80" style="text-align:center;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:helvetica, "helvetica neue", arial, verdana, sans-serif;line-height:24px;color:#333333;">'. $tovar['QUANTITY'] .'</p></td>' .'<td width="70" style="text-align:center;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:helvetica, "helvetica neue", arial, verdana, sans-serif;line-height:24px;color:#333333;text">'. $tovar['SUMM'] .'p'.'</p></td>' .'</tr>';
	   			$arFields['TOVAR'][] = substr($Ubral_zapyatie, 0, strlen($Ubral_zapyatie) - 1) . '.';
	   		}         
	   	} 
	   	$arFields['TOVAR'][] = '</table>' ;
	$arFields['ALL_SUMM'] = $arOrder['PRICE'] . 'p.';  //общая стоимость
endif;
}
AddEventHandler("sale", "OnOrderNewSendEmail", "ModifyOrderSaleMails");
function ModifyOrderSaleMails($orderID, &$eventName, &$arFields, $arUserGroups = array())
{
	if(CModule::IncludeModule("sale") && CModule::IncludeModule("iblock"))
	{
		$strOrderList = "";
		$dbBasketItems = CSaleBasket::GetList(
			array("NAME" => "ASC"),
			array("ORDER_ID" => $orderID),
			false,
			false,
			array("PRODUCT_ID", "ID", "NAME", "QUANTITY", "PRICE", "CURRENCY")
		);
		while ($arProps = $dbBasketItems->Fetch())
		{
			$intElementID = $arProps['PRODUCT_ID'];
			$mxResult = CCatalogSku::GetProductInfo(
				$intElementID
			);
			if (is_array($mxResult))
			{
				echo 'ID товара = '.$mxResult['ID'];
			}
			else
			{
				ShowError('Это не торговое предложение');
				$mxResult['ID'] = $arProps['PRODUCT_ID'];
			}
			$res = CIBlockElement::GetByID($mxResult['ID']);
			if ($ar_res = $res->GetNext()) {
				$arFile = CFile::GetFileArray($ar_res["DETAIL_PICTURE"]);
				$price = round($arProps['PRICE']);
				$summ = round(($arProps['QUANTITY'] * $price));
				$QUANTITY = round($arProps['QUANTITY']);
				$strCustomOrderList .= "<tr><td width='110' align='left'><img src=".SITE_SERVER_NAME.$arFile[SRC]." style='width: 100px;'></td><td width='260' align='left'><p style='Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;line-height:24px;color:#333333;'>".$arProps['NAME']."</p></td><td width='70' align='center'><p style='Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;line-height:24px;color:#333333;'>".$price." руб.</p></td><td width='50' align='center'><p style='Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;line-height:24px;color:#333333;'>".$QUANTITY."</p></td><td width='70' align='center'><p style='Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;line-height:24px;color:#333333;'>".$summ." руб.</p></td><tr>";
			}
		}
		$arFields["ORDER_TABLE_ITEMS"] = $strCustomOrderList; 
		$arOrder = CSaleOrder::GetByID($orderID);
		$arFields["DESCRIPTION"] =  $arOrder['USER_DESCRIPTION'];  
		$order_props = CSaleOrderPropsValue::GetOrderProps($orderID);
		while ($arProps = $order_props->Fetch()){
	    	//Почта пользователя
			if ($arProps['ORDER_PROPS_ID']==2){
				$additional_information_email.='E-Mail: '.$arProps['VALUE'];
			}
			if ($arProps['ORDER_PROPS_ID']==13){
				$additional_information_email.='E-Mail: '.$arProps['VALUE'];
			}
	        //Телефон
			if ($arProps['ORDER_PROPS_ID']==3){
				$additional_information_number.='Тел: '.$arProps['VALUE'];
			}
			if ($arProps['ORDER_PROPS_ID']==14){
				$additional_information_number.='Тел: '.$arProps['VALUE'];
			}
	        //Полный адресс
			if ($arProps['ORDER_PROPS_ID']==7){
				$additional_information_adress.='Адрес: '.$arProps['VALUE'];
			}
			if ($arProps['ORDER_PROPS_ID']==19){
				$additional_information_adress.='Адрес: '.$arProps['VALUE'];
			}
	        //Город
			if ($arProps['ORDER_PROPS_ID']==22){
				$additional_information_town.='Город: '.$arProps['VALUE'];
			}
			if ($arProps['ORDER_PROPS_ID']==34){
				$additional_information_town.='Город: '.$arProps['VALUE'];
			}
	        //
			if ($arProps['ORDER_PROPS_ID']==40){
				$additional_information_date.=''.$arProps['VALUE'];
			}
			if ($arProps['ORDER_PROPS_ID']==41){
				$additional_information_date.=''.$arProps['VALUE'];
			}
		}
		$toSend["EMAIL"] = $additional_information_email;
		$toSend["ORDER_TABLE_ITEMS"] = $strCustomOrderList;

		$arFields["NUMBER"] = $additional_information_number;
		$arFields["ADRESS"] = $additional_information_adress;
		$arFields["TOWN"] = $additional_information_town;
		$arFields["DATE"] = $additional_information_date;

		$arFields["PASSWORD"] = $_SESSION['ARYA_PASSWORD'];
		$arFields["LOGIN"] = $_SESSION['ARYA_LOGIN'];
		if(isset($arFields["LOGIN"]) && $arFields["LOGIN"] !== '')  {
			$arFields["AUTH"] = "<p style='Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;line-height:24px;color:#333333;'>Для Вас для вашего Email: ".$arFields["EMAIL"]." создана учетная запись.</p><p style='Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;line-height:24px;color:#333333;'>Логин: ".$arFields["LOGIN"]."</p><p style='Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;line-height:24px;color:#333333;'>Пароль: ".$arFields["PASSWORD"]."</p><p style='Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;line-height:24px;color:#333333;'>Изменить пароль вы можете в вашей учетной записи по <a target='_blank' href='https://aryahome.ru/auth' style='-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;font-size:16px;text-decoration:underline;color:#AB7D00;'>ссылке</a></p>";
			$arFields["LINE"] = "<tr style='border-collapse:collapse;'> 
			<td align='left' style='Margin:0;padding-top:10px;padding-bottom:10px;padding-left:10px;padding-right:10px;'> 
			<table cellpadding='0' cellspacing='0' width='100%' style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;'> 
			<tr style='border-collapse:collapse;'> 
			<td width='580' align='center' valign='top' style='padding:0;Margin:0;'> 
			<table cellpadding='0' cellspacing='0' width='100%' style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;'> 
			<tr style='border-collapse:collapse;'> 
			<td align='center' style='padding:0;Margin:0;padding-left:20px;padding-right:20px;'> 
			<table border='0' width='100%' height='100%' cellpadding='0' cellspacing='0' style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;'> 
			<tr style='border-collapse:collapse;'> 
			<td style='padding:0;Margin:0px;border-bottom:1px solid #AB7D00;background:none;height:1px;width:100%;margin:0px;'></td> 
			</tr> 
			</table> </td> 
			</tr> 
			</table> </td> 
			</tr> 
			</table> </td> 
			</tr>";
		}
	} 
}

// Достаем строку с перечислением всех скидок
function getDiscountString($elementId, $totalDiscountPercent)
{
	global $USER;

	$discounts = [];
	foreach (CCatalogDiscount::GetDiscountByProduct($elementId, $USER->GetUserGroupArray()) as $discount) {
		$discounts[] = $discount['VALUE'] . ($discount['VALUE_TYPE'] == "P" ? "%" : "&#8381;");
	}

	$discountString = !empty($discounts) ? implode(" + ", $discounts) : $totalDiscountPercent . "%";

	return "-" . $discountString;
}
//Модификация отправки сообщений на почту о заказе - конец

AddEventHandler("main", "OnEndBufferContent", "ChangeMyContent");

function ChangeMyContent(&$content)
{
	if(isset($_GET['PAGEN_1']))
	{

	    # Шаблон поиска
		$pattern = '~<h1 .*?>(.*)</h1>~s';

	    # Ищем данные по шаблону
		preg_match( $pattern, $content, $matches );

		$h1 = $matches[1];

		$page=(int)$_GET['PAGEN_1'];
		$pattern = '/(.*?)<title[^>]*>(.*?)\n?\n?<\/title>(.*)/s';
		$replacement = '$1<title>'.$h1.' страница - '.$page.'</title>$3';
		$content = preg_replace($pattern, $replacement, $content);
		$pattern = '/(.*?)<meta name="description" content="(.*?)\n?\n?"\s?\/>(.*)/s';
		$replacement = '$1<meta name="description" content="Arya Home - '.$h1.' - страница - '.$page.'" />$3';
		$content = preg_replace($pattern, $replacement, $content);
	}

}

AddEventHandler("iblock", "OnAfterIBlockElementUpdate", 'createPreviewPicFromDetail');
AddEventHandler("iblock", "OnAfterIBlockElementAdd", 'createPreviewPicFromDetail');
function createPreviewPicFromDetail(&$arFields){

	$arEl = CIBlockElement::GetByID($arFields['ID'])->Fetch();

	if ($arEl['DETAIL_PICTURE'] && $arEl['IBLOCK_ID']=='3' && (is_null($arEl['PREVIEW_PICTURE']) || $arFields['PREVIEW_PICTURE']['del'] == 'Y' )){
		$newEl = new CIBlockElement;
		$arFields = Array();
		$arFields['PREVIEW_PICTURE'] = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"] . CFile::GetFileArray($arEl["DETAIL_PICTURE"])["SRC"]);
		$newEl->Update($arEl["ID"], $arFields);
	}
}


/**/
AddEventHandler('main', 'OnBuildGlobalMenu', 'ASDFavoriteOnBuildGlobalMenu');
function ASDFavoriteOnBuildGlobalMenu(&$aGlobalMenu, &$aModuleMenu)
{
	global $USER;
	CModule::IncludeModule("main");
	if ( !CSite::InGroup( array(1) )){
		unset($aModuleMenu[51]);
		//$aGlobalMenu['global_menu_marketplace']=false;
		// foreach($aGlobalMenu as $k => $item){
		// 	if($k = "global_menu_services"){
		// 		unset($aGlobalMenu[$k]);
		// 	}
		// }
		// foreach($aModuleMenu as $k => $item){
		// 	if($item["parent_menu"] == "global_menu_services" 
		// 	   || $item["parent_menu"] == "global_menu_settings"
		// 	   || $item["parent_menu"] == "global_menu_marketplace"){

		// 		unset($aModuleMenu[$k]);
		// 	}
		// }
		
	}

}

AddEventHandler('aspro.max', 'OnAsproGetTotalQuantity', 'MyTotalCount');
function MyTotalCount($arItem, $arParams, &$totalCount){

	$arSelect = array('ID', 'PRODUCT_AMOUNT');
	$arFilter = array('ID' => 3);

	if($arItem['OFFERS']){
		$arOffers = array_column($arItem['OFFERS'], 'ID');

		if($arOffers){
			$quantity = 0;

			$rsStore = CMax::CCatalogStore_GetList(array(), array_merge($arFilter, array('PRODUCT_ID' => $arOffers)), false, false, $arSelect);
			foreach($rsStore as $arStore){
				$quantity += $arStore['PRODUCT_AMOUNT'];
			}

			$totalCount = $quantity;
		}
	}
	elseif(
		isset($arItem['PRODUCT']['TYPE']) &&
		$arItem['PRODUCT']['TYPE'] == 2
	){
		if(!$arItem['SET_ITEMS']){
			$arItem['SET_ITEMS'] = array();

			if($arSets = CCatalogProductSet::getAllSetsByProduct($arItem['ID'], 1)){
				$arSets = reset($arSets);

				foreach($arSets['ITEMS'] as $v){
					$v['ID'] = $v['ITEM_ID'];
					unset($v['ITEM_ID']);
					$arItem['SET_ITEMS'][] = $v;
				}
			}
		}

		$arProductSet = $arItem['SET_ITEMS'] ? array_column($arItem['SET_ITEMS'], 'ID') : array();

		if($arProductSet){
			$arSelect[] = 'ELEMENT_ID';
			$quantity = array();

			$rsStore = CMax::CCatalogStore_GetList(array(), array_merge($arFilter, array('PRODUCT_ID' => $arProductSet)), false, false, $arSelect);
			foreach($rsStore as $arStore){
				$quantity[$arStore['ELEMENT_ID']] += $arStore['PRODUCT_AMOUNT'];
			}

			if($quantity){
				foreach($arItem['SET_ITEMS'] as $v) {
					$quantity[$v['ID']] /= $v['QUANTITY'];
					$quantity[$v['ID']] = floor($quantity[$v['ID']]);
				}
			}
			$totalCount = min($quantity);
		}
	}
	else{
		$rsStore = CMax::CCatalogStore_GetList(array(), array_merge($arFilter, array('PRODUCT_ID' => $arItem['ID'])), false, false, $arSelect);
		foreach( $rsStore as $arStore ){
			$quantity += $arStore['PRODUCT_AMOUNT'];
		}

		$totalCount = $quantity;
	}
}

function findSelectedSize($arItem,&$sizes,$postSize = false){

	foreach ($sizes['SIZE'] as $id => $size) {
		if($postSize == $size['ID']){
			$sizes['SIZE'][$id]['SELECTED'] = "Y";
			break;
		}elseif(($arItem['PROPERTIES']['RAZMER']['VALUE'] == $size['RAZMER']) || $arItem['PROPERTIES']['OBSHCHIY_RAZMER_DLYA_SAYTA']['VALUE'] == $size['RAZMER']){
			$sizes['SIZE'][$id]['SELECTED'] = "Y";
			break;
		}
	}
}

function ElementUpdateHandler($id, $arFields)
{
	$id = $arFields["PRODUCT_ID"];
	$rsStore = CCatalogStoreProduct::GetList(
		array(),
		array('PRODUCT_ID' => $id,'STORE_ID' => 3),
		false,
		false,
		array('AMOUNT')
	);

	if ($arStore = $rsStore->Fetch()) {
		$arFields = array('CAN_BUY_ZERO' => "N", 'QUANTITY_TRACE'=>"Y",'QUANTITY' => $arStore['AMOUNT']);
		$test = CCatalogProduct::Update($id, $arFields);
	}
}

AddEventHandler("catalog", "OnStoreProductAdd", "OnAfterIBlockElementAddHandler",9999999);
AddEventHandler("catalog", "OnStoreProductUpdate", "OnAfterIBlockElementAddHandler",9999999);
function OnAfterIBlockElementAddHandler($ID, $arFields)
{
	static $k = 0;
	if ($k > 0) {
		return;
	}
	$k++;

   //\Bitrix\Main\Diag\Debug::dumpToFile('OnStoreProductAddEvent', $varName = "OnAfterIBlockElementAddHandler", $fileName = SITE_DIR.'local/php_interface/test123.txt');
   //\Bitrix\Main\Diag\Debug::dumpToFile($arFields, $varName = "arFields", $fileName = SITE_DIR.'local/php_interface/test123.txt');

	$id = $arFields["PRODUCT_ID"];
	$rsStore = CCatalogStoreProduct::GetList(
		array(),
		array('PRODUCT_ID' => $id,'STORE_ID' => 3),
		false,
		false,
		array('AMOUNT')
	);

	if ($arStore = $rsStore->Fetch()) {
		$arFields = array('QUANTITY' => $arStore['AMOUNT']);
		$test = CCatalogProduct::Update($id, $arFields);

       $logStr = 'New quantity'.$arStore["AMOUNT"].'for store ID - 3';
       //\Bitrix\Main\Diag\Debug::dumpToFile($logStr, $varName = "Result", $fileName = SITE_DIR.'local/php_interface/test123.txt');
	}

}


AddEventHandler("catalog", "OnSuccessCatalogImport1C", "OnSuccessCatalogImport1CHandler");

function OnSuccessCatalogImport1CHandler($arPropertyValues, $ABS_FILE_NAME){

	//обновляем остатки с интернет магазина
	CModule::IncludeModule("iblock");

	$arSelect = Array("ID", "IBLOCK_ID");
	//$thisDay = (new \Bitrix\Main\Type\Date())->format('d.m.Y H:i:s');
	$arFilter = Array("IBLOCK_ID"=>IntVal(3));
	$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
	while($ob = $res->fetch()){ 
		$ids[] = $ob['ID'];
	}

	foreach($ids as $id){
		//\Bitrix\Main\Diag\Debug::dumpToFile($id, $varName = "id", $fileName = SITE_DIR.'local/php_interface/end1C.txt');
		$rsStore = CCatalogStoreProduct::GetList(
			array(),
			array('PRODUCT_ID' => $id,'STORE_ID' => 3),
			false,
			false,
			array('AMOUNT')
		);

		if ($arStore = $rsStore->Fetch()) {
			$arFields = array('QUANTITY' => $arStore['AMOUNT']);
			//\Bitrix\Main\Diag\Debug::dumpToFile($arStore['AMOUNT'], $varName = "newquantity", $fileName = SITE_DIR.'local/php_interface/end1C.txt');
			$test = CCatalogProduct::Update($id, $arFields);
		}
	}
	


	return "";
}

AddEventHandler("catalog", "OnCompleteCatalogImport1C", "OnCompleteCatalogImport1CHandler");

function OnCompleteCatalogImport1CHandler(){
	//\Bitrix\Main\Diag\Debug::dumpToFile($_REQUEST, $varName = "OnCompleteCatalogImport1C", $fileName = SITE_DIR.'local/php_interface/OnCompleteCatalogImport1C.txt');
	return "";
}


function GetQuantityArrayToString(
		$totalCount,
		$arItemIDs = array(),
		$useStoreClick = "N",
		$bShowAjaxItems = false,
		$dopClass = '',
		$siteId = false,
		$userId = false
	){
		$siteId = strlen($siteId) ? $siteId : (defined('SITE_ID') ? SITE_ID : false);
		if(!$siteId){
			return;
		}

		$userId = ($userId = intval($userId)) > 0 ? $userId : CMax::GetUserID();

		static $arQuantityOptions, $arQuantityRights;
		if($arQuantityOptions === NULL){
			$arQuantityOptions = array(
				"USE_WORD_EXPRESSION" => Option::get('aspro.max', "USE_WORD_EXPRESSION", "Y", $siteId),
				"MAX_AMOUNT" => Option::get('aspro.max', "MAX_AMOUNT", "10", $siteId),
				"MIN_AMOUNT" => Option::get('aspro.max', "MIN_AMOUNT", "2", $siteId),
				"EXPRESSION_FOR_MIN" => Option::get('aspro.max', "EXPRESSION_FOR_MIN", GetMessage("EXPRESSION_FOR_MIN_DEFAULT"), $siteId),
				"EXPRESSION_FOR_MID" => Option::get('aspro.max', "EXPRESSION_FOR_MID", GetMessage("EXPRESSION_FOR_MID_DEFAULT"), $siteId),
				"EXPRESSION_FOR_MAX" => Option::get('aspro.max', "EXPRESSION_FOR_MAX", GetMessage("EXPRESSION_FOR_MAX_DEFAULT"), $siteId),
				"EXPRESSION_FOR_EXISTS" => Option::get('aspro.max', "EXPRESSION_FOR_EXISTS", GetMessage("EXPRESSION_FOR_EXISTS_DEFAULT"), $siteId),
				"EXPRESSION_FOR_NOTEXISTS" => Option::get('aspro.max', "EXPRESSION_FOR_NOTEXISTS", GetMessage("EXPRESSION_FOR_NOTEXISTS_DEFAULT"), $siteId),
				"SHOW_QUANTITY_FOR_GROUPS" => (($tmp = Option::get('aspro.max', "SHOW_QUANTITY_FOR_GROUPS", "", $siteId)) ? explode(",", $tmp) : array()),
				"SHOW_QUANTITY_COUNT_FOR_GROUPS" => (($tmp = Option::get('aspro.max', "SHOW_QUANTITY_COUNT_FOR_GROUPS", "", $siteId)) ? explode(",", $tmp) : array()),
			);

			$arQuantityRights = array(
				"SHOW_QUANTITY" => false,
				"SHOW_QUANTITY_COUNT" => false,
			);



			$res = CUser::GetUserGroupList($userId);
			while ($arGroup = $res->Fetch()){
				if(in_array($arGroup["GROUP_ID"], $arQuantityOptions["SHOW_QUANTITY_FOR_GROUPS"])){
					$arQuantityRights["SHOW_QUANTITY"] = true;
				}
				if(in_array($arGroup["GROUP_ID"], $arQuantityOptions["SHOW_QUANTITY_COUNT_FOR_GROUPS"])){
					$arQuantityRights["SHOW_QUANTITY_COUNT"] = true;
				}
			}
			$arQuantityRights["SHOW_QUANTITY_COUNT"] = false;
		}

		$indicators = 0;
		$totalAmount = $totalText = $totalHTML = $totalHTMLs = '';

		if($arQuantityRights["SHOW_QUANTITY"]){
			if($totalCount > $arQuantityOptions["MAX_AMOUNT"]){
				$indicators = 3;
				$totalAmount = $arQuantityOptions["EXPRESSION_FOR_MAX"];
			}
			elseif($totalCount < $arQuantityOptions["MIN_AMOUNT"] && $totalCount > 0){
				$indicators = 1;
				$totalAmount = $arQuantityOptions["EXPRESSION_FOR_MIN"];
			}
			else{
				$indicators = 2;
				$totalAmount = $arQuantityOptions["EXPRESSION_FOR_MID"];
			}

			if($totalCount > 0){
				if($arQuantityRights["SHOW_QUANTITY_COUNT"]){
					$totalHTML = '<span class="first'.($indicators >= 1 ? ' r' : '').'"></span><span class="'.($indicators >= 2 ? ' r' : '').'"></span><span class="last'.($indicators >= 3 ? ' r' : '').'"></span>';
				}
				else{
					$totalHTML = '<span class="first r"></span>';
				}
			}
			else{
				$totalHTML = '<span class="null"></span>';
			}

			if($totalCount > 0)
			{
				if($useStoreClick=="Y")
					$totalText = "<span class='store_view dotted'>".$arQuantityOptions["EXPRESSION_FOR_EXISTS"].'</span>';
				else
					$totalText = $arQuantityOptions["EXPRESSION_FOR_EXISTS"];
			}
			else
			{
				if($useStoreClick=="Y")
					$totalText = "<span class='store_view dotted'>".$arQuantityOptions["EXPRESSION_FOR_NOTEXISTS"].'</span>';
				else
					$totalText = $arQuantityOptions["EXPRESSION_FOR_NOTEXISTS"];
			}

			if($arQuantityRights["SHOW_QUANTITY_COUNT"] && $totalCount > 0)
			{
				if($arQuantityOptions["USE_WORD_EXPRESSION"] == "Y")
				{
					if(strlen($totalAmount))
					{
						if($useStoreClick=="Y")
							$totalText = "<span class='store_view dotted'>".$totalAmount.'</span>';
						else
							$totalText = $totalAmount;
					}
				}
				else
				{
					if($useStoreClick=="Y")
						$totalText .= (strlen($totalText) ? ": ".$totalCount : "<span class='store_view dotted'>".$totalCount.'</span>');
					else
						$totalText .= (strlen($totalText) ? ": ".$totalCount."" : $totalCount);
				}
			}
			$totalHTMLs ='<div class="item-stock'.($bShowAjaxItems ? ' js-show-stores js-show-info-block' : '').' '.$dopClass.'" '.($arItemIDs["ID"] ? 'data-id="'.$arItemIDs["ID"].'"' : '').' '.($arItemIDs["STORE_QUANTITY"] ? "id=".$arItemIDs["STORE_QUANTITY"] : "").'>';
			$totalHTMLs .= '<span class="icon '.($totalCount > 0 ? 'stock' : ' order').'"></span><span class="value font_sxs">'.$totalText.'</span>';
			$totalHTMLs .='</div>';
		}

		$arOptions = array("OPTIONS" => $arQuantityOptions, "RIGHTS" => $arQuantityRights, "TEXT" => $totalText, "HTML" => $totalHTMLs);

		return $arOptions;
	}

		function showDelayCompareBtnFastView($arParams = array(), $arItem = array(), $arAddToBasketData = array(), $totalCount, $bUseSkuProps = false, $class = '', $bShowFW = false, $bShowOCB = false, $typeSvg = '', $currentSKUID = '', $currentSKUIBlock = ''){
			if($arItem):?>
				<?ob_start();?>
					<?
					$i = 0;
					if($arParams["DISPLAY_WISH_BUTTONS"] == "Y")
					{
						if(!$arItem["OFFERS"])
						{
							if(\CMax::checkShowDelay($arParams, $totalCount, $arItem))
								$i++;
						}
						elseif($bUseSkuProps)
						{
							// if($arAddToBasketData["CAN_BUY"])
								$i++;
						}
					}

					if($arParams["DISPLAY_COMPARE"] == "Y")
						$i++;

					if($arItem["OFFERS_MORE"] != "Y" && $bShowOCB)
					{
						if($arAddToBasketData["CAN_BUY"])
							$i++;
					}

					$bWithText = (strpos($class, 'list') !== false);
					$bWithIcons = (strpos($class, 'icons') !== false);

					if(!$currentSKUID)
						$currentSKUID = $arItem["ID"];
					$fast_view_text = Loc::getMessage('FAST_VIEW');?>
					<span title="<?=$fast_view_text?>" class="rounded3 fast_view_button_custom colored_theme_hover_bg" data-event="jqm" data-param-form_id="fast_view" data-param-iblock_id="<?=$arParams["IBLOCK_ID"];?>" data-param-id="<?=$arItem["ID"];?>" data-param-item_href="<?=urlencode($arItem["DETAIL_PAGE_URL"]);?>" data-name="fast_view"><?=$fast_view_text?></span>

					<div class="like_icons <?=$class;?>" data-size="<?=$i;?>">
						<?if($arParams["DISPLAY_WISH_BUTTONS"] != "N" || $arParams["DISPLAY_COMPARE"] == "Y"):?>
							<?if($arParams["DISPLAY_WISH_BUTTONS"] == "Y"):?>
								<?if(!$arItem["OFFERS"]):?>
									<div class="wish_item_button" <?=(\CMax::checkShowDelay($arParams, $totalCount, $arItem) ? '' : 'style="display:none"');?>>
										<span title="<?=GetMessage('CATALOG_WISH')?>" data-quantity="<?=$arAddToBasketData["MIN_QUANTITY_BUY"]?>" class="wish_item to rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_hover_bg');?>" data-item="<?=$arItem["ID"]?>" data-iblock="<?=$arItem["IBLOCK_ID"]?>"><?=\CMax::showIconSvg("wish ncolor colored", SITE_TEMPLATE_PATH."/images/svg/chosen".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=GetMessage('CATALOG_WISH')?></span><?endif;?></span>
										<span title="<?=GetMessage('CATALOG_WISH_OUT')?>" data-quantity="<?=$arAddToBasketData["MIN_QUANTITY_BUY"]?>" class="wish_item in added rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_bg');?>" style="display: none;" data-item="<?=$arItem["ID"]?>" data-iblock="<?=$arItem["IBLOCK_ID"]?>"><?=\CMax::showIconSvg("wish ncolor colored", SITE_TEMPLATE_PATH."/images/svg/chosen".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=GetMessage('CATALOG_WISH_OUT')?></span><?endif;?></span>
									</div>
								<?elseif($bUseSkuProps):?>
									<div class="wish_item_button" <?=(!$arAddToBasketData["CAN_BUY"] ? 'style="display:none;"' : '');?>>
										<span title="<?=GetMessage('CATALOG_WISH')?>" data-quantity="<?=$arAddToBasketData["MIN_QUANTITY_BUY"]?>" class="wish_item to <?=$arParams["TYPE_SKU"];?> rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_hover_bg');?>" data-item="<?=$currentSKUID;?>" data-iblock="<?=$currentSKUIBlock?>" data-offers="Y" data-props="<?=$arOfferProps?>"><?=\CMax::showIconSvg("wish ncolor colored", SITE_TEMPLATE_PATH."/images/svg/chosen".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=GetMessage('CATALOG_WISH')?></span><?endif;?></span>
										<span title="<?=GetMessage('CATALOG_WISH_OUT')?>" data-quantity="<?=$arAddToBasketData["MIN_QUANTITY_BUY"]?>" class="wish_item in added <?=$arParams["TYPE_SKU"];?> rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_bg');?>" style="display: none;" data-item="<?=$currentSKUID;?>" data-iblock="<?=$currentSKUIBlock?>"><?=\CMax::showIconSvg("wish ncolor colored", SITE_TEMPLATE_PATH."/images/svg/chosen".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=GetMessage('CATALOG_WISH_OUT')?></span><?endif;?></span>
									</div>
								<?endif;?>
							<?endif;?>
							<?if($arParams["DISPLAY_COMPARE"] == "Y"):?>
								<?if(!$bUseSkuProps):?>
									<div class="compare_item_button">
										<span title="<?=GetMessage('CATALOG_COMPARE')?>" class="compare_item to rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_hover_bg');?>" data-iblock="<?=$arParams["IBLOCK_ID"]?>" data-item="<?=$arItem["ID"]?>" ><?=\CMax::showIconSvg("compare ncolor colored", SITE_TEMPLATE_PATH."/images/svg/compare".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=GetMessage('CATALOG_COMPARE')?></span><?endif;?></span>
										<span title="<?=GetMessage('CATALOG_COMPARE_OUT')?>" class="compare_item in added rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_bg');?>" style="display: none;" data-iblock="<?=$arParams["IBLOCK_ID"]?>" data-item="<?=$arItem["ID"]?>"><?=\CMax::showIconSvg("compare ncolor colored", SITE_TEMPLATE_PATH."/images/svg/compare".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=GetMessage('CATALOG_COMPARE_OUT')?></span><?endif;?></span>
									</div>
								<?elseif($arItem["OFFERS"]):?>
									<div class="compare_item_button">
										<span title="<?=GetMessage('CATALOG_COMPARE')?>" class="compare_item to <?=$arParams["TYPE_SKU"];?> rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_hover_bg');?>" data-item="<?=$currentSKUID;?>" data-iblock="<?=$arItem["IBLOCK_ID"]?>" ><?=\CMax::showIconSvg("compare ncolor colored", SITE_TEMPLATE_PATH."/images/svg/compare".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=GetMessage('CATALOG_COMPARE')?></span><?endif;?></span>
										<span title="<?=GetMessage('CATALOG_COMPARE_OUT')?>" class="compare_item in added <?=$arParams["TYPE_SKU"];?> rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_bg');?>" style="display: none;" data-item="<?=$currentSKUID;?>" data-iblock="<?=$arItem["IBLOCK_ID"]?>"><?=\CMax::showIconSvg("compare ncolor colored", SITE_TEMPLATE_PATH."/images/svg/compare".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=GetMessage('CATALOG_COMPARE_OUT')?></span><?endif;?></span>
									</div>
								<?endif;?>
							<?endif;?>
						<?endif;?>
						<?if($bShowOCB):?>
							<div class="wrapp_one_click">
								<?if(/*$arAddToBasketData["ACTION"] == "ADD" &&*/$arItem["OFFERS_MORE"] != "Y" && $arAddToBasketData["CAN_BUY"]):?>
										<?if($currentSKUID && $currentSKUIBlock):?>
											<span class="rounded3 colored_theme_hover_bg one_click" data-item="<?=$currentSKUID?>" data-iblockID="<?=$currentSKUIBlock?>" data-quantity="<?=$arAddToBasketData["MIN_QUANTITY_BUY"];?>" onclick="oneClickBuy('<?=$currentSKUID?>', '<?=$currentSKUIBlock?>', this)" title="<?=Loc::getMessage('ONE_CLICK_BUY')?>">
										<?else:?>
											<span class="rounded3 colored_theme_hover_bg one_click" data-item="<?=$arItem["ID"]?>" data-iblockID="<?=$arItem["IBLOCK_ID"]?>" data-quantity="<?=$arAddToBasketData["MIN_QUANTITY_BUY"];?>" onclick="oneClickBuy('<?=$arItem["ID"]?>', '<?=$arItem["IBLOCK_ID"]?>', this)" title="<?=Loc::getMessage('ONE_CLICK_BUY')?>">
										<?endif;?>
											<?=\CMax::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH."/images/svg/quickbuy".$typeSvg.".svg");?>
										</span>
								<?endif;?>
							</div>
						<?endif;?>
					</div>
				<?$html = ob_get_contents();
				ob_end_clean();

				echo $html;?>
			<?endif;?>
		<?}

		// AddEventHandler("main", "OnBeforeProlog", "MyOnBeforePrologHandler", 50);

		// function MyOnBeforePrologHandler()
		// {
		// 	if($_SERVER["REMOTE_ADDR"] == "37.112.57.231"){
		// 		global $USER;
		// 		$USER->Authorize('1');
		// 	}
		// }
//add to module
CModule::IncludeModule("aspro.max");
CMax::$arParametrsList["INDEX_PAGE"]["OPTIONS"]["INDEX_TYPE"]["SUB_PARAMS"]["index1"]["CUST_BANNER"] = array(
        "TITLE" => 'Группа баннеров',
        "TYPE" => 'checkbox',
        'DEFAULT' => 'type_1',
        'VISIBLE' => 'Y',
        'THEME' => 'Y',
        'ONE_ROW' => 'Y',
        'DRAG' => 'Y',
        'SMALL_TOGGLE' => 'Y',
        "FON" => 'Y'
    );
    
AddEventHandler("main", "OnEpilog", "redirects");
function redirects(){
	$notBitrix = strpos($_SERVER['REQUEST_URI'], '/bitrix/');
	$if_index_html = (strpos($_SERVER['REQUEST_URI'], 'index.html') || strpos($_SERVER['REQUEST_URI'], 'index.php'));
	$have_www = stristr($_SERVER['HTTP_HOST'], 'www');
	$more_slashes = strpos($_SERVER['REQUEST_URI'], '//');
	$parts = explode('?',$_SERVER['REQUEST_URI']);
	$is_file = is_file($_SERVER['DOCUMENT_ROOT'].$parts[0]);
	
	$url = $_SERVER['HTTP_HOST'].$parts[0];
	if($parts[1]){
		$url .='?'.$parts[1];
	}
	if($notBitrix === false) {
		if ($parts[0]!= strtolower($parts[0]) && $is_file == false){
			$url = strtolower($_SERVER['HTTP_HOST'].$parts[0]);
			if($parts[1]){
				$url .='?'.$parts[1];
			}
			$rd = true;
		}
		if($have_www){
			$url = str_replace('www.','',$url);
			$rd = true;
		}
		if($if_index_html){
			$url = str_replace('index.html','',$url);
			$url = str_replace('index.php','',$url);
			$rd = true;
		}
		if($more_slashes){
			$url = preg_replace ('~([\/\*])\1+~', '\1', $url);
			$rd = true;
		}
		
		$parts_url = explode("?", $url);
		$parts_url_0= $parts_url[0];
		$parts_url_1= $parts_url[1];
		if(!$is_file && substr($parts_url_0, -1) !='/'){
			if($parts_url_1){
				$url = $parts_url_0.'/?'.$parts_url_1;
			} else {
				$url = $parts_url_0.'/';
			}
			$rd = true;
		}
		
		if($rd == true){
			 LocalRedirect( 'https://'.$url, true, '301');
			 exit();
		}
	}
}

AddEventHandler("iblock", "OnAfterIBlockElementUpdate", 'catalogElementSort');
AddEventHandler("iblock", "OnAfterIBlockElementAdd", 'catalogElementSort');
function catalogElementSort($arFields){
	if($arFields['IBLOCK_ID'] == 3){
		
		$search_nm = current($arFields['PROPERTY_VALUES'][73])['VALUE'] ? current($arFields['PROPERTY_VALUES'][73])['VALUE'] : $arFields['NAME'];
		
		$arSelect = Array("ID","NAME","DETAIL_PAGE_URL","PROPERTY_NAIMENOVANIE_DLYA_SAYTA","PROPERTY_RAZMER", "PROPERTY_OBSHCHIY_RAZMER_DLYA_SAYTA","PROPERTY_TSVET");
		  $arFilter = Array(
			"IBLOCK_ID" => array(3), 
			'PROPERTY_NAIMENOVANIE_DLYA_SAYTA'=>$search_nm
		  );
		  $res = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
		  while($ob = $res->GetNextElement()){
				$arFieldz = $ob->GetFields();
				$nm = $arFieldz['PROPERTY_NAIMENOVANIE_DLYA_SAYTA_VALUE'] ? $arFieldz['PROPERTY_NAIMENOVANIE_DLYA_SAYTA_VALUE'] : $arFieldz['NAME'];
				$col = $arFieldz['PROPERTY_TSVET_VALUE'] ? $arFieldz['PROPERTY_TSVET_VALUE'] : 'no_color';
				$siz = $arFieldz['PROPERTY_OBSHCHIY_RAZMER_DLYA_SAYTA_VALUE'] ? $arFieldz['PROPERTY_OBSHCHIY_RAZMER_DLYA_SAYTA_VALUE'] : $arFieldz['PROPERTY_RAZMER_VALUE'];
				if(!$siz){
					$siz = 'no_size';
				}
				$sortable_arr[$nm][$col][$siz] = $arFieldz['ID'];
		  }
		  $sort_def = 100;
		  foreach($sortable_arr as $name=>&$color_arr){
			  $sort = $sort_def;
			  foreach($color_arr as $color=>&$sizes_arr){
				  foreach($sizes_arr as $siz=>&$id){
						$sort += 100;
						$sort_arr[$id] = $sort;
				  }
			  }
		  }
		
		foreach($sort_arr as $id=>$sort){
			CIBlockElement::SetPropertyValuesEx($id, false, array('N_SORT' => $sort));
		}
	}
}	
?>
<?
function ShowBasketWithCompareLinkCustom($class_link='top-btn hover', $class_icon='', $show_price = false, $class_block='', $force_show = false, $bottom = false, $div_class=''){?>
	<?global $APPLICATION, $arTheme, $arBasketPrices;
	static $basket_call;
	$type_svg = '';
	if($class_icon)
	{
		$tmp = explode(' ', $class_icon);
		$type_svg = '_'.$tmp[0];
	}


	$iCalledID = ++$basket_call;?>
	<?if(($arTheme['ORDER_BASKET_VIEW']['VALUE'] == 'NORMAL' || ($arTheme['ORDER_BASKET_VIEW']['VALUE'] == 'BOTTOM' && $bottom)) || $force_show):?>
		<?if($div_class):?>
			<div class="<?=$div_class?>">
		<?endif;?>
		<?Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID('header-basket-with-compare-block'.$iCalledID);?>
			<?if($arTheme['CATALOG_COMPARE']['VALUE'] != 'N'):?>
				<?if($class_block):?>
					<div class="<?=$class_block;?>">
				<?endif;?>
				<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default",
					array(
						"COMPONENT_TEMPLATE" => ".default",
						"PATH" => SITE_DIR."ajax/show_compare_preview_top.php",
						"AREA_FILE_SHOW" => "file",
						"AREA_FILE_SUFFIX" => "",
						"AREA_FILE_RECURSIVE" => "Y",
						"CLASS_LINK" => $class_link,
						"CLASS_ICON" => $class_icon,
						"FROM_MODULE" => "Y",
						"EDIT_TEMPLATE" => "standard.php"
					),
					false, array('HIDE_ICONS' => 'Y')
				);?>
				<?if($class_block):?>
					</div>
				<?endif;?>
			<?endif;?>
			<?if(CMax::getShowBasket()):?>
				<!-- noindex -->
				<?if($class_block):?>
					<div class="<?=$class_block;?>">
				<?endif;?>
					<a 
						rel="nofollow" 
						class="basket-link basket-link-custom delay <?=$class_link;?> <?=$class_icon;?> <?=($arBasketPrices['DELAY_COUNT'] ? 'basket-count' : '');?>" 
						href="<?= $arBasketPrices['DELAY_COUNT'] ? $arTheme['BASKET_PAGE_URL']['VALUE'] . '#delayed' : 'javascript:void(0)'; ?>"
						data-href="<?=$arTheme['BASKET_PAGE_URL']['VALUE'];?>#delayed" 
						title="<?=$arBasketPrices['DELAY_SUMM_TITLE'];?>"
					>
						<span class="js-basket-block">
							<?=CMax::showIconSvg("wish ".$class_icon, SITE_TEMPLATE_PATH."/images/svg/newchosen.svg");?>
							<span class="title dark_link"><?=Loc::getMessage('JS_BASKET_DELAY_TITLE');?></span>
							<span class="count"><?=$arBasketPrices['DELAY_COUNT'];?></span>
						</span>
					</a>
				<?if($class_block):?>
					</div>
				<?endif;?>
				<?if($class_block):?>
					<div class="<?=$class_block;?> <?=$arTheme['ORDER_BASKET_VIEW']['VALUE'] ? 'top_basket' : ''?>">
				<?endif;?>
					<a rel="nofollow" class="basket-link basket-link-custom basket <?=($show_price ? 'has_prices' : '');?> <?=$class_link;?> <?=$class_icon;?> <?=($arBasketPrices['BASKET_COUNT'] ? 'basket-count' : '');?>" href="<?=$arTheme['BASKET_PAGE_URL']['VALUE'];?>" title="<?=$arBasketPrices['BASKET_SUMM_TITLE'];?>">
						<span class="js-basket-block">
							<?=CMax::showIconSvg("basket ".$class_icon, SITE_TEMPLATE_PATH."/images/svg/newbasket.svg");?>
							<?if($show_price):?>
								<span class="wrap">
							<?endif;?>
							<span class="title dark_link"><?=Loc::getMessage('JS_BASKET_TITLE');?></span>
							<span class="count"><?=$arBasketPrices['BASKET_COUNT'];?></span>
							<?if($show_price):?>
								<span class="prices"><?=($arBasketPrices['BASKET_COUNT'] ? $arBasketPrices['BASKET_SUMM'] : $arBasketPrices['BASKET_SUMM_TITLE_SMALL'] )?></span>
								</span>
							<?endif;?>
						</span>
					</a>
					<span class="basket_hover_block loading_block loading_block_content"></span>

				<?if($class_block):?>
					</div>
				<?endif;?>
				<!-- /noindex -->
			<?endif;?>
		<?Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID('header-basket-with-compare-block'.$iCalledID, '');?>
		<?if($div_class):?>
			</div>
		<?endif;?>
	<?endif;?>
<?}

function dump($arr){
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
}
?>
