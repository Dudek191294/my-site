<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Drop unused Stack columns: slug, color, website_url.
 */
final class Version20260827103500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove unused stack.slug, stack.color and stack.website_url';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_stack_slug');
        $this->addSql('ALTER TABLE stack DROP slug');
        $this->addSql('ALTER TABLE stack DROP color');
        $this->addSql('ALTER TABLE stack DROP website_url');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stack ADD slug VARCHAR(120) DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE stack ADD color VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE stack ADD website_url VARCHAR(500) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_stack_slug ON stack (slug)');
    }
}
