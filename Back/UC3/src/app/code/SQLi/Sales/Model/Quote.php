<?php
/**
 * SQLi_Sales extension.
 *
 * @category   SQLi
 * @package    SQLi_Sales
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Sales\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\DataObject\Factory;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Store\Model\StoreManagerInterface;
use SQLi\Sales\Helper\Data;

/**
 * Class Quote.
 *
 * @package SQLi\Sales\Model
 */
class Quote
{
    /**
     * @var CartInterfaceFactory
     */
    protected $cartFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Factory
     */
    protected $dataFactory;

    /**
     * @var Rate
     */
    protected $shippingRate;

    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;

    public function __construct(
        CartInterfaceFactory $cartInterfaceFactory,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager,
        CartRepositoryInterface $cartRepository,
        ProductRepositoryInterface $productRepository,
        Factory $dataFactory,
        Rate $shippingRate,
        CartManagementInterface $cartManagement
    ) {
        $this->cartFactory = $cartInterfaceFactory;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->dataFactory = $dataFactory;
        $this->shippingRate = $shippingRate;
        $this->cartManagement = $cartManagement;
    }

    /**
     * Create quote from custom param.
     *
     * @param $customerId
     * @param $products
     * @param $shippingAddress
     * @param $storeId
     * @param string|null $purchasePointId
     *
     * @return mixed
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createQuote(
        $customerId,
        $products,
        $shippingAddress,
        $storeId,
        $purchasePointId = null
    )
    {
        $isGuest = $customerId == 'guest' ? true : false;

        $quote = $this->cartFactory->create();
        if ($shippingAddress) {
            $formatShippingAddress = [
                'lastname' => $shippingAddress['lastName'],
                'street' => $shippingAddress['address'],
                'city' => $shippingAddress['city'],
                'country_id' => 'CH',
                'postcode' => $shippingAddress['zip'],
                'save_in_address_book' => 0
            ];
            if (isset($shippingAddress['company']) && !empty($shippingAddress['company'])) {
                $formatShippingAddress['firstname'] = ".";
                $formatShippingAddress['company'] = $shippingAddress['company'];
            } else {
                $formatShippingAddress['firstname'] = $shippingAddress['firstName'];
            }
            $addressData = [
                'shipping_address' => $formatShippingAddress,
            ];
        } else {
            $addressData = [
                'shipping_address' => [
                    'firstname' => 'Anonymous', //address Details
                    'lastname' => 'Anonymous',
                    'street' => 'Place Saint-Francois 1',
                    'city' => 'Lausanne',
                    'country_id' => 'CH',
                    'region_id' => 126, //Retrieve
                    'postcode' => '1004',
                    'telephone' => '02346234245',
                    'fax' => '56456',
                    'save_in_address_book' => 0
                ],
            ];
        }
        try {
            $store = $this->storeManager->getStore($storeId);
        } catch (\Exception $e) {
            $store = $this->storeManager->getStore(1);
        }
        $quote->setStore($store);

        if (isset($shippingAddress['isAddressChanged'])) {
            $quote->setIsAddressChanged($this->getIsAddressChangedValue($shippingAddress['isAddressChanged']));
        } else {
            $quote->setIsAddressChanged(0);
        }
        if (!$isGuest) {
            $customer= $this->customerRepository->getById($customerId);
            if ($shippingAddress) {
                $customer->setEmail($shippingAddress['email']);
            }
            $quote->assignCustomer($customer); //Assign quote to customer

            //adding nespresso club member id to the quote.
            if ($customer->getCustomAttribute('nespresso_club_member_id')->getValue()) {
                $quote->setNespressoClubMemberId(
                    $customer->getCustomAttribute('nespresso_club_member_id')->getValue()
                );
            }
        } else {
            $quote->setCustomerEmail(Data::ANONYMOUS_EMAIL);
        }

        if ($purchasePointId) {
            $quote->setNespressoPurchasePointId($purchasePointId);
        }

        foreach ($products as $purchase) {
            $product = $this->productRepository->get($purchase['sku']);

            if (!$product->getId()) {
                return false;
            }

            $request = $this->dataFactory->create([
                'product' => $product->getId(),
                'item' => $product->getId(),
                'qty' => $purchase['quantity'],
            ]);

            $quote->addProduct($product, $request);
        }

        $quote->getBillingAddress()->addData($addressData['shipping_address']);
        $quote->getShippingAddress()->addData($addressData['shipping_address']);

        // Collect Rates, Set Shipping & Payment Method
        $this->shippingRate
            ->setCode('freeshipping_freeshipping')
            ->getPrice(1);

        $shippingAddress = $quote->getShippingAddress();

        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod('flatrate_flatrate');

        $quote->getShippingAddress()->addShippingRate($this->shippingRate);

        $this->cartRepository->save($quote);

        $quote->setPaymentMethod('checkmo'); //payment method

        if ($isGuest) {
            $quote->setCheckoutMethod('guest');
        }

        $quote->setInventoryProcessed(false);

        $quote->getPayment()->importData(['method' => 'checkmo']);

        $quote->collectTotals();
        $this->cartRepository->save($quote);

        return $quote;
    }

    /**
     * Get int based on boolean or string boolean value
     *
     * @param $val
     * @return int
     */
    protected function getIsAddressChangedValue($val)
    {
        if ($val === 'true' || $val === true || filter_var($val, FILTER_VALIDATE_BOOLEAN)) {
            return 1;
        }
        return 0;
    }
}
