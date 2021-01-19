<?php
/**
 * SQLi_Twint extension.
 *
 * @category   SQLi
 * @package    SQLi_Twint
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Sales\Model;

use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use SQLi\Twint\Model\DBSessionManagement;
use SQLi\Twint\Model\RequestCheckinFactory;
use SQLi\Twint\Model\Config as TwintConfig;

/**
 * Class Order
 * @package SQLi\Sales\Model
 */
class Order
{
    /**
     * @var TwintConfig
     */
    protected $config;

    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var RequestCheckinFactory
     */
    protected $requestCheckinFactory;

    /**
     * @var DBSessionManagement
     */
    protected $dbSessionManagement;
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;
    /**
     * @var CartInterfaceFactory
     */
    private $cartFactory;
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $_quoteRepo;


    /**
     * Order constructor.
     *
     * @param TwintConfig $config
     * @param CartManagementInterface $cartManagement
     * @param RequestCheckinFactory $requestCheckinFactory
     * @param DBSessionManagement $dbSessionManagement
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepo
     * @param \Magento\Sales\Model\Order $order
     */
    public function __construct(
        TwintConfig $config,
        CartManagementInterface $cartManagement,
        RequestCheckinFactory $requestCheckinFactory,
        DBSessionManagement $dbSessionManagement,
        \Magento\Quote\Model\QuoteRepository $quoteRepo,
        \Magento\Sales\Model\Order $order
    ) {

        $this->config = $config;
        $this->_quoteRepo = $quoteRepo;
        $this->order = $order;
        $this->cartManagement = $cartManagement;
        $this->requestCheckinFactory = $requestCheckinFactory;
        $this->dbSessionManagement = $dbSessionManagement;
    }

    /**
     * Create Order from quote id.
     *
     * @param int $quoteId
     * @param string $sessionId
     *
     * @return array|false|string
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createOrderFromQuoteId($quoteId, $sessionId)
    {
        $orderId = $this->cartManagement->placeOrder($quoteId);

        if (!$orderId) {
            return null;
        }

        //TODO use native magento mtds
        $quote = $this->_quoteRepo->get($quoteId);
        $ordData = $this->order->load($orderId);
        $ordData->setNespressoClubMemberId($quote->getNespressoClubMemberId());
        $ordData->setNespressoPurchasePointId($quote->getNespressoPurchasePointId());
        # set your field name instead of `setCustomOrderAttribute`
        $ordData->save();

        $this->dbSessionManagement->saveMagentoOrderIdBySessionId($sessionId, $orderId);

        //check mock mode
        $mockMode = $this->config->getMockMode();
        if($mockMode) {
            $returnArray = [
                'token'=> '',
                'orderId' => $orderId
            ];
            return json_encode($returnArray);
        }

        $requestCheckin = $this->requestCheckinFactory->create();
        $returnData = $requestCheckin->sendRequest($sessionId);

        if (!isset($returnData['token'])) {
            return null;
        }

        //TODO: replace with deferredInterface as soon it's live on Magento side.
        exec(
            'php pub/nespresso/monitor_checkin.php ' . $sessionId . ' ' . $returnData['pairingUuid'] . ' '
            . $orderId . ' > /dev/null &'
        );

        $returnArray = [
            'token'=> $returnData['token'],
            'orderId' => $orderId
        ];

        return json_encode($returnArray);
    }
}
