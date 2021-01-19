<?php
/**
 * SQLi_AddressVerification extension.
 *
 * @category   SQLi
 * @package    SQLi_AddressVerification
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\AddressVerification\Helper\Oauth;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class Activator
 * @package SQLi\AddressVerification\Helper\Oauth
 */
class Activator
{
    protected static $instance;

    public $params;

    const SUCCESS_CODE = 200;

    const STATUS_SUCCESS = 'success';

    const STATUS_ERROR = 'error';

    /**
     * Default constructor.
     * Set the static variables.
     *
     * @param array $params
     */
    public function __construct($params = array())
    {
        $this->setParams($params);
    }

    /**
     * Method to set APP Environment Params
     * @param array $params configuration array
     *
     * @return void
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * Function to build the authorization URL.
     * It fetches the necessary app id and secret from the saved options.
     *
     * @return array|string
     *
     * */
    public function getAuthorizeUrl()
    {
        if (!array_key_exists('clientId', $this->params) ||
            !array_key_exists('oauthServer', $this->params) ||
            !array_key_exists('clientScope', $this->params) ||
            !array_key_exists('redirectUri', $this->params)
        ) {
            return [
                'status' => 'error',
                'message' => 'Authorization Param(s) missing'
            ];
        }

        $clientId = $this->params['clientId'];
        $oauthServer = $this->params['oauthServer'];
        $clientScope = $this->params['clientScope'];

        $requestUrl  = $oauthServer;
        $requestUrl .= "/authorization?response_type=code";
        $requestUrl .= "&client_id=" . $clientId;
        $requestUrl .= "&scope=" . $clientScope ."+openid+profile+email+address";

        if (isset($this->params['redirectUri']) && !empty($this->params['redirectUri'])) {
            $requestUrl .= "&redirect_uri=" . $this->params['redirectUri'];
        }
        return $requestUrl;
    }

    /**
     * Function to handle authorize callback.
     * Checks for received authorization code,
     * And Sends the code using Post to  authorization server.
     *
     * If success set variables and return true to controller.
     * If Fails return empty array.
     *
     * @param $authorizationCode
     * @return array          Response array
     * @throws GuzzleException
     */
    public function handleAuthorizeCallback($authorizationCode)
    {
        if (!empty($authorizationCode)) {
            $response = $this->fetchAccessTokens($authorizationCode);
            if (self::STATUS_SUCCESS === $response['status']) {
                return $response;
            }
        }
        return [];
    }

    /**
     * Function to get a new set of token tokens
     * from an previous refresh token
     * @param string $refreshToken saved refresh token
     * @return array response
     * @throws GuzzleException
     */
    public function refreshTokens($refreshToken)
    {
        if (empty($refreshToken)) {
            return [
                'status' => 'error',
                'message' => 'Refresher token is required'
            ];
        }

        $clientId = $this->params['clientId'];
        $clientSecret = $this->params['clientSecret'];
        $oauthServer = $this->params['oauthServer'];

        $grantType = 'refresh_token';

        $url = $oauthServer . "/token";
        $requestArgs['form_params'] = array(
            'refresh_token' => $refreshToken,
            'grant_type' => $grantType,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        );

        try{
            $guzzleClient = new Client();
            $response = $guzzleClient->request('POST', $url, $requestArgs);
        } catch(Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
        return $this->processResponse($response);
    }

    /**
     * Function to get a new set of tokens
     * @return array response
     * @throws GuzzleException
     */
    public function fetchAccessTokens()
    {
        $clientId = $this->params['clientId'];
        $clientSecret = $this->params['clientSecret'];
        $oauthServer = $this->params['oauthServer'];
        $redirectUri = $this->params['redirectUri'];
        $clientScope = $this->params['clientScope'];

        $grantType = 'client_credentials';

        //Request Token
        $url = $oauthServer . "/token";
        $requestArgs['form_params'] = array(
            'grant_type' =>     $grantType,
            'client_id' =>      $clientId,
            'client_secret' =>  $clientSecret,
            'redirect_uri' =>   $redirectUri,
            'scope' => $clientScope,
        );

        try{
            $guzzleClient = new Client();
            $response = $guzzleClient->request('POST', $url, $requestArgs);
        } catch(Exception $e) {
           return [
                'status' => 'unsuccessful',
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }
        return $this->processResponse($response);
    }

    /**
     * Method to process guzzle client Response
     * and return results to be saved.
     *
     * @param  object $response Guzzle Response Object.
     * @return array response
     */
    protected function processResponse($response)
    {
        $code = $response->getStatusCode();
        $result = array();
        if (self::SUCCESS_CODE === $response->getStatusCode()) {
            $body = $response->getBody();
            $result['status'] = self::STATUS_SUCCESS;
            $result['body'] = json_decode($body);
        } else {
            $result['status'] = self::STATUS_ERROR;
            $result['error'] = array(
                'code' => $code,
                'message' => $response->getReasonPhrase()
            );
        }
        return $result;
    }
}

