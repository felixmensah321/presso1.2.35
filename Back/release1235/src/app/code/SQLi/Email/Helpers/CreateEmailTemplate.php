<?php
/**
 * SQLi_Email extension.
 *
 * @category   SQLi
 * @package    SQLi_Email
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */

namespace SQLi\Email\Helpers;

use Exception;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Email\Model\BackendTemplate;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;

class CreateEmailTemplate
{
    private $templateFactory;

    //Location of html templates
    const TEMPLATEPATH = '/../view/frontend/email/';
    //Path for guest order email config
    const CONFIGGUEST = 'sales_email/order/guest_template';
    //Path for customers email config
    const CONFIG = 'sales_email/order/template';
    //Path for header config
    const HEADER = 'design/email/header_template';
    //Path for footer config
    const FOOTER = 'design/email/footer_template';
    //Enums for template type
    const ENUM_HEADER = 0;
    const ENUM_CONTENT = 1;
    const ENUM_FOOTER = 2;
    //Needed for recover order datas in template
    const ISLEGACY = 1;

    const STORES = 'stores';

    private $configWriter;
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;
    /**
     * @var BackendTemplate
     */
    private $backEmailTemplate;

    /**
     * CreateEmailTemplate constructor.
     * @param TemplateFactory $templateFactory
     * @param Config $configWriter
     * @param StoreRepositoryInterface $storeRepository
     * @param BackendTemplate $backEmailTemplate
     */
    public function __construct(
        TemplateFactory $templateFactory,
        Config $configWriter,
        StoreRepositoryInterface $storeRepository,
        BackendTemplate $backEmailTemplate
    ) {
        $this->templateFactory = $templateFactory;
        $this->configWriter = $configWriter;
        $this->storeRepository = $storeRepository;
        $this->backEmailTemplate = $backEmailTemplate;
    }

    /**
     * Allow to create the template in db
     * @param $code
     * @param $subject
     * @param $file
     * @param $orig_code
     * @param $scope
     * @param $enum
     */
    public function createEmailTemplate($code, $subject, $file, $orig_code, $scope, $enum)
    {
        $filePath = __DIR__ . self::TEMPLATEPATH . $file;
        $content = file_get_contents($filePath);
        $template = $this->templateFactory->create();
        $templateData = [
            'is_legacy' => self::ISLEGACY,
            'template_code' => $code,
            'orig_template_code' => $orig_code,
            'template_type' => TemplateTypesInterface::TYPE_HTML,
            'template_text' => $content,
            'template_subject' => $subject,
        ];
        $template->setData($templateData);
        try {
            $template->save();
            $templateId = $template->getTemplateId();
            if ($scope !== null) {
                switch ($enum) {
                    case self::ENUM_CONTENT:
                        $this->assignEmailTemplate(self::CONFIGGUEST, $templateId, $scope, self::STORES);
                        $this->assignEmailTemplate(self::CONFIG, $templateId, $scope, self::STORES);
                        break;
                    case self::ENUM_HEADER:
                        $this->assignEmailTemplate(self::HEADER, $templateId, $scope, self::STORES);
                        break;
                    case self::ENUM_FOOTER:
                        $this->assignEmailTemplate(self::FOOTER, $templateId, $scope, self::STORES);
                        break;
                    default:
                        break;
                }
            }
        } catch (Exception $e) {
            echo 'ERROR during template\'s creation: ' . $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * Assign template to the given store
     * @param $path
     * @param $value
     * @param $store_code
     * @param $scope
     * @throws NoSuchEntityException
     */
    public function assignEmailTemplate($path, $value, $store_code, $scope)
    {
        $store = $this->storeRepository->get($store_code);
        $this->configWriter->saveConfig(
            $path,
            $value,
            $scope,
            $store->getId()
        );
    }
}
