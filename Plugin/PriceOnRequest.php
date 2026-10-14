<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\QuoteRequest\Plugin;

use Magento\Framework\Escaper;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Pricing\SaleableInterface;
use Zero\QuoteRequest\Model\Config;

/**
 * A Price on Request product shows "Price on request" wherever Magento
 * renders its price (product page, listings, widgets); its other price
 * boxes (tier prices, "as low as") show nothing.
 */
class PriceOnRequest
{
    public function __construct(
        private readonly Config $config,
        private readonly Escaper $escaper
    ) {
    }

    public function aroundRender(Render $subject, callable $proceed, $priceCode, SaleableInterface $saleableItem, array $arguments = [])
    {
        if (!$this->config->isQuoteOnly($saleableItem)) {
            return $proceed($priceCode, $saleableItem, $arguments);
        }
        if ($priceCode !== 'final_price') {
            return '';
        }

        return '<div class="price-box zero-price-on-request" data-price-on-request><span class="price">'
            . $this->escaper->escapeHtml(__('Price on request')) . '</span></div>';
    }
}
