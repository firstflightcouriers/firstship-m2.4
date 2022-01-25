<?php
namespace Firstflight\Firstship\Block\Tracking;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Url\DecoderInterface;
use Firstflight\Firstship\Helper\Data;
use Magento\Framework\App\RequestInterface;

class Info extends Template
{

    /** @var DecoderInterface */
    protected $urlDecoder;
    
    /** @var Data */
    protected $helper;
    
    /** @var RequestInterface */
    protected $requestInterface;

    /**
     * Construct
     *
     * @param Template\Context $context
     * @param DecoderInterface $urlDecoder
     * @param Data $helper
     * @param RequestInterface $requestInterface
     */
    public function __construct(
        Template\Context $context,
        DecoderInterface $urlDecoder,
        Data $helper,
        RequestInterface $requestInterface
    ) {
        $this->urlDecoder = $urlDecoder;
        $this->helper = $helper;
        $this->requestInterface = $requestInterface;
        parent::__construct($context);
    }

    /**
     * get tracking data
     *
     * @return mixed
     */
    public function getTrack()
    {
        $hash = $this->requestInterface->getParam('hash');
        $trackingNumber = $this->urlDecoder->decode($hash);
        return $this->helper->getTracking($trackingNumber);
    }
}
