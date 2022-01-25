<?php
namespace Firstflight\Firstship\Helper;

class ConfigData extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ACTIVE = "carriers/firstship/active";
    const ALLOW_COUNTRY = "carriers/firstship/specificcountry";
    const IS_SANDBOX = "carriers/firstship/sandbox_mode";
    const ACCOUNT_NO = "carriers/firstship/account_no";
    const LIVE_USERNAME = "carriers/firstship/live_api_username";
    const LIVE_PASSWORD = "carriers/firstship/live_api_password";
    const SANDBOX_USERNAME = "carriers/firstship/sandbox_api_username";
    const SANDBOX_PASSWORD = "carriers/firstship/sandbox_api_password";
    const ALLOW_SPECIFIC = "carriers/firstship/sallowspecific";
    const SHIPPING_TYPE = "carriers/firstship/shipping_type";
    const SERVICE_TYPE = "carriers/firstship/service_type";

    const ORIGIN_ADDRESS1 = "shipping/origin/street_line1";
    const ORIGIN_ADDRESS2 = "shipping/origin/street_line2";
    const ORIGIN_CITY = "shipping/origin/city";
    const ORIGIN_POSTCODE = "shipping/origin/postcode";
    const ORIGIN_COUNTRY_ID = "shipping/origin/country_id";
    const ORIGIN_PHONE = "general/store_information/phone";
    const ORIGIN = "carriers/firstship/origin";
    
    const STORE_GENERAL_EMAIL_SENDER_NAME = "trans_email/ident_general/name";
    const STORE_GENERAL_EMAIL = "trans_email/ident_general/email";
    
    const STORE_NAME = "general/store_information/name";

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $scopeConfig;

    /** @var \Magento\Backend\Model\Auth\Session */
    protected $authSession;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Backend\Model\Auth\Session $authSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        $this->authSession = $authSession;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function isActive()
    {
        return $this->getConfigData(self::ACTIVE);
    }

    /**
     * get config value
     *
     * @param string $path
     * @return string
     */
    public function getConfigData($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get admin user
     *
     * @return \Magento\User\Model\User
     */
    public function getCurrentUser()
    {
        return $this->authSession->getUser();
    }

    /**
     * check is sendbox
     *
     * @return boolean
     */
    public function isSandbox()
    {
        return $this->getConfigData(self::IS_SANDBOX);
    }

    /**
     * get account number from config
     *
     * @return string
     */
    public function getAccountNo()
    {
        return $this->getConfigData(self::ACCOUNT_NO);
    }

    /**
     * get username from config
     *
     * @return string
     */
    public function getUserName()
    {
        if ($this->isSandbox()) {
            return $this->getConfigData(self::SANDBOX_USERNAME);
        } else {
            return $this->getConfigData(self::LIVE_USERNAME);
        }
    }

    /**
     * get password from config
     *
     * @return string
     */
    public function getPassword()
    {
        if ($this->isSandbox()) {
            return $this->getConfigData(self::SANDBOX_PASSWORD);
        } else {
            return $this->getConfigData(self::LIVE_PASSWORD);
        }
    }

    /**
     * get shipping type from config
     *
     * @return string
     */
    public function getShippingType()
    {
        return $this->getConfigData(self::SHIPPING_TYPE);
    }

    /**
     * get service type from config
     *
     * @return string
     */
    public function getServiceType()
    {
        return $this->getConfigData(self::SERVICE_TYPE);
    }

    /**
     * get origin country id from config
     *
     * @return void
     */
    public function getOriginCountryId()
    {
        return $this->getConfigData(self::ORIGIN_COUNTRY_ID);
    }

    /**
     * get origin address 1 from config
     *
     * @return string
     */
    public function getOriginAddress1()
    {
        return $this->getConfigData(self::ORIGIN_ADDRESS1);
    }

    /**
     * get origin address 2 from config
     *
     * @return string
     */
    public function getOriginAddress2()
    {
        return $this->getConfigData(self::ORIGIN_ADDRESS2);
    }

    /**
     * get origin city from config
     *
     * @return string
     */
    public function getOriginCity()
    {
        return $this->getConfigData(self::ORIGIN_CITY);
    }

    /**
     * get origin phone from config
     *
     * @return string
     */
    public function getOriginPhone()
    {
        return $this->getConfigData(self::ORIGIN_PHONE);
    }

    /**
     * get origin postcode from config
     *
     * @return string
     */
    public function getOriginPostCode()
    {
        return $this->getConfigData(self::ORIGIN_POSTCODE);
    }

    /**
     * get origin postcode from config
     *
     * @return string
     */
    public function getOrigin()
    {
        return $this->getConfigData(self::ORIGIN);
    }

    /**
     * get store general from config
     *
     * @return string
     */
    public function getStoreGeneralEmail()
    {
        return $this->getConfigData(self::STORE_GENERAL_EMAIL);
    }

    /**
     * get store general email sender name from config
     *
     * @return string
     */
    public function getStoreGeneralEmailSenderName()
    {
        return $this->getConfigData(self::STORE_GENERAL_EMAIL_SENDER_NAME);
    }

    /**
     * get store name from config
     *
     * @return string
     */
    public function getStoreName()
    {
        return $this->getConfigData(self::STORE_NAME);
    }
}
