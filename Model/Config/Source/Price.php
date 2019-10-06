<?php
namespace Srg\AutoRelatedProducts\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Price
 *
 * @package   Srg\AutoRelatedProducts\Model\Config\Source
 * @author    SRG Group <dev@srg.hu>
 * @copyright 2019 SRG Group Kft.
 */
class Price implements OptionSourceInterface {

	/**
	 * @return array
	 */
	public function toOptionArray() {
		return [
		['value' => 'any', 'label' => __('Any Price')],
		['value' => 'same', 'label' => __('Same as Product price')],
		['value' => 'more', 'label' => __('More then current Product Price')],
		['value' => 'less', 'label' => __('Less then current Product Price')]
		];
	}


}
