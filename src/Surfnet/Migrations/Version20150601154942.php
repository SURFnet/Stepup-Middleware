<?php

namespace Surfnet\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150601154942 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE raa');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE raa (id VARCHAR(36) NOT NULL COLLATE utf8_unicode_ci, institution VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, name_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, location LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, contact_information LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, INDEX idx_raa_institution (institution), INDEX idx_raa_institution_nameid (institution, name_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }
}
