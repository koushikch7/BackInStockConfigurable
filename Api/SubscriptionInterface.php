<?php

namespace CHK\BackInStockConfigurable\Api;

/**
 * @api
 */
interface SubscriptionInterface
{
    /**
     * @param string $email
     * @param string $productUrl
     * @param string $simple
     * @param string $configurable
     *
     * @return array
     */
    public function subscribe(
        $email,
        $productUrl,
        $simple,
        $configurable
    );
}
