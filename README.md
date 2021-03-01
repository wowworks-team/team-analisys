[![Build Status](https://travis-ci.org/wowworks-team/team-analisys.svg?branch=main)](https://travis-ci.org/wowworks-team/team-analisys)
# Analysis


Bitbucket settings
-------------------
1. Log into BitBucket
2. Visit the Settings page for your organization
3. Click the "OAuth" tab under "Access Management"
4. Click the Add Consumer button. Attention! You will need to grant read access to the section: "Account".
5. Configure and save.

Jira settings
-------------------

### Add OAuth Consumer
1. Create [API Token] (https://confluence.atlassian.com/cloud/api-tokens-938839638.html)

Usage
-------------------
1. Run command `php init`.
2. Set the Key and Secret shown in the new entry in the list of OAuth consumers at `\common\config\params-local.php`.
```php
    'bitbucket' => [
        'key' => <your-OAuth-Key>
        'secret' => <your-OAuth-Secret>,
                'workspace' => <your-workspace>, 
                'repositories' => [
                    <you-repository-slag-1>,
                    <you-repository-slag-2>
                ],
    ]
```

For example in URL https://bitbucket.org/teamsinspace/documentation-tests: teamsinspace - `your-workspace`; documentation-tests - `you-repository-slag`.

3.Add settings for connecting to the database at `\common\config\params-local.php`
```php
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => '',
            'password' => '',
            'username' => '',
            'charset' => 'utf8',
        ],
    ]
```

4.Set your Jira params at  `\common\config\params-local.php`.
```php
    'jira' => [
        'host' => <your-jira-host>
        'user' => <your-jira-username>,
        'token' => <jira-password-OR-api-token>,
        'projectKeys' => [
            <project-key-1>,
            <project-key-2>
        ]
    ]
```
5.Apply migrations `php yii migrate`.

6.Run command `php yii cron/sync "dateFrom"`. Where "dateFrom" is the date from the beginning of which you need to download the data, the "dateFrom" must be in the format "Y-m-d".
When the command run without argument "dateFrom", data will be loaded for the last 24 hours.


