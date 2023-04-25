<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230415201925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE i23_paniers (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_user INTEGER DEFAULT NULL, CONSTRAINT FK_625719616B3CA4B FOREIGN KEY (id_user) REFERENCES i23_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_625719616B3CA4B ON i23_paniers (id_user)');
        $this->addSql('CREATE TABLE i23_paniers_produits (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_panier INTEGER NOT NULL, id_produit INTEGER NOT NULL, quantite INTEGER NOT NULL, prix DOUBLE PRECISION NOT NULL, CONSTRAINT FK_1B4F5C632FBB81F FOREIGN KEY (id_panier) REFERENCES i23_paniers (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1B4F5C63F7384557 FOREIGN KEY (id_produit) REFERENCES i23_produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_1B4F5C632FBB81F ON i23_paniers_produits (id_panier)');
        $this->addSql('CREATE INDEX IDX_1B4F5C63F7384557 ON i23_paniers_produits (id_produit)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1B4F5C632FBB81FF7384557 ON i23_paniers_produits (id_panier, id_produit)');
        $this->addSql('CREATE TABLE i23_produit (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, prix DOUBLE PRECISION NOT NULL, quantite_Stock INTEGER NOT NULL)');
        $this->addSql('CREATE TABLE i23_users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, panier_id INTEGER NOT NULL, login VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, nom VARCHAR(200) DEFAULT NULL, prenom VARCHAR(200) DEFAULT NULL, date_naissance DATE DEFAULT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_67D32048F77D927C FOREIGN KEY (panier_id) REFERENCES i23_paniers (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_67D32048AA08CB10 ON i23_users (login)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_67D32048F77D927C ON i23_users (panier_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE i23_paniers');
        $this->addSql('DROP TABLE i23_paniers_produits');
        $this->addSql('DROP TABLE i23_produit');
        $this->addSql('DROP TABLE i23_users');
    }
}
