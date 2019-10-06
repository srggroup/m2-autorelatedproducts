<?php

namespace Srg\AutoRelatedProducts\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Attribute
 *
 * @package   Srg\AutoRelatedProducts\Model\Config\Source
 * @author    SRG Group <dev@srg.hu>
 * @copyright 2019 SRG Group Kft.
 */
class Attribute implements OptionSourceInterface {


	/**
	 * @return array
	 */
	public function toOptionArray() {
		return [
			['value' => 'any', 'label' => __('Any attrubte')],
			['value' => 'same', 'label' => __('Same as Product')]
		];
	}


}
