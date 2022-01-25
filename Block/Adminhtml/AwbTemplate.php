<?php
namespace Firstflight\Firstship\Block\Adminhtml;

/**
 * block for Awb template
 */
class AwbTemplate extends \Magento\Backend\Block\Template
{
    /** @var \Magento\Sales\Model\Order\Shipment */
    public $shipping;
    
    /** @var String */
    public $formUrl;
    
    /** @var \Firstflight\Firstship\Helper\Data */
    public $dataHelper;

    /** @var CollectionFactory*/
    public $shipmentTrackCollectionFactory;

    /** @var \Magento\Framework\View\Element\Html\Select*/
    public $select;

    /**
     * construct
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Firstflight\Firstship\Helper\Data $dataHelper
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $shipmentTrackCollectionFactory
     * @param \Magento\Framework\View\Element\Html\Select $select
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Firstflight\Firstship\Helper\Data $dataHelper,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $shipmentTrackCollectionFactory,
        \Magento\Framework\View\Element\Html\Select $select
    ) {
        $this->dataHelper = $dataHelper;
        $this->shipmentTrackCollectionFactory = $shipmentTrackCollectionFactory;
        $this->select = $select;
        parent::__construct($context);
    }

    /**
     * set shipment object for letter use
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipping
     * @return self
     */
    public function setShipping(\Magento\Sales\Model\Order\Shipment $shipping) : self
    {
        $this->shipping = $shipping;
        return $this;
    }

    /**
     * get aireway form data
     *
     * @return mixed
     */
    public function getAwbFormData()
    {
        return $this->dataHelper->getCreateAirwayBillData($this->shipping->getId());
    }
    
    /**
     * set form url
     *
     * @param string $url
     * @return void
     */
    public function setFormUrl($url)
    {
        $this->formUrl = $url;
        return $this;
    }

    /**
     * get form url
     *
     * @return String
     */
    public function getFormUrl()
    {
        return $this->formUrl;
    }

    /**
     * get tracking data
     *
     * @return Array
     */
    public function getTrackData()
    {
        return $this->shipmentTrackCollectionFactory->create()
        ->addAttributeToFilter('carrier_code', 'firstship')
        ->addAttributeToFilter('parent_id', $this->shipping->getId());
    }

    public function getDestination()
    {
        $shippingAddress = $this->dataHelper->getCustomerShippingAddress(
            $this->shipping->getShippingAddressId()
        );
        
        $data = $this->dataHelper->getCity($shippingAddress->getCountryId());

        $opt = array_map(function ($val) {
            return [
                'value' => $val['CityCode'],
                'label' => $val['CityName']
            ];
        }, $data['CityListLocation']);

        $this->select->setOptions($opt);
        $this->select->setTitle('Destination');
        $this->select->setName('awb[AirwayBillData][Destination]');
        $this->select->setClass('input-select admin__control-select');

        return $this->select->toHtml();
    }
}
