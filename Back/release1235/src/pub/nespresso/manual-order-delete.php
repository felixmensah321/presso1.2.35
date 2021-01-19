<?php
require __DIR__ . '/../../app/bootstrap.php';
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
/** @var \Magento\Framework\ObjectManager\ObjectManager $objectManager */
$objectManager = $bootstrap->getObjectManager();
$registry = $objectManager->get('Magento\Framework\Registry');
/** @var $order \Magento\Sales\Model\Order */
$orderCollection = $objectManager->create(\Magento\Sales\Model\ResourceModel\Order\Collection::class);
$registry->register('isSecureArea','true');
if ($orderCollection->count() > 0) {
    echo "[Delete Orders] " . $orderCollection->count() . " orders to delete." . PHP_EOL;
    foreach ($orderCollection as $order) {
        $order->delete();
    }
    echo "[Delete Orders] All orders are deleted." . PHP_EOL;
    $registry->unregister('isSecureArea');
} else {
    echo "[Delete Orders] No order to delete." . PHP_EOL;
}