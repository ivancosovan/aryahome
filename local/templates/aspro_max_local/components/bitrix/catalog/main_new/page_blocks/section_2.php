<?
global $arTheme;
$APPLICATION->IncludeComponent(
	"bitrix:catalog.section.list",
	"sections_compact_new",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"VIEW_TYPE" => "lg",
		"NO_MARGIN" => "Y",
		"SHOW_ICONS" => "Y",
		"SECTION_USER_FIELDS" => $arParams["USER_FIELDS"],
		"SECTION_FIELDS" => array('ID', 'IBLOCK_ID', 'PICTURE'),
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
		"COUNT_ELEMENTS" => "N",
		"TOP_DEPTH" => 1,
		"SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
		"VIEW_MODE" => $arParams["SECTIONS_VIEW_MODE"],
		"SHOW_PARENT_NAME" => $arParams["SECTIONS_SHOW_PARENT_NAME"],
		"HIDE_SECTION_NAME" => (isset($arParams["SECTIONS_HIDE_SECTION_NAME"]) ? $arParams["SECTIONS_HIDE_SECTION_NAME"] : "N"),
		"ADD_SECTIONS_CHAIN" => ((!$iSectionsCount || $arParams['INCLUDE_SUBSECTIONS'] !== "N") ? 'N' : 'Y'),
		"SECTIONS_LIST_PREVIEW_PROPERTY" => $arParams["SECTIONS_LIST_PREVIEW_PROPERTY"],
		"SECTIONS_LIST_PREVIEW_DESCRIPTION" => "N",
		"SHOW_SECTION_LIST_PICTURES" => $arParams["SHOW_SECTION_PICTURES"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"TEMPLATE_TYPE" => 	$arTheme['CATALOG_PAGE_SECTIONS']['VALUE'],
		"FILTER_NAME" => "arSectionFilter",
		"CACHE_FILTER" => "Y",
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
		"STORES" => $arParams["STORES"],
		"ASPRO_COUNT_ELEMENTS" => $arParams["SECTION_COUNT_ELEMENTS"]
	),
	$component
);?>