<?php

namespace common\bitbucket;

use common\bitbucket\models\BitbucketCommits;
use common\bitbucket\models\BitbucketPullRequests;
use common\bitbucket\models\ProfileBitbucket;
use DateTime;
use Exception;

class BitbucketSynchronizationService
{
    const DEFAULT_DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var string
     */
    private $workspace;

    /**
     * @var string[]
     */
    private $repositoryNames;

    /**
     * @var BitbucketService
     */
    private $bitbucketService;

    /**
     * BitbucketSynchronizationService constructor.
     * @param string $workspace
     * @param string[] $repositoryNames
     * @param BitbucketService $bitbucketService
     */
    public function __construct(string $workspace, array $repositoryNames, BitbucketService $bitbucketService)
    {
        $this->workspace = $workspace;
        $this->repositoryNames = $repositoryNames;
        $this->bitbucketService = $bitbucketService;
    }

    public function syncFromDate(string $dateFrom): void
    {
        $this->syncProfilesData($this->workspace);
        foreach (ProfileBitbucket::find()->each() as $profile) {
            $this->syncPullRequestsDataFromDateByUser($profile->account_id, $dateFrom);
        }
    }

    private function syncProfilesData(string $workspace): void
    {
        $size = $this->bitbucketService->getWorkspaceUsers($workspace)['size'];
        for ($page = 1; $page <= $size; $page++) {
            $workspaceUsersData = $this->bitbucketService->getWorkspaceUsers($workspace, $page);
            foreach ($workspaceUsersData['values'] as $bitbucketUser) {
                $userExists = ProfileBitbucket::find()
                    ->where(['account_id' => $bitbucketUser['user']['account_id']])
                    ->exists();

                if (!$userExists) {
                    $profileBitbucket = new ProfileBitbucket();
                    $profileBitbucket->account_id = $bitbucketUser['user']['account_id'];
                    $profileBitbucket->display_name = $bitbucketUser['user']['display_name'];
                    $profileBitbucket->nickname = $bitbucketUser['user']['nickname'];

                    if (!$profileBitbucket->save()) {
                        throw new Exception("Dont save user: {$profileBitbucket->display_name}");
                    }
                }
            }
        }
    }

    private function syncPullRequestsDataFromDateByUser(string $bitbucketAccountId, string $dateFrom):void
    {
        foreach ($this->repositoryNames as $repositoryName) {
            $size = $this->bitbucketService->getPullRequests($this->workspace, $repositoryName)['size'];
            for ($page = 1; $page <= $size; $page++) {
                $query = sprintf('author.account_id="%1$s" AND (created_on>="%2$s" OR updated_on>="%2$s")', $bitbucketAccountId, $dateFrom);
                $params = ['q' => $query, 'page' => $page];

                $requestData = $this->bitbucketService->getPullRequests(
                    $this->workspace,
                    $repositoryName,
                    $states = [],
                    $params
                );

                if (empty($requestData['values'])) {
                    break;
                }

                foreach ($requestData['values'] as $pullRequest) {
                    $pullRequestModel = $this->updateOrCreatePullRequest($pullRequest);
                    $this->syncCommitsDataFromDateByPullRequest($repositoryName, $pullRequestModel, $dateFrom);
                }
            }
        }
    }

    private function syncCommitsDataFromDateByPullRequest(
        string $repositoryName,
        BitbucketPullRequests $pullRequest,
        string $dateFrom
    ): void {
        $hasNextPage = true;
        $query = null;
        while ($hasNextPage) {
            $commits = $this->bitbucketService->getCommitsForPullRequest(
                $this->workspace,
                $repositoryName,
                $pullRequest->bitbucket_id,
                $query
            );

            if (empty($commits['values'])) {
                    break;
            }

            foreach ($commits['values'] as $commitData) {
                if (strtotime($commitData['date']) < strtotime($dateFrom)) {
                    $hasNextPage = false;
                    break;
                }
                $this->createCommit($pullRequest->id, $commitData);
            }

            isset($commits['next'])
                ? $query = (parse_url($commits['next'])['query'] ?? null)
                : $hasNextPage = false;
        }
    }

    private function updateOrCreatePullRequest(array $pullRequestData): BitbucketPullRequests
    {
        $pullRequestModel = BitbucketPullRequests::find()
            ->where(
                [
                    'bitbucket_id' => $pullRequestData['id'],
                    'repository_name' => $pullRequestData['source']['repository']['name']
                ]
            )
            ->one();

        if (!($pullRequestModel instanceof BitbucketPullRequests)) {
            $pullRequestModel = new BitbucketPullRequests();
            $pullRequestModel->bitbucket_id = $pullRequestData['id'];
            $pullRequestModel->repository_name = $pullRequestData['source']['repository']['name'];
            $pullRequestModel->closed_by_user_account_id = $pullRequestData['closed_by']['account_id'] ?? null;
            $pullRequestModel->created_on = $this->convertDateFormat($pullRequestData['created_on'], self::DEFAULT_DATE_FORMAT);
        }

        $pullRequestModel->title = $pullRequestData['title'] ?? null;
        $pullRequestModel->description = $pullRequestData['description'] ?? null;
        $pullRequestModel->state = $pullRequestData['state'] ?? null;
        $pullRequestModel->author_account_id = $pullRequestData['author']['account_id'];
        $pullRequestModel->branch_source = $pullRequestData['source']['branch']['name'] ?? null;
        $pullRequestModel->branch_destination = $pullRequestData['destination']['branch']['name'] ?? null;
        $pullRequestModel->updated_on = $this->convertDateFormat($pullRequestData['updated_on'], self::DEFAULT_DATE_FORMAT);

        if (!$pullRequestModel->save()) {
            throw new Exception("Dont save pull request: {$pullRequestModel->title}");
        }

        return $pullRequestModel;
    }

    private function createCommit(int $pullRequestId, array $commitData): void
    {
        $commitModel = BitbucketCommits::find()
            ->where(
                [
                    'hash' => $commitData['hash'],
                    'repository_name' => $commitData['repository']['name']
                ]
            )
            ->one();

        if (!$commitModel instanceof BitbucketCommits) {
            $commitModel = new BitbucketCommits();
            $commitModel->repository_name = $commitData['repository']['name'];
            $commitModel->hash = $commitData['hash'];
            $commitModel->message = $commitData['message'] ?? null;
            $commitModel->date = $this->convertDateFormat($commitData['date'], self::DEFAULT_DATE_FORMAT);
            $commitModel->author_account_id = isset($commitData['author']['user']) ? $commitData['author']['user']['account_id'] : $commitData['author']['raw'];
        }

        $commitModel->pull_request_id = $pullRequestId;//ToDo only for adding pullRequestId to old records
        if (!$commitModel->save()) {
            throw new Exception("Dont save commit: {$commitModel->message}");
        }
    }

    private function convertDateFormat(string $date, string $format): string
    {
        return (new DateTime($date))->format($format);
    }
}
