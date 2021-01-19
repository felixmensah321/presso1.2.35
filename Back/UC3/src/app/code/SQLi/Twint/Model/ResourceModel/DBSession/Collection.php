<?php
/**
 * SQLi_Twint extension.
 *
 * @category   SQLi
 * @package    SQLi_Twint
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Twint\Model\ResourceModel\DBSession;

/**
 * Class Collection.
 *
 * @package SQLi\Twint\Model\ResourceModel\DBSession
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * State values.
     */
    const STATE_NEW = 1;
    const STATE_INVALID = 2;
    const STATE_EXPIRED = 3;

    /**
     * Define resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('SQLi\Twint\Model\DBSession', 'SQLi\Twint\Model\ResourceModel\DBSession');
    }
}
