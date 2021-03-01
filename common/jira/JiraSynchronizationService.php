<?php

namespace common\jira;

use common\jira\models\JiraIssue;
use common\jira\models\ProfileJira;
use Exception;
use JiraRestApi\Issue\Issue;
use JiraRestApi\User\User as JiraUser;

/**
 * Class JiraSynchronizationService
 * @package common\services\Jira
 */
class JiraSynchronizationService
{
    /**
     * @var string[]
     */
    private $projectKeys;

    /**
     * @var JiraService
     */
    private $jiraService;

    /**
     * JiraSynchronizationService constructor.
     * @param string[] $projectKeys
     * @param JiraService $jiraService
     */
    public function __construct(array $projectKeys, JiraService $jiraService)
    {
        $this->projectKeys = $projectKeys;
        $this->jiraService = $jiraService;
    }

    public function syncFromDate(string $dateFrom): void
    {
        foreach ($this->projectKeys as $key) {
            $users = $this->jiraService->getUsersByProject($key);
            foreach ($users as $user) {
                $this->syncProfilesData($user);
            }
        }

        foreach (ProfileJira::find()->each() as $profile) {
            $this->syncIssuesDataFromDateByUser($profile->account_id, $dateFrom);
        }
    }

    private function syncProfilesData(JiraUser $user): void
    {
        $userExists = ProfileJira::find()
            ->where(['account_id' => $user->accountId])
            ->exists();

        if (!$userExists && $user->active) {
            $profileJira = new ProfileJira();
            $profileJira->account_id = $user->accountId;
            $profileJira->display_name = $user->displayName;
            if (!$profileJira->save()) {
                throw new Exception("Dont save user: {$profileJira->display_name}");
            }
        }
    }

    private function syncIssuesDataFromDateByUser(string $jiraAccountId, string $dateFrom): void
    {
        $idStoryPointsCustomField = $this->jiraService->getIdStoryPointCustomField();
        $issueSearchResult = $this->jiraService->getIssueListByAssignedUser($jiraAccountId);
        $totalCount = $issueSearchResult->getTotal();
        $maxResult = $issueSearchResult->getMaxResults();

        $page = ceil($totalCount / $maxResult);

        for ($startAt = 0; $startAt < $page; $startAt++) {
            $queryParams = [
                'dateFrom' => $dateFrom,
                'startAt' => $startAt,
                'maxResults' => $startAt ? $startAt * $maxResult : $maxResult
            ];

            $issueSearchResult = $this->jiraService->getIssueListByAssignedUser(
                $jiraAccountId,
                $queryParams
            );

            if (empty($issueSearchResult->getIssues())) {
                break;
            }

            foreach ($issueSearchResult->getIssues() as $issue) {
                $this->updateOrCreateIssue($jiraAccountId, $issue, $idStoryPointsCustomField);
            }
        }
    }

    /**
     * @param string $jiraAccountId
     * @param Issue $issue
     * @param int $idStoryPointsCustomField
     */
    private function updateOrCreateIssue(string $jiraAccountId, Issue $issue, int $idStoryPointsCustomField): void
    {
        $jiraIssueModel = JiraIssue::find()
            ->where(['key' => $issue->key])
            ->one();

        if (!($jiraIssueModel instanceof JiraIssue)) {
            $jiraIssueModel = new JiraIssue();
        }

        $jiraIssueModel->assigned_user_account_id = $jiraAccountId;
        $jiraIssueModel->key = $issue->key;
        $jiraIssueModel->project_key = $issue->fields->project->key;
        $jiraIssueModel->summary = $issue->fields->summary;
        $jiraIssueModel->status = $issue->fields->status->statuscategory->key ?? null;
        $jiraIssueModel->story_points = $issue->fields->customFields["customfield_{$idStoryPointsCustomField}"] ?? null;
        $jiraIssueModel->created_on = $issue->fields->created->format("Y-m-d H:i:s");
        $jiraIssueModel->updated_on = $issue->fields->updated->format("Y-m-d H:i:s");

        if (!$jiraIssueModel->save()) {
            throw new Exception("Dont save issue: {$jiraIssueModel->key} for assigned user: {$jiraAccountId}");
        }
    }
}
