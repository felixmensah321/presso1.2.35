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

/**
 * Class DBSession.
 *
 * @package SQLi\Twint\Model
 */
class DBSession extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('SQLi\Twint\Model\ResourceModel\DBSession');
    }
}
