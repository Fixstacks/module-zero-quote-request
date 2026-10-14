<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\QuoteRequest\Model;

/**
 * Set while an accepted quote's lines go into the cart: then (only then)
 * Price on Request products may be added, at the quoted price.
 */
class Accepting
{
    private ?array $line = null;

    public function start(array $request, array $line): void
    {
        $this->line = ['request_id' => (int) $request['request_id'], 'item_id' => (int) $line['item_id'], 'qty' => (float) $line['qty']];
    }

    public function stop(): void
    {
        $this->line = null;
    }

    /** @return array{request_id: int, item_id: int, qty: float}|null */
    public function line(): ?array
    {
        return $this->line;
    }
}
