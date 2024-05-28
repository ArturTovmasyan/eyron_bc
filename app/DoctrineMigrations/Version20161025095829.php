<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161025095829 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE users_goals set listed_date = "2016:01:01 01:01" WHERE listed_date IS NULL');
        $this->addSql('UPDATE users_goals set urgent = 0 WHERE urgent IS NULL');
        $this->addSql('UPDATE users_goals set important = 0 WHERE important IS NULL');
        $this->addSql('ALTER TABLE users_goals CHANGE listed_date listed_date DATETIME NOT NULL, CHANGE urgent urgent TINYINT(1) NOT NULL, CHANGE important important TINYINT(1) NOT NULL, CHANGE is_visible is_visible TINYINT(1) NOT NULL');

        $this->addSql('UPDATE goal_image set list_image = 0 WHERE list_image IS NULL');
        $this->addSql('UPDATE goal_image set cover_image = 0 WHERE cover_image IS NULL');
        $this->addSql('ALTER TABLE goal_image CHANGE list_image list_image TINYINT(1) NOT NULL, CHANGE cover_image cover_image TINYINT(1) NOT NULL');
        $this->addSql('UPDATE fos_user set created_at = "2016:01:01 01:01" WHERE created_at is NULL ');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE users_goals CHANGE is_visible is_visible TINYINT(1) DEFAULT NULL, CHANGE urgent urgent TINYINT(1) DEFAULT NULL, CHANGE important important TINYINT(1) DEFAULT NULL, CHANGE listed_date listed_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE goal_image CHANGE list_image list_image TINYINT(1) DEFAULT NULL, CHANGE cover_image cover_image TINYINT(1) DEFAULT NULL');
    }
}
