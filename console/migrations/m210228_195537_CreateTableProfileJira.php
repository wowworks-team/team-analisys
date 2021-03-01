<?php

use common\jira\models\ProfileJira;
use yii\db\Migration;

/**
 * Class m210228_195537_CreateTableProfileJira
 */
class m210228_195537_CreateTableProfileJira extends Migration
{
    public function safeUp()
    {
        $this->createTable(ProfileJira::tableName(), [
            'id' => $this->primaryKey(),
            'account_id' => $this->string()->notNull(),
            'display_name' => $this->string(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime()
        ]);
    }

    public function safeDown()
    {
        $this->dropTable(ProfileJira::tableName());
    }
}
