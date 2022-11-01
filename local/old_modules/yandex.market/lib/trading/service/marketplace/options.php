<?php

namespace Yandex\Market\Trading\Service\Marketplace;

use Yandex\Market;
use Bitrix\Main;
use Yandex\Market\Trading\Service as TradingService;
use Yandex\Market\Trading\Entity as TradingEntity;

class Options extends TradingService\Common\Options
{
	/** @var Provider */
	protected $provider;

	protected static function includeMessages()
	{
		Main\Localization\Loc::loadMessages(__FILE__);
		parent::includeMessages();
	}

	public function __construct(Provider $provider)
	{
		parent::__construct($provider);
	}

	public function getTitle($version = '')
	{
		$suffix = $version !== '' ? '_' . $version : '';

		return static::getLang('TRADING_SERVICE_MARKETPLACE_TITLE' . $suffix);
	}

	public function getPaySystemId()
	{
		return (string)$this->getValue('PAY_SYSTEM_ID');
	}

	public function getDeliveryId()
	{
		return (string)$this->getValue('DELIVERY_ID');
	}

	public function includeBasketSubsidy()
	{
		return (string)$this->getValue('BASKET_SUBSIDY_INCLUDE') === Market\Reference\Storage\Table::BOOLEAN_Y;
	}

	public function getSubsidyPaySystemId()
	{
		return (string)$this->getValue('SUBSIDY_PAY_SYSTEM_ID');
	}

	public function useWarehouses()
	{
		return (string)$this->getValue('USE_WAREHOUSES') === Market\Reference\Storage\Table::BOOLEAN_Y;
	}

	public function getWarehouseStoreField()
	{
		return $this->getRequiredValue('WAREHOUSE_STORE_FIELD');
	}

	public function getProductStores()
	{
		return (array)$this->getRequiredValue('PRODUCT_STORE');
	}

	public function productUpdatedAt()
	{
		$dateFormatted = (string)$this->getValue('PRODUCT_UPDATED_AT');

		return (
			$dateFormatted !== ''
				? new Main\Type\DateTime($dateFormatted, \DateTime::ATOM)
				: null
		);
	}

	public function isAllowModifyPrice()
	{
		return true;
	}

	public function isAllowProductSkuPrefix()
	{
		return Market\Config::isExpertMode();
	}

	/** @return Options\SelfTestOption */
	public function getSelfTestOption()
	{
		return $this->getFieldset('SELF_TEST');
	}

	public function getEnvironmentFieldActions()
	{
		return array_filter([
			$this->getEnvironmentCisActions(),
			$this->getEnvironmentItemsActions(),
		]);
	}

	protected function getEnvironmentCisActions()
	{
		return [
			'FIELD' => 'SHIPMENT.ITEM.STORE.MARKING_CODE',
			'PATH' => 'send/cis',
			'PAYLOAD' => static function(array $action) {
				$itemsMap = [];
				$newIndex = 0;
				$result = [
					'items' => [],
				];

				foreach ($action['VALUE'] as $storeItem)
				{
					$markingCode = trim($storeItem['VALUE']);

					if ($markingCode === '') { continue; }

					$itemKey = $storeItem['XML_ID'] . ':' . $storeItem['PRODUCT_ID'];
					$cis = Market\Data\Trading\Cis::fromMarkingCode($markingCode);

					if (isset($itemsMap[$itemKey]))
					{
						$itemIndex = $itemsMap[$itemKey];
						$result['items'][$itemIndex]['instances'][] = [ 'cis' => $cis ];
					}
					else
					{
						$itemsMap[$itemKey] = $newIndex;
						$result['items'][$newIndex] = [
							'productId' => $storeItem['PRODUCT_ID'],
							'xmlId' => $storeItem['XML_ID'],
							'instances' => [
								[ 'cis' => $cis ],
							],
						];

						++$newIndex;
					}
				}

				return !empty($result['items']) ? $result : null;
			}
		];
	}

	protected function getEnvironmentItemsActions()
	{
		if (Market\Config::getOption('trading_silent_basket', 'N') === 'Y') { return null; }

		return [
			'FIELD' => 'BASKET.QUANTITY',
			'PATH' => 'send/items',
			'PAYLOAD' => static function(array $action) {
				$result = [
					'items' => [],
				];

				foreach ($action['VALUE'] as $basketItem)
				{
					$quantity = (float)$basketItem['VALUE'];

					if ($quantity <= 0) { continue; }

					$result['items'][] = [
						'productId' => $basketItem['PRODUCT_ID'],
						'xmlId' => $basketItem['XML_ID'],
						'count' => $quantity,
					];
				}

				return $result;
			}
		];
	}

	public function takeChanges(TradingService\Reference\Options\Skeleton $previous)
	{
		/** @var Options $previous */
		Market\Reference\Assert::typeOf($previous, static::class, 'previous');

		$this->takeProductChanges($previous);
	}

	protected function takeProductChanges(Options $previous)
	{
		if ($this->compareStoreChanges($previous) || $this->compareSkuChanges($previous))
		{
			$timestamp = new Main\Type\DateTime();

			$this->values['PRODUCT_UPDATED_AT'] = $timestamp->format(\DateTime::ATOM);
		}
	}

	protected function compareStoreChanges(Options $previous)
	{
		if ($previous->useWarehouses() !== $this->useWarehouses())
		{
			$changed = true;
		}
		else if ($this->useWarehouses())
		{
			$changed = $previous->getWarehouseStoreField() !== $this->getWarehouseStoreField();
		}
		else
		{
			$currentStores = $this->getProductStores();
			$previousStores = $previous->getProductStores();
			$newStores = array_diff($currentStores, $previousStores);
			$deletedStores = array_diff($previousStores, $currentStores);

			$changed = !empty($newStores) || !empty($deletedStores);
		}

		return $changed;
	}

	protected function compareSkuChanges(Options $previous)
	{
		$currentMap = $this->getProductSkuMap();
		$previousMap = $previous->getProductSkuMap();

		if (empty($currentMap) !== empty($previousMap))
		{
			$changed = true;
		}
		else if (!empty($previousMap))
		{
			$changed = false;

			foreach ($previousMap as $key => $previousLink)
			{
				$currentLink = isset($currentMap[$key])
					? $currentMap[$key]
					: null;

				if (
					$currentLink === null
					|| $currentLink['IBLOCK'] !== $previousLink['IBLOCK']
					|| $currentLink['FIELD'] !== $previousLink['FIELD']
				)
				{
					$changed = true;
					break;
				}
			}
		}
		else
		{
			$changed = false;
		}

		return $changed;
	}

	public function getTabs()
	{
		return [
			'COMMON' => [
				'name' => static::getLang('TRADING_SERVICE_MARKETPLACE_TAB_COMMON'),
				'sort' => 1000,
			],
			'STORE' => [
				'name' => static::getLang('TRADING_SERVICE_MARKETPLACE_TAB_STORE'),
				'sort' => 2000,
			],
			'STATUS' => [
				'name' => static::getLang('TRADING_SERVICE_MARKETPLACE_TAB_STATUS'),
				'sort' => 3000,
				'data' => [
					'WARNING' => static::getLang('TRADING_SERVICE_MARKETPLACE_TAB_STATUS_NOTE'),
				]
			],
		];
	}

	public function getFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		return
			$this->getCommonFields($environment, $siteId)
			+ $this->getCompanyFields($environment, $siteId)
			+ $this->getIncomingRequestFields($environment, $siteId)
			+ $this->getOauthRequestFields($environment, $siteId)
			+ $this->getOrderPersonFields($environment, $siteId)
			+ $this->getOrderPaySystemFields($environment, $siteId)
			+ $this->getOrderDeliveryFields($environment, $siteId)
			+ $this->getOrderBasketSubsidyFields($environment, $siteId)
			+ $this->getOrderPropertyUtilFields($environment, $siteId)
			+ $this->getProductSkuMapFields($environment, $siteId)
			+ $this->getProductStoreFields($environment, $siteId)
			+ $this->getProductPriceFields($environment, $siteId)
			+ $this->getProductSelfTestFields($environment, $siteId)
			+ $this->getStatusInFields($environment, $siteId)
			+ $this->getStatusOutFields($environment, $siteId);
	}

	protected function getPersonTypeDefaultValue(TradingEntity\Reference\PersonType $personType, $siteId)
	{
		return $personType->getLegalId($siteId);
	}

	protected function getOrderPersonFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		$result = parent::getOrderPersonFields($environment, $siteId);

		return $this->applyFieldsOverrides($result, [
			'GROUP' => static::getLang('TRADING_SERVICE_MARKETPLACE_GROUP_ORDER'),
			'GROUP_DESCRIPTION' => static::getLang('TRADING_SERVICE_MARKETPLACE_GROUP_ORDER_DESCRIPTION'),
			'SORT' => 3200,
		]);
	}

	protected function getOrderPaySystemFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		try
		{
			$paySystem = $environment->getPaySystem();
			$paySystemEnum = $paySystem->getEnum($siteId);
			$firstPaySystem = reset($paySystemEnum);

			$result = [
				'PAY_SYSTEM_ID' => [
					'TYPE' => 'enumeration',
					'MANDATORY' => $paySystem->isRequired() ? 'Y' : 'N',
					'NAME' => static::getLang('TRADING_SERVICE_MARKETPLACE_OPTION_PAY_SYSTEM_ID'),
					'GROUP' => static::getLang('TRADING_SERVICE_MARKETPLACE_GROUP_ORDER'),
					'GROUP_DESCRIPTION' => static::getLang('TRADING_SERVICE_MARKETPLACE_GROUP_ORDER_DESCRIPTION'),
					'VALUES' => $paySystemEnum,
					'SETTINGS' => [
						'DEFAULT_VALUE' => $firstPaySystem !== false ? $firstPaySystem['ID'] : null,
						'STYLE' => 'max-width: 220px;',
					],
					'SORT' => 3400,
				]
			];
		}
		catch (Market\Exceptions\NotImplemented $exception)
		{
			$result = [];
		}

		return $result;
	}

	protected function getOrderDeliveryFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		try
		{
			$delivery = $environment->getDelivery();
			$deliveryEnum = $delivery->getEnum($siteId);
			$defaultDelivery = null;
			$emptyDelivery = array_filter($deliveryEnum, function($option) {
				return $option['TYPE'] === Market\Data\Trading\Delivery::EMPTY_DELIVERY;
			});

			if (empty($emptyDelivery))
			{
				$firstEmptyDelivery = reset($emptyDelivery);
				$defaultDelivery = $firstEmptyDelivery['ID'];
			}
			else if (!empty($deliveryEnum))
			{
				$firstDelivery = reset($deliveryEnum);
				$defaultDelivery = $firstDelivery['ID'];
			}

			$result = [
				'DELIVERY_ID' => [
					'TYPE' => 'enumeration',
					'MANDATORY' => $delivery->isRequired() ? 'Y' : 'N',
					'NAME' => static::getLang('TRADING_SERVICE_MARKETPLACE_OPTION_DELIVERY_ID'),
					'GROUP' => static::getLang('TRADING_SERVICE_MARKETPLACE_GROUP_ORDER'),
					'GROUP_DESCRIPTION' => static::getLang('TRADING_SERVICE_MARKETPLACE_GROUP_ORDER_DESCRIPTION'),
					'VALUES' => $deliveryEnum,
					'SETTINGS' => [
						'DEFAULT_VALUE' => $defaultDelivery,
						'STYLE' => 'max-width: 220px;',
					],
					'SORT' => 3300,
				],
			];
		}
		catch (Market\Exceptions\NotImplemented $exception)
		{
			$result = [];
		}

		return $result;
	}

	protected function getOrderBasketSubsidyFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		try
		{
			$paySystem = $environment->getPaySystem();
			$paySystemEnum = $paySystem->getEnum($siteId);

			$result = [
				'BASKET_SUBSIDY_INCLUDE' => [
					'TYPE' => 'boolean',
					'NAME' => static::getLang('TRADING_SERVICE_MARKETPLACE_OPTION_BASKET_SUBSIDY_INCLUDE'),
					'SORT' => 3450,
				],
				'SUBSIDY_PAY_SYSTEM_ID' => [
					'TYPE' => 'enumeration',
					'NAME' => static::getLang('TRADING_SERVICE_MARKETPLACE_OPTION_SUBSIDY_PAY_SYSTEM_ID'),
					'HELP_MESSAGE' => static::getLang('TRADING_SERVICE_MARKETPLACE_OPTION_SUBSIDY_PAY_SYSTEM_ID_HELP'),
					'VALUES' => $paySystemEnum,
					'SETTINGS' => [
						'CAPTION_NO_VALUE' => static::getLang('TRADING_SERVICE_MARKETPLACE_OPTION_SUBSIDY_PAY_SYSTEM_ID_NO_VALUE'),
						'STYLE' => 'max-width: 220px;'
					],
					'SORT' => 3451,
					'DEPEND' => [
						'BASKET_SUBSIDY_INCLUDE' => [
							'RULE' => 'ANY',
							'VALUE' => Market\Ui\UserField\BooleanType::VALUE_Y,
						],
					],
				],
			];
		}
		catch (Market\Exceptions\NotImplemented $exception)
		{
			$result = [];
		}

		return $result;
	}

	protected function getOrderPropertyUtilFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		$result = parent::getOrderPropertyUtilFields($environment, $siteId);

		return $this->applyFieldsOverrides($result, [
			'GROUP' => static::getLang('TRADING_SERVICE_MARKETPLACE_GROUP_ORDER_PROPERTY'),
			'SORT' => 3500,
		]);
	}

	protected function getProductSkuMapFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		$result = parent::getProductSkuMapFields($environment, $siteId);
		$overridable = array_diff_key($result, [
			'PRODUCT_SKU_ADV_PREFIX' => true,
		]);

		return
			$this->applyFieldsOverrides($overridable, [ 'HIDDEN' => 'N' ])
			+ $result;
	}

	protected function getProductStoreFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		global $APPLICATION;

		try
		{
			$store = $environment->getStore();
			$supportsWarehouses = $this->provider->getFeature()->supportsWarehouses();

			$warehouseFields = [
				'USE_WAREHOUSES' => [
					'TYPE' => 'boolean',
					'TAB' => 'STORE',
					'NAME' => static::getLang('TRADING_SERVICE_MARKETPLACE_OPTION_USE_WAREHOUSES'),
					'HELP_MESSAGE' => static::getLang('TRADING_SERVICE_MARKETPLACE_OPTION_USE_WAREHOUSES_HELP'),
					'SORT' => 1100,
					'HIDDEN' => $supportsWarehouses ? 'N' : 'Y',
				],
				'WAREHOUSE_STORE_FIELD' => [
					'TYPE' => 'enumeration',
					'TAB' => 'STORE',
					'MANDATORY' => 'Y',
					'NAME' => static::getLang('TRADING_SERVICE_MARKETPLACE_OPTION_WAREHOUSE_STORE_FIELD'),
					'HELP_MESSAGE' => static::getLang('TRADING_SERVICE_MARKETPLACE_OPTION_WAREHOUSE_STORE_FIELD_HELP', [
						'#LANG#' => LANGUAGE_ID,
						'#BACKURL#' => rawurlencode($APPLICATION->GetCurPageParam('')),
					]),
					'SORT' => 1105,
					'VALUES' => $store->getFieldEnum($siteId),
					'HIDDEN' => $supportsWarehouses ? 'N' : 'Y',
					'SETTINGS' => [
						'DEFAULT_VALUE' => $store->getWarehouseDefaultField(),
						'STYLE' => 'max-width: 220px;',
					],
					'DEPEND' => [
						'USE_WAREHOUSES' => [
							'RULE' => 'EMPTY',
							'VALUE' => false,
						],
					],
				],
			];
			$commonFields = parent::getProductStoreFields($environment, $siteId);

			if ($supportsWarehouses)
			{
				foreach ($commonFields as &$commonField)
				{
					if (isset($commonField['INTRO']))
					{
						$warehouseFields['USE_WAREHOUSES']['INTRO'] = $commonField['INTRO'];
						unset($commonField['INTRO']);
					}

					$commonField['SORT'] += 5;
					$commonField['DEPEND'] = [
						'USE_WAREHOUSES' => [
							'RULE' => 'EMPTY',
							'VALUE' => true,
						],
					];
				}
				unset($commonField);
			}

			$result = $warehouseFields + $commonFields;
		}
		catch (Market\Exceptions\NotImplemented $exception)
		{
			$result = [];
		}

		return $result;

	}

	protected function getProductPriceFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		$result = parent::getProductPriceFields($environment, $siteId);

		if (!Market\Config::isExpertMode())
		{
			$result = $this->applyFieldsOverrides($result, [
				'HIDDEN' => 'Y',
			]);
		}

		return $result;
	}

	protected function getProductSelfTestFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		$result = [];
		$defaults = [
			'TAB' => 'STORE',
			'GROUP' => static::getLang('TRADING_SERVICE_MARKETPLACE_OPTION_SELF_TEST'),
			'SORT' => 2300,
		];

		foreach ($this->getSelfTestOption()->getFields($environment, $siteId) as $name => $field)
		{
			$key = sprintf('SELF_TEST[%s]', $name);

			$result[$key] = $field + $defaults;
		}

		return $result;
	}

	protected function getFieldsetMap()
	{
		return [
			'SELF_TEST' => Options\SelfTestOption::class,
		];
	}
}