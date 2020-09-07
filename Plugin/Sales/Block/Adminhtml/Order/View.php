<?php
namespace Joana\UpdateEmail\Plugin\Sales\Block\Adminhtml\Order;

use Magento\Sales\Block\Adminhtml\Order\View as OrderView;
use Magento\Framework\App\Config\ScopeConfigInterface;

class View
{
    /**
     * @var ScopeConfigInterface
     */
    protected $getValue;

    /**
     * View constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->getValue = $scopeConfig;
    }

    /*
     * Get the value of the field "Enable Customer Creation for Guest Orders" in the Configuration
     */
    private function isEnable()
    {
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->getValue->getValue("emailsection/customer_create/enable", $scope);
    }

    /**
     * @param OrderView $subject
     */

    public function beforeSetLayout(OrderView $subject)
    {
        /**
         * Check if the customer order is created from the guest and if the configuration field is enable
         */
        if($subject->getOrder()->getCustomerIsGuest() && $this->isEnable() == '1')
        {
            $subject->addButton(
                'order_custom_button',
                [
                    'label' => __('Create Customer Account'),
                    'class' => __('custom-button'),
                    'id' => 'create-customer-account-button',
                    'onclick' => 'setLocation(\'' . $subject->getUrl('email/order/createcustomer') . '\')'
                ]
            );
        }
    }
}
