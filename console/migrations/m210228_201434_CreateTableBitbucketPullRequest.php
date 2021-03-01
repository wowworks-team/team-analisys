<?php

use common\bitbucket\models\BitbucketPullRequests;
use yii\db\Migration;

/**
 * Class m210228_201434_CreateTableBitbucketPullRequest
 */
class m210228_201434_CreateTableBitbucketPullRequest extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(BitbucketPullRequests::tableName(), [
            'id' => $this->primaryKey(),
            'bitbucket_id' => $this->integer()->notNull(),
            'title' => $this->string(),
            'description' => $this->text(),
            'state' => $this->string(),
            'author_account_id' => $this->string(),
            'branch_source' => $this->string(),
            'branch_destination' => $this->string(),
            'closed_by_user_account_id' => $this->string(),
            'repository_name' => $this->string(),
            'created_on' => $this->dateTime(),
            'updated_on' => $this->dateTime(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(BitbucketPullRequests::tableName());
    }
}
