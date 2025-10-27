<?php
/**
 * Konverty Affiliate Tracker Creditmemo Save Observer
 * Sends webhook when credit memo (refund) is created
 * 
 * @category  Konverty
 * @package   Konverty_AffiliateTracker
 * @copyright Copyright (c) 2025 Konverty
 */

namespace Konverty\AffiliateTracker\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Konverty\AffiliateTracker\Helper\Data as HelperData;

class CreditmemoSaveObserver implements ObserverInterface
{
    /**
     * Status ID for refunded orders (matches webhookShopify.jsp)
     */
    const STATUS_REFUNDED = 17;

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

        /** @var Creditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        
        if (!$creditmemo || !$creditmemo->getId()) {
            return;
        }

        $order = $creditmemo->getOrder();
        if (!$order || !$order->getId()) {
            return;
        }

        // Only send webhook on first save (when creditmemo is created)
        if ($creditmemo->getOrigData('entity_id')) {
            return; // Already exists, skip
        }

        $this->helper->log('Credit memo created for order: ' . $order->getIncrementId(), [
            'creditmemo_id' => $creditmemo->getIncrementId(),
            'refund_amount' => $creditmemo->getGrandTotal()
        ]);

        // Prepare webhook data (matching Shopify webhook format)
        $webhookData = [
            'id' => $creditmemo->getId(),
            'order_id' => $order->getIncrementId(),
            'status' => 'refunded',
            'financial_status' => 'refunded',
            'created_at' => date('c'), // ISO 8601 format
            'refund_amount' => (float)$creditmemo->getGrandTotal(),
            'platform' => 'magento'
        ];

        // Send webhook to update order status
        $this->helper->sendWebhook($webhookData, $order->getStoreId());
    }
}


