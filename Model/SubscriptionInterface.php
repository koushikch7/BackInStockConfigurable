<?php

namespace CHK\BackInStockConfigurable\Model;

use Magento\Catalog\Api\Data\ProductInterface as Product;

/**
 * Interface SubscriptionInterface
 * @package CHK\BackInStockConfigurable\Model
 */
interface SubscriptionInterface
{
    /**
     * Proceed this subscription
     */
    public function proceed();

    /**
     * Is ready to send notification for user
     * @param Product $product
     * @return bool
     */
    public function isReady($product = null);

    /**
     * @return Product
     */
    public function getProduct();
}
