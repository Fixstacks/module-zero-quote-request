<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\QuoteRequest\Controller\Request;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;
use Zero\QuoteRequest\Model\Config;

/**
 * /quotes/request: ask for a quote for the cart (its lines, your details, a
 * comment). An empty cart goes back to the cart page.
 */
class Index implements HttpGetActionInterface
{
    public function __construct(
        private readonly PageFactory $pageFactory,
        private readonly ForwardFactory $forwardFactory,
        private readonly RedirectFactory $redirectFactory,
        private readonly CheckoutSession $checkoutSession,
        private readonly Config $config
    ) {
    }

    public function execute()
    {
        if (!$this->config->cartButton()) {
            return $this->forwardFactory->create()->forward('noroute');
        }
        if (!$this->checkoutSession->getQuote()->getItemsCount()) {
            return $this->redirectFactory->create()->setPath('checkout/cart');
        }
        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set(__('Request a Quote'));
        $page->getConfig()->setRobots('NOINDEX,NOFOLLOW');

        return $page;
    }
}
