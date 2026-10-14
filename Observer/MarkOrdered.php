<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\QuoteRequest\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Zero\QuoteRequest\Model\Requests;

/**
 * An order from an accepted quote marks the request ordered.
 */
class MarkOrdered implements ObserverInterface
{
    public function __construct(private readonly Requests $requests)
    {
    }

    public function execute(Observer $observer): void
    {
        $requestId = (int) $observer->getEvent()->getQuote()?->getData('zero_quote_request_id');
        $order = $observer->getEvent()->getOrder();
        if ($requestId && $order) {
            $this->requests->markOrdered($requestId, (string) $order->getIncrementId());
        }
    }
}
