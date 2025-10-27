<?php
/**
 * Konverty Affiliate Tracker Data Helper
 * 
 * @category  Konverty
 * @package   Konverty_AffiliateTracker
 * @copyright Copyright (c) 2025 Konverty
 */

namespace Konverty\AffiliateTracker\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLED = 'konverty_affiliate/general/enabled';
    const XML_PATH_ENDPOINT_URL = 'konverty_affiliate/general/endpoint_url';
    const XML_PATH_WEBHOOK_ENDPOINT_URL = 'konverty_affiliate/general/webhook_endpoint_url';
    const XML_PATH_COOKIE_LIFETIME = 'konverty_affiliate/general/cookie_lifetime';
    const XML_PATH_DEBUG_MODE = 'konverty_affiliate/general/debug_mode';
    const XML_PATH_TRACK_VISITS = 'konverty_affiliate/advanced/track_visits';
    const XML_PATH_TRACK_SALES = 'konverty_affiliate/advanced/track_sales';
    const XML_PATH_SEND_WEBHOOKS = 'konverty_affiliate/advanced/send_webhooks';
    const XML_PATH_COOKIE_PREFIX = 'konverty_affiliate/advanced/cookie_prefix';

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param Curl $curl
     */
    public function __construct(
        Context $context,
        Curl $curl
    ) {
        $this->curl = $curl;
        $this->logger = $context->getLogger();
        parent::__construct($context);
    }

    /**
     * Check if module is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get tracking endpoint URL
     *
     * @param int|null $storeId
     * @return string
     */
    public function getEndpointUrl($storeId = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ENDPOINT_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get webhook endpoint URL
     *
     * @param int|null $storeId
     * @return string
     */
    public function getWebhookEndpointUrl($storeId = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_WEBHOOK_ENDPOINT_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get cookie lifetime in days
     *
     * @param int|null $storeId
     * @return int
     */
    public function getCookieLifetime($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_COOKIE_LIFETIME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if debug mode is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isDebugMode($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_DEBUG_MODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if visit tracking is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isTrackVisitsEnabled($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_TRACK_VISITS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if sales tracking is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isTrackSalesEnabled($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_TRACK_SALES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if sending webhooks is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isSendWebhooksEnabled($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_SEND_WEBHOOKS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get cookie prefix
     *
     * @param int|null $storeId
     * @return string
     */
    public function getCookiePrefix($storeId = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_COOKIE_PREFIX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Send webhook notification
     *
     * @param array $data
     * @param int|null $storeId
     * @return bool
     */
    public function sendWebhook($data, $storeId = null)
    {
        if (!$this->isEnabled($storeId) || !$this->isSendWebhooksEnabled($storeId)) {
            $this->log('Webhook sending is disabled');
            return false;
        }

        $endpoint = $this->getWebhookEndpointUrl($storeId);
        if (empty($endpoint)) {
            $this->log('Webhook endpoint URL is not configured');
            return false;
        }

        try {
            $jsonData = json_encode($data);
            $this->log('Sending webhook to: ' . $endpoint, $data);

            $this->curl->setOption(CURLOPT_TIMEOUT, 10);
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
            $this->curl->addHeader('Content-Type', 'application/json');
            $this->curl->addHeader('X-Magento-Webhook', 'true');
            
            $this->curl->post($endpoint, $jsonData);
            
            $statusCode = $this->curl->getStatus();
            $response = $this->curl->getBody();

            $this->log('Webhook response: Status ' . $statusCode, ['response' => $response]);

            if ($statusCode >= 200 && $statusCode < 300) {
                return true;
            } else {
                $this->log('Webhook failed with status ' . $statusCode, ['response' => $response]);
                return false;
            }
        } catch (\Exception $e) {
            $this->log('Webhook exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($message, $context = [])
    {
        if ($this->isDebugMode()) {
            $this->logger->info('[Konverty Affiliate Tracker] ' . $message, $context);
        }
    }
}


