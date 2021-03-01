<?php

use common\bitbucket\models\ProfileBitbucket;
use yii\db\Migration;

/**
 * Class m210228_200915_CreateTableProfileBitbucket
 */
class m210228_200915_CreateTableProfileBitbucket extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(ProfileBitbucket::tableName(), [
            'id' => $this->primaryKey(),
            'account_id' => $this->string()->notNull(),
            'nickname' => $this->string(),
            'display_name' => $this->string(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(ProfileBitbucket::tableName());
    }
}
