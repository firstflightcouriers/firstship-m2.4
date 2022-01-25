<?php
namespace Firstflight\Firstship\Controller\Adminhtml\Action;

use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterfaceFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Firstflight\Firstship\Helper\Data;

class GenerateAwbNumber extends Action
{
    
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    /**
     * @var ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var ShipmentTrackInterfaceFactory
     */
    private $trackFactory;

    /**  @var \Firstflight\Firstship\Helper\Data  */
    private $helper;

    public function __construct(
        Action\Context $context,
        ShipmentLoader $shipmentLoader,
        ShipmentRepositoryInterface $shipmentRepository = null,
        ShipmentTrackInterfaceFactory $trackFactory = null,
        SerializerInterface $serializer = null,
        Data $helper
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->shipmentLoader = $shipmentLoader;
        $this->shipmentRepository = $shipmentRepository ?: ObjectManager::getInstance()
            ->get(ShipmentRepositoryInterface::class);
        $this->trackFactory = $trackFactory ?: ObjectManager::getInstance()
            ->get(ShipmentTrackInterfaceFactory::class);
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(SerializerInterface::class);
    }

    /**
     * Add new tracking number action.
     *
     * @return ResultInterface
     */
    public function execute()
    {
        try {
            $awb = $this->getRequest()->getParam('awb');
            if ($awb && ($awb['AirwayBillData'])) {
                if (empty($awb['AirwayBillData']['SendersPhone'])) {
                    throw new LocalizedException(__('Sender\'s phone number is missing...'));
                }
            }
            $tracking = $this->helper->getCreateAirwayBill(
                $this->getRequest()->getParam('shipment_id'),
                $this->getRequest()->getParam('awb')
            );
            
            if (isset($tracking['Code']) && $tracking['Code'] == 1) {
                $number = $tracking['AirwayBillNumber'];
            } else {
                throw new LocalizedException(__($tracking['Description']));
            }
            $carrier = 'firstship';
            $title = "Firstflight Firstship";

            if (empty($carrier)) {
                throw new LocalizedException(__('Please specify a carrier.'));
            }
            if (empty($number)) {
                throw new LocalizedException(__('Please enter a tracking number.'));
            }

            $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
            $shipment = $this->shipmentLoader->load();
            if ($shipment) {
                $track = $this->trackFactory->create()->setNumber(
                    $number
                )->setCarrierCode(
                    $carrier
                )->setTitle(
                    $title
                );
                $shipment->addTrack($track);
                $this->shipmentRepository->save($shipment);
                $this->getMessageManager()->addSuccessMessage(__("AWB number generated successfully"));
            } else {
                $this->getMessageManager()->addErrorMessage(__('We can\'t initialize shipment for adding awb number.'));
            }
        } catch (LocalizedException $e) {
            $this->getMessageManager()->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->getMessageManager()->addErrorMessage(__('Cannot add tracking number.'));
        }
        return $this->resultRedirectFactory->create()->setPath(
            'admin/order_shipment/view',
            ['shipment_id'=>$this->getRequest()->getParam('shipment_id')]
        );
    }
}
