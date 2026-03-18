<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Application;
use Revolution\DiscordManager\Providers\DiscordInteractionsServiceProvider;
use Revolution\DiscordManager\Support\CommandOptionType;
use Revolution\DiscordManager\Support\CommandType;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            DiscordInteractionsServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [];
    }

    /**
     * Define environment setup.
     *
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('discord_interactions', [
            'guild' => [
                [
                    'name' => 'test',
                    'description' => 'test command',
                    'type' => CommandType::CHAT_INPUT,
                    'guild_id' => 1,
                    'options' => [
                        [
                            'name' => 'message',
                            'description' => 'optional message',
                            'type' => CommandOptionType::STRING,
                        ],
                    ],
                ],
            ],

            'global' => [
                [
                    'name' => 'hello',
                    'description' => 'hello command',
                    'type' => CommandType::CHAT_INPUT,
                ],
            ],

            'commands' => app_path('/Discord/Interactions'),

            'version' => 10,

            // Bot token
            'token' => 'test',
            // APPLICATION ID
            'bot' => '1',
            // PUBLIC KEY
            'public_key' => 'test',

            'path' => 'discord/webhook',
            'route' => 'discord.webhook',
            'middleware' => 'throttle',
        ]);
    }
}
