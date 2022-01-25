<?php

namespace Firstflight\Firstship\Model;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * Custom shipping model
 */
class Carrier extends AbstractCarrier implements CarrierInterface
{
    /** @var string */
    protected $_methodCode = 'firstflight';
    
    /** @var string */
    protected $_carrierCode = 'firstship';

    /** @var string */
    protected $_code = 'firstship';

    /** @var bool */
    protected $_isFixed = true;

    /** @var \Magento\Shipping\Model\Rate\ResultFactory */
    private $rateResultFactory;
    
    /** @var \Magento\Framework\UrlInterface */
    private $urlBuilder;

    /** @var \Magento\Framework\Url\EncoderInterface */
    protected $urlEncoder;

    /** @var \Firstflight\Firstship\Helper\Data */
    private $dataHelper;

    /** @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory */
    private $rateMethodFactory;

    /**
     * constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Firstflight\Firstship\Helper\Data $dataHelper
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Firstflight\Firstship\Helper\Data $dataHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->dataHelper = $dataHelper;
        $this->urlEncoder = $urlEncoder;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Custom Shipping Rates Collector
     *
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        
        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_carrierCode);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_methodCode);
        $method->setMethodTitle($this->getConfigData('name'));

        $data = [
            'dest_country_id' => $request->getDestCountryId(),
            'package_weight' => $request->getPackageWeight(),
            'country_id' => $request->getCountryId()
        ];
        
        $rate = $this->dataHelper->getRateFinder($data);
        $shippingCost = (float)$rate['NetAmount'];
        
        $method->setPrice($shippingCost);
        $method->setCost($shippingCost);

        $result->append($method);

        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_methodCode => $this->getConfigData('name')];
    }

    /**
     * get tracking info object
     *
     * @param int $trackingNumber
     * @return \Magento\Framework\DataObject
     */
    public function getTrackingInfo($trackingNumber)
    {
        $tracking = new \Magento\Framework\DataObject();
        $tracking->setData([
            'carrier' => $this->_code,
            'carrier_title' => $this->getConfigData('title'),
            'tracking' => $trackingNumber,
            'url' => $this->_getTrackingUrl($trackingNumber)
        ]);
        return $tracking;
    }

    /**
     * get tracking url
     *
     * @param string $trackingNumber
     * @return string
     */
    protected function _getTrackingUrl($trackingNumber)
    {
        $params = [
            '_nosid' => true,
            '_direct' => 'firstflight/tracking/info',
            '_query' => ['hash' => $this->urlEncoder->encode($trackingNumber)]
        ];

        return $this->urlBuilder->getUrl('', $params);
    }
}
