<?php
namespace Firstflight\Firstship\Plugin\Magento\Shipping\Block\Adminhtml;

use Magento\Shipping\Block\Adminhtml\View as Subject;

/**
 * View class for add button and template of generate awb number
 * and print pdf shipment
 */
class View
{

    /**
     * constructor
     *
     * @param \Firstflight\Firstship\Helper\ConfigData $config
     */
    public function __construct(\Firstflight\Firstship\Helper\ConfigData $config)
    {
        $this->config = $config;
    }
    /**
     * before plugin for setlayout
     *
     * @param Subject $subject
     * @param mix $layout
     * @return mix
     */
    public function beforeSetLayout(
        Subject $subject,
        $layout
    ) {
        if ($this->config->isActive()) {
            $subject->addButton('generate_awb_number', [
                'label' => __('Generate AWB number'),
                'onclick' => 'openFormModel();',
                'class' => 'action-default action-generate-awb-number',
            ]);
            $subject->addButton('print_packaging_slip', [
                'label' => __('Print packaging slip'),
                'onclick' => 'openPrintModel()',
                'class' => 'action-default action-print-packaging-slip',
            ]);
        }
        return [$layout];
    }

    /**
     * after plugin for tohtml method
     *
     * @param Subject $subject
     * @param String $result
     * @return String
     */
    public function afterToHtml(Subject $subject, $result)
    {
        if ($subject->getNameInLayout() == 'sales_shipment_view') {
            if ($this->config->isActive()) {
                $customBlockHtml = $subject->getLayout()->createBlock(
                    \Firstflight\Firstship\Block\Adminhtml\AwbTemplate::class,
                    $subject->getNameInLayout().'_modal_box'
                )
                ->setShipping($subject->getShipment())
                ->setFormUrl(
                    $subject->getUrl('firstflight/action/generateawbnumber', [
                        'shipment_id' => $subject->getShipment()->getId(),
                        'order_id' => $subject->getShipment()->getOrderId(),
                    ])
                )->setTemplate('Firstflight_Firstship::order/modalbox.phtml')->toHtml();
                return $result.$customBlockHtml;
            }
        }
        return $result;
    }
}
