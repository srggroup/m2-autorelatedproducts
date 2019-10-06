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

	const TYPE_MANUAL = 'manual';
	const TYPE_REPLACE = 'replace';
	const TYPE_MERGE = 'merge';

	/**
	 * @return array
	 */
	public function toOptionArray() {
		return [
			['value' => self::TYPE_MANUAL, 'label' => __('Manual Added Product')],
			['value' => self::TYPE_REPLACE, 'label' => __('Replace Manual Added Products')],
			['value' => self::TYPE_MERGE, 'label' => __('Merge Products')]
		];
	}


}
