<?php

namespace Yandex\Market\Trading\Entity\Reference;

use Yandex\Market;
use Bitrix\Main;

abstract class Price
{
	protected $environment;

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * ����� ���������� ���
	 *
	 * @return array{ID: string, VALUE: string}[]
	 */
	public function getSourceEnum()
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'getSourceEnum');
	}

	/**
	 * ����� ����� ���
	 *
	 * @return array{ID: string, VALUE: string}[]
	 */
	public function getTypeEnum()
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'getTypeEnum');
	}

	/**
	 * ���� ��� �� ��������� ��� ����� �������������
	 *
	 * @param int[]|null $userGroups
	 *
	 * @return string[]
	 */
	public function getTypeDefaults(array $userGroups = null)
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'getTypeDefaults');
	}

	/**
	 * ������ �� ����� ������� ��� ������������ �������
	 *
	 * @param int[] $productIds
	 * @param array<int, float[]>|null $quantities
	 * @param array $context
	 *
	 * @return array<int|string, array>
	 */
	public function getBasketData($productIds, $quantities = null, array $context = [])
	{
		return [];
	}
}