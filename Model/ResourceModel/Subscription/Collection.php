<?php

namespace CHK\BackInStockConfigurable\Model\ResourceModel\Subscription;

use CHK\BackInStockConfigurable\Model\ResourceModel\Subscription as SubscriptionResourceModel;
use CHK\BackInStockConfigurable\Model\Subscription as SubscriptionModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package CHK\BackInStockConfigurable\Model\ResourceModel\Subscription
 */
class Collection extends AbstractCollection
{

    protected function _construct()
    {
        $this->_init(
            SubscriptionModel::class,
            SubscriptionResourceModel::class
        );
    }
}
