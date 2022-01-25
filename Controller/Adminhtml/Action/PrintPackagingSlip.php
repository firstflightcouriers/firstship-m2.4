<?php
namespace Firstflight\Firstship\Controller\Adminhtml\Action;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Firstflight\Firstship\Helper\PdfData;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Sales\Api\Data\ShipmentTrackInterfaceFactory;

class PrintPackagingSlip extends Action
{
    protected $pdfData;
    protected $fileFactory;
    protected $shipmentTrackInterfaceFactory;

    public function __construct(
        Action\Context $context,
        PdfData $pdfData,
        FileFactory $fileFactory,
        ShipmentTrackInterfaceFactory $shipmentTrackInterfaceFactory
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->pdfData = $pdfData;
        $this->shipmentTrackInterfaceFactory = $shipmentTrackInterfaceFactory;
    }

    public function execute()
    {
        $trackId = $this->_request->getParam('tracking_id');
        $track = $this->shipmentTrackInterfaceFactory->create()->load($trackId);
        $pdf = $this->pdfData->getPdf($track);
        $fileContent = ['type' => 'string', 'value' => $pdf->render(), 'rm' => true];
        if (isset($track)) {
            return $this->fileFactory->create(
                'packingslip-'.$track->getTrackNumber().'.pdf',
                $fileContent,
                DirectoryList::VAR_DIR,
                'application/pdf'
            );
        }
    }
}
