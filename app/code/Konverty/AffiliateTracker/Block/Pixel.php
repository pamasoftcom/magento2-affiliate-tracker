<?php
/**
 * Konverty Affiliate Tracker Pixel Configuration Block
 * 
 * @category  Konverty
 * @package   Konverty_AffiliateTracker
 * @copyright Copyright (c) 2025 Konverty
 */

namespace Konverty\AffiliateTracker\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Konverty\AffiliateTracker\Helper\Data as HelperData;

class Pixel extends Template
{
    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * @param Context $context
     * @param HelperData $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperData $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get pixel configuration as JSON
     *
     * @return string
     */
    public function getConfigJson()
    {
        $config = [
            'endpoint' => $this->helper->getEndpointUrl(),
            'cookiePrefix' => $this->helper->getCookiePrefix(),
            'cookieLifetime' => (int)$this->helper->getCookieLifetime(),
            'platform' => 'magento',
            'enabled' => $this->helper->isEnabled(),
            'trackVisits' => $this->helper->isTrackVisitsEnabled(),
            'trackSales' => $this->helper->isTrackSalesEnabled(),
            'debug' => $this->helper->isDebugMode()
        ];

        return json_encode($config, JSON_UNESCAPED_SLASHES);
    }

    /**
     * Check if tracking is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->helper->isEnabled();
    }
}


