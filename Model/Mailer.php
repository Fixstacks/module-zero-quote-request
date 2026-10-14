<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\QuoteRequest\Model;

use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * The three emails: a new request (to the store), "we got it" and "your
 * quote" (to the customer, with the link to it).
 */
class Mailer
{
    public function __construct(
        private readonly TransportBuilder $transportBuilder,
        private readonly StateInterface $inlineTranslation,
        private readonly StoreManagerInterface $storeManager,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly Config $config,
        private readonly Requests $requests,
        private readonly PriceCurrencyInterface $priceCurrency,
        private readonly Escaper $escaper,
        private readonly LoggerInterface $logger
    ) {
    }

    public function newRequest(array $request): void
    {
        $storeId = (int) $request['store_id'];
        $identity = $this->config->value('notify_identity', $storeId);
        $email = (string) $this->scopeConfig->getValue('trans_email/ident_' . $identity . '/email', ScopeInterface::SCOPE_STORE, $storeId);
        $this->send('template_new', $request, $email, (string) __('Store'));
        $this->send('template_received', $request, (string) $request['email'], (string) $request['name']);
    }

    public function quoted(array $request): void
    {
        $this->send('template_quoted', $request, (string) $request['email'], (string) $request['name']);
    }

    private function send(string $template, array $request, string $to, string $toName): void
    {
        if ($to === '') {
            return;
        }
        $storeId = (int) $request['store_id'];
        $store = $this->storeManager->getStore($storeId);
        $this->inlineTranslation->suspend();
        try {
            $this->transportBuilder
                ->setTemplateIdentifier($this->config->value($template, $storeId))
                ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
                ->setTemplateVars([
                    'number' => Requests::number($request),
                    'name' => (string) $request['name'],
                    'email' => (string) $request['email'],
                    'phone' => (string) $request['phone'],
                    'company' => (string) $request['company'],
                    'comment' => (string) $request['comment'],
                    'answer' => (string) $request['answer'],
                    'valid_until' => $request['expires_at'] ? substr((string) $request['expires_at'], 0, 10) : '',
                    'items_html' => $this->itemsHtml($request),
                    'url' => $store->getUrl('quotes/view', ['_query' => ['token' => $request['token']]]),
                    'store_name' => (string) $store->getFrontendName(),
                ])
                ->setFromByScope($this->config->value('identity', $storeId), $storeId)
                ->addTo($to, $toName)
                ->getTransport()
                ->sendMessage();
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('Zero_QuoteRequest: %s for %s not sent: %s', $template, Requests::number($request), $e->getMessage()));
        } finally {
            $this->inlineTranslation->resume();
        }
    }

    /**
     * The lines as an HTML table (escaped here; the templates print it raw).
     */
    private function itemsHtml(array $request): string
    {
        $quoted = $request['status'] === Requests::QUOTED;
        $rows = '';
        $total = 0.0;
        foreach ($this->requests->items((int) $request['request_id']) as $line) {
            $options = implode(', ', array_map(static fn ($o) => $o['label'] . ': ' . $o['value'], $line['options']));
            $price = $quoted && $line['quoted_price'] !== null ? (float) $line['quoted_price'] : null;
            $total += $price !== null ? $price * (float) $line['qty'] : 0;
            $rows .= '<tr><td style="padding:6px 8px;border-bottom:1px solid #eee;">' . $this->escaper->escapeHtml($line['name'])
                . ($options !== '' ? '<br><small>' . $this->escaper->escapeHtml($options) . '</small>' : '')
                . '</td><td style="padding:6px 8px;border-bottom:1px solid #eee;text-align:right;">' . (float) $line['qty'] . '</td>'
                . ($quoted ? '<td style="padding:6px 8px;border-bottom:1px solid #eee;text-align:right;">' . $this->escaper->escapeHtml($this->money($price, $request)) . '</td>' : '')
                . '</tr>';
        }
        $head = '<tr><th style="text-align:left;padding:6px 8px;">' . $this->escaper->escapeHtml(__('Product')) . '</th><th style="text-align:right;padding:6px 8px;">' . $this->escaper->escapeHtml(__('Qty')) . '</th>'
            . ($quoted ? '<th style="text-align:right;padding:6px 8px;">' . $this->escaper->escapeHtml(__('Price Each')) . '</th>' : '') . '</tr>';
        $foot = $quoted ? '<tr><td colspan="2" style="padding:6px 8px;text-align:right;"><strong>' . $this->escaper->escapeHtml(__('Total')) . '</strong></td><td style="padding:6px 8px;text-align:right;"><strong>' . $this->escaper->escapeHtml($this->money($total, $request)) . '</strong></td></tr>' : '';

        return '<table style="width:100%;border-collapse:collapse;">' . $head . $rows . $foot . '</table>';
    }

    private function money(?float $amount, array $request): string
    {
        if ($amount === null) {
            return '';
        }

        return $this->priceCurrency->format($amount, false, 2, (int) $request['store_id'], $request['currency'] ?: null);
    }
}
