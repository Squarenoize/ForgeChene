<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261015161738 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` CHANGE status status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE order_status_history CHANGE status status VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart DROP FOREIGN KEY FK_BA388B7A76ED395');
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25271AD5CDBF');
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25274584665A');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993981AD5CDBF');
        $this->addSql('ALTER TABLE `order` ADD status_id INT NOT NULL, DROP status');
        $this->addSql('CREATE INDEX IDX_F52993986BF700BD ON `order` (status_id)');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F098D9F6D38');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F094584665A');
        $this->addSql('ALTER TABLE order_status_history DROP FOREIGN KEY FK_471AD77E8D9F6D38');
        $this->addSql('ALTER TABLE order_status_history ADD status_id INT NOT NULL, DROP status');
        $this->addSql('CREATE INDEX IDX_471AD77E6BF700BD ON order_status_history (status_id)');
    }
}
