<?php
/**
 * Copyright (C) 2014 Ready Business System
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Rbs\Discount\Modifiers;

/**
* @name \Rbs\Discount\Modifiers\FreeShippingFee
*/
class FreeShippingFee extends \Rbs\Commerce\Cart\CartDiscountModifier
{
	/**
	 * @return boolean
	 */
	public function apply()
	{
		foreach ($this->cart->getFees() as $fee)
		{
			$shippingModeId = $fee->getOptions()->get('shippingModeId');

			if (!$shippingModeId) {
				continue;
			}

			$data = $this->discount->getParametersData();
			if (is_array($data) && isset($data['shippingMode']) && ($data['shippingMode'] != $shippingModeId))
			{
				continue;
			}

			$feeItems = $fee->getItems();

			if (count($feeItems)) {
				$price = $feeItems[0]->getPrice();
				if ($price && $price->getValue() !== null)
				{
					$dp = clone($price);
					$dp->setValue(- $dp->getValue() * $fee->getQuantity());
					$this->setPrice($dp);

					$taxes = [];
					foreach ($fee->getTaxes() as $tax)
					{
						$dpt = clone($tax);
						$dpt->setValue(- $dpt->getValue());
						$taxes[] = $dpt;
					}
					$this->setTaxes($taxes);
					$this->setOptions(['feeKey' => $fee->getKey(), 'shippingModeId' => $shippingModeId]);

				}
			}
		}

		return parent::apply();
	}
}