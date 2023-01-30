<?php

namespace Vendor\FreeShipping\Model;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;

class Carrier extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    const CARRIER_CODE = 'vendor_freeshipping';

    protected $_code = self::CARRIER_CODE;
    protected $scopeConfig;
    protected $_rateResultFactory;
    protected $_rateMethodFactory;
    protected $_rateErrorFactory;


    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_rateErrorFactory = $rateErrorFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_rateResultFactory = $rateResultFactory;
        $this->logger = $logger;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }
    
    

    public function collectRates(RateRequest $request)
    {
        $allowedSkus = explode(',', $this->scopeConfig->getValue('carriers/free_shipping/allowed_skus'));
        $isActive = $this->scopeConfig->getValue('carriers/free_shipping/active');
        $ShippingMethodCode = $this->_code;

        if (!$isActive) {
            return false;
        }

        $items = $request->getAllItems();
        foreach ($items as $item) {

            if (in_array($item->getSku(), $allowedSkus)) {
                $this->_logger->debug('in the allowed array');
                $result = $this->_rateResultFactory->create();
                $method = $this->_rateMethodFactory->create();
                $method->setCarrier($ShippingMethodCode);
                $method->setCarrierTitle($this->getConfigData('title'));
                $method->setMethod($ShippingMethodCode);
                $method->setMethodTitle($this->getConfigData('name'));
                $method->setPrice(0);
                $method->setCost(0);
                $result->append($method);
                return $result;
            }
        }
        return false;
    }

    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
}
