<?php
// phpcs:ignoreFile
namespace SR\DeliveryDate\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class DeliveryDateConfigProvider implements ConfigProviderInterface
{
    const XPATH_FORMAT = "carriers/deliverydateandtime/format";
    const XPATH_DISABLED = "carriers/deliverydateandtime/disabled";
    const XPATH_ACTIVE = "carriers/deliverydateandtime/active";
    const XPATH_HOURMIN = "carriers/deliverydateandtime/hourMin";
    const XPATH_HOURMAX = "carriers/deliverydateandtime/hourMax";
    const XPATH_STARTDATE = "carriers/deliverydateandtime/startdate";
    const XPATH_ENDDATE = "carriers/deliverydateandtime/enddate";
    const XPATH_CUTOFMIN = "carriers/deliverydateandtime/cutofminute";
    const XPATH_OPTIONDELIVERYTIME = "carriers/deliverydateandtime/optiondeliverytime";
    const XPATH_START_TIME = "carriers/deliverydateandtime/starttimne";
    const XPATH_END_TIME = "carriers/deliverydateandtime/endtimne";

    const XPATH_DYNAMIC_SLOT_ACTIVE = "carriers/deliverydateandtime/dynamicslotactive";
    const XPATH_DYNAMIC_SLOT_OPTIONS = "carriers/deliverydateandtime/dynamicoptiondeliverytime";

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $timezoneInterface;
    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        TimezoneInterface $timezoneInterface
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->timezoneInterface = $timezoneInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $store = $this->getStoreId();
        $disabled = $this->scopeConfig->getValue(
            self::XPATH_DISABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $active = $this->scopeConfig->getValue(
            self::XPATH_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $hourMin = $this->scopeConfig->getValue(
            self::XPATH_HOURMIN,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $hourMax = $this->scopeConfig->getValue(
            self::XPATH_HOURMAX,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $format = $this->scopeConfig->getValue(
            self::XPATH_FORMAT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $startDate = $this->scopeConfig->getValue(
            self::XPATH_STARTDATE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $endDate = $this->scopeConfig->getValue(
            self::XPATH_ENDDATE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $cutofmin = $this->scopeConfig->getValue(
            self::XPATH_CUTOFMIN,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $optionDeliveryTime = $this->scopeConfig->getValue(
            self::XPATH_OPTIONDELIVERYTIME,
            ScopeInterface::SCOPE_STORE,
            $store
        );
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
        $dslotOptions = $this->scopeConfig->getValue(
            self::XPATH_DYNAMIC_SLOT_OPTIONS,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        if ($dslotstatus) {
            $deliveryTimeOptions = explode(",", $dslotOptions);
        } else {
            $deliveryTimeOptions = explode(",", $optionDeliveryTime);
        }

        $noday = 0;
        if ($disabled == -1) {
            $noday = 1;
        }

        $getStoreTime = $this->getStoreTime(); // 164902 // 052127

        if (isset($starttime) && !empty($starttime)) {
            $starttimnenew = "";
            $starttimearr = explode(",", $starttime);
            foreach ($starttimearr as $starttimnenewvalue) {
                $starttimnenew .= $starttimnenewvalue;
            }

            $endtimnenew = "";
            $endtimearr = explode(",", $endtime);
            if (isset($endtime) && !empty($endtime)) {
                foreach ($endtimearr as $endtimnenewvalue) {
                    $endtimnenew .= $endtimnenewvalue;
                }
            }

            //for Current date
            $deleveryTimeArray = ["Select Time"];
            foreach ($deliveryTimeOptions as $optionTime) {
                $configtime1 = $optionTime;
                $timeCheckAmPm = explode("-", $configtime1);

                $timeAm = strrpos($timeCheckAmPm[0], "AM");

                if ($dslotstatus) {
                    if ($timeAm) {
                        $time1 = explode(":", $configtime1);

                        $time1minutArr = explode("-", $time1[1]);
                        $time1minut = (int) $time1minutArr[0];

                        $time1 = $time1[0]; //07

                        if ($time1minut + $cutofmin > 60) {
                            $extramin = $time1minut + $cutofmin - 60;
                            // $time1++;
                            $updateTime1 = $time1 . $extramin . "00";
                        } else {
                            $updateTime1 = $time1 . $cutofmin . "00";
                        }
                        // $updateTime1 = $time1.$cutofmin.'00';
                        if ($starttimnenew <= $updateTime1 && $endtimnenew >= $updateTime1) {
                            if ($updateTime1 >= $getStoreTime) {
                                $deleveryTimeArray[] = $configtime1;
                            }
                        }
                    } elseif (strrpos($timeCheckAmPm[0], "PM")) {
                        $time1 = explode(":", $configtime1);

                        $time1minutArr = explode("-", $time1[1]);
                        $time1minut = (int) $time1minutArr[0];

                        $time1 = $time1[0]; //07
                        $time1 = (int) $time1 + (int) 12; //15

                        if ($time1minut + $cutofmin > 60) {
                            $extramin = $time1minut + $cutofmin - 60;
                            // $time1++;
                            $updateTime1 = $time1 . $extramin . "00";
                        } else {
                            $updateTime1 = $time1 . $cutofmin . "00";
                        }
                        // $updateTime1 = $time1 . $cutofmin . "00";
                        if ($endtimnenew >= $updateTime1 && $starttimnenew <= $updateTime1) {
                            if ($updateTime1 >= $getStoreTime) {
                                $deleveryTimeArray[] = $configtime1;
                            }
                        }
                    }
                } else {
                    if ($timeAm) {
                        $time1 = explode(":", $configtime1);

                        $time1 = $time1[0]; //07

                        $updateTime1 = $time1 . $cutofmin . "00";

                        if ($starttimnenew <= $updateTime1 && $endtimnenew >= $updateTime1) {
                            if ($updateTime1 >= $getStoreTime) {
                                $deleveryTimeArray[] = $configtime1;
                            }
                        }
                    } elseif (strrpos($timeCheckAmPm[0], "PM")) {
                        $time1 = explode(":", $configtime1);
                        $time1 = $time1[0]; //07
                        $time1 = (int) $time1 + (int) 12; //15
                        $updateTime1 = $time1 . $cutofmin . "00";
                        if ($endtimnenew >= $updateTime1 && $starttimnenew <= $updateTime1) {
                            if ($updateTime1 >= $getStoreTime) {
                                $deleveryTimeArray[] = $configtime1;
                            }
                        }
                    }
                }
            }

            // for starttime

            if ($starttimnenew >= $getStoreTime) {
                $deleveryTimeArray = [];
                $starttimeinoptions = str_replace(",", ":", $starttime);
                $deleveryTimeArray[] =
                    "Todays we are available after - " . $starttimeinoptions;
            }
            if ($endtimnenew <= $getStoreTime) {
                $deleveryTimeArray = [];
                $starttimeinoptions = str_replace(",", ":", $endtime);
                $deleveryTimeArray[] =
                    "todays end time - " . $starttimeinoptions;
                if ($startDate == "today") {
                    $startDate = "+1d";
                }
            }
        }

        // for other date
        $otherdeleveryTimeArray = ["Select Time"];
        foreach ($deliveryTimeOptions as $optionTime) {
            $configtime1 = $optionTime;
            $timeCheckAmPm = explode("-", $configtime1);

            $timeAm = strrpos($timeCheckAmPm[0], "AM");

            if ($dslotstatus) {
                if ($timeAm) {
                    $time1 = explode(":", $configtime1);

                    $time1minutArr = explode("-", $time1[1]);
                    $time1minut = (int) $time1minutArr[0];
                    $time1 = $time1[0]; //07
                    $updateTime1 = $time1 . ($time1minut + $cutofmin) . "00";
                    if ($starttimnenew <= $updateTime1 && $endtimnenew >= $updateTime1) {
                        $otherdeleveryTimeArray[] = $configtime1;
                    }
                } elseif (strrpos($timeCheckAmPm[0], "PM")) {
                    $time1 = explode(":", $configtime1);
                    $time1minutArr = explode("-", $time1[1]);
                    $time1minut = (int) $time1minutArr[0];
                    $time1 = $time1[0]; //07

                    if ($time1 == 12) {
                        $time1 = $time1;
                    } else {
                        $time1 = (int) $time1 + (int) 12; //15
                    }
                    $updateTime1 = $time1 . ($time1minut + $cutofmin) . "00";

                    if ($endtimnenew >= $updateTime1 && $starttimnenew <= $updateTime1) {
                        $otherdeleveryTimeArray[] = $configtime1;
                    }
                }
            } else {
                if ($timeAm) {
                    $time1 = explode(":", $configtime1);

                    $time1 = $time1[0]; //07
                    $updateTime1 = $time1 . $cutofmin . "00";
                    if ($starttimnenew <= $updateTime1 && $endtimnenew >= $updateTime1) {
                        $otherdeleveryTimeArray[] = $configtime1;
                    }
                } elseif (strrpos($timeCheckAmPm[0], "PM")) {
                    $time1 = explode(":", $configtime1);
                    $time1 = $time1[0]; //07
                    $time1 = (int) $time1 + (int) 12; //15
                    $updateTime1 = $time1 . $cutofmin . "00";
                    if ($endtimnenew >= $updateTime1 && $starttimnenew <= $updateTime1) {
                        $otherdeleveryTimeArray[] = $configtime1;
                    }
                }
            }
        }

        // provide data on config js

        $config = [
            "shipping" => [
                "delivery_date" => [
                    "format" => $format,
                    "disabled" => $disabled,
                    "active" => $active,
                    "noday" => $noday,
                    "hourMin" => $hourMin,
                    "hourMax" => $hourMax,
                    "startDate" => $startDate,
                    "endDate" => $endDate,
                    "cutofmin" => $cutofmin,
                    "starttimne" => $starttimnenew,
                ],
                "delivery_time" => [
                    "customvalue" => $deleveryTimeArray,
                ],
                "otherdelevery_time" => [
                    "customtime" => $otherdeleveryTimeArray,
                ],
            ],
        ];

        return $config;
    }

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getStoreId();
    }

    public function getStoreTime()
    {
        $formatDate = $this->timezoneInterface->formatDate();
        // you can also get format wise date and time
        $dateTime = $this->timezoneInterface->date()->format("Y-m-d H:i:s");
        $date = $this->timezoneInterface->date()->format("Y-m-d");
        $time = $this->timezoneInterface->date()->format("His");
        return $time;
    }
}
