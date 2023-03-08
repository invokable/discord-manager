<?php

namespace Revolution\DiscordManager\Facades;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Facade;
use Revolution\DiscordManager\Contracts\Factory;

/**
 * @method static mixed interaction(\Illuminate\Http\Request $request)
 * @method static PendingRequest http(int $version = 10)
 *
 * @see \Revolution\DiscordManager\DiscordManager
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
