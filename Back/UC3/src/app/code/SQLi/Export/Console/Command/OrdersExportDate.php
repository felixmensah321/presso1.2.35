<?php


namespace SQLi\Export\Console\Command;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\ObjectManager\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OrdersExportDate extends Command
{
    const BEGIN = 'beginDate';
    const END = 'endDate';
    const STATUS = 'status';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('orders:export:date');
        $this->setDescription('Generate Export XML with dates provide in parametters.');
        $this->addOption(
            self::BEGIN,
            null,
            InputOption::VALUE_REQUIRED,
            'Begining date with format MM/DD/YYYY'
        );

        $this->addOption(
            self::END,
            null,
            InputOption::VALUE_OPTIONAL,
            'Ending date with format MM/DD/YYYY'
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
        if ($args['beginDate'] && $importManager->createOrderExportFileWithParams("date", $input->getOptions())) {
            $output->writeln("Process has finished. You can find the file in var/export/xml/cli");
        } else {
            $help = new HelpCommand();
            $help->setCommand($this);
            $output->writeln("An error has occurred. Please check arguments");
            return $help->run($input, $output);

        }
    }
}
