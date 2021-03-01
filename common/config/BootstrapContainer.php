<?php

namespace common\config;

use common\bitbucket\BitbucketSynchronizationService;
use common\jira\JiraSynchronizationService;
use Yii;
use yii\base\BootstrapInterface;

class BootstrapContainer implements BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        $container = Yii::$container;

        $container->setSingleton(
            \common\bitbucket\BitbucketService::class,
            [
                'class' => \common\bitbucket\BitbucketService::class,
            ],
            [
                Yii::$app->params['bitbucket']['key'],
                Yii::$app->params['bitbucket']['secret'],
            ]
        );

        $container->setSingleton(
            \common\jira\JiraService::class,
            [
                'class' => \common\jira\JiraService::class,
            ],
            [
                Yii::$app->params['jira']['host'],
                Yii::$app->params['jira']['user'],
                Yii::$app->params['jira']['token'],
            ]
        );

        $container->setSingleton(
            BitbucketSynchronizationService::class,
            [
                'class' => BitbucketSynchronizationService::class
            ],
            [
                Yii::$app->params['bitbucket']['workspace'],
                Yii::$app->params['bitbucket']['repositories'],
            ]
        );

        $container->setSingleton(
            JiraSynchronizationService::class,
            [
                'class' => JiraSynchronizationService::class
            ],
            [
                Yii::$app->params['jira']['projectKeys'],
            ]
        );
    }
}
