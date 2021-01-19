<?php
/**
 * SQLi_AddressVerification extension.
 *
 * @category   SQLi
 * @package    SQLi_AddressVerification
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\AddressVerification\Console\Command;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Console\Cli;
use Magento\Framework\Event\ManagerInterface;
use SQLi\AddressVerification\Helper\Data;
use SQLi\AddressVerification\Helper\TokenChecker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;


/**
 * Class SwissPostConnect
 * @package SQLi\AddressVerification\Console\Command
 */
class SwissPostConnect extends Command
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var TokenChecker
     */
    protected $helperTokenChecker;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Index constructor.
     * @param Data $helperData
     * @param TokenChecker $helperTokenChecker
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $helperData,
        TokenChecker $helperTokenChecker,
        LoggerInterface $logger
    ) {
        $this->helperData = $helperData;
        $this->helperTokenChecker = $helperTokenChecker;
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('swisspost:connect')
             ->setDescription('connects to swisspost openid oauth');

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Process started...');

        $checkToken = $this->helperTokenChecker->getToken();
        if($checkToken) {
            $output->writeln('We have a valid Token...');
            $output->writeln($checkToken);
        }

        $configData = $this->helperData->getAllConfig();
        $activatorObj = $this->helperTokenChecker->getActivatorObj($configData);

        //if we have authorization code and access token already exist
        if(!empty($configData['authorizationCode'])) {
            if(!empty($configData['accessToken']) && !empty($configData['tokenExpiryTime'])) {
                $tokenExpired = $this->helperTokenChecker->hasTokenExpired($configData['tokenExpiryTime']);
                if($tokenExpired) {
                    $response = $this->helperTokenChecker->getNewToken($configData['refreshToken']);
                    if($response) {
                        $output->writeln('successfully got a token...');
                        $output->writeln($checkToken);
                    }
                }
            }
        }

        if(empty($configData['authorizationCode'])) {
            $urlToGoTo = $activatorObj->getAuthorizeUrl();
            if(is_array($urlToGoTo)) {
                $output->writeln($urlToGoTo['message']);
                $this->logger->error('Error message', ['exception' => $urlToGoTo['message']]);
                return false;
            } else {
                header('Location: '.$urlToGoTo);
            }
        }
    }
}
