<?php
/**
 * SQLi_Twint extension.
 *
 * @category   SQLi
 * @package    SQLi_Twint
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Twint\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Route\ConfigInterface;
use Magento\Framework\App\Router\ActionList;
use Magento\Framework\App\RouterInterface;

/**
 * Class Router.
 *
 * @package SQLi\Twint\Controller
 */
class Router implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var ActionList
     */
    private $actionList;

    /**
     * @var ConfigInterface
     */
    private $routeConfig;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $formkey;

    /**
     * Router constructor.
     *
     * @param ActionFactory $actionFactory
     * @param ActionList $actionList
     * @param ConfigInterface $routeConfig
     */
    public function __construct(
        ActionFactory $actionFactory,
        ActionList $actionList,
        ConfigInterface $routeConfig,
        \Magento\Framework\Data\Form\FormKey $formKey
    ) {
        $this->actionFactory = $actionFactory;
        $this->actionList = $actionList;
        $this->routeConfig = $routeConfig;
        $this->formkey = $formKey;
    }

    /**
     * Checks if request path match with the notifyCheckin request path.
     *
     * @param RequestInterface $request
     * @return ActionInterface|null
     */
    public function match(RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');
        if (!preg_match('/(v2\/twint\/terminals\/)\w+\/(checkin)$/', $identifier)) {
            return null;
        }

        $modules = $this->routeConfig->getModulesByFrontName('twint');
        if (empty($modules)) {
            return null;
        }

        //Generate formkey as it's post request.
        $params = $request->getParams();
        $params['form_key'] = $this->formkey->getFormKey();
        $request->setParams($params);

        $actionClassName = $this->actionList->get($modules[0], null, 'NotifyCheckin', 'index');
        $actionInstance = $this->actionFactory->create($actionClassName);

        return $actionInstance;
    }
}
