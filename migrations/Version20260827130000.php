<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260827130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Simplify project case-study fields and add SiteSetting section intros';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project ADD challenge TEXT DEFAULT NULL');
        $this->addSql("UPDATE project SET description = overview WHERE TRIM(description) = '' AND TRIM(overview) <> ''");
        $this->addSql(<<<'SQL'
            UPDATE project
            SET challenge = NULLIF(
                TRIM(BOTH FROM (
                    SELECT string_agg(TRIM(value), E'\n\n')
                    FROM jsonb_array_elements_text(challenges::jsonb) AS t(value)
                    WHERE TRIM(value) <> ''
                )),
                ''
            )
            SQL);
        $this->addSql("UPDATE project SET challenge = problem WHERE (challenge IS NULL OR TRIM(challenge) = '') AND TRIM(problem) <> ''");
        $this->addSql('ALTER TABLE project ALTER solution DROP NOT NULL');
        $this->addSql("UPDATE project SET solution = NULL WHERE TRIM(solution) = ''");
        $this->addSql('ALTER TABLE project DROP overview');
        $this->addSql('ALTER TABLE project DROP problem');
        $this->addSql('ALTER TABLE project DROP result');
        $this->addSql('ALTER TABLE project DROP architecture');
        $this->addSql('ALTER TABLE project DROP technical_decisions');
        $this->addSql('ALTER TABLE project DROP challenges');
        $this->addSql('ALTER TABLE project DROP lessons_learned');

        $this->addSql('ALTER TABLE site_setting ADD projects_intro TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE site_setting ADD experience_intro TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE site_setting ADD stack_intro TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE site_setting ADD github_intro TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_setting DROP github_intro');
        $this->addSql('ALTER TABLE site_setting DROP stack_intro');
        $this->addSql('ALTER TABLE site_setting DROP experience_intro');
        $this->addSql('ALTER TABLE site_setting DROP projects_intro');

        $this->addSql('ALTER TABLE project ADD overview TEXT NOT NULL DEFAULT \'\'');
        $this->addSql('ALTER TABLE project ADD problem TEXT NOT NULL DEFAULT \'\'');
        $this->addSql('ALTER TABLE project ADD result TEXT NOT NULL DEFAULT \'\'');
        $this->addSql('ALTER TABLE project ADD architecture JSON NOT NULL DEFAULT \'{"frontend":"","api":"","backend":"","database":"","infrastructure":""}\'');
        $this->addSql('ALTER TABLE project ADD technical_decisions JSON NOT NULL DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE project ADD challenges JSON NOT NULL DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE project ADD lessons_learned JSON NOT NULL DEFAULT \'[]\'');
        $this->addSql("UPDATE project SET problem = challenge WHERE challenge IS NOT NULL AND TRIM(challenge) <> ''");
        $this->addSql("UPDATE project SET solution = '' WHERE solution IS NULL");
        $this->addSql('ALTER TABLE project ALTER solution SET NOT NULL');
        $this->addSql('ALTER TABLE project DROP challenge');
    }
}
