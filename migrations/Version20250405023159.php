<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250405023159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE conversations (id UUID NOT NULL, created_at TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX conversations_created_at_idx ON conversations (created_at)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN conversations.id IS '(DC2Type:conversation_id)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messages (id UUID NOT NULL, conversation_id UUID NOT NULL, sent_by UUID NOT NULL, content TEXT NOT NULL, sent_at TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_DB021E969AC0396 ON messages (conversation_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_DB021E96C378DCF6 ON messages (sent_by)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX messages_sent_at_idx ON messages (sent_at)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messages.id IS '(DC2Type:message_id)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messages.conversation_id IS '(DC2Type:conversation_id)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messages.sent_by IS '(DC2Type:participant_id)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messages.content IS '(DC2Type:message_content)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE participants (id UUID NOT NULL, conversation_id UUID NOT NULL, name VARCHAR(255) NOT NULL, user_id UUID NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_716970929AC0396 ON participants (conversation_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX participants_user_id_idx ON participants (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN participants.id IS '(DC2Type:participant_id)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN participants.conversation_id IS '(DC2Type:conversation_id)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN participants.name IS '(DC2Type:participant_name)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN participants.user_id IS '(DC2Type:user_id)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users (id UUID NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, created_at TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, removed_at TIMESTAMP(6) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX users_email_idx ON users (email)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN users.id IS '(DC2Type:user_id)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN users.first_name IS '(DC2Type:firstname)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN users.email IS '(DC2Type:email)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users_credentials (id UUID NOT NULL, username VARCHAR(255) NOT NULL, hashed_password VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_DB014FCF85E0677 ON users_credentials (username)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX users_credentials_username_idx ON users_credentials (username)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN users_credentials.id IS '(DC2Type:user_id)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN users_credentials.username IS '(DC2Type:username)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN users_credentials.hashed_password IS '(DC2Type:hashed_password)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages ADD CONSTRAINT FK_DB021E969AC0396 FOREIGN KEY (conversation_id) REFERENCES conversations (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages ADD CONSTRAINT FK_DB021E96C378DCF6 FOREIGN KEY (sent_by) REFERENCES participants (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participants ADD CONSTRAINT FK_716970929AC0396 FOREIGN KEY (conversation_id) REFERENCES conversations (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages DROP CONSTRAINT FK_DB021E969AC0396
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages DROP CONSTRAINT FK_DB021E96C378DCF6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participants DROP CONSTRAINT FK_716970929AC0396
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE conversations
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messages
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE participants
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users_credentials
        SQL);
    }
}
