<?
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");
$arFields = [
   'NAME'		   => 'Рассчитанная сортировка',
   'ACTIVE'        => 'Y',
   'SORT'          => 50,
   'CODE'          => 'N_SORT',
   'PROPERTY_TYPE' => 'N',
   'IBLOCK_ID'     => 3,
];
$ibp = new CIBlockProperty;
$PropID = $ibp->Add($arFields);