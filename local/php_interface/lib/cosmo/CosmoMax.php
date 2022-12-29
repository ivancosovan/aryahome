<?php

namespace Cosmo;

if (!defined('ASPRO_MAX_MODULE_ID')) {
  define('ASPRO_MAX_MODULE_ID', 'aspro.max');
}

use \Bitrix\Main\Application,
  \Bitrix\Main\Type\Collection,
  \Bitrix\Main\Loader,
  \Bitrix\Main\IO\File,
  \Bitrix\Main\Localization\Loc,
  \Bitrix\Main\Config\Option;

use CMax;

/**
 *
 */

class CosmoMax extends CMax
{
  const moduleID    = ASPRO_MAX_MODULE_ID;

  public static function GetAddToBasketArray(&$arItem, $totalCount = 0, $defaultCount = 1, $basketUrl = '', $bDetail = false, $arItemIDs = array(), $class_btn = "small", $arParams = array())
  {
    static $arAddToBasketOptions, $bUserAuthorized;
    if ($arAddToBasketOptions === NULL) {
      $arAddToBasketOptions = array(
        "SHOW_BASKET_ONADDTOCART" => Option::get(self::moduleID, "SHOW_BASKET_ONADDTOCART", "Y", SITE_ID) == "Y",
        "USE_PRODUCT_QUANTITY_LIST" => Option::get(self::moduleID, "USE_PRODUCT_QUANTITY_LIST", "Y", SITE_ID) == "Y",
        "USE_PRODUCT_QUANTITY_DETAIL" => Option::get(self::moduleID, "USE_PRODUCT_QUANTITY_DETAIL", "Y", SITE_ID) == "Y",
        "BUYNOPRICEGGOODS" => Option::get(self::moduleID, "BUYNOPRICEGGOODS", "NOTHING", SITE_ID),
        "BUYMISSINGGOODS" => Option::get(self::moduleID, "BUYMISSINGGOODS", "ADD", SITE_ID),
        "EXPRESSION_ORDER_BUTTON" => Option::get(self::moduleID, "EXPRESSION_ORDER_BUTTON", GetMessage("EXPRESSION_ORDER_BUTTON_DEFAULT"), SITE_ID),
        "EXPRESSION_ORDER_TEXT" => Option::get(self::moduleID, "EXPRESSION_ORDER_TEXT", GetMessage("EXPRESSION_ORDER_TEXT_DEFAULT"), SITE_ID),
        "EXPRESSION_SUBSCRIBE_BUTTON" => Option::get(self::moduleID, "EXPRESSION_SUBSCRIBE_BUTTON", GetMessage("EXPRESSION_SUBSCRIBE_BUTTON_DEFAULT"), SITE_ID),
        "EXPRESSION_SUBSCRIBED_BUTTON" => Option::get(self::moduleID, "EXPRESSION_SUBSCRIBED_BUTTON", GetMessage("EXPRESSION_SUBSCRIBED_BUTTON_DEFAULT"), SITE_ID),
        "EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT" => Option::get(self::moduleID, "EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT", GetMessage("EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT"), SITE_ID),
        "EXPRESSION_ADDEDTOBASKET_BUTTON_DEFAULT" => Option::get(self::moduleID, "EXPRESSION_ADDEDTOBASKET_BUTTON_DEFAULT", GetMessage("EXPRESSION_ADDEDTOBASKET_BUTTON_DEFAULT"), SITE_ID),
        "EXPRESSION_READ_MORE_OFFERS_DEFAULT" => Option::get(self::moduleID, "EXPRESSION_READ_MORE_OFFERS_DEFAULT", GetMessage("EXPRESSION_READ_MORE_OFFERS_DEFAULT"), SITE_ID),
      );

      global $USER;
      $bUserAuthorized = $USER->IsAuthorized();
    }

    $buttonText = $buttonHTML = $buttonACTION = '';
    $quantity = $ratio = 1;
    $max_quantity = 0;
    $float_ratio = is_double($arItem["CATALOG_MEASURE_RATIO"]);

    $minPriceRangeQty = 0;
    // if (isset($arItem['PRICE_MATRIX']) && $arItem['PRICE_MATRIX']) {
    if (isset($arItem['ITEM_PRICE_MODE']) && $arItem['ITEM_PRICE_MODE'] === 'Q') {
      $priceSelected = $arItem['ITEM_PRICE_SELECTED'];
      if (isset($arItem['FIX_PRICE_MATRIX']) && $arItem['FIX_PRICE_MATRIX']) {
        $priceSelected = $arItem['FIX_PRICE_MATRIX']['PRICE_SELECT'];
      }
      if (isset($arItem['ITEM_PRICES']) && $arItem['ITEM_PRICES'][$priceSelected]['MIN_QUANTITY'] != 1) {
        $minPriceRangeQty = $arItem['ITEM_PRICES'][$priceSelected]['MIN_QUANTITY'];
      }
    }

    $setMinQty = false;
    if ($arItem["CATALOG_MEASURE_RATIO"] || $minPriceRangeQty) {
      if ($minPriceRangeQty && ($minPriceRangeQty > $arItem["CATALOG_MEASURE_RATIO"])) {
        $quantity = $minPriceRangeQty;
        $setMinQty = true;
      } else {
        $quantity = $arItem["CATALOG_MEASURE_RATIO"];
      }
      if ($arItem["CATALOG_MEASURE_RATIO"]) {
        $ratio = $arItem["CATALOG_MEASURE_RATIO"];
      }
    } else {
      $quantity = $defaultCount;
    }

    if ($arItem["CATALOG_QUANTITY_TRACE"] == "Y") {
      if ($totalCount < $quantity) {
        $quantity = ($totalCount > $arItem["CATALOG_MEASURE_RATIO"] ? $totalCount : $arItem["CATALOG_MEASURE_RATIO"]);
      }
      if ($arItem["CATALOG_CAN_BUY_ZERO"] !== "Y") {
        $max_quantity = $totalCount;
      }
    }

    $canBuy = $arItem["CAN_BUY"];
    if ($arParams['USE_REGION'] == 'Y' && $arParams['STORES']) {
      $canBuy = (($totalCount && ($arItem["OFFERS"] || $arItem["CAN_BUY"])) || ((!$totalCount && $arItem["CATALOG_QUANTITY_TRACE"] == "N") || (!$totalCount && $arItem["CATALOG_QUANTITY_TRACE"] == "Y" && $arItem["CATALOG_CAN_BUY_ZERO"] == "Y")));
    }
    $arItem["CAN_BUY"] = $canBuy;

    //for buy_services in basket_fly
    if (isset($arParams["EXACT_QUANTITY"]) && $arParams["EXACT_QUANTITY"] > 0)
      $quantity = $arParams["EXACT_QUANTITY"];


    $arItemProps = $arItem['IS_OFFER'] === 'Y' ? ($arParams['OFFERS_CART_PROPERTIES'] ? implode(';', $arParams['OFFERS_CART_PROPERTIES']) : "") : ($arParams['PRODUCT_PROPERTIES'] ? implode(';', $arParams['PRODUCT_PROPERTIES']) : "");
    $partProp = ($arParams["PARTIAL_PRODUCT_PROPERTIES"] ? $arParams["PARTIAL_PRODUCT_PROPERTIES"] : "");
    $addProp = ($arParams["ADD_PROPERTIES_TO_BASKET"] ? $arParams["ADD_PROPERTIES_TO_BASKET"] : "");
    $emptyProp = $arItem["EMPTY_PROPS_JS"];
    if ($arItem["OFFERS"]) {
      global $arTheme;
      $type_sku = is_array($arTheme) ? (isset($arTheme["TYPE_SKU"]["VALUE"]) ? $arTheme["TYPE_SKU"]["VALUE"] : $arTheme["TYPE_SKU"]) : 'TYPE_1';
      if (!$bDetail && $arItem["OFFERS_MORE"] != "Y" && $type_sku != "TYPE_2") {
        $buttonACTION = 'ADD';
        $buttonText = array($arAddToBasketOptions['EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT'], $arAddToBasketOptions['EXPRESSION_ADDEDTOBASKET_BUTTON_DEFAULT']);
        $buttonHTML = '<span class="btn btn-default transition_bg ' . $class_btn . ' read_more1 to-cart animate-load" id="' . $arItemIDs['BUY_LINK'] . '" data-offers="N" data-iblockID="' . $arItem["IBLOCK_ID"] . '" data-item="' . $arItem["ID"] . '">' . self::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH . "/images/svg/basket.svg", $buttonText[0]) . '<span>' . $buttonText[0] . '</span></span><a rel="nofollow" href="' . $basketUrl . '" id="' . $arItemIDs['BASKET_LINK'] . '" class="' . $class_btn . ' in-cart btn btn-default transition_bg" data-item="' . $arItem["ID"] . '"  style="display:none;">' . self::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH . "/images/svg/inbasket.svg", $buttonText[1]) . '<span>' . $buttonText[1] . '</span></a>';
      } elseif (($bDetail && $arItem["FRONT_CATALOG"] == "Y") || $arItem["OFFERS_MORE"] == "Y" || $type_sku == "TYPE_2") {
        $buttonACTION = 'MORE';
        $buttonText = array($arAddToBasketOptions['EXPRESSION_READ_MORE_OFFERS_DEFAULT']);
        $buttonHTML = '<a class="btn btn-default basket read_more ' . $class_btn . '" rel="nofollow" href="' . $arItem["DETAIL_PAGE_URL"] . '" data-item="' . $arItem["ID"] . '">' . self::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH . "/images/svg/more_c.svg", $buttonText[0]) . '<span>' . $buttonText[0] . '</span></a>';
      }
    } elseif ($arItem["SHOW_MORE_BUTTON"] == "Y") {
      $buttonACTION = 'MORE';
      $buttonText = array($arAddToBasketOptions['EXPRESSION_READ_MORE_OFFERS_DEFAULT']);
      $buttonHTML = '<a class="btn btn-default basket read_more ' . $class_btn . '" rel="nofollow" href="' . $arItem["DETAIL_PAGE_URL"] . '" data-item="' . $arItem["ID"] . '">' . self::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH . "/images/svg/more_c.svg", $buttonText[0]) . '<span>' . $buttonText[0] . '</span></a>';
    } else {
      if ($bPriceExists = isset($arItem["MIN_PRICE"]) && $arItem["MIN_PRICE"]["VALUE"] > 0) {
        // price exists
        if ($totalCount > 0 && (isset($arItem["CAN_BUY"]) && $arItem["CAN_BUY"])) {
          // rest exists
          if ((isset($arItem["CAN_BUY"]) && $arItem["CAN_BUY"]) || (isset($arItem["MIN_PRICE"]) && $arItem["MIN_PRICE"]["CAN_BUY"] == "Y")) {
            if ($bDetail && $arItem["FRONT_CATALOG"] == "Y") {
              $buttonACTION = 'MORE';
              $buttonText = array($arAddToBasketOptions['EXPRESSION_READ_MORE_OFFERS_DEFAULT']);
              $rid = ($arItem["RID"] ? "?RID=" . $arItem["RID"] : "");
              $buttonHTML = '<a class="btn btn-default transition_bg basket read_more ' . $class_btn . '" rel="nofollow" href="' . $arItem["DETAIL_PAGE_URL"] . $rid . '" data-item="' . $arItem["ID"] . '">' . self::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH . "/images/svg/more_c.svg", $buttonText[0]) . '<span>' . $buttonText[0] . '</span></a>';
            } else {
              $arItem["CAN_BUY"] = 1;
              $buttonACTION = 'ADD';
              $butSvg = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M18.5 20C18.5 20.8284 17.8284 21.5 17 21.5C16.1716 21.5 15.5 20.8284 15.5 20C15.5 19.1716 16.1716 18.5 17 18.5C17.8284 18.5 18.5 19.1716 18.5 20Z" stroke="white"></path>
                                        <path d="M8.5 20C8.5 20.8284 7.82843 21.5 7 21.5C6.17157 21.5 5.5 20.8284 5.5 20C5.5 19.1716 6.17157 18.5 7 18.5C7.82843 18.5 8.5 19.1716 8.5 20Z" stroke="white"></path>
                                        <path d="M17.0002 16.5H8.00024C7.00024 16.5 6.00024 15 7.50023 13.5" stroke="white" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M2.5 3.5C4 3.5 5 4 5.5 5.5" stroke="white" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M5.5 5.5L7.5 13.5L19.5 12.5L21.5 5.5H5.5Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>';
              $buttonText = array($arAddToBasketOptions['EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT'], $arAddToBasketOptions['EXPRESSION_ADDEDTOBASKET_BUTTON_DEFAULT']);
              $buttonHTML = '<button data-value="' . $arItem["MIN_PRICE"]["DISCOUNT_VALUE"] . '" data-currency="' . $arItem["MIN_PRICE"]["CURRENCY"] . '"  class="purchase__button to-cart transition_bg animate-load" 
                            data-item="' . $arItem["ID"] . '" data-float_ratio="' . $float_ratio . '" data-ratio="' . $ratio . '" data-bakset_div="bx_basket_div_' . $arItem["ID"] . ($arItemIDs['DOP_ID'] ? '_' . $arItemIDs['DOP_ID'] : '') . '" 
                            data-props="' . $arItemProps . '" data-part_props="' . $partProp . '" data-add_props="' . $addProp . '"  data-empty_props="' . $emptyProp . '" data-offers="' . $arItem["IS_OFFER"] . '" data-iblockID="' . $arItem["IBLOCK_ID"] . '"  
                            data-quantity="' . $quantity . '">' . $butSvg . '<span>' . $buttonText[0] . '</span></button>
                            <a rel="nofollow" href="' . $basketUrl . '" class="' . $class_btn . ' in-cart purchase__button transition_bg" data-item="' . $arItem["ID"] . '"  style="display:none;">' . self::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH . "/images/svg/inbasket.svg", $buttonText[1]) . '<span>' . $buttonText[1] . '</span></a>';
            }
          } elseif ($arItem["CATALOG_SUBSCRIBE"] == "Y") {
            $buttonACTION = 'SUBSCRIBE';
            $buttonText = array($arAddToBasketOptions['EXPRESSION_SUBSCRIBE_BUTTON'], $arAddToBasketOptions['EXPRESSION_SUBSCRIBED_BUTTON']);
            $buttonHTML = '<span class="' . $class_btn . ' ss to-subscribe' . (!$bUserAuthorized ? ' auth' : '') . (self::checkVersionModule('16.5.3', 'catalog') ? ' nsubsc' : '') . ' btn btn-default transition_bg" rel="nofollow" data-param-form_id="subscribe" data-name="subscribe" data-param-id="' . $arItem["ID"] . '" data-item="' . $arItem["ID"] . '">' . self::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH . "/images/svg/subsribe_c.svg", $buttonText[0]) . '<span>' . $buttonText[0] . '</span></span><span class="' . $class_btn . ' ss in-subscribe btn btn-default transition_bg" rel="nofollow" style="display:none;" data-item="' . $arItem["ID"] . '">' . self::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH . "/images/svg/subsribe_c.svg", $buttonText[0]) . '<span>' . $buttonText[1] . '</span></span>';
          }
        } else {
          if (!strlen($arAddToBasketOptions['EXPRESSION_ORDER_BUTTON'])) {
            $arAddToBasketOptions['EXPRESSION_ORDER_BUTTON'] = GetMessage("EXPRESSION_ORDER_BUTTON_DEFAULT");
          }
          if (!strlen($arAddToBasketOptions['EXPRESSION_SUBSCRIBE_BUTTON'])) {
            $arAddToBasketOptions['EXPRESSION_SUBSCRIBE_BUTTON'] = GetMessage("EXPRESSION_SUBSCRIBE_BUTTON_DEFAULT");
          }
          if (!strlen($arAddToBasketOptions['EXPRESSION_SUBSCRIBED_BUTTON'])) {
            $arAddToBasketOptions['EXPRESSION_SUBSCRIBED_BUTTON'] = GetMessage("EXPRESSION_SUBSCRIBED_BUTTON_DEFAULT");
          }
          // no rest
          if ($bDetail && $arItem["FRONT_CATALOG"] == "Y") {
            $buttonACTION = 'MORE';
            $buttonText = array($arAddToBasketOptions['EXPRESSION_READ_MORE_OFFERS_DEFAULT']);
            $rid = ($arItem["RID"] ? "?RID=" . $arItem["RID"] : "");
            $buttonHTML = '<a class="btn btn-default basket read_more ' . $class_btn . '" rel="nofollow" href="' . $arItem["DETAIL_PAGE_URL"] . $rid . '" data-item="' . $arItem["ID"] . '">' . self::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH . "/images/svg/more_c.svg", $buttonText[0]) . '<span>' . $buttonText[0] . '</span></a>';
          } else {
            $buttonACTION = $arAddToBasketOptions["BUYMISSINGGOODS"];
            if ($arAddToBasketOptions["BUYMISSINGGOODS"] == "ADD" /*|| $arItem["CAN_BUY"]*/) {
              if ($arItem["CAN_BUY"]) {
                $buttonText = array($arAddToBasketOptions['EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT'], $arAddToBasketOptions['EXPRESSION_ADDEDTOBASKET_BUTTON_DEFAULT']);
                $buttonHTML = '<span data-value="' . $arItem["MIN_PRICE"]["DISCOUNT_VALUE"] . '" data-currency="' . $arItem["MIN_PRICE"]["CURRENCY"] . '" class="' . $class_btn . ' to-cart btn btn-default transition_bg animate-load" data-item="' . $arItem["ID"] . '" data-float_ratio="' . $float_ratio . '" data-ratio="' . $ratio . '" data-bakset_div="bx_basket_div_' . $arItem["ID"] . '" data-props="' . $arItemProps . '" data-part_props="' . $partProp . '" data-add_props="' . $addProp . '"  data-empty_props="' . $emptyProp . '" data-offers="' . $arItem["IS_OFFER"] . '" data-iblockID="' . $arItem["IBLOCK_ID"] . '" data-quantity="' . $quantity . '">' . self::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH . "/images/svg/basket.svg", $buttonText[0]) . '<span>' . $buttonText[0] . '</span></span><a rel="nofollow" href="' . $basketUrl . '" class="' . $class_btn . ' in-cart btn btn-default transition_bg" data-item="' . $arItem["ID"] . '"  style="display:none;">' . self::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH . "/images/svg/inbasket.svg", $buttonText[1]) . '<span>' . $buttonText[1] . '</span></a>';
              } else {
                if ($arAddToBasketOptions["BUYMISSINGGOODS"] == "SUBSCRIBE" && $arItem["CATALOG_SUBSCRIBE"] == "Y") {
                  $buttonText = array($arAddToBasketOptions['EXPRESSION_SUBSCRIBE_BUTTON'], $arAddToBasketOptions['EXPRESSION_SUBSCRIBED_BUTTON']);
                  $buttonHTML = '<span class="' . $class_btn . ' ss to-subscribe' . (!$bUserAuthorized ? ' auth' : '') . (self::checkVersionModule('16.5.3', 'catalog') ? ' nsubsc' : '') . ' btn btn-default transition_bg" rel="nofollow" data-name="subscribe" data-param-form_id="subscribe" data-param-id="' . $arItem["ID"] . '"  data-item="' . $arItem["ID"] . '">' . self::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH . "/images/svg/subsribe_c.svg", $buttonText[0]) . '<span>' . $buttonText[0] . '</span></span><span class="' . $class_btn . ' ss in-subscribe btn btn-default transition_bg" rel="nofollow" style="display:none;" data-item="' . $arItem["ID"] . '">' . self::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH . "/images/svg/subsribe_c.svg", $buttonText[0]) . '<span>' . $buttonText[1] . '</span></span>';
                } else {
                  $buttonText = array($arAddToBasketOptions['EXPRESSION_ORDER_BUTTON']);
                  $buttonHTML = '<span class="' . $class_btn . ' to-order btn btn-default animate-load" data-event="jqm" data-param-form_id="TOORDER" data-name="toorder" data-autoload-product_name="' . self::formatJsName($arItem["NAME"]) . '" data-autoload-product_id="' . $arItem["ID"] . '">' . self::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH . "/images/svg/mail_c.svg", $buttonText[0]) . '<span>' . $buttonText[0] . '</span></span>';
                  if ($arAddToBasketOptions['EXPRESSION_ORDER_TEXT']) {
                    $buttonHTML .= '<div class="more_text">' . $arAddToBasketOptions['EXPRESSION_ORDER_TEXT'] . '</div>';
                  }
                }
              }
            } elseif ($arAddToBasketOptions["BUYMISSINGGOODS"] == "SUBSCRIBE" && $arItem["CATALOG_SUBSCRIBE"] == "Y") {

              $buttonText = array($arAddToBasketOptions['EXPRESSION_SUBSCRIBE_BUTTON'], $arAddToBasketOptions['EXPRESSION_SUBSCRIBED_BUTTON']);
              $buttonHTML = '<span class="' . $class_btn . ' ss to-subscribe ' . (!$bUserAuthorized ? ' auth' : '') . (self::checkVersionModule('16.5.3', 'catalog') ? ' nsubsc' : '') . ' btn btn-default transition_bg" data-name="subscribe" data-param-form_id="subscribe" data-param-id="' . $arItem["ID"] . '"  rel="nofollow" data-item="' . $arItem["ID"] . '">' . self::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH . "/images/svg/mail_c.svg", $buttonText[0]) . '<span>' . $buttonText[0] . '</span></span><span class="' . $class_btn . ' ss in-subscribe btn btn-default transition_bg" rel="nofollow" style="display:none;" data-item="' . $arItem["ID"] . '">' . self::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH . "/images/svg/mail_c.svg", $buttonText[0]) . '<span>' . $buttonText[1] . '</span></span>';
            } elseif ($arAddToBasketOptions["BUYMISSINGGOODS"] == "ORDER") {
              $buttonText = array($arAddToBasketOptions['EXPRESSION_ORDER_BUTTON']);
              $buttonHTML = '<span class="' . $class_btn . ' to-order btn btn-default animate-load" data-event="jqm" data-param-form_id="TOORDER" data-name="toorder" data-autoload-product_name="' . self::formatJsName($arItem["NAME"]) . '" data-autoload-product_id="' . $arItem["ID"] . '">' . self::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH . "/images/svg/mail_c.svg", $buttonText[0]) . '<span>' . $buttonText[0] . '</span></span>';
              if ($arAddToBasketOptions['EXPRESSION_ORDER_TEXT']) {
                $buttonHTML .= '<div class="more_text">' . $arAddToBasketOptions['EXPRESSION_ORDER_TEXT'] . '</div>';
              }
            }
          }
        }
      } else {
        // no price or price <= 0
        if ($bDetail && $arItem["FRONT_CATALOG"] == "Y") {
          $buttonACTION = 'MORE';
          $buttonText = array($arAddToBasketOptions['EXPRESSION_READ_MORE_OFFERS_DEFAULT']);
          $buttonHTML = '<a class="btn btn-default transition_bg basket read_more ' . $class_btn . '" rel="nofollow" href="' . $arItem["DETAIL_PAGE_URL"] . '" data-item="' . $arItem["ID"] . '">' . self::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH . "/images/svg/more_c.svg", $buttonText[0]) . '<span>' . $buttonText[0] . '</span></a>';
        } else {
          $buttonACTION = $arAddToBasketOptions["BUYNOPRICEGGOODS"];
          if ($arAddToBasketOptions["BUYNOPRICEGGOODS"] == "ORDER") {
            $buttonText = $arAddToBasketOptions['EXPRESSION_ORDER_BUTTON'] ? array($arAddToBasketOptions['EXPRESSION_ORDER_BUTTON']) : array(Loc::getMessage('EXPRESSION_ORDER_BUTTON_DEFAULT'));
            $buttonHTML = '<span class="' . $class_btn . ' to-order btn btn-default animate-load" data-event="jqm" data-param-form_id="TOORDER" data-name="toorder" data-autoload-product_name="' . self::formatJsName($arItem["NAME"]) . '" data-autoload-product_id="' . $arItem["ID"] . '">' . self::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH . "/images/svg/mail_c.svg", $buttonText[0]) . '<span>' . $buttonText[0] . '</span></span>';
            if ($arAddToBasketOptions['EXPRESSION_ORDER_TEXT']) {
              $buttonHTML .= '<div class="more_text">' . $arAddToBasketOptions['EXPRESSION_ORDER_TEXT'] . '</div>';
            }
          }
        }
      }
    }

    //add name atr for js notice
    $buttonHTML .= '<span class="hidden" data-js-item-name="' . self::formatJsName($arItem['IPROPERTY_VALUES']["ELEMENT_PAGE_TITLE"] ?? $arItem['NAME']) . '"></span>';

    $arOptions = array("OPTIONS" => $arAddToBasketOptions, "TEXT" => $buttonText, "HTML" => $buttonHTML, "ACTION" => $buttonACTION, "RATIO_ITEM" => $ratio, "MIN_QUANTITY_BUY" => $quantity, "MAX_QUANTITY_BUY" => $max_quantity, "CAN_BUY" => $canBuy);

    if ($setMinQty) {
      $arOptions["SET_MIN_QUANTITY_BUY"] = true;
    }

    foreach (GetModuleEvents(ASPRO_MAX_MODULE_ID, 'OnAsproGetBuyBlockElement', true) as $arEvent) // event for manipulation with buy block element
      ExecuteModuleEventEx($arEvent, array($arItem, $totalCount, $arParams, &$arOptions));

    return $arOptions;
  }

  public static function ShowCabinetLinkNew($icon = true, $text = true, $class_icon = '', $show_mess = false, $message = '')
  {
    global $APPLICATION, $arTheme;
    static $hauth_call;

    $iCalledID = ++$hauth_call;

    $type_svg = '';
    if ($class_icon) {
      $tmp = explode(' ', $class_icon);
      $type_svg = '_' . $tmp[0];
    }
    $userID = self::GetUserID();
    $html = '<!-- noindex --><div class="auth_wr_inner ' . ($userID && $text ? 'with_dropdown' : '') . '">';
    if (!$message)
      $message = Loc::getMessage('CABINET_LINK');

    if ($userID) {
      global $USER;
      $name = ($USER->GetFullName() ? $USER->GetFullName() : $USER->GetLogin());

      $html .= '<a rel="nofollow" title="' . $name . '" class="personal-link dark-color logined header__login' . ($text ? /*' with_dropdown'*/ '' : '') . '" href="' . $arTheme['PERSONAL_PAGE_URL']['VALUE'] . '">';
      if ($icon)
        $html .= '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path class="path-stroke" d="M20.5 20.5C20.5 18.5 18.5 16.5 15.5 16.5C15.5 18.5 14 19.5 12 19.5C10 19.5 8.5 18.5 8.5 16.5C5.5 16.5 3.5 18.5 3.5 20.5" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"></path>
        <path class="path-stroke" d="M12.0001 14.4999C8.50014 14.4999 8.5 8.49994 8.5 6.49995C11.5001 6.49995 12.5001 5.49997 13.5 4.50004L13.5001 4.5C13.5001 5.49994 14.5 6.49994 15.5001 6.49995V6.50051C15.5 8.50105 15.4998 14.4999 12.0001 14.4999Z" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"></path>
        <path class="path-fill" d="M15.5 14C15.2239 14 15 14.2239 15 14.5C15 14.7761 15.2239 15 15.5 15V14ZM19.5 13.5L19.7236 13.9472C19.8674 13.8753 19.9678 13.7387 19.9935 13.5801C20.0193 13.4214 19.9672 13.2601 19.8536 13.1464L19.5 13.5ZM12 1C11.7239 1 11.5 1.22386 11.5 1.5C11.5 1.77614 11.7239 2 12 2V1ZM15.5 15C17.0995 15 18.6768 14.4706 19.7236 13.9472L19.2764 13.0528C18.3232 13.5294 16.9005 14 15.5 14V15ZM19.8536 13.1464C19.2146 12.5075 18.862 11.5106 18.6071 10.2596C18.4812 9.64162 18.3836 8.98741 18.2804 8.30954C18.1781 7.63699 18.0706 6.9432 17.9264 6.27023C17.6398 4.9329 17.1949 3.60572 16.2818 2.61439C15.3485 1.60101 13.9851 1 12 1V2C13.7649 2 14.839 2.52399 15.5463 3.29186C16.2738 4.08178 16.6727 5.1921 16.9486 6.47976C17.0856 7.11929 17.1891 7.78488 17.2918 8.45999C17.3937 9.12978 17.4953 9.8115 17.6272 10.4592C17.888 11.7394 18.2854 12.9925 19.1464 13.8536L19.8536 13.1464Z" fill="#333333"></path>
        <path class="path-fill" d="M8.5 14C8.77614 14 9 14.2239 9 14.5C9 14.7761 8.77614 15 8.5 15V14ZM4.5 13.5L4.27639 13.9472C4.13264 13.8753 4.0322 13.7387 4.00646 13.5801C3.98071 13.4214 4.0328 13.2601 4.14645 13.1464L4.5 13.5ZM12 1C12.2761 1 12.5 1.22386 12.5 1.5C12.5 1.77614 12.2761 2 12 2V1ZM8.5 15C6.90052 15 5.32319 14.4706 4.27639 13.9472L4.72361 13.0528C5.67681 13.5294 7.09948 14 8.5 14V15ZM4.14645 13.1464C4.78536 12.5075 5.13804 11.5106 5.39287 10.2596C5.51875 9.64162 5.6164 8.98741 5.71956 8.30954C5.8219 7.63699 5.92939 6.9432 6.0736 6.27023C6.36017 4.9329 6.8051 3.60572 7.71816 2.61439C8.65154 1.60101 10.0149 1 12 1V2C10.2351 2 9.16096 2.52399 8.45371 3.29186C7.72615 4.08178 7.32733 5.1921 7.0514 6.47976C6.91436 7.11929 6.81091 7.78488 6.70818 8.45999C6.60625 9.12978 6.50468 9.8115 6.37275 10.4592C6.11196 11.7394 5.71464 12.9925 4.85355 13.8536L4.14645 13.1464Z" fill="#333333"></path>
        <path class="path-stroke" d="M15.5 16.5C14 16.5 13.4999 16 13.5 14.5" stroke="#333333" stroke-linecap="round"></path>
        <path class="path-stroke" d="M8.5 16.5C10 16.5 10.5001 16 10.5 14.5" stroke="#333333" stroke-linecap="round"></path>
      </svg>';
      if ($text)
        $html .= '<span class="wrap">';

      if ($text)
        $html .= '<span class="name">' . Loc::getMessage('CABINET_LINK') . '</span>';
      if ($show_mess)
        $html .= '<span class="title">' . $message . '</span>';

      if ($text)
        $html .= '</span>';

      $html .= '</a>';
      if ($text)
        $html .= self::showIconSvg('downs', SITE_TEMPLATE_PATH . '/images/svg/trianglearrow_down.svg', $message, $class_icon);; ?>
      <? ob_start(); ?>
      <? $APPLICATION->IncludeComponent(
        "bitrix:menu",
        "cabinet_dropdown",
        array(
          "COMPONENT_TEMPLATE" => "cabinet_dropdown",
          "MENU_CACHE_TIME" => "3600000",
          "MENU_CACHE_TYPE" => "A",
          "MENU_CACHE_USE_GROUPS" => "Y",
          "MENU_CACHE_GET_VARS" => array(),
          "DELAY" => "N",
          "MAX_LEVEL" => "4",
          "ALLOW_MULTI_SELECT" => "Y",
          "ROOT_MENU_TYPE" => "cabinet",
          "CHILD_MENU_TYPE" => "left",
          "USE_EXT" => "Y"
        ),
        array("HIDE_ICONS" => "Y")
      ); ?>
      <? $html .= ob_get_contents();
      ob_end_clean(); ?>
    <?
    } else {
      $url = ((isset($_GET['backurl']) && $_GET['backurl']) ? $_GET['backurl'] : $APPLICATION->GetCurUri());
      $html .= '<a rel="nofollow" title="' . Loc::getMessage('CABINET_LINK') . '" class="personal-link dark-color animate-load header__login" data-event="jqm" data-param-type="auth" data-param-backurl="' . htmlspecialcharsbx($url) . '" data-name="auth" href="' . $arTheme['PERSONAL_PAGE_URL']['VALUE'] . '">';
      if ($icon)
        $html .= self::showIconSvg('cabinet', SITE_TEMPLATE_PATH . '/images/svg/user.svg', $message, $class_icon);
      if ($text)
        $html .= '<span class="wrap">';

      if ($text)
        $html .= '<span class="name">' . Loc::getMessage('LOGIN') . '</span>';
      if ($show_mess)
        $html .= '<span class="title">' . $message . '</span>';
      if ($text)
        $html .= '</span>';

      $html .= '</a>';
    }
    $html .= '</div><!-- /noindex -->'; ?>

    <? \Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID('header-auth-block' . $iCalledID); ?>
    <?= $html; ?>
    <? \Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID('header-auth-block' . $iCalledID); ?>

  <? }

  public static function ShowBasketWithCompareLink($class_link = 'top-btn hover', $class_icon = '', $show_price = false, $class_block = '', $force_show = false, $bottom = false, $div_class = '')
  { ?>
    <? global $APPLICATION, $arTheme, $arBasketPrices;
    static $basket_call;
    $type_svg = '';
    if ($class_icon) {
      $tmp = explode(' ', $class_icon);
      $type_svg = '_' . $tmp[0];
    }


    $iCalledID = ++$basket_call; ?>
    <? if (($arTheme['ORDER_BASKET_VIEW']['VALUE'] == 'NORMAL' || ($arTheme['ORDER_BASKET_VIEW']['VALUE'] == 'BOTTOM' && $bottom)) || $force_show) : ?>
      <? if ($div_class) : ?>
        <div class="<?= $div_class ?>">
        <? endif; ?>
        <? \Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID('header-basket-with-compare-block' . $iCalledID); ?>
        <? if ($arTheme['CATALOG_COMPARE']['VALUE'] != 'N') : ?>
          <? if ($class_block) : ?>
            <div class="<?= $class_block; ?>">
            <? endif; ?>
            <? $APPLICATION->IncludeComponent(
              "bitrix:main.include",
              ".default",
              array(
                "COMPONENT_TEMPLATE" => ".default",
                "PATH" => SITE_DIR . "ajax/show_compare_preview_top.php",
                "AREA_FILE_SHOW" => "file",
                "AREA_FILE_SUFFIX" => "",
                "AREA_FILE_RECURSIVE" => "Y",
                "CLASS_LINK" => $class_link,
                "CLASS_ICON" => $class_icon,
                "FROM_MODULE" => "Y",
                "EDIT_TEMPLATE" => "standard.php"
              ),
              false,
              array('HIDE_ICONS' => 'Y')
            ); ?>
            <? if ($class_block) : ?>
            </div>
          <? endif; ?>
        <? endif; ?>
        <? if (self::getShowBasket()) : ?>
          <!-- noindex -->
          <? if ($class_block) : ?>
            <div class="<?= $class_block; ?>">
            <? endif; ?>
            <div class="header__favourites">
              <a rel="nofollow" class="basket-link delay <?= $class_link; ?> <?= $class_icon; ?> <?= ($arBasketPrices['DELAY_COUNT'] ? 'basket-count' : ''); ?>" href="<?= $arTheme['BASKET_PAGE_URL']['VALUE']; ?>#delayed" data-href="<?= $arTheme['BASKET_PAGE_URL']['VALUE']; ?>#delayed" title="<?= $arBasketPrices['DELAY_SUMM_TITLE']; ?>">
                <span class="js-basket-block">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17 3.5C14.5 3.5 12 5.5 12 7.5C12 5.5 9.5 3.5 7 3.5C4.5 3.5 2.5 5.5 2.5 8.5C2.5 11.5 4.5 17.5 12 20.5C19.5 17.5 21.5 11.5 21.5 8.5C21.5 5.5 19.5 3.5 17 3.5Z" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                  <span class="count header__count"><?= $arBasketPrices['DELAY_COUNT']; ?></span>
                </span>
              </a>
            </div>
            <? if ($class_block) : ?>
            </div>
          <? endif; ?>
          <? if ($class_block) : ?>
            <div class="<?= $class_block; ?> <?= $arTheme['ORDER_BASKET_VIEW']['VALUE'] ? 'top_basket' : '' ?>">
            <? endif; ?>
            <div class="header__cart">
              <a rel="nofollow" class="basket-link basket <?= ($show_price ? 'has_prices' : ''); ?> <?= $class_link; ?> <?= $class_icon; ?> <?= ($arBasketPrices['BASKET_COUNT'] ? 'basket-count' : ''); ?>" href="<?= $arTheme['BASKET_PAGE_URL']['VALUE']; ?>" title="<?= $arBasketPrices['BASKET_SUMM_TITLE']; ?>">
                <span class="js-basket-block">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18.5 20C18.5 20.8284 17.8284 21.5 17 21.5C16.1716 21.5 15.5 20.8284 15.5 20C15.5 19.1716 16.1716 18.5 17 18.5C17.8284 18.5 18.5 19.1716 18.5 20Z" stroke="#333333"></path>
                    <path d="M8.5 20C8.5 20.8284 7.82843 21.5 7 21.5C6.17157 21.5 5.5 20.8284 5.5 20C5.5 19.1716 6.17157 18.5 7 18.5C7.82843 18.5 8.5 19.1716 8.5 20Z" stroke="#333333"></path>
                    <path d="M17.0002 16.5H8.00024C7.00024 16.5 6.00024 15 7.50023 13.5" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M2.5 3.5C4 3.5 5 4 5.5 5.5" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M5.5 5.5L7.5 13.5L19.5 12.5L21.5 5.5H5.5Z" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                  <? if ($show_price) : ?>
                    <span class="wrap">
                    <? endif; ?>
                    <span class="count header__count"><?= $arBasketPrices['BASKET_COUNT']; ?></span>
                    <? if ($show_price) : ?>
                      <span class="prices"><?= ($arBasketPrices['BASKET_COUNT'] ? $arBasketPrices['BASKET_SUMM'] : $arBasketPrices['BASKET_SUMM_TITLE_SMALL']) ?></span>
                    </span>
                  <? endif; ?>
                </span>
              </a>
              <span class="basket_hover_block loading_block loading_block_content"></span>
            </div>

            <? if ($class_block) : ?>
            </div>
          <? endif; ?>
          <!-- /noindex -->
        <? endif; ?>
        <? \Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID('header-basket-with-compare-block' . $iCalledID, ''); ?>
        <? if ($div_class) : ?>
        </div>
      <? endif; ?>
    <? endif; ?>
  <? }

  public static function ShowMobileMenuBasket()
  {
    static $mbasket_call;
    global $APPLICATION, $arTheme, $arBasketPrices, $compare_items;

    $basketUrl = trim($arTheme['BASKET_PAGE_URL']['VALUE']);
    $compareUrl = trim($arTheme['COMPARE_PAGE_URL']['VALUE']);

    $bShowBasket = (strlen($basketUrl) && self::getShowBasket());

    $iCalledID = ++$mbasket_call;
    $count_compare = 0;
    if ($compare_items) {
      $count_compare = count($compare_items);
    } else {
      if (isset($_SESSION["CATALOG_COMPARE_LIST"][$arTheme['CATALOG_IBLOCK_ID']['VALUE']]['ITEMS'])) {
        $compare_items = array_keys($_SESSION["CATALOG_COMPARE_LIST"][$arTheme['CATALOG_IBLOCK_ID']['VALUE']]['ITEMS']);
        $count_compare = count(array_keys($compare_items));
      }
    } ?>
    <? \Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID('mobile-basket-with-compare-block' . $iCalledID); ?>
    <!-- noindex -->
    <? if ($bShowBasket) : ?>
      <li class="counters header-mobile__item">
        <a rel="nofollow" class="dark-color basket-link basket ready <?= ($arBasketPrices['BASKET_COUNT'] ? 'basket-count' : ''); ?>" href="<?= $basketUrl ?>">
          <span class="count<?= (!$arBasketPrices['BASKET_COUNT'] ? ' empted' : '') ?> header-mobile__count"><?= $arBasketPrices['BASKET_COUNT']; ?></span>
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18.5 20C18.5 20.8284 17.8284 21.5 17 21.5C16.1716 21.5 15.5 20.8284 15.5 20C15.5 19.1716 16.1716 18.5 17 18.5C17.8284 18.5 18.5 19.1716 18.5 20Z" stroke="#333333"></path>
            <path d="M8.5 20C8.5 20.8284 7.82843 21.5 7 21.5C6.17157 21.5 5.5 20.8284 5.5 20C5.5 19.1716 6.17157 18.5 7 18.5C7.82843 18.5 8.5 19.1716 8.5 20Z" stroke="#333333"></path>
            <path d="M17 16.5H8C6.99999 16.5 6 15 7.49999 13.5" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"></path>
            <path d="M2.5 3.5C4 3.5 5 4 5.5 5.5" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"></path>
            <path d="M5.5 5.5L7.5 13.5L19.5 12.5L21.5 5.5H5.5Z" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
          <span class="header-mobile__caption"><?= Loc::getMessage('JS_BASKET_TITLE') ?></span>
        </a>
      </li>
      <li class="counters header-mobile__item">
        <a rel="nofollow" class="dark-color basket-link delay ready <?= ($arBasketPrices['DELAY_COUNT'] ? 'basket-count' : ''); ?>" href="<?= $arTheme['BASKET_PAGE_URL']['VALUE']; ?>#delayed" data-href="<?= $basketUrl; ?>#delayed">
          <span class="count<?= (!$arBasketPrices['DELAY_COUNT'] ? ' empted' : '') ?> header-mobile__count"><?= $arBasketPrices['DELAY_COUNT']; ?></span>
          <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M17.8 3.5C15.3 3.5 12.8 5.5 12.8 7.5C12.8 5.5 10.3 3.5 7.80005 3.5C5.30005 3.5 3.30005 5.5 3.30005 8.5C3.30005 11.5 5.30005 17.5 12.8 20.5C20.3 17.5 22.3 11.5 22.3 8.5C22.3 5.5 20.3 3.5 17.8 3.5Z" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
          <span class="header-mobile__caption"><?= Loc::getMessage('JS_BASKET_DELAY_TITLE') ?></span>
        </a>
      </li>
    <? endif; ?>
    <!-- /noindex -->
    <? \Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID('mobile-basket-with-compare-block' . $iCalledID); ?>
<? }
    
}
