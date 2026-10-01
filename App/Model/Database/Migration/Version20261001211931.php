<?php

declare(strict_types=1);

namespace App\Model\Database\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261001211931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, guid_hash CHAR(64) NOT NULL, url VARCHAR(2048) NOT NULL, title VARCHAR(512) NOT NULL, author VARCHAR(255) DEFAULT NULL, summary LONGTEXT DEFAULT NULL, content_html MEDIUMTEXT DEFAULT NULL, content_text MEDIUMTEXT DEFAULT NULL, full_text_failed TINYINT NOT NULL, published_at DATETIME DEFAULT NULL, fetched_at DATETIME NOT NULL, read_at DATETIME DEFAULT NULL, starred TINYINT NOT NULL, feedback SMALLINT NOT NULL, feed_id INT NOT NULL, main_image_id INT DEFAULT NULL, INDEX idx_article_fetched (fetched_at), INDEX idx_article_published (published_at), INDEX idx_article_feed_published (feed_id, published_at), INDEX idx_article_feed_fetched (feed_id, fetched_at), FULLTEXT INDEX ft_article_search (title, content_text), UNIQUE INDEX uniq_article_feed_guid (feed_id, guid_hash), INDEX IDX_23A0E6651A5BC03 (feed_id), INDEX IDX_23A0E66E4873418 (main_image_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE article_image (article_id INT NOT NULL, image_id INT NOT NULL, INDEX IDX_B28A764E7294869C (article_id), INDEX IDX_B28A764E3DA5256D (image_id), PRIMARY KEY (article_id, image_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE feed (id INT AUTO_INCREMENT NOT NULL, url VARCHAR(2048) NOT NULL, url_hash CHAR(64) NOT NULL, site_url VARCHAR(2048) DEFAULT NULL, title VARCHAR(255) NOT NULL, etag VARCHAR(255) DEFAULT NULL, last_modified VARCHAR(255) DEFAULT NULL, last_fetched_at DATETIME DEFAULT NULL, last_error LONGTEXT DEFAULT NULL, error_count INT NOT NULL, fetch_full_text TINYINT NOT NULL, active TINYINT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_234044ABCFECAB00 (url_hash), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, source_url VARCHAR(2048) NOT NULL, hash CHAR(40) NOT NULL, path VARCHAR(255) DEFAULT NULL, mime_type VARCHAR(32) DEFAULT NULL, size INT DEFAULT NULL, width INT DEFAULT NULL, height INT DEFAULT NULL, status VARCHAR(16) NOT NULL, attempts SMALLINT NOT NULL, downloaded_at DATETIME DEFAULT NULL, feed_id INT NOT NULL, INDEX idx_image_status (status), UNIQUE INDEX uniq_image_feed_hash (feed_id, hash), INDEX IDX_C53D045F51A5BC03 (feed_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE setting (name VARCHAR(64) NOT NULL, value LONGTEXT NOT NULL, PRIMARY KEY (name)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, password_hash VARCHAR(255) NOT NULL, active TINYINT NOT NULL, created_at DATETIME NOT NULL, last_login_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6651A5BC03 FOREIGN KEY (feed_id) REFERENCES feed (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66E4873418 FOREIGN KEY (main_image_id) REFERENCES image (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE article_image ADD CONSTRAINT FK_B28A764E7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_image ADD CONSTRAINT FK_B28A764E3DA5256D FOREIGN KEY (image_id) REFERENCES image (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F51A5BC03 FOREIGN KEY (feed_id) REFERENCES feed (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6651A5BC03');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66E4873418');
        $this->addSql('ALTER TABLE article_image DROP FOREIGN KEY FK_B28A764E7294869C');
        $this->addSql('ALTER TABLE article_image DROP FOREIGN KEY FK_B28A764E3DA5256D');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F51A5BC03');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE article_image');
        $this->addSql('DROP TABLE feed');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE setting');
        $this->addSql('DROP TABLE user');
    }
}
