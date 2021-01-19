<?php
/**
 * SQLi_Twint extension.
 *
 * @category   SQLi
 * @package    SQLi_Twint
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Twint\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class DBSession.
 *
 * @package SQLi\Twint\Model\ResourceModel
 */
class DBSession extends AbstractDb
{
    /**
     * Initialize model.
     */
    protected function _construct()
    {
        $this->_init('twint_session', 'entity_id');
    }
}
