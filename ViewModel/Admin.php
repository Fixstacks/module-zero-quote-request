<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\QuoteRequest\ViewModel;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zero\QuoteRequest\Model\Config;
use Zero\QuoteRequest\Model\Requests;

/**
 * Sales > Quote Requests.
 */
class Admin implements ArgumentInterface
{
    public function __construct(
        private readonly Requests $requests,
        private readonly RequestInterface $request,
        private readonly StoreManagerInterface $storeManager,
        private readonly PriceCurrencyInterface $priceCurrency,
        private readonly Config $config,
        private readonly UrlInterface $url
    ) {
    }

    public function status(): string
    {
        return (string) $this->request->getParam('status');
    }

    public function search(): string
    {
        return trim((string) $this->request->getParam('q'));
    }

    public function all(): array
    {
        return $this->requests->all($this->status(), $this->search());
    }

    public function current(): ?array
    {
        $id = (int) $this->request->getParam('id');

        return $id ? $this->requests->get($id) : null;
    }

    public function items(array $request): array
    {
        return $this->requests->items((int) $request['request_id']);
    }

    public function number(array $request): string
    {
        return Requests::number($request);
    }

    public function storeName(array $request): string
    {
        $store = $this->storeManager->getStore((int) $request['store_id']);

        return $store->getWebsite()->getName() . ' / ' . $store->getName();
    }

    public function validDays(array $request): int
    {
        return $this->config->validDays((int) $request['store_id']);
    }

    public function money(?float $amount, array $request): string
    {
        return $amount === null ? '' : $this->priceCurrency->format($amount, false, 2, (int) $request['store_id'], $request['currency'] ?: null);
    }

    public function storefrontUrl(array $request): string
    {
        return $this->storeManager->getStore((int) $request['store_id'])->getUrl('quotes/view', ['_query' => ['token' => $request['token']]]);
    }

    public function url(string $route, array $params = []): string
    {
        return $this->url->getUrl($route, $params);
    }
}
