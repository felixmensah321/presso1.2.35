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

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\OrderRepositoryInterface;
use SQLi\Customer\Helper\Data;
use SQLi\Twint\Model\ResourceModel\DBSession as DBSessionResourceModel;
use SQLi\Twint\Model\ResourceModel\DBSession\CollectionFactory as DBSessionCollectionFactory;

/**
 * Class DBSessionManagement.
 *
 * @package SQLi\Twint\Model
 */
class DBSessionManagement
{
    /**
     * @var \SQLi\Twint\Model\DBSessionFactory
     */
    protected $dbSessionFactory;

    /**
     * @var DBSessionResourceModel
     */
    protected $dbSessionResourceModel;

    /**
     * @var DBSessionCollectionFactory
     */
    protected $dbSessionCollectionFactory;

    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * DBSessionManagement constructor.
     *
     * @param DBSessionFactory $dbSessionFactory
     * @param DBSessionResourceModel $dbSessionResourceModel
     * @param Json $jsonSerializer
     * @param DBSessionCollectionFactory $dbSessionCollectionFactory
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
      DBSessionFactory $dbSessionFactory,
      DBSessionResourceModel $dbSessionResourceModel,
      Json $jsonSerializer,
      DBSessionCollectionFactory $dbSessionCollectionFactory,
      OrderRepositoryInterface $orderRepository
    ) {
        $this->dbSessionFactory = $dbSessionFactory;
        $this->dbSessionResourceModel = $dbSessionResourceModel;
        $this->jsonSerializer = $jsonSerializer;
        $this->dbSessionCollectionFactory = $dbSessionCollectionFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Save db session row.
     *
     * @param $sessionId
     * @param array|null $customParams
     * @param string|null $customerRelationId
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function saveSessionRow($sessionId, $customParams = null, $customerRelationId = null)
    {
        $dbSessionRow = $this->dbSessionFactory->create();
        $dbSessionRow->setSessionId($sessionId)
            ->setState(\SQLi\Twint\Model\ResourceModel\DBSession\Collection::STATE_NEW);

        if ($customParams) {
            $dbSessionRow->setCustomParams($this->jsonSerializer->serialize($customParams));
        }

        if ($customerRelationId) {
            $dbSessionRow->setCustomerRelationId($customerRelationId);
        }

        $this->dbSessionResourceModel->save($dbSessionRow);
    }

    /**
     * Get row by session id.
     *
     * @param $sessionId
     *
     * @return bool
     */
    public function getRowBySessionId($sessionId)
    {
        $collection = $this->dbSessionCollectionFactory->create()
            ->addFieldToFilter('session_id', $sessionId);

        if ($collection->count() > 0) {
            return $collection->getFirstItem();
        }

        return false;
    }

    /**
     * Get row by session id.
     *
     * @param $sessionId
     * @param $customerRelationId
     * @return bool
     */
    public function getRowBySessionAndRelationId($sessionId, $customerRelationId)
    {
        $collection = $this->dbSessionCollectionFactory->create()
            ->addFieldToFilter('session_id', $sessionId)
            ->addFieldToFilter(Data::ATTRIBUTE_NESPRESSO_CUSTOMER_RELATION_ID, $customerRelationId);

        if ($collection->count() > 0) {
            return $collection->getFirstItem();
        }

        return false;
    }

    /**
     * Check if we have to invalid the session or put it as expired.
     *
     * @param DBSession $session
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function invalidSessionBySession($session)
    {
        if ((int)$session->getState() !== \SQLi\Twint\Model\ResourceModel\DBSession\Collection::STATE_NEW) {
            return false;
        }

        $timeMinus15min = strtotime('-15 min');

        if (strtotime($session->getCreatedAt()) - $timeMinus15min < 0) {
            //Session is expired
            $session->setState(\SQLi\Twint\Model\ResourceModel\DBSession\Collection::STATE_EXPIRED);
            $this->dbSessionResourceModel->save($session);
            return false;
        }

        //Invalid the session
        $session->setState(\SQLi\Twint\Model\ResourceModel\DBSession\Collection::STATE_INVALID);
        $this->dbSessionResourceModel->save($session);

        return true;
    }


    /**
     * Get custom param by session.
     *
     * @param DBSession $session
     *
     * @return array|bool
     */
    public function getCustomParamsBySession($session)
    {
        $customParams = $session->getCustomParams();

        if ($customParams) {
            return $customParams;
        }

        return false;
    }

    /**
     * Get the CustomerRelationUuid by session id.
     *
     * @param string $sessionId
     *
     * @return bool
     */
    public function getCustomerRelationIdBySessionId($sessionId)
    {
        $session = $this->getRowBySessionId($sessionId);

        if ($session) {
            return $session->getCustomerRelationId();
        }

        return false;
    }

    /**
     * Save the PairingUuid and the PairingStatus by session id.
     *
     * @param string $sessionId
     * @param string $pairingId
     * @param string $status
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function savePairingUidAndStatusBySessionId($sessionId, $pairingId, $status)
    {
        $session = $this->getRowBySessionId($sessionId);

        if ($session) {
            $session->setPairingId($pairingId)
                ->setPairingStatus($status);
            $this->dbSessionResourceModel->save($session);

            return true;
        }

        return false;
    }

    /**
     * Save Magento order id by session id.
     *
     * @param string $sessionId
     * @param int $orderId
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function saveMagentoOrderIdBySessionId($sessionId, $orderId)
    {
        $session = $this->getRowBySessionId($sessionId);

        if ($session) {
            $session->setMagentoOrderId($orderId);
            $this->dbSessionResourceModel->save($session);

            return true;
        }

        return false;
    }

    /**
     * Get magento order id by session id.
     *
     * @param string $sessionId
     *
     * @return int|bool
     */
    public function getMagentoOrderIdBySessionId($sessionId)
    {
        $session = $this->getRowBySessionId($sessionId);

        if ($session) {
            return $session->getMagentoOrderId();
        }

        return false;
    }

    /**
     * Save twint order id by session id.
     *
     * @param string $sessionId
     * @param int $orderId
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function saveTwintOrderIdBySessionId($sessionId, $orderId)
    {
        $session = $this->getRowBySessionId($sessionId);

        if ($session) {
            $session->setTwintOrderId($orderId);
            $this->dbSessionResourceModel->save($session);

            //Save the twint order id in the order
            $magentoOrderId = $session->getMagentoOrderId();
            try {
                $order = $this->orderRepository->get($magentoOrderId);
                $order->setTwintOrderId($orderId);
                $this->orderRepository->save($order);
            } catch (\Exception $e) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Get twint order id by session id.
     *
     * @param string $sessionId
     *
     * @return int|bool
     */
    public function getTwintOrderIdBySessionId($sessionId)
    {
        $session = $this->getRowBySessionId($sessionId);

        if ($session) {
            return $session->getTwintOrderId();
        }

        return false;
    }
}

