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

if($arResult['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'])
{
	if(is_array($arResult['PROPERTIES']['CML2_ARTICLE']['VALUE']))
	{
		$arResult['CML2_ARTICLE']= $arResult['PROPERTIES']['CML2_ARTICLE'];
		$arResult['CML2_ARTICLE']['VALUE']= reset($arResult['PROPERTIES']['CML2_ARTICLE']['VALUE']);
		$arResult['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'] = reset($arResult['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE']);
	}
	else
	{
		$arResult['CML2_ARTICLE'] = $arResult['PROPERTIES']['CML2_ARTICLE'];
	}
}

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

if(!$arParams["PICTURE_RATIO"])
	$arParams["PICTURE_RATIO"] = "square";

$arSizePict = [
	"square" => [600,600],
	"horizontal" => [600,450],
	"vertical" => [450,600],
];

if(!in_array($arParams["PICTURE_RATIO"], array_keys($arSizePict)))
	$arParams["PICTURE_RATIO"] = "square";

if($arParams["USE_CUSTOM_RESIZE"] == "Y")
{
	$arIBlockFields = CIBlock::GetFields($arParams["IBLOCK_ID"]);
	if($arIBlockFields['DETAIL_PICTURE'] && $arIBlockFields['DETAIL_PICTURE']['DEFAULT_VALUE'])
	{
		if($arIBlockFields['DETAIL_PICTURE']['DEFAULT_VALUE']['WIDTH'] && $arIBlockFields['DETAIL_PICTURE']['DEFAULT_VALUE']['HEIGHT'])
		{
			$arSizePict[$arParams["PICTURE_RATIO"]][0] = $arIBlockFields['DETAIL_PICTURE']['DEFAULT_VALUE']['WIDTH'];
			$arSizePict[$arParams["PICTURE_RATIO"]][1] = $arIBlockFields['DETAIL_PICTURE']['DEFAULT_VALUE']['HEIGHT'];
		}
	}
}

$arResult['SHOW_SLIDER'] = true;
if($productSlider){
	foreach($productSlider as $i => $arImage){
		$productSlider[$i] = array_merge(
			$arImage, array(
				"BIG" => array('src' => CFile::GetPath($arImage["ID"])),
				"SMALL" => CFile::ResizeImageGet($arImage["ID"], array("width" => $arSizePict[$arParams["PICTURE_RATIO"]][0], "height" => $arSizePict[$arParams["PICTURE_RATIO"]][1]), BX_RESIZE_IMAGE_PROPORTIONAL, true, array()),
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

/* ADDITIONAL GALLERY */
if($arParams['USE_ADDITIONAL_GALLERY'] === 'Y'){
	$arResult['ADDITIONAL_GALLERY'] = $arElementAdditionalGallery = $arOffersAdditionalGallery = array();

	if($arResult['OFFERS'] && $arParams['ADDITIONAL_GALLERY_OFFERS_PROPERTY_CODE']){
		foreach($arResult['OFFERS'] as &$arOffer){
			if('TYPE_1' === $arParams['TYPE_SKU']){
				$arOffersAdditionalGallery[$arOffer['ID']] = array();
			}
			if($arOffer['PROPERTIES'] && isset($arOffer['PROPERTIES'][$arParams['ADDITIONAL_GALLERY_OFFERS_PROPERTY_CODE']]) && $arOffer['PROPERTIES'][$arParams['ADDITIONAL_GALLERY_OFFERS_PROPERTY_CODE']]['VALUE']){
				foreach($arOffer['PROPERTIES'][$arParams['ADDITIONAL_GALLERY_OFFERS_PROPERTY_CODE']]['VALUE'] as $img){
					$alt = (strlen($arPhoto['DESCRIPTION']) ? $arPhoto['DESCRIPTION'] : (strlen($arResult['DETAIL_PICTURE']['ALT']) ? $arResult['DETAIL_PICTURE']['ALT']  : (strlen($arPhoto['ALT']) ? $arPhoto['ALT'] : $arResult['NAME'])));
                	$title = (strlen($arPhoto['DESCRIPTION']) ? $arPhoto['DESCRIPTION'] : (strlen($arResult['DETAIL_PICTURE']['TITLE']) ? $arResult['DETAIL_PICTURE']['TITLE']  :(strlen($arPhoto['TITLE']) ? $arPhoto['TITLE'] : $arResult['NAME'])));
                	if($arParams['ALT_TITLE_GET'] == 'SEO')
                	{
                		$alt = (strlen($arResult['DETAIL_PICTURE']['ALT']) ? $arResult['DETAIL_PICTURE']['ALT']  : (strlen($arPhoto['ALT']) ? $arPhoto['ALT'] : $arResult['NAME']));
                		$title = (strlen($arResult['DETAIL_PICTURE']['TITLE']) ? $arResult['DETAIL_PICTURE']['TITLE']  :(strlen($arPhoto['TITLE']) ? $arPhoto['TITLE'] : $arResult['NAME']));
                	}
					$arPhoto = array(
						'DETAIL' => ($arPhoto = CFile::GetFileArray($img)),
						'PREVIEW' => CFile::ResizeImageGet($img, array('width' => 1500, 'height' => 1500), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true),
						'THUMB' => CFile::ResizeImageGet($img , array('width' => 60, 'height' => 60), BX_RESIZE_IMAGE_EXACT, true),
						'TITLE' => $title,
						'ALT' => $alt,
					);
					if('TYPE_1' === $arParams['TYPE_SKU']){
						$arOffersAdditionalGallery[$arOffer['ID']][] = $arPhoto;
					}
					else{
						$arOffersAdditionalGallery[] = $arPhoto;
					}
				}
			}
		}
		unset($arOffer);
	}

	if($arParams['ADDITIONAL_GALLERY_PROPERTY_CODE'] && isset($arResult['PROPERTIES'][$arParams['ADDITIONAL_GALLERY_PROPERTY_CODE']]) && $arResult['PROPERTIES'][$arParams['ADDITIONAL_GALLERY_PROPERTY_CODE']]['VALUE']){
		foreach($arResult['PROPERTIES'][$arParams['ADDITIONAL_GALLERY_PROPERTY_CODE']]['VALUE'] as $img){
			$alt = (strlen($arPhoto['DESCRIPTION']) ? $arPhoto['DESCRIPTION'] : (strlen($arResult['DETAIL_PICTURE']['ALT']) ? $arResult['DETAIL_PICTURE']['ALT']  : (strlen($arPhoto['ALT']) ? $arPhoto['ALT'] : $arResult['NAME'])));
        	$title = (strlen($arPhoto['DESCRIPTION']) ? $arPhoto['DESCRIPTION'] : (strlen($arResult['DETAIL_PICTURE']['TITLE']) ? $arResult['DETAIL_PICTURE']['TITLE']  :(strlen($arPhoto['TITLE']) ? $arPhoto['TITLE'] : $arResult['NAME'])));
        	if($arParams['ALT_TITLE_GET'] == 'SEO')
        	{
        		$alt = (strlen($arResult['DETAIL_PICTURE']['ALT']) ? $arResult['DETAIL_PICTURE']['ALT']  : (strlen($arPhoto['ALT']) ? $arPhoto['ALT'] : $arResult['NAME']));
        		$title = (strlen($arResult['DETAIL_PICTURE']['TITLE']) ? $arResult['DETAIL_PICTURE']['TITLE']  :(strlen($arPhoto['TITLE']) ? $arPhoto['TITLE'] : $arResult['NAME']));
        	}
			$arElementAdditionalGallery[] = array(
				'DETAIL' => ($arPhoto = CFile::GetFileArray($img)),
				'PREVIEW' => CFile::ResizeImageGet($img, array('width' => 1500, 'height' => 1500), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true),
				'THUMB' => CFile::ResizeImageGet($img , array('width' => 60, 'height' => 60), BX_RESIZE_IMAGE_EXACT, true),
				'TITLE' => $title,
				'ALT' => $alt,
			);
		}
	}
	if($arResult['OFFERS']){
		if('TYPE_1' !== $arParams['TYPE_SKU']){
			$arResult['ADDITIONAL_GALLERY'] = $arOffersAdditionalGallery ? array_merge($arOffersAdditionalGallery, $arElementAdditionalGallery) : $arElementAdditionalGallery;
		}
		else{
			foreach($arOffersAdditionalGallery as $offerID => $arGallery){
				$arResult['ADDITIONAL_GALLERY'][$offerID] = $arOffersAdditionalGallery[$offerID] ? array_merge($arOffersAdditionalGallery[$offerID], $arElementAdditionalGallery) : $arElementAdditionalGallery;
			}
		}
	}
	else{
		$arResult['ADDITIONAL_GALLERY'] = $arElementAdditionalGallery;
	}

	unset($arElementAdditionalGallery, $arOffersAdditionalGallery);
}
$arResult["TMP_OFFERS_PROP"] = array();
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
				if (isset($arOffer['DISPLAY_PROPERTIES'][$strOneCode]))
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
			if (isset($arOffer['DISPLAY_PROPERTIES'][$strOneCode]) && ( !$bUseModuleProps || in_array($strOneCode, $arParams['OFFERS_PROPERTY_CODE']) ))
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
					$arOffer['MORE_PHOTO'][$i]["SMALL"] = CFile::ResizeImageGet($arImage["ID"], array("width" => $arSizePict[$arParams["PICTURE_RATIO"]][0], "height" => $arSizePict[$arParams["PICTURE_RATIO"]][1]), BX_RESIZE_IMAGE_PROPORTIONAL, true, array());
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

	$arUsedFields = $arSortFields = $arPropSKU = array();

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

	if ('TYPE_1' != $arParams['TYPE_SKU'] ) {
		if ($arParams["OFFERS_SORT_FIELD"] == "REGION_PRICE" || $arParams["OFFERS_SORT_FIELD2"] == "REGION_PRICE") {
			global $arRegion;
			if ($arRegion) {
				if(!$arRegion["PROPERTY_SORT_REGION_PRICE_VALUE"] || $arRegion["PROPERTY_SORT_REGION_PRICE_VALUE"] == "component")
				{
					$price = CCatalogGroup::GetList(array(), array("NAME" => $arParams["SORT_REGION_PRICE"]), false, false, array("ID", "NAME"))->GetNext();
					$sortPrice = "CATALOG_PRICE_".$price["ID"];
				}
				else
				{
					$sortPrice = "CATALOG_PRICE_".$arRegion["PROPERTY_SORT_REGION_PRICE_VALUE"];
				}
			} else {
				$price_name = ($arParams["SORT_REGION_PRICE"] ? $arParams["SORT_REGION_PRICE"] : "BASE");
				$price = CCatalogGroup::GetList(array(), array("NAME" => $price_name), false, false, array("ID", "NAME"))->GetNext();
				$sortPrice = "CATALOG_PRICE_".$price["ID"];
			}
			if ($arParams["OFFERS_SORT_FIELD"] == "REGION_PRICE") {
				$arParams["OFFERS_SORT_FIELD"] = $sortPrice;
			}
			if ($arParams["OFFERS_SORT_FIELD2"] == "REGION_PRICE") {
				$arParams["OFFERS_SORT_FIELD2"] = $sortPrice;
			}
		}

		$arSortFields[strtoupper($arParams["OFFERS_SORT_FIELD"])] = ($arParams["OFFERS_SORT_ORDER"] == "asc" ? SORT_ASC : SORT_DESC);
		$arSortFields[strtoupper($arParams["OFFERS_SORT_FIELD2"])] = ($arParams["OFFERS_SORT_ORDER2"] == "asc" ? SORT_ASC : SORT_DESC);
	}
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

	$postfix = '';
	global $arSite;
	if(\Bitrix\Main\Config\Option::get("aspro.max", "HIDE_SITE_NAME_TITLE", "N")=="N")
		$postfix = ' - '.$arSite['SITE_NAME'];
	
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

			$arAddToBasketData = CMax::GetAddToBasketArray($arOffer, $totalCount, $arParams["DEFAULT_COUNT"], $arParams["BASKET_URL"], true, $arItemIDs["ALL_ITEM_IDS"], 'btn-lg', $arParams);
			$arAddToBasketData["HTML"] = str_replace('data-item', 'data-props="'.$arOfferProps.'" data-item', $arAddToBasketData["HTML"]);

			$firstPhoto = current($arOffer['MORE_PHOTO']);
			$arOneRow = array(
				'ID' => $arOffer['ID'],
				'NAME' => $arOffer['~NAME'],
				'IBLOCK_ID' => $arOffer['IBLOCK_ID'],
				'TREE' => $arOffer['TREE'],
				'PRICE' => $arOffer['MIN_PRICE'],
				'PRICES' => $arOffer['PRICES'],
				'POSTFIX' => $postfix,
				'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
				'SHOW_DISCOUNT_TIME_EACH_SKU' => $arParams['SHOW_DISCOUNT_TIME_EACH_SKU'],
				'SHOW_ARTICLE_SKU' => $arParams['SHOW_ARTICLE_SKU'],
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
				'ONE_CLICK_BUY_HTML' => \Aspro\Functions\CAsproMax::showItemOCB($arAddToBasketData, $arOffer, $arParams, true, ' round-ignore '),
				'OFFER_PROPS' => $arOfferProps,
				'TYPE_PROP' => $arParams["PROPERTIES_DISPLAY_TYPE"],
				'NO_PHOTO' => $arEmptyPreview,
				'SHOW_MEASURE' => ($arParams["SHOW_MEASURE"]=="Y" ? "Y" : "N"),
				'SHOW_POPUP_PRICE' => $arParams["SHOW_POPUP_PRICE"],
				'PRODUCT_ID' => $arResult['ID'],
				'PARENT_PICTURE' => $arResult["PREVIEW_PICTURE"],
				'ACTIVE' => $arOffer['ACTIVE'],
				'SUBSCRIPTION' => true,
				'ITEM_PRICE_MODE' => $arOffer['ITEM_PRICE_MODE'],
				'ITEM_PRICES' => $arOffer['ITEM_PRICES'],
				'ITEM_PRICE_SELECTED' => $arOffer['ITEM_PRICE_SELECTED'],
				'ITEM_QUANTITY_RANGES' => $arOffer['ITEM_QUANTITY_RANGES'],
				'ITEM_QUANTITY_RANGE_SELECTED' => $arOffer['ITEM_QUANTITY_RANGE_SELECTED'],
				'ITEM_MEASURE_RATIOS' => $arOffer['ITEM_MEASURE_RATIOS'],
				'ITEM_MEASURE_RATIO_SELECTED' => $arOffer['ITEM_MEASURE_RATIO_SELECTED'],
				'ADDITIONAL_GALLERY' => $arResult['ADDITIONAL_GALLERY'][$arOffer['ID']],
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

		$minItemPriceID = 0;
		$minItemPrice = 0;
		$minItemPriceFormat = "";
		$imgOffers=true;

		$iconSize = (CMax::GetFrontParametrValue('CATALOG_COMPARE') != "N" ? 1 : 0);

		$bCheckDelay = false;
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
			if($arParams["OFFERS_PROPERTY_CODE"])
			{
				foreach($arParams["OFFERS_PROPERTY_CODE"] as $code)
				{
					if(!isset($arTmpProps[$code]))
					{
						$arTmpProps[$code] = array(
							"NAME" => $arOffer["PROPERTIES"][$code]["NAME"],
							"CODE" => $code,
							"HINT" => $arOffer["PROPERTIES"][$code]["HINT"],
							"ID" => $arOffer["PROPERTIES"][$code]["ID"],
							"PROPERTY_TYPE" => $arOffer["PROPERTIES"][$code]["PROPERTY_TYPE"],
							"IS_EMPTY" => true
						);
					}

					if (!$arOffer["PROPERTIES"][$code]["VALUE"])
					{
						$arResult['OFFERS'][$keyOffer][] = GetMessage("EMPTY_VALUE_SKU");
						continue;
					}

					$arTmpProps[$code]["IS_EMPTY"] = false;

					if(is_array($arOffer["PROPERTIES"][$code]["VALUE"]))
					{
						if($arOffer["PROPERTIES"][$code]['PROPERTY_TYPE'] == 'E')
							$arResult['OFFERS'][$keyOffer][] = implode('/', $arOffer["PROPERTIES"][$code]["DISPLAY_VALUE"]);
						else
							$arResult['OFFERS'][$keyOffer][] = implode("/", $arOffer["PROPERTIES"][$code]["VALUE"]);
					}
					else
					{
						if($arOffer["PROPERTIES"][$code]['PROPERTY_TYPE'] == 'E')
							$arResult['OFFERS'][$keyOffer][] = $arOffer["PROPERTIES"][$code]["DISPLAY_VALUE"];
						else
							$arResult['OFFERS'][$keyOffer][] = $arOffer["PROPERTIES"][$code]["VALUE"];
					}
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

			$arResult['OFFERS'][$keyOffer]['TOTAL_COUNT'] = CMax::GetTotalCount($arOffer, $arParams);
			$arResult['OFFERS'][$keyOffer]['ADD_TO_BASKET_DATA'] = CMax::GetAddToBasketArray($arOffer, $arResult['OFFERS'][$keyOffer]['TOTAL_COUNT'], $arParams["DEFAULT_COUNT"], $arParams["BASKET_URL"], false, array(), 'small', $arParams);

			if($arParams["DISPLAY_WISH_BUTTONS"] != "N")
			{
				if($arResult['OFFERS'][$keyOffer]['ADD_TO_BASKET_DATA']["CAN_BUY"])
				{
					if(\CMax::checkShowDelay($arParams, $arResult['OFFERS'][$keyOffer]['TOTAL_COUNT'], $arOffer))
					{
						$bCheckDelay = true;
					}
				}
			}
		}
		if($bCheckDelay)
			++$iconSize;

		$arResult['ICONS_SIZE'] = $iconSize;

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

	if($arResult['OFFERS']) {
		$arResult['MAX_PRICE'] = CMax::getMaxPriceFromOffersExt(
			$arResult['OFFERS'],
			$boolConvert ? $arResult['CONVERT_CURRENCY']['CURRENCY_ID'] : $strBaseCurrency
		);
		
		$arFirstSkuPicture = array();
		$bNeedFindPicture = (CMax::GetFrontParametrValue("SHOW_FIRST_SKU_PICTURE") == "Y") && $bEmptyPictureProduct;
		if( $bNeedFindPicture ){
			$bFindPicture = false;
						
			foreach ($arResult['OFFERS'] as $keyOffer => $arOffer) {
				if (($arOffer['DETAIL_PICTURE'] && $arOffer['PREVIEW_PICTURE']) || (!$arOffer['DETAIL_PICTURE'] && $arOffer['PREVIEW_PICTURE'])) {
					$arOffer['DETAIL_PICTURE'] = $arOffer['PREVIEW_PICTURE'];
				}

				if ($arOffer['DETAIL_PICTURE'] && !$bFindPicture) {
					$arResult["FIRST_SKU_PICTURE"] = CFile::ResizeImageGet($arOffer["DETAIL_PICTURE"], array("width"=>350, "height"=>350), BX_RESIZE_IMAGE_PROPORTIONAL, true);
					$bFindPicture = true;
					break;
				}
			}
		}
	}

	//format prices when USE_PRICE_COUNT
	$arResult = array_merge($arResult, CMax::formatPriceMatrix($arResult));
}

/*complect*/
$arResult["SET_ITEMS_QUANTITY"] = $arResult["SET_ITEMS"] = array();

if($arParams["SHOW_KIT_PARTS"] == "Y" && $arResult["CATALOG_TYPE"] == CCatalogProduct::TYPE_SET)
{
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

			if(($res['DETAIL_PICTURE'] && $res['PREVIEW_PICTURE']) || (!$res['DETAIL_PICTURE'] && $res['PREVIEW_PICTURE']))
				$res['DETAIL_PICTURE'] = $res['PREVIEW_PICTURE'];

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
			$arResult["SET_ITEMS"][$key]["MEASURE"] = \Bitrix\Catalog\ProductTable::getCurrentRatioWithMeasure($setItem['ID']);
			if($arParams["USE_PRICE_COUNT"])
			{
				if($bCatalog)
				{
					$arResult["SET_ITEMS"][$key]["PRICE_MATRIX"] = CatalogGetPriceTableEx($arResult["SET_ITEMS"][$key]["ID"], 0, $arPriceTypeID, 'Y', $arConvertParams);
					foreach($arResult["SET_ITEMS"][$key]["PRICE_MATRIX"]["COLS"] as $keyColumn=>$arColumn)
					{
						$arResult["SET_ITEMS"][$key]["PRICE_MATRIX"]["COLS"][$keyColumn]["NAME_LANG"] = htmlspecialcharsbx($arColumn["NAME_LANG"]);
						/*if(count($arResult["SET_ITEMS"][$key]['PRICE_MATRIX']['ROWS']) > 1)
						{*/
							$setItem = array_merge($arResult["SET_ITEMS"][$key], CMax::formatPriceMatrix($arResult["SET_ITEMS"][$key]));
							$arResult["SET_ITEMS"][$key] = $setItem;
						//}
					}

					$arResult["SET_ITEMS"][$key]['FIX_PRICE_MATRIX']['PRICE_SELECT'] = 0;
					// $arResult["SET_ITEMS"][$key]["FIX_PRICE_MATRIX"] = CMax::checkPriceRangeExt($setItem);

					foreach($arResult["SET_ITEMS"][$key]['PRICE_MATRIX']['ROWS'] as $range => $arInterval)
					{
						$minimalPrice = null;
						foreach($arResult["SET_ITEMS"][$key]['PRICE_MATRIX']['MATRIX'] as $arPrice)
						{
							if($arPrice[$range])
							{
								if($minimalPrice === null || $minimalPrice['DISCOUNT_PRICE'] > $arPrice[$range]['DISCOUNT_PRICE'])
								{
									if($arPrice[$range]['PRICE'] > $arPrice[$range]['DISCOUNT_PRICE'])
									{
										$arPrice[$range]['PERCENT'] = round((($arPrice[$range]['PRICE']-$arPrice[$range]['DISCOUNT_PRICE'])/$arPrice[$range]['PRICE'])*100);
										$arPrice[$range]['DISCOUNT'] = ($arPrice[$range]['PRICE']-$arPrice[$range]['DISCOUNT_PRICE']);
										$arPrice[$range]['PRINT_DISCOUNT'] = CCurrencyLang::CurrencyFormat($arPrice[$range]['PRICE']-$arPrice[$range]['DISCOUNT_PRICE'], $arPrice[$range]['CURRENCY'], true);
										$arPrice[$range]['BASE_PRICE'] = $arPrice[$range]['PRICE'];
										$arPrice[$range]['PRICE'] = $arPrice[$range]['DISCOUNT_PRICE'];
										unset($arPrice[$range]['PRINT_PRICE']);
									}
									$minimalPrice = $arPrice[$range];
								}
							}
						}
						$arResult["SET_ITEMS"][$key]['ITEM_PRICES'][$range] = $minimalPrice;
					}
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

if(!empty($arResult['DISPLAY_PROPERTIES']))
{
	foreach($arResult['DISPLAY_PROPERTIES'] as $propKey => $arDispProp)
	{
		if('F' == $arDispProp['PROPERTY_TYPE'])
			unset($arResult['DISPLAY_PROPERTIES'][$propKey]);
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

/*akc*/
$arResult["STOCK"] = \Aspro\Functions\CAsproMax::getLinkedItems($arResult, "LINK_SALE", $arParams);

/*services*/
$arResult["SERVICES"] = \Aspro\Functions\CAsproMax::getLinkedItems($arResult, "SERVICES", $arParams);

/*news*/
$arResult["NEWS"] = \Aspro\Functions\CAsproMax::getLinkedItems($arResult, "LINK_NEWS", $arParams);

/*blog*/
$arResult["BLOG"] = \Aspro\Functions\CAsproMax::getLinkedItems($arResult, "LINK_BLOG", $arParams);
if ($arParams["LINKED_BLOG"]) {
	$arResult["BLOG"] = array_merge((array)$arResult["BLOG"], (array)$arParams["LINKED_BLOG"]);
}

/*associated*/
if($arParams['LINKED_FILTER_BY_PROP']['ASSOCIATED'] || $arParams['LINKED_FILTER_BY_FILTER']['ASSOCIATED'])
{
	if($arParams['LINKED_FILTER_BY_PROP']['ASSOCIATED'])
		$arResult['ASSOCIATED'] = array('ID' => $arParams['LINKED_FILTER_BY_PROP']['ASSOCIATED']);

	$arResult['ASSOCIATED']['IBLOCK_ID'] = $arParams['IBLOCK_ID'];
	CMax::makeElementFilterInRegion($arResult['ASSOCIATED']);

	if($arParams['LINKED_FILTER_BY_FILTER']['ASSOCIATED']['FILTER'])
		$arResult['ASSOCIATED'][] = $arParams['LINKED_FILTER_BY_FILTER']['ASSOCIATED']['FILTER'];
}

/*similar*/
if($arParams['LINKED_FILTER_BY_PROP']['EXPANDABLES'] || $arParams['LINKED_FILTER_BY_FILTER']['EXPANDABLES'])
{
	if($arParams['LINKED_FILTER_BY_PROP']['EXPANDABLES'])
		$arResult['EXPANDABLES'] = array('ID' => $arParams['LINKED_FILTER_BY_PROP']['EXPANDABLES']);

	$arResult['EXPANDABLES']['IBLOCK_ID'] = $arParams['IBLOCK_ID'];
	CMax::makeElementFilterInRegion($arResult['EXPANDABLES']);

	if($arParams['LINKED_FILTER_BY_FILTER']['EXPANDABLES']['FILTER'])
		$arResult['EXPANDABLES'][] = $arParams['LINKED_FILTER_BY_FILTER']['EXPANDABLES']['FILTER'];
}

/*brand item*/
$arBrand = array();
if(strlen($arResult["DISPLAY_PROPERTIES"]["BRAND"]["VALUE"]) && $arResult["PROPERTIES"]["BRAND"]["LINK_IBLOCK_ID"]){
	$arBrand = CMaxCache::CIBLockElement_GetList(array('CACHE' => array("MULTI" =>"N", "TAG" => CMaxCache::GetIBlockCacheTag($arResult["PROPERTIES"]["BRAND"]["LINK_IBLOCK_ID"]))), array("IBLOCK_ID" => $arResult["PROPERTIES"]["BRAND"]["LINK_IBLOCK_ID"], "ACTIVE"=>"Y", "ID" => $arResult["DISPLAY_PROPERTIES"]["BRAND"]["VALUE"]));

	$arBrand['CATALOG_PAGE_URL'] = $arResult['SECTION']['SECTION_PAGE_URL'] . 'filter/brand-is-' . $arBrand['CODE'] . '/apply/';

	if(class_exists('\Aspro\Max\Smartseo\General\Smartseo')) {
		$arBrand['CATALOG_PAGE_URL'] = \Aspro\Max\Smartseo\General\Smartseo::replaceRealUrlByNew($arBrand['CATALOG_PAGE_URL']);
	}

	if($arBrand){
		if($arParams["SHOW_BRAND_PICTURE"] == "Y" && ($arBrand["PREVIEW_PICTURE"] || $arBrand["DETAIL_PICTURE"])){
			$picture = ($arBrand["PREVIEW_PICTURE"] ? $arBrand["PREVIEW_PICTURE"] : $arBrand["DETAIL_PICTURE"]);
			$arBrand["IMAGE"] = CFile::ResizeImageGet($picture, array("width" => 200, "height" => 40), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true);
			$arBrand["IMAGE"]["ALT"] = $arBrand["IMAGE"]["TITLE"] = $arBrand["NAME"];
			if($arBrand["DETAIL_PICTURE"])
			{
				$arBrand["IMAGE"]["INFO"] = CFile::GetFileArray($arBrand["DETAIL_PICTURE"]);

				$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($arBrand["IBLOCK_ID"], $arBrand["ID"]);
				$arBrand["IMAGE"]["IPROPERTY_VALUES"] = $ipropValues->getValues();
				if($arBrand["IMAGE"]["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_TITLE"])
					$arBrand["IMAGE"]["TITLE"] = $arBrand["IMAGE"]["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_TITLE"];
				if($arBrand["IMAGE"]["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_ALT"])
					$arBrand["IMAGE"]["ALT"] = $arBrand["IMAGE"]["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_ALT"];

				if($arBrand["IMAGE"]["INFO"]["DESCRIPTION"])
					$arBrand["IMAGE"]["ALT"] = $arBrand["IMAGE"]["TITLE"] = $arBrand["IMAGE"]["INFO"]["DESCRIPTION"];
			}
		}
	}
}

$arResult["BRAND_ITEM"] = $arBrand;

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
			//$arResult["SIZE_PATH"] = SITE_DIR."/include/table_sizes/detail_".strtolower($tableSizes).".php";
			//$arResult["SIZE_PATH"] = str_replace("//", "/", $arResult["SIZE_PATH"]);
		}
	}
}

if(in_array('HELP_TEXT', $arParams['PROPERTY_CODE']))
{
	
	
							
	$arResult['HELP_TEXT_FILE'] = false;
	if(isset($arResult['DISPLAY_PROPERTIES']['HELP_TEXT']) && $arResult['DISPLAY_PROPERTIES']['HELP_TEXT']['~VALUE']):
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
	$arResult['LINK_STAFF'] = isset($arResult['DISPLAY_PROPERTIES']['LINK_STAFF']) ? $arResult['DISPLAY_PROPERTIES']['LINK_STAFF']['VALUE'] : '';
	$arResult['LINK_VACANCY'] = isset($arResult['DISPLAY_PROPERTIES']['LINK_VACANCY']) ? $arResult['DISPLAY_PROPERTIES']['LINK_VACANCY']['VALUE'] : '';

	$arVideo = array();
	if(isset($arResult['DISPLAY_PROPERTIES']['VIDEO']) && strlen($arResult["DISPLAY_PROPERTIES"]["VIDEO"]["VALUE"]))
		$arVideo[] = $arResult["DISPLAY_PROPERTIES"]["VIDEO"]["~VALUE"];

	if(isset($arResult['DISPLAY_PROPERTIES']['VIDEO_YOUTUBE']) && isset($arResult["DISPLAY_PROPERTIES"]["VIDEO_YOUTUBE"]["VALUE"]))
	{
		if(is_array($arResult["DISPLAY_PROPERTIES"]["VIDEO_YOUTUBE"]["VALUE"]))
			$arVideo = $arVideo + $arResult["DISPLAY_PROPERTIES"]["VIDEO_YOUTUBE"]["~VALUE"];
		elseif(strlen($arResult["DISPLAY_PROPERTIES"]["VIDEO_YOUTUBE"]["VALUE"]))
			$arVideo[] = $arResult["DISPLAY_PROPERTIES"]["VIDEO_YOUTUBE"]["~VALUE"];
	}

	if(isset($arResult['SECTION_FULL']['UF_VIDEO']) && strlen($arResult["SECTION_FULL"]["UF_VIDEO"]))
		$arVideo[] = $arResult["SECTION_FULL"]["~UF_VIDEO"];
	if(isset($arResult['SECTION_FULL']['UF_VIDEO_YOUTUBE']) && strlen($arResult["SECTION_FULL"]["UF_VIDEO_YOUTUBE"]))
		$arVideo[] = $arResult["SECTION_FULL"]["~UF_VIDEO_YOUTUBE"];
	$arResult["VIDEO"] = $arVideo;

	$arResult['DISPLAY_PROPERTIES'] = CMax::PrepareItemProps($arResult['DISPLAY_PROPERTIES']);

	$arGroupsProp = array();
	if($arResult["DISPLAY_PROPERTIES"])
	{
		foreach($arResult["DISPLAY_PROPERTIES"] as $propCode => $arProp)
		{
			$arGroupsProp[$propCode] = $arProp;
		}
	}
	$arResult["GROUPS_PROPS"] = $arGroupsProp;
}

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
		"CART_CLASS" => "btn-lg",
		"SHOW_SKU_DESCRIPTION" => $arParams['SHOW_SKU_DESCRIPTION'],
		"GALLERY_WIDTH" => $arSizePict[$arParams["PICTURE_RATIO"]][0],
		"GALLERY_HEIGHT" => $arSizePict[$arParams["PICTURE_RATIO"]][1],
		'USE_ADDITIONAL_GALLERY' => $arParams['USE_ADDITIONAL_GALLERY'],
		'ADDITIONAL_GALLERY_OFFERS_PROPERTY_CODE' => $arParams['ADDITIONAL_GALLERY_OFFERS_PROPERTY_CODE'],
		'ADDITIONAL_GALLERY_PROPERTY_CODE' => $arParams['ADDITIONAL_GALLERY_PROPERTY_CODE'],
		'USE_STORE_CLICK' => ($arParams["USE_STORE"] == "Y" && $arResult["STORES_COUNT"] && $arResult['CATALOG_TYPE'] != CCatalogProduct::TYPE_SET ? "Y" : "N"),
	);
}

$db_res = CPrice::GetList(
	array(),
	array(
		"PRODUCT_ID" => $arResult['ID'],
		"CATALOG_GROUP_ID" => '7'
	)
);

if ($ar_res = $db_res->Fetch()) {

	$oldPrice = $ar_res["PRICE"];
	$oldPrice = round($oldPrice);
	$oldPrice = floor($oldPrice);

	$skidkaPercentByOldPrice = (($arResult['MIN_PRICE']['VALUE'] - $oldPrice) / $oldPrice) * 100;

	$skidkaPercentByOldPrice = round($skidkaPercentByOldPrice);
	$skidkaPercentByOldPrice = floor($skidkaPercentByOldPrice);

	$raznicaByOldBrice = $oldPrice - $arResult['MIN_PRICE']['VALUE'];

	if (abs($skidkaPercentByOldPrice) > 0){
		$arResult['SKIDKA_PERCENT_BY_OLD_PRICE'] = $skidkaPercentByOldPrice;
		$arResult['RAZNICA_BY_OLD_PRICE'] = $raznicaByOldBrice;
	}

}


?>