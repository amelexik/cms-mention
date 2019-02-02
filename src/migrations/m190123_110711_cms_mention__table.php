<?php

use yii\db\Migration;

/**
 * Class m190123_110711_cms_mention__table
 */
class m190123_110711_cms_mention__table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->createTable('{{%mention}}', [
            'eid1' => $this->integer(11)->notNull()->comment('Element 1 primary key'),
            'cid1' => $this->integer(11)->notNull()->comment('Content Type 1 primary key'),
            'eid2' => $this->integer(11)->notNull()->comment('Element 2 primary key'),
            'cid2' => $this->integer(11)->notNull()->comment('Content Type 2  primary key'),
        ]);

        /**
         * Делаем внешние ключи на CmsContent
         * Если CmsContent удаляется из системы - каскадно удаляем все связи {{%mention}}
         */
        $this->addForeignKey('FK_MENTION_CMS_CONTENT_1', '{{%mention}}', 'cid1', '{{%cms_content}}', 'id', 'CASCADE', 'CASCADE');

        $this->addForeignKey('FK_MENTION_CMS_CONTENT_2', '{{%mention}}', 'cid2', '{{%cms_content}}', 'id', 'CASCADE', 'CASCADE');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%mention}}');
    }
}
