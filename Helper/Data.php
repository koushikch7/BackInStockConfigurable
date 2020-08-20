<?php

namespace CHK\BackInStockConfigurable\Helper;

use CHK\BackInStockConfigurable\Model\Subscription;
use CHK\BackInStockConfigurable\Model\SubscriptionRepository;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface as State;
use Magento\Framework\Url;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\ProductFactory;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    const EMAIL_TEMPLATE = 'back_in_stock_configurable_notification_email_template';

    const XML_PATH_IS_ENABLED                  = 'chk_back_in_stock_configurable/settings/enabled';
    const XML_PATH_IS_SCHEDULED                = 'chk_back_in_stock_configurable/settings/cron_send_notifications_enable';
    const XML_PATH_EMAIL_FROM                  = 'trans_email/ident_support/email';
    const XML_PATH_NAME_FROM                   = 'trans_email/ident_support/name';
    const XML_PATH_ATTRIBUTES_SORTING_ORDER    = 'chk_back_in_stock_configurable/settings/attributes_sorting_order';
    const XML_PATH_POPUP_HEADER_CMS_BLOCK_ID   = 'chk_back_in_stock_configurable/settings/popup_header_cms_block_id';
    const XML_PATH_FRONTEND_DETAILS_PAGE_LABEL = 'chk_back_in_stock_configurable/settings/frontend_label';

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepository;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var State
     */
    private $inlineTranslation;

    /**
     * @var CollectionFactory
     */
    private $templatesFactory;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var UrlInterface
     */
    private $frontUrlModel;

    /**
     * @var Url
     */
    private $url;

    /**
     * Data constructor.
     * @param Context $context
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SubscriptionRepository $subscriptionRepository
     * @param TransportBuilder $transportBuilder
     * @param State $inlineTranslation
     * @param CollectionFactory $templatesFactory
     * @param ImageHelper $imageHelper
     * @param ProductFactory $productFactory
     * @param StoreManagerInterface $storeManager
     * @param Url $url
     */
    public function __construct(
        Context $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SubscriptionRepository $subscriptionRepository,
        TransportBuilder $transportBuilder,
        State $inlineTranslation,
        CollectionFactory $templatesFactory,
        ImageHelper $imageHelper,
        ProductFactory $productFactory,
        StoreManagerInterface $storeManager,
        Url $url
    ) {
        $this->searchCriteriaBuilder  = $searchCriteriaBuilder;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->transportBuilder       = $transportBuilder;
        $this->inlineTranslation      = $inlineTranslation;
        $this->templatesFactory       = $templatesFactory;
        $this->imageHelper            = $imageHelper;
        $this->productFactory = $productFactory;
        $this->_storeManager = $storeManager;
        $this->url = $url;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_IS_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isScheduledNotifications()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_IS_SCHEDULED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param Subscription $subscription
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    protected function getEmailTemplateVariables(Subscription $subscription)
    {
        $subscriptionId = $subscription->getId();
        $subscriptionStoreId = $subscription->getStoreId();
        $storeCode = (string)$this->_storeManager->getStore($subscriptionStoreId)->getCode();
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $product =$subscription->getProduct();
        $imageUrl = $baseUrl . 'pub/media/catalog/product' . $product->getImage();
        return [
            'product_name'  => $product->getName(),
            'product_image' => $imageUrl,
            'product_url'   => $subscription->getProductUrl(),
            'product_price'   => $product->getPrice(),
        ];
    }

    /**
     * @param $productId
     * @param string $storeCode
     * @return string
     */
    private function  getProductFrontendUrl($productId, $storeCode = 'default')
    {
        return $this->url->getUrl(
            'catalog/product/view',
            ['id' => $productId, '_nosid' => true, '_query' => ['___store' => $storeCode]
            ]
        );
    }

    /**
     * @return string
     */
    public function getEmailFrom()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_FROM,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getNameFrom()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_NAME_FROM,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param Subscription $subscription
     * @throws LocalizedException
     * @throws MailException
     */
    public function sendMail(Subscription $subscription)
    {
        $this->inlineTranslation->suspend();

        $template = $this->templatesFactory->create()->addFieldToFilter('template_code', self::EMAIL_TEMPLATE)->getFirstItem();

        $identifier = $template->getId() ? $template->getId() : self::EMAIL_TEMPLATE;

        $transport = $this->transportBuilder->setTemplateIdentifier($identifier)
            ->setTemplateOptions([
                'area'  => Area::AREA_FRONTEND,
                'store' => $subscription->getStoreId()
            ])->setTemplateVars($this->getEmailTemplateVariables($subscription)
            )->setFrom([
                'email' => $this->getEmailFrom(),
                'name'  => $this->getNameFrom(),
            ])->addTo($subscription->getEmail())
            ->getTransport();

        $transport->sendMessage();

        $this->inlineTranslation->resume();
    }

    /**
     * @return array
     */
    public function getAttributesSortingOrder()
    {
        $rawValue = $this->scopeConfig->getValue(
            self::XML_PATH_ATTRIBUTES_SORTING_ORDER,
            ScopeInterface::SCOPE_STORE
        );

        return array_map('trim', explode(',', $rawValue));
    }

    /**
     * @return string
     */
    public function getPopupHeaderCmsBlockId()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_POPUP_HEADER_CMS_BLOCK_ID,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getFrontendDetailPageLabel()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FRONTEND_DETAILS_PAGE_LABEL,
            ScopeInterface::SCOPE_STORE
        );
    }
}

