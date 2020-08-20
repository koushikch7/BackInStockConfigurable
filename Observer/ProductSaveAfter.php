<?php

namespace CHK\BackInStockConfigurable\Observer;

use CHK\BackInStockConfigurable\Model\Subscription;
use CHK\BackInStockConfigurable\Model\SubscriptionRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use CHK\BackInStockConfigurable\Helper\Data as ModuleHelper;

/**
 * Class ProductSaveAfter
 * @package CHK\BackInStockConfigurable\Observer
 */
class ProductSaveAfter implements ObserverInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SubscriptionRepository
     */
    protected $subscriptionRepository;

    /**
     * @var ModuleHelper
     */
    protected $moduleHelper;

    /**
     * ProductSaveAfter constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SubscriptionRepository $subscriptionRepository
     * @param ModuleHelper $moduleHelper
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SubscriptionRepository $subscriptionRepository,
        ModuleHelper $moduleHelper
    ) {
        $this->searchCriteriaBuilder  = $searchCriteriaBuilder;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->moduleHelper           = $moduleHelper;
    }

    /**
     * Process product notifications
     *
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        if ($this->moduleHelper->isScheduledNotifications()) {
            return;
        }

        $product = $observer->getProduct();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('product_id', $product->getId())
            ->create();

        $subscriptions = $this->subscriptionRepository->getList($searchCriteria)->getItems();

        /** @var Subscription $subscription */
        foreach ($subscriptions as $subscription) {
            if ($subscription->isReady($product)) {
                $subscription->proceed();

                $this->subscriptionRepository->delete($subscription);
            }
        }
    }
}
