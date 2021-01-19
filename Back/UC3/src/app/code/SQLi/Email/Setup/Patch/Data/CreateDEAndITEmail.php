<?php
/**
 * SQLi_Email extension.
 *
 * @category   SQLi
 * @package    SQLi_Email
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */

namespace SQLi\Email\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use SQLi\Email\Helpers\CreateEmailTemplate;

/**
 * Class CreateDEEmail
 * @package SQLi\Email\Setup\Patch\Data
 */
class CreateDEAndITEmail implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CreateEmailTemplate
     */
    private $createEmailTemplate;

    const HEADER_NAME = 'Order Header - ';
    const FOOTER_NAME = 'Order Footer - ';
    const CONTENT_NAME = 'Order Content - ';
    const HEADER_FILE = '_Header.html';
    const FOOTER_FILE = '_Footer.html';
    const CONTENT_FILE = '_Content.html';
    const SUBJECT = '{{trans "Your %store_name order confirmation" store_name=$store.getFrontendName()}}';
    const HEADER_CODE = '_nespresso_order_header';
    const FOOTER_CODE = '_nespresso_order_footer';
    const CONTENT_CODE = '_nespresso_order_content';
    const SCOPE = 'nespresso_';
    const LANGUAGES = [
        "nespresso_de" => "DE",
        "nespresso_it" => "IT"
    ];

    /**
     * CreateITEmail constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CreateEmailTemplate $createEmailTemplate
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CreateEmailTemplate $createEmailTemplate
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->createEmailTemplate = $createEmailTemplate;
    }

    /**
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return CreateDEEmail|void
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        foreach (self::LANGUAGES as $storecode => $languageId) {
            $this->createEmailTemplate->createEmailTemplate(self::HEADER_NAME . $languageId, self::SUBJECT, $languageId . self::HEADER_FILE, $languageId . self::HEADER_CODE, $storecode, CreateEmailTemplate::ENUM_HEADER);
            $this->createEmailTemplate->createEmailTemplate(self::FOOTER_NAME . $languageId, self::SUBJECT, $languageId . self::FOOTER_FILE, $languageId . self::FOOTER_CODE, $storecode, CreateEmailTemplate::ENUM_FOOTER);
            $this->createEmailTemplate->createEmailTemplate(self::CONTENT_NAME . $languageId, self::SUBJECT, $languageId . self::CONTENT_FILE, $languageId . self::CONTENT_CODE, $storecode, CreateEmailTemplate::ENUM_CONTENT);
        }
        $this->moduleDataSetup->endSetup();
    }

    /**
     * @return array|void
     */
    public function revert()
    {
        return [];
    }
}
