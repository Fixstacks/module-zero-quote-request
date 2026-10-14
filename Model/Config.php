<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\QuoteRequest\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    /** Product attribute: Price on Request. */
    public const QUOTE_ONLY_ATTRIBUTE = 'zero_quote_only';

    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->flag('enabled', $storeId);
    }

    public function cartButton(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId) && $this->flag('cart_button', $storeId);
    }

    public function validDays(?int $storeId = null): int
    {
        return max(1, (int) $this->value('valid_days', $storeId));
    }

    public function value(string $field, ?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue('zero_quote_request/general/' . $field, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isQuoteOnly(mixed $product): bool
    {
        return $product instanceof Product && (bool) $product->getData(self::QUOTE_ONLY_ATTRIBUTE) && $this->isEnabled((int) $product->getStoreId() ?: null);
    }

    private function flag(string $field, ?int $storeId): bool
    {
        return $this->scopeConfig->isSetFlag('zero_quote_request/general/' . $field, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
