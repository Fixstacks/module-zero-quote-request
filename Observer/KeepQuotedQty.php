<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\QuoteRequest\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * A quote's lines keep the quoted quantity (the price was for it).
 */
class KeepQuotedQty implements ObserverInterface
{
    public function __construct(private readonly Json $json)
    {
    }

    public function execute(Observer $observer): void
    {
        foreach ($observer->getEvent()->getQuote()->getAllVisibleItems() as $item) {
            $option = $item->getOptionByCode(GuardQuoteOnly::OPTION);
            if (!$option) {
                continue;
            }
            $line = $this->json->unserialize((string) $option->getValue());
            $qty = (float) ($line['qty'] ?? 0);
            if ($qty > 0 && abs((float) $item->getQty() - $qty) > 0.0001) {
                $item->setData('qty', $qty);
            }
        }
    }
}
