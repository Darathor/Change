<?php
namespace ChangeTests\Plugins\Modules\Stock\Documents;

use Rbs\Stock\Documents\Sku;

class SkuTest extends \ChangeTests\Change\TestAssets\TestCase
{
	public static function setUpBeforeClass()
	{
		$appServices = static::initDocumentsDb();
		$schema = new \Rbs\Stock\Setup\Schema($appServices->getDbProvider()->getSchemaManager());
		$schema->generate();
		$appServices->getDbProvider()->closeConnection();
	}

	public static function tearDownAfterClass()
	{
		static::clearDB();
	}

	protected function attachSharedListener(\Zend\EventManager\SharedEventManager $sharedEventManager)
	{
		parent::attachSharedListener($sharedEventManager);
		$this->attachCommerceServicesSharedListener($sharedEventManager);
	}

	protected function setUp()
	{
		parent::setUp();
		$this->initServices($this->getApplication());
	}

	public function testGetSetMass()
	{
		$sku = $this->getApplicationServices()->getDocumentManager()->getNewDocumentInstanceByModelName('Rbs_Stock_Sku');
		/* @var $sku Sku */
		$sku->setMass(1.2);
		$this->assertEquals(1.2, $sku->getMass());
		$this->assertEquals(1.2, $sku->getMass(Sku::UNIT_MASS_KG));
		$this->assertEquals(1200, $sku->getMass(Sku::UNIT_MASS_G));
		$this->assertEquals(2.6455471462, $sku->getMass(Sku::UNIT_MASS_LBS));

		$sku->setMass(1.2, Sku::UNIT_MASS_KG);
		$this->assertEquals(1.2, $sku->getMass());
		$this->assertEquals(1.2, $sku->getMass(Sku::UNIT_MASS_KG));
		$this->assertEquals(1200, $sku->getMass(Sku::UNIT_MASS_G));
		$this->assertEquals(2.6455471462, $sku->getMass(Sku::UNIT_MASS_LBS));

		$sku->setMass(120, Sku::UNIT_MASS_G);
		$this->assertEquals(0.12, $sku->getMass());
		$this->assertEquals(0.12, $sku->getMass(Sku::UNIT_MASS_KG));
		$this->assertEquals(120, $sku->getMass(Sku::UNIT_MASS_G));
		$this->assertEquals(0.26455471462, $sku->getMass(Sku::UNIT_MASS_LBS));

		$sku->setMass(0.026455471462, Sku::UNIT_MASS_LBS);
		$this->assertEquals(0.012, $sku->getMass());
		$this->assertEquals(0.012, $sku->getMass(Sku::UNIT_MASS_KG));
		$this->assertEquals(12, $sku->getMass(Sku::UNIT_MASS_G));
		$this->assertEquals(0.026455471462, $sku->getMass(Sku::UNIT_MASS_LBS));

		$sku->setMass(1);
		$sku->setMass('1');
		$this->setExpectedException('InvalidArgumentException');
		$sku->setMass('a');
	}

	public function testGetSetLength()
	{
		$sku = $this->getApplicationServices()->getDocumentManager()->getNewDocumentInstanceByModelName('Rbs_Stock_Sku');
		/* @var $sku Sku */
		$sku->setLength(1);
		$this->assertEquals(1, $sku->getLength());
		$this->assertEquals(1, $sku->getLength(Sku::UNIT_LENGTH_M));
		$this->assertEquals(100, $sku->getLength(Sku::UNIT_LENGTH_CM));
		$this->assertEquals(39.370078740157, $sku->getLength(Sku::UNIT_LENGTH_INCH));

		$sku->setLength(1, Sku::UNIT_LENGTH_M);
		$this->assertEquals(1, $sku->getLength());
		$this->assertEquals(1, $sku->getLength(Sku::UNIT_LENGTH_M));
		$this->assertEquals(100, $sku->getLength(Sku::UNIT_LENGTH_CM));
		$this->assertEquals(39.370078740157, $sku->getLength(Sku::UNIT_LENGTH_INCH));

		$sku->setLength(10, Sku::UNIT_LENGTH_CM);
		$this->assertEquals(0.1, $sku->getLength());
		$this->assertEquals(0.1, $sku->getLength(Sku::UNIT_LENGTH_M));
		$this->assertEquals(10, $sku->getLength(Sku::UNIT_LENGTH_CM));
		$this->assertEquals(3.9370078740157, $sku->getLength(Sku::UNIT_LENGTH_INCH));

		$sku->setLength(0.39370078740157, Sku::UNIT_LENGTH_INCH);
		$this->assertEquals(0.01, $sku->getLength());
		$this->assertEquals(0.01, $sku->getLength(Sku::UNIT_LENGTH_M));
		$this->assertEquals(1, $sku->getLength(Sku::UNIT_LENGTH_CM));
		$this->assertEquals(0.39370078740157, $sku->getLength(Sku::UNIT_LENGTH_INCH));

		$sku->setLength(1);
		$sku->setLength('1');
		$this->setExpectedException('InvalidArgumentException');
		$sku->setLength('a');
	}

	public function testGetSetWidth()
	{
		$sku = $this->getApplicationServices()->getDocumentManager()->getNewDocumentInstanceByModelName('Rbs_Stock_Sku');
		/* @var $sku Sku */
		$sku->setWidth(1);
		$this->assertEquals(1, $sku->getWidth());
		$this->assertEquals(1, $sku->getWidth(Sku::UNIT_LENGTH_M));
		$this->assertEquals(100, $sku->getWidth(Sku::UNIT_LENGTH_CM));
		$this->assertEquals(39.370078740157, $sku->getWidth(Sku::UNIT_LENGTH_INCH));

		$sku->setWidth(1, Sku::UNIT_LENGTH_M);
		$this->assertEquals(1, $sku->getWidth());
		$this->assertEquals(1, $sku->getWidth(Sku::UNIT_LENGTH_M));
		$this->assertEquals(100, $sku->getWidth(Sku::UNIT_LENGTH_CM));
		$this->assertEquals(39.370078740157, $sku->getWidth(Sku::UNIT_LENGTH_INCH));

		$sku->setWidth(10, Sku::UNIT_LENGTH_CM);
		$this->assertEquals(0.1, $sku->getWidth());
		$this->assertEquals(0.1, $sku->getWidth(Sku::UNIT_LENGTH_M));
		$this->assertEquals(10, $sku->getWidth(Sku::UNIT_LENGTH_CM));
		$this->assertEquals(3.9370078740157, $sku->getWidth(Sku::UNIT_LENGTH_INCH));

		$sku->setWidth(0.39370078740157, Sku::UNIT_LENGTH_INCH);
		$this->assertEquals(0.01, $sku->getWidth());
		$this->assertEquals(0.01, $sku->getWidth(Sku::UNIT_LENGTH_M));
		$this->assertEquals(1, $sku->getWidth(Sku::UNIT_LENGTH_CM));
		$this->assertEquals(0.39370078740157, $sku->getWidth(Sku::UNIT_LENGTH_INCH));

		$sku->setWidth(1);
		$sku->setWidth('1');
		$this->setExpectedException('InvalidArgumentException');
		$sku->setWidth('a');
	}

	public function testGetSetHeight()
	{
		$sku = $this->getApplicationServices()->getDocumentManager()->getNewDocumentInstanceByModelName('Rbs_Stock_Sku');
		/* @var $sku Sku */
		$sku->setHeight(1);
		$this->assertEquals(1, $sku->getHeight());
		$this->assertEquals(1, $sku->getHeight(Sku::UNIT_LENGTH_M));
		$this->assertEquals(100, $sku->getHeight(Sku::UNIT_LENGTH_CM));
		$this->assertEquals(39.370078740157, $sku->getHeight(Sku::UNIT_LENGTH_INCH));

		$sku->setHeight(1, Sku::UNIT_LENGTH_M);
		$this->assertEquals(1, $sku->getHeight());
		$this->assertEquals(1, $sku->getHeight(Sku::UNIT_LENGTH_M));
		$this->assertEquals(100, $sku->getHeight(Sku::UNIT_LENGTH_CM));
		$this->assertEquals(39.370078740157, $sku->getHeight(Sku::UNIT_LENGTH_INCH));

		$sku->setHeight(10, Sku::UNIT_LENGTH_CM);
		$this->assertEquals(0.1, $sku->getHeight());
		$this->assertEquals(0.1, $sku->getHeight(Sku::UNIT_LENGTH_M));
		$this->assertEquals(10, $sku->getHeight(Sku::UNIT_LENGTH_CM));
		$this->assertEquals(3.9370078740157, $sku->getHeight(Sku::UNIT_LENGTH_INCH));

		$sku->setHeight(0.39370078740157, Sku::UNIT_LENGTH_INCH);
		$this->assertEquals(0.01, $sku->getHeight());
		$this->assertEquals(0.01, $sku->getHeight(Sku::UNIT_LENGTH_M));
		$this->assertEquals(1, $sku->getHeight(Sku::UNIT_LENGTH_CM));
		$this->assertEquals(0.39370078740157, $sku->getHeight(Sku::UNIT_LENGTH_INCH));

		$sku->setHeight(1);
		$sku->setHeight('1');
		$this->setExpectedException('InvalidArgumentException');
		$sku->setHeight('a');
	}

	public function testGetSetLabel()
	{
		/** @var $sku \Rbs\Stock\Documents\Sku */
		$sku = $this->getApplicationServices()->getDocumentManager()->getNewDocumentInstanceByModelName('Rbs_Stock_Sku');
		$sku->setLabel('test');
		$this->assertEquals('test', $sku->getLabel());
		$this->assertEquals('test', $sku->getCode());

		$sku->setCode('toto');
		$this->assertEquals('toto', $sku->getLabel());
		$this->assertEquals('toto', $sku->getCode());
	}

	public function testCodeUnicity()
	{
		/** @var $sku \Rbs\Stock\Documents\Sku */
		$sku = $this->getApplicationServices()->getDocumentManager()->getNewDocumentInstanceByModelName('Rbs_Stock_Sku');
		$sku->setCode('TUTU');
		$tm = $this->getApplicationServices()->getTransactionManager();
		try
		{
			$tm->begin();
			$sku->save();
			$tm->commit();
		}
		catch (\Exception $e)
		{
			throw $tm->rollBack($e);
		}
		$this->assertGreaterThan(0, $sku->getId());

		/** @var $skuConflict \Rbs\Stock\Documents\Sku */
		$skuConflict = $this->getApplicationServices()->getDocumentManager()->getNewDocumentInstanceByModelName('Rbs_Stock_Sku');
		$skuConflict->setCode('TUTU');
		try
		{
			$tm->begin();
			$skuConflict->save();
			$tm->commit();
		}
		catch (\Exception $e)
		{
			$tm->rollBack($e);
			$this->assertInstanceOf('RuntimeException', $e);
			$this->assertEquals('A SKU with the same code already exists', $e->getMessage());
			$this->assertEquals(999999, $e->getCode());
		}
	}

	public function testCleanInventory()
	{
		$documentManager = $this->getApplicationServices()->getDocumentManager();

		/** @var $sku1 \Rbs\Stock\Documents\Sku */
		$sku1 = $documentManager->getNewDocumentInstanceByModelName('Rbs_Stock_Sku');
		$sku1->setCode('TATA');

		/** @var $sku2 \Rbs\Stock\Documents\Sku */
		$sku2 = $documentManager->getNewDocumentInstanceByModelName('Rbs_Stock_Sku');
		$sku2->setCode('TITI');

		$tm = $this->getApplicationServices()->getTransactionManager();
		try
		{
			$tm->begin();
			$sku1->save();
			$sku2->save();
			$tm->commit();
		}
		catch (\Exception $e)
		{
			throw $tm->rollBack($e);
		}

		try
		{
			$tm->begin();
			$query1 = $documentManager->getNewQuery('Rbs_Stock_InventoryEntry');
			$query1->andPredicates($query1->eq('sku', $sku1));
			$stocks = $query1->getDocuments();
			$this->assertEquals(1, $stocks->count());

			/** @var $stock1 \Rbs\Stock\Documents\InventoryEntry */
			$stock1 = $stocks->offsetGet(0);
			$this->assertInstanceOf('\Rbs\Stock\Documents\InventoryEntry', $stock1);
			$stock1->setLevel(10);
			$stock1->save();

			$query1 = $documentManager->getNewQuery('Rbs_Stock_InventoryEntry');
			$query1->andPredicates($query1->eq('sku', $sku2));
			$stocks = $query1->getDocuments();
			$this->assertEquals(1, $stocks->count());

			/** @var $stock2 \Rbs\Stock\Documents\InventoryEntry */
			$stock2 = $stocks->offsetGet(0);
			$this->assertInstanceOf('\Rbs\Stock\Documents\InventoryEntry', $stock2);
			$stock2->setLevel(20);
			$stock2->save();

			$tm->commit();
		}
		catch (\Exception $e)
		{
			throw $tm->rollBack($e);
		}

		$query1 = $documentManager->getNewQuery('Rbs_Stock_InventoryEntry');
		$query1->andPredicates($query1->eq('sku', $sku1));
		$this->assertEquals(1, $query1->getCountDocuments());

		$query2 = $documentManager->getNewQuery('Rbs_Stock_InventoryEntry');
		$query2->andPredicates($query2->eq('sku', $sku2));
		$this->assertEquals(1, $query2->getCountDocuments());

		try
		{
			$tm->begin();
			$sku1->delete();
			$tm->commit();
		}
		catch (\Exception $e)
		{
			throw $tm->rollBack($e);
		}

		$query1 = $documentManager->getNewQuery('Rbs_Stock_InventoryEntry');
		$query1->andPredicates($query1->eq('sku', $sku1));
		$this->assertEquals(0, $query1->getCountDocuments());

		$query2 = $documentManager->getNewQuery('Rbs_Stock_InventoryEntry');
		$query2->andPredicates($query2->eq('sku', $sku2));
		$this->assertEquals(1, $query2->getCountDocuments());

		try
		{
			$tm->begin();
			$sku2->delete();
			$tm->commit();
		}
		catch (\Exception $e)
		{
			throw $tm->rollBack($e);
		}

		$query2 = $documentManager->getNewQuery('Rbs_Stock_InventoryEntry');
		$query2->andPredicates($query2->eq('sku', $sku2));
		$this->assertEquals(0, $query2->getCountDocuments());
	}
}
