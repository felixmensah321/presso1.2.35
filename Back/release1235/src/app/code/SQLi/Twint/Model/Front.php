<?php
/**
 * SQLi_Twint extension.
 *
 * @category   SQLi
 * @package    SQLi_Twint
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Twint\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Front.
 *
 * @package SQLi\Twint\Model
 */
class Front
{
    /**
     * XML Path Twint front
     */
    const XML_PATH_FRONT_404 = 'front/url/404';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Front constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
      ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve the URL for the 404 page.
     *
     * @return mixed
     */
    public function get404Url()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FRONT_404
        );
    }
}
