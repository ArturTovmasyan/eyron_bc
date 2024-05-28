<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160629195757 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE fos_user CHANGE created created_at DATETIME NOT NULL;");
        $this->addSql("ALTER TABLE fos_user CHANGE updated updated_at DATETIME NOT NULL;");
        $this->addSql("ALTER TABLE fos_user CHANGE birth_date date_of_birth DATETIME DEFAULT NULL;");
        $this->addSql("ALTER TABLE fos_user CHANGE last_name lastname VARCHAR(64) DEFAULT NULL;");
        $this->addSql("ALTER TABLE fos_user CHANGE first_name firstname VARCHAR(64) DEFAULT NULL;");
        $this->addSql("ALTER TABLE fos_user CHANGE language locale VARCHAR(8) DEFAULT NULL;");
        $this->addSql("ALTER TABLE fos_user CHANGE facebook_id facebook_uid VARCHAR(255) DEFAULT NULL;");
        $this->addSql("ALTER TABLE fos_user CHANGE twitter_id twitter_uid VARCHAR(255) DEFAULT NULL;");
        $this->addSql("ALTER TABLE fos_user CHANGE google_id gplus_uid VARCHAR(255) DEFAULT NULL;");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
