<?php

declare(strict_types=1);

namespace Revolution\DiscordManager\Facades;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Facade;
use Revolution\DiscordManager\Contracts\Factory;
use Revolution\DiscordManager\DiscordCommandRegistry;

/**
 * @method static mixed interaction(\Illuminate\Http\Request $request)
 * @method static PendingRequest http(int $version = 10)
 *
 * @see DiscordCommandRegistry
 */
class DiscordManager extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return Factory::class;
    }
}
