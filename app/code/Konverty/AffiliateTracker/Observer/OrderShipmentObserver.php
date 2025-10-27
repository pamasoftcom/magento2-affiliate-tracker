<?php
/**
 * Konverty Affiliate Tracker Order Shipment Observer
 * Sends webhook when order is shipped (fulfilled)
 * 
 * @category  Konverty
 * @package   Konverty_AffiliateTracker
 * @copyright Copyright (c) 2025 Konverty
 */

namespace Konverty\AffiliateTracker\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Shipment;
use Konverty\AffiliateTracker\Helper\Data as HelperData;

class OrderShipmentObserver implements ObserverInterface
{
    /**
     * Status ID for fulfilled orders (matches webhookShopify.jsp)
     */
    const STATUS_FULFILLED = 1;

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

        /** @var Shipment $shipment */
        $shipment = $observer->getEvent()->getShipment();
        
        if (!$shipment || !$shipment->getId()) {
            return;
        }

        $order = $shipment->getOrder();
        if (!$order || !$order->getId()) {
            return;
        }

        // Only send webhook on first save (when shipment is created)
        if ($shipment->getOrigData('entity_id')) {
            return; // Already exists, skip
        }

        $this->helper->log('Shipment created for order: ' . $order->getIncrementId(), [
            'shipment_id' => $shipment->getIncrementId(),
            'tracking_numbers' => $this->getTrackingNumbers($shipment)
        ]);

        // Prepare webhook data (matching Shopify webhook format)
        $webhookData = [
            'id' => $shipment->getId(),
            'order_id' => $order->getIncrementId(),
            'status' => 'fulfilled',
            'fulfillment_status' => 'fulfilled',
            'created_at' => date('c'), // ISO 8601 format
            'tracking_numbers' => $this->getTrackingNumbers($shipment),
            'platform' => 'magento'
        ];

        // Send webhook to update order status
        $this->helper->sendWebhook($webhookData, $order->getStoreId());
    }

    /**
     * Get tracking numbers from shipment
     *
     * @param Shipment $shipment
     * @return array
     */
    protected function getTrackingNumbers($shipment)
    {
        $trackingNumbers = [];
        foreach ($shipment->getAllTracks() as $track) {
            $trackingNumbers[] = $track->getTrackNumber();
        }
        return $trackingNumbers;
    }
}


