<?php

namespace common\jira\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string $account_id
 * @property string $display_name
 */
class ProfileJira extends ActiveRecord
{
    public static function tableName()
    {
        return '{{profile_jira}}';
    }

    public function rules()
    {
        return [
            ['account_id', 'required'],
            [['account_id', 'display_name'], 'string'],
            [['created', 'updated', 'created_at', 'updated_at'], 'safe']
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
