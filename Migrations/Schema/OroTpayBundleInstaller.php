<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class OroTpayBundleInstaller implements Installation
{
    public function getMigrationVersion(): string
    {
        return 'v1_1';
    }

    public function up(Schema $schema, QueryBag $queries): void
    {
        /** Tables generation **/
        $this->createOroIntegrationTransportTable($schema);

        $this->createOroTpayLabelTable($schema);
        $this->createOroTpayShortLabelTable($schema);
        $this->createOroTpayBlikLabelTable($schema);
        $this->createOroTpayCardsLabelTable($schema);
        $this->createOroTpayPblLabelTable($schema);
        $this->createOroTpayVisaMobileLabelTable($schema);
        $this->createOroTpayPragmaPayLabelTable($schema);
        $this->createOroTpayApplePayLabelTable($schema);
        $this->createOroTpayGooglePayLabelTable($schema);
    }

    /**
     * Create oro_tpay_blik_label table
     */
    private function createOroTpayBlikLabelTable(Schema $schema): void
    {
        if ($schema->hasTable('oro_tpay_blik_label')) {
            return;
        }

        $table = $schema->createTable('oro_tpay_blik_label');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->addIndex(['transport_id'], 'idx_36f174a09909c13f', []);
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id'], 'uniq_36f174a0eb576e89');

        $this->addOroTpayBlikLabelForeignKeys($schema);
    }

    /**
     * Create oro_tpay_cards_label table
     */
    private function createOroTpayCardsLabelTable(Schema $schema): void
    {
        if ($schema->hasTable('oro_tpay_cards_label')) {
            return;
        }

        $table = $schema->createTable('oro_tpay_cards_label');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addIndex(['transport_id'], 'idx_3778ea6a9909c13f', []);
        $table->addUniqueIndex(['localized_value_id'], 'uniq_3778ea6aeb576e89');

        $this->addOroTpayCardsLabelForeignKeys($schema);
    }

    /**
     * Create oro_tpay_pbl_label table
     */
    private function createOroTpayPblLabelTable(Schema $schema): void
    {
        if ($schema->hasTable('oro_tpay_pbl_label')) {
            return;
        }

        $table = $schema->createTable('oro_tpay_pbl_label');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id'], 'uniq_870bbe88eb576e89');
        $table->addIndex(['transport_id'], 'idx_870bbe889909c13f', []);

        $this->addOroTpayPblLabelForeignKeys($schema);
    }

    /**
     * Create oro_tpay_visa_mobile_label table
     */
    private function createOroTpayVisaMobileLabelTable(Schema $schema): void
    {
        if ($schema->hasTable('oro_tpay_visa_mobile_label')) {
            return;
        }

        $table = $schema->createTable('oro_tpay_visa_mobile_label');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->addIndex(['transport_id'], 'idx_c60e2cd99909c13f', []);
        $table->addUniqueIndex(['localized_value_id'], 'uniq_c60e2cd9eb576e89');
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);

        $this->addOroTpayVisaMobileLabelForeignKeys($schema);
    }

    /**
     * Create oro_tpay_pragma_pay_label table
     */
    private function createOroTpayPragmaPayLabelTable(Schema $schema): void
    {
        if ($schema->hasTable('oro_tpay_pragma_pay_label')) {
            return;
        }

        $table = $schema->createTable('oro_tpay_pragma_pay_label');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id'], 'uniq_98beb80ceb576e89');
        $table->addIndex(['transport_id'], 'idx_98beb80c9909c13f', []);

        $this->addOroTpayPragmaPayLabelForeignKeys($schema);
    }

    /**
     * Create oro_tpay_apple_pay_label table
     */
    private function createOroTpayApplePayLabelTable(Schema $schema): void
    {
        if ($schema->hasTable('oro_tpay_apple_pay_label')) {
            return;
        }

        $table = $schema->createTable('oro_tpay_apple_pay_label');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->addIndex(['transport_id'], 'idx_e3520a699909c13f', []);
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id'], 'uniq_e3520a69eb576e89');

        $this->addOroTpayApplePayLabelForeignKeys($schema);
    }

    /**
     * Create oro_tpay_google_pay_label table
     */
    private function createOroTpayGooglePayLabelTable(Schema $schema): void
    {
        if ($schema->hasTable('oro_tpay_google_pay_label')) {
            return;
        }

        $table = $schema->createTable('oro_tpay_google_pay_label');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->addUniqueIndex(['localized_value_id'], 'uniq_dbebf9c9eb576e89');
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addIndex(['transport_id'], 'idx_dbebf9c99909c13f', []);

        $this->addOroTpayGooglePayLabelForeignKeys($schema);
    }

    /**
     * Create oro_integration_transport table
     */
    private function createOroIntegrationTransportTable(Schema $schema): void
    {
        $table = $schema->getTable('oro_integration_transport');

        if (!$table->hasColumn('tpay_client_id')) {
            $table->addColumn('tpay_client_id', 'crypted_string', ['notnull' => false, 'length' => 255, 'comment' => '(DC2Type:crypted_string)']);
        }

        if (!$table->hasColumn('tpay_client_secret')) {
            $table->addColumn('tpay_client_secret', 'crypted_string', ['notnull' => false, 'length' => 255, 'comment' => '(DC2Type:crypted_string)']);
        }

        if (!$table->hasColumn('tpay_merchant_id')) {
            $table->addColumn('tpay_merchant_id', 'crypted_string', ['notnull' => false, 'length' => 255, 'comment' => '(DC2Type:crypted_string)']);
        }

        if (!$table->hasColumn('tpay_google_merchant_id')) {
            $table->addColumn('tpay_google_merchant_id', 'crypted_string', ['notnull' => false, 'length' => 255, 'comment' => '(DC2Type:crypted_string)']);
        }

        if (!$table->hasColumn('tpay_apple_merchant_id')) {
            $table->addColumn('tpay_apple_merchant_id', 'crypted_string', ['notnull' => false, 'length' => 255, 'comment' => '(DC2Type:crypted_string)']);
        }

        if (!$table->hasColumn('tpay_merchant_rsa_key')) {
            $table->addColumn('tpay_merchant_rsa_key', 'crypted_text', ['notnull' => false, 'comment' => '(DC2Type:crypted_text)']);
        }

        if (!$table->hasColumn('tpay_notification_security_code')) {
            $table->addColumn('tpay_notification_security_code', 'crypted_string', ['notnull' => false, 'length' => 255, 'comment' => '(DC2Type:crypted_string)']);
        }

        if (!$table->hasColumn('tpay_production_mode')) {
            $table->addColumn('tpay_production_mode', 'boolean', ['default' => '1', 'notnull' => false]);
        }

        if (!$table->hasColumn('tpay_redirect_hidden_in_checkout')) {
            $table->addColumn('tpay_redirect_hidden_in_checkout', 'boolean', ['default' => '1', 'notnull' => false]);
        }
    }

    /**
     * Create oro_tpay_label table
     */
    private function createOroTpayLabelTable(Schema $schema): void
    {
        if ($schema->hasTable('oro_tpay_label')) {
            return;
        }

        $table = $schema->createTable('oro_tpay_label');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->addUniqueIndex(['localized_value_id'], 'uniq_3f379f71eb576e89');
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addIndex(['transport_id'], 'idx_3f379f719909c13f', []);

        $this->addOroTpayLabelForeignKeys($schema);
    }

    /**
     * Create oro_tpay_short_label table
     */
    private function createOroTpayShortLabelTable(Schema $schema): void
    {
        if ($schema->hasTable('oro_tpay_short_label')) {
            return;
        }

        $table = $schema->createTable('oro_tpay_short_label');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->addIndex(['transport_id'], 'idx_4767a66f9909c13f', []);
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id'], 'uniq_4767a66feb576e89');

        $this->addOroTpayShortLabelForeignKeys($schema);
    }

    /**
     * Add oro_tpay_redirect_label foreign keys.
     */
    private function addOroTpayRedirectLabelForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_tpay_redirect_label');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_transport'),
            ['transport_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_tpay_blik_label foreign keys.
     */
    private function addOroTpayBlikLabelForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_tpay_blik_label');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_transport'),
            ['transport_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_tpay_cards_label foreign keys.
     */
    private function addOroTpayCardsLabelForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_tpay_cards_label');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_transport'),
            ['transport_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_tpay_pbl_label foreign keys.
     */
    private function addOroTpayPblLabelForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_tpay_pbl_label');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_transport'),
            ['transport_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_tpay_visa_mobile_label foreign keys.
     */
    private function addOroTpayVisaMobileLabelForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_tpay_visa_mobile_label');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_transport'),
            ['transport_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_tpay_pragma_pay_label foreign keys.
     */
    private function addOroTpayPragmaPayLabelForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_tpay_pragma_pay_label');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_transport'),
            ['transport_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_tpay_apple_pay_label foreign keys.
     */
    private function addOroTpayApplePayLabelForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_tpay_apple_pay_label');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_transport'),
            ['transport_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_tpay_google_pay_label foreign keys.
     */
    private function addOroTpayGooglePayLabelForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_tpay_google_pay_label');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_transport'),
            ['transport_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_tpay_label foreign keys.
     */
    private function addOroTpayLabelForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_tpay_label');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_transport'),
            ['transport_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_tpay_short_label foreign keys.
     */
    private function addOroTpayShortLabelForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_tpay_short_label');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_transport'),
            ['transport_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }
}
