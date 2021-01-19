<?php
/**
 * SQLi_Twint extension.
 *
 * @category   SQLi
 * @package    SQLi_Twint
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Twint\Model\Config\Backend;

/**
 * Class Certificate.
 *
 * @package SQLi\Twint\Model\Config\Backend
 */
class Certificate extends \Magento\Config\Model\Config\Backend\File
{
    /**
     * @inheritDoc
     */
    public function _getAllowedExtensions()
    {
        return ['pem'];
    }
}
