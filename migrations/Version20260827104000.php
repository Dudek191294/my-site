<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260827104000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make project.role optional';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project ALTER role DROP NOT NULL');
        $this->addSql("UPDATE project SET role = NULL WHERE role = ''");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE project SET role = '' WHERE role IS NULL");
        $this->addSql('ALTER TABLE project ALTER role SET NOT NULL');
    }
}
