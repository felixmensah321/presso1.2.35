<?php

namespace SQLi\Export\Console\Command;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\ObjectManager\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OrdersExportOrderId extends Command
{
    const ORDERID = 'orderId';
    const STATUS = 'status';

    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('orders:export:orderId');
        $this->setDescription('Generate Export XML with orderIds provide in parametters.');
        $this->addOption(
            self::ORDERID,
            null,
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            'Name'
        );

        $this->addOption(
            self::STATUS,
            null,
            InputOption::VALUE_OPTIONAL,
            'Orders status'
        );

        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bootstrap = Bootstrap::create(
            BP,
            $_SERVER
        );
        /** @var ObjectManager $objectManager */
        $objectManager = $bootstrap->getObjectManager();

        $importManager = $objectManager->create("SQLi\Export\Model\Export\Order");
        $args = $input->getOptions();
        if ($args['orderId'] && $importManager->createOrderExportFileWithParams("orderId", $args)) {
            $output->writeln("Process has finished. You can find the file in var/export/xml/cli");
        } else {
            $output->writeln("An error has occurred. Please check arguments");
            $output->writeln('<comment>' . $this->getSynopsis(false) . '</comment>', OutputInterface::OUTPUT_NORMAL);
        }
    }
}
