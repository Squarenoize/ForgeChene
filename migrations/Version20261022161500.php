<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022161500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make user personal fields nullable for anonymization';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(180) DEFAULT NULL, CHANGE password password VARCHAR(255) DEFAULT NULL, CHANGE firstname firstname VARCHAR(100) DEFAULT NULL, CHANGE lastname lastname VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(180) NOT NULL, CHANGE password password VARCHAR(255) NOT NULL, CHANGE firstname firstname VARCHAR(100) NOT NULL, CHANGE lastname lastname VARCHAR(100) NOT NULL');
    }
}
