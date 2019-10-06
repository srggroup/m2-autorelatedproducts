<?php
namespace Srg\AutoRelatedProducts\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class RelatedProductDisplay
 *
 * @package   Srg\AutoRelatedProducts\Model\Config\Source
 * @author    SRG Group <dev@srg.hu>
 * @copyright 2019 SRG Group Kft.
 */
class RelatedProductDisplay implements OptionSourceInterface {


	/**
	 * @return array
	 */
	public function toOptionArray() {
		return [
			['value' => 'manual', 'label' => __('Manual Added Product')],
			['value' => 'replace', 'label' => __('Replace Manual Added Products')],
			['value' => 'merge', 'label' => __('Merge Products')]
		];
	}


}
