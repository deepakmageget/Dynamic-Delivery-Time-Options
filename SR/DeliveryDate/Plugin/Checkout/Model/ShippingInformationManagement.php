<?php
namespace SR\DeliveryDate\Plugin\Checkout\Model;

class ShippingInformationManagement
{
    protected $quoteRepository;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $extAttributes = $addressInformation->getExtensionAttributes();
        $deliveryDate = $extAttributes->getDeliveryDate();
        $deliveryComment = $extAttributes->getDeliveryComment();
        $deliveryTime = $extAttributes->getDeliveryTime();
        $quote = $this->quoteRepository->getActive($cartId);
        $quote->setDeliveryDate($deliveryDate);
        $quote->setDeliveryComment($deliveryComment);

        $strposition1 = strpos((string) $deliveryTime, "Select");
        $strposition2 = strpos((string) $deliveryTime, "available");
        $strposition3 = strpos((string) $deliveryTime, "end");
        $a = true;
        if ($strposition1) {
            $a = false;
        }
        if ($strposition2) {
            $a = false;
        }
        if ($strposition3) {
            $a = false;
        }
        if ($a == true) {
            $quote->setDeliveryTime($deliveryTime);
        }
    }
}
