<?php

namespace CHK\BackInStockConfigurable\Block\Product;

use Magento\Framework\View\Element\Template;
use CHK\BackInStockConfigurable\Helper\Data as DataHelper;

class Form Extends Template
{
    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * Form constructor.
     * @param Template\Context $context
     * @param DataHelper $dataHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        DataHelper $dataHelper,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getFrontendDetailsPageLabel()
    {
        return $this->dataHelper->getFrontendDetailPageLabel();
    }
}
