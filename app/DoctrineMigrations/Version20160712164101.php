<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160712164101 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql('DELETE FROM comment WHERE NOT EXISTS (SELECT g.id FROM goal as g WHERE g.id = comment.thread_id)');
        $this->addSql('DELETE FROM thread WHERE NOT EXISTS (SELECT g.id FROM goal as g WHERE g.id = thread.id)');
        $this->addSql('UPDATE comment as c SET c.thread_id = CONCAT("goal_", (select g.slug from goal as g where g.id = c.thread_id)) WHERE EXISTS (select g.slug from goal as g where g.id = c.thread_id)');
        $this->addSql('UPDATE thread as t SET t.id = CONCAT("goal_", (select g.slug from goal as g where g.id = t.id)) WHERE EXISTS (select g.slug from goal as g where g.id = t.id)');
        $this->addSql('UPDATE thread as t SET t.num_comments = (SELECT COUNT(*) FROM comment as c WHERE c.thread_id = t.id)');
        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
        // this up() migration is auto-generated, please modify it to your needs

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
