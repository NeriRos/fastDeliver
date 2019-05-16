<?php
namespace LightX\FastDeliver\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'hk_baldar_order'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('hk_baldar_order'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Admin Brand User Id'
            )
            ->addColumn(
                'origin_postcode',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['nullable' => true, 'default' => null],
                'Origin Postcode'
            )
            ->addColumn(
                'origin_street',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Origin Street'
            )
            ->addColumn(
                'origin_house_number',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['nullable' => true, 'default' => null],
                'Origin House Number'
            )
            ->addColumn(
                'origin_town',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Origin Town'
            )
            ->addColumn(
                'origin_company',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Origin Company'
            )
            ->addColumn(
                'baldar_client_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Client Code'
            )
            ->setComment('Baldar Orders');

        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
