<?php
namespace Firstflight\Firstship\Model;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Marketplace\Helper\Cache;

/**
 * @api
 * @since 100.0.2
 */
class Webservice
{
    /** @var Curl */
    protected $curlClient;

    /** @var string */
    protected $urlPrefix = 'https://';

    /** @var string */
    protected $apiUrl = 'ontrack.firstflightme.com/FFCService.svc';

    /** @var Cache */
    protected $cache;

    /**
     * construct
     * @param Curl $curl
     * @param Cache $cache
     */
    public function __construct(
        Curl $curl,
        Cache $cache
    ) {
        $this->curlClient = $curl;
        $this->cache = $cache;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->urlPrefix . $this->apiUrl;
    }
    
    /**
     * get curl call
     *
     * @param Array $data
     * @param String $method
     * @return mix
     */
    public function getCurl($data, $method, $apiUrl = null)
    {
        if ($apiUrl == null) {
            $apiUrl = $this->getApiUrl() ."/". $method;
        } else {
            $apiUrl = $apiUrl ."/". $method;
        }
        try {
            $data_string = json_encode($data);

            $this->getCurlClient()->setOptions(
                [
                    CURLOPT_URL => $apiUrl,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $data_string,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json']
                ]
            );
            $this->getCurlClient()->post($apiUrl, []);
            $response = json_decode($this->getCurlClient()->getBody(), true);
            if ($response) {
                $this->getCache()->savePartnersToCache($response);
                return $response;
            } else {
                return $this->getCache()->loadPartnersFromCache();
            }
        } catch (\Exception $e) {
            return $this->getCache()->loadPartnersFromCache();
        }
    }

    /**
     * @return Curl
     */
    public function getCurlClient()
    {
        return $this->curlClient;
    }

    /**
     * @return cache
     */
    public function getCache()
    {
        return $this->cache;
    }
}
