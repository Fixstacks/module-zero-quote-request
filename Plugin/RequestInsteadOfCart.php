<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\QuoteRequest\Plugin;

use Magento\Catalog\Block\Product\View;
use Magento\Framework\View\Element\Template;
use Zero\QuoteRequest\Model\Config;

/**
 * On a Price on Request product's page, Add to Cart becomes "Request a
 * price" (its form posts the product form's fields with HTMX).
 */
class RequestInsteadOfCart
{
    private const ADD_TO_CART = ['product.info.addtocart', 'product.info.addtocart.additional'];

    public function __construct(private readonly Config $config)
    {
    }

    public function afterToHtml(View $subject, string $html): string
    {
        if (!in_array($subject->getNameInLayout(), self::ADD_TO_CART, true) || !$this->config->isQuoteOnly($subject->getProduct())) {
            return $html;
        }

        return $subject->getLayout()->createBlock(Template::class)
            ->setTemplate('Zero_QuoteRequest::product.phtml')
            ->setData('product', $subject->getProduct())
            ->toHtml();
    }
}
