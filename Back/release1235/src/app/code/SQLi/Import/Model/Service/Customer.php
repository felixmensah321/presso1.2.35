<?php
/**
 * SQLi_Import extension.
 *
 * @category   SQLi
 * @package    SQLi_Import
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */

namespace SQLi\Import\Model\Service;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use SQLi\Customer\Model\ResourceModel\NespressoCustomerRepository;
use SQLi\Import\Logger\ImportLogger;

/**
 * Class Customer.
 *
 * @package SQLi\Import\Model\Service
 */
class Customer
{
    /**
     * Customer Import row ids mapping
     */
    const CLUBMEMBERID = 0;
    const ROW_LANG_INDEX = 2;
    const COMPANY = 3;
    const GENDER = 4;
    const LASTNAME = 5;
    const FIRSTNAME = 6;
    const CONTACT = 7;
    const POSTCODE = 8;
    const CITY = 9;
    const ADDRESS_1 = 10;
    const ADDRESS_2 = 11;
    const EMAIL = 12;

    /**
     * @var NespressoCustomerRepository
     */
    protected $nespressoCustomerRepository;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $customerResourceModel;

    /**
     * @var array
     */
    protected $customerEntityEmailMapping = [];

    /**
     * @var array
     */
    protected $customerEntityAddressIdMapping = [];

    /**
     * @var array
     */
    protected $customerToUpdate = [];

    /**
     * @var array
     */
    protected $customerToCreate = [];

    /**
     * @var ImportLogger
     */
    protected $logger;

    /**
     * @var \SQLi\Import\Model\Mapper\Customer
     */
    protected $mapper;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * Customer constructor.
     *
     * @param NespressoCustomerRepository $nespressoCustomerRepository
     * @param \Magento\Customer\Model\ResourceModel\Customer $customerResourceModel
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param ImportLogger $logger
     */
    public function __construct(
        NespressoCustomerRepository $nespressoCustomerRepository,
        \Magento\Customer\Model\ResourceModel\Customer $customerResourceModel,
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
        ImportLogger $logger
    ) {
        $this->nespressoCustomerRepository = $nespressoCustomerRepository;
        $this->customerResourceModel = $customerResourceModel;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->logger = $logger;
    }

    public function setMapper($mapper)
    {
        $this->mapper = $mapper;
    }

    public function insertCustomers()
    {
        if (!empty($this->customerToCreate['customer_entity'])) {
            $this->logger->debug('[CUSTOMER] SERVICE - ' . count($this->customerToCreate['customer_entity']) . ' customers to import.');
            $this->createOrUpdateCustomerEntity($this->customerToCreate['customer_entity'], false);

            $additionalInfo = $this->buildAdditionalInfo($this->customerToCreate['additional_info']);
            if (empty($additionalInfo['customer_address_entity'])) {
                return false;
            }
            $this->createOrUpdateAddressEntity($additionalInfo['customer_address_entity'], false);
        } else {
            $this->logger->debug('[CUSTOMER] SERVICE - no new customer detected');
        }
    }

    public function updateCustomers()
    {
        if (!empty($this->customerToUpdate['customer_entity'])) {
            $this->logger->debug('[CUSTOMER] SERVICE - ' . count($this->customerToUpdate['customer_entity']) . ' customers to update.');
            $this->createOrUpdateCustomerEntity($this->customerToUpdate['customer_entity'], true);
            if (!empty($this->customerToUpdate['address_data'])) {
                $this->createOrUpdateAddressEntity($this->customerToUpdate['address_data'], true);
            }
        } else {
            $this->logger->debug('[CUSTOMER] SERVICE - no customer to update');
        }
    }

    /**
     * Insert or update customer listing based on mapped data.
     *
     * @param array $customerData
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function insertOrUpdateCustomerList()
    {
        $this->insertCustomers();
        $this->updateCustomers();
    }

    public function clearData()
    {
        unset($this->customerToCreate);
        $this->customerToCreate = array();
        unset($this->customerToUpdate);
        $this->customerToUpdate = array();
    }

    /**
     * Create or update customer entity.
     *
     * @param array $customerData
     * @param bool $update
     *
     * @return bool
     */
    protected function createOrUpdateCustomerEntity($customerData, $update)
    {
        $columns = [
            'website_id',
            'group_id',
            'store_id',
            'firstname',
            'lastname',
            'email',
            'gender',
            'created_in',
            'nespresso_club_member_id'
        ];

        if ($update) {
            $columns[] = 'entity_id';
        }
        try {
            $this->customerResourceModel->getConnection()
                ->insertOnDuplicate(
                    'customer_entity',
                    $customerData,
                    $columns
                );
            return true;
        } catch (\Exception $e) {
            $this->logger->debug('[CUSTOMER] SERVICE - error during customer creation or update.');
            $this->logger->debug($e->getMessage());
            return false;
        }
    }

    /**
     * Create or update customer entity.
     *
     * @param array $addressData
     * @param bool $update
     *
     * @return bool
     */
    protected function createOrUpdateAddressEntity($addressData, $update)
    {
        $columns = [
            'parent_id',
            'is_active',
            'city',
            'firstname',
            'lastname',
            'country_id',
            'postcode',
            'street',
            'company'
        ];

            if (count($addressData) > 0) {
                if ($update) {
                    $columns[] = 'entity_id';
                    for ($i = 0; $i < count($addressData); $i++) {
                       try {
                           $this->customerImportQuery($addressData[$i], 'update', $columns);
                       } catch (\Exception $e) {
                           $this->logger->debug('[CUSTOMER] SERVICE - error during address creation or update.');
                           $this->logger->debug($e->getMessage());
                           $this->logger->debug(print_r($addressData[$i],1));
                       }
                    }
                    return true;
                }
                for ($i = 0; $i < count($addressData); $i++) {
                    try {
                        $this->customerImportQuery($addressData[$i], 'insertOnDuplicate', $columns);
                        $customer = $this->customerRepository->getById($addressData[$i]['parent_id']);

                        $customer->setDefaultShipping($customer->getAddresses()[0]->getId());
                        $customer->setDefaultBilling($customer->getAddresses()[0]->getId());

                        $this->customerRepository->save($customer);
                    } catch (\Exception $e) {
                        $this->logger->debug('[CUSTOMER] SERVICE - error during address creation or update.');
                        $this->logger->debug($e->getMessage());
                        $this->logger->debug(print_r($addressData[$i],1));
                    }
                }
            }
            return true;
    }

    /**
     * Run customer Import Query to insert or update address.
     * @param $addressData
     * @param $query
     * @param $columns
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    protected function customerImportQuery($addressData, $query, $columns)
    {
        if ($query == 'update') {
            $prepareArray =  [
                'city'       => $addressData['city'],
                'firstname'  => $addressData['firstname'],
                'lastname'   => $addressData['lastname'],
                'country_id' => $addressData['country_id'],
                'postcode'   => $addressData['postcode'],
                'street'     =>  empty($addressData['street']) ? 'autogenerated' : $addressData['street'],
            ];
            if (isset($addressData['company'])) {
                $prepareArray['company'] = $addressData['company'];
            }
            $this->customerResourceModel->getConnection()
                ->update(
                    'customer_address_entity',
                   $prepareArray,
                    'parent_id =' . $addressData['parent_id']
                );
            $customer = $this->customerRepository->getById($addressData['parent_id']);
                $customer->setDefaultShipping($customer->getAddresses()[0]->getId());
                $customer->setDefaultBilling($customer->getAddresses()[0]->getId());
            $this->customerRepository->save($customer);
        } else {
            $this->customerResourceModel->getConnection()
                ->insertOnDuplicate(
                    'customer_address_entity',
                    $addressData,
                    $columns
                );
        }
    }

    /**
     * Format data to inject addresses et nespresso club member id.
     *
     * @param $newCustomerList
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function buildAdditionalInfo($newCustomerList)
    {
        $dataToInsert = [];

        $this->initCustomerEntityEmailMapping(true);

        foreach ($newCustomerList as $newCustomer) {
            $entityId = null;
            $fake_email = ltrim($newCustomer['nespresso_club_member_id'], '0') . '@autogenerated.com';
            if (isset($this->customerEntityEmailMapping[$newCustomer['email']])) {
                $entityId = $this->customerEntityEmailMapping[$newCustomer['email']];
            } elseif (isset($this->customerEntityEmailMapping[$fake_email])) {
                $entityId = $this->customerEntityEmailMapping[$fake_email];
            }
            if ($entityId) {
                $addressData = [
                    'parent_id' => $entityId,
                    'is_active' => 1,
                    'city' => $newCustomer['city'],
                    'firstname' => ($newCustomer['firstname'] == ' ') ? 'autogenerated' : $newCustomer['firstname'],
                    'lastname' => $newCustomer['lastname'],
                    'country_id' => $newCustomer['country_id'],
                    'postcode' => empty($newCustomer['postcode']) ? '0000' : $newCustomer['postcode'],
                    'street' => empty($newCustomer['street']) ? 'autogenerated' : $newCustomer['street'],
                ];
                $dataToInsert['customer_address_entity'][] = $addressData;
            }
        }
        return $dataToInsert;
    }

    /**
     * @param $customerList
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function detectNewOrUpdateCustomer($customer)
    {
        if (empty($this->mapper->getStoresLangIdMapping())) {
            $this->mapper->initStoreMapping();
        }

        if (array_key_exists(self::EMAIL, $customer)) {
            if ($customer[self::EMAIL] != '') {
                $email = $customer[self::EMAIL];
            } else {
                $email = ltrim($customer[self::CLUBMEMBERID], '0') . '@autogenerated.com';
            }
        } else {
            $email = ltrim($customer[self::CLUBMEMBERID], '0') . '@autogenerated.com';
        }

        $lang = $this->mapper->getStoresLangIdMapping()[$customer[self::ROW_LANG_INDEX]];
        $address_street = $customer[self::ADDRESS_1];
        if (isset($customer[self::ADDRESS_2]) && strlen($customer[self::ADDRESS_2]) > 0) {
            if (!empty($address_street)){
                $address_street = $address_street . PHP_EOL . $customer[self::ADDRESS_2];
            } else {
                $address_street = $customer[self::ADDRESS_2];
            }
        }
        if ($address_street === null || empty($address_street)){
            $address_street = 'autogonerated';
        }
        $nespressoClubMemberId = ltrim($customer[self::CLUBMEMBERID], '0');
        if ($customer[self::COMPANY]) {
            $customerEntity = [
                'website_id' => 1, // todo improve : load website id by repository,
                'group_id' => 1, // todo improve : load group id by repository,
                'store_id' => $lang,
                'firstname' => empty($customer[self::FIRSTNAME]) ? '.' : $customer[self::FIRSTNAME],
                'lastname' => empty($customer[self::CONTACT]) ? $this->myTrim($customer[self::LASTNAME]) : $customer[self::CONTACT],
                'email' => $email,
                'gender' => (int)$customer[self::GENDER],
                'created_in' => $this->mapper->getStoreManager()->getStore($lang)->getName(),
                'nespresso_club_member_id' => $nespressoClubMemberId
            ];
            $addressEntity = [
                'nespresso_club_member_id' => $nespressoClubMemberId,
                'email' => $email,
                'firstname' => empty($customer[self::FIRSTNAME]) ? '.' : $customer[self::FIRSTNAME],
                'lastname' => empty($customer[self::CONTACT]) ? $this->myTrim($customer[self::LASTNAME]) : $customer[self::CONTACT],
                'country_id' => 'CH',
                'postcode' => empty($customer[self::POSTCODE]) ? '0000' : $customer[self::POSTCODE],
                'city' => $customer[self::CITY],
                'street' =>  empty($address_street) ? 'autogenerated' : $address_street,
                'company' => $customer[self::LASTNAME],
            ];
        } else {
            $customerEntity = [
                'website_id' => 1, // todo improve : load website id by repository,
                'group_id' => 1, // todo improve : load group id by repository,
                'store_id' => $lang,
                'firstname' => empty($customer[self::FIRSTNAME]) ? '.' : $customer[self::FIRSTNAME],
                'lastname' => $customer[self::LASTNAME],
                'email' => $email,
                'gender' => (int)$customer[self::GENDER],
                'created_in' => $this->mapper->getStoreManager()->getStore($lang)->getName(),
                'nespresso_club_member_id' => $nespressoClubMemberId
            ];
            $addressEntity = [
                'nespresso_club_member_id' => $nespressoClubMemberId,
                'email' => $email,
                'firstname' => empty($customer[self::FIRSTNAME]) ? '.' : $customer[self::FIRSTNAME],
                'lastname' => $customer[self::LASTNAME],
                'country_id' => 'CH',
                'postcode' => empty($customer[self::POSTCODE]) ? '0000' : $customer[self::POSTCODE],
                'city' => $customer[self::CITY],
                'street' => empty($address_street) ? 'autogenerated' : $address_street,
            ];
        }
        $fake_email = ltrim($customer[self::CLUBMEMBERID], '0') . '@autogenerated.com';
        if ($email != '' && (isset($this->customerEntityEmailMapping[$email]) || isset($this->customerEntityEmailMapping[$fake_email]))) {
            if (isset($this->customerEntityEmailMapping[$email])) {
                $customerEntity['entity_id'] = $this->customerEntityEmailMapping[$email];
            } else {
                $customerEntity['entity_id'] = $this->customerEntityEmailMapping[$fake_email];
            }
            $addressData = $this->getAddressData($customerEntity['entity_id'], $customer);
            $this->customerToUpdate['customer_entity'][] = $customerEntity;
            $this->customerToUpdate['additional_info'][] = $addressEntity;
            $this->customerToUpdate['address_data'][] = $addressData;
        } else {
            $this->customerToCreate['customer_entity'][] = $customerEntity;
            $this->customerToCreate['additional_info'][] = $addressEntity;
        }
    }

    private function myTrim($string) {
        $unvailableChar = array("<",">","{","}","[","]","!","@","#","$","+","=","%","^","*","(",")","/",";");
        $replace = str_replace($unvailableChar, "", $string);
        return $replace;
    }

    public function getAddressData($parentId, $customer)
    {
        $address_street = $customer[self::ADDRESS_1];
        if (isset($customer[self::ADDRESS_2]) && strlen($customer[self::ADDRESS_2]) > 0) {
            if (!empty($address_street)){
                $address_street = $address_street . PHP_EOL . $customer[self::ADDRESS_2];
            } else {
                $address_street = $customer[self::ADDRESS_2];
            }
        }
        if ($address_street === null || empty($address_street)){
            $address_street = 'autogonerated';
        }
        if ($customer[self::COMPANY]) {
            $addressData = [
                'parent_id' => $parentId,
                'is_active' => 1,
                'city' => $customer[self::CITY],
                'firstname' => empty($customer[self::FIRSTNAME]) ? '.' : $customer[self::FIRSTNAME],
                'lastname' => empty($customer[self::CONTACT]) ? $this->myTrim($customer[self::LASTNAME]) : $customer[self::CONTACT],
                'country_id' => 'CH',
                'postcode' => $customer[self::POSTCODE],
                'street' =>  empty($address_street) ? 'autogenerated' : $address_street,
                'company' => $customer[self::LASTNAME],
            ];
        } else {
            $addressData = [
                'parent_id' => $parentId,
                'is_active' => 1,
                'city' => $customer[self::CITY],
                'firstname' => empty($customer[self::FIRSTNAME]) ? '.' : $customer[self::FIRSTNAME],
                'lastname' => $customer[self::LASTNAME],
                'country_id' => 'CH',
                'postcode' => $customer[self::POSTCODE],
                'street' => empty($address_street) ? 'autogenerated' : $address_street,
            ];
        }
        return $addressData;
    }

    /**
     * Retrieve mapping between customer entity and customer email.
     *
     * @param bool $force
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function initCustomerEntityEmailMapping($force = false)
    {
        if (empty($this->customerEntityEmailMapping) || $force == true) {
            $this->customerEntityEmailMapping = $this->nespressoCustomerRepository->getAllEntityEmailPairs();
        }
        return $this->customerEntityEmailMapping;
    }
}
