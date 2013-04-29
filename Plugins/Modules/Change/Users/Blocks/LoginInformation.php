<?php
namespace Change\Users\Blocks;

use Change\Documents\Property;
use Change\Presentation\Blocks\BlockManager;
use Change\Presentation\Blocks\Information;

/**
 * Class LoginInformation
 * @package Change\Users\Blocks
 * @name \Change\Users\Blocks\LoginInformation
 */
class LoginInformation extends Information
{
	/**
	 * @param string $name
	 * @param BlockManager $blockManager
	 */
	function __construct($name, $blockManager)
	{
		parent::__construct($name);
		$ucf = array('ucf');
		$i18nManager = $blockManager->getPresentationServices()->getApplicationServices()->getI18nManager();
		$this->setLabel($i18nManager->trans('m.change.users.blocks.login'));
		$this->addInformationMeta('realm', Property::TYPE_STRING, true, 'web')
			->setLabel($i18nManager->trans('m.change.users.blocks.login-realm', $ucf));
	}
}
