<?php
namespace Rbs\Generic\Events\Http\Web;

use Change\Http\Web\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

/**
 * @name \Rbs\Generic\Events\Http\Web\Listeners
 */
class Listeners implements ListenerAggregateInterface
{
	/**
	 * Attach one or more listeners
	 * Implementors may add an optional $priority argument; the EventManager
	 * implementation will pass this to the aggregate.
	 * @param EventManagerInterface $events
	 * @return void
	 */
	public function attach(EventManagerInterface $events)
	{
		$events->attach(Event::EVENT_ACTION, array($this, 'registerActions'));
		$callback = function (Event $event)
		{
			(new \Rbs\User\Http\Web\Login())->authenticate($event);
		};
		$events->attach(Event::EVENT_AUTHENTICATE, $callback, 5);

		$callback = function (Event $event)
		{
			$extension = new \Rbs\Generic\Presentation\Twig\Extension($event->getPresentationServices(), $event->getDocumentServices(), $event->getUrlManager());
			$event->getPresentationServices()->getTemplateManager()->addExtension($extension);
			(new \Rbs\Website\Events\WebsiteResolver())->resolve($event);
		};
		$events->attach(Event::EVENT_REQUEST, $callback, 10);
	}

	/**
	 * Detach all previously attached listeners
	 * @param EventManagerInterface $events
	 * @return void
	 */
	public function detach(EventManagerInterface $events)
	{
		// TODO: Implement detach() method.
	}

	/**
	 * @param Event $event
	 */
	public function registerActions(Event $event)
	{
		if (!$event->getAction())
		{
			$relativePath = $event->getParam('relativePath');
			if (preg_match('/^Imagestorage\/([A-Za-z0-9]+)\/([0-9]+)\/([0-9]+)(\/.+)$/', $relativePath, $matches))
			{
				$storageName = $matches [1];
				$maxWidth = intval($matches[2]);
				$maxHeight = intval($matches[3]);
				$path = $matches[4];

				$originalURI = $event->getApplicationServices()->getStorageManager()->buildChangeURI($storageName, $path);
				$changeURI = $event->getApplicationServices()->getStorageManager()
					->buildChangeURI($storageName, $path, array('max-width' => $maxWidth, 'max-height' => $maxHeight));

				$event->setParam('originalURI', $originalURI);
				$event->setParam('changeURI', $changeURI);
				$event->setParam('maxWidth', $maxWidth);
				$event->setParam('maxHeight', $maxHeight);
				$action = function ($event)
				{
					(new \Rbs\Media\Http\Web\Actions\GetImagestorageItemContent())->execute($event);
				};
				$event->setAction($action);
				return;
			}
		}
	}
}