<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250619030245 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, category_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, INDEX IDX_64C19C1727ACA70 (parent_id), INDEX IDX_64C19C112469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, date DATETIME NOT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_F5299398A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE order_item (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, size VARCHAR(10) DEFAULT NULL, color VARCHAR(30) DEFAULT NULL, quantity INT DEFAULT NULL, price NUMERIC(10, 2) NOT NULL, INDEX IDX_52EA1F094584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, name VARCHAR(150) NOT NULL, description LONGTEXT NOT NULL, price_buy NUMERIC(10, 2) NOT NULL, price_sell NUMERIC(10, 2) NOT NULL, image VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_D34A04AD12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE purchase (id INT AUTO_INCREMENT NOT NULL, supplier_id INT DEFAULT NULL, date DATETIME NOT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_6117D13B2ADD6D8C (supplier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE purchase_item (id INT AUTO_INCREMENT NOT NULL, purchase_id INT DEFAULT NULL, product_id INT DEFAULT NULL, quantity INT NOT NULL, price NUMERIC(10, 2) NOT NULL, INDEX IDX_6FA8ED7D558FBEB9 (purchase_id), INDEX IDX_6FA8ED7D4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE supplier (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, phone VARCHAR(20) DEFAULT NULL, address VARCHAR(20) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(50) DEFAULT NULL, email VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE variant (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, size VARCHAR(10) DEFAULT NULL, color VARCHAR(30) DEFAULT NULL, stock_quantity INT NOT NULL, INDEX IDX_F143BFAD4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category ADD CONSTRAINT FK_64C19C112469DE2 FOREIGN KEY (category_id) REFERENCES category (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F094584665A FOREIGN KEY (product_id) REFERENCES product (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B2ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE purchase_item ADD CONSTRAINT FK_6FA8ED7D558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchase (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE purchase_item ADD CONSTRAINT FK_6FA8ED7D4584665A FOREIGN KEY (product_id) REFERENCES product (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE variant ADD CONSTRAINT FK_F143BFAD4584665A FOREIGN KEY (product_id) REFERENCES product (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE category DROP FOREIGN KEY FK_64C19C1727ACA70
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category DROP FOREIGN KEY FK_64C19C112469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F094584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD12469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B2ADD6D8C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE purchase_item DROP FOREIGN KEY FK_6FA8ED7D558FBEB9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE purchase_item DROP FOREIGN KEY FK_6FA8ED7D4584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE variant DROP FOREIGN KEY FK_F143BFAD4584665A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `order`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE order_item
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE purchase
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE purchase_item
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE supplier
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE variant
        SQL);
    }
}
