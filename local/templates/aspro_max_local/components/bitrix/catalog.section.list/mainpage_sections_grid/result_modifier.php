<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
	
foreach($arResult['SECTIONS'] as $key => $section){
	$arFileTmp = CFile::ResizeImageGet(
	$section["DETAIL_PICTURE"],
	array("width" => 800, "height" => 800),
		BX_RESIZE_IMAGE_EXACT,
	);
	
	$arResult['SECTIONS'][$key]['DETAIL_PICTURE']=$arFileTmp['src'];
	if($key>7){
		break;
	}
}
?>