<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200217143535 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE city (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, post_code VARCHAR(10) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE excursion (id INT AUTO_INCREMENT NOT NULL, place_id INT DEFAULT NULL, date DATETIME NOT NULL, limit_date DATETIME NOT NULL, duration VARCHAR(255) NOT NULL COMMENT \'(DC2Type:dateinterval)\', name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, visibility TINYINT(1) NOT NULL, participant_limit INT NOT NULL, INDEX IDX_9B08E72FDA6A219 (place_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE excursion_user (excursion_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_4429656F4AB4296F (excursion_id), INDEX IDX_4429656FA76ED395 (user_id), PRIMARY KEY(excursion_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE place (id INT AUTO_INCREMENT NOT NULL, city_id INT DEFAULT NULL, adress VARCHAR(255) NOT NULL, INDEX IDX_741D53CD8BAC62AF (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, phone VARCHAR(30) NOT NULL, remember_me TINYINT(1) NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649444F97DD (phone), UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE excursion ADD CONSTRAINT FK_9B08E72FDA6A219 FOREIGN KEY (place_id) REFERENCES place (id)');
        $this->addSql('ALTER TABLE excursion_user ADD CONSTRAINT FK_4429656F4AB4296F FOREIGN KEY (excursion_id) REFERENCES excursion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE excursion_user ADD CONSTRAINT FK_4429656FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE place ADD CONSTRAINT FK_741D53CD8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE place DROP FOREIGN KEY FK_741D53CD8BAC62AF');
        $this->addSql('ALTER TABLE excursion_user DROP FOREIGN KEY FK_4429656F4AB4296F');
        $this->addSql('ALTER TABLE excursion DROP FOREIGN KEY FK_9B08E72FDA6A219');
        $this->addSql('ALTER TABLE excursion_user DROP FOREIGN KEY FK_4429656FA76ED395');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE excursion');
        $this->addSql('DROP TABLE excursion_user');
        $this->addSql('DROP TABLE place');
        $this->addSql('DROP TABLE user');
    }
}
