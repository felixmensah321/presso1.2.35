<?php
/**
 * Twint_Export extension.
 *
 * @category Twint
 * @author SQLI Dev Team
 * @copyright Copyright (c) SQLI (http://www.sqli.com/)
 */

namespace SQLi\Export\Model\Type;

use DOMDocument;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use SQLi\Customer\Helper\Data;
use SQLi\Export\Helper\Config;
use SQLi\Export\Helper\ConstRegistry;
use SQLi\Sales\Helper\Data as SalesHelper;
use SQLi\Customer\Helper\Data as CustomerHelper;

class Xml
{

    /**
     * default export folder for orders from cli call
     */
    const CLIEXPORTFOLDERNAME = 'cli/';

    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var
     */
    protected $doc;

    /**
     * @var array
     */
    protected $membersArray = [];

    /**
     * @var array
     */
    protected $customersArray = [];

    /**
     * @var int
     */
    protected $totalQty = 0;

    /**
     * @var int
     */
    protected $totalOrders = 0;

    /**
     * @var
     */
    protected $provider;

    /**
     * @var int
     */
    protected $totalGuests = 0;
    /**
     * @var
     */
    protected $rootNode;
    /**
     * @var \DomElement
     */
    protected $batchInfo;
    /**
     * @var SalesHelper
     */
    protected $salesHelper;
    /**
     * @var CustomerHelper
     */
    protected $customerHelper;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    //number of sales in one file
    const NUM_ROWS_PER_FILE = 500;

    /**
     * Csv constructor.
     *
     * @param Filesystem $filesystem
     * @param Config $configHelper
     * @param SalesHelper $salesHelper
     * @param CustomerHelper $customerHelper
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        Config $configHelper,
        SalesHelper $salesHelper,
        CustomerHelper $customerHelper,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->configHelper = $configHelper;
        $this->salesHelper = $salesHelper;
        $this->customerHelper = $customerHelper;
        $this->customerRepository = $customerRepository;
    }

    /**
     * recover informations
     * @return \DomElement
     */
    protected function getBatchInfo()
    {
        $batchInfos = $this->doc->createElement(ConstRegistry::TAG_IMPORT_BATCH);
        $importBatchAttributes = [
            "xmlns" => "http://nesclub.nespresso.com/webservice/club/xsd/",
            "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
            "xsi:schemaLocation" => "http://nesclub.nespresso.com/webservice/club/xsd/ http://nesclub.nespresso.com/webservice/club/xsd/",
            "version_number" => "1.0"
        ];
        foreach ($importBatchAttributes as $label => $value) {
            $batchInfos->setAttribute($label, $value);
        }

        $this->rootNode = $batchInfos;
        return $batchInfos;
    }

    /**
     * Print Date time
     */
    protected function getBatchDate()
    {
        $batchDate = $this->doc->createElement(ConstRegistry::TAG_BATCH_DATE_TIME);
        $batchDate->appendChild(
            $this->doc->createTextNode(date('Y-m-dTH:i:s'))
        );
        $this->batchInfo->appendChild($batchDate);
    }

    /**
     * Define unique ID based on the datetime
     * @param $isBis
     * @param $nbFile
     */
    protected function getBatchNumber($isBis, $nbFile)
    {
        $batchNumber = $this->doc->createElement(ConstRegistry::TAG_BATCH_NUMBER);
        $batchIncrement = 1;
        if ($isBis) {
            $batchIncrement = 2;
        }
        $fileNumber = intval($nbFile);
        if (!$nbFile && !is_int($nbFile)) {
            $fileNumber = 0;
        }
        $batchNumber->appendChild(
            $this->doc->createTextNode(date('ymd') . $batchIncrement . $fileNumber)
        );
        $this->batchInfo->appendChild($batchNumber);
    }

    /**
     * print the sender id
     */
    protected function getSenderId()
    {
        $senderId = $this->doc->createElement(ConstRegistry::TAG_SENDER_ID);
        $senderId->appendChild(
            $this->doc->createTextNode(ConstRegistry::SENDER_ID)
        );
        $this->batchInfo->appendChild($senderId);
    }

    /**
     * print the number of orders
     */
    protected function getTotalOrders()
    {
        $totalOrders = $this->doc->createElement(ConstRegistry::TAG_TOTAL_ORDERS);
        $totalOrders->appendChild(
            $this->doc->createTextNode($this->totalOrders)
        );
        $this->batchInfo->appendChild($totalOrders);
    }

    /**
     * Print the total quantity of items
     */
    protected function getTotalQty()
    {
        $totalQty = $this->doc->createElement(ConstRegistry::TAG_TOTAL_QUANTITY);
        $totalQty->appendChild(
            $this->doc->createTextNode(number_format($this->totalQty))
        );
        $this->batchInfo->appendChild($totalQty);
    }

    /**
     * Print the number of customers
     */
    protected function getTotalCutomers()
    {
        $totalCustomers = $this->doc->createElement(ConstRegistry::TAG_TOTAL_CUSTOMERS);
        $totalCustomers->appendChild(
            $this->doc->createTextNode(count($this->customersArray))
        );
        $this->batchInfo->appendChild($totalCustomers);
    }

    /**
     * Print the number of members id
     */
    protected function getTotalMembers()
    {
        $totalMembers = $this->doc->createElement(ConstRegistry::TAG_TOTAL_MEMBERS);
        $totalMembers->appendChild(
            $this->doc->createTextNode(count($this->membersArray))
        );
        $this->batchInfo->appendChild($totalMembers);
    }

    /**
     * @param $provider
     * @param bool $isBis
     * @throws FileSystemException
     */
    public function createXmlFileFromProvider($provider, $isBis = false, $isFromCommand = false)
    {
        $this->provider = $provider;
        $this->directory->create('export');
        $this->directory->create('export/xml');
        $nbFile = 1;
        if ($isFromCommand) {
            $filenameDate = new \DateTime('now', new \DateTimeZone('Europe/Zurich'));
            $interval = new \DateInterval('P1Y');
            $filenameDate->sub($interval);
            $date = $filenameDate->format('Ymd_His');
            $exportFolder = self::CLIEXPORTFOLDERNAME;
        } else {
            $date = date('Ymd_His');
            $exportFolder = $this->configHelper->getExportFolder();
        }
        $file = $this->initFile($date, $exportFolder, $nbFile, $isBis);
        $items = $this->provider->getResults()->getItems();
        $i = 0;
        foreach ($items as $item) {
            $this->totalOrders++;
            $this->fillRecord($item);
            $i++;
            if ($i % self::NUM_ROWS_PER_FILE === 0) {
                $this->closeFile($file);
                $nbFile++;
                $file = $this->initFile($date, $exportFolder, $nbFile, $isBis);
            }
        }

//        Comment bu not delete in case of reuse
//        $this->fillEndInfos();
        $this->closeFile($file);
    }

    /**
     * init export file
     * @param $date
     * @param $exportFolder
     * @param $nbFile
     * @param $isBis
     * @return string
     */
    protected function initFile($date, $exportFolder, $nbFile, $isBis)
    {
        $file = 'export/xml/' . $exportFolder . $this->provider->getFilename() . '_' . $date . '_' . $nbFile . '.xml';
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;
        $this->batchInfo = $this->getBatchInfo();
        $this->collectInfos($isBis, $nbFile);
        return $file;
    }

    /**
     * Insert data in file and write it
     * @param $file
     * @throws FileSystemException
     */
    protected function closeFile($file)
    {
        $this->doc->appendChild($this->batchInfo);
        $this->directory->writeFile($file, $this->doc->saveXML());
    }

    /**
     * Collect initials info
     * @param $isBis
     * @param $nbFile
     */
    protected function collectInfos($isBis, $nbFile)
    {
        $this->getBatchDate();
        $this->getBatchNumber($isBis, $nbFile);
        $this->getSenderId();
    }

    /**
     * @param $member
     */
    protected function membersTab($member)
    {
        if (!isset($this->membersArray[$member])) {
            $this->membersArray[$member] = 1;
        }
    }

    /**
     * @param $customer
     */
    protected function customersTab($customer)
    {
        if (!isset($this->customersArray[$customer])) {
            $this->customersArray[$customer] = 1;
        }
    }

    /**
     * Print the customer informations
     * @param Order $order
     * @return mixed
     */
    protected function fillCustomer($order)
    {
        $orderCustomerId = $order->getCustomerId();
        if ($orderCustomerId) {
            $this->customersTab($orderCustomerId);
        } else {
            $this->totalGuests++;
        }
        $customerXml = $this->doc->createElement(ConstRegistry::TAG_CUSTOMER);
        $extensionAttributes = $order->getExtensionAttributes();
        $shippingAssignments = $extensionAttributes->getShippingAssignments();
        if (count($shippingAssignments)) {
            $shippingAssignment = current($shippingAssignments);
            $shipping = $shippingAssignment->getShipping();
            $shippingAddress = $shipping->getAddress();
            $customerXml->appendChild($this->getDeliveryAddress($shippingAddress, $order));
        }
        if (!$this->salesHelper->isAnonymousOrder($order)) {
            $orderEmail = $order->getCustomerEmail();
            $customer = $order->getCustomer();
            if (empty($customer) && !empty($orderEmail)) {
                try {
                    $customer = $this->customerRepository->getById($order->getCustomerId());
                } catch (NoSuchEntityException $exception) {
                    // We don't need to do anything more if there are no customer existing
                }
                if ($customer && $customer->getId()) {
                    // Contact Preferences
                    $contactPreferenceXml = $this->doc->createElement(ConstRegistry::TAG_CONTACT_PREFERENCE);
                    $contactPreferenceXml->appendChild($this->getChildNode(ConstRegistry::TAG_EMAIL, $orderEmail));

                    $customerOptinAttribute = $customer->getCustomAttribute(Data::ATTRIBUTE_NEWSLETTER_OPTIN);
                    $customerOptin = 0;
                    if ($customerOptinAttribute) {
                        $customerOptin = (int) $customerOptinAttribute->getValue();
                    }
                    $contactPreferenceXml->appendChild($this->getChildNode(ConstRegistry::TAG_MAILING_AUTHORIZATION, $customerOptin));

                    $customerXml->appendChild($contactPreferenceXml);
                }

            }
        }

        return $customerXml;
    }

    /**
     * Build XML for delivery address
     * @param OrderAddressInterface $address
     * @param Order $order
     * @return mixed
     */
    protected function getDeliveryAddress($address, $order)
    {
        $addressXml = $this->doc->createElement(ConstRegistry::TAG_DELIVERY_ADDRESS);
        $orderLanguage = $order->getLanguage();
        $street = $address->getStreet();
        if (is_array($street)) {
            $street = current($street);
        }

        // XML Mapping of address data
        $xmlMapping = [
            ConstRegistry::TAG_ADDRESS_LINE1 => $street,
            ConstRegistry::TAG_POST_CODE => $address->getPostcode(),
            ConstRegistry::TAG_CITY => $address->getCity()
        ];

        if (!empty($address->getCompany())) {
            $xmlMapping[ConstRegistry::TAG_CONTACTNAME] = $address->getLastname();
            $xmlMapping[ConstRegistry::TAG_COMPANY] = $address->getCompany();
        } else {
            $xmlMapping[ConstRegistry::TAG_CIVILITY] = $order->getCustomerGender();
            $xmlMapping[ConstRegistry::TAG_NAME] = $address->getLastname();
            $xmlMapping[ConstRegistry::TAG_FIRST_NAME] = $address->getFirstname();
        }

        if (!empty($orderLanguage)) {
            $xmlMapping[ConstRegistry::TAG_LANGUAGE] = $orderLanguage;
        }

        return $this->mapArrayToXml($addressXml, $xmlMapping);
    }

    /**
     * @param $phoneDatas
     * @return mixed
     */
    protected function getPhone($phoneDatas)
    {
        $phoneXml = $this->doc->createElement(ConstRegistry::TAG_PHONE);
        // XML Mapping of phone data
        $xmlMapping = [
            ConstRegistry::TAG_TYPE => '',
            ConstRegistry::TAG_PREFIX => '',
            ConstRegistry::TAG_PHONE_NUMBER => $phoneDatas
        ];

        return $this->mapArrayToXml($phoneXml, $xmlMapping);
    }

    /**
     * fill the xml footer
     */
    protected function fillEndInfos()
    {
        $this->getTotalOrders();
        $this->getTotalQty();
        $this->getTotalCutomers();
        $this->getTotalMembers();
    }

    /**
     * @param $tag
     * @param $value
     * @return mixed
     */
    protected function getChildNode($tag, $value)
    {
        $child = $this->doc->createElement($tag);
        $child->appendChild(
            $this->doc->createTextNode($value)
        );
        return $child;
    }

    /**
     * @param Order $order
     */
    protected function fillRecord($order)
    {
        $nespressoPurchasePointId = $order->getNespressoPurchasePointId();
        $isNespressoPurchasePoint = $this->salesHelper->isNespressoPurchasePoint($nespressoPurchasePointId);
        $nespressoClubMemberId = $order->getNespressoClubMemberId();
        $isAddressChanged = (bool) $order->getIsAddressChanged();
        $identificationMode = $this->salesHelper->getCustomerIdentificationMode($order);

        $record = $this->doc->createElement(ConstRegistry::TAG_RECORD);

        if ($identificationMode == ConstRegistry::IS_REGISTERED) {
            $this->membersTab($nespressoClubMemberId);
        } else if ($identificationMode == ConstRegistry::IS_NEW) {
            $nespressoClubMemberId = '';
            $record->appendChild($this->getChildNode(ConstRegistry::TAG_CHANNEL, 1));
            $isAddressChanged = true;
        } else {
            $nespressoClubMemberId = ConstRegistry::DEFAULT_MEMBER_NUMBER;
        }

        if (!empty($nespressoClubMemberId)) {
            $record->appendChild($this->getChildNode(ConstRegistry::TAG_MEMBER_ID, $nespressoClubMemberId));
        }

        $record->appendChild($this->getChildNode(ConstRegistry::TAG_IDENTIFICATION_MODE, $identificationMode));

        if (!$isNespressoPurchasePoint && $isAddressChanged) {
            $record->appendChild($this->getChildNode(ConstRegistry::TAG_ADDRESS_CHANGES, $isAddressChanged));
            $record->appendChild($this->fillCustomer($order));
        }

        $record->appendChild($this->fillOrder($order));

        $this->batchInfo->appendChild($record);
    }

    /**
     * Print the order informations
     * @param Order $item
     * @return mixed
     */
    protected function fillOrder($item)
    {
        $nespressoPurchasePointId = $item->getNespressoPurchasePointId();
        $order = $this->doc->createElement(ConstRegistry::TAG_ORDER);
        $isNespressoPurchasePoint = $this->salesHelper->isNespressoPurchasePoint($nespressoPurchasePointId);
        $date = date_create_from_format('Y-m-d H:i:s', $item->getCreatedAt());
        $date->setTimezone(new \DateTimeZone($this->configHelper->getLocal()));
        $order->appendChild($this->getChildNode(ConstRegistry::TAG_ORDER_DATE, $date->format('Y-m-d')));
        $order->appendChild($this->getChildNode(ConstRegistry::TAG_ORDER_TIME, $date->format('His')));
        if ($isNespressoPurchasePoint) {
            $order->appendChild($this->getChildNode(ConstRegistry::TAG_MOVEMENT_CODE, $this->configHelper->getMovementCode0()));
            $order->appendChild($this->getChildNode(ConstRegistry::TAG_PAYMENT_MODE, $this->configHelper->getPaymentMode0()));
            $order->appendChild($this->getChildNode(ConstRegistry::TAG_ORDER_SOURCE, $this->configHelper->getOrderSource0()));
            $order->appendChild($this->getChildNode(ConstRegistry::TAG_STOCK, $this->configHelper->getStock0()));
            $order->appendChild($this->getChildNode(ConstRegistry::TAG_PAYMENT_MODULE, $nespressoPurchasePointId));
        } else {
            $order->appendChild($this->getChildNode(ConstRegistry::TAG_MOVEMENT_CODE, $this->configHelper->getMovementCode1()));
            $order->appendChild($this->getChildNode(ConstRegistry::TAG_PAYMENT_MODE, $this->configHelper->getPaymentMode1()));
            $order->appendChild($this->getChildNode(ConstRegistry::TAG_ORDER_SOURCE, $this->configHelper->getOrderSource1()));
            $order->appendChild($this->getChildNode(ConstRegistry::TAG_STOCK, $this->configHelper->getStock1()));
            $order->appendChild($this->getChildNode(ConstRegistry::TAG_PAYMENT_MODULE, ConstRegistry::PAYMENT_MODULE_DEFAULT));
        }

        if ($isNespressoPurchasePoint) {
            $order->appendChild($this->getChildNode(ConstRegistry::TAG_DELIVERY_MODE, $this->configHelper->getDeliveryMode0()));
        } else {
            $day = $date->format('w');
            $hour = $date->format('G');
            $min = $date->format('i');

            //If the date of order is between thursday 04:30 PM and Friday 04:30 PM we set to another delivery mode
            if (intval($day) === 4 && intval($hour) >= 16) {
                if (intval($hour) === 16 && intval($min) < 30) {
                    $order->appendChild($this->getChildNode(ConstRegistry::TAG_DELIVERY_MODE, $this->configHelper->getDeliveryMode1()));
                } else {
                    $order->appendChild($this->getChildNode(ConstRegistry::TAG_DELIVERY_MODE, $this->configHelper->getDeliveryModeWeekend1()));
                }
            } elseif (intval($day) === 5 && intval($hour) < 17) {
                if (intval($hour) === 16 && intval($min) >= 30) {
                    $order->appendChild($this->getChildNode(ConstRegistry::TAG_DELIVERY_MODE, $this->configHelper->getDeliveryMode1()));
                } else {
                    $order->appendChild($this->getChildNode(ConstRegistry::TAG_DELIVERY_MODE, $this->configHelper->getDeliveryModeWeekend1()));
                }
            } else {
                $order->appendChild($this->getChildNode(ConstRegistry::TAG_DELIVERY_MODE, $this->configHelper->getDeliveryMode1()));
            }
        }
        $baseTotalDue = $item->getBaseTotalDue();
        $orderCurrencyCode = $item->getOrderCurrencyCode();
        $order->appendChild($this->getChildNode(ConstRegistry::TAG_INVOICE_AMOUNT, number_format($baseTotalDue, 2)));
        $order->appendChild($this->getChildNode(ConstRegistry::TAG_NES_ORIGIN, ConstRegistry::NES_ORIGIN));
        $order->appendChild($this->getChildNode(ConstRegistry::TAG_EXTERNAL_ORDER_ID, $this->provider->getRowData($item)[1]));
        $order->appendChild($this->getChildNode(ConstRegistry::TAG_PREPAID_AMOUNT, number_format($baseTotalDue, 2)));
        $order->appendChild($this->getChildNode(ConstRegistry::TAG_CURRENCY, $orderCurrencyCode));
        $order->appendChild($this->fillOrderDetails($item));
        return $order;
    }

    /**
     * @param Order $order
     * @return mixed
     */
    protected function fillOrderDetails($order)
    {
        $orderDetails = $this->doc->createElement(ConstRegistry::TAG_ORDER_DETAILS);
        $i = 1;
        /** @var OrderItemInterface $product */
        foreach ($order->getAllVisibleItems() as $product) {
            $orderLine = $this->doc->createElement(ConstRegistry::TAG_ORDER_LINE);
            $orderLine->appendChild($this->getChildNode(ConstRegistry::TAG_PRODUCT_CODE, $product->getSku()));
            $qtyOrdered = $product->getQtyOrdered();
            $this->totalQty += $qtyOrdered;
            $orderLine->appendChild($this->getChildNode(ConstRegistry::TAG_QUANTITY, number_format($qtyOrdered)));
            $orderLine->appendChild($this->getChildNode(ConstRegistry::TAG_LINE_NR, $i));
            $orderDetails->appendChild($orderLine);
            $i++;
        }
        return $orderDetails;
    }

    /**
     * @param $document
     * @param $xmlMapping
     * @return mixed
     */
    protected function mapArrayToXml($document, $xmlMapping)
    {
        foreach ($xmlMapping as $destinationField => $data) {
            if (is_array($data) && !empty($data)) {
                $childDoc = $this->doc->createElement($destinationField);
                $childNode = $this->mapArrayToXml($childDoc, $data);
                $document->appendChild($childNode);
            } else {
                $node = $this->getChildNode($destinationField, $data);
                $document->appendChild($node);
            }
        }
        return $document;
    }
}
