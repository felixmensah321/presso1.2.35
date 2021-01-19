<?php


namespace SQLi\Twint\Logger;

class SoapApiLog
{
    /**
     * @var TwintLogger
     */
    protected $logger;

    /**
     * Plugin constructor.
     * @param TwintLogger $logger
     */
    public function __construct(TwintLogger $logger)
    {
        $this->logger = $logger;
    }

    public function beforeDispatch(
        \Magento\Webapi\Controller\Soap $subject,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->logger->info('SOURCE: ' . $request->getClientIp());
        $this->logger->info('METHOD: ' . $request->getMethod());
        $this->logger->info('PATH: ' . $request->getPathInfo());
        $this->logger->info('CONTENT: ' . $request->getContent() . PHP_EOL);
    }
}