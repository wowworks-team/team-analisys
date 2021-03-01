<?php

use common\jira\models\JiraIssue;
use yii\db\Migration;

/**
 * Class m210228_200515_CreateTableJiraIssue
 */
class m210228_200515_CreateTableJiraIssue extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(JiraIssue::tableName(), [
            'id' => $this->primaryKey(),
            'assigned_user_account_id' => $this->string()->notNull(),
            'key' => $this->string()->notNull(),
            'project_key' => $this->string(),
            'summary' => $this->string(),
            'status' => $this->string(),
            'story_points' => $this->smallInteger(),
            'created_on' => $this->dateTime(),
            'updated_on' => $this->dateTime(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(JiraIssue::tableName());
    }
}
