<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260317123500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create machine and process tables with relationship';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE machine (
            id INT AUTO_INCREMENT NOT NULL, 
            total_memory INT NOT NULL, 
            total_cpu INT NOT NULL, 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        $this->addSql('CREATE TABLE process (
            id INT AUTO_INCREMENT NOT NULL, 
            required_memory INT NOT NULL, 
            required_cpu INT NOT NULL, 
            machine_id INT DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        $this->addSql('ALTER TABLE process ADD CONSTRAINT FK_PROCESS_MACHINE FOREIGN KEY (machine_id) REFERENCES machine (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_PROCESS_MACHINE ON process (machine_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE process DROP FOREIGN KEY FK_PROCESS_MACHINE');
        $this->addSql('DROP INDEX IDX_PROCESS_MACHINE ON process');
        
        $this->addSql('DROP TABLE process');
        $this->addSql('DROP TABLE machine');
    }
}
