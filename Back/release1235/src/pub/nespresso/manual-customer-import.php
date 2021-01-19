<?php

require __DIR__ . '/../../app/bootstrap.php';
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
/** @var \Magento\Framework\ObjectManager\ObjectManager $objectManager */
$objectManager = $bootstrap->getObjectManager();

$importManager = $objectManager->create('SQLi\Import\Model\Manager');

if (isset($argv[1])) {
    for ($i=1; $i < $argc; $i++) {
        $files[] = $argv[$i];
    }
    $importManager->launchCustomerImport($files);
} else {
    $importManager->launchCustomerImport();
}



