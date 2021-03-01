<?php

namespace common\jira\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $id
 * @property string $assigned_user_account_id
 * @property string $key
 * @property string $project_key
 * @property string $summary
 * @property string $status
 * @property int $story_points
 * @property string $created_on
 * @property string $updated_on
 * @property string $created_at
 * @property string $updated_at
 */
class JiraIssue extends ActiveRecord
{
    const STATUS_IN_PROGRESS = 'indeterminate';
    const STATUS_DONE = 'done';

    public static function tableName()
    {
        return '{{jira_issue}}';
    }

    public function rules()
    {
        return [
            [['assigned_user_account_id', 'key'], 'required'],
            [['assigned_user_account_id', 'key', 'project_key', 'summary', 'status'], 'string'],
            ['story_points', 'integer'],
            [['created_on', 'updated_on', 'created_at', 'updated_at'], 'safe']
        ];
    }

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
}
