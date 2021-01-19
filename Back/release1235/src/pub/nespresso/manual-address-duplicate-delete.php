<?php
require __DIR__ . '/../../app/bootstrap.php';
$time_start = microtime(true);
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
/** @var \Magento\Framework\ObjectManager\ObjectManager $objectManager */
$objectManager = $bootstrap->getObjectManager();
$registry = $objectManager->get('Magento\Framework\Registry');

$resourceModelAddress = $objectManager->create(\Magento\Customer\Model\ResourceModel\Address::class);
$address = $objectManager->create(\Magento\Customer\Model\Address::class);

$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection = $resource->getConnection();
$registry->register('isSecureArea', 'true');

if (isset($argv[1])) {
    $startFrom = ' OFFSET ' . $argv[1];
}

$sql = "SELECT cae.entity_id
FROM customer_entity ce
         INNER JOIN customer_address_entity cae ON ce.entity_id = cae.parent_id
WHERE cae.updated_at < (SELECT max(updated_at) FROM customer_address_entity WHERE parent_id = ce.entity_id) LIMIT 500 $startFrom";
$countDeleted = 0;

while ($row = $connection->fetchRow($sql)) {
    $address->load($row['entity_id']);
    echo "CustomerId: " . $address->getCustomerId() . PHP_EOL;
    echo "> AddressId to delete: " . $address->getEntityId() . PHP_EOL;
    if ($address) {
        $resourceModelAddress->delete($address);
        echo ">> Deleted " . PHP_EOL;
        $countDeleted++;
    }
}

$registry->unregister('isSecureArea');
echo 'Total deleted: ' . $countDeleted . PHP_EOL;
echo 'Total execution time in seconds: ' . (microtime(true) - $time_start) . PHP_EOL;

