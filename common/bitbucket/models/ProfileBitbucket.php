<?php

namespace common\bitbucket\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property string $id
 * @property string $account_id
 * @property string $nickname
 * @property string $display_name
 * @property string $created_at
 * @property string $updated_at
 */
class ProfileBitbucket extends ActiveRecord
{
    public static function tableName()
    {
        return '{{profile_bitbucket}}';
    }

    public function rules()
    {
        return [
            ['account_id', 'required'],
            [['account_id', 'nickname', 'display_name'], 'string'],
            [['created_at', 'updated_at'], 'safe']
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
