<?php
/**
 * Twint monitor order script.
 *
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
require __DIR__ . '/../../app/bootstrap.php';
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
/** @var \Magento\Framework\ObjectManager\ObjectManager $objectManager */
$objectManager = $bootstrap->getObjectManager();

//Parse param + launch monitoring
if (isset($argv[1])) {
    $orderUuid = $argv[1];
    $magentoOrderId = $argv[2];
    $cpt = 0;
    do {
        $monitorOrder = $objectManager->create('SQLi\Twint\Model\MonitorOrder');
        try {
            $success = $monitorOrder->sendRequest($orderUuid);
        } catch (\Exception $e) {
            file_put_contents('../../var/log/twint.log', '[TwintMonitorOrder] ERROR orderuuid:' . $orderUuid . ' -> ' . $e->getMessage(), FILE_APPEND);
            $success = 0;
        }

        if ($success !== 0) {
            file_put_contents('../../var/log/twint.log', '[TwintMonitorOrder]: SUCCESS not 0 -> ' . $success, FILE_APPEND);
            break;
        }
        sleep(1);
        $cpt++;
    } while ($cpt < 240);
    if ($success > 0) {
        file_put_contents('../../var/log/twint.log', '[TwintMonitorOrder]: SUCCESS > 0', FILE_APPEND);
        $monitorOrder->updateOrderStatus($success, $magentoOrderId);
    } else {
        file_put_contents('../../var/log/twint.log', '[TwintMonitorOrder]: SUCCESS < 0', FILE_APPEND);
        $monitorOrder->updateOrderStatus(false, $magentoOrderId);
    }
}
