/**
 * Konverty Affiliate Tracking Pixel for Magento 2
 * 
 * @category  Konverty
 * @package   Konverty_AffiliateTracker
 * @copyright Copyright (c) 2025 Konverty
 */

(function() {
    'use strict';

    // Configuration - will be populated by Magento
    var config = window.konvertyConfig || {
        endpoint: 'https://admin.konverty.com/trackShopify.jsp',
        cookiePrefix: 'aff_',
        cookieLifetime: 60,
        platform: 'magento',
        enabled: true,
        trackVisits: true,
        trackSales: true,
        debug: false
    };

    if (!config.enabled) {
        log('Konverty tracking disabled');
        return;
    }

    // Debug logger
    function log(message, data) {
        if (config.debug && console) {
            console.log('[Konverty Pixel]', message, data || '');
        }
    }

    // Cookie utilities
    function setCookie(name, value, days) {
        var expires = new Date(Date.now() + days * 86400000).toUTCString();
        document.cookie = encodeURIComponent(name) + '=' + encodeURIComponent(value) + 
                         '; expires=' + expires + '; path=/; SameSite=Lax';
        log('Cookie set:', name + '=' + value);
    }

    function getCookie(name) {
        var cookies = document.cookie.split('; ');
        for (var i = 0; i < cookies.length; i++) {
            var parts = cookies[i].split('=');
            if (parts.length < 2) continue;
            var cookieName = decodeURIComponent(parts[0]);
            if (cookieName === name) {
                return decodeURIComponent(parts[1]);
            }
        }
        return null;
    }

    function deleteCookie(name) {
        document.cookie = encodeURIComponent(name) + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; SameSite=Lax';
        log('Cookie deleted:', name);
    }

    function getAllAffiliateCookies() {
        var params = {};
        var cookies = document.cookie.split('; ');
        for (var i = 0; i < cookies.length; i++) {
            var parts = cookies[i].split('=');
            if (parts.length < 2) continue;
            var cookieName = decodeURIComponent(parts[0]);
            if (cookieName.indexOf(config.cookiePrefix) === 0) {
                var paramName = cookieName.substring(config.cookiePrefix.length);
                if (paramName) {
                    params[paramName] = decodeURIComponent(parts[1]);
                }
            }
        }
        return params;
    }

    function deleteAllAffiliateCookies() {
        var cookies = document.cookie.split('; ');
        for (var i = 0; i < cookies.length; i++) {
            var parts = cookies[i].split('=');
            if (parts.length < 2) continue;
            var cookieName = decodeURIComponent(parts[0]);
            if (cookieName.indexOf(config.cookiePrefix) === 0) {
                deleteCookie(cookieName);
            }
        }
        log('All affiliate cookies deleted');
    }

    // Get URL parameters
    function getUrlParams() {
        var params = {};
        var searchParams = new URLSearchParams(window.location.search);
        searchParams.forEach(function(value, key) {
            if (value) {
                params[key] = value;
            }
        });
        return params;
    }

    // Send tracking data
    function sendTrackingData(data) {
        log('Sending tracking data:', data);

        // Method 1: POST with fetch (modern browsers)
        if (window.fetch) {
            fetch(config.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data),
                mode: 'no-cors',
                keepalive: true
            }).then(function() {
                log('Tracking data sent via fetch');
            }).catch(function(error) {
                log('Fetch error:', error);
            });
        }

        // Method 2: Fallback with image beacon
        try {
            var url = new URL(config.endpoint);
            url.searchParams.append('type', data.type);
            url.searchParams.append('data', btoa(JSON.stringify(data)));
            
            var img = new Image();
            img.src = url.toString();
            img.style.display = 'none';
            document.body.appendChild(img);
            log('Tracking beacon sent via image');
        } catch (e) {
            log('Beacon error:', e);
        }
    }

    // Track visit
    function trackVisit() {
        if (!config.trackVisits) {
            log('Visit tracking disabled');
            return;
        }

        var urlParams = getUrlParams();
        
        // Only track if there are URL parameters
        if (Object.keys(urlParams).length === 0) {
            log('No URL parameters, skipping visit tracking');
            return;
        }

        // Save all URL parameters as cookies
        for (var key in urlParams) {
            if (urlParams.hasOwnProperty(key)) {
                setCookie(config.cookiePrefix + key, urlParams[key], config.cookieLifetime);
            }
        }

        // Send visit event
        var visitData = {
            type: 'visit',
            platform: config.platform,
            shop: window.location.hostname,
            timestamp: new Date().toISOString(),
            params: urlParams,
            url: window.location.href
        };

        sendTrackingData(visitData);
        log('Visit tracked');
    }

    // Track sale (called from success page)
    function trackSale(orderData) {
        if (!config.trackSales) {
            log('Sale tracking disabled');
            return;
        }

        var affiliateParams = getAllAffiliateCookies();
        
        // Only track if we have affiliate parameters
        if (Object.keys(affiliateParams).length === 0) {
            log('No affiliate cookies found, skipping sale tracking');
            return;
        }

        log('Affiliate params found:', affiliateParams);
        log('Order data:', orderData);

        // Prepare sale data
        var saleData = {
            type: 'sale',
            platform: config.platform,
            shop: window.location.hostname,
            timestamp: new Date().toISOString(),
            order_id: orderData.orderId || orderData.order_id || '',
            total_price: parseFloat(orderData.totalPrice || orderData.total_price || 0),
            subtotal_price: parseFloat(orderData.subtotalPrice || orderData.subtotal_price || 0),
            currency: orderData.currency || 'EUR',
            discount_codes: orderData.discountCodes || orderData.discount_codes || [],
            customer: {
                email: orderData.customerEmail || orderData.customer_email || '',
                phone: orderData.customerPhone || orderData.customer_phone || '',
                name: orderData.customerName || orderData.customer_name || ''
            },
            shipping_address: orderData.shippingAddress || orderData.shipping_address || {},
            billing_address: orderData.billingAddress || orderData.billing_address || {},
            line_items: orderData.lineItems || orderData.line_items || [],
            params: affiliateParams,
            payment_info: orderData.paymentInfo || orderData.payment_info || []
        };

        sendTrackingData(saleData);
        log('Sale tracked');

        // Delete affiliate cookies after successful sale tracking
        setTimeout(function() {
            deleteAllAffiliateCookies();
        }, 1000);
    }

    // Auto-initialize visit tracking on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', trackVisit);
    } else {
        trackVisit();
    }

    // Expose public API
    window.Konverty = window.Konverty || {};
    window.Konverty.trackVisit = trackVisit;
    window.Konverty.trackSale = trackSale;
    window.Konverty.config = config;

    log('Konverty Pixel initialized', config);

})();

