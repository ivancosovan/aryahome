<?php

namespace Yandex\Market\Trading\Service\Marketplace\Action\Stocks;

use Yandex\Market;
use Bitrix\Main;
use Yandex\Market\Trading\Entity as TradingEntity;
use Yandex\Market\Trading\Service as TradingService;

class Action extends TradingService\Common\Action\HttpAction
{
	use Market\Reference\Concerns\HasMessage;

	/** @var TradingService\Marketplace\Provider */
	protected $provider;
	/** @var Request */
	protected $request;

	public function __construct(TradingService\Marketplace\Provider $provider, TradingEntity\Reference\Environment $environment, Main\HttpRequest $request, Main\Server $server)
	{
		parent::__construct($provider, $environment, $request, $server);
	}

	protected function createRequest(Main\HttpRequest $request, Main\Server $server)
	{
		return new Request($request, $server);
	}

	public function process()
	{
		$stores = $this->getStores();
		$offerMap = $this->getOfferMap();
		$productIds = $offerMap !== null ? array_values($offerMap) : $this->request->getSkus();
		$productAmounts = $this->loadAmounts($stores, $productIds);
		$productAmounts = $this->applySettingsUpdated($productAmounts);

		$this->collectSku($productAmounts, $offerMap);
	}

	protected function getStores()
	{
		$options = $this->provider->getOptions();

		if ($options->useWarehouses())
		{
			$field = $options->getWarehouseStoreField();
			$warehouseId = $this->request->getPartnerWarehouseId();
			$result = $this->environment->getStore()->findStores($field, $warehouseId);

			if (empty($result))
			{
				$message = self::getMessage('CANT_FIND_WAREHOUSE_STORE', [
					'#CODE#' => $warehouseId,
				]);

				throw new Market\Exceptions\Api\InvalidOperation($message);
			}
		}
		else
		{
			$result = $this->provider->getOptions()->getProductStores();
		}

		return $result;
	}

	protected function getOfferMap()
	{
		$offerIds = $this->request->getSkus();
		$command = new TradingService\Common\Command\OfferMap(
			$this->provider,
			$this->environment
		);

		return $command->make($offerIds);
	}

	protected function getProductId($offerId, $offerMap)
	{
		$result = null;

		if ($offerMap === null)
		{
			$result = $offerId;
		}
		else if (isset($offerMap[$offerId]))
		{
			$result = $offerMap[$offerId];
		}

		return $result;
	}

	protected function loadAmounts($stores, $productIds)
	{
		$store = $this->environment->getStore();

		return $store->getAmounts($stores, $productIds);
	}

	protected function applySettingsUpdated($productAmounts)
	{
		$updated = $this->provider->getOptions()->productUpdatedAt();

		if ($updated === null) { return $productAmounts; }

		foreach ($productAmounts as &$productAmount)
		{
			$productAmount['TIMESTAMP_X'] = Market\Data\DateTime::max($productAmount['TIMESTAMP_X'], $updated);
		}
		unset($productAmount);

		return $productAmounts;
	}

	protected function collectSku($productAmounts, $offerMap)
	{
		$skus = [];
		$requestWarehouseId = $this->request->getWarehouseId();
		$productMap = ($offerMap !== null ? array_flip($offerMap) : null);

		foreach ($productAmounts as $productAmount)
		{
			$productId = $productAmount['ID'];
			$offerId = $this->getOfferId($productId, $productMap);

			if ($offerId !== null)
			{
				$updatedAt = Market\Data\Date::convertForService($productAmount['TIMESTAMP_X']);
				$skuItem = [
					'sku' => (string)$offerId,
					'warehouseId' => $requestWarehouseId,
					'items' => []
				];

				if (isset($productAmount['QUANTITY_LIST']))
				{
					foreach ($productAmount['QUANTITY_LIST'] as $type => $quantity)
					{
						$skuItem['items'][] = [
							'type' => $type,
							'count' => (string)$this->normalizeItemCount($quantity),
							'updatedAt' => $updatedAt
						];
					}
				}
				else if (isset($productAmount['QUANTITY']))
				{
					$skuItem['items'][] = [
						'type' => Market\Data\Trading\Stocks::TYPE_FIT,
						'count' => (string)$this->normalizeItemCount($productAmount['QUANTITY']),
						'updatedAt' => $updatedAt
					];
				}

				$skus[] = $skuItem;
			}
		}

		$this->response->setField('skus', $skus);
	}

	protected function normalizeItemCount($count)
	{
		return max(0, (int)$count);
	}

	protected function getOfferId($productId, $productMap)
	{
		$result = null;

		if ($productMap === null)
		{
			$result = $productId;
		}
		else if (isset($productMap[$productId]))
		{
			$result = $productMap[$productId];
		}

		return $result;
	}
}