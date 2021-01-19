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

use SQLi\Twint\Api\CheckAndInvalidateInterface;
use SQLi\Twint\Logger\TwintLogger;

/**
 * Class CheckAndInvalidate.
 *
 * @package SQLi\Twint\Model
 */
class CheckAndInvalidate implements CheckAndInvalidateInterface
{
    /**
     * @var DBSessionManagement
     */
    protected $dbSessionManagement;

    /**
     * @var TwintLogger
     */
    private $twintLogger;

    /**
     * @var Front
     */
    protected $frontConfig;

    /**
     * @var Config
     */
    protected $twintConfig;

    /**
     * CheckAndInvalidate constructor.
     *
     * @param DBSessionManagement $dbSessionManagement
     * @param Front $frontConfig
     *      * @param TwintLogger $twintLogger
     * @param Config $twintConfig
     */
    public function __construct(
        DBSessionManagement $dbSessionManagement,
        Front $frontConfig,
        TwintLogger $twintLogger,
        Config $twintConfig
    )
    {
        $this->dbSessionManagement = $dbSessionManagement;
        $this->frontConfig = $frontConfig;
        $this->twintLogger = $twintLogger;
        $this->twintConfig = $twintConfig;
    }

    /**
     * @inheritDoc
     */
    public function checkAndInvalidate($sessionId)
    {
        $this->logInformation("Verify for sessionId : " . $sessionId);
        $session = $this->dbSessionManagement->getRowBySessionId($sessionId);

        if (!$session) {
            $this->logInformation("The session with sessionId: " . $sessionId . " could not be found");
            return $this->frontConfig->get404Url();
        }

        $result = $this->dbSessionManagement->invalidSessionBySession($session);

        if ($result) {
            $customParams = $this->dbSessionManagement->getCustomParamsBySession($session);
            $this->logInformation("The session params are: " . json_encode($customParams));
            if ($customParams) {
                $cParams['customParams'] = json_decode($customParams, true);
                $cParams['customerRelationId'] = $session->getCustomerRelationId();
                return json_encode($cParams);
            }
            return $this->twintConfig->getCallbackUrl();
        }
        $this->logInformation("The session with Id: " . $sessionId . " is not valide.");
        return $this->frontConfig->get404Url();
    }

    /**
     * @param $string
     * @return bool
     */
    public function isJSON($string)
    {
        return is_string($string) &&
               is_array(json_decode($string, true)) &&
               (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    /**
     * Useful function to log message.
     *
     * @param string|array $message
     */
    private function logInformation($message)
    {
        if (!is_string($message)) {
            $this->twintLogger->debug('[TwintCheckAndInvalidate] ' . implode('<=>', $message));
        } else {
            $this->twintLogger->debug('[TwintCheckAndInvalidate] ' . $message);
        }
    }
}
