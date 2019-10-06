<?php
namespace Srg\AutoRelatedProducts\Plugin\Block\Product\ProductList;

use Magento\Framework\Data\CollectionFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\LocalizedException;
use Srg\AutoRelatedProducts\Helper\Data;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Srg\AutoRelatedProducts\Model\Config\Source\RelatedProductDisplay;

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
	 * @var StoreManagerInterface
	 */
	protected $storeManager;

	/**
	 * @var CollectionFactory
	 */
	private $collectionFactory;


	/**
	 * Related constructor.
	 *
	 * @param Context                  $context
	 * @param CategoryFactory          $categoryFactory
	 * @param Registry                 $registry
	 * @param ProductCollectionFactory $productCollectionFactory
	 * @param CollectionFactory        $collectionFactory
	 * @param Data                     $dataHelper
	 *
	 * @throws NoSuchEntityException
	 */
	public function __construct(
		Context $context,
		CategoryFactory $categoryFactory,
		Registry $registry,
		ProductCollectionFactory $productCollectionFactory,
		CollectionFactory $collectionFactory,
		Data $dataHelper
	) {
		$this->categoryFactory = $categoryFactory;
		$this->registry = $registry;
		$this->productCollectionFactory = $productCollectionFactory;
		$this->dataHelper = $dataHelper;
		$this->storeManager = $context->getStoreManager();
		$this->action = $this->dataHelper->getRelatedDisplayFilter();
		$this->collectionFactory = $collectionFactory;

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
	 * @param ProductCollection                                  $result
	 *
	 * @return mixed
	 * @throws NoSuchEntityException
	 * @throws LocalizedException
	 */
	public function afterGetItems(\Magento\Catalog\Block\Product\ProductList\Related $subject, $result) {
		if ($this->dataHelper->isEnabled()) {
			if ($this->action == RelatedProductDisplay::TYPE_MERGE) {
				$collection = $this->getRelatedProductsCollection($result);
				return $collection;
			} elseif ($this->action == RelatedProductDisplay::TYPE_REPLACE) {
				$collection = $this->getRelatedProductsCollection();
				return $collection;
			}
		}
		return $result;
	}


	/**
	 * @param ProductCollection $mergeWith
	 *
	 * @return ProductCollection
	 * @throws NoSuchEntityException
	 * @throws LocalizedException
	 */
	private function getRelatedProductsCollection($mergeWith = null) {
		$product = $this->registry->registry('current_product');
	   
		$price = $product->getFinalPrice();

		$productCount = $this->dataHelper->getProductsCount();

		if ($mergeWith instanceof ProductCollection) {
			$productCount = max(0, $productCount - $mergeWith->getSize());
		}

		if ($this->dataHelper->getCategoryFilter() == 'same') {
			$productCategories = $this->getProductCategories($product);
			$categoryId = end($productCategories);
			$category = $this->categoryFactory->create()->load($categoryId);
			$productCollection = $category->getProductCollection()->addAttributeToSelect('*')->addStoreFilter($this->storeManager->getStore());
		} else {
			$productCollection = $this->getProductCollection();
		}

		if ($this->dataHelper->getPriceFilter() == 'same') {
			$productCollection->addFieldToFilter('price', ['eq' => $price]);
		} elseif ($this->dataHelper->getPriceFilter() == 'more') {
			$productCollection->addFieldToFilter('price', array('gt' => $price));
		} elseif ($this->dataHelper->getPriceFilter() == 'less') {
			$productCollection->addFieldToFilter('price', ['lt' => $price]);
		}

		$productCollection->addAttributeToFilter('visibility', Visibility::VISIBILITY_BOTH);
		$productCollection->addAttributeToFilter('status', Status::STATUS_ENABLED);
		$productCollection->setPageSize($productCount);
		$productCollection->getSelect()->orderRand();

		if ($mergeWith instanceof ProductCollection) {
			/** @var Collection $mergedCollection */
			$mergedCollection = $this->collectionFactory->create();
			foreach ($mergeWith as $product) {
				if ($mergedCollection)
					try {
						$mergedCollection->addItem($product);
					} catch (LocalizedException $e) {}
			}
			foreach ($productCollection as $product) {
				try {
					$mergedCollection->addItem($product);
				} catch (LocalizedException $e) {}
			}
			return $mergedCollection;
		}

		return $productCollection;
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
	 * @return ProductCollection
	 * @throws NoSuchEntityException
	 */
	public function getProductCollection() {
		$collection = $this->productCollectionFactory->create();
		$collection->addAttributeToSelect('*');
		$collection->addStoreFilter($this->storeManager->getStore());
		return $collection;
	}


}
