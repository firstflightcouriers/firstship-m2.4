<?php
namespace Firstflight\Firstship\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /** @var \Magento\Framework\Message\ManagerInterface */
    public $messageManager;
    
    /** @var \Firstflight\Firstship\Model\Webservice */
    protected $webService;
    
    /** @var ConfigData */
    public $config;
    
    /** @var \Magento\Framework\Pricing\Helper\Data */
    public $priceData;

    protected $regionFactory;

    protected $countryFactory;

    /** @var \Magento\Sales\Api\OrderAddressRepositoryInterface */
    protected $orderAddressRepositoryInterface;
    
    /** @var \Magento\Sales\Api\ShipmentRepositoryInterface */
    protected $shipmentRepositoryInterface;

    const API = "http://mobapp.firstflightme.com/FirstFlightService.svc";

    /**
     * constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepositoryInterface
     * @param \Firstflight\Firstship\Model\Webservice $webService
     * @param \Magento\Sales\Api\OrderAddressRepositoryInterface $orderAddressRepositoryInterface
     * @param \Magento\Framework\Pricing\Helper\Data $priceData
     * @param ConfigData $config
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepositoryInterface,
        \Firstflight\Firstship\Model\Webservice $webService,
        \Magento\Sales\Api\OrderAddressRepositoryInterface $orderAddressRepositoryInterface,
        \Magento\Framework\Pricing\Helper\Data $priceData,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        ConfigData $config
    ) {
        $this->messageManager = $messageManager;
        $this->shipmentRepositoryInterface = $shipmentRepositoryInterface;
        $this->config = $config;
        $this->priceData = $priceData;
        $this->webService = $webService;
        $this->orderAddressRepositoryInterface = $orderAddressRepositoryInterface;
        $this->regionFactory = $regionFactory;
        $this->_countryFactory = $countryFactory;
        parent::__construct($context);
    }

    /**
     * get tracking info from firstflight
     *
     * @param integer $trackingNo
     * @return mixed
     */
    public function getTracking($trackingNo = 12345)
    {
        $data = [
            'TrackingAWB'=> $trackingNo,
            'UserName' => $this->config->getUserName(),
            'Password' => $this->config->getPassword(),
            'AccountNo'=> $this->config->getAccountNo(),
            'Country'=> $this->config->getOriginCountryId()
        ];
        return $this->webService->getCurl($data, 'Tracking');
    }

    /**
     * get rate of source and destination
     *
     * @param array $data
     * @return mixed
     */
    public function getRateFinder($data)
    {
        $data = [
            "AccountNo" => $this->config->getAccountNo(),
            "Destination" => $data['dest_country_id'],
            "Dimension" => "",
            "Origin" => $this->config->getOrigin(),
            "Product" => $this->config->getShippingType(),
            "ServiceType" => $this->config->getServiceType(),
            "UserName" => $this->config->getUserName(),
            "Password" => $this->config->getPassword(),
            "Weight" => $data['package_weight'],
            "country" => $data['country_id']
        ];
        return $this->webService->getCurl($data, 'RateFinder');
    }

    /**
     * generate tracking array
     *
     * @param string $shipmentId
     * @return mixed
     */
    public function getCreateAirwayBillData($shipmentId)
    {
        $shipment = $this->getShippingById($shipmentId);
        $order = $shipment->getOrder();
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $shippingAddress = $this->getCustomerShippingAddress($shipment->getShippingAddressId());
        $billingAddress = $order->getBillingAddress()->getData();
        $region = $this->regionFactory->create()->load($billingAddress['region_id']);
        $regionCode = $region->getCode();
        $country = $this->_countryFactory->create()->loadByCode($this->config->getOriginCountryId());
        $countryName = $country->getName();
        $receiversCountry = $this->_countryFactory->create()->loadByCode($shippingAddress->getCountryId());
        $receiversCountryName = $receiversCountry->getName();

        $street = $shippingAddress->getStreet();
        foreach ($order->getAllVisibleItems() as $_item) {
              $productName = $_item->getName();
        } 

        $data =  [
            'AirwayBillData' => [
              'AirWayBillCreatedBy' => trim($this->config->getCurrentUser()->getFirstname(). " "
              .$this->config->getCurrentUser()->getLastname()),
              'CODAmount' => '0',
              'CODCurrency' => '',
              'OriginCountry' => $countryName,
              'Destination' => "",
              'DutyConsigneePay' => '0',
              'GoodsDescription' => $productName, //'ITEM DESCRIPTION',
              'NumberofPeices' => $shipment->getTotalQty(),
              'Origin' => $this->config->getOrigin(),
              'ProductType' => $this->config->getShippingType(),
              'ReceiversAddress1' => (isset($street[0]))?$street[0]:null,
              'ReceiversAddress2' => (isset($street[1]))?$street[1]:null,
              'ReceiversCity' => $shippingAddress->getCity(),
              'ReceiversSubCity' => '',
              'ReceiversCountry' => $receiversCountryName,//$shippingAddress->getCountryId(),
              'ReceiversCompany' => $shippingAddress->getCompany(),
              'ReceiversContactPerson' => trim($shippingAddress->getPrefix().' '.$shippingAddress->getFirstname().' '
              .$shippingAddress->getLastname()),
              'ReceiversEmail' => $shippingAddress->getEmail(),
              'ReceiversGeoLocation' => '',
              'ReceiversMobile' => $shippingAddress->getTelephone(),
              'ReceiversPhone' => $shippingAddress->getTelephone(),
              'ReceiversPinCode' => $shippingAddress->getPostcode(),
              'ReceiversProvince' => '',
              'SendersAddress1' => $this->config->getOriginAddress1(),
              'SendersAddress2' => $this->config->getOriginAddress2(),
              'SendersCity' => $this->config->getOriginCity(),
              'SendersSubCity' => $regionCode,
              'SendersCountry' => $this->config->getOriginCountryId(),
              'SendersCompany' => $this->config->getStoreName(),
              'SendersContactPerson' => $this->config->getStoreGeneralEmailSenderName(),
              'SendersEmail' => $this->config->getStoreGeneralEmail(),
              'SendersGeoLocation' => '',
              'SendersMobile' => '',
              'SendersPhone' => $this->config->getOriginPhone(),
              'SendersPinCode' => $this->config->getOriginPostCode(),
              'ServiceType' => ($method->getCode()=='cashondelivery')?"CAD":"NOR",
              'ShipmentDimension' => '15X20X25',
              'ShipmentInvoiceCurrency' => $order->getOrderCurrencyCode(),
              'ShipmentInvoiceValue' => $order->getGrandTotal(),
              'ShipperReference' => 'ABCDEF74',
              'ShipperVatAccount' => '',
              'SpecialInstruction' => '',
              'Weight' => $order->getWeight(),
              "AccountNo" => $this->config->getAccountNo(),
            ]
        ];
        return $data;
    }

    /**
     * generate tracking to firstflight
     *
     * @param string $shipmentId
     * @param array $postData
     * @return mixed
     */
    public function getCreateAirwayBill($shipmentId, $postData)
    {
        $data = $this->getCreateAirwayBillData($shipmentId);
        $data['AirwayBillData'] = $postData['AirwayBillData'];
        $data['UserName'] = $this->config->getUserName();
        $data['Password'] = $this->config->getPassword();
        $data['AccountNo'] = $this->config->getAccountNo();
        $data['Country'] = $this->config->getOriginCountryId();

        return $this->webService->getCurl($data, 'CreateAirwayBill');
    }

    /**
     * get shipping city list
     *
     * @param string $countryCode
     * @return mixed
     */
    public function getCity($countryCode = "")
    {
        $data['UserName'] = $this->config->getUserName();
        $data['Password'] = $this->config->getPassword();
        $data['Country'] = $countryCode;

        return $this->webService->getCurl($data, 'CityList', self::API);
    }

    /**
     * get shipping country list
     *
     * @param string $countryCode
     * @return mixed
     */
    public function getCountry($countryCode = "")
    {
        $data['UserName'] = $this->config->getUserName();
        $data['Password'] = $this->config->getPassword();
        $data['Country'] = $countryCode;

        return $this->webService->getCurl($data, 'CountryMaster', self::API);
    }

    /**
     * get customer shipping address from address id
     *
     * @param string $addressId
     * @return Magento\Sales\Model\Order\Address
     */
    public function getCustomerShippingAddress($addressId)
    {
        return $this->orderAddressRepositoryInterface->get($addressId);
    }
    
    public function getShippingById($shipmentId)
    {
        return $this->shipmentRepositoryInterface->get($shipmentId);
    }

    public function roundPrice($price, $format = true)
    {
        if ($format) {
            return $this->priceData->currency(round($price), true, false);
        } else {
            return round($price);
        }
    }

    public function getInvoiceValueByshippingId($shipmentId)
    {
        $shipping = $this->getShippingById($shipmentId);
        $price = 0;
        foreach ($shipping->getItems() as $key => $item) {
            $price += $item->getPrice();
        }
        return $price;
    }
}
