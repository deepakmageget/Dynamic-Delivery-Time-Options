<?php
namespace SR\DeliveryDate\Model\Adminhtml\System\Config\Source;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class DynamicSlotDeliveryTime implements
    \Magento\Framework\Option\ArrayInterface
{
    const XPATH_START_TIME = "carriers/deliverydateandtime/starttimne";
    const XPATH_END_TIME = "carriers/deliverydateandtime/endtimne";
    const XPATH_DYNAMIC_SLOT_ACTIVE = "carriers/deliverydateandtime/dynamicslotactive";
    const XPATH_DYNAMIC_SLOT_MINUTE = "carriers/deliverydateandtime/dynamicslotgenerateminute";

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $timezoneInterface;

    public function __construct(
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        TimezoneInterface $timezoneInterface
    ) {
        $this->_groupCollectionFactory = $groupCollectionFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->timezoneInterface = $timezoneInterface;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $store = $this->getStoreId();
        $getStoreTime = $this->getStoreTime();
        $getStoreTimeAmPm = $this->getStoreTimeAmPm();

        $starttime = $this->scopeConfig->getValue(
            self::XPATH_START_TIME,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $endtime = $this->scopeConfig->getValue(
            self::XPATH_END_TIME,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $dslotstatus = $this->scopeConfig->getValue(
            self::XPATH_DYNAMIC_SLOT_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $dslotminute = $this->scopeConfig->getValue(
            self::XPATH_DYNAMIC_SLOT_MINUTE,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        $start_time = str_replace(",", ":", $starttime);
        $end_time = str_replace(",", ":", $endtime);

        $start_time = $start_time; //start time as string
        $end_time = $end_time; //end time as string

        $start_time = strtotime($start_time);
        $end_time = strtotime($end_time);
        $slot = strtotime(date("H:i:s", $start_time) . "+$dslotminute minutes");

        $data = [];

        for ($i = 0; $slot <= $end_time; $i++) {
            $data[$i] = [
                "start" => date("h:iA", $start_time),
                "end" => date("h:iA", $slot),
            ];

            $start_time = $slot;
            $slot = strtotime(
                date("H:i", $start_time) . "+$dslotminute minutes"
            );
        }

        $dynaDeleveryTime = [];

        foreach ($data as $slottime) {
            $finelOptions = "";

            $slotTimeStart = $slottime["start"];
            $slotTimeEnd = $slottime["end"];

            $finelOptions .= $slotTimeStart . " - " . $slotTimeEnd;

            $dynaDeleveryTime[] = $finelOptions;
        }

        $deleveryTime = $dynaDeleveryTime;

        foreach ($deleveryTime as $data) {
            $this->_options[] = ["label" => $data, "value" => $data];
        }
        if (isset($this->_options)) {
            return $this->_options;
        }
    }

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getStoreId();
    }

    public function getStoreTime()
    {
        $time = $this->timezoneInterface->date()->format("His");
        return $time;
    }
    public function getStoreTimeAmPm()
    {
        return $timeAmPm = $this->timezoneInterface->date()->format("HisA");
    }
}
