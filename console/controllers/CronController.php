<?php

namespace console\controllers;

use common\bitbucket\BitbucketSynchronizationService;
use common\jira\JiraSynchronizationService;
use DateTime;
use Exception;
use yii\console\Controller;

class CronController extends Controller
{
    const SYNC_DATE_FROM = '-1 days';

    /**
     * @var BitbucketSynchronizationService
     */
    private $bitbucketSynchronizationService;

    /**
     * @var JiraSynchronizationService
     */
    private $jiraSynchronizationService;

    /**
     * CronController constructor.
     * @param $id
     * @param $module
     * @param BitbucketSynchronizationService $bitbucketSynchronizationService
     * @param JiraSynchronizationService $jiraSynchronizationService
     * @param array $config
     */
    public function __construct(
        $id,
        $module,
        BitbucketSynchronizationService $bitbucketSynchronizationService,
        JiraSynchronizationService $jiraSynchronizationService,
        $config = []
    ) {
        $this->bitbucketSynchronizationService = $bitbucketSynchronizationService;
        $this->jiraSynchronizationService = $jiraSynchronizationService;

        parent::__construct($id, $module, $config);
    }

    public function actionSync(string $dateFrom = null): void
    {
        if (!is_null($dateFrom)) {
            $this->validateDate($dateFrom, 'Y-m-d');
        } else {
            $date = new DateTime();
            $date->modify(self::SYNC_DATE_FROM);
            $dateFrom = $date->format('Y-m-d');
        }

        $this->bitbucketSynchronizationService->syncFromDate($dateFrom);
        $this->jiraSynchronizationService->syncFromDate($dateFrom);

        $this->stdout("Success! Data there are update from {$dateFrom}\n");
    }

    private function validateDate(string $date, string $format): void
    {
        $dateTime = DateTime::createFromFormat($format, $date);
        if (!($dateTime && $dateTime->format($format) === $date)) {
            throw new Exception("The date must be in the format: '{$format}'");
        }
    }
}
