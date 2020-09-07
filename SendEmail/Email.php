<?php
namespace Joana\UpdateEmail\SendEmail;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Message\ManagerInterface;

class Email
{
    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;
    /**
     * @var TransportBuilder
     */
    private $_transportBuilder;
    /**
     * @var ManagerInterface
     */
    private $_messageManager;

    /**
     * Email constructor.
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param ManagerInterface $manager
     */
    public function __construct( StoreManagerInterface $storeManager,
                                 TransportBuilder $transportBuilder,
                                 ManagerInterface $manager)
    {
        $this->_storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->_messageManager = $manager;
    }


    /**
     * @param $emailTemplate
     * @param $to
     * @param $from
     * @param array $templateVars
     */
    public function sendEmail($emailTemplate, $to, $from, $templateVars = array()){
        try {
            $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getId());
            $transport = $this->_transportBuilder->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError(
                __(
                    $e->getMessage()
                )
            );
        }
    }
}
