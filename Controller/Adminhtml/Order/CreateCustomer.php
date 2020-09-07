<?php
namespace Joana\UpdateEmail\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Sales\Model\OrderRepository;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Joana\UpdateEmail\SendEmail\Email;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Customer\Model\Customer;

class CreateCustomer extends Action
{
    /**
     * @var OrderRepository
     */
    private $_orderRepository;
    /**
     * @var CustomerFactory
     */
    private $_customerFactory;
    /**
     * @var ManagerInterface
     */
    private $_messageManager;
    /**
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;
    /**
     * @var Email
     */
    private $_email;


    /**
     * CreateCustomer constructor.
     * @param Action\Context $context
     * @param CustomerFactory $customerFactory
     * @param OrderRepository $orderRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param Email $email
     * @param ManagerInterface $message
     */
    public function __construct(Action\Context $context,CustomerFactory $customerFactory,
                        OrderRepository $orderRepository,ScopeConfigInterface $scopeConfig,
                        Email $email,ManagerInterface $message)
    {
        parent::__construct($context);
        $this->_orderRepository = $orderRepository;
        $this->_customerFactory = $customerFactory;
        $this->_messageManager = $message;
        $this->_email = $email;
        $this->_scopeConfig = $scopeConfig;

    }

    /**
     * Get the value in configuration
     * @return mixed
     */

    private function _getCustomerEmailTemplate()
    {
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->_scopeConfig->getValue("emailsection/customer_create/customer_create_email",$scope);
    }


    /**
     * Create a new customer from order sales button in the top
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        $order = $this->_orderRepository->get($orderId);

        $customerEmail = $order->getCustomerEmail();
        $customerFirstName = $order->getBillingAddress()->getFirstname();
        $customerLastName = $order->getBillingAddress()->getLastname();

        try{
            $customer = $this->_customerFactory->create();

            $customer->setFirstname($customerFirstName);
            $customer->setLastname($customerLastName);
            $customer->setEmail($customerEmail);

            $customer->save();

            $this->_messageManager->addSuccessMessage("New Customer created successfully");
        }
        catch(\Exception $e)
        {
            $this->_messageManager->addError("Customer with this email already exists.");
        }

        if($customer->getId()){

            $this->_setCustomerDataToOrder($order,$customer);

            $this->_email->sendEmail
            (
                $this->_getCustomerEmailTemplate(),
                array($customerEmail),
                array(
                    'email' => 'joanashehaj01@gmail.com',
                    'name' => 'Shero Support'
                ),
                array(
                    'customer_name' =>$order->getCustomerName(),
                    'firstname' => $customerFirstName,
                    'lastname' => $customerLastName,
                    'email' => $customerEmail
                )
            );
        }
        $this->_redirect("sales/order/view/order_id/$orderId");
    }

         /** Create an account for guests users for the current order */
     private function _setCustomerDataToOrder(Order $order,Customer $customer){
        try{
            $order->setCustomerIsGuest(0);
            $order->setCustomerId($customer->getId());
            $order->setCustomerFirstname($customer->getFirstname());
            $order->setCustomerLastname($customer->getLastname());
            $order->setCustomerGroupId($customer->getGroupId());
            $order->save();
            $this->_messageManager->addSuccessMessage("Order Updated");
        }catch(\Exception $e){
            $this->_messageManager->addError(
                $e->getMessage()
            );
        }
    }

}
