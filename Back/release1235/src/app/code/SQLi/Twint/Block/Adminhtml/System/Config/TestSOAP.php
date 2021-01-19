<?php
/**
 * SQLi_Twint extension.
 *
 * @category   SQLi
 * @package    SQLi_Twint
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */

namespace SQLi\Twint\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use SQLi\Twint\Model\Config;
use Magento\Backend\Block\Template\Context;

/**
 * Class TestSOAP
 * @package SQLi\Twint\Block\Adminhtml\System\Config
 */
class TestSOAP extends Field
{
    /**
     * @var string
     */
    protected $_template = 'SQLi_Twint::system/config/test_soap.phtml';
    /**
     * @var Config
     */
    private $config;

    /**
     * TestSOAP constructor.
     * @param Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * @param AbstractElement $element
     * @param Config $config
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element) {
        return $this->_toHtml();
    }

    /**
     * Config passed to block
     */
    public function getJsonConfig()
    {
        return json_encode([
            'testUrl'     => $this->getUrl('admin_twint/soap/test')
        ]);
    }

}
