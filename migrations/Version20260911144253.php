<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260911144253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bandeau (id INT AUTO_INCREMENT NOT NULL, message VARCHAR(255) NOT NULL, niveau VARCHAR(255) NOT NULL, debut_le DATETIME DEFAULT NULL, fin_le DATETIME DEFAULT NULL, actif TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('CREATE TABLE demande (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, nom VARCHAR(160) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, immatriculation VARCHAR(15) DEFAULT NULL, kilometrage INT DEFAULT NULL, creneau_souhaite VARCHAR(255) DEFAULT NULL, message LONGTEXT DEFAULT NULL, consentement_relance TINYINT NOT NULL, statut VARCHAR(255) NOT NULL, cree_le DATETIME NOT NULL, rdv_confirme_le DATETIME DEFAULT NULL, rappel_envoye_le DATETIME DEFAULT NULL, purge_prevue_le DATE NOT NULL, prestation_id INT DEFAULT NULL, vehicule_id INT DEFAULT NULL, INDEX idx_demande_statut (statut), INDEX idx_demande_purge_prevue_le (purge_prevue_le), INDEX IDX_2694D7A59E45C554 (prestation_id), INDEX IDX_2694D7A54A4A3511 (vehicule_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fermeture_exceptionnelle (id INT AUTO_INCREMENT NOT NULL, debut_le DATE NOT NULL, fin_le DATE NOT NULL, libelle VARCHAR(160) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('CREATE TABLE journal_audit (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(80) NOT NULL, entite VARCHAR(80) NOT NULL, entite_id INT DEFAULT NULL, cree_le DATETIME NOT NULL, utilisateur_id INT DEFAULT NULL, INDEX idx_journal_audit_cree_le (cree_le), INDEX IDX_71C3CC53FB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page_locale (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(160) NOT NULL, commune VARCHAR(120) NOT NULL, titre VARCHAR(160) NOT NULL, contenu LONGTEXT NOT NULL, seo_titre VARCHAR(160) DEFAULT NULL, seo_description VARCHAR(320) DEFAULT NULL, actif TINYINT NOT NULL, UNIQUE INDEX uniq_page_locale_slug (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('CREATE TABLE parametres_site (id INT NOT NULL, raison_sociale VARCHAR(180) NOT NULL, adresse VARCHAR(255) NOT NULL, code_postal VARCHAR(10) NOT NULL, ville VARCHAR(120) NOT NULL, telephone VARCHAR(20) NOT NULL, email VARCHAR(180) NOT NULL, siret VARCHAR(14) NOT NULL, latitude NUMERIC(10, 7) DEFAULT NULL, longitude NUMERIC(10, 7) DEFAULT NULL, url_fiche_google VARCHAR(255) DEFAULT NULL, url_leboncoin VARCHAR(255) DEFAULT NULL, numero_whatsapp VARCHAR(20) DEFAULT NULL, delai_reponse_jours_ouvres INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('CREATE TABLE photo_vehicule (id INT AUTO_INCREMENT NOT NULL, fichier VARCHAR(255) NOT NULL, texte_alternatif VARCHAR(255) NOT NULL, ordre INT NOT NULL, principale TINYINT NOT NULL, vehicule_id INT NOT NULL, INDEX IDX_BA13E0974A4A3511 (vehicule_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('CREATE TABLE plage_horaire (id INT AUTO_INCREMENT NOT NULL, jour INT NOT NULL, matin_debut TIME DEFAULT NULL, matin_fin TIME DEFAULT NULL, aprem_debut TIME DEFAULT NULL, aprem_fin TIME DEFAULT NULL, ferme TINYINT NOT NULL, UNIQUE INDEX uniq_plage_horaire_jour (jour), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('CREATE TABLE prestation (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(160) NOT NULL, nom VARCHAR(160) NOT NULL, famille VARCHAR(255) NOT NULL, resume VARCHAR(280) NOT NULL, description LONGTEXT NOT NULL, tarif_indicatif NUMERIC(8, 2) DEFAULT NULL, mention_tarif VARCHAR(255) DEFAULT NULL, mise_en_avant TINYINT NOT NULL, ordre INT NOT NULL, actif TINYINT NOT NULL, image VARCHAR(255) DEFAULT NULL, seo_titre VARCHAR(160) DEFAULT NULL, seo_description VARCHAR(320) DEFAULT NULL, UNIQUE INDEX uniq_prestation_slug (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('CREATE TABLE question_frequente (id INT AUTO_INCREMENT NOT NULL, question VARCHAR(255) NOT NULL, reponse LONGTEXT NOT NULL, categorie VARCHAR(80) DEFAULT NULL, ordre INT NOT NULL, actif TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, nom VARCHAR(120) NOT NULL, actif TINYINT NOT NULL, derniere_connexion_le DATETIME DEFAULT NULL, UNIQUE INDEX uniq_utilisateur_email (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vehicule (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(32) NOT NULL, titre VARCHAR(180) NOT NULL, marque VARCHAR(80) NOT NULL, modele VARCHAR(80) NOT NULL, version VARCHAR(80) DEFAULT NULL, mis_en_circulation_le DATE NOT NULL, kilometrage INT NOT NULL, energie VARCHAR(255) NOT NULL, boite VARCHAR(255) NOT NULL, puissance_fiscale INT DEFAULT NULL, puissance_din INT DEFAULT NULL, prix NUMERIC(9, 2) DEFAULT NULL, description LONGTEXT NOT NULL, statut VARCHAR(255) NOT NULL, url_annonce_externe VARCHAR(255) DEFAULT NULL, publie_le DATETIME DEFAULT NULL, modifie_le DATETIME NOT NULL, UNIQUE INDEX uniq_vehicule_reference (reference), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT FK_2694D7A59E45C554 FOREIGN KEY (prestation_id) REFERENCES prestation (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT FK_2694D7A54A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE journal_audit ADD CONSTRAINT FK_71C3CC53FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE photo_vehicule ADD CONSTRAINT FK_BA13E0974A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A59E45C554');
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A54A4A3511');
        $this->addSql('ALTER TABLE journal_audit DROP FOREIGN KEY FK_71C3CC53FB88E14F');
        $this->addSql('ALTER TABLE photo_vehicule DROP FOREIGN KEY FK_BA13E0974A4A3511');
        $this->addSql('DROP TABLE bandeau');
        $this->addSql('DROP TABLE demande');
        $this->addSql('DROP TABLE fermeture_exceptionnelle');
        $this->addSql('DROP TABLE journal_audit');
        $this->addSql('DROP TABLE page_locale');
        $this->addSql('DROP TABLE parametres_site');
        $this->addSql('DROP TABLE photo_vehicule');
        $this->addSql('DROP TABLE plage_horaire');
        $this->addSql('DROP TABLE prestation');
        $this->addSql('DROP TABLE question_frequente');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE vehicule');
    }
}
