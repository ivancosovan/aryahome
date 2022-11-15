<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;

Loader::includeModule('sale');

$city = false;

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$code = htmlspecialchars($request->getPost("code"));

$res = \Bitrix\Sale\Location\LocationTable::getList([
                                                        'filter' => [
                                                            '=TYPE.ID' => '5',
                                                            '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                                                            '=CODE' => $code
                                                        ],
                                                        'select' => ['NAME.NAME'],
                                                        'limit' => 1
                                                    ]);
if ($item = $res->fetch()) {
    $city = $item['SALE_LOCATION_LOCATION_NAME_NAME'];
}

echo $city;