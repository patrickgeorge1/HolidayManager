<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190627102144 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE benefits (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE benefits_user (benefits_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_879ED923731ED7DA (benefits_id), INDEX IDX_879ED923A76ED395 (user_id), PRIMARY KEY(benefits_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE benefits_user ADD CONSTRAINT FK_879ED923731ED7DA FOREIGN KEY (benefits_id) REFERENCES benefits (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE benefits_user ADD CONSTRAINT FK_879ED923A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE benefits_user DROP FOREIGN KEY FK_879ED923731ED7DA');
        $this->addSql('DROP TABLE benefits');
        $this->addSql('DROP TABLE benefits_user');
    }
}
