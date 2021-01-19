<?php
/**
 * Twint monitor checkin script.
 *
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
require __DIR__ . '/../../app/bootstrap.php';
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
/** @var \Magento\Framework\ObjectManager\ObjectManager $objectManager */
$objectManager = $bootstrap->getObjectManager();

//Parse param + launch monitoring
if (isset($argv[1]) && isset($argv[2]) && isset($argv[3])) {
    $sessionId = $argv[1];
    $pairingUuid = $argv[2];
    $magentoOrderId = $argv[3];
    $cpt = 0;
    $success = false;
    $cancelOrder = false;
    do {
        $monitorCheckin = $objectManager->create('SQLi\Twint\Model\MonitorCheckIn');
        $result = $monitorCheckin->sendRequest($sessionId, $pairingUuid);
        if ($result) {
            $pairingStatus = $monitorCheckin->getIsPairedFromResponse($result);
            if ($pairingStatus) {
                $success = true;
                break;
            }
        }
        sleep(1);
        $cpt++;
    } while ($cpt < 120);

    if ($success) {
        $dbSessionManagement = $objectManager->create('SQLi\Twint\Model\DBSessionManagement');
        $startOrder = $objectManager->create('SQLi\Twint\Model\StartOrder');
        $result = $startOrder->sendRequest($pairingUuid, $dbSessionManagement->getMagentoOrderIdBySessionId($sessionId));

        if ($result) {
            $dbSessionManagement->saveTwintOrderIdBySessionId($sessionId, $result);
            //TODO: replace with deferredInterface as soon it's live on Magento side.
            exec('php pub/nespresso/monitor_order.php ' . $result . ' ' . $magentoOrderId . ' > /dev/null &');
        } else {
            $cancelOrder = true;
        }
    } else {
        $cancelOrder = true;
    }

    if ($cancelOrder) {
        $dbSessionManagement = $objectManager->create('SQLi\Twint\Model\DBSessionManagement');
        $monitorOrder = $objectManager->create('SQLi\Twint\Model\MonitorOrder');
        $monitorOrder->updateOrderStatus(false, $dbSessionManagement->getMagentoOrderIdBySessionId($sessionId));
    }
}
