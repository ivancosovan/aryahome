<?
use Bitrix\Main\Type\Collection;
use Bitrix\Currency\CurrencyTable;
use Bitrix\Iblock;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */

$displayPreviewTextMode = array(
	'H' => true,
	'E' => true,
	'S' => true
);
$detailPictMode = array(
	'IMG' => true,
	'POPUP' => true,
	'MAGNIFIER' => true,
	'GALLERY' => true
);

$arDefaultParams = array(
	'TYPE_SKU' => 'Y',
	'ADD_PICT_PROP' => '-',
	'OFFER_ADD_PICT_PROP' => '-',
	'OFFER_TREE_PROPS' => array('-'),
	'ADD_TO_BASKET_ACTION' => 'ADD',
	'DEFAULT_COUNT' => '1',
);
$arParams = array_merge($arDefaultParams, $arParams);
if ('TYPE_1' != $arParams['TYPE_SKU'] )
	$arParams['TYPE_SKU'] = 'N';

$arParams['ADD_PICT_PROP'] = trim($arParams['ADD_PICT_PROP']);
if ('-' == $arParams['ADD_PICT_PROP'])
	$arParams['ADD_PICT_PROP'] = '';
$arParams['LABEL_PROP'] = trim($arParams['LABEL_PROP']);
if ('-' == $arParams['LABEL_PROP'])
	$arParams['LABEL_PROP'] = '';
$arParams['OFFER_ADD_PICT_PROP'] = trim($arParams['OFFER_ADD_PICT_PROP']);
if ('-' == $arParams['OFFER_ADD_PICT_PROP'])
	$arParams['OFFER_ADD_PICT_PROP'] = '';
if (!is_array($arParams['OFFER_TREE_PROPS']))
	$arParams['OFFER_TREE_PROPS'] = array($arParams['OFFER_TREE_PROPS']);
foreach ($arParams['OFFER_TREE_PROPS'] as $key => $value)
{
	$value = (string)$value;
	if ('' == $value || '-' == $value)
		unset($arParams['OFFER_TREE_PROPS'][$key]);
}

/*stores product*/
$arStores=CMax::CCatalogStore_GetList(array(), array("ACTIVE" => "Y"), false, false, array());
$arResult["STORES_COUNT"] = count($arStores);

if ('N' != $arParams['DISPLAY_NAME'])
	$arParams['DISPLAY_NAME'] = 'Y';
if (!isset($detailPictMode[$arParams['DETAIL_PICTURE_MODE']]))
	$arParams['DETAIL_PICTURE_MODE'] = 'IMG';
if ('Y' != $arParams['ADD_DETAIL_TO_SLIDER'])
	$arParams['ADD_DETAIL_TO_SLIDER'] = 'N';
if (!isset($displayPreviewTextMode[$arParams['DISPLAY_PREVIEW_TEXT_MODE']]))
	$arParams['DISPLAY_PREVIEW_TEXT_MODE'] = 'E';
if ('Y' != $arParams['PRODUCT_SUBSCRIPTION'])
	$arParams['PRODUCT_SUBSCRIPTION'] = 'N';
if ('Y' != $arParams['SHOW_DISCOUNT_PERCENT'])
	$arParams['SHOW_DISCOUNT_PERCENT'] = 'N';
if ('Y' != $arParams['SHOW_OLD_PRICE'])
	$arParams['SHOW_OLD_PRICE'] = 'N';
if ('Y' != $arParams['SHOW_MAX_QUANTITY'])
	$arParams['SHOW_MAX_QUANTITY'] = 'N';
if ($arParams['SHOW_BASIS_PRICE'] != 'Y')
	$arParams['SHOW_BASIS_PRICE'] = 'N';
if (!is_array($arParams['ADD_TO_BASKET_ACTION']))
	$arParams['ADD_TO_BASKET_ACTION'] = array($arParams['ADD_TO_BASKET_ACTION']);
$arParams['ADD_TO_BASKET_ACTION'] = array_filter($arParams['ADD_TO_BASKET_ACTION'], 'CIBlockParameters::checkParamValues');
if (empty($arParams['ADD_TO_BASKET_ACTION']) || (!in_array('ADD', $arParams['ADD_TO_BASKET_ACTION']) && !in_array('BUY', $arParams['ADD_TO_BASKET_ACTION'])))
	$arParams['ADD_TO_BASKET_ACTION'] = array('BUY');
if ($arParams['SHOW_CLOSE_POPUP'] != 'Y')
	$arParams['SHOW_CLOSE_POPUP'] = 'N';

$arParams['MESS_BTN_BUY'] = trim($arParams['MESS_BTN_BUY']);
$arParams['MESS_BTN_ADD_TO_BASKET'] = trim($arParams['MESS_BTN_ADD_TO_BASKET']);
$arParams['MESS_BTN_SUBSCRIBE'] = trim($arParams['MESS_BTN_SUBSCRIBE']);
$arParams['MESS_BTN_COMPARE'] = trim($arParams['MESS_BTN_COMPARE']);
$arParams['MESS_NOT_AVAILABLE'] = trim($arParams['MESS_NOT_AVAILABLE']);
if ('Y' != $arParams['USE_VOTE_RATING'])
	$arParams['USE_VOTE_RATING'] = 'N';
if ('vote_avg' != $arParams['VOTE_DISPLAY_AS_RATING'])
	$arParams['VOTE_DISPLAY_AS_RATING'] = 'rating';
if ('Y' != $arParams['USE_COMMENTS'])
	$arParams['USE_COMMENTS'] = 'N';
if ('Y' != $arParams['BLOG_USE'])
	$arParams['BLOG_USE'] = 'N';
if ('Y' != $arParams['VK_USE'])
	$arParams['VK_USE'] = 'N';
if ('Y' != $arParams['FB_USE'])
	$arParams['FB_USE'] = 'N';
if ('Y' == $arParams['USE_COMMENTS'])
{
	if ('N' == $arParams['BLOG_USE'] && 'N' == $arParams['VK_USE'] && 'N' == $arParams['FB_USE'])
		$arParams['USE_COMMENTS'] = 'N';
}

$arEmptyPreview = false;
$strEmptyPreview = SITE_TEMPLATE_PATH.'/images/svg/noimage_product.svg';
if(file_exists($_SERVER['DOCUMENT_ROOT'].$strEmptyPreview))
{
	/*$arSizes = getimagesize($_SERVER['DOCUMENT_ROOT'].$strEmptyPreview);
	if (!empty($arSizes))
	{*/
		$arEmptyPreview = array(
			'SRC' => $strEmptyPreview,
			/*'WIDTH' => (int)$arSizes[0],
			'HEIGHT' => (int)$arSizes[1]*/
		);
/*	}
	unset($arSizes);*/
}
unset($strEmptyPreview);

$arSKUPropList = array();
$arSKUPropIDs = array();
$arSKUPropKeys = array();
$boolSKU = false;
$strBaseCurrency = '';
$boolConvert = isset($arResult['CONVERT_CURRENCY']['CURRENCY_ID']);


if(is_array($arResult['PROPERTIES']['CML2_ARTICLE']['VALUE']))
{
	$arResult['PROPERTIES']['CML2_ARTICLE']['VALUE'] = reset($arResult['PROPERTIES']['CML2_ARTICLE']['VALUE']);
	$arResult['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'] = reset($arResult['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE']);
}

if ($arResult['MODULES']['catalog'])
{
	if (!$boolConvert)
		$strBaseCurrency = CCurrency::GetBaseCurrency();

	$arSKU = CCatalogSKU::GetInfoByProductIBlock($arParams['IBLOCK_ID']);
	$boolSKU = !empty($arSKU) && is_array($arSKU);
	$bUseModuleProps = \Bitrix\Main\Config\Option::get("iblock", "property_features_enabled", "N") === "Y";

	if ($bUseModuleProps)
		$arParams['OFFERS_CART_PROPERTIES'] = \Bitrix\Catalog\Product\PropertyCatalogFeature::getBasketPropertyCodes($arSKU['IBLOCK_ID'], ['CODE' => 'Y']);

	if (empty($arParams['OFFER_TREE_PROPS']) && isset($arParams['OFFERS_CART_PROPERTIES']) && is_array($arParams['OFFERS_CART_PROPERTIES'])) {
		$arParams['OFFER_TREE_PROPS'] = $arParams['OFFERS_CART_PROPERTIES'];
		foreach ($arParams['OFFER_TREE_PROPS'] as $key => $value)
		{
			$value = (string)$value;
			if ('' == $value || '-' == $value)
				unset($arParams['OFFER_TREE_PROPS'][$key]);
		}
	}

	if ( $bUseModuleProps && $boolSKU && $featureProps = \Bitrix\Catalog\Product\PropertyCatalogFeature::getOfferTreePropertyCodes( $arSKU["IBLOCK_ID"], array('CODE' => 'Y')) ) {
		$arParams['OFFER_TREE_PROPS'] = $featureProps;
	}
	if ( $boolSKU && $featureProps = \Bitrix\Iblock\Model\PropertyFeature::getDetailPageShowPropertyCodes( $arSKU["IBLOCK_ID"], array('CODE' => 'Y') ) ) {
		$arParams['OFFERS_PROPERTY_CODE'] = $featureProps;
	}

	if ($boolSKU && !empty($arParams['OFFER_TREE_PROPS']))
	{
		$arSKUPropList = CIBlockPriceTools::getTreeProperties(
			$arSKU,
			$arParams['OFFER_TREE_PROPS'],
			array(
				//'PICT' => $arEmptyPreview,
				'NAME' => '-'
			)
		);
		$arResult["SKU_IBLOCK_ID"]=$arSKU["IBLOCK_ID"];
		$arSKUPropIDs = array_keys($arSKUPropList);

	}
}
$arConvertParams = array();
if ('Y' == $arParams['CONVERT_CURRENCY'])
{
	if (!CModule::IncludeModule('currency'))
	{
		$arParams['CONVERT_CURRENCY'] = 'N';
		$arParams['CURRENCY_ID'] = '';
	}
	else
	{
		$arResultModules['currency'] = true;
		$arCurrencyInfo = CCurrency::GetByID($arParams['CURRENCY_ID']);
		if (!(is_array($arCurrencyInfo) && !empty($arCurrencyInfo)))
		{
			$arParams['CONVERT_CURRENCY'] = 'N';
			$arParams['CURRENCY_ID'] = '';
		}
		else
		{
			$arParams['CURRENCY_ID'] = $arCurrencyInfo['CURRENCY'];
			$arConvertParams['CURRENCY_ID'] = $arCurrencyInfo['CURRENCY'];
		}
	}
}

$arResult['CHECK_QUANTITY'] = false;
if (!isset($arResult['CATALOG_MEASURE_RATIO']))
	$arResult['CATALOG_MEASURE_RATIO'] = 1;
if (!isset($arResult['CATALOG_QUANTITY']))
	$arResult['CATALOG_QUANTITY'] = 0;
$arResult['CATALOG_QUANTITY'] = (
	0 < $arResult['CATALOG_QUANTITY'] && is_float($arResult['CATALOG_MEASURE_RATIO'])
	? (float)$arResult['CATALOG_QUANTITY']
	: (int)$arResult['CATALOG_QUANTITY']
);
$arResult['CATALOG'] = false;
if (!isset($arResult['CATALOG_SUBSCRIPTION']) || 'Y' != $arResult['CATALOG_SUBSCRIPTION'])
	$arResult['CATALOG_SUBSCRIPTION'] = 'N';

// CIBlockPriceTools::getLabel($arResult, $arParams['LABEL_PROP']);

if('Y' !== $arParams['ADD_DETAIL_TO_SLIDER'] && $arResult['DETAIL_PICTURE']){
	// CIBlockPriceTools :: getSliderForItem() gibt ein Array DETAIL_PICTURE wenn WEITERE FOTOS leer, auch wenn ADD_DETAIL_TO_SLIDER == N
	// unset($arResult['DETAIL_PICTURE']);
}
$arResult['ALT_TITLE_GET'] = $arParams['ALT_TITLE_GET'];
$productSlider = CMax::getSliderForItemExt($arResult, $arParams['ADD_PICT_PROP'], 'Y' == $arParams['ADD_DETAIL_TO_SLIDER']);
$bEmptyPictureProduct = false;

if (empty($productSlider))
{
	if($arResult['PREVIEW_PICTURE'] && 'Y' == $arParams['ADD_DETAIL_TO_SLIDER']){
		$productSlider = array(
			0 => $arResult['PREVIEW_PICTURE'],
		);
	}
	else{
		$productSlider = array(
			0 => $arEmptyPreview
		);
		$bEmptyPictureProduct = true;
	}
}

$arSizePict = [500, 500];

if($arParams["USE_CUSTOM_RESIZE"] == "Y")
{
	$arIBlockFields = CIBlock::GetFields($arParams["IBLOCK_ID"]);
	if($arIBlockFields['DETAIL_PICTURE'] && $arIBlockFields['DETAIL_PICTURE']['DEFAULT_VALUE'])
	{
		if($arIBlockFields['DETAIL_PICTURE']['DEFAULT_VALUE']['WIDTH'] && $arIBlockFields['DETAIL_PICTURE']['DEFAULT_VALUE']['HEIGHT'])
		{
			$arSizePict[0] = $arIBlockFields['DETAIL_PICTURE']['DEFAULT_VALUE']['WIDTH'];
			$arSizePict[1] = $arIBlockFields['DETAIL_PICTURE']['DEFAULT_VALUE']['HEIGHT'];
		}
	}
}

$arResult['SHOW_SLIDER'] = true;
if($productSlider){
	foreach($productSlider as $i => $arImage){
		$productSlider[$i] = array_merge(
			$arImage, array(
				"BIG" => array('src' => CFile::GetPath($arImage["ID"])),
				"SMALL" => CFile::ResizeImageGet($arImage["ID"], array("width" => $arSizePict[0], "height" => $arSizePict[1]), BX_RESIZE_IMAGE_PROPORTIONAL, true, array()),
				"THUMB" => CFile::ResizeImageGet($arImage["ID"], array("width" => 50, "height" => 50), BX_RESIZE_IMAGE_PROPORTIONAL, true, array()),
			)
		);
	}
}

$productSliderCount = count($productSlider);
$arResult['MORE_PHOTO'] = $productSlider;
$arResult['MORE_PHOTO_COUNT'] = count($productSlider);

if ($arResult['MODULES']['catalog'])
{
	$arResult['CATALOG'] = true;
	if (!isset($arResult['CATALOG_TYPE']))
		$arResult['CATALOG_TYPE'] = CCatalogProduct::TYPE_PRODUCT;
	if (
		(CCatalogProduct::TYPE_PRODUCT == $arResult['CATALOG_TYPE'] || CCatalogProduct::TYPE_SKU == $arResult['CATALOG_TYPE'])
		&& !empty($arResult['OFFERS'])
	)
	{
		$arResult['CATALOG_TYPE'] = CCatalogProduct::TYPE_SKU;
	}
	switch ($arResult['CATALOG_TYPE'])
	{
		case CCatalogProduct::TYPE_SET:
			$arResult['OFFERS'] = array();
			$arResult['CHECK_QUANTITY'] = ('Y' == $arResult['CATALOG_QUANTITY_TRACE'] && 'N' == $arResult['CATALOG_CAN_BUY_ZERO']);
			break;
		case CCatalogProduct::TYPE_SKU:
			break;
		case CCatalogProduct::TYPE_PRODUCT:
		default:
			$arResult['CHECK_QUANTITY'] = ('Y' == $arResult['CATALOG_QUANTITY_TRACE'] && 'N' == $arResult['CATALOG_CAN_BUY_ZERO']);
			break;
	}
}
else
{
	$arResult['CATALOG_TYPE'] = 0;
	$arResult['OFFERS'] = array();
}

if ($arResult['CATALOG'] && isset($arResult['OFFERS']) && !empty($arResult['OFFERS']))
{
	$boolSKUDisplayProps = false;

	$arResultSKUPropIDs = array();
	$arFilterProp = array();
	$arNeedValues = array();
	if('TYPE_1' == $arParams['TYPE_SKU'] && $arResult['OFFERS'] ){
		foreach ($arResult['OFFERS'] as &$arOffer)
		{
			foreach ($arSKUPropIDs as &$strOneCode)
			{
				if (isset($arOffer['DISPLAY_PROPERTIES'][$strOneCode]) && ( !$bUseModuleProps || in_array($strOneCode, $arParams['OFFERS_PROPERTY_CODE']) ))
				{
					$arResultSKUPropIDs[$strOneCode] = true;
					if (!isset($arNeedValues[$arSKUPropList[$strOneCode]['ID']]))
						$arNeedValues[$arSKUPropList[$strOneCode]['ID']] = array();
					$valueId = (
						$arSKUPropList[$strOneCode]['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_LIST
						? $arOffer['DISPLAY_PROPERTIES'][$strOneCode]['VALUE_ENUM_ID']
						: $arOffer['DISPLAY_PROPERTIES'][$strOneCode]['VALUE']
					);
					$arNeedValues[$arSKUPropList[$strOneCode]['ID']][$valueId] = $valueId;
					unset($valueId);
					if (!isset($arFilterProp[$strOneCode]))
						$arFilterProp[$strOneCode] = $arSKUPropList[$strOneCode];
				}
			}
			unset($strOneCode);
		}
		unset($arOffer);

		CIBlockPriceTools::getTreePropertyValues($arSKUPropList, $arNeedValues);
		$arResult["TMP_OFFERS_PROP"] = $arSKUPropList;

		if(!$arResult['DISPLAY_PROPERTIES'])
		{
			foreach($arResult['OFFERS'] as $arOffer)
			{
				foreach($arOffer['DISPLAY_PROPERTIES'] as $arProp)
				{
					if(!$arResult["TMP_OFFERS_PROP"][$arProp['CODE']])
					{
						if(!is_array($arProp["DISPLAY_VALUE"]))
							$arProp["DISPLAY_VALUE"] = array($arProp["DISPLAY_VALUE"]);


						foreach($arProp["DISPLAY_VALUE"] as $value){
							if(strlen($value)){
								$arResult['DISPLAY_PROPERTIES_OFFERS'] = true;
								break 3;
							}
						}
					}
				}
			}
		}
	}

	$arSKUPropIDs = array_keys($arSKUPropList);
	$arSKUPropKeys = array_fill_keys($arSKUPropIDs, false);


	$arMatrixFields = $arSKUPropKeys;
	$arMatrix = array();

	$arNewOffers = array();

	$arIDS = array($arResult['ID']);
	$offerSet = array();
	$arResult['OFFER_GROUP'] = false;
	$arResult['OFFERS_PROP'] = false;

	$arDouble = array();

	foreach ($arResult['OFFERS'] as $keyOffer => $arOffer)
	{
		$arOffer['ID'] = (int)$arOffer['ID'];
		if (isset($arDouble[$arOffer['ID']]))
			continue;
		$arIDS[] = $arOffer['ID'];
		$boolSKUDisplayProperties = false;
		$arOffer['OFFER_GROUP'] = false;
		$arRow = array();
		foreach ($arSKUPropIDs as $propkey => $strOneCode)
		{
			$arCell = array(
				'VALUE' => 0,
				'SORT' => PHP_INT_MAX,
				'NA' => true
			);
			if (isset($arOffer['DISPLAY_PROPERTIES'][$strOneCode]))
			{
				$arMatrixFields[$strOneCode] = true;
				$arCell['NA'] = false;
				if ('directory' == $arSKUPropList[$strOneCode]['USER_TYPE'])
				{
					$intValue = $arSKUPropList[$strOneCode]['XML_MAP'][$arOffer['DISPLAY_PROPERTIES'][$strOneCode]['VALUE']];
					$arCell['VALUE'] = $intValue;
				}
				elseif ('L' == $arSKUPropList[$strOneCode]['PROPERTY_TYPE'])
				{
					$arCell['VALUE'] = (int)$arOffer['DISPLAY_PROPERTIES'][$strOneCode]['VALUE_ENUM_ID'];
				}
				elseif ('E' == $arSKUPropList[$strOneCode]['PROPERTY_TYPE'])
				{
					$arCell['VALUE'] = (int)$arOffer['DISPLAY_PROPERTIES'][$strOneCode]['VALUE'];
				}
				$arCell['SORT'] = $arSKUPropList[$strOneCode]['VALUES'][$arCell['VALUE']]['SORT'];
			}
			$arRow[$strOneCode] = $arCell;
		}
		$arMatrix[$keyOffer] = $arRow;

		CIBlockPriceTools::setRatioMinPrice($arOffer, false);

		$arOffer['MORE_PHOTO'] = array();
		$arOffer['MORE_PHOTO_COUNT'] = 0;
		$arOffer['ALT_TITLE_GET'] = $arParams['ALT_TITLE_GET'];
		$offerSlider = CMax::getSliderForItemExt($arOffer, $arParams['OFFER_ADD_PICT_PROP'], true); // $arParams['ADD_DETAIL_TO_SLIDER'] == 'Y'

		$arOffer['MORE_PHOTO'] = $offerSlider;

		if($arOffer['MORE_PHOTO']){
			foreach($arOffer['MORE_PHOTO'] as $i => $arImage){
				if($arImage["ID"]){
					$arOffer['MORE_PHOTO'][$i]["BIG"]['src'] = CFile::GetPath($arImage["ID"]);
					$arOffer['MORE_PHOTO'][$i]["SMALL"] = CFile::ResizeImageGet($arImage["ID"], array("width" => $arSizePict[0], "height" => $arSizePict[1]), BX_RESIZE_IMAGE_PROPORTIONAL, true, array());
					$arOffer['MORE_PHOTO'][$i]["THUMB"] = CFile::ResizeImageGet($arImage["ID"], array("width" => 52, "height" => 52), BX_RESIZE_IMAGE_PROPORTIONAL, true, array());
				}
			}
		}

		$arOffer['MORE_PHOTO_COUNT'] = count($arOffer['MORE_PHOTO']);

		$boolSKUDisplayProps = !empty($arOffer['DISPLAY_PROPERTIES']);

		$arDouble[$arOffer['ID']] = true;
		$arNewOffers[$keyOffer] = $arOffer;
	}
	$arResult['OFFERS'] = $arNewOffers;
	$arResult['SHOW_OFFERS_PROPS'] = $boolSKUDisplayProps;

	$arUsedFields = array();
	$arSortFields = array();

	foreach ($arSKUPropIDs as $propkey => $strOneCode)
	{
		$boolExist = $arMatrixFields[$strOneCode];
		foreach ($arMatrix as $keyOffer => $arRow)
		{
			if ($boolExist)
			{
				if (!isset($arResult['OFFERS'][$keyOffer]['TREE']))
					$arResult['OFFERS'][$keyOffer]['TREE'] = array();
				$arResult['OFFERS'][$keyOffer]['TREE']['PROP_'.$arSKUPropList[$strOneCode]['ID']] = $arMatrix[$keyOffer][$strOneCode]['VALUE'];
				$arResult['OFFERS'][$keyOffer]['SKU_SORT_'.$strOneCode] = $arMatrix[$keyOffer][$strOneCode]['SORT'];
				$arUsedFields[$strOneCode] = true;
				$arSortFields['SKU_SORT_'.$strOneCode] = SORT_NUMERIC;

				$arPropSKU[$strOneCode][$arMatrix[$keyOffer][$strOneCode]["VALUE"]] = $arSKUPropList[$strOneCode]["VALUES"][$arMatrix[$keyOffer][$strOneCode]["VALUE"]];
			}
			else
			{
				unset($arMatrix[$keyOffer][$strOneCode]);
			}
		}

		if($arPropSKU[$strOneCode]){
			// sort sku prop values
			Collection::sortByColumn($arPropSKU[$strOneCode], array("SORT" => array(SORT_NUMERIC, SORT_ASC), "NAME" => array(SORT_STRING, SORT_ASC)));
			$arSKUPropList[$strOneCode]["VALUES"] = $arPropSKU[$strOneCode];
		}
	}
	$arResult['OFFERS_PROP'] = $arUsedFields;
	$arResult['OFFERS_PROP_CODES'] = (!empty($arUsedFields) ? base64_encode(serialize(array_keys($arUsedFields))) : '');

	unset($arPropSKU);

	Collection::sortByColumn($arResult['OFFERS'], $arSortFields);

	/*offers & nabor*/
	$offerSet = array();
	if (!empty($arIDS) && CBXFeatures::IsFeatureEnabled('CatCompleteSet'))
	{
		$offerSet = array_fill_keys($arIDS, false);
		$rsSets = CCatalogProductSet::getList(
			array(),
			array(
				'@OWNER_ID' => $arIDS,
				'=SET_ID' => 0,
				'=TYPE' => CCatalogProductSet::TYPE_GROUP
			),
			false,
			false,
			array('ID', 'OWNER_ID')
		);
		while ($arSet = $rsSets->Fetch())
		{
			$arSet['OWNER_ID'] = (int)$arSet['OWNER_ID'];
			$offerSet[$arSet['OWNER_ID']] = true;
			$arResult['OFFER_GROUP'] = true;
		}
		if ($offerSet[$arResult['ID']])
		{
			foreach ($offerSet as &$setOfferValue)
			{
				if ($setOfferValue === false)
				{
					$setOfferValue = true;
				}
			}
			unset($setOfferValue);
			unset($offerSet[$arResult['ID']]);
		}
		if ($arResult['OFFER_GROUP'])
		{
			$offerSet = array_filter($offerSet);
			$arResult['OFFER_GROUP_VALUES'] = array_keys($offerSet);
		}
	}

	$arMatrix = array();
	$intSelected = -1;
	$arResult['MIN_PRICE'] = false;
	$arResult['MIN_BASIS_PRICE'] = false;
	$arPropsSKU=array();
	$arOfferProps = implode(';', $arParams['OFFERS_CART_PROPERTIES']);

	global $arSite;
	$bChangeTitleItem = \Bitrix\Main\Config\Option::get('aspro.max', 'CHANGE_TITLE_ITEM_DETAIL', 'N') === 'Y';

	if( 'TYPE_1' == $arParams['TYPE_SKU'] && $arResult['OFFERS'] ){
		if ($arParams['OID']) {
			$arResult['OFFER_ID_SELECTED'] = $arParams['OID'];
		}
		foreach ($arResult['OFFERS'] as $keyOffer => $arOffer)
		{
			if ($arResult['OFFER_ID_SELECTED'] > 0)
				$foundOffer = ($arResult['OFFER_ID_SELECTED'] == $arOffer['ID']);
			else
				$foundOffer = $arOffer['CAN_BUY'];

			if ($foundOffer)
				$intSelected = $keyOffer;
			if (empty($arResult['MIN_PRICE']) /*&& $arOffer['CAN_BUY']*/)
			{
				// $arResult['MIN_PRICE'] = (isset($arOffer['RATIO_PRICE']) ? $arOffer['RATIO_PRICE'] : $arOffer['MIN_PRICE']);
				$arResult['MIN_PRICE'] = $arOffer['MIN_PRICE'];
				$arResult['MIN_BASIS_PRICE'] = $arOffer['MIN_PRICE'];
			}

			if (isset($offerSet[$arOffer['ID']]))
			{
				$arOffer['OFFER_GROUP'] = true;
				$arResult['OFFERS'][$keyOffer]['OFFER_GROUP'] = true;
			}
			reset($arOffer['MORE_PHOTO']);
		}

		if (-1 == $intSelected){
			$intSelected = 0;
		}
		$arResult['OFFERS_SELECTED'] = $intSelected;

		foreach ($arResult['OFFERS'] as $keyOffer => $arOffer)
		{
			$arResult['OFFERS'][$keyOffer]['PREVIEW_PICTURE_FIELD'] = $arOffer['PREVIEW_PICTURE'];
			if($arResult['OFFERS_SELECTED'] !== $keyOffer){
				continue;
			}
			$arSKUProps = false;
			if (!empty($arOffer['DISPLAY_PROPERTIES']))
			{
				$boolSKUDisplayProps = true;
				$arSKUProps = array();
				foreach ($arOffer['DISPLAY_PROPERTIES'] as &$arOneProp)
				{
					if ('F' == $arOneProp['PROPERTY_TYPE'] || ($arParams['OFFER_TREE_PROPS'] && in_array($arOneProp['CODE'], $arParams['OFFER_TREE_PROPS'])))
						continue;
					$arOneProp['SHOW_HINTS'] = $arParams['SHOW_HINTS'];
					$arSKUProps[] = array(
						'NAME' => $arOneProp['NAME'],
						'VALUE' => (is_array($arOneProp['DISPLAY_VALUE']) && count($arOneProp['DISPLAY_VALUE']) > 1 ? implode(', ', $arOneProp['DISPLAY_VALUE']) : $arOneProp['DISPLAY_VALUE']),
						'CODE' => $arOneProp['CODE'],
						'SHOW_HINTS' => $arParams['SHOW_HINTS'],
						'HINT' => $arOneProp['HINT'],
					);
					$arPropsSKU[]=$arOneProp;
				}
				unset($arOneProp);
			}

			$totalCount = CMax::GetTotalCount($arOffer, $arParams);
			$arOffer['IS_OFFER'] = 'Y';
			$arOffer['IBLOCK_ID'] = $arResult['IBLOCK_ID'];
			$arPriceTypeID = array();

			/* get additional query for OFFER price when PRICE_RANGE will start not from 1 */
			if (!$arOffer['PRICES'] && $arResult['CAT_PRICES']) {
				if ($arOffer['ITEM_PRICE_MODE'] === 'Q') {
					$arOfferPrices = CIBlockElement::GetList($arOrder, ['ID' => $arOffer['ID']], false, false, array_merge(['ID', 'NAME'], array_column($arResult['CAT_PRICES'], 'SELECT')))->Fetch();
					$arOffer['PRICES'] = CIBlockPriceTools::GetItemPrices($arOffer["IBLOCK_ID"], $arResult['CAT_PRICES'], $arOfferPrices, 'Y', $arConvertParams);
					if (!empty($arOffer["PRICES"])) {
                        foreach ($arOffer['PRICES'] as &$arOnePrice) {
                            if ($arOnePrice['MIN_PRICE'] == 'Y') {
                                $arOffer['MIN_PRICE'] = $arOnePrice;
                                break;
                            }
                        }
                        unset($arOnePrice);
                    }
				}
			}
			/* */

			if($arOffer['PRICES'])
			{
				foreach($arOffer['PRICES'] as $priceKey => $arOfferPrice)
				{
					if($arOfferPrice['CAN_BUY'] == 'Y')
						$arPriceTypeID[] = $arOfferPrice['PRICE_ID'];
					if($arOffer['CATALOG_GROUP_NAME_'.$arOfferPrice['PRICE_ID']])
						$arOffer['PRICES'][$priceKey]['GROUP_NAME'] = $arOffer['CATALOG_GROUP_NAME_'.$arOfferPrice['PRICE_ID']];
				}
			}
			//format offer prices when USE_PRICE_COUNT
			$sPriceMatrix = '';
			if($arParams['USE_PRICE_COUNT'])
			{
				if(function_exists('CatalogGetPriceTableEx') && (isset($arOffer['PRICE_MATRIX'])) && !$arOffer['PRICE_MATRIX'] && $arPriceTypeID)
				{
					$arOffer['PRICE_MATRIX'] = CatalogGetPriceTableEx($arOffer["ID"], 0, $arPriceTypeID, 'Y', $arConvertParams);
					if(count($arOffer['PRICE_MATRIX']['ROWS']) <= 1)
					{
						$arOffer['PRICE_MATRIX'] = '';
					}
					$arResult['OFFERS'][$keyOffer]['PRICE_MATRIX'] = $arOffer['PRICE_MATRIX'];
				}

				$arOffer = array_merge($arOffer, CMax::formatPriceMatrix($arOffer));
				$sPriceMatrix = CMax::showPriceMatrix($arOffer, $arParams, $arOffer['~CATALOG_MEASURE_NAME']);
			}

			$arAddToBasketData = CMax::GetAddToBasketArray($arOffer, $totalCount, $arParams["DEFAULT_COUNT"], $arParams["BASKET_URL"], false, $arItemIDs["ALL_ITEM_IDS"], 'btn-lg no-icons', $arParams);
			$arAddToBasketData["HTML"] = str_replace('data-item', 'data-props="'.$arOfferProps.'" data-item', $arAddToBasketData["HTML"]);

			$firstPhoto = current($arOffer['MORE_PHOTO']);
			$arOneRow = array(
				'ID' => $arOffer['ID'],
				'NAME' => $arOffer['~NAME'],
				'IBLOCK_ID' => $arOffer['IBLOCK_ID'],
				'TREE' => $arOffer['TREE'],
				'PRICE' => $arOffer['MIN_PRICE'],
				'PRICES' => $arOffer['PRICES'],
				'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
				'SHOW_ARTICLE_SKU' => $arParams['SHOW_ARTICLE_SKU'],
				"SHOW_DISCOUNT_TIME_EACH_SKU" => $arParams["SHOW_DISCOUNT_TIME_EACH_SKU"],
				'ARTICLE_SKU' => ($arParams['SHOW_ARTICLE_SKU'] == 'Y' ? (isset($arResult['PROPERTIES']['CML2_ARTICLE']['VALUE']) && $arResult['PROPERTIES']['CML2_ARTICLE']['VALUE'] ? '<span class="block_title" itemprop="name">'.$arResult['PROPERTIES']['CML2_ARTICLE']['NAME'].': '.'</span><span class="value" itemprop="value">'.$arResult['PROPERTIES']['CML2_ARTICLE']['VALUE'].'</span>' : '') : ''),
				'PRICE_MATRIX' => $sPriceMatrix,
				'BASIS_PRICE' => $arOffer['MIN_PRICE'],
				'PRICES_HTML' => \Aspro\Functions\CAsproMaxItem::showItemPrices($arParams, $arOffer["PRICES"], $arOffer['~CATALOG_MEASURE_NAME'], $arOffer["MIN_PRICE"]["ID"], ($arParams['SHOW_DISCOUNT_PERCENT_NUMBER'] == "Y" ? "N" : "Y"), false, true),
				'POPUP_VIDEO' => (isset($arOffer['PROPERTIES']['POPUP_VIDEO']) && $arOffer['PROPERTIES']['POPUP_VIDEO']['VALUE'] ? $arOffer['PROPERTIES']['POPUP_VIDEO']['VALUE'] : $arResult['PROPERTIES']['POPUP_VIDEO']['VALUE']),
				'DISPLAY_PROPERTIES' => $arSKUProps,
				'PREVIEW_PICTURE' => $arOffer["PREVIEW_PICTURE"],
				'DETAIL_PICTURE' => $firstPhoto,
				'CHECK_QUANTITY' => $arOffer['CHECK_QUANTITY'],
				'MAX_QUANTITY' => $totalCount,
				'STEP_QUANTITY' => $arOffer['CATALOG_MEASURE_RATIO'],
				'QUANTITY_FLOAT' => is_double($arOffer['CATALOG_MEASURE_RATIO']),
				'MEASURE' => $arOffer['~CATALOG_MEASURE_NAME'],
				'OFFER_GROUP' => (isset($offerSet[$arOffer['ID']]) && $offerSet[$arOffer['ID']]),
				'CAN_BUY' => ($arAddToBasketData['CAN_BUY'] ? 'Y' : $arOffer['CAN_BUY']),
				'CATALOG_SUBSCRIBE' => $arOffer['CATALOG_SUBSCRIBE'],
				'SLIDER' => $arOffer['MORE_PHOTO'],
				'SLIDER_COUNT' => $arOffer['MORE_PHOTO_COUNT'],
				'AVAILIABLE' => CMax::GetQuantityArray($totalCount, array(), ($arResult["STORES_COUNT"] ? "Y" : "N")),
				'URL' => $arOffer['DETAIL_PAGE_URL'],
				'CONFIG' => $arAddToBasketData,
				'HTML' => $arAddToBasketData["HTML"],
				'ACTION' => $arAddToBasketData["ACTION"],
				'PRODUCT_QUANTITY_VARIABLE' => $arParams["PRODUCT_QUANTITY_VARIABLE"],
				'TYPE_SKU' => $arParams["TYPE_SKU"],
				'SHOW_ONE_CLICK_BUY' => $arParams["SHOW_ONE_CLICK_BUY"],
				'ONE_CLICK_BUY' => GetMessage("ONE_CLICK_BUY"),
				'ONE_CLICK_BUY_HTML' => \Aspro\Functions\CAsproMax::showItemOCB($arAddToBasketData, $arOffer, $arParams, true, ''),
				'OFFER_PROPS' => $arOfferProps,
				'TYPE_PROP' => 'DIV',
				'NO_PHOTO' => $arEmptyPreview,
				'SHOW_MEASURE' => ($arParams["SHOW_MEASURE"]=="Y" ? "Y" : "N"),
				'PRODUCT_ID' => $arResult['ID'],
				'PARENT_PICTURE' => $arResult["PREVIEW_PICTURE"],
				'SHOW_POPUP_PRICE' => $arParams["SHOW_POPUP_PRICE"],
				'ACTIVE' => $arOffer['ACTIVE'],
				'SUBSCRIPTION' => true,
				'ITEM_PRICE_MODE' => $arOffer['ITEM_PRICE_MODE'],
				'ITEM_PRICES' => $arOffer['ITEM_PRICES'],
				'ITEM_PRICE_SELECTED' => $arOffer['ITEM_PRICE_SELECTED'],
				'ITEM_QUANTITY_RANGES' => $arOffer['ITEM_QUANTITY_RANGES'],
				'ITEM_QUANTITY_RANGE_SELECTED' => $arOffer['ITEM_QUANTITY_RANGE_SELECTED'],
				'ITEM_MEASURE_RATIOS' => $arOffer['ITEM_MEASURE_RATIOS'],
				'ITEM_MEASURE_RATIO_SELECTED' => $arOffer['ITEM_MEASURE_RATIO_SELECTED'],
			);
			if($arOneRow["PRICE"]["DISCOUNT_DIFF"]){
				$percent=round(($arOneRow["PRICE"]["DISCOUNT_DIFF"]/$arOneRow["PRICE"]["VALUE"])*100, 2);
				$arOneRow["PRICE"]["DISCOUNT_DIFF_PERCENT_RAW"]="-".$percent."%";
			}

			$arMatrix[$keyOffer] = $arOneRow;
		}
	}
	/*set min_price_id*/
	if('TYPE_1' != $arParams['TYPE_SKU'] && $arResult['OFFERS'] ){
		$arResult['MIN_PRICE'] = CMax::getMinPriceFromOffersExt(
			$arResult['OFFERS'],
			$boolConvert ? $arResult['CONVERT_CURRENCY']['CURRENCY_ID'] : $strBaseCurrency
		);
		$arTmpProps=array();
		if($arParams["OFFERS_PROPERTY_CODE"]){
			foreach($arParams["OFFERS_PROPERTY_CODE"] as $code){
				$arTmpProps[$code]=array("CODE"=>$code);
			}
		}
		$minItemPriceID = 0;
		$minItemPrice = 0;
		$minItemPriceFormat = "";
		$imgOffers=true;
		foreach ($arResult['OFFERS'] as $keyOffer => $arOffer){
			$imgID=($arOffer['PREVIEW_PICTURE'] ? $arOffer['PREVIEW_PICTURE'] : ($arOffer['DETAIL_PICTURE'] ? $arOffer['DETAIL_PICTURE'] : false));
			if(!$imgID){
				$imgOffers=false;
			}
			if($arOffer["MIN_PRICE"]["CAN_ACCESS"]){
				if($arOffer["MIN_PRICE"]["DISCOUNT_VALUE"] < $arOffer["MIN_PRICE"]["VALUE"]){
					$minOfferPrice = $arOffer["MIN_PRICE"]["DISCOUNT_VALUE"];
					$minOfferPriceFormat = $arOffer["MIN_PRICE"]["PRINT_DISCOUNT_VALUE"];
					$minOfferPriceID = $arOffer["MIN_PRICE"]["PRICE_ID"];
				}
				else{
					$minOfferPrice = $arOffer["MIN_PRICE"]["VALUE"];
					$minOfferPriceFormat = $arOffer["MIN_PRICE"]["PRINT_VALUE"];
					$minOfferPriceID = $arOffer["MIN_PRICE"]["PRICE_ID"];
				}

				if($minItemPrice > 0 && $minOfferPrice < $minItemPrice){
					$minItemPrice = $minOfferPrice;
					$minItemPriceFormat = $minOfferPriceFormat;
					$minItemPriceID = $minOfferPriceID;
					$minItemID = $arOffer["ID"];
				}
				elseif($minItemPrice == 0){
					$minItemPrice = $minOfferPrice;
					$minItemPriceFormat = $minOfferPriceFormat;
					$minItemPriceID = $minOfferPriceID;
					$minItemID = $arOffer["ID"];
				}
			}
			if($arTmpProps){
				foreach($arTmpProps as $key => $arProp){
					$arTmpProps[$key]["IS_EMPTY"] = true;
					$arTmpProps[$key]["NAME"] = $arOffer["PROPERTIES"][$arProp["CODE"]]["NAME"];
					$arTmpProps[$key]["HINT"] = $arOffer["PROPERTIES"][$arProp["CODE"]]["HINT"];
					$arTmpProps[$key]["ID"] = $arOffer["PROPERTIES"][$arProp["CODE"]]["ID"];
					if (!$arOffer["PROPERTIES"][$arProp["CODE"]]["VALUE"]){
						$arResult['OFFERS'][$keyOffer][] = GetMessage("EMPTY_VALUE_SKU");
						continue;
					}
					if (is_array($arOffer["PROPERTIES"][$arProp["CODE"]]["VALUE"])){
						$arResult['OFFERS'][$keyOffer][] = implode("/", $arOffer["PROPERTIES"][$arProp["CODE"]]["VALUE"]);
					}
					else{
						$arResult['OFFERS'][$keyOffer][] = $arOffer["PROPERTIES"][$arProp["CODE"]]["VALUE"];
					}
					$arTmpProps[$key]["IS_EMPTY"] = false;
				}
			}

			//format offer prices when USE_PRICE_COUNT
			if($arParams['USE_PRICE_COUNT'])
			{
				$arPriceTypeID = array();
				if($arOffer['PRICES'])
				{
					foreach($arOffer['PRICES'] as $priceKey => $arOfferPrice)
					{
						if($arOffer['CATALOG_GROUP_NAME_'.$arOfferPrice['PRICE_ID']])
						{
							$arPriceTypeID[] = $arOfferPrice['PRICE_ID'];
							$arOffer['PRICES'][$priceKey]['GROUP_NAME'] = $arOffer['CATALOG_GROUP_NAME_'.$arOfferPrice['PRICE_ID']];
						}
					}
				}
				if(function_exists('CatalogGetPriceTableEx') && (isset($arOffer['PRICE_MATRIX'])) && !$arOffer['PRICE_MATRIX'])
					$arOffer["PRICE_MATRIX"] = CatalogGetPriceTableEx($arOffer["ID"], 0, $arPriceTypeID, 'Y', $arConvertParams);

				if(count($arOffer['PRICE_MATRIX']['ROWS']) <= 1)
				{
					$arOffer['PRICE_MATRIX'] = '';
				}

				$arResult['OFFERS'][$keyOffer] = array_merge($arOffer, CMax::formatPriceMatrix($arOffer));
			}
		}
		$arResult['MIN_PRICE']["MIN_PRICE_ID"]=$minItemPriceID;
		$arResult['MIN_PRICE']["MIN_ITEM_ID"]=$minItemID;

		$arResult["SKU_PROPERTIES"]=$arTmpProps;
		$arResult["SKU_IMD"]=$imgOffers;
	}

	if (-1 == $intSelected){
		$intSelected = 0;
	}

	$arResult['JS_OFFERS'] = $arMatrix;
	$arResult['OFFERS_SELECTED'] = $intSelected;

	$arResult['OFFERS_IBLOCK'] = $arSKU['IBLOCK_ID'];

	if('TYPE_1' == $arParams['TYPE_SKU'] && $arResult['OFFERS'] ){
		//more photo for current offer
		if( isset($arResult['OFFERS'][$arResult['OFFERS_SELECTED']]) && is_array($arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['MORE_PHOTO']) ){
			$arResult['MORE_PHOTO'] = $bEmptyPictureProduct && $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['MORE_PHOTO_COUNT'] > 0 ? $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['MORE_PHOTO'] : array_merge($arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['MORE_PHOTO'], $arResult['MORE_PHOTO']);
			$arResult['MORE_PHOTO_COUNT'] = $bEmptyPictureProduct && $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['MORE_PHOTO_COUNT'] > 0 ? $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['MORE_PHOTO_COUNT'] : $arResult['MORE_PHOTO_COUNT'] + $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['MORE_PHOTO_COUNT'];
		}
		if($bChangeTitleItem){
			$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($arResult["SKU_IBLOCK_ID"], $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['ID']);
			$arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['IPROPERTY_VALUES'] = $ipropValues->getValues();
		}
	}

}

if ($arResult['MODULES']['catalog'] && $arResult['CATALOG'])
{
	if ($arResult['CATALOG_TYPE'] == CCatalogProduct::TYPE_PRODUCT || $arResult['CATALOG_TYPE'] == CCatalogProduct::TYPE_SET)
	{
		CIBlockPriceTools::setRatioMinPrice($arResult, false);
		$arResult['MIN_BASIS_PRICE'] = $arResult['MIN_PRICE'];
	}
	if (CBXFeatures::IsFeatureEnabled('CatCompleteSet') && $arResult['CATALOG_TYPE'] == CCatalogProduct::TYPE_PRODUCT)
	{
		$rsSets = CCatalogProductSet::getList(
			array(),
			array(
				'@OWNER_ID' => $arResult['ID'],
				'=SET_ID' => 0,
				'=TYPE' => CCatalogProductSet::TYPE_GROUP
			),
			false,
			false,
			array('ID', 'OWNER_ID')
		);
		if ($arSet = $rsSets->Fetch())
		{
			$arResult['OFFER_GROUP'] = true;
		}
	}

	if($arParams['USE_PRICE_COUNT'])
	{
		if($arResult['OFFERS'])
		{
			foreach($arResult['OFFERS'] as $keyOffer => $arOffer)
			{
				//format prices when USE_PRICE_COUNT
				if($arOffer['PRICES'])
				{
					foreach($arOffer['PRICES'] as $priceKey => $arOfferPrice)
					{
						if($arOffer['CATALOG_GROUP_NAME_'.$arOfferPrice['PRICE_ID']])
						{
							$arPriceTypeID[] = $arOfferPrice['PRICE_ID'];
							$arOffer['PRICES'][$priceKey]['GROUP_NAME'] = $arOffer['CATALOG_GROUP_NAME_'.$arOfferPrice['PRICE_ID']];
						}
					}
				}
				if(function_exists('CatalogGetPriceTableEx') && (isset($arOffer['PRICE_MATRIX'])) && !$arOffer['PRICE_MATRIX'])
				{
					$arPriceTypeID = array();
					if($arOffer['PRICES'])
					{
						foreach($arOffer['PRICES'] as $priceKey => $arOfferPrice)
						{
							if($arOffer['CATALOG_GROUP_NAME_'.$arOfferPrice['PRICE_ID']])
							{
								$arPriceTypeID[] = $arOfferPrice['PRICE_ID'];
								$arOffer['PRICES'][$priceKey]['GROUP_NAME'] = $arOffer['CATALOG_GROUP_NAME_'.$arOfferPrice['PRICE_ID']];
							}
						}
					}
					$arOffer["PRICE_MATRIX"] = CatalogGetPriceTableEx($arOffer["ID"], 0, $arPriceTypeID, 'Y', $arConvertParams);
				}
				$arOffer["FIX_PRICE_MATRIX"] = CMax::checkPriceRangeExt($arOffer);
				$arResult['OFFERS'][$keyOffer] = array_merge($arOffer, CMax::formatPriceMatrix($arOffer));
			}
			$arResult['MIN_PRICE'] = CMax::getMinPriceFromOffersExt(
				$arResult['OFFERS'],
				$boolConvert ? $arResult['CONVERT_CURRENCY']['CURRENCY_ID'] : $strBaseCurrency
			);
		}
		else
		{
			$arResult["FIX_PRICE_MATRIX"] = CMax::checkPriceRangeExt($arResult);
		}
	} elseif (isset($arResult['ITEM_PRICE_MODE']) && $arResult['ITEM_PRICE_MODE'] === 'Q') {
		//set PRICE_MATRIX when PRICE_RANGE will start not from 1
		if (
			function_exists('CatalogGetPriceTableEx')
			&& (isset($arResult['PRICE_MATRIX']))
			&& !$arResult['PRICE_MATRIX']
			&& $arResult['CAT_PRICES']
		) {
			$arResult['PRICE_MATRIX'] = CatalogGetPriceTableEx($arResult["ID"], 0, array_column($arResult['CAT_PRICES'], 'ID'), 'Y', $arConvertParams);
		}
		$arResult["FIX_PRICE_MATRIX"] = CMax::checkPriceRangeExt($arResult);
	}

	//format prices when USE_PRICE_COUNT
	$arResult = array_merge($arResult, CMax::formatPriceMatrix($arResult));
}

/*complect*/
if($arParams["SHOW_KIT_PARTS"] == "Y"){
	//const TYPE_SET = 1;
	//const TYPE_GROUP = 2;
	$arSetItems = $arSetItemsOtherID = array();

	$arSets = CCatalogProductSet::getAllSetsByProduct($arResult["ID"], 1);

	if (is_array($arSets) && !empty($arSets))
	{
		foreach( $arSets as $key => $set) {
			\Bitrix\Main\Type\Collection::sortByColumn($set["ITEMS"], array('SORT' => SORT_ASC));
			foreach($set["ITEMS"] as $i=>$val) {
				$arSetItems[] = $val["ITEM_ID"];
				$arSetItemsOtherID[$val["ITEM_ID"]]["SORT"] = $val["SORT"];
				$arSetItemsOtherID[$val["ITEM_ID"]]["QUANTITY"] = $val["QUANTITY"];
			}
		}
	}
	$arResultPrices = CIBlockPriceTools::GetCatalogPrices($arParams["IBLOCK_ID"], $arParams["PRICE_CODE"]);

	$arSelect = Array("ID", "IBLOCK_ID", "NAME", "DETAIL_PAGE_URL", "PREVIEW_PICTURE", "DETAIL_PICTURE");
	$arPriceTypeID = array();
	foreach($arResultPrices as &$value)
	{
		if($value['CAN_VIEW'] && $value['CAN_BUY'])
		{
			$arSelect[] = $value["SELECT"];
			$arPriceTypeID[]=  $value["ID"];
		}
	}
	if (!empty($arSetItems))
	{
		$db_res = CIBlockElement::GetList(Array("SORT"=>"ASC"), Array("ID"=>$arSetItems), false, false, $arSelect);
		$bShowQuantity = false;
		while ($res = $db_res->GetNext())
		{
			$res["SORT"] = $arSetItemsOtherID[$res["ID"]]["SORT"];
			$res["QUANTITY"] = $arSetItemsOtherID[$res["ID"]]["QUANTITY"];
			$arResult["SET_ITEMS"][$res['ID']] = $res;
			if($arSetItemsOtherID[$res["ID"]]["QUANTITY"] > 1)
				$bShowQuantity = true;
		}
		$arResult["SET_ITEMS_QUANTITY"] = $bShowQuantity;
		$arResult["SET_ITEMS"] = array_values($arResult["SET_ITEMS"]);
		\Bitrix\Main\Type\Collection::sortByColumn($arResult["SET_ITEMS"], array('SORT' => SORT_ASC));
	}

	$bCatalog = CModule::IncludeModule('catalog');

	if (is_array($arResult["SET_ITEMS"]) && !empty($arResult["SET_ITEMS"]))
	{
		foreach($arResult["SET_ITEMS"] as $key => $setItem)
		{
			if($arParams["USE_PRICE_COUNT"])
			{
				if($bCatalog)
				{
					$arResult["SET_ITEMS"][$key]["PRICE_MATRIX"] = CatalogGetPriceTableEx($arResult["SET_ITEMS"][$key]["ID"], 0, $arPriceTypeID, 'Y', $arConvertParams);
					foreach($arResult["SET_ITEMS"][$key]["PRICE_MATRIX"]["COLS"] as $keyColumn=>$arColumn)
						$arResult["SET_ITEMS"][$key]["PRICE_MATRIX"]["COLS"][$keyColumn]["NAME_LANG"] = htmlspecialcharsbx($arColumn["NAME_LANG"]);
				}
			}
			else
			{
				$arResult["SET_ITEMS"][$key]["PRICES"] = CIBlockPriceTools::GetItemPrices($arParams["IBLOCK_ID"], $arResultPrices, $arResult["SET_ITEMS"][$key], $arParams['PRICE_VAT_INCLUDE'], $arConvertParams);
				if (!empty($arResult["SET_ITEMS"][$key]["PRICES"]))
				{
					foreach ($arResult["SET_ITEMS"][$key]['PRICES'] as &$arOnePrice)
					{ if ('Y' == $arOnePrice['MIN_PRICE']) { $arResult["SET_ITEMS"][$key]['MIN_PRICE'] = $arOnePrice; break;} }
					unset($arOnePrice);
				}

			}
		}
	}
}

if($arSKUPropList)
{
	foreach($arSKUPropList as $keySKU => $arPropSKU)
	{
		if(!$arPropSKU['HINT'])
		{
			$arTmp = CIBlockProperty::GetByID($arPropSKU["ID"], $arResult["SKU_IBLOCK_ID"])->Fetch();
			$arSKUPropList[$keySKU]['HINT'] = $arTmp['HINT'];
		}
		$arSKUPropList[$keySKU]['SHOW_HINTS'] = $arParams['SHOW_HINTS'];
	}
}

$arResult['SKU_PROPS'] = $arSKUPropList;
$arResult['DEFAULT_PICTURE'] = $arEmptyPreview;

$arResult['CURRENCIES'] = array();
if ($arResult['MODULES']['currency']){
	if ($boolConvert){
		$currencyFormat = CCurrencyLang::GetFormatDescription($arResult['CONVERT_CURRENCY']['CURRENCY_ID']);
		$arResult['CURRENCIES'] = array(
			array(
				'CURRENCY' => $arResult['CONVERT_CURRENCY']['CURRENCY_ID'],
				'FORMAT' => array(
					'FORMAT_STRING' => $currencyFormat['FORMAT_STRING'],
					'DEC_POINT' => $currencyFormat['DEC_POINT'],
					'THOUSANDS_SEP' => $currencyFormat['THOUSANDS_SEP'],
					'DECIMALS' => $currencyFormat['DECIMALS'],
					'THOUSANDS_VARIANT' => $currencyFormat['THOUSANDS_VARIANT'],
					'HIDE_ZERO' => $currencyFormat['HIDE_ZERO']
				)
			)
		);
		unset($currencyFormat);
	}else{
		$currencyIterator = CurrencyTable::getList(array(
			'select' => array('CURRENCY'),
			'filter' => array('BASE' => 'Y')
		));
		while ($currency = $currencyIterator->fetch()){
			$currencyFormat = CCurrencyLang::GetFormatDescription($currency['CURRENCY']);
			$arResult['CURRENCIES'][] = array(
				'CURRENCY' => $currency['CURRENCY'],
				'FORMAT' => array(
					'FORMAT_STRING' => $currencyFormat['FORMAT_STRING'],
					'DEC_POINT' => $currencyFormat['DEC_POINT'],
					'THOUSANDS_SEP' => $currencyFormat['THOUSANDS_SEP'],
					'DECIMALS' => $currencyFormat['DECIMALS'],
					'THOUSANDS_VARIANT' => $currencyFormat['THOUSANDS_VARIANT'],
					'HIDE_ZERO' => $currencyFormat['HIDE_ZERO']
				)
			);
		}
		unset($currencyFormat, $currency, $currencyIterator);
	}
}

/*brand item*/
$arBrand = array();
if(strlen($arResult["DISPLAY_PROPERTIES"]["BRAND"]["VALUE"]) && $arResult["PROPERTIES"]["BRAND"]["LINK_IBLOCK_ID"]){
	$arBrand = CMaxCache::CIBLockElement_GetList(array('CACHE' => array("MULTI" =>"N", "TAG" => CMaxCache::GetIBlockCacheTag($arResult["PROPERTIES"]["BRAND"]["LINK_IBLOCK_ID"]))), array("IBLOCK_ID" => $arResult["PROPERTIES"]["BRAND"]["LINK_IBLOCK_ID"], "ACTIVE"=>"Y", "ID" => $arResult["DISPLAY_PROPERTIES"]["BRAND"]["VALUE"]));
	if($arBrand){
		if($arParams["SHOW_BRAND_PICTURE"] == "Y" && ($arBrand["PREVIEW_PICTURE"] || $arBrand["DETAIL_PICTURE"])){
			$arBrand["IMAGE"] = CFile::ResizeImageGet(($arBrand["PREVIEW_PICTURE"] ? $arBrand["PREVIEW_PICTURE"] : $arBrand["DETAIL_PICTURE"]), array("width" => 200, "height" => 40), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true);
		}
	}
}

$arResult["BRAND_ITEM"] = $arBrand;
$arResult["ARTICLE"] = (strlen($arResult["DISPLAY_PROPERTIES"]["CML2_ARTICLE"]["VALUE"]) && $arResult["PROPERTIES"]["CML2_ARTICLE"]["VALUE"]);
if($arResult["DISPLAY_PROPERTIES"]["CML2_ARTICLE"]["VALUE"]) {
	$arResult["CML2_ARTICLE"] = $arResult["PROPERTIES"]["CML2_ARTICLE"];
}

/*get section table sizes*/
$tmpTableSizes = $tableSizes = '';
if($arResult["SECTION"])
{
	$arSection = CMaxCache::CIBlockSection_GetList(array('CACHE' => array("MULTI" =>"N", "TAG" => CMaxCache::GetIBlockCacheTag($arParams["IBLOCK_ID"]))), array('GLOBAL_ACTIVE' => 'Y', "ID" => $arResult["SECTION"]["ID"], "IBLOCK_ID" => $arParams["IBLOCK_ID"]), false, array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "LEFT_MARGIN", "RIGHT_MARGIN", "NAME", "UF_TABLE_SIZES"));
	if($arSection['UF_TABLE_SIZES'])
		$tmpTableSizes = $arSection['UF_TABLE_SIZES'];

	if(!$tmpTableSizes)
	{
		if($arResult["SECTION"]["DEPTH_LEVEL"] > 2)
		{
			$arSectionParent = CMaxCache::CIBlockSection_GetList(array('CACHE' => array("MULTI" =>"N", "TAG" => CMaxCache::GetIBlockCacheTag($arParams["IBLOCK_ID"]))), array('GLOBAL_ACTIVE' => 'Y', "ID" => $arSection["IBLOCK_SECTION_ID"], "IBLOCK_ID" => $arParams["IBLOCK_ID"]), false, array("ID", "IBLOCK_ID", "NAME", "UF_TABLE_SIZES"));
			if($arSectionParent['UF_TABLE_SIZES'])
				$tmpTableSizes = $arSectionParent['UF_TABLE_SIZES'];

			if(!$tmpTableSizes)
			{
				$sectionRoot = CMaxCache::CIBlockSection_GetList(array('CACHE' => array("MULTI" => "N", "TAG" => CMaxCache::GetIBlockCacheTag($arParams["IBLOCK_ID"]))), array('GLOBAL_ACTIVE' => 'Y', "<=LEFT_BORDER" => $arSection["LEFT_MARGIN"], ">=RIGHT_BORDER" => $arSection["RIGHT_MARGIN"], "DEPTH_LEVEL" => 1, "IBLOCK_ID" => $arParams["IBLOCK_ID"]), false, array("ID", "IBLOCK_ID", "NAME", "UF_TABLE_SIZES"));
				if($sectionRoot['UF_TABLE_SIZES'])
					$tmpTableSizes = $sectionRoot['UF_TABLE_SIZES'];
			}
		}
		else
		{
			$sectionRoot = CMaxCache::CIBlockSection_GetList(array('CACHE' => array("MULTI" => "N", "TAG" => CMaxCache::GetIBlockCacheTag($arParams["IBLOCK_ID"]))), array('GLOBAL_ACTIVE' => 'Y', "<=LEFT_BORDER" => $arSection["LEFT_MARGIN"], ">=RIGHT_BORDER" => $arSection["RIGHT_MARGIN"], "DEPTH_LEVEL" => 1, "IBLOCK_ID" => $arParams["IBLOCK_ID"]), false, array("ID", "IBLOCK_ID", "NAME", "UF_TABLE_SIZES"));
			if($sectionRoot['UF_TABLE_SIZES'])
				$tmpTableSizes = $sectionRoot['UF_TABLE_SIZES'];
		}
	}

	if($tmpTableSizes)
	{
		$rsTypes = CUserFieldEnum::GetList(array(), array("ID" => $tmpTableSizes));
		if($arType = $rsTypes->GetNext())
			$tableSizes = $arType['XML_ID'];
		if($tableSizes)
		{
			$arResult["SIZE_PATH"] = SITE_DIR."/include/table_sizes/detail_".strtolower($tableSizes).".php";
			$arResult["SIZE_PATH"] = str_replace("//", "/", $arResult["SIZE_PATH"]);
		}
	}
}

if(in_array('HELP_TEXT', $arParams['PROPERTY_CODE']))
{
	$arResult['HELP_TEXT_FILE'] = false;
	if($arResult['DISPLAY_PROPERTIES']['HELP_TEXT']['~VALUE']):
		$arResult['HELP_TEXT'] = $arResult['DISPLAY_PROPERTIES']['HELP_TEXT']['~VALUE'];
	elseif($arParams['HELP_TEXT']):
		$arResult['HELP_TEXT'] = $arParams['HELP_TEXT'];
	else:?>
		<?ob_start();?>
			<?$APPLICATION->IncludeComponent(
				"bitrix:main.include",
				"",
				Array(
					"AREA_FILE_SHOW" => "page",
					"AREA_FILE_SUFFIX" => "help_text",
					"EDIT_TEMPLATE" => ""
				)
			);?>
		<?$help_text = ob_get_contents();
		ob_end_clean();
		$bshowHelpTextFromFile = true;
		if( strlen( trim($help_text) ) < 1){
			$bshowHelpTextFromFile = false;
		} else{
			$bIsBitrixDiv = ( strpos($help_text, 'bx_incl_area') !== false );
			$textWithoutTags = strip_tags($help_text);
			if( $bIsBitrixDiv && (strlen( trim($textWithoutTags) ) < 1) ){
				$bshowHelpTextFromFile = false;
			}
		}

		if( $bshowHelpTextFromFile ){
			$arResult['HELP_TEXT'] = $help_text;
			$arResult['HELP_TEXT_FILE'] = true;
		}
		?>
	<?endif;?>
<?}

if(!empty($arResult['DISPLAY_PROPERTIES']))
{
	$arResult['DISPLAY_PROPERTIES'] = CMax::PrepareItemProps($arResult['DISPLAY_PROPERTIES']);
	foreach ($arResult['DISPLAY_PROPERTIES'] as $propKey => $arDispProp)
	{
		if(!in_array($arDispProp["CODE"], array("SERVICES", "HIT", "RECOMMEND", "NEW", "STOCK", "VIDEO", "VIDEO_YOUTUBE", "POPUP_VIDEO", "CML2_ARTICLE")))
			$arGroupsProp[$propKey] = $arDispProp;
	}
}
$arResult["GROUPS_PROPS"] = $arGroupsProp;


if('TYPE_1' == $arParams['TYPE_SKU'] && $arResult['OFFERS']){
	//for ajax offers
	$arResult['SKU_CONFIG'] = array(
		"SHOW_ABSENT" => $arParams["SHOW_ABSENT"],
		"HIDE_NOT_AVAILABLE_OFFERS" => $arParams["HIDE_NOT_AVAILABLE_OFFERS"],
		"PRICE_CODE" => $arParams["PRICE_CODE"],
		"OFFER_TREE_PROPS" => $arParams["OFFER_TREE_PROPS"],
		"OFFER_SHOW_PREVIEW_PICTURE_PROPS" => $arParams["OFFER_SHOW_PREVIEW_PICTURE_PROPS"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
		"CURRENCY_ID" => $arParams["CURRENCY_ID"],
		"OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
		"OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
		"OFFERS_SORT_FIELD2" => $arParams["OFFERS_SORT_FIELD2"],
		"OFFERS_SORT_ORDER2" => $arParams["OFFERS_SORT_ORDER2"],
		"LIST_OFFERS_LIMIT" => $arParams["OFFERS_LIMIT"],
		"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
		"LIST_OFFERS_PROPERTY_CODE" => $arParams["OFFERS_PROPERTY_CODE"],
		"SHOW_DISCOUNT_TIME" => $arParams["SHOW_DISCOUNT_TIME"],
		"SHOW_COUNTER_LIST" => $arParams["SHOW_COUNTER_LIST"],
		"PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
		"USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"] ? 'Y' : 'N',
		"SHOW_MEASURE" => $arParams["SHOW_MEASURE"],
		"SHOW_OLD_PRICE" => $arParams["SHOW_OLD_PRICE"],
		"SHOW_DISCOUNT_PERCENT" => $arParams["SHOW_DISCOUNT_PERCENT"],
		"SHOW_DISCOUNT_PERCENT_NUMBER" => $arParams["SHOW_DISCOUNT_PERCENT_NUMBER"],
		"USE_REGION" => $arParams["USE_REGION"],
		"STORES" => $arParams["STORES"],
		"DEFAULT_COUNT" => $arParams["DEFAULT_COUNT"],
		"BASKET_URL" => $arParams["BASKET_URL"],
		"SHOW_GALLERY" => "Y",
		"MAX_GALLERY_ITEMS" => "0",
		"OFFERS_CART_PROPERTIES" => $arParams["OFFERS_CART_PROPERTIES"],
		"PRODUCT_PROPERTIES" => $arParams["PRODUCT_PROPERTIES"],
		"PARTIAL_PRODUCT_PROPERTIES" => $arParams["PARTIAL_PRODUCT_PROPERTIES"],
		"ADD_PROPERTIES_TO_BASKET" => $arParams["ADD_PROPERTIES_TO_BASKET"],
		"SHOW_ONE_CLICK_BUY" => $arParams["SHOW_ONE_CLICK_BUY"],
		"SHOW_DISCOUNT_TIME_EACH_SKU" => $arParams["SHOW_DISCOUNT_TIME_EACH_SKU"],
		"SHOW_ARTICLE_SKU" => $arParams["SHOW_ARTICLE_SKU"],
		"SHOW_POPUP_PRICE" => CMax::GetFrontParametrValue('SHOW_POPUP_PRICE'),
		"ADD_PICT_PROP" => $arParams["ADD_PICT_PROP"],
		"ADD_DETAIL_TO_SLIDER" => $arParams["ADD_DETAIL_TO_SLIDER"],
		"OFFER_ADD_PICT_PROP" => $arParams["OFFER_ADD_PICT_PROP"],
		"PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
		//"IBINHERIT_TEMPLATES" => $arSeoItem ? $arIBInheritTemplates : array(),
		"DISPLAY_COMPARE" => CMax::GetFrontParametrValue('CATALOG_COMPARE'),
		"DISPLAY_WISH_BUTTONS" => $arParams["DISPLAY_WISH_BUTTONS"],
		"IS_DETAIL" => "Y",
		"SKU_DETAIL_ID" => $arParams["SKU_DETAIL_ID"],
		"OCB_CLASS" => "",
		"CART_CLASS" => "btn-lg no-icons",
		"SHOW_SKU_DESCRIPTION" => $arParams['SHOW_SKU_DESCRIPTION'],
		"GALLERY_WIDTH" => $arSizePict[$arParams["PICTURE_RATIO"]][0],
		"GALLERY_HEIGHT" => $arSizePict[$arParams["PICTURE_RATIO"]][1],
		'USE_ADDITIONAL_GALLERY' => $arParams['USE_ADDITIONAL_GALLERY'],
		'ADDITIONAL_GALLERY_OFFERS_PROPERTY_CODE' => $arParams['ADDITIONAL_GALLERY_OFFERS_PROPERTY_CODE'],
		'ADDITIONAL_GALLERY_PROPERTY_CODE' => $arParams['ADDITIONAL_GALLERY_PROPERTY_CODE'],
	);
}



$arFilter = array();

$arKeys[] = $arResult['ID'];
$nameForSite[$key] = $arResult['PROPERTIES']['NAIMENOVANIE_DLYA_SAYTA']["VALUE"];
$color[$key] = $arResult['PROPERTIES']['PROPERTY_TSVET']["VALUE"];


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

// Функция сортировки по полю ORDER
function cmp($a, $b) {
	return strnatcmp($a["ORDER"], $b["ORDER"]);
}


$i = 0;
$arSort= Array("NAME"=>"ASC");
$arSelect = Array("ID","NAME","IBLOCK_ID","PROPERTY_NAIMENOVANIE_DLYA_SAYTA","PROPERTY_RAZMER","PROPERTY_TSVET","PREVIEW_PICTURE","DETAIL_PAGE_URL","PROPERTY_OBSHCHIY_RAZMER_DLYA_SAYTA","DETAIL_PAGE_URL");
$arFilter = Array(
	"IBLOCK_ID" => $arParams['IBLOCK_ID'],
	"PROPERTY_NAIMENOVANIE_DLYA_SAYTA" => $nameForSite,
	//'>CATALOG_STORE_AMOUNT_3' => 0,
	"ACTIVE" => "Y",
	"!SECTION_ID" => 0
);

$res = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
while($ob = $res->GetNextElement()){
	$arFields = $ob->GetFields();
	if(!$arSku[$arFields['PROPERTY_NAIMENOVANIE_DLYA_SAYTA_VALUE']][$arFields["PROPERTY_TSVET_VALUE"]]){
		$arSku[$arFields['PROPERTY_NAIMENOVANIE_DLYA_SAYTA_VALUE']][$arFields["PROPERTY_TSVET_VALUE"]] = array(
			"ID" => $arFields['ID'],
			"URL" => $arFields['DETAIL_PAGE_URL'],
			"SRC" => CFile::GetPath($arFields["PREVIEW_PICTURE"]),
		);
	}

	if(!$arFields["PROPERTY_OBSHCHIY_RAZMER_DLYA_SAYTA_VALUE"]){
		$arFields["PROPERTY_OBSHCHIY_RAZMER_DLYA_SAYTA_VALUE"] = $arFields["PROPERTY_RAZMER_VALUE"];
	}

	if($arFields["PROPERTY_OBSHCHIY_RAZMER_DLYA_SAYTA_VALUE"]){
		$arSku[$arFields['PROPERTY_NAIMENOVANIE_DLYA_SAYTA_VALUE']][$arFields["PROPERTY_TSVET_VALUE"]]["SIZE"][] =
		array(
			"ID" => $arFields['ID'],
			"URL" => $arFields['DETAIL_PAGE_URL'],
			"RAZMER" => $arFields["PROPERTY_OBSHCHIY_RAZMER_DLYA_SAYTA_VALUE"],
			"COLOR" => $arFields["PROPERTY_TSVET_VALUE"],

			// Присваем индексы для дальнейшей сортировки
			"ORDER" => array_key_exists($arFields["PROPERTY_OBSHCHIY_RAZMER_DLYA_SAYTA_VALUE"], $sizesOrder) ?  $sizesOrder[$arFields["PROPERTY_OBSHCHIY_RAZMER_DLYA_SAYTA_VALUE"]] : 0,
		);
	}
}

// далее запишим всё по своим местам + скрипт ajax на обновление данных о товаре
if (array_key_exists($arResult['PROPERTIES']['NAIMENOVANIE_DLYA_SAYTA']["VALUE"], $arSku) && $arResult['PROPERTIES']['NAIMENOVANIE_DLYA_SAYTA']["VALUE"]) {
	foreach ($arSku[$arResult['PROPERTIES']['NAIMENOVANIE_DLYA_SAYTA']["VALUE"]] as &$prop) {
		// Искусственная сортировка по размеру
		usort($prop['SIZE'], "cmp");
	}

	$arResult['OFFERS_CUSTOM'] = $arSku[$arResult['PROPERTIES']['NAIMENOVANIE_DLYA_SAYTA']["VALUE"]];
}

?>
