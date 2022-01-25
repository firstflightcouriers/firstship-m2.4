<?php
namespace Firstflight\Firstship\Plugin\Magento\Shipping\Block\Adminhtml\Order;

use Magento\Shipping\Block\Adminhtml\Order\Tracking as Subject;

class Tracking
{
    /**
     * Add first ship to tracking method list in admin
     *
     * @param Subject $subject
     * @param Array $result
     * @return Array
     */
    public function afterGetCarriers(Subject $subject, $result)
    {
        return array_merge($result, [
            'firstship' => 'Firstflight Firstship'
        ]);
    }
}
