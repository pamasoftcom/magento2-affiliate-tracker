<?php
/**
 * Konverty Affiliate Tracker Order Success Block
 * 
 * @category  Konverty
 * @package   Konverty_AffiliateTracker
 * @copyright Copyright (c) 2025 Konverty
 */

namespace Konverty\AffiliateTracker\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Model\Order;
use Konverty\AffiliateTracker\Helper\Data as HelperData;

class OrderSuccess extends Template
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param HelperData $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        HelperData $helper,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get last order
     *
     * @return Order|null
     */
    public function getOrder()
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    /**
     * Get order data as JSON for tracking
     *
     * @return string
     */
    public function getOrderDataJson()
    {
        $order = $this->getOrder();
        if (!$order || !$order->getId()) {
            return json_encode([]);
        }

        // Prepare line items
        $lineItems = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $lineItems[] = [
                'product_id' => $item->getProductId(),
                'variant_id' => $item->getProductId(), // Magento doesn't have separate variant ID like Shopify
                'title' => $item->getName(),
                'sku' => $item->getSku(),
                'quantity' => (int)$item->getQtyOrdered(),
                'price' => (float)$item->getPriceInclTax(),
                'discounts' => [] // Could be enhanced to include per-item discounts
            ];
        }

        // Prepare discount codes
        $discountCodes = [];
        if ($order->getCouponCode()) {
            $discountCodes[] = $order->getCouponCode();
        }

        // Prepare payment info
        $payment = $order->getPayment();
        $paymentInfo = [];
        if ($payment) {
            $paymentInfo[] = [
                'gateway' => $payment->getMethod(),
                'amount' => (float)$order->getGrandTotal()
            ];
        }

        // Prepare addresses
        $shippingAddress = [];
        if ($order->getShippingAddress()) {
            $shippingAddr = $order->getShippingAddress();
            $shippingAddress = [
                'name' => $shippingAddr->getName(),
                'company' => $shippingAddr->getCompany(),
                'address1' => $shippingAddr->getStreetLine(1),
                'address2' => $shippingAddr->getStreetLine(2),
                'city' => $shippingAddr->getCity(),
                'province' => $shippingAddr->getRegion(),
                'zip' => $shippingAddr->getPostcode(),
                'country' => $shippingAddr->getCountryId(),
                'phone' => $shippingAddr->getTelephone()
            ];
        }

        $billingAddress = [];
        if ($order->getBillingAddress()) {
            $billingAddr = $order->getBillingAddress();
            $billingAddress = [
                'name' => $billingAddr->getName(),
                'company' => $billingAddr->getCompany(),
                'address1' => $billingAddr->getStreetLine(1),
                'address2' => $billingAddr->getStreetLine(2),
                'city' => $billingAddr->getCity(),
                'province' => $billingAddr->getRegion(),
                'zip' => $billingAddr->getPostcode(),
                'country' => $billingAddr->getCountryId(),
                'phone' => $billingAddr->getTelephone()
            ];
        }

        $orderData = [
            'orderId' => $order->getIncrementId(),
            'totalPrice' => (float)$order->getGrandTotal(),
            'subtotalPrice' => (float)$order->getSubtotal(),
            'currency' => $order->getOrderCurrencyCode(),
            'discountCodes' => $discountCodes,
            'customerEmail' => $order->getCustomerEmail(),
            'customerPhone' => $order->getBillingAddress() ? $order->getBillingAddress()->getTelephone() : '',
            'customerName' => $order->getCustomerName(),
            'shippingAddress' => $shippingAddress,
            'billingAddress' => $billingAddress,
            'lineItems' => $lineItems,
            'paymentInfo' => $paymentInfo
        ];

        return json_encode($orderData, JSON_UNESCAPED_SLASHES);
    }

    /**
     * Check if tracking is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->helper->isEnabled() && $this->helper->isTrackSalesEnabled();
    }
}


