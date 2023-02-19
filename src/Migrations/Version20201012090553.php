<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201012090553 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE travaux (id INT AUTO_INCREMENT NOT NULL, arbre_id INT DEFAULT NULL, essence_id INT DEFAULT NULL, epaysage_id INT DEFAULT NULL, inventaire_id INT NOT NULL, type VARCHAR(255) NOT NULL, abattage VARCHAR(255) DEFAULT NULL, travaux_collet_multiple LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', travaux_tronc_multiple LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', travaux_houppier_multiple LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', travaux_commentaire VARCHAR(255) DEFAULT NULL, travaux_houppier_other VARCHAR(255) DEFAULT NULL, travaux_collet_other VARCHAR(255) DEFAULT NULL, travaux_tronc_other VARCHAR(255) DEFAULT NULL, travaux_tronc_protection VARCHAR(255) DEFAULT NULL, travaux LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', travaux_other VARCHAR(255) DEFAULT NULL, travaux_soin VARCHAR(255) DEFAULT NULL, travaux_protection VARCHAR(255) DEFAULT NULL, nbre_sujet_concerne INT DEFAULT NULL, status_travaux TINYINT(1) DEFAULT \'1\', date_travaux VARCHAR(255) DEFAULT NULL, date_pro_visite VARCHAR(255) DEFAULT NULL, user_edited_date_travaux DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, groupe_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, statut INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP TABLE parasite');
        $this->addSql('ALTER TABLE profil CHANGE groupe_type groupe_type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE espece ADD created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE arbre CHANGE proximite_other proximite_other LONGTEXT DEFAULT NULL, CHANGE accessibilite_other accessibilite_other TINYTEXT DEFAULT NULL, CHANGE travaux_commentaire travaux_commentaire TEXT DEFAULT NULL, CHANGE critere_other critere_other TINYTEXT DEFAULT NULL, CHANGE risque_general_other risque_general_other TEXT DEFAULT NULL, CHANGE type_passage_other type_passage_other TINYTEXT DEFAULT NULL, CHANGE travaux_tronc_other travaux_tronc_other TEXT DEFAULT NULL, CHANGE travaux_collet_other travaux_collet_other TEXT DEFAULT NULL, CHANGE travaux_houppier_other travaux_houppier_other VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD deleted TINYINT(1) DEFAULT \'0\', ADD city VARCHAR(255) DEFAULT NULL, ADD phone_number VARCHAR(255) DEFAULT NULL, ADD zip_code VARCHAR(255) DEFAULT NULL, ADD address VARCHAR(255) DEFAULT NULL, ADD address2 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE groupe ADD is_stripped TINYINT(1) DEFAULT NULL, ADD sub_id VARCHAR(255) DEFAULT NULL, ADD customer_id VARCHAR(255) DEFAULT NULL, ADD date_echeance DATETIME DEFAULT NULL, ADD date_subscribed DATETIME DEFAULT NULL, ADD siret VARCHAR(255) DEFAULT NULL, ADD num_certification VARCHAR(255) DEFAULT NULL, ADD cp VARCHAR(5) DEFAULT NULL, ADD ville VARCHAR(100) DEFAULT NULL, ADD img_logo VARCHAR(255) DEFAULT NULL, ADD address_societe VARCHAR(255) DEFAULT NULL, ADD name_societe VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE essence CHANGE etat_san_general_other etat_san_general_other TEXT DEFAULT NULL, CHANGE critere_com critere_com TINYTEXT DEFAULT NULL, CHANGE proximite_other proximite_other VARCHAR(255) DEFAULT NULL, CHANGE type_passage_other type_passage_other VARCHAR(35) DEFAULT NULL');
        $this->addSql('ALTER TABLE champignons CHANGE img_url img_url LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE parasite (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE travaux');
        $this->addSql('DROP TABLE notification');
        $this->addSql('ALTER TABLE arbre CHANGE proximite_other proximite_other VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE accessibilite_other accessibilite_other VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE travaux_commentaire travaux_commentaire VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE critere_other critere_other VARCHAR(100) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE risque_general_other risque_general_other VARCHAR(100) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE type_passage_other type_passage_other VARCHAR(100) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE travaux_tronc_other travaux_tronc_other VARCHAR(100) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE travaux_collet_other travaux_collet_other VARCHAR(50) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE travaux_houppier_other travaux_houppier_other VARCHAR(50) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE champignons CHANGE img_url img_url LONGTEXT DEFAULT NULL COLLATE utf8mb4_bin');
        $this->addSql('ALTER TABLE espece DROP created_at');
        $this->addSql('ALTER TABLE essence CHANGE etat_san_general_other etat_san_general_other VARCHAR(100) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE critere_com critere_com VARCHAR(150) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE proximite_other proximite_other VARCHAR(100) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE type_passage_other type_passage_other VARCHAR(100) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE groupe DROP is_stripped, DROP sub_id, DROP customer_id, DROP date_echeance, DROP date_subscribed, DROP siret, DROP num_certification, DROP cp, DROP ville, DROP img_logo, DROP address_societe, DROP name_societe');
        $this->addSql('ALTER TABLE profil CHANGE groupe_type groupe_type VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE user DROP deleted, DROP city, DROP phone_number, DROP zip_code, DROP address, DROP address2');
    }
}
