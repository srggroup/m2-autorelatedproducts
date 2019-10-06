<?php
namespace Srg\AutoRelatedProducts\Plugin\Block\Product\ProductList;

use Srg\AutoRelatedProducts\Helper\Data;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Related
 *
 * @package   Srg\AutoRelatedProducts\Plugin\Block\Product\ProductList
 * @author    SRG Group <dev@srg.hu>
 * @copyright 2019 SRG Group Kft.
 */
class Related {

	/**
	 * @var CategoryFactory
	 */
	protected $categoryFactory;

	/**
	 * @var Registry
	 */
	protected $registry;

	/**
	 * @var Data
	 */
	protected $dataHelper;

	/**
	 * @var CollectionFactory
	 */
	protected $productCollectionFactory;

	/**
	 * @var string
	 */
	protected $action;

	/**
	 * @var Configurable
	 */
	protected $configurable;

	/**
	 * @var StoreManagerInterface
	 */
	protected $storeManager;


	/**
	 * Related constructor.
	 *
	 * @param Context             $context
	 * @param CategoryFactory                                              $categoryFactory
	 * @param Registry                                                     $registry
	 * @param CollectionFactory                                            $productCollectionFactory
	 * @param Configurable $configurable
	 * @param Data                                                         $dataHelper
	 *
	 * @throws NoSuchEntityException
	 */
	public function __construct(
		Context $context,
		CategoryFactory $categoryFactory,
		Registry $registry,
		CollectionFactory $productCollectionFactory,
		Configurable $configurable,
		Data $dataHelper
	) {
		$this->categoryFactory = $categoryFactory;
		$this->registry = $registry;
		$this->productCollectionFactory = $productCollectionFactory;
		$this->configurable = $configurable;
		$this->dataHelper = $dataHelper;
		$this->storeManager = $context->getStoreManager();
		$this->action = '';

		$_time = $this->dataHelper->getCacheTime();
		if ($_time > 0 && $cacheKey = $this->cacheKey()) {
			$this->addData(array(
				'cache_lifetime'    => $_time,
				'cache_tags'        => array(\Magento\Store\Model\Store::CACHE_TAG),
				'cache_key'         => $cacheKey,
			));
		}
	}


	/**
	 * @return bool|string
	 * @throws NoSuchEntityException
	 */
	protected function cacheKey() {
		$product = $this->registry->registry('product');
		if ($product) {
			return get_class() . '::' .  $this->storeManager->getStore()->getCode() . '::' . $product->getId();
		}
		return false;
	}


	/**
	 * @param \Magento\Catalog\Block\Product\ProductList\Related $subject
	 * @param                                                    $result
	 *
	 * @return mixed
	 * @throws NoSuchEntityException
	 */
	public function afterGetItems(
		\Magento\Catalog\Block\Product\ProductList\Related $subject,
		$result
	) {
		if ($this->dataHelper->isEnabled()) {
			$relatedDisplay = $this->dataHelper->getRelatedDisplayFilter();
			if ($relatedDisplay == "manual") {
				return $result;
			} elseif ($relatedDisplay == "replace") {
				$this->action = $relatedDisplay;
				$collection = $this->getRelatedProductsCollection($result);
				return $collection;
			} else {
				$this->action = $relatedDisplay;
				$collection = $this->getRelatedProductsCollection($result);
				return $collection;
			}
		}
		return $result;
	}


	/**
	 * @param $loadedCollection
	 *
	 * @return mixed
	 * @throws NoSuchEntityException
	 */
	private function getRelatedProductsCollection($loadedCollection) {
		$product = $this->registry->registry('current_product');
	   
		if ($product->getTypeId() === 'configurable') {
			$price = $product->getFinalPrice();
		} else {
			$price = $product->getFinalPrice();
		}
		
		$attributes = $product->getAttributes();
		$productCount = $this->dataHelper->getProductsCount();

		if ($this->dataHelper->getCategoryFilter() == "same") {
			$productCategories = $this->getProductCategories($product);
			$categoryId = end($productCategories);
			$category = $this->categoryFactory->create()->load($categoryId);
			$product_collection = $category->getProductCollection()->addAttributeToSelect('*')->addStoreFilter($this->storeManager->getStore());
		} else {
			$product_collection = $this->getProductCollection();
		}
		if ($this->action == 'merge') {
		}
		if ($this->dataHelper->getPriceFilter() == "same") {
			$product_collection->addFieldToFilter('price', ['eq' => $price]);
		} elseif ($this->dataHelper->getPriceFilter() == "more") {
			$product_collection->addFieldToFilter('price', array('gt' => $price));
		} elseif ($this->dataHelper->getPriceFilter() == "less") {
			$product_collection->addFieldToFilter('price', ['lt' => $price]);
		}

		$product_collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
		$product_collection->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
		$product_collection->setPageSize($productCount);
		$product_collection->getSelect()->orderRand();
		return $product_collection;
	}


	/**
	 * @param $product
	 *
	 * @return mixed
	 */
	public function getProductCategories($product) {
		return $product->getCategoryIds();
	}


	/**
	 * @param $product
	 */
	public function getProductAttributes($product) {
	}


	/**
	 * @return mixed
	 * @throws NoSuchEntityException
	 */
	public function getProductCollection() {
		$collection = $this->productCollectionFactory->create();
		$collection->addAttributeToSelect('*');
		$collection->addStoreFilter($this->storeManager->getStore());
		return $collection;
	}


}
