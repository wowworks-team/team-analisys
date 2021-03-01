<?php

namespace common\jira;

use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\Field\Field;
use JiraRestApi\Field\FieldService;
use JiraRestApi\Issue\Issue;
use JiraRestApi\Issue\IssueSearchResult;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\Issue\JqlQuery;
use JiraRestApi\User\User;
use JiraRestApi\User\UserService;

class JiraService
{
    const ORDER_ASC = 'asc';
    const ORDER_DESC = 'desc';

    /** @var  string */
    private $jiraHost;

    /** @var  string */
    private $jiraUsername;

    /** @var  string */
    private $jiraToken;

    /** @var  ArrayConfiguration */
    private $configuration;

    public function __construct(string $jiraHost, string $jiraUserName, string $jiraToken)
    {
        $this->jiraHost = $jiraHost;
        $this->jiraUsername = $jiraUserName;
        $this->jiraToken = $jiraToken;

        $this->configuration = new ArrayConfiguration([
            'jiraHost' => $this->jiraHost,
            'jiraUser' => $this->jiraUsername,
            'jiraPassword' => $this->jiraToken
        ]);
    }

    /**
     * @param string $projectKey
     * @return User[]
     */
    public function getUsersByProject(string $projectKey): array
    {
        $service = new UserService($this->configuration);

        $params = [
            'project' => $projectKey,
            'startAt' => 0,
            'maxResults' => 100,
        ];

        return $service->findAssignableUsers($params);
    }

    public function getIssueListByAssignedUser(string $jiraAccountId, $queryParams = []): IssueSearchResult
    {
        $jql = new JqlQuery();
        $jql->setAssignee($jiraAccountId);

        if (isset($queryParams['dateFrom'])) {
            $jql->addExpression(
                JqlQuery::FIELD_UPDATED,
                JqlQuery::OPERATOR_GREATER_THAN_EQUALS,
                $queryParams['dateFrom']
            );
        }

        if (isset($queryParams['dateTo'])) {
            $jql->addExpression(
                JqlQuery::FIELD_UPDATED,
                JqlQuery::OPERATOR_LESS_THAN_EQUALS,
                $queryParams['dateTo']
            );
        }

        $jql->addAnyExpression('ORDER BY ' . JqlQuery::FIELD_UPDATED . ' ' . self::ORDER_DESC);

        $startAt = $queryParams['startAt'] ?? 0;
        $maxResults = $queryParams['maxResults'] ?? 50;

        return $this->searchIssuesByQuery($jql->getQuery(), $startAt, $maxResults);
    }

    public function getIdStoryPointCustomField(): int
    {
        $fields = (new FieldService($this->configuration))->getAllFields(Field::CUSTOM);
        $field = current(array_filter($fields, function ($field) {
            return $field->name === 'Story Points';
        }));

        return (int) explode('_', $field->id)[1];
    }

    private function searchIssuesByQuery(string $query, int $startAt = 0, int $maxResults = 50): IssueSearchResult
    {
        $issueService = new IssueService($this->configuration);
        return $issueService->search($query, $startAt, $maxResults);
    }
}
