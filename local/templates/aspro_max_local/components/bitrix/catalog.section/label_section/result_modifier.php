<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if($arResult['ITEMS']){
	foreach($arResult['ITEMS']  as $k=>&$arItem){
		if(!in_array($arItem['IBLOCK_SECTION_ID'],$sections)){
			$sections[] = $arItem['IBLOCK_SECTION_ID'];
		}
		$arResult['PRODUCTS'][] = $arItem['ID'];
		
		$arSort= Array("NAME"=>"ASC");
		$arSelect = Array("ID","NAME","DETAIL_PAGE_URL","PROPERTY_NAIMENOVANIE_DLYA_SAYTA","PROPERTY_RAZMER", "PROPERTY_OBSHCHIY_RAZMER_DLYA_SAYTA","PROPERTY_TSVET");
		  $arFilter = Array(
			"IBLOCK_ID" => $arParams['IBLOCK_ID'], 
			"PROPERTY_TSVET" => array($arItem['PROPERTIES']['TSVET']['VALUE']),
			'CATALOG_AVAILABLE'=>'Y',
		  );
		  if(!$arItem['PROPERTIES']['NAIMENOVANIE_DLYA_SAYTA']['VALUE']){
			$arFilter["NAME"] = array($arItem['NAME']);
		  } else {
			$arFilter["PROPERTY_NAIMENOVANIE_DLYA_SAYTA"]= array($arItem['PROPERTIES']['NAIMENOVANIE_DLYA_SAYTA']['VALUE']);
		  }
		  $res = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
		  while($ob = $res->GetNextElement()){
			  $arFields = $ob->GetFields();
			  // Массив для искусственной сортировки
			  $sizesOrder = array(
				 'XS'    => 100,
				 'S'     => 110,
				 'M'     => 120,
				 'L'     => 130,
				 'XL'    => 140,
				 'XXL'   => 150,
				 '3XL'   => 160,
				 'XXXL'  => 161,
				 '4XL'   => 170,
				 'XXXXL' => 171,
			  );

			  $arr = [];
			  $arr["ID"] = $arFields['ID'];
			  $arr["DETAIL_PAGE_URL"] = $arFields['DETAIL_PAGE_URL'];
			  $arr["RAZMER"] = $arFields['PROPERTY_OBSHCHIY_RAZMER_DLYA_SAYTA_VALUE'] ? $arFields['PROPERTY_OBSHCHIY_RAZMER_DLYA_SAYTA_VALUE'] : $arFields['PROPERTY_RAZMER_VALUE'];

			  // Присваем индексы для дальнейшей сортировки
			  $arr["ORDER"] = array_key_exists($arr["RAZMER"], $sizesOrder) ?  $sizesOrder[$arr["RAZMER"]] : 0;

			  $arItem['SIZES'][] = $arr;
		  }
	}
	
	$arResult['SECTION_CODES'] = array();
	$resSect = CIBlockSection::GetList(
		array("SORT"=>"ASC"),
		array("ID" => $sections),
		false,
		array("CODE","ID"),
		false
	);
	while($ar_result = $resSect->GetNext())
  	{
  	  $arResult['SECTION_CODES'][$ar_result['ID']] = $ar_result['CODE'];
  	}
	$this->__component->arResult['PRODUCTS'] = $arResult['PRODUCTS'];
	$this->__component->SetResultCacheKeys(array('PRODUCTS'));
}
