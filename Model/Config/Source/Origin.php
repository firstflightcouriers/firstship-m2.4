<?php
namespace Firstflight\Firstship\Model\Config\Source;

/**
 * @api
 * @since 100.0.2
 */
class Origin implements \Magento\Framework\Option\ArrayInterface
{

    /** @var \Firstflight\Firstship\Helper\Data $data*/
    protected $data;

    /**
     * @param \Firstflight\Firstship\Helper\Data $data
     */
    public function __construct(
        \Firstflight\Firstship\Helper\Data $data
    ) {
        $this->data = $data;
    }

    /**
     * get city list of origin country
     *
     * @return array
     */
    public function getOriginArray()
    {
        $countryId = $this->data->config->getOriginCountryId();
        if ($countryId) {
            return $this->data->getCity($countryId);
        } else {
            return $this->data->getCity();
        }
    }

    /**
     * prepare data before return
     *
     * @return array
     */
    public function prepareData()
    {
        try {
            
            $data = $this->getOriginArray();

            $responce = array_map(function ($val) {
                return [
                    'key' => $val['CityCode'],
                    'val' => $val['CityName']
                ];
            }, $data['CityListLocation']);
        } catch (\Exception $e) {
            if ($this->data->config->isActive()) {
                $this->data->messageManager->addErrorMessage(
                    __("Check your credentials at sales > delivery methods > First flight")
                );
                $this->data->messageManager->addErrorMessage($e->getMessage());
            }
            $responce = [];
        }
        return $responce;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optArr = [];
        foreach ($this->prepareData() as $key => $value) {
            $optArr[] = [
                'value' => $value['key'],
                'label' => $value['val'],

            ];
        }
        return $optArr;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $arr = [];
        foreach ($this->prepareData() as $key => $value) {
            $arr[$value[0]['key']] = $arr[$value[0]['val']];
        }
    }
}
