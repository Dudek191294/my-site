<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Data migration: ensure a single SiteSetting row exists so a fresh environment can boot.
 * Does not overwrite existing settings.
 */
final class Version20260831100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert default site_setting row when the table is empty';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            INSERT INTO site_setting (
                site_name,
                role_title,
                headline,
                positioning,
                about_body,
                updated_at
            )
            SELECT
                'Portfolio',
                '',
                'Portfolio',
                '',
                '',
                NOW()
            WHERE NOT EXISTS (SELECT 1 FROM site_setting)
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DELETE FROM site_setting
            WHERE site_name = 'Portfolio'
              AND headline = 'Portfolio'
              AND about_body = ''
              AND (SELECT COUNT(*) FROM site_setting) = 1
            SQL);
    }
}
