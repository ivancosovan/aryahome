<?php

namespace Yandex\Market\Component\TradingOrder;

use Bitrix\Main;
use Yandex\Market;

class GridList extends Market\Component\Base\GridList
{
	use Market\Reference\Concerns\HasLang;

	protected $orderFields;
	protected $setup;

	protected static function includeMessages()
	{
		Main\Localization\Loc::loadMessages(__FILE__);
	}

	public function processAjaxAction($action, $data)
	{
		if ($action === 'boxes')
		{
			$count = $this->getAjaxActionBoxesCount();

			$this->processOrderAction($data, 'sendBoxes', $count);
		}
		else
		{
			parent::processAjaxAction($action, $data);
		}
	}

	protected function getAjaxActionBoxesCount()
	{
		if (!isset($_REQUEST['boxes']))
		{
			throw new Main\ArgumentException('boxes count is missing');
		}

		return (string)$_REQUEST['boxes'];
	}

	protected function processOrderAction($actionData, $method, $payload)
	{
		$errorMessages = [];
		$hasSuccess = false;

		foreach ($this->getActionSelectedIds($actionData) as $externalId)
		{
			$sendResult = $this->{$method}($externalId, $payload);

			if ($sendResult->isSuccess())
			{
				$hasSuccess = true;
			}
			else
			{
				$errorMessages[] = implode('<br />', $sendResult->getErrorMessages());
			}
		}

		if ($hasSuccess)
		{
			Market\Trading\State\SessionCache::releaseByType('order');
		}

		if (!empty($errorMessages))
		{
			throw new Main\SystemException(implode('<br />', $errorMessages));
		}
	}

	protected function getActionSelectedIds($data)
	{
		if (!empty($data['IS_ALL']))
		{
			throw new Main\NotSupportedException();
		}

		return (array)$data['ID'];
	}

	protected function sendBoxes($externalId, $count)
	{
		$result = new Main\Result();

		try
		{
			$setup = $this->getSetup();
			$accountNumber = $this->getOrderNumber($externalId);
			$shipmentId = $this->getOrderShipmentId($externalId, $accountNumber);

			$procedure = new Market\Trading\Procedure\Runner(
				Market\Trading\Entity\Registry::ENTITY_TYPE_ORDER,
				$accountNumber
			);

			$procedure->run($setup, 'send/boxes', [
				'orderId' => $externalId,
				'orderNum' => $accountNumber,
				'shipmentId' => $shipmentId,
				'boxes' => $this->makeBoxes($externalId, $count),
			]);
		}
		catch (Main\SystemException $exception)
		{
			$exceptionMessage = $exception->getMessage();
			$message = static::getLang('COMPONENT_TRADING_ORDER_LIST_ORDER_ACTION_FAILED', [
				'#ORDER_ID#' => $externalId,
				'#MESSAGE#' => $exceptionMessage,
			], $exceptionMessage);

			$result->addError(new Main\Error($message));
		}

		return $result;
	}

	protected function getOrderNumber($externalId, $useAccountNumber = null)
	{
		$setup = $this->getSetup();
		$platform = $setup->getPlatform();
		$orderRegistry = $setup->getEnvironment()->getOrderRegistry();

		return $orderRegistry->search($externalId, $platform, $useAccountNumber);
	}

	protected function getOrderShipmentId($externalId, $accountNumber)
	{
		return
			$this->getOrderShipmentIdFromData($externalId)
			?: $this->getOrderShipmentIdFromAction($externalId, $accountNumber);
	}

	protected function getOrderShipmentIdFromData($externalId)
	{
		$uniqueKey = $this->getSetup()->getService()->getUniqueKey();

		return Market\Trading\State\OrderData::getValue($uniqueKey, $externalId, 'SHIPMENT_ID');
	}

	protected function getOrderShipmentIdFromAction($externalId, $accountNumber)
	{
		$setup = $this->getSetup();
		$procedure = new Market\Trading\Procedure\Runner(
			Market\Trading\Entity\Registry::ENTITY_TYPE_ORDER,
			$accountNumber
		);

		$response = $procedure->run($setup, 'admin/view', [
			'id' => $externalId,
			'useCache' => true,
		]);

		$shipments = $response->getField('shipments');
		$shipment = is_array($shipments) ? reset($shipments) : false;

		return isset($shipment['ID']) ? $shipment['ID'] : null;
	}

	protected function makeBoxes($externalId, $count)
	{
		$result = [];

		for ($index = 1; $index <= $count; ++$index)
		{
			$result[] = [
				'fulfilmentId' => $externalId . '-' . $index,
			];
		}

		return $result;
	}

	public function getFields(array $select = [])
	{
		$result = $this->getOrderFields();

		if (!empty($select))
		{
			$selectMap = array_flip($select);
			$result = array_intersect_key($result, $selectMap);
		}

		return $result;
	}

	protected function getOrderFields()
	{
		if ($this->orderFields === null)
		{
			$fields = $this->loadOrderFields();
			$fields = $this->filterSupportsFields($fields);

			$this->orderFields = $fields;
		}

		return $this->orderFields;
	}

	protected function loadOrderFields()
	{
		return $this->makeFields([
			'ID' => [
				'TYPE' => 'primary',
				'NAME' => static::getLang('COMPONENT_TRADING_ORDER_LIST_FIELD_ID', [
					'#SERVICE_NAME#' => $this->getSetup()->getService()->getInfo()->getTitle('DATIVE'),
				]),
				'SORTABLE' => false,
				'SETTINGS' => [
					'URL_FIELD' => 'SERVICE_URL',
				],
			],
			'ORDER_ID' => [
				'TYPE' => 'primary',
				'SORTABLE' => false,
				'SETTINGS' => [
					'URL_FIELD' => 'EDIT_URL',
				],
			],
			'ACCOUNT_NUMBER' => [
				'TYPE' => 'primary',
				'SORTABLE' => false,
				'SETTINGS' => [
					'URL_FIELD' => 'EDIT_URL',
				],
			],
			'DATE_CREATE' => [
				'TYPE' => 'datetime',
				'SORTABLE' => false,
			],
			'DATE_SHIPMENT' => [
				'TYPE' => 'datetime',
				'MULTIPLE' => 'Y',
				'SORTABLE' => false,
			],
			'DATE_DELIVERY' => [
				'TYPE' => 'dateTimePeriod',
				'SORTABLE' => false,
			],
			'BASKET' => [
				'TYPE' => 'tradingOrderItem',
				'MULTIPLE' => 'Y',
				'FILTERABLE' => false,
				'SORTABLE' => false,
			],
			'BOX_COUNT' => [
				'TYPE' => 'number',
				'FILTERABLE' => false,
				'SORTABLE' => false,
				'SETTINGS' => [
					'UNIT' => static::getLang('COMPONENT_TRADING_ORDER_LIST_FIELD_BOX_COUNT_UNIT'),
				],
				'SUPPORTS' => [
					Market\Trading\Service\Manager::SERVICE_MARKETPLACE . ':' . Market\Trading\Service\Manager::BEHAVIOR_DEFAULT,
				],
			],
			'TOTAL' => [
				'TYPE' => 'price',
				'FILTERABLE' => false,
				'SORTABLE' => false,
			],
			'SUBSIDY' => [
				'TYPE' => 'price',
				'FILTERABLE' => false,
				'SORTABLE' => false,
			],
			'STATUS' => [
				'TYPE' => 'enumeration',
				'SELECTABLE' => false,
				'SORTABLE' => false,
				'VALUES' => $this->getStatusEnum(),
			],
			'STATUS_LANG' => [
				'TYPE' => 'string',
				'FILTERABLE' => false,
				'SORTABLE' => false,
			],
			'FAKE' => [
				'TYPE' => 'boolean',
				'SORTABLE' => false,
			],
			'WAIT_CANCELLATION_APPROVE' => [
				'TYPE' => 'boolean',
				'SORTABLE' => false,
				'SELECTABLE' => false,
				'SUPPORTS' => [
					Market\Trading\Service\Manager::SERVICE_MARKETPLACE . ':' . Market\Trading\Service\Manager::BEHAVIOR_DBS,
				],
			],
		]);
	}

	protected function filterSupportsFields(array $fields)
	{
		$setup = $this->getSetup();
		$match = [
			$setup->getServiceCode(),
			$setup->getServiceCode() . ':' . $setup->getBehaviorCode(),
		];

		foreach ($fields as $key => $field)
		{
			if (!isset($field['SUPPORTS'])) { continue; }

			$intersect = array_intersect((array)$field['SUPPORTS'], $match);

			if (empty($intersect))
			{
				unset($fields[$key]);
			}
		}

		return $fields;
	}

	protected function getStatusEnum()
	{
		$serviceStatus = $this->getSetup()->getService()->getStatus();
		$result = [];

		foreach ($serviceStatus->getVariants() as $status)
		{
			$result[] = [
				'ID' => $status,
				'VALUE' => $serviceStatus->getTitle($status, 'SHORT'),
			];
		}

		return $result;
	}

	protected function makeFields($fields)
	{
		$result = [];

		foreach ($fields as $name => $field)
		{
			$userField = $field;
			$fieldTitle = isset($field['NAME'])
				? $field['NAME']
				: static::getLang('COMPONENT_TRADING_ORDER_LIST_FIELD_' . $name);

			if (!isset($field['USER_TYPE']) && isset($field['TYPE']))
			{
				$userField['USER_TYPE'] = Market\Ui\UserField\Manager::getUserType($field['TYPE']);
			}

			$userField += [
				'MULTIPLE' => 'N',
				'EDIT_IN_LIST' => 'Y',
				'EDIT_FORM_LABEL' => $fieldTitle,
				'FIELD_NAME' => $name,
				'SETTINGS' => [],
			];

			$result[$name] = $userField;
		}

		return $result;
	}

	public function load(array $queryParameters = [])
	{
		$procedure = new Market\Trading\Procedure\Runner(Market\Trading\Entity\Registry::ENTITY_TYPE_ORDER, null);
		$setup = $this->getSetup();
		$service = $setup->wakeupService();
		$logger = $service->getLogger();

		$fetchParameters =
			$this->convertQueryToFetchParameters($queryParameters)
			+ $this->getDefaultFetchParameters()
			+ $this->getEnvironmentFetchParameters();

		$this->configureLogger($logger);

		$response = $procedure->run($setup, 'admin/list', $fetchParameters);

		$orders = $response->getField('orders');
		$totalCount = $response->getField('totalCount');

		return [
			'ITEMS' => $this->extendItems($orders),
			'TOTAL_COUNT' => $totalCount,
		];
	}

	protected function configureLogger($logger)
	{
		if ($logger instanceof Market\Logger\Reference\Logger)
		{
			$logger->setLevel(Market\Logger\Level::ERROR);
		}
	}

	protected function extendItems($items)
	{
		foreach ($items as &$item)
		{
			if (empty($item['ORDER_ID']))
			{
				$item['DISABLED'] = true;
			}
		}
		unset($item);

		return $items;
	}

	protected function convertQueryToFetchParameters($queryParameters)
	{
		$result = [];

		if (isset($queryParameters['limit'], $queryParameters['offset']))
		{
			$result['pageSize'] = $queryParameters['limit'];
			$result['page'] = floor($queryParameters['offset'] / $queryParameters['limit']) + 1;
		}

		if (isset($queryParameters['filter']))
		{
			foreach ($queryParameters['filter'] as $key => $value)
			{
				switch ($key)
				{
					case 'STATUS':
						$result['status'] = $value;
					break;

					case '>=DATE_CREATE':
						$result['fromDate'] = new Main\Type\DateTime($value);
					break;

					case '<=DATE_CREATE':
						$result['toDate'] = new Main\Type\DateTime($value);
					break;

					case '>=DATE_SHIPMENT':
						$result['fromShipmentDate'] = new Main\Type\Date($value);
					break;

					case '<=DATE_SHIPMENT':
						$result['toShipmentDate'] = new Main\Type\Date($value);
					break;

					case 'FAKE':
						$result['fake'] = ((string)$value === '1');
					break;

					case 'ID':
						$ids = $this->searchExternalIds($value, 'EXTERNAL_ORDER_ID') ?: (array)$value;

						$result['id'] = isset($result['id']) ? array_intersect($result['id'], $ids) : $ids;
					break;

					case 'ORDER_ID':
						$ids = $this->searchExternalIds($value, 'ORDER_ID');

						$result['id'] = isset($result['id']) ? array_intersect($result['id'], $ids) : $ids;
					break;

					case 'ACCOUNT_NUMBER':
						$ids =
							$this->searchExternalIds($value, 'ACCOUNT_NUMBER')
							?: $this->searchExternalIds($value, 'ORDER_ID');

						$result['id'] = isset($result['id']) ? array_intersect($result['id'], $ids) : $ids;
					break;

					case 'WAIT_CANCELLATION_APPROVE':
						$result['onlyWaitingForCancellationApprove'] = ((string)$value === '1');
					break;
				}
			}
		}

		return $result;
	}

	protected function searchExternalIds($value, $field)
	{
		$orderRegistry = $this->getSetup()->getEnvironment()->getOrderRegistry();
		$platform = $this->getSetup()->getPlatform();

		return $orderRegistry->suggestExternalIds($value, $field, $platform);
	}

	protected function getDefaultFetchParameters()
	{
		$isLoadMoreAction = $this->isLoadMoreAction();

		return [
			'flushCache' => !$isLoadMoreAction,
			'useCache' => true,
			'suppressErrors' => true,
		];
	}

	protected function getEnvironmentFetchParameters()
	{
		global $USER;

		$accessParameter = $this->getComponentParam('CHECK_ACCESS');

		return [
			'userId' => $USER->GetID(),
			'checkAccess' => isset($accessParameter) ? (bool)$accessParameter : true,
		];
	}

	protected function isLoadMoreAction()
	{
		return $_REQUEST['mode'] === 'loadMore';
	}

	public function loadTotalCount(array $queryParameters = [])
	{
		return null;
	}

	public function filterActions($item, $actions)
	{
		foreach ($actions as $actionIndex => &$action)
		{
			if (empty($item['ORDER_ID']))
			{
				$isValid = false;
			}
			else if ($action['TYPE'] === 'EDIT')
			{
				$isValid = isset($item['EDIT_URL']);
			}
			else if ($action['TYPE'] === 'PRINT')
			{
				$isValid = !empty($item['PRINT_READY']);
			}
			else
			{
				$isValid = $this->matchActionFilter($action, $item);

				if ($isValid && isset($action['MENU']))
				{
					foreach ($action['MENU'] as $menuKey => $menuAction)
					{
						if (!$this->matchActionFilter($menuAction, $item))
						{
							unset($action['MENU'][$menuKey]);
						}
					}

					$action['MENU'] = array_values($action['MENU']);

					if (empty($action['MENU']))
					{
						$isValid = false;
					}
				}
			}

			if (!$isValid)
			{
				unset($actions[$actionIndex]);
			}
		}
		unset($action);

		return $actions;
	}

	protected function matchActionFilter($action, $item)
	{
		if (!isset($action['FILTER']) || !is_array($action['FILTER'])) { return true; }

		$result = true;

		foreach ($action['FILTER'] as $field => $condition)
		{
			if (Market\Data\TextString::getPosition($field, '!') === 0)
			{
				$field = Market\Data\TextString::getSubstring($field, 1);
				$inverse = true;
			}
			else
			{
				$inverse = false;
			}

			$value = isset($item[$field]) ? $item[$field] : null;

			$isConditionIterable = is_array($condition);
			$isValueIterable = is_array($value);

			if ($isConditionIterable || $isValueIterable)
			{
				$conditionIterable = $isConditionIterable ? $condition : [ $condition ];
				$valueIterable = $isValueIterable ? $value : [ $value ];

				$match = (count(array_intersect($conditionIterable, $valueIterable)) > 0);
			}
			else
			{
				$match = (string)$condition === (string)$value;
			}

			if ($inverse) { $match = !$match; }

			if (!$match)
			{
				$result = false;
				break;
			}
		}

		return $result;
	}

	public function getSetup()
	{
		if ($this->setup === null)
		{
			$this->setup = $this->loadSetup();
		}

		return $this->setup;
	}

	protected function loadSetup()
	{
		$setupId = (int)$this->getComponentParam('SETUP_ID');

		return Market\Trading\Setup\Model::loadById($setupId);
	}

	public function getRequiredParams()
	{
		return [
			'SETUP_ID',
		];
	}

	public function getDefaultFilter()
	{
		return [];
	}

	public function getDefaultSort()
	{
		return [];
	}

	public function deleteItem($id)
	{
		throw new Main\NotSupportedException();
	}
}