<?php

namespace common\tests\unit;

use Codeception\Test\Unit;
use common\jira\JiraService;
use common\jira\JiraSynchronizationService;
use common\jira\models\JiraIssue;
use common\jira\models\ProfileJira;
use DateTime;
use JiraRestApi\Issue\Issue;
use JiraRestApi\Issue\IssueField;
use JiraRestApi\Issue\IssueSearchResult;
use JiraRestApi\Issue\IssueStatus;
use JiraRestApi\Issue\Statuscategory;
use JiraRestApi\Project\Project;
use JiraRestApi\User\User as JiraUser;
use PHPUnit\Framework\MockObject\MockObject;

class JiraSynchronizationServiceTest extends Unit
{
    private const TEST_DATE_FROM = '2020-01-01';

    private const TEST_PROJECT_KEY = 'test_project_key';
    private const TEST_ISSUE_KEY = 'test_issue_key';
    private const TEST_JIRA_ACCOUNT_ID = 'random_account_id';
    private const TEST_ID_STORY_POINT_CUSTOM_FIELD = 111111;
    private const TEST_STORY_POINTS = 22222;

    /**
     * @var JiraService|MockObject
     */
    private $jiraApiService;

    /**
     * @var JiraSynchronizationService
     */
    private $jiraSyncService;

    public function _before()
    {
        $this->jiraApiService = $this->make(
            JiraService::class,
            [
                'getUsersByProject' => [$this->getFixtureJiraProfile()],
                'getIssueListByAssignedUser' => $this->getFixtureIssue(),
                'getIdStoryPointCustomField' => self::TEST_ID_STORY_POINT_CUSTOM_FIELD,
            ]
        );

        $this->jiraSyncService = new JiraSynchronizationService(
            [self::TEST_PROJECT_KEY],
            $this->jiraApiService
        );

        $this->jiraSyncService->syncFromDate(self::TEST_DATE_FROM);
    }

    public function testSaveProfileJira(): void
    {
        $expectedProfile = $this->getFixtureJiraProfile();
        $actualProfile = ProfileJira::findOne(['account_id' => $expectedProfile->accountId]);

        $this->assertInstanceOf(ProfileJira::class, $actualProfile);
        $this->assertEquals($expectedProfile->accountId, $actualProfile->account_id);
        $this->assertEquals($expectedProfile->displayName, $actualProfile->display_name);
    }

    public function testSaveIssue(): void
    {
        $expectedIssue = $this->getFixtureIssue()->getIssues()[0];
        $actualIssue = JiraIssue::findOne(['key' => $expectedIssue->key]);

        $this->assertInstanceOf(JiraIssue::class, $actualIssue);
        $this->assertEquals($expectedIssue->fields->status->statuscategory->key, $actualIssue->status);
        $this->assertEquals($expectedIssue->fields->summary, $actualIssue->summary);

        $idStoryPointsCustomField = self::TEST_ID_STORY_POINT_CUSTOM_FIELD;
        $this->assertEquals($expectedIssue->fields->customFields["customfield_{$idStoryPointsCustomField}"], $actualIssue->story_points);
        $this->assertEquals(self::TEST_PROJECT_KEY, $actualIssue->project_key);
        $this->assertEquals(self::TEST_JIRA_ACCOUNT_ID, $actualIssue->assigned_user_account_id);
    }

    private function getFixtureJiraProfile(): JiraUser
    {
        $data = [
            'accountId' => self::TEST_JIRA_ACCOUNT_ID,
            'displayName' => 'display_name',
            'active' => true
        ];

        return new JiraUser($data);
    }

    private function getFixtureIssue(): IssueSearchResult
    {
        $statusCategory = new Statuscategory();
        $statusCategory->key = 'key';

        $status = new IssueStatus();
        $status->statuscategory = $statusCategory;

        $fields = new IssueField();
        $fields->project = (new Project())->setKey(self::TEST_PROJECT_KEY);
        $fields->summary = 'summary';
        $fields->status = $status;

        $idStoryPointsCustomField = self::TEST_ID_STORY_POINT_CUSTOM_FIELD;
        $fields->customFields = ["customfield_{$idStoryPointsCustomField}" => self::TEST_STORY_POINTS];
        $fields->created = new DateTime();
        $fields->updated = new DateTime();

        $issue = new Issue();
        $issue->key = self::TEST_ISSUE_KEY;
        $issue->fields = $fields;

        $issues = new IssueSearchResult();
        $issues->setIssues([$issue]);
        $issues->setStartAt(1);
        $issues->setMaxResults(1);
        $issues->setTotal(1);

        return $issues;
    }
}
