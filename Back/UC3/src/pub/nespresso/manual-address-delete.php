<?php
require __DIR__ . '/../../app/bootstrap.php';
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
/** @var \Magento\Framework\ObjectManager\ObjectManager $objectManager */
$objectManager = $bootstrap->getObjectManager();
$registry = $objectManager->get('Magento\Framework\Registry');
/** @var $order \Magento\Sales\Model\Order */
$customerCollection = $objectManager->create(\Magento\Customer\Model\ResourceModel\Customer\Collection::class);
$address = $objectManager->create(\Magento\Customer\Model\ResourceModel\Address::class);
$customerCollection->setPageSize(1000);
$total = $customerCollection->getLAstPageNumber();

$currentPage = 0;

$registry->register('isSecureArea', 'true');
while ($currentPage <= $total) {
    $customerCollection->setCurPage($currentPage)->load();
    if ($customerCollection->count() > 0) {
        foreach ($customerCollection as $customer) {
            $adrs = $customer->getAddresses();
            if (is_array($adrs) && count($adrs) > 1) {
                $tmp = null;
                foreach ($adrs as $a) {
                    if ($tmp) {
                        if ($a->getUpdatedAt() > $tmp->getUpdateAt()) {
                            $tmp->delete();
                            $tmp = $a;
                        } else {
                            $a->delete();
                        }
                    } else {
                        $tmp = $a;
                    }
                }
            }
        }
        $registry->unregister('isSecureArea');
    }
    $currentPage++;
}
