<?php

namespace common\bitbucket\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "bitbucket_pullrequests".
 *
 * @property int $id
 * @property string $repository_name
 * @property int $bitbucket_id
 * @property string $title
 * @property string $description
 * @property string $state
 * @property string $created_on
 * @property string $updated_on
 * @property string $author_account_id
 * @property string $branch_source
 * @property string $branch_destination
 * @property string $closed_by_user_account_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $author
 * @property string $closed_by
 */
class BitbucketPullRequests extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bitbucket_pull_requests';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['bitbucket_id'], 'required'],
            ['bitbucket_id', 'integer'],
            [['description'], 'string'],
            [['repository_name', 'title', 'author_account_id', 'branch_source', 'branch_destination', 'closed_by_user_account_id'], 'string', 'max' => 255],
            [['state'], 'string', 'max' => 50],
            [['created_on', 'updated_on', 'created_at', 'updated_at'], 'safe'],
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
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
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
            'bitbucket_id' => Yii::t('app', 'Bitbucket ID'),
            'title' => Yii::t('app', 'Title'),
            'description' => Yii::t('app', 'Description'),
            'state' => Yii::t('app', 'State'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
            'author' => Yii::t('app', 'Author'),
            'branch_source' => Yii::t('app', 'Branch Source'),
            'branch_destination' => Yii::t('app', 'Branch Destination'),
            'closed_by' => Yii::t('app', 'Closed By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
