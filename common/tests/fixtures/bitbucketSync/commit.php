<?php
return array (
    'pagelen' => 10,
    'values' =>
        [
            [
                'hash' => 'random_hash',
                'repository' => [
                    'type' => 'repository',
                    'name' => 'repository name',
                    'full_name' => 'workspace/repository-slug',
                    'uuid' => '{random_uuid}',
                ],
                'author' => [
                    'raw' => 'Test Test <test@test.ru>',
                    'type' => 'author',
                    'user' => [
                        'display_name' => 'Test Test',
                        'uuid' => '{random_uuid}',
                        'nickname' => 'test',
                        'type' => 'user',
                        'account_id' => 'random_account_id',
                    ],
                ],
                'summary' => [
                    'raw' => 'random summary',
                    'markup' => 'markdown',
                    'html' => 'html',
                    'type' => 'rendered',
                ],
                'parents' => [
                    [
                        'hash' => 'random_hash',
                        'type' => 'commit',
                    ],
                ],
                'date' => '2021-02-26T08:04:11+00:00',
                'message' => 'random message',
                'type' => 'commit',
            ],
        ],
    'page' => 1,
);
