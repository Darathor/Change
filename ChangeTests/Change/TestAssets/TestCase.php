<?php

namespace ChangeTests\Change\TestAssets;

/**
 * @name \ChangeTests\Change\TestAssets\TestCase
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * @return \ChangeTests\Change\TestAssets\Application
	 */
	protected static function getNewApplication()
	{
		return new \ChangeTests\Change\TestAssets\Application();
	}

	/**
	 * @param \Change\Application $application
	 * @return \Change\Application\ApplicationServices
	 */
	protected static function getNewApplicationServices(\Change\Application $application)
	{
		return new \Change\Application\ApplicationServices($application);
	}

	/**
	 * @param \Change\Application\ApplicationServices $applicationServices
	 * @throws \RuntimeException
	 * @return \Change\Documents\DocumentServices
	 */
	protected static function getNewDocumentServices(\Change\Application\ApplicationServices $applicationServices)
	{
		return new \Change\Documents\DocumentServices($applicationServices);
	}

	/**
	 * @param \Change\Application\ApplicationServices $applicationServices
	 * @throws \RuntimeException
	 * @return \Change\Presentation\PresentationServices
	 */
	protected static function getNewPresentationServices(\Change\Application\ApplicationServices $applicationServices)
	{
		return new \Change\Presentation\PresentationServices($applicationServices);
	}

	/**
	 * @var \ChangeTests\Change\TestAssets\Application
	 */
	protected $application;

	/**
	 * @var \Change\Application\ApplicationServices
	 */
	protected $applicationServices;

	/**
	 * @var \Change\Documents\DocumentServices
	 */
	protected $documentServices;

	/**
	 * @var \Change\Presentation\PresentationServices
	 */
	protected $presentationServices;

	/**
	 * @return \ChangeTests\Change\TestAssets\Application
	 */
	protected function getApplication()
	{
		if (!$this->application)
		{
			$this->application = static::getNewApplication();
		}
		return $this->application;
	}

	/**
	 * @return \Change\Application\ApplicationServices
	 */
	public function getApplicationServices()
	{
		if (!$this->applicationServices)
		{
			$this->applicationServices  = static::getNewApplicationServices($this->getApplication());
		}
		return $this->applicationServices;
	}

	/**
	 * @throws \RuntimeException
	 * @return \Change\Documents\DocumentServices
	 */
	public function getDocumentServices()
	{
		if (!$this->documentServices)
		{
			$this->documentServices = static::getNewDocumentServices($this->getApplicationServices());
		}
		return $this->documentServices;
	}

	/**
	 * @return \Change\Presentation\PresentationServices
	 */
	public function getPresentationServices()
	{
		if (!$this->presentationServices)
		{
			$this->presentationServices = static::getNewPresentationServices($this->getApplicationServices());
		}
		return $this->presentationServices;
	}


	public static function initDb()
	{
		$app = static::getNewApplication();
		$appServices = static::getNewApplicationServices($app);

		$generator = new \Change\Db\Schema\Generator($app->getWorkspace(), $appServices->getDbProvider());
		$generator->generateSystemSchema();
	}

	public static function initDocumentsClasses()
	{
		$app = static::getNewApplication();
		$appServices = static::getNewApplicationServices($app);

		$appServices->getPluginManager()->compile(false);

		$compiler = new \Change\Documents\Generators\Compiler($app, $appServices);
		$compiler->generate();
	}

	public static function initDocumentsDb()
	{
		$app = static::getNewApplication();
		$appServices = static::getNewApplicationServices($app);

		$generator = new \Change\Db\Schema\Generator($app->getWorkspace(), $appServices->getDbProvider());
		$generator->generateSystemSchema();

		$appServices->getPluginManager()->compile(false);

		$compiler = new \Change\Documents\Generators\Compiler($app, $appServices);
		$compiler->generate();

		$generator->generatePluginsSchema();
	}

	public static function clearDB()
	{
		$dbp =  static::getNewApplicationServices(static::getNewApplication())->getDbProvider();
		$dbp->getSchemaManager()->clearDB();
	}
}