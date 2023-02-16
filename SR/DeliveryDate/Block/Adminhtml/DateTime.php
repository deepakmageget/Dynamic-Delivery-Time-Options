<?php

namespace SR\DeliveryDate\Block\Adminhtml;

use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;

class DateTime extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_coreRegistry;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    public function render(
        \Magento\Framework\Data\Form\Element\AbstractElement $element
    ) {
        $element->setDateFormat(
            \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT
        );

        return parent::render($element);
    }
}
