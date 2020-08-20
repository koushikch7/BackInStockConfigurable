<?php

namespace CHK\BackInStockConfigurable\Model;

use CHK\BackInStockConfigurable\Helper\Data;
use CHK\BackInStockConfigurable\Model\ResourceModel\Subscription as SubscriptionResource;
use CHK\BackInStockConfigurable\Model\ResourceModel\Subscription\Collection;
use CHK\BackInStockConfigurable\Model\SubscriptionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\DataObject;

/**
 * Class Subscription
 * @package CHK\BackInStockConfigurable\Model
 */
class Subscription extends AbstractModel implements SubscriptionInterface
{
    const CACHE_TAG = 'chk_backinstock_subscription';

    /**
     * @var Data
     */
    private $moduleHelper;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * Subscription constructor.
     * @param Context $context
     * @param Registry $registry
     * @param SubscriptionResource $resource
     * @param Collection $resourceCollection
     * @param Data $moduleHelper
     * @param ProductFactory $productFactory
     * @param StockRegistryInterface $stockRegistry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SubscriptionResource $resource,
        Collection $resourceCollection,
        Data $moduleHelper,
        ProductFactory $productFactory,
        StockRegistryInterface $stockRegistry,
        array $data = []
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->productFactory = $productFactory;
        $this->stockRegistry = $stockRegistry;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('CHK\BackInStockConfigurable\Model\ResourceModel\Subscription');
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @inheritdoc
     */
    public function proceed()
    {
        $this->moduleHelper->sendMail($this);
    }

    /**
     * @inheritdoc
     */
    public function getProduct()
    {
        return $this->productFactory->create()->load($this->getProductId());
    }

    /**
     * @inheritdoc
     */
    public function isReady($product = null)
    {
        if (!$product) {
            $product = $this->getProduct();
        }

        $stockData = $product->getStockData();

        if ($stockData) {
            $stockItem = new DataObject($stockData);
        } else {
            $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        }

        return 0 < $stockItem->getQty() && $stockItem->getIsInStock();
    }
}
