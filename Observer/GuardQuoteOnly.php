<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\QuoteRequest\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Zero\QuoteRequest\Model\Accepting;
use Zero\QuoteRequest\Model\Config;

/**
 * A Price on Request product only reaches the cart through an accepted
 * quote; a quote's line is marked (zero_quote_request: request, quantity) so
 * it stays apart from the same product bought, at its quantity.
 */
class GuardQuoteOnly implements ObserverInterface
{
    public const OPTION = 'zero_quote_request';

    public function __construct(
        private readonly Accepting $accepting,
        private readonly Config $config,
        private readonly Json $json
    ) {
    }

    public function execute(Observer $observer): void
    {
        $product = $observer->getEvent()->getProduct();
        $line = $this->accepting->line();
        if ($line !== null) {
            $product->addCustomOption(self::OPTION, $this->json->serialize($line));

            return;
        }
        if ($this->config->isQuoteOnly($product)) {
            throw new LocalizedException(__('%1 is priced on request: ask for a quote on its page.', $product->getName()));
        }
    }
}
