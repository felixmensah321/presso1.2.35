<?php

use \Magento\Catalog\Model\Product;
use \Magento\Framework\App\Bootstrap;

require __DIR__ . '/app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);

$obj = $bootstrap->getObjectManager();

// Set the state. You can also set "adminhtml"
$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('adminhtml');

$serviceLocator = new \Zend\ServiceManager\ServiceManager();
$serviceLocator->setService(\Magento\Setup\Mvc\Bootstrap\InitParamListener::BOOTSTRAP_PARAM, []);

$provider = new \Magento\Setup\Model\ObjectManagerProvider($serviceLocator);

$dataSetupFactory = new \Magento\Setup\Module\DataSetupFactory($provider);
/** @var \Magento\Framework\Setup\SchemaSetupInterface | \Magento\Framework\Setup\ModuleDataSetupInterface $setup */
$setup = $dataSetupFactory->create();
$eavSetupFactory = new \Magento\Eav\Setup\EavSetupFactory($obj);
$eavSetup = $eavSetupFactory->create(['setup' => $setup]);


// In this example I used the Customer Entity ID. but you can use \Magento\Catalog\Model\Product::ENTITY among others
$eavSetup->removeAttribute(Product::ENTITY, 'strap_bags');
