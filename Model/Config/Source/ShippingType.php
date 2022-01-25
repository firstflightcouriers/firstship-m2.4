<?php
namespace Firstflight\Firstship\Model\Config\Source;

/**
 * @api
 * @since 100.0.2
 */
class ShippingType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'DOX', 'label' => __('Documents')],
            ['value' => 'XPS', 'label' => __('Parcels')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'DOX' => __('Documents'),
            'XPS' => __('Parcels')
        ];
    }
}
