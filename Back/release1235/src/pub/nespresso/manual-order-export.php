<?php

require __DIR__ . '/../../app/bootstrap.php';
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
/** @var \Magento\Framework\ObjectManager\ObjectManager $objectManager */
$objectManager = $bootstrap->getObjectManager();

$importManager = $objectManager->create('SQLi\Export\Model\Export\Order');

$importManager->createOrderExportFile();
