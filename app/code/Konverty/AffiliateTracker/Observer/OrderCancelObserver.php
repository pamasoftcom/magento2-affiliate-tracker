<?php
/**
 * Konverty Affiliate Tracker Order Cancel Observer
 * Sends webhook when order is cancelled
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

class OrderCancelObserver implements ObserverInterface
{
    /**
     * Status ID for cancelled orders (matches webhookShopify.jsp)
     */
    const STATUS_CANCELLED = 4;

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

        $this->helper->log('Order cancelled: ' . $order->getIncrementId());

        // Prepare webhook data (matching Shopify webhook format)
        $webhookData = [
            'id' => $order->getIncrementId(),
            'order_id' => $order->getIncrementId(),
            'status' => 'cancelled',
            'financial_status' => 'voided',
            'cancelled_at' => date('c'), // ISO 8601 format
            'platform' => 'magento'
        ];

        // Send webhook to update order status
        $this->helper->sendWebhook($webhookData, $order->getStoreId());
    }
}


