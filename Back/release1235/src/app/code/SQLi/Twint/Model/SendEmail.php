<?php
/**
 * SQLi_Twint extension.
 *
 * @category Cellap
 * @author SQLI Dev Team
 * @copyright Copyright (c) SQLI (http://www.sqli.com/)
 */

namespace SQLi\Twint\Model;


use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class SendEmail extends AbstractHelper
{
    const XML_PATH_EMAIL_TEMPLATE = 'sales_email/order/template';
    const XML_PATH_CONFIRM_EMAIL_IDENTITY = 'trans_email/ident_sales/email';
    const XML_PATH_CONFIRM_EMAIL_NAME = 'trans_email/ident_sales/name';

    protected $transportBuilder;
    protected $storeManager;
    protected $inlineTranslation;
    /**
     * @var \Zend\Log\Logger
     */
    private $twintLogger;
    /**
     * @var \Magento\Framework\App\State
     */
    private $areaState;

    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $state,
        \Magento\Framework\App\State $areaState
    )
    {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
        $this->areaState = $areaState;

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/twint.log');
        $this->twintLogger = new \Zend\Log\Logger();
        $this->twintLogger->addWriter($writer);

        parent::__construct($context);
    }

    private function myLogger($arg)
    {
        $message = print_r($arg, true);
        $this->twintLogger->info($message);
    }

    public function sendEmail($order)
    {
        if (!$order) {
            $this->myLogger("[Monitor Order] Order is null");
        }
        // this is an example and you can change template id,fromEmail,toEmail,etc as per your need.

        $storeId = $order->getStore()->getId();
        $templateId = $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $toEmail = $order->getCustomerEmail();; // receiver email id TODO remove once tests ok

        try {
            $this->areaState->getAreaCode();
        } catch (\Exception $e) {
            $this->areaState->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        }

        try {
            // template variables pass here
            $templateVars = [
                'formated_total' => number_format($order->getGrandTotal(), 2, '.', ' '),
                'order' => $order,
                'billing' => $order->getBillingAddress(),
                'store' => $order->getStore(),
                'created_at_formatted' => $order->getCreatedAtFormatted(2),
                'order_data' => [
                    'customer_name' => $order->getCustomerName(),
                    'is_not_virtual' => $order->getIsNotVirtual(),
                    'email_customer_note' => $order->getEmailCustomerNote(),
                    'frontend_status_label' => $order->getFrontendStatusLabel()
                ]
            ];
            $this->inlineTranslation->suspend();
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $fromEmail = $this->scopeConfig->getValue(
                self::XML_PATH_CONFIRM_EMAIL_IDENTITY,
                $storeScope
            );

            $fromName = $this->scopeConfig->getValue(
                self::XML_PATH_CONFIRM_EMAIL_NAME,
                $storeScope
            );
            $templateFrom = [
                'email' => $fromEmail,
                'name' => $fromName
            ];
            $this->myLogger('[MonitorOrder] Send email from ' . $fromEmail . ' to ' . $toEmail);
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFromByScope($templateFrom)
                ->addTo($toEmail)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->myLogger($e->getMessage());
            $this->_logger->info($e->getMessage());
        }
    }
}
