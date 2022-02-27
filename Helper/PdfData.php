<?php
namespace Firstflight\Firstship\Helper;

use Zend\Barcode\Barcode;
use Magento\Framework\App\Filesystem\DirectoryList;

class PdfData extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $config;
    protected $orderAddressRepositoryInterface;
    protected $shipmentRepositoryInterface;
    protected $pdf;
    protected $data;
    protected $moduleDir;
    protected $filesystem;
    protected $directoryList;
    protected $shippingId;
    /**
     * constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepositoryInterface
     * @param \Magento\Sales\Api\OrderAddressRepositoryInterface $orderAddressRepositoryInterface
     * @param \Magento\Framework\Module\Dir $moduleDir
     * @param \Magento\Framework\Filesystem $filesystem
     * @param ConfigData $config
     * @param Data $data
     * @param DirectoryList $directoryList
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepositoryInterface,
        \Magento\Sales\Api\OrderAddressRepositoryInterface $orderAddressRepositoryInterface,
        \Magento\Framework\Module\Dir $moduleDir,
        \Magento\Framework\Filesystem $filesystem,
        ConfigData $config,
        Data $data,
        DirectoryList $directoryList
    ) {
        $this->shipmentRepositoryInterface = $shipmentRepositoryInterface;
        $this->orderAddressRepositoryInterface = $orderAddressRepositoryInterface;
        $this->config = $config;
        $this->data = $data;
        $this->moduleDir = $moduleDir;
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;

        parent::__construct($context);
    }

    public function initPdf()
    {
        $this->pdf = new \Zend_Pdf();
        $this->pdf->pages[] = $this->pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
        $this->page = $this->pdf->pages[0]; // this will get reference to the first page.

        $this->style = new \Zend_Pdf_Style();
        $this->style->setLineColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        $this->pageWidth = $this->page->getWidth(); //595
        $this->pageHight = $this->page->getHeight(); //842

        $this->page->setLineWidth(1.5);

        $this->x = 30;
        $this->y = 770;
    }

    public function drawBorders()
    {
        $this->page->drawRectangle(
            10,
            10,
            $this->pageWidth - 10,
            $this->pageHight - 10,
            \Zend_Pdf_Page::SHAPE_DRAW_STROKE
        );

        $this->page->drawLine(
            $this->x - 20,
            $this->y - 610,
            $this->x + 555,
            $this->y - 610
        );

        // drow dashed line
        $this->page->setLineDashingPattern(
            [1.5],
            1.6
        );

        $this->page->drawLine(
            $this->x - 20,
            $this->y - 60,
            $this->x + 555,
            $this->y - 60
        ); // where

        $this->page->drawLine(
            $this->x - 20,
            $this->y - 120,
            $this->x + 555,
            $this->y - 120
        );

        $this->page->drawLine(
            $this->x - 20,
            $this->y - 215,
            $this->x + 555,
            $this->y - 215
        );

        $this->page->drawLine(
            $this->x - 20,
            $this->y - 280,
            $this->x + 555,
            $this->y - 280
        );

        $this->page->drawLine(
            $this->x - 20,
            $this->y - 470,
            $this->x + 555,
            $this->y - 470
        );

        $this->page->drawLine(
            $this->x + 270,
            $this->y + 60,
            $this->x + 270,
            $this->y - 60
        ); // vertical - First logo row

        $this->page->drawLine(
            $this->x + 270,
            $this->y - 60,
            $this->x + 270,
            $this->y - 120
        ); // vertical

        $this->page->drawLine(
            $this->x + 270,
            $this->y - 60,
            $this->x + 270,
            $this->y - 120
        ); // vertical Service type

        $this->page->drawLine(
            $this->x + 270,
            $this->y - 60,
            $this->x + 270,
            $this->y - 215
        ); // vertical Service

        $this->page->drawLine(
            $this->x + 300,
            $this->y - 170,
            $this->x + 300,
            $this->y - 170
        ); //sa
        
        $this->page->drawLine(
            $this->x + 270,
            $this->y - 215,
            $this->x + 270,
            $this->y - 120
        ); // vertical 

        $this->page->drawLine(
            $this->x + 270,
            $this->y - 60,
            $this->x + 270,
            $this->y - 280
        ); // vertical 

        $this->page->drawLine(
            $this->x + 270,
            $this->y - 280,
            $this->x + 270,
            $this->y - 470
        ); // vertical sender - reciever
    }

    public function addLogo()
    {
        // Add logo image
        $viewPath = $this->moduleDir->getDir('Firstflight_Firstship', \Magento\Framework\Module\Dir::MODULE_VIEW_DIR);
        $logoFile = DIRECTORY_SEPARATOR."logo.png";
        $filePath = DIRECTORY_SEPARATOR."adminhtml".DIRECTORY_SEPARATOR."web".DIRECTORY_SEPARATOR."image".$logoFile;
        $imagePath = $viewPath.$filePath;
        
        $image = \Zend_Pdf_Image::imageWithPath($imagePath);
        
        //top border of the page
        $widthLimit = 226;
        //half of the page width
        $heightLimit = 92;
        //assuming the image is not a "skyscraper"
        $width = $image->getPixelWidth();
        $height = $image->getPixelHeight();
        
        //preserving aspect ratio (proportions)
        $ratio = $width / $height;
        if ($ratio > 1 && $width > $widthLimit) {
            $width = $widthLimit;
            $height = $width / $ratio;
        } elseif ($ratio < 1 && $height > $heightLimit) {
            $height = $heightLimit;
            $width = $height * $ratio;
        } elseif ($ratio == 1 && $height > $heightLimit) {
            $height = $heightLimit;
            $width = $widthLimit;
        }
        
        $y1 = 800 - $height;
        $y2 = 800;
        $x1 = 330;
        $x2 = $x1 + $width;
        
        //coordinates after transformation are rounded by Zend
        $this->page->drawImage(
            $image,
            $x1,
            $y1,
            $x2,
            $y2
        );
    }

    public function addBarcode($awbNumber)
    {
        // Only the text to draw is required
        $barcodeOptions = ['text' => $awbNumber];

        // No required options
        $rendererOptions = [];

        $code = Barcode::factory(
            'code39',
            'image',
            $barcodeOptions,
            $rendererOptions
        )->draw();

        $path  =  $this->directoryList->getPath(DirectoryList::VAR_DIR);
        $imagePath = $path.'/barcode.png';
        $imagepng = "imagepng";
        $imagedestroy = "imagedestroy";
        $imagepng($code, $imagePath);
        $imagedestroy($code);
                    
        $image = \Zend_Pdf_Image::imageWithPath($imagePath);

        $width = $image->getPixelWidth();
        $height = $image->getPixelHeight();

        $y1 = $this->pageHight - 730 - $height;
        $y2 = $this->pageHight - 730;
        $x1 = 280;
        $x2 = $x1 + $width;
        
        //coordinates after transformation are rounded by Zend
        $this->page->drawImage(
            $image,
            $x1,
            $y1,
            $x2,
            $y2
        );

        $dir = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $dir->delete($imagePath);
    }

    public function preparePdf($data)
    {
        $rootDirectory = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        $viewPath = $this->moduleDir->getDir('Firstflight_Firstship', \Magento\Framework\Module\Dir::MODULE_VIEW_DIR);
        $filePath = $viewPath.DIRECTORY_SEPARATOR."adminhtml".
        DIRECTORY_SEPARATOR."web".DIRECTORY_SEPARATOR."fonts".DIRECTORY_SEPARATOR;
        
        $font = \Zend_Pdf_Font::fontWithPath(
            $filePath.'GnuFreeFont/FreeSerif.ttf'
        );
        $this->style->setFont($font, 40);
        $this->page->setStyle($this->style);
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#0a0b1d'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText(__("ORGN"), 60, $this->pageHight - 160, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithPath(
            $filePath.'Roboto/Roboto-Medium.ttf'
        );
        $this->style->setFont($font, 30);
        $this->page->setStyle($this->style);
        //$this->page->drawText(__("AE-DXB"), 80, 760, 'UTF-8'); SendersCity
         $this->page->drawText($data['AirwayBillData']['SendersCountry'].' - '.$data['AirwayBillData']['SendersSubCity'], 80, 760, 'UTF-8');
 
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#626a70'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText($data['AirwayBillData']['SendersCity'], 60, $this->pageHight - 185, 'UTF-8');

       
        $font = \Zend_Pdf_Font::fontWithPath(
            $filePath.'GnuFreeFont/FreeSerif.ttf'
        );
        $this->style->setFont($font, 40);
        $this->page->setStyle($this->style);
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#0a0b1d'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText(__("DEST"), 190, $this->pageHight - 160, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithPath(
            $filePath.'Roboto/Roboto-Medium.ttf'
        );
        $this->style->setFont($font, 40);
        $this->page->setStyle($this->style);
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#626a70'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText($data['AirwayBillData']['ReceiversCity'], 190, $this->pageHight - 185, 'UTF-8');
       

        $font = \Zend_Pdf_Font::fontWithPath(
            $filePath.'GnuFreeFont/FreeSerif.ttf'
        );
        $this->style->setFont($font, 40);
        $this->page->setStyle($this->style);
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#0a0b1d'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText(__("ACCOUNT NUMBER"), 355, $this->pageHight - 160, 'UTF-8');


        $font = \Zend_Pdf_Font::fontWithPath(
            $filePath.'Roboto/Roboto-Medium.ttf'
        );
        $this->style->setFont($font, 40);
        $this->page->setStyle($this->style);
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#626a70'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText($data['AirwayBillData']['AccountNo'], 435, $this->pageHight - 185, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithPath(
             $filePath.'GnuFreeFont/FreeSerif.ttf'
        );
        $this->style->setFont($font, 40);
        $this->page->setStyle($this->style);
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#0a0b1d'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText(__("DOMESTIC"), 35, $this->pageHight - 230, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithPath(
            $filePath.'Roboto/Roboto-Medium.ttf'
        );
        $this->style->setFont($font, 40);
        $this->page->setStyle($this->style);
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#626a70'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText(__("PKGT"), 60, $this->pageHight - 265, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithPath(
             $filePath.'GnuFreeFont/FreeSerif.ttf'
        );
        $this->style->setFont($font, 40);
        $this->page->setStyle($this->style);
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#0a0b1d'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText(__("PARCEL"), 188, $this->pageHight - 230, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithPath(
            $filePath.'Roboto/Roboto-Medium.ttf'
        );
        $this->style->setFont($font, 40);
        $this->page->setStyle($this->style);
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#626a70'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText(__("PDTT"), 188, $this->pageHight - 265, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithPath(
             $filePath.'GnuFreeFont/FreeSerif.ttf'
        );
        $this->style->setFont($font, 40);
        $this->page->setStyle($this->style);
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#0a0b1d'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText(__("SERVICE TYPE"), 355, $this->pageHight - 230, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithPath(
            $filePath.'Roboto/Roboto-Medium.ttf'
        );
        $this->style->setFont($font, 40);
        $this->page->setStyle($this->style);
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#626a70'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText($data['AirwayBillData']['ServiceType'], 410, $this->pageHight - 265, 'UTF-8');

       $this->page->setLineDashingPattern(
            [5],
            1.6
        );

        if ($data['AirwayBillData']['ServiceType'] == "CAD") {
            $this->page->drawText(__("Amount"), $this->x + 425, $this->pageHight - 265, 'UTF-8');

            $font = \Zend_Pdf_Font::fontWithPath(
                 $filePath.'GnuFreeFont/FreeSerif.ttf'
            );
            $this->style->setFont($font, 40);
            $this->page->setStyle($this->style);
            $this->style->setFillColor(new \Zend_Pdf_Color_Html('#0a0b1d'));
            $this->style->setFont($font, 20);
            $this->page->setStyle($this->style);
            $this->page->drawText(
                substr($this->data->roundPrice(
                    $data['AirwayBillData']['ShipmentInvoiceValue']
                ), 0, -3),
                $this->x + 430,
                $this->pageHight - 275,
                'UTF-8'
            );
        }
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#0a0b1d'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText(__("Ref:"), $this->x + 5, $this->pageHight - 315, 'UTF-8');

        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#626a70'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText(
            $data['AirwayBillData']['ShipperReference'],
            $this->x + 65,
            $this->pageHight - 315,
            'UTF-8'
        );

        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#0a0b1d'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText(__("Date:"), $this->x + 5, $this->pageHight - 345, 'UTF-8');

        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#626a70'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText($data['shipping_create_at'], $this->x + 65, $this->pageHight - 345, 'UTF-8');
        
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#0a0b1d'));
        $this->style->setFont($font, 21);
        $this->page->setStyle($this->style);
        $this->page->drawText(__("PCS"), $this->x + 327, $this->pageHight - 310, 'UTF-8');
        
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#626a70'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText($this->data->roundPrice(
            $data['AirwayBillData']['NumberofPeices'],
            false
        ), $this->x + 343, $this->pageHight - 340, 'UTF-8');

        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#0a0b1d'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText(__("WT"), $this->x + 470, $this->pageHight - 310, 'UTF-8');        

        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#626a70'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText(__("%1 Kgs", $this->data->roundPrice(
            $data['AirwayBillData']['Weight'],
            false
        )), $this->x + 460, $this->pageHight - 340, 'UTF-8');        
      

        $font = \Zend_Pdf_Font::fontWithPath(
            $filePath.'Roboto/Roboto-Regular.ttf'
        );
        $this->page->setStyle($this->style);
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#0a0b1d'));
        $this->style->setFont($font, 18);
        $this->page->setStyle($this->style);
        $this->page->drawText(__("Sender"), $this->x - 6, $this->pageHight - 375, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithPath(
            $filePath.'LinLibertineFont/LinLibertine_Bd-2.8.1.ttf'
        );
        $this->page->setStyle($this->style);
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#0a0b1d'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText(
            $data['AirwayBillData']['SendersContactPerson'],
            $this->x - 6,
            $this->pageHight - 410,
            'UTF-8'
        );

        $this->page->setStyle($this->style);
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#626a70'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText(
            $data['AirwayBillData']['SendersContactPerson'],
            $this->x - 6,
            $this->pageHight - 440,
            'UTF-8'
        );
        $this->page->drawText(
            $data['AirwayBillData']['SendersAddress1'],
            $this->x - 6,
            $this->pageHight - 460,
            'UTF-8'
        );
        $this->page->drawText(
            $data['AirwayBillData']['SendersAddress2'],
            $this->x - 6,
            $this->pageHight - 480,
            'UTF-8'
        );
        $this->page->drawText(
            $data['AirwayBillData']['SendersCity'].' , '.$data['AirwayBillData']['OriginCountry'],
            $this->x - 6,
            $this->pageHight - 500,
            'UTF-8'
        );

        $this->style->setFont($font, 24);
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#0a0b1d'));
        $this->page->setStyle($this->style);
        $this->page->drawText(__("Ph: "), $this->x - 6, $this->pageHight - 530, 'UTF-8');
        $this->page->drawText(
            $data['AirwayBillData']['SendersPhone'],
            $this->x + 30,
            $this->pageHight - 530,
            'UTF-8'
        );

        $font = \Zend_Pdf_Font::fontWithPath(
            $filePath.'Roboto/Roboto-Regular.ttf'
        );
        $this->page->setStyle($this->style);
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#0a0b1d'));
        $this->style->setFont($font, 18);
        $this->page->setStyle($this->style);
        $this->page->drawText(__("Reciever"), $this->x + 280, $this->pageHight - 375, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithPath(
            $filePath.'LinLibertineFont/LinLibertine_Bd-2.8.1.ttf'
        );
        $this->page->setStyle($this->style);
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#0a0b1d'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText(
            $data['AirwayBillData']['ReceiversContactPerson'],
            $this->x + 280,
            $this->pageHight - 410,
            'UTF-8'
        );

        $this->page->setStyle($this->style);
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#626a70'));
        $this->style->setFont($font, 20);
        $this->page->setStyle($this->style);
        $this->page->drawText(
            $data['AirwayBillData']['ReceiversContactPerson'],
            $this->x + 280,
            $this->pageHight - 440,
            'UTF-8'
        );
        $this->page->drawText(
            $data['AirwayBillData']['ReceiversAddress1'],
            $this->x + 280,
            $this->pageHight - 460,
            'UTF-8'
        );
        $this->page->drawText(
            $data['AirwayBillData']['ReceiversAddress2'],
            $this->x + 280,
            $this->pageHight - 480,
            'UTF-8'
        );
        $this->page->drawText(
            $data['AirwayBillData']['ReceiversCity'].' , '.$data['AirwayBillData']['ReceiversCountry'],
            $this->x + 280,
            $this->pageHight - 500,
            'UTF-8'
        );        
 
        $this->style->setFont($font, 24);
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#000000'));
        $this->page->setStyle($this->style);
        $this->page->drawText(__("Ph: "), $this->x + 280, $this->pageHight - 530, 'UTF-8');
        $this->page->drawText(
            $data['AirwayBillData']['ReceiversPhone'],
            $this->x + 320,
            $this->pageHight - 530,
            'UTF-8'
        );

        $this->style->setFont($font, 20);
        $this->style->setFillColor(new \Zend_Pdf_Color_Html('#0a0b1d'));
        $this->page->setStyle($this->style);
        $this->page->drawText(__("Description:"), $this->x - 6, $this->pageHight - 570, 'UTF-8');
        $this->page->drawText(
            $data['AirwayBillData']['GoodsDescription'],
            $this->x + 110,
            $this->pageHight - 570,
            'UTF-8'
        );
        
        $this->page->drawText(__("Invoice value:"), $this->x - 6, $this->pageHight - 595, 'UTF-8');
        $this->page->drawText(
            substr($this->data->roundPrice(
                $this->data->getInvoiceValueByshippingId($this->shippingId)
            ), 0, -3),
            $this->x + 120,
            $this->pageHight - 595,
            'UTF-8'
        );

        $this->page->drawText(__("Instruction:"), $this->x - 6, $this->pageHight - 620, 'UTF-8');
        $this->page->drawText(
            $data['AirwayBillData']['SpecialInstruction'],
            $this->x + 110,
            $this->pageHight - 620,
            'UTF-8'
        );

        $this->style->setFont($font, 22);
        $this->page->setStyle($this->style);
        $this->page->drawText(__("Airwaybill Number"), $this->x - 6, $this->pageHight - 765, 'UTF-8');
    }

    public function getPdf($track)
    {
        $this->initPdf();
        $this->drawBorders();
        $this->addLogo();
        $this->shippingId = $track->getParentId();
        $data = $this->data->getCreateAirwayBillData($this->shippingId);
        $data['shipping_create_at'] = $this->data->getShippingById($this->shippingId)->getCreatedAt();
        $this->preparePdf($data);
        $this->addBarcode($track->getTrackNumber());
        return $this->pdf;
    }
}
