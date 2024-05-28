<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE success_story_voters DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE success_story_voters ADD id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, ADD created DATETIME NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX IDX_duplicate_voters ON success_story_voters (success_story_id, user_id)');
        $this->addSql("UPDATE success_story_voters SET created='2016-01-01 00:00:00'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE success_story_voters MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX IDX_duplicate_voters ON success_story_voters');
        $this->addSql('ALTER TABLE success_story_voters DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE success_story_voters DROP id, DROP created');
        $this->addSql('ALTER TABLE success_story_voters ADD PRIMARY KEY (success_story_id, user_id)');
    }
}
