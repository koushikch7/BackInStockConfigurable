<?php

namespace CHK\BackInStockConfigurable\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Subscription
 * @package CHK\BackInStockConfigurable\Model\ResourceModel
 */
class Subscription extends AbstractDb
{
    const TABLE_NAME    = 'chk_backinstock_subscription';
    const ID_FIELD_NAME = 'subscription_id';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::ID_FIELD_NAME);
    }
}
