<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250807213848 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4A1F55203D');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4AA76ED395');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4A1F55203D FOREIGN KEY (topic_id) REFERENCES topic (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE completed_course DROP FOREIGN KEY FK_A8BA7457A76ED395');
        $this->addSql('ALTER TABLE completed_course DROP FOREIGN KEY FK_A8BA7457591CC992');
        $this->addSql('ALTER TABLE completed_course ADD CONSTRAINT FK_A8BA7457A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE completed_course ADD CONSTRAINT FK_A8BA7457591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE completed_lesson DROP FOREIGN KEY FK_46506F1DCDF80196');
        $this->addSql('ALTER TABLE completed_lesson DROP FOREIGN KEY FK_46506F1DA76ED395');
        $this->addSql('ALTER TABLE completed_lesson ADD CONSTRAINT FK_46506F1DCDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE completed_lesson ADD CONSTRAINT FK_46506F1DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B9395C3F3');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B591CC992');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13BCDF80196');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B9395C3F3 FOREIGN KEY (customer_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13BCDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4A1F55203D');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4AA76ED395');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4A1F55203D FOREIGN KEY (topic_id) REFERENCES topic (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE completed_course DROP FOREIGN KEY FK_A8BA7457591CC992');
        $this->addSql('ALTER TABLE completed_course DROP FOREIGN KEY FK_A8BA7457A76ED395');
        $this->addSql('ALTER TABLE completed_course ADD CONSTRAINT FK_A8BA7457591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE completed_course ADD CONSTRAINT FK_A8BA7457A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE completed_lesson DROP FOREIGN KEY FK_46506F1DCDF80196');
        $this->addSql('ALTER TABLE completed_lesson DROP FOREIGN KEY FK_46506F1DA76ED395');
        $this->addSql('ALTER TABLE completed_lesson ADD CONSTRAINT FK_46506F1DCDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
        $this->addSql('ALTER TABLE completed_lesson ADD CONSTRAINT FK_46506F1DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13BCDF80196');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B591CC992');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B9395C3F3');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13BCDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B9395C3F3 FOREIGN KEY (customer_id) REFERENCES user (id)');
    }
}
