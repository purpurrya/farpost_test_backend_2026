<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260321180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop created_at from machine and process when column exists';
    }

    public function up(Schema $schema): void
    {
        foreach (['machine', 'process'] as $table) {
            $n = (int) $this->connection->fetchOne(
                'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                [$table, 'created_at']
            );
            if ($n > 0) {
                $this->addSql(sprintf('ALTER TABLE %s DROP COLUMN created_at', $table));
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
