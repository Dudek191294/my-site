<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Rename Skill → Stack (tables, joins, icon_color → color) and add website_url.
 * Preserves existing skill rows and M2M relations.
 */
final class Version20260827092000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename skill to stack; rename join tables; add website_url; rename icon_color to color';
    }

    public function up(Schema $schema): void
    {
        // --- project_skill → project_stack ---
        $this->addSql('ALTER TABLE project_skill DROP CONSTRAINT fk_4d68ede9166d1f9c');
        $this->addSql('ALTER TABLE project_skill DROP CONSTRAINT fk_4d68ede95585c142');
        $this->addSql('ALTER TABLE project_skill RENAME TO project_stack');
        $this->addSql('ALTER TABLE project_stack RENAME COLUMN skill_id TO stack_id');
        $this->addSql('ALTER INDEX project_skill_pkey RENAME TO project_stack_pkey');
        $this->addSql('ALTER INDEX idx_4d68ede9166d1f9c RENAME TO idx_project_stack_project');
        $this->addSql('ALTER INDEX idx_4d68ede95585c142 RENAME TO idx_project_stack_stack');

        // --- experience_skill → experience_stack ---
        $this->addSql('ALTER TABLE experience_skill DROP CONSTRAINT fk_3d6f986146e90e27');
        $this->addSql('ALTER TABLE experience_skill DROP CONSTRAINT fk_3d6f98615585c142');
        $this->addSql('ALTER TABLE experience_skill RENAME TO experience_stack');
        $this->addSql('ALTER TABLE experience_stack RENAME COLUMN skill_id TO stack_id');
        $this->addSql('ALTER INDEX experience_skill_pkey RENAME TO experience_stack_pkey');
        $this->addSql('ALTER INDEX idx_3d6f986146e90e27 RENAME TO idx_experience_stack_experience');
        $this->addSql('ALTER INDEX idx_3d6f98615585c142 RENAME TO idx_experience_stack_stack');

        // --- skill → stack ---
        $this->addSql('ALTER TABLE skill RENAME TO stack');
        $this->addSql('ALTER TABLE stack RENAME COLUMN icon_color TO color');
        $this->addSql('ALTER TABLE stack ADD website_url VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER INDEX skill_pkey RENAME TO stack_pkey');
        $this->addSql('ALTER INDEX uniq_skill_slug RENAME TO uniq_stack_slug');
        $this->addSql('ALTER INDEX idx_skill_published_category RENAME TO idx_stack_published_category');
        $this->addSql('ALTER INDEX idx_skill_category_sort RENAME TO idx_stack_category_sort');

        // --- restore FKs to renamed tables/columns ---
        $this->addSql('ALTER TABLE project_stack ADD CONSTRAINT FK_PROJECT_STACK_PROJECT FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_stack ADD CONSTRAINT FK_PROJECT_STACK_STACK FOREIGN KEY (stack_id) REFERENCES stack (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE experience_stack ADD CONSTRAINT FK_EXPERIENCE_STACK_EXPERIENCE FOREIGN KEY (experience_id) REFERENCES experience (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE experience_stack ADD CONSTRAINT FK_EXPERIENCE_STACK_STACK FOREIGN KEY (stack_id) REFERENCES stack (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project_stack DROP CONSTRAINT FK_PROJECT_STACK_PROJECT');
        $this->addSql('ALTER TABLE project_stack DROP CONSTRAINT FK_PROJECT_STACK_STACK');
        $this->addSql('ALTER TABLE experience_stack DROP CONSTRAINT FK_EXPERIENCE_STACK_EXPERIENCE');
        $this->addSql('ALTER TABLE experience_stack DROP CONSTRAINT FK_EXPERIENCE_STACK_STACK');

        $this->addSql('ALTER TABLE stack DROP COLUMN website_url');
        $this->addSql('ALTER TABLE stack RENAME COLUMN color TO icon_color');
        $this->addSql('ALTER TABLE stack RENAME TO skill');
        $this->addSql('ALTER INDEX stack_pkey RENAME TO skill_pkey');
        $this->addSql('ALTER INDEX uniq_stack_slug RENAME TO uniq_skill_slug');
        $this->addSql('ALTER INDEX idx_stack_published_category RENAME TO idx_skill_published_category');
        $this->addSql('ALTER INDEX idx_stack_category_sort RENAME TO idx_skill_category_sort');

        $this->addSql('ALTER TABLE project_stack RENAME COLUMN stack_id TO skill_id');
        $this->addSql('ALTER TABLE project_stack RENAME TO project_skill');
        $this->addSql('ALTER INDEX project_stack_pkey RENAME TO project_skill_pkey');
        $this->addSql('ALTER INDEX idx_project_stack_project RENAME TO idx_4d68ede9166d1f9c');
        $this->addSql('ALTER INDEX idx_project_stack_stack RENAME TO idx_4d68ede95585c142');

        $this->addSql('ALTER TABLE experience_stack RENAME COLUMN stack_id TO skill_id');
        $this->addSql('ALTER TABLE experience_stack RENAME TO experience_skill');
        $this->addSql('ALTER INDEX experience_stack_pkey RENAME TO experience_skill_pkey');
        $this->addSql('ALTER INDEX idx_experience_stack_experience RENAME TO idx_3d6f986146e90e27');
        $this->addSql('ALTER INDEX idx_experience_stack_stack RENAME TO idx_3d6f98615585c142');

        $this->addSql('ALTER TABLE project_skill ADD CONSTRAINT fk_4d68ede9166d1f9c FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_skill ADD CONSTRAINT fk_4d68ede95585c142 FOREIGN KEY (skill_id) REFERENCES skill (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE experience_skill ADD CONSTRAINT fk_3d6f986146e90e27 FOREIGN KEY (experience_id) REFERENCES experience (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE experience_skill ADD CONSTRAINT fk_3d6f98615585c142 FOREIGN KEY (skill_id) REFERENCES skill (id) ON DELETE CASCADE');
    }
}
