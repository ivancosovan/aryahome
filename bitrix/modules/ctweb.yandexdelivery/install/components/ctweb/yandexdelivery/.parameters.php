<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
    "GROUPS" => array(
        "FILTER_SETTINGS" => array(
            "SORT" => 150,
            "NAME" => GetMessage("GROUP_FILTER_SETTINGS"),
        ),
        "MAP_SETTINGS" => array(
            "SORT" => 150,
            "NAME" => GetMessage("GROUP_MAP_SETTINGS"),
        ),
    ),
    "PARAMETERS" => array(
        "RELOCATION" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("RELOCATION"),
            "TYPE" => "STRING",
            "DEFAULT" => "/personal/",
        ),
        "MAP_WIDTH" => array(
            "PARENT" => "MAP_SETTINGS",
            "NAME" => GetMessage("MAP_WIDTH"),
            "TYPE" => "STRING",
            "DEFAULT" => "100%",
        ),
        "MAP_HEIGHT" => array(
            "PARENT" => "MAP_SETTINGS",
            "NAME" => GetMessage("MAP_HEIGHT"),
            "TYPE" => "STRING",
            "DEFAULT" => "400px",
        ),
        "REGION_OPACITY" => array(
            "PARENT" => "MAP_SETTINGS",
            "NAME" => GetMessage("REGION_OPACITY"),
            "TYPE" => "STRING",
            "DEFAULT" => "0.3",
        ),
        "ROUTE_COLOR" => array(
            "PARENT" => "MAP_SETTINGS",
            "NAME" => GetMessage("ROUTE_COLOR"),
            "TYPE" => "STRING",
            "DEFAULT" => "4A84E3ff",
        ),
        "ROUTE_OPACITY" => array(
            "PARENT" => "MAP_SETTINGS",
            "NAME" => GetMessage("ROUTE_OPACITY"),
            "TYPE" => "STRING",
            "DEFAULT" => "0.8",
        ),
        "MAP_ZOOM" => array(
            "PARENT" => "MAP_SETTINGS",
            "NAME" => GetMessage("MAP_ZOOM"),
            "TYPE" => "STRING",
            "DEFAULT" => "14",
        ),
        "REGION_FILTER_NAME" => array(
            "PARENT" => "FILTER_SETTINGS",
            "NAME" => GetMessage("REGION_FILTER_NAME"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ),
        "STORE_FILTER_NAME" => array(
            "PARENT" => "FILTER_SETTINGS",
            "NAME" => GetMessage("STORE_FILTER_NAME"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ),

//        "REGION_ACTIVE" => array(
//            "PARENT" => "DATA_SOURCE",
//            "NAME" => GetMessage("REGION_ACTIVE"),
//            "TYPE" => "CHECKBOX",
//            "DEFAULT" => "Y",
//        ),
    ),
);