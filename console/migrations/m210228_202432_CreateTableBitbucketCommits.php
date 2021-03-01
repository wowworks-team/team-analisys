<?php

use common\bitbucket\models\BitbucketCommits;
use yii\db\Migration;

/**
 * Class m210228_202432_CreateTableBitbucketCommits
 */
class m210228_202432_CreateTableBitbucketCommits extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(BitbucketCommits::tableName(), [
            'id' => $this->primaryKey(),
            'hash' => $this->string(),
            'message' => $this->text(),
            'author_account_id' => $this->string(),
            'repository_name' => $this->string(),
            'date' => $this->dateTime(),
            'loaded_date' => $this->dateTime(),
            'pull_request_id' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(BitbucketCommits::tableName());
    }
}
