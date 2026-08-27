<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Align join-table index names with Doctrine mapping expectations.
 */
final class Version20260827092500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename project_stack / experience_stack indexes to Doctrine default names';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER INDEX idx_experience_stack_experience RENAME TO IDX_22FA077C46E90E27');
        $this->addSql('ALTER INDEX idx_experience_stack_stack RENAME TO IDX_22FA077C37C70060');
        $this->addSql('ALTER INDEX idx_project_stack_project RENAME TO IDX_52FD72F4166D1F9C');
        $this->addSql('ALTER INDEX idx_project_stack_stack RENAME TO IDX_52FD72F437C70060');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER INDEX IDX_22FA077C46E90E27 RENAME TO idx_experience_stack_experience');
        $this->addSql('ALTER INDEX IDX_22FA077C37C70060 RENAME TO idx_experience_stack_stack');
        $this->addSql('ALTER INDEX IDX_52FD72F4166D1F9C RENAME TO idx_project_stack_project');
        $this->addSql('ALTER INDEX IDX_52FD72F437C70060 RENAME TO idx_project_stack_stack');
    }
}
