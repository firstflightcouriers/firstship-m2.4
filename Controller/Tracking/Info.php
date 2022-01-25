<?php
namespace Firstflight\Firstship\Controller\Tracking;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

class Info implements HttpGetActionInterface
{
    /** @var PageFactory */
    protected $resultPageFactory;

    /**
     * construct
     *
     * @param PageFactory $resultPageFactory
     */
    public function __construct(PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
    }
    
    /**
     * loa layout
     *
     * @return void
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
