<?php
/**
 * Copyright (C) 2014 Ready Business System
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Change\Presentation\Layout;

/**
 * @package Change\Presentation\Layout
 * @name \Change\Presentation\Layout\Container
 */
class Container extends Item
{
	/**
	 * @var integer
	 */
	protected $grid;

	/**
	 * @var string
	 */
	protected $gridMode;

	/**
	 * @return int
	 */
	public function getGrid()
	{
		return $this->grid;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'container';
	}

	/**
	 * @param array $data
	 * @return void
	 */
	public function initialize(array $data)
	{
		parent::initialize($data);
		$this->grid = $data['grid'];
	}
}