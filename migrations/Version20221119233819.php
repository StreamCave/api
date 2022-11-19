<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221119233819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE camera_group (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', id_ninja VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, uplay_tag VARCHAR(255) DEFAULT NULL, position_top INT DEFAULT NULL, position_bottom INT DEFAULT NULL, position_left INT DEFAULT NULL, position_right INT DEFAULT NULL, UNIQUE INDEX UNIQ_1220A202D17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE info_group (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', titre VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, logo LONGTEXT DEFAULT NULL, text_scroll JSON DEFAULT NULL, UNIQUE INDEX UNIQ_67998130D17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE match_group (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', team_name_a VARCHAR(255) DEFAULT NULL, logo_team_a LONGTEXT DEFAULT NULL, team_name_b VARCHAR(255) DEFAULT NULL, logo_team_b VARCHAR(255) DEFAULT NULL, start_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_D3AA3B64D17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE model (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, image LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, price INT NOT NULL, UNIQUE INDEX UNIQ_D79572D9D17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE overlay (id INT AUTO_INCREMENT NOT NULL, model_id INT DEFAULT NULL, user_owner_id INT DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_B9FF3CBED17F50A6 (uuid), UNIQUE INDEX UNIQ_B9FF3CBE7975B7E7 (model_id), INDEX IDX_B9FF3CBE9EB185F9 (user_owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE overlay_user (overlay_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_4E237622F77080E1 (overlay_id), INDEX IDX_4E237622A76ED395 (user_id), PRIMARY KEY(overlay_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE poll_group (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', question LONGTEXT NOT NULL, answers JSON NOT NULL, time INT DEFAULT NULL, good_answer JSON NOT NULL, UNIQUE INDEX UNIQ_B713904D17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE popup_group (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', content LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_1B083912D17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE tweet_group (id INT AUTO_INCREMENT NOT NULL, pseudo VARCHAR(255) DEFAULT NULL, at VARCHAR(255) DEFAULT NULL, avatar LONGTEXT DEFAULT NULL, media_type VARCHAR(255) DEFAULT NULL, media_url LONGTEXT DEFAULT NULL, content LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, token LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649D17F50A6 (uuid), UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE widget (id INT AUTO_INCREMENT NOT NULL, match_group_id INT DEFAULT NULL, info_group_id INT DEFAULT NULL, camera_group_id INT DEFAULT NULL, tweet_group_id INT DEFAULT NULL, poll_group_id INT DEFAULT NULL, popup_group_id INT DEFAULT NULL, model_id INT DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, image LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, visible TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_85F91ED0D17F50A6 (uuid), INDEX IDX_85F91ED0232E03D1 (match_group_id), INDEX IDX_85F91ED0E961A22D (info_group_id), INDEX IDX_85F91ED0C29AEFEF (camera_group_id), INDEX IDX_85F91ED0BDB2B34C (tweet_group_id), INDEX IDX_85F91ED048CEA1F7 (poll_group_id), INDEX IDX_85F91ED079BCB7BD (popup_group_id), INDEX IDX_85F91ED07975B7E7 (model_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('ALTER TABLE overlay ADD CONSTRAINT FK_B9FF3CBE7975B7E7 FOREIGN KEY (model_id) REFERENCES model (id)');
        $this->addSql('ALTER TABLE overlay ADD CONSTRAINT FK_B9FF3CBE9EB185F9 FOREIGN KEY (user_owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE overlay_user ADD CONSTRAINT FK_4E237622F77080E1 FOREIGN KEY (overlay_id) REFERENCES overlay (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE overlay_user ADD CONSTRAINT FK_4E237622A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE widget ADD CONSTRAINT FK_85F91ED0232E03D1 FOREIGN KEY (match_group_id) REFERENCES match_group (id)');
        $this->addSql('ALTER TABLE widget ADD CONSTRAINT FK_85F91ED0E961A22D FOREIGN KEY (info_group_id) REFERENCES info_group (id)');
        $this->addSql('ALTER TABLE widget ADD CONSTRAINT FK_85F91ED0C29AEFEF FOREIGN KEY (camera_group_id) REFERENCES camera_group (id)');
        $this->addSql('ALTER TABLE widget ADD CONSTRAINT FK_85F91ED0BDB2B34C FOREIGN KEY (tweet_group_id) REFERENCES tweet_group (id)');
        $this->addSql('ALTER TABLE widget ADD CONSTRAINT FK_85F91ED048CEA1F7 FOREIGN KEY (poll_group_id) REFERENCES poll_group (id)');
        $this->addSql('ALTER TABLE widget ADD CONSTRAINT FK_85F91ED079BCB7BD FOREIGN KEY (popup_group_id) REFERENCES popup_group (id)');
        $this->addSql('ALTER TABLE widget ADD CONSTRAINT FK_85F91ED07975B7E7 FOREIGN KEY (model_id) REFERENCES model (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE overlay DROP FOREIGN KEY FK_B9FF3CBE7975B7E7');
        $this->addSql('ALTER TABLE overlay DROP FOREIGN KEY FK_B9FF3CBE9EB185F9');
        $this->addSql('ALTER TABLE overlay_user DROP FOREIGN KEY FK_4E237622F77080E1');
        $this->addSql('ALTER TABLE overlay_user DROP FOREIGN KEY FK_4E237622A76ED395');
        $this->addSql('ALTER TABLE widget DROP FOREIGN KEY FK_85F91ED0232E03D1');
        $this->addSql('ALTER TABLE widget DROP FOREIGN KEY FK_85F91ED0E961A22D');
        $this->addSql('ALTER TABLE widget DROP FOREIGN KEY FK_85F91ED0C29AEFEF');
        $this->addSql('ALTER TABLE widget DROP FOREIGN KEY FK_85F91ED0BDB2B34C');
        $this->addSql('ALTER TABLE widget DROP FOREIGN KEY FK_85F91ED048CEA1F7');
        $this->addSql('ALTER TABLE widget DROP FOREIGN KEY FK_85F91ED079BCB7BD');
        $this->addSql('ALTER TABLE widget DROP FOREIGN KEY FK_85F91ED07975B7E7');
        $this->addSql('DROP TABLE camera_group');
        $this->addSql('DROP TABLE info_group');
        $this->addSql('DROP TABLE match_group');
        $this->addSql('DROP TABLE model');
        $this->addSql('DROP TABLE overlay');
        $this->addSql('DROP TABLE overlay_user');
        $this->addSql('DROP TABLE poll_group');
        $this->addSql('DROP TABLE popup_group');
        $this->addSql('DROP TABLE tweet_group');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE widget');
    }
}
