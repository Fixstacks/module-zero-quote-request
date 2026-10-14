<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\QuoteRequest\Controller\Adminhtml\Request;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Sales > Quote Requests: every request (or one, ?id=, to answer).
 */
class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Zero_QuoteRequest::requests';

    public function execute()
    {
        $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $page->setActiveMenu('Zero_QuoteRequest::requests');
        $page->getConfig()->getTitle()->prepend(__('Quote Requests'));

        return $page;
    }
}
