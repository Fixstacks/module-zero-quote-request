<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\QuoteRequest\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Zero\QuoteRequest\Model\Config;

/**
 * "Price on Request" on every product, in every attribute set: its price is
 * hidden and Add to Cart becomes a request.
 */
class AddQuoteOnlyAttribute implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory
    ) {
    }

    public function apply(): void
    {
        $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup])->addAttribute(Product::ENTITY, Config::QUOTE_ONLY_ATTRIBUTE, [
            'type' => 'int',
            'label' => 'Price on Request',
            'input' => 'boolean',
            'source' => Boolean::class,
            'note' => 'Hides the price; Add to Cart becomes "Request a Price" (Zero Quote Requests).',
            'required' => false,
            'default' => 0,
            'global' => Attribute::SCOPE_WEBSITE,
            'visible' => true,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'group' => 'General',
            'sort_order' => 210,
        ]);
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
