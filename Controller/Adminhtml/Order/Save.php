<?php
namespace Joana\UpdateEmail\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Sales\Model\Order\Email\Sender\OrderSenderFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\OrderRepository;
use Joana\UpdateEmail\SendEmail\Email;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    private $_resultPageFactory;
    /**
     * @var OrderRepository
     */
    private $_orderRepository;
    /**
     * @var JsonFactory
     */
    private $_resultFactory;
    /**
     * @var OrderSenderFactory
     */
    private $_orderSender;
    /**
     * @var Email
     */
    private $_email;
    /**
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;


    /**
     * Save constructor.
     * @param Context $context
     * @param JsonFactory $resultFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param PageFactory $resultPageFactory
     * @param OrderRepository $orderRepository
     * @param OrderSenderFactory $orderSenderFactory
     * @param Email $email
     */
    public function __construct(
        Context $context, JsonFactory $resultFactory,ScopeConfigInterface $scopeConfig,
        PageFactory $resultPageFactory,OrderRepository $orderRepository,
        OrderSenderFactory $orderSenderFactory,Email $email
    )
    {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_orderRepository = $orderRepository;
        $this->_resultFactory = $resultFactory;
        $this->_orderSender = $orderSenderFactory;
        $this->_email = $email;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Get the configuration system value
     * @return mixed
     */

    private function _getOrderEditEmailTemplate()
    {
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->_scopeConfig->getValue("emailsection/email_settings/customer_email_changed", $scope);
    }


    /**
     * Save a new email for that guest customer with ajax
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $resultJson = $this->_resultFactory->create();
        $orderId = $this->getRequest()->getParam('order_id', false);
        $email = $this->getRequest()->getParam('email', false);
        $error = false;


        $order = $this->_orderRepository->get($orderId);
        $oldEmail = $order->getCustomerEmail();


        try {
            $order->setCustomerEmail($email);
            $order->save();
            $response = ['error' => $error,"message"=>"Email changed successfully"];
            $resultJson->setData($response);
        } catch (\Exception $e) {
            $error = true;
            $response = ['error' => $error,'message'=>'Cannot change the email address.'];
            $resultJson->setData($response);
        }


         if(!$error)
        {
            $customerName = $order->getCustomerName();
            if(!$order->getCustomerId()){
                $customerName = $order->getBillingAddress()->getFirstname();
            }
            $this->_email->sendEmail
            (
                $this->_getOrderEditEmailTemplate(),
                array($order->getCustomerEmail()),
                array(
                    'email' =>'joanashehaj01@sherocommerce.com',
                    'name' => 'Support'
                ),
                array(
                    'order_id' => $order->getIncrementId(),
                    'customer_name' =>$customerName,
                    'new_email'   => $order->getCustomerEmail(),
                    'old_email' => $oldEmail,
                )
            );

            $sender = $this->_orderSender->create();
            $sender->send($order);
        }
        return $resultJson;
    }
}
