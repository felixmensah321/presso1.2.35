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
use SQLi\Import\Helper\Data;
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
     * @var Data
     */
    protected $helper;

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
        ImportLogger $logger,
        Data $helper
    ) {
        $this->nespressoCustomerRepository = $nespressoCustomerRepository;
        $this->customerResourceModel = $customerResourceModel;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->logger = $logger;
        $this->helper = $helper;
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
     * @param $update
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
                'street'     =>  empty($addressData['street']) ? Data::AUTOGENERATED : $addressData['street'],
            ];
            if (isset($addressData['company'])) {
                $prepareArray['company'] = $addressData['company'];
            }
            $this->customerResourceModel->getConnection()
                ->update(
                    'customer_address_entity',
                   $prepareArray,
                    ['parent_id = ?' => $addressData['parent_id']]
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
            $fakeEmail = $this->helper->getAutogeneratedEmail(ltrim($newCustomer['nespresso_club_member_id'], '0') );
            if (isset($this->customerEntityEmailMapping[$newCustomer['email']])) {
                $entityId = $this->customerEntityEmailMapping[$newCustomer['email']];
            } elseif (isset($this->customerEntityEmailMapping[$fakeEmail])) {
                $entityId = $this->customerEntityEmailMapping[$fakeEmail];
            }
            if ($entityId) {
                $addressData = [
                    'parent_id' => $entityId,
                    'is_active' => 1,
                    'city' => $newCustomer['city'],
                    'firstname' => ($newCustomer['firstname'] == ' ') ? Data::AUTOGENERATED : $newCustomer['firstname'],
                    'lastname' => $newCustomer['lastname'],
                    'country_id' => $newCustomer['country_id'],
                    'postcode' => empty($newCustomer['postcode']) ? '0000' : $newCustomer['postcode'],
                    'street' => empty($newCustomer['street']) ? Data::AUTOGENERATED : $newCustomer['street'],
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

        $isAnonymous = $this->helper->isAnonymous();
        $nespressoClubMemberId = ltrim($customer[self::CLUBMEMBERID], '0');
        $fakeEmail = $this->helper->getAutogeneratedEmail(ltrim($customer[self::CLUBMEMBERID], '0'));

        // If anonymous mode is enabled customer sensitive information will not be imported
        if (array_key_exists(self::EMAIL, $customer) && !empty($customer[self::EMAIL]) && !$isAnonymous) {
            $email = $customer[self::EMAIL];
        } else {
            $email = $fakeEmail;
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
            $address_street = Data::AUTOGENERATED;
        }

        if (!empty($customer[self::FIRSTNAME])) {
            $customerFirstname = $customer[self::FIRSTNAME];
        } else {
            $customerFirstname = '.';
        }
        $customerContact = empty($customer[self::CONTACT]) ? $this->myTrim($customer[self::LASTNAME]) : $customer[self::CONTACT];
        $customerLastname = $customer[self::LASTNAME];

        $customerEntity = [
            'firstname' => $customerFirstname,
            'website_id' => 1, // todo improve : load website id by repository,
            'group_id' => 1, // todo improve : load group id by repository,
            'store_id' => $lang,
            'email' => $email,
            'gender' => (int)$customer[self::GENDER],
            'created_in' => $this->mapper->getStoreManager()->getStore($lang)->getName(),
            'nespresso_club_member_id' => $nespressoClubMemberId
        ];
        $addressEntity = [
            'firstname' => $customerFirstname,
            'nespresso_club_member_id' => $nespressoClubMemberId,
            'email' => $email,
            'country_id' => 'CH',
            'postcode' => empty($customer[self::POSTCODE]) ? '0000' : $customer[self::POSTCODE],
            'city' => $customer[self::CITY],
            'street' =>  empty($address_street) ? Data::AUTOGENERATED : $address_street
        ];

        if ($customer[self::COMPANY]) {
            $customerEntity['lastname'] = $customerContact;
            $addressEntity['lastname'] = $customerContact;
            $addressEntity['company'] = $customerLastname;
        } else {
            $customerEntity['lastname'] = $customerLastname;
            $addressEntity['lastname'] = $customerLastname;
            $addressEntity['company'] = '';
        }

        // Anonymous mode overriding
        if ($isAnonymous) {
            $customerEntity['firstname'] = $this->helper->anonymise($customerEntity['firstname']);
            $customerEntity['lastname'] = $this->helper->anonymise($customerEntity['lastname']);
            if (array_key_exists('contact', $customerEntity)) {
                $customerEntity['contact'] = $this->helper->anonymise($customerEntity['contact']);
            }
            $addressEntity['firstname'] = $customerEntity['firstname'];
            $addressEntity['lastname'] = $customerEntity['lastname'];
            $addressEntity['city'] = Data::AUTOGENERATED;
            $addressEntity['street'] = Data::AUTOGENERATED;
        }

        if ($email != '' && (isset($this->customerEntityEmailMapping[$email]) || isset($this->customerEntityEmailMapping[$fakeEmail]))) {
            if (isset($this->customerEntityEmailMapping[$email])) {
                $customerEntity['entity_id'] = $this->customerEntityEmailMapping[$email];
            } else {
                $customerEntity['entity_id'] = $this->customerEntityEmailMapping[$fakeEmail];
            }
            $addressData = $this->getAddressData($customerEntity['entity_id'], $customerEntity ,$addressEntity);
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

    public function getAddressData($parentId, $customerData, $addressData)
    {
        $addressData = [
            'parent_id' => $parentId,
            'is_active' => 1,
            'city' => $addressData['city'],
            'firstname' => $customerData['firstname'],
            'lastname' => $customerData['lastname'],
            'country_id' => 'CH',
            'postcode' => $addressData['postcode'],
            'street' =>  $addressData['street'],
            'company' => $addressData['company']
        ];
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
