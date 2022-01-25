<?php
namespace Firstflight\Firstship\Model\Config\Source;

/**
 * @api
 * @since 100.0.2
 */
class ServiceType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'NOR', 'label' => __('Normal Service')],
            ['value' => 'IMP', 'label' => __('Import Service')]
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
            'NOR' => __('Normal Service'),
            'IMP' => __('Import Service')
        ];
    }
}
