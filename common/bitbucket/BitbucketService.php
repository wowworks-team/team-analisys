<?php

namespace common\bitbucket;

use Bitbucket\API\Http\Client;
use Bitbucket\API\Http\Listener\OAuth2Listener;
use Bitbucket\API\Repositories\PullRequests;
use yii\helpers\Json;

class BitbucketService
{
    const API_VERSION_2_0 = '2.0';

    /**
     * @var string
     */
    private $userKey;

    /**
     * @var string
     */
    private $userSecret;

    /**
     * @var string[]
     */
    private $oauthParams;

    /**
     * @var PullRequests
     */
    private $pullRequests;

    /**
     * @var Client
     */
    private $bitbucketApiClient;

    /**
     * BitbucketService constructor.
     * @param string $userKey
     * @param string $userSecret
     */
    public function __construct(
        string $userKey,
        string $userSecret
    ) {
        $this->userKey = $userKey;
        $this->userSecret = $userSecret;

        $this->oauthParams = [
            'client_id' => $userKey,
            'client_secret'  => $userSecret
        ];

        $this->pullRequests = new PullRequests();
        $this->pullRequests
            ->getClient()
            ->addListener(new OAuth2Listener($this->oauthParams));

        $this->bitbucketApiClient = new Client();
        $this->bitbucketApiClient->addListener(
            new OAuth2Listener($this->oauthParams)
        );
    }

    public function getWorkspaceUsers(string $workspace, $page = 1): array
    {
        $endpoint = sprintf('workspaces/%s/members?%s', $workspace, $page);

        $members = $this->bitbucketApiClient->setApiVersion(self::API_VERSION_2_0)->get($endpoint);
        return $this->decode($members->getContent());
    }

    /**
     * @param string $userName
     * @param string $repositoryName
     * @param array $states
     *
     * @return mixed
     */
    public function getPullRequests(string $userName, string $repositoryName, $states = [], $params = [])
    {
        if (!empty($states)) {
            $params['state'] = $states;
        }

        return $this->decode($this->pullRequests->all($userName, $repositoryName, $params)->getContent());
    }

    public function getCommitsForPullRequest(
        string $workspace,
        string $repositoryName,
        int $bitbucketPullRequestId,
        string $query = null
    ): array {
        $endpoint = sprintf(
            'repositories/%s/%s/pullrequests/%d/commits',
            $workspace,
            $repositoryName,
            $bitbucketPullRequestId
        );
        if ($query) {
            $endpoint .= "?{$query}";
        }
        $commits = $this->bitbucketApiClient->setApiVersion(self::API_VERSION_2_0)->get($endpoint);

        return $this->decode($commits->getContent());
    }

    private function decode(string $encodedData): array
    {
        return Json::decode($encodedData);
    }
}
