<?php
/**
 * SQLi_Config extension.
 *
 * @category SQLi
 * @author SQLI Dev Team
 * @copyright Copyright (c) SQLI (http://www.sqli.com/)
 */

namespace SQLi\Config\Setup\Patch\Data;

use Magento\Authorization\Model\Acl\Role\Group;
use Magento\Authorization\Model\ResourceModel\Role;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateUserRoles implements DataPatchInterface
{
    const ROLES = [
        "eCommerce eMerchandizer",
        "Fulfillment operator",
        "Auditor"
        ];

    /**
     * RoleFactory
     *
     * @var roleFactory
     */
    private $roleFactory;

    /**
     * @var Role
     */
    private $roleResource;

    public function __construct(
        Role $resource,
        RoleFactory $roleFactory
    ) {
        $this->roleResource = $resource;
        $this->roleFactory = $roleFactory;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        foreach (self::ROLES as $roleName) {
            $role = $this->roleFactory->create();

            $role->setName($roleName)
                ->setPid(0)
                ->setRoleType(Group::ROLE_TYPE)
                ->setUserType(UserContextInterface::USER_TYPE_ADMIN);

            $this->roleResource->save($role);
        }
    }
}
