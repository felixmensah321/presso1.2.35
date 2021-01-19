<?php
/**
 * SQLi_Customer extension.
 *
 * @category   SQLi
 * @package    SQLi_Customer
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Customer\Model\ResourceModel;

use Magento\Customer\Model\ResourceModel\Customer;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Eav\Model\AttributeRepository;
use \Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Webapi\Rest\Request;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\AddressFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use SQLi\Customer\Api\NespressoCustomerRepositoryInterface;
use SQLi\Customer\Helper\Data;
use SQLi\Twint\Model\Config;
use SQLi\Twint\Model\DBSessionManagement;
use SQLi\Twint\Model\Front;

/**
 * Class NespressoCustomerRepository.
 *
 * @package SQLi\Customer\Model\ResourceModel
 */
class NespressoCustomerRepository implements NespressoCustomerRepositoryInterface
{
    /**
     * @var array
     */
    private $customerRegistryByNessMemberId = [];

    /**
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var Customer
     */
    protected $customerResourceModel;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var DBSessionManagement
     */
    protected $dbSessionManagement;

    /**
     * @var Front
     */
    protected $frontConfig;

    /**
     * @var Config
     */
    protected $twintConfig;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var AddressFactory
     */
    protected $addressFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $dataObjectConverter;

    /**
     * NespressoCustomerRepository constructor.
     *
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param Customer $customerResourceModel
     * @param AttributeRepository $attributeRepository
     * @param LoggerInterface $logger
     * @param Front $frontConfig
     * @param Config $twintConfig
     * @param Request $request
     * @param DBSessionManagement $dbSessionManagement
     * @param StoreManagerInterface $storeManager
     * @param CustomerFactory $customerFactory
     * @param AddressFactory $addressFactory
     * @param CustomerRepositoryInterface $customerRepository ,
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     */
    public function __construct(
        CustomerCollectionFactory $customerCollectionFactory,
        Customer $customerResourceModel,
        AttributeRepository $attributeRepository,
        LoggerInterface $logger,
        Front $frontConfig,
        Config $twintConfig,
        Request $request,
        DBSessionManagement $dbSessionManagement,
        StoreManagerInterface $storeManager,
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory,
        CustomerRepositoryInterface $customerRepository,
        ExtensibleDataObjectConverter $dataObjectConverter
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->customerResourceModel = $customerResourceModel;
        $this->logger = $logger;
        $this->frontConfig = $frontConfig;
        $this->twintConfig = $twintConfig;
        $this->request = $request;
        $this->dbSessionManagement = $dbSessionManagement;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->addressFactory   = $addressFactory;
        $this->customerRepository = $customerRepository;
        $this->attributeRepository = $attributeRepository;
        $this->dataObjectConverter = $dataObjectConverter;
    }

    /**
     * @inheritDoc
     */
    public function getCustomerByNesMemberId($nesMemberId)
    {
        if (isset($this->customerRegistryByNessMemberId[$nesMemberId])) {
            return $this->customerRegistryByNessMemberId[$nesMemberId];
        }

        $customerCollection = $this->customerCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addStaticField(Data::ATTRIBUTE_NESPRESSO_MEMBER_ID)
            ->addFieldToFilter(Data::ATTRIBUTE_NESPRESSO_MEMBER_ID, $nesMemberId)
        ;

        if ($customerCollection->count() == 0) {
            return [];
        } else {
            $customerDataModel = $customerCollection->getFirstItem()->getDataModel();
            return $customerDataModel;
        }
    }

    /**
     * @param $customerRelationId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerByCustomerRelationId($customerRelationId)
    {
        $customerCollection = $this->customerCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addStaticField(Data::ATTRIBUTE_NESPRESSO_CUSTOMER_RELATION_ID)
            ->addFieldToFilter(Data::ATTRIBUTE_NESPRESSO_CUSTOMER_RELATION_ID, $customerRelationId);

        ;

        if ($customerCollection->count() == 0) {
            return [];
        } else {
            return $customerCollection->getFirstItem()->getDataModel();
        }
    }

    /**
     * @param $email
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerByEmail($email)
    {
        $customerCollection = $this->customerCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('email', $email);
        if ($customerCollection->count() == 0) {
            return [];
        } else {
            return $customerCollection->getFirstItem()->getDataModel();
        }
    }

    /**
     * @inheritDoc
     */
    public function authorizeCustomer()
    {
        $bodyParams = $this->request->getBodyParams();

        $customerFullData = $this->getCustomerByNesMemberId($bodyParams['memberId']);

        if($customerFullData) {
            //we need to match the postalcode with the member_id
            if(isset($customerFullData->getAddresses()[0])) {
                $memberPostalCode = $customerFullData->getAddresses()[0]->getPostcode();

                // If zip sent from front is on more than 4 digits we check for an egality on all digits
                // Else we check only the 4 first digits
                $memberPostalCode = strlen($bodyParams['postalCode']) > 4 ? $memberPostalCode : substr($memberPostalCode, 0, 4);
                if($memberPostalCode == $bodyParams['postalCode']) {
                    $customerData = $this->customerRepository->getById($customerFullData->getId());
                    if($customerData) {
                        $customerData->setCustomAttribute(Data::ATTRIBUTE_NESPRESSO_CUSTOMER_RELATION_ID, $bodyParams['customerRelationId']);
                        $this->customerRepository->save($customerData);
                    }
                    return $this->getCustomerByNesMemberId($bodyParams['memberId']);
                }
                    $this->logger->error('Error message', ['exception' => 'Could not match Postal Code']);
                    throw new InputMismatchException(__('Could not match Postal Code'));
            } else {
                    $this->logger->error('Error message', ['exception' => 'customer must have at least one address']);
                    throw new NoSuchEntityException(__('No Address found'));
            }
        }
        $this->logger->error('Error message', ['exception' => 'could not find/load customer data']);
        throw new AuthorizationException(__('Authentication error'));
    }

    /**
     * @inheritDoc
     */
    public function autoAuthorizeCustomer()
    {
        $bodyParams = $this->request->getBodyParams();

        // get session data from db
        $sessionData = $this->dbSessionManagement->getRowBySessionAndRelationId
        (
            $bodyParams['sessionId'],
            $bodyParams['customerRelationId']
        );

        if (!$sessionData) {
            //we cannot find session data so we send a 404 response
            $this->logger->error('Error message', ['exception' => 'No Session data found']);
            return $this->frontConfig->get404Url();
        }
        $customerData = $this->getCustomerByCustomerRelationId($bodyParams['customerRelationId']);
        if($customerData) {
            return $customerData;
        }
        return $this->frontConfig->get404Url();
    }

    /**
     * @param $paramsArray
     * @throws InputException
     */
    public function validateCustomerParams($paramsArray)
    {
        if(is_array($paramsArray)) {

            //always validate email address
            if (empty($paramsArray['email'])) {
                $this->logger->error('Error message', ['exception' => 'Email field is required']);
                throw InputException::requiredField('Email');
            }
            if(!empty($paramsArray['email'])) {
                if (!filter_var($paramsArray['email'], FILTER_VALIDATE_EMAIL)) {
                    $this->logger->error('Error message', ['exception' => 'Email value is not valid']);
                    throw InputException::invalidFieldValue('Email', $paramsArray['email']);
                }
            }

            // Validate the below only if user is not a guest
            if(!$paramsArray['guestMode']) {
                if (empty($paramsArray['firstName'])) {
                    $this->logger->error('Error message', ['exception' => 'firstName field is required']);
                    throw InputException::requiredField('firstName');
                }
                if (empty($paramsArray['lastName'])) {
                    $this->logger->error('Error message', ['exception' => 'lastName field is required']);
                    throw InputException::requiredField('lastName');
                }
                if (empty($paramsArray['postalCode'])) {
                    $this->logger->error('Error message', ['exception' => 'postalCode field is required']);
                    throw InputException::requiredField('postalCode');
                }
                $lastNameLength = strlen ($paramsArray['lastName']);
                if ( $lastNameLength > 35) {
                    $this->logger->error('Error message', ['exception' => 'Last Name must have not more than 35 characters']);
                    throw InputException::invalidFieldValue('firstName', $paramsArray['firstName']);
                }
                $fistNameLength = strlen ($paramsArray['firstName']);
                if ( $fistNameLength > 20) {
                    $this->logger->error('Error message', ['exception' => 'First Name must have not more than 20 characters']);
                    throw InputException::invalidFieldValue('firstName', $paramsArray['firstName']);
                }
                $cityLength = strlen ($paramsArray['city']);
                if ( $cityLength > 35) {
                    $this->logger->error('Error message', ['exception' => 'city must have not more than 35 characters']);
                    throw InputException::invalidFieldValue('city', $paramsArray['city']);
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function createCustomer()
    {
        $bodyParams = $this->request->getBodyParams();
        $msg = 'Error processing your request';

        // VALIDATE Customer Input if Guest Mode is False
        $this->validateCustomerParams($bodyParams);

        try {
            $customer = $this->customerFactory->create();
            $customer->setWebsiteId($this->storeManager->getWebsite()->getWebsiteId());
            $customerId = $customer->loadByEmail($bodyParams['email'])->getId();

            //SCENARIO 2 (Customer wants a Nespresso Account and has an existing customer account on Magento)
            if (!$bodyParams['guestMode'] && $customerId) {
                //email exist, so we check if its associated with a Nespresso ID
                $nespressoId = $customer->getNespressoClubMemberId();
                if(!empty($nespressoId)) {
                    $msg = 'Account Already Exist With Nespresso ID';
                    throw new AlreadyExistsException(__('ALREADY_EXIST_WITH_NESPRESSO_ID'));
                }
                $msg = 'Account Already Exist';
                throw new AlreadyExistsException(__('ACCOUNT_ALREADY_EXIST'));
            }

            //SCENARIO 3 (Customer doesnt want an account and has existing account in Magento)
            if($bodyParams['guestMode'] && $customerId) {
                $msg = 'Account Already Exist';
                throw new LocalizedException(__('ACCOUNT_ALREADY_EXIST'));
            }

            // SCENARIO 4 (Customer doesnt want an account and has no existing account in Magento)
            if($bodyParams['guestMode'] && !$customerId) {
                $message = ['status' => 1];
                return json_encode($message);
            }

            // SCENARIO 1 (Customer wants a Nespresso Account and don't have an existing customer account on Magento)
            //save customer information
            $customer->setEmail($bodyParams['email']);
            $customer->setFirstname($bodyParams['firstName']);
            $customer->setLastname($bodyParams['lastName']);
            $customer->setGender($bodyParams['title']);
            $customer->setForceConfirmed(true);
            $customer->save();

            //save newsletter optin custom attribute using repository interface
            $customerData = $this->customerRepository->getById($customer->getId());
            if($customerData) {
                $customerData->setCustomAttribute(Data::ATTRIBUTE_NEWSLETTER_OPTIN, $bodyParams['optin']);
                $this->customerRepository->save($customerData);
                $this->logger->info('Info message', ['Notice' => 'Optin data updated successfully']);
            }

            // Proceed to save customer address information
            $customerAddress = $this->addressFactory->create();
            $customerAddress->setCustomerId($customer->getId())
                ->setFirstname($bodyParams['firstName'])
                ->setLastname($bodyParams['lastName'])
                ->setCountryId('CH')
                ->setPostcode($bodyParams['postalCode'])
                ->setCity($bodyParams['city'])
                ->setStreet($bodyParams['address'])
                ->setSaveInAddressBook('1');
            try {
                $customerAddress->save();
            } catch (\Exception $e) {
                $this->logger->error('Error message', ['exception' => $e->getMessage()]);
            }

            // COMPLETE SCENARIO 1 by returning Customer Data
            $customerData = $this->getCustomerByEmail($customer->getEmail());
            $data = $this->dataObjectConverter->toNestedArray($customerData,[]);
            $message = ['status' => 1, 'customer' => $data];
            return json_encode($message);

        } catch (\Exception $e) {
            $this->logger->error('Error message', ['exception' => $e->getMessage()]);
        }
        throw new LocalizedException(__($msg));
    }

    /**
     * Retrieve an array with all nespresso member id.
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllNespressoMemberId()
    {
        return $this->customerResourceModel->getConnection()->fetchCol($this->getAllNespressoMemberIdIdsSelect());
    }

    /**
     * Retrieve an array with email - entity id pairs.
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllEntityEmailPairs()
    {
        return $this->customerResourceModel->getConnection()->fetchPairs($this->getAllEntityEmailPairsSelect());
    }

    /**
     * Retrieve an array with address entity_id - parent id (customer_id) pairs.
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllEntityIdAddressIdPairs()
    {
        return $this->customerResourceModel->getConnection()->fetchPairs($this->getAllEntityIdAddressIdPairsSelect());
    }

    /**
     * Create all ids retrieving select.
     *
     * @return \Magento\Framework\DB\Select
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getAllNespressoMemberIdIdsSelect()
    {
        $nesClubMemberIdAttrId = $this->attributeRepository->get(
            'customer',
            Data::ATTRIBUTE_NESPRESSO_MEMBER_ID
        );

        $idsSelect = clone $this->customerResourceModel->getConnection()
            ->select()
            ->from('customer_entity_varchar')
            ->where(
                'attribute_id = ?',
                $nesClubMemberIdAttrId->getAttributeId()
            );
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $idsSelect->columns('value');

        return $idsSelect;
    }

    /**
     * Create entity_id email pair select.
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function getAllEntityEmailPairsSelect()
    {

        $idsSelect = clone $this->customerResourceModel->getConnection()
            ->select()
            ->from('customer_entity');
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $idsSelect->columns(['email', 'entity_id']);

        return $idsSelect;
    }

    /**
     * Create entity_id parent_id pair select.
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function getAllEntityIdAddressIdPairsSelect()
    {

        $idsSelect = clone $this->customerResourceModel->getConnection()
            ->select()
            ->from('customer_address_entity');
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $idsSelect->columns(['entity_id', 'parent_id']);

        return $idsSelect;
    }
}

