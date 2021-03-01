<?php

namespace common\tests\unit;

use Codeception\Test\Unit;
use common\bitbucket\BitbucketService;
use common\bitbucket\BitbucketSynchronizationService;
use common\bitbucket\models\BitbucketCommits;
use common\bitbucket\models\BitbucketPullRequests;
use common\bitbucket\models\ProfileBitbucket;
use PHPUnit\Framework\MockObject\MockObject;

class BitbucketSynchronizationServiceTest extends Unit
{
    private const TEST_WORKSPACE = 'test_workspace';
    private const TEST_REPOSITORY = ['test_repo'];

    private const TEST_DATE_FROM = '2020-01-01';

    private const PATH_TO_FIXTURE_PROFILE = 'common/tests/fixtures/bitbucketSync/profile.php';
    private const PATH_TO_FIXTURE_PULL_REQUEST = 'common/tests/fixtures/bitbucketSync/pullRequest.php';
    private const PATH_TO_FIXTURE_COMMIT = 'common/tests/fixtures/bitbucketSync/commit.php';

    /**
     * @var BitbucketService|MockObject
     */
    private $bitbucketApiService;

    /**
     * @var BitbucketSynchronizationService
     */
    private $bitbucketSyncService;

    public function _before()
    {
        $this->bitbucketApiService = $this->make(
            BitbucketService::class,
            [
                'getWorkspaceUsers' => $this->getFixtureBitbucketProfile(),
                'getPullRequests' => $this->getFixturePullRequest(),
                'getCommitsForPullRequest' => $this->getFixtureCommit(),

            ]
        );
        $this->bitbucketSyncService = new BitbucketSynchronizationService(
            self::TEST_WORKSPACE,
            self::TEST_REPOSITORY,
            $this->bitbucketApiService
        );

        $this->bitbucketSyncService->syncFromDate(self::TEST_DATE_FROM);
        parent::_before();
    }

    public function testSaveBitbucketProfile()
    {
        $expectedProfile = $this->getFixtureBitbucketProfile()['values'][0]['user'];
        $actualProfile = ProfileBitbucket::findOne(['account_id' => $expectedProfile['account_id']]);

        $this->assertInstanceOf(ProfileBitbucket::class, $actualProfile);
        $this->assertEquals($expectedProfile['account_id'], $actualProfile->account_id);
        $this->assertEquals($expectedProfile['display_name'], $actualProfile->display_name);
    }

    public function testSavePullRequest()
    {
        $expectedPullRequest = $this->getFixturePullRequest()['values'][0];
        $actualPullRequest = BitbucketPullRequests::findOne(
            ['author_account_id' => $expectedPullRequest['author']['account_id']]
        );

        $this->assertInstanceOf(BitbucketPullRequests::class, $actualPullRequest);
        $this->assertEquals($expectedPullRequest['author']['account_id'], $actualPullRequest->author_account_id);
        $this->assertEquals($expectedPullRequest['state'], $actualPullRequest->state);
        $this->assertEquals($expectedPullRequest['id'], $actualPullRequest->bitbucket_id);
        $this->assertEquals($expectedPullRequest['title'], $actualPullRequest->title);
        $this->assertEquals($expectedPullRequest['destination']['branch']['name'], $actualPullRequest->branch_destination);
    }

    public function testSaveCommit()
    {
        $expectedCommit = $this->getFixtureCommit()['values'][0];
        $actualCommit = BitbucketCommits::findOne(
            ['author_account_id' => $expectedCommit['author']['user']['account_id']]
        );

        $this->assertInstanceOf(BitbucketCommits::class, $actualCommit);
        $this->assertEquals($expectedCommit['author']['user']['account_id'], $actualCommit->author_account_id);
        $this->assertEquals($expectedCommit['hash'], $actualCommit->hash);
        $this->assertEquals($expectedCommit['repository']['name'], $actualCommit->repository_name);

        $actualPullRequest = BitbucketPullRequests::findOne($actualCommit->pull_request_id);

        $this->assertInstanceOf(BitbucketPullRequests::class, $actualPullRequest);
    }

    private function getFixtureBitbucketProfile(): array
    {
        return require(self::PATH_TO_FIXTURE_PROFILE);
    }

    private function getFixturePullRequest(): array
    {
        return require(self::PATH_TO_FIXTURE_PULL_REQUEST);
    }

    private function getFixtureCommit(): array
    {
        return require(self::PATH_TO_FIXTURE_COMMIT);
    }
}
