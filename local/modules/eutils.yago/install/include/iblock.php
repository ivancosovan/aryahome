<?
IncludeModuleLangFile(__FILE__);
if(CModule::IncludeModule("iblock")):
	//Install iblok type
	$arFields = Array(
		'ID'=>'eutils_yandex_go',
		'SECTIONS'=>'N',
		'IN_RSS'=>'N',
		'SORT'=>500,
		'LANG'=>Array(
			'en'=>Array(
				'NAME'=>'Yandex.GO Delivery',
				'SECTION_NAME'=>'Sections',
				'ELEMENT_NAME'=>'Operation'
				),
			'ru'=>Array(
				'NAME'=>'Доставка Яндекс.GO',
				'SECTION_NAME'=>'Разделы',
				'ELEMENT_NAME'=>'Элементы'
				)
			)
		);
	
	$obBlocktype = new CIBlockType;
// 	$DB->StartTransaction();
	$res = $obBlocktype->Add($arFields);
	if(!$res)
		{
// 		   $DB->Rollback();
		   echo 'Error: '.$obBlocktype->LAST_ERROR.'<br>';
		}
// 		else
// 		   $DB->Commit();
	
		//Install iblok
		$sites = array();
		$rsSites = CSite::GetList($by="sort", $order="desc", array());
		while($arSite = $rsSites->Fetch())
		{
		  $sites[] = $arSite["ID"];
		}
	$ib = new CIBlock;
	
	//ADD iblock
	$arFields = Array(
	  "ACTIVE" => 'Y',
	  "NAME" => 'Статусы доставки Яндекс.GO',
	  "CODE" => 'yandex_go_deliverys',
	  "IBLOCK_TYPE_ID" => 'eutils_yandex_go',
	  "SITE_ID" => $sites,
	  "WORKFLOW" => 'N',
	  "SORT" => 500,
	  "DESCRIPTION_TYPE" => 'text',
	  "GROUP_ID" => Array("1"=>"R", "2"=>"R"),
	  "LIST_PAGE_URL" => "#SITE_DIR#/statusy-dostavki/",
        "SECTION_PAGE_URL" => "#SITE_DIR#/statusy-dostavki/#SECTION_CODE#/",
        "DETAIL_PAGE_URL" => "#SITE_DIR#/statusy-dostavki/#SECTION_CODE#/#ELEMENT_ID#/",
      "FIELDS" => array(
        // Символьный код элементов
        "CODE" => array(
        "IS_REQUIRED" => "Y", // Обязательное
        "DEFAULT_VALUE" => array(
            "UNIQUE" => "Y", // Проверять на уникальность
            "TRANSLITERATION" => "Y", // Транслитерировать
            "TRANS_LEN" => "30", // Максмальная длина транслитерации
            "TRANS_CASE" => "L", // Приводить к нижнему регистру
            "TRANS_SPACE" => "-", // Символы для замены
            "TRANS_OTHER" => "-",
            "TRANS_EAT" => "Y",
            "USE_GOOGLE" => "N",
            ),
        ),
      )
	  );
	  $ID = $ib->Add($arFields);
	  
	 
	//Install iblok propertys
	$arProps = array(
					"CLAIM_ID" => array(
							  "NAME" => 'claimId (id заказа в Яндекс)',
							  "ACTIVE" => "Y",
							  "SORT" => "100",
							  "CODE" => "CLAIM_ID",
							  "PROPERTY_TYPE" => "S",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
                    "request_id" => array(
							  "NAME" => 'id исходящей заявки',
							  "ACTIVE" => "Y",
							  "SORT" => "100",
							  "CODE" => "request_id",
							  "PROPERTY_TYPE" => "S",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
                    "ORDER_ID" => array(
							  "NAME" => 'ID заказа',
							  "ACTIVE" => "Y",
							  "SORT" => "100",
							  "CODE" => "ORDER_ID",
							  "PROPERTY_TYPE" => "S",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
                    "available_cancel_state" => array(
							  "NAME" => 'Признак отмены заказа (информация от Яндекса)',
							  "ACTIVE" => "Y",
							  "SORT" => "100",
							  "CODE" => "available_cancel_state",
							  "PROPERTY_TYPE" => "L",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y',
							  "VALUES" => array (
							  					"0" => array( "XML_ID" => "free",
															  "VALUE" => 'free',
															  "DEF" => "N",
															  "SORT" => "100"
															),
												"1" => array( "XML_ID" => "paid",
															  "VALUE" => 'paid',
															  "DEF" => "N",
															  "SORT" => "200"
															),
                                                ),
                              ),
                    "eta" => array(
							  "NAME" => 'Примерное время ожидания, мин.',
							  "ACTIVE" => "Y",
							  "SORT" => "100",
							  "CODE" => "eta",
							  "PROPERTY_TYPE" => "S",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
                    "PICKUP" => array(
							  "NAME" => 'Доставка из',
							  "ACTIVE" => "Y",
							  "SORT" => "100",
							  "CODE" => "PICKUP",
							  "PROPERTY_TYPE" => "S",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
                    "DESTINATION" => array(
							  "NAME" => 'Доставка в',
							  "ACTIVE" => "Y",
							  "SORT" => "100",
							  "CODE" => "DESTINATION",
							  "PROPERTY_TYPE" => "S",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
                    "DRIVER" => array(
							  "NAME" => 'Курьер',
							  "ACTIVE" => "Y",
							  "SORT" => "100",
							  "CODE" => "DRIVER",
							  "PROPERTY_TYPE" => "S",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
                    "DRIVER_PHONE" => array(
							  "NAME" => 'Тел. водителя',
							  "ACTIVE" => "Y",
							  "SORT" => "100",
							  "CODE" => "DRIVER_PHONE",
							  "PROPERTY_TYPE" => "S",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
                    "car_number" => array(
							  "NAME" => 'Номер авто (или курьера)',
							  "ACTIVE" => "Y",
							  "SORT" => "100",
							  "CODE" => "car_number",
							  "PROPERTY_TYPE" => "S",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
                    "car_model" => array(
							  "NAME" => 'Марка авто',
							  "ACTIVE" => "Y",
							  "SORT" => "100",
							  "CODE" => "car_model",
							  "PROPERTY_TYPE" => "S",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
                    "status" => array(
							  "NAME" => 'Статус доставки',
							  "ACTIVE" => "Y",
							  "SORT" => "100",
							  "CODE" => "status",
							  "PROPERTY_TYPE" => "L",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y',
							  "VALUES" => array (
							  					"0" => array( "XML_ID" => "new",
															  "VALUE" => 'new',
															  "DEF" => "N",
															  "SORT" => "100"
															),
												"1" => array( "XML_ID" => "estimating",
															  "VALUE" => 'estimating',
															  "DEF" => "N",
															  "SORT" => "100"
															),
                                                "2" => array( "XML_ID" => "estimating_failed",
															  "VALUE" => 'estimating_failed',
															  "DEF" => "N",
															  "SORT" => "100"
															),
                                                "3" => array( "XML_ID" => "ready_for_approval",
															  "VALUE" => 'ready_for_approval',
															  "DEF" => "N",
															  "SORT" => "100"
															),
                                                "4" => array( "XML_ID" => "accepted",
															  "VALUE" => 'accepted',
															  "DEF" => "N",
															  "SORT" => "100"
															),
                                                "5" => array( "XML_ID" => "performer_lookup",
															  "VALUE" => 'performer_lookup',
															  "DEF" => "N",
															  "SORT" => "100"
															),
                                                "6" => array( "XML_ID" => "performer_draft",
															  "VALUE" => 'performer_draft',
															  "DEF" => "N",
															  "SORT" => "100"
															),
                                                "7" => array( "XML_ID" => "performer_found",
															  "VALUE" => 'performer_found',
															  "DEF" => "N",
															  "SORT" => "100"
															),
                                                "8" => array( "XML_ID" => "performer_not_found",
															  "VALUE" => 'performer_not_found',
															  "DEF" => "N",
															  "SORT" => "300"
															),
                                                "9" => array( "XML_ID" => "pickup_arrived",
															  "VALUE" => 'pickup_arrived',
															  "DEF" => "N",
															  "SORT" => "200"
															),
                                                "10" => array( "XML_ID" => "ready_for_pickup_confirmation",
															  "VALUE" => 'ready_for_pickup_confirmation',
															  "DEF" => "N",
															  "SORT" => "200"
															),
                                                "11" => array( "XML_ID" => "pickuped",
															  "VALUE" => 'pickuped',
															  "DEF" => "N",
															  "SORT" => "200"
															),
                                                "12" => array( "XML_ID" => "delivery_arrived",
															  "VALUE" => 'delivery_arrived',
															  "DEF" => "N",
															  "SORT" => "200"
															),
                                                "13" => array( "XML_ID" => "ready_for_delivery_confirmation",
															  "VALUE" => 'ready_for_delivery_confirmation',
															  "DEF" => "N",
															  "SORT" => "200"
															),
                                                "14" => array( "XML_ID" => "pay_waiting",
															  "VALUE" => 'pay_waiting',
															  "DEF" => "N",
															  "SORT" => "200"
															),
                                                "15" => array( "XML_ID" => "delivered",
															  "VALUE" => 'delivered',
															  "DEF" => "N",
															  "SORT" => "200"
															),
                                                "16" => array( "XML_ID" => "delivered_finish",
															  "VALUE" => 'delivered_finish',
															  "DEF" => "N",
															  "SORT" => "300"
															),
                                                "17" => array( "XML_ID" => "returning",
															  "VALUE" => 'returning',
															  "DEF" => "N",
															  "SORT" => "200"
															),
                                                "18" => array( "XML_ID" => "return_arrived",
															  "VALUE" => 'return_arrived',
															  "DEF" => "N",
															  "SORT" => "200"
															),
                                                "19" => array( "XML_ID" => "ready_for_return_confirmation",
															  "VALUE" => 'ready_for_return_confirmation',
															  "DEF" => "N",
															  "SORT" => "200"
															),
                                                "20" => array( "XML_ID" => "returned",
															  "VALUE" => 'returned',
															  "DEF" => "N",
															  "SORT" => "200"
															),
                                                "21" => array( "XML_ID" => "returned_finish",
															  "VALUE" => 'returned_finish',
															  "DEF" => "N",
															  "SORT" => "300"
															),
                                                "22" => array( "XML_ID" => "failed",
															  "VALUE" => 'failed',
															  "DEF" => "N",
															  "SORT" => "300"
															),
                                                "23" => array( "XML_ID" => "cancelled",
															  "VALUE" => 'cancelled',
															  "DEF" => "N",
															  "SORT" => "300"
															),
                                                "24" => array( "XML_ID" => "cancelled_with_payment",
															  "VALUE" => 'cancelled_with_payment',
															  "DEF" => "N",
															  "SORT" => "300"
															),
                                                "25" => array( "XML_ID" => "cancelled_by_taxi",
															  "VALUE" => 'cancelled_by_taxi',
															  "DEF" => "N",
															  "SORT" => "300"
															),
                                                "26" => array( "XML_ID" => "cancelled_with_items_on_hands",
															  "VALUE" => 'cancelled_with_items_on_hands',
															  "DEF" => "N",
															  "SORT" => "300"
															)
                                                ),
                              ),
					"offer_id" => array(
							  "NAME" => 'Оффер',
							  "ACTIVE" => "Y",
							  "SORT" => "100",
							  "CODE" => "offer_id",
							  "PROPERTY_TYPE" => "S",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
                    "offer_price" => array(
							  "NAME" => 'Стоимость доставки',
							  "ACTIVE" => "Y",
							  "SORT" => "100",
							  "CODE" => "offer_price",
							  "PROPERTY_TYPE" => "S",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  )
				  );
	$ibp = new CIBlockProperty;
	foreach($arProps as $prop) {
		$PropID = $ibp->Add($prop);
	}
endif;
?>		
