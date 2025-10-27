<?php
/**
 * Konverty Affiliate Tracker Order Complete Observer
 * Tracks completed orders (but sale is already tracked by JavaScript pixel)
 * 
 * @category  Konverty
 * @package   Konverty_AffiliateTracker
 * @copyright Copyright (c) 2025 Konverty
 */

namespace Konverty\AffiliateTracker\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Konverty\AffiliateTracker\Helper\Data as HelperData;

class OrderCompleteObserver implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * @param HelperData $helper
     */
    public function __construct(
        HelperData $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->helper->isEnabled() || !$this->helper->isSendWebhooksEnabled()) {
            return;
        }

        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        
        if (!$order || !$order->getId()) {
            return;
        }

        // Log only - the sale tracking is done via JavaScript pixel on success page
        $this->helper->log('Order placed: ' . $order->getIncrementId(), [
            'order_id' => $order->getIncrementId(),
            'status' => $order->getStatus(),
            'state' => $order->getState()
        ]);

        // Note: We don't send webhook here because the sale is tracked by the JavaScript pixel
        // This observer is here for logging and potential future enhancements
    }
}


