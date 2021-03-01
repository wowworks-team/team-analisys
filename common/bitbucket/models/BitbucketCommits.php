<?php

namespace common\bitbucket\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "bitbucket_commits".
 *
 * @property int $id
 * @property int $pull_request_id
 * @property string $hash
 * @property string $message
 * @property string $author_account_id
 * @property string $repository_name
 * @property string $date
 * @property string $loaded_date
 */
class BitbucketCommits extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bitbucket_commits';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['pull_request_id', 'integer'],
            [['repository_name', 'message'], 'string'],
            [['hash', 'author_account_id'], 'string', 'max' => 255],
            [['date', 'loaded_date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['loaded_date'],
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'repository_name' => Yii::t('app', 'Repository Name'),
            'local_pull_request_id' => Yii::t('app', 'Local Pull Request Id'),
            'hash' => Yii::t('app', 'Hash'),
            'message' => Yii::t('app', 'Message'),
            'date' => Yii::t('app', 'Date'),
            'author' => Yii::t('app', 'Author'),
            'loaded_date' => Yii::t('app', 'Loaded date'),
        ];
    }
}
