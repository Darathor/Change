<?php
namespace Change\Application;

use Change\Application\Package\Package;
use Zend\Stdlib\ErrorHandler;
use Zend\Json\Json;
use Zend\Loader\StandardAutoloader;

/**
 * @name Change\Application\PackageManager
 */
class PackageManager
{
	/**
	 * @var \Change\Application
	 */
	protected $application;

	/**
	 * @param \Change\Application $application
	 */
	public function __construct(\Change\Application $application)
	{
		$this->application = $application;
	}

	/**
	 * Clear all PackageManager related class
	 *
	 * @api
	 */
	public function clearCache()
	{
		$this->clearPsr0Cache();
	}

	/**
	 * Clear PSR-0
	 */
	protected function clearPsr0Cache()
	{
		$path = $this->getPsr0CachePath();
		if (file_exists($path))
		{
			ErrorHandler::start();
			unlink($path);
			ErrorHandler::stop(true);
		}
	}

	/**
	 * Path to the PSR-0 Cache Path
	 *
	 * @api
	 * @return string
	 */
	protected function getPsr0CachePath()
	{
		return $this->application->getWorkspace()->tmpPath('.psr-0.ser');
	}

	/**
	 * Return the list of PSR-0 compatible autload registered by installed packages
	 *
	 * @array
	 */
	public function getRegisteredAutoloads()
	{
		$path = $this->getPsr0CachePath();
		if (!file_exists($path))
		{
			$namespaces = array();
			// Libraries
			$librariesPattern = $this->application->getWorkspace()->librariesPath('*', '*', 'composer.json');
			foreach(\Zend\Stdlib\Glob::glob($librariesPattern, \Zend\Stdlib\Glob::GLOB_NOESCAPE + \Zend\Stdlib\Glob::GLOB_NOSORT) as $filePath)
			{
				$namespaces = array_merge($namespaces, $this->parseComposerFile($filePath, true));
			}
			// Plugin Modules
			$pluginsModulesPattern = $this->application->getWorkspace()->pluginsModulesPath('*', '*', 'composer.json');
			foreach(\Zend\Stdlib\Glob::glob($pluginsModulesPattern, \Zend\Stdlib\Glob::GLOB_NOESCAPE + \Zend\Stdlib\Glob::GLOB_NOSORT) as $filePath)
			{
				$parts = explode(DIRECTORY_SEPARATOR, $filePath);
				$partsCount = count($parts);
				$normalizedVendor = ucfirst(strtolower($parts[$partsCount-3]));
				$normalizedName = ucfirst(strtolower($parts[$partsCount-2]));
				$namespace =  $normalizedVendor . '\\' . $normalizedName . '\\';
				$namespaces = array_merge($namespaces, array($namespace => dirname($filePath)), $this->parseComposerFile($filePath));
			}
			// Project modules
			$projectModulesPattern = $this->application->getWorkspace()->projectModulesPath('*');
			foreach (\Zend\Stdlib\Glob::glob($projectModulesPattern, \Zend\Stdlib\Glob::GLOB_NOESCAPE + \Zend\Stdlib\Glob::GLOB_NOSORT) as $modulePath)
			{
				$parts = explode(DIRECTORY_SEPARATOR, $modulePath);
				$partsCount = count($parts);
				$moduleName = ucfirst(strtolower($parts[$partsCount-1]));
				$namespaces['Project\\' . $moduleName . '\\'] = $modulePath;
			}
			\Change\Stdlib\File::write($path, \Zend\Serializer\Serializer::serialize($namespaces));
		}
		return \Zend\Serializer\Serializer::unserialize(file_get_contents($path));
	}

	/**
	 *
	 * @param string $filePath path to the composer.json file
	 * @param boolean $appendNamespacePath
	 * @return array
	 */
	protected function parseComposerFile($filePath, $appendNamespacePath = false)
	{
		$composer = Json::decode(file_get_contents($filePath), Json::TYPE_ARRAY);
		$namespaces = array();
		if (isset($composer['autoload']) && isset($composer['autoload']['psr-0']))
		{
			$basePath = dirname($filePath);
			$namespaces =  $composer['autoload']['psr-0'];
			array_walk($namespaces, function(&$item, $key) use ($basePath, $appendNamespacePath){
				$item =  $basePath . DIRECTORY_SEPARATOR  . $item;
				if ($appendNamespacePath)
				{
					$item .= DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $key);
				}
			});
		}
		return $namespaces;
	}
}