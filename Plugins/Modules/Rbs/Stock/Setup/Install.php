<?php
/**
 * Copyright (C) 2014 Ready Business System
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Rbs\Stock\Setup;

/**
 * @name \Rbs\Stock\Setup\Install
 */
class Install extends \Change\Plugins\InstallBase
{
	/**
	 * @param \Change\Plugins\Plugin $plugin
	 * @param \Change\Application $application
	 * @param \Change\Configuration\EditableConfiguration $configuration
	 * @throws \RuntimeException
	 */
	public function executeApplication($plugin, $application, $configuration)
	{
		$configuration->addPersistentEntry('Rbs/Stock/disableReservation', false);
		$configuration->addPersistentEntry('Rbs/Stock/disableMovement', false);
	}

	/**
	 * @param \Change\Plugins\Plugin $plugin
	 * @param \Change\Db\InterfaceSchemaManager $schemaManager
	 * @throws \RuntimeException
	 */
	public function executeDbSchema($plugin, $schemaManager)
	{
		$schema = new Schema($schemaManager);
		$schema->generate();
	}

	/**
	 * @param \Change\Plugins\Plugin $plugin
	 * @param \Change\Services\ApplicationServices $applicationServices
	 * @throws \Exception
	 */
	public function executeServices($plugin, $applicationServices)
	{
		$cm = $applicationServices->getCollectionManager();
		if ($cm->getCollection('Rbs_Stock_Collection_Unit') === null)
		{
			$tm = $applicationServices->getTransactionManager();
			try
			{
				$tm->begin();
				/* @var $collection \Rbs\Collection\Documents\Collection */
				$collection = $applicationServices->getDocumentManager()->getNewDocumentInstanceByModelName('Rbs_Collection_Collection');

				$item = $collection->newCollectionItem();
				$item->setValue('PC');
				$item->setLabel('pc.');
				$item->getCurrentLocalization()->setTitle($applicationServices->getI18nManager()->trans('m.rbs.stock.setup.unit_piece', array('ucf')));
				$item->setLocked(true);

				$collection->setLabel('SKU Units');
				$collection->setCode('Rbs_Stock_Collection_Unit');
				$collection->setLocked(true);
				$collection->getItems()->add($item);
				$collection->save();
				$tm->commit();
			}
			catch (\Exception $e)
			{
				throw $tm->rollBack($e);
			}
		}

		if ($cm->getCollection('Rbs_Stock_Collection_Threshold') === null)
		{
			$tm = $applicationServices->getTransactionManager();
			try
			{
				$tm->begin();
				/* @var $collection \Rbs\Collection\Documents\Collection */
				$collection = $applicationServices->getDocumentManager()->getNewDocumentInstanceByModelName('Rbs_Collection_Collection');
				$collection->setLabel('SKU Threshold');
				$collection->setCode('Rbs_Stock_Collection_Threshold');
				$collection->setLocked(true);


				$item = $collection->newCollectionItem();
				$item->setValue(\Rbs\Stock\StockManager::THRESHOLD_AVAILABLE);
				$item->setLabel('Available');
				$item->getCurrentLocalization()->setTitle($applicationServices->getI18nManager()->trans('m.rbs.stock.setup.sku_threshold_available', array('ucf')));
				$item->setLocked(true);
				$collection->getItems()->add($item);

				$item = $collection->newCollectionItem();
				$item->setValue('LOW');
				$item->setLabel('Low');
				$item->getCurrentLocalization()->setTitle($applicationServices->getI18nManager()->trans('m.rbs.stock.setup.sku_threshold_low', array('ucf')));
				$collection->getItems()->add($item);

				$item = $collection->newCollectionItem();
				$item->setValue(\Rbs\Stock\StockManager::THRESHOLD_UNAVAILABLE);
				$item->setLabel('Unavailable');
				$item->getCurrentLocalization()->setTitle($applicationServices->getI18nManager()->trans('m.rbs.stock.setup.sku_threshold_unavailable', array('ucf')));
				$item->setLocked(true);
				$collection->getItems()->add($item);

				$collection->create();
				$tm->commit();
			}
			catch (\Exception $e)
			{
				throw $tm->rollBack($e);
			}
		}
	}

	/**
	 * @param \Change\Plugins\Plugin $plugin
	 */
	public function finalize($plugin)
	{
		$plugin->setConfigurationEntry('locked', true);
	}
}
