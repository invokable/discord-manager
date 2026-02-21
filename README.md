# Laravel Discord Manager

[![Ask DeepWiki](https://deepwiki.com/badge.svg)](https://deepwiki.com/invokable/discord-manager)

> **Note** As of v5, only the Interactions command is provided, which is webhook based and therefore easy to use with Laravel. All features using WebSockets have been removed.

## Overview

Discord Manager is a Laravel package that provides seamless integration with Discord's Interactions API using webhook-based architecture. This package allows you to create and manage Discord slash commands, handle user interactions, and respond to Discord events directly from your Laravel application.

### Key Features

- **Webhook-based Architecture**: Secure and efficient handling of Discord interactions through webhooks
- **Slash Commands Support**: Create and register both guild-specific and global Discord slash commands
- **Laravel Integration**: Native Laravel service provider with configuration publishing and Artisan commands
- **Automatic Command Discovery**: Automatically loads and registers interaction commands from your application
- **Flexible Response System**: Support for immediate responses, deferred responses, and followup messages
- **Component Support**: Built-in support for Discord UI components like buttons, select menus, and modals
- **Event-driven**: Integrates with Laravel's event system for handling Discord interactions

### How It Works

The package operates by receiving webhook requests from Discord when users interact with your bot's commands. These requests are validated, processed through middleware, and dispatched to your custom command handlers. The workflow ensures secure communication with Discord while providing a familiar Laravel development experience.

### See also

- [Socialite for Discord](https://github.com/invokable/socialite-discord)
- [Laravel Notification for Discord(Webhook)](https://github.com/invokable/laravel-notification-discord-webhook)

## Requirements
- PHP >= 8.3
- Laravel >= 12.0

## Installation

### Step 1: Install the Package

```shell
composer require revolution/discord-manager
```

### Step 2: Discord Application Setup

Before configuring the package, you need to create a Discord application and bot:

1. Go to the [Discord Developer Portal](https://discord.com/developers/applications)
2. Click "New Application" and give it a name
3. Navigate to the "Bot" section and click "Add Bot"
4. Copy the bot token for `DISCORD_BOT_TOKEN`
5. Go to "General Information" and copy the Application ID for `DISCORD_BOT`
6. Copy the Public Key for `DISCORD_PUBLIC_KEY`
7. For guild-specific commands, copy your Discord server's Guild ID for `DISCORD_GUILD`

### Step 3: Environment Configuration

Add the following variables to your `.env` file:

```env
# Bot token from Discord Developer Portal > Bot section
DISCORD_BOT_TOKEN=your_bot_token_here

# Application ID from Discord Developer Portal > General Information
DISCORD_BOT=your_application_id_here

# Public Key from Discord Developer Portal > General Information  
DISCORD_PUBLIC_KEY=your_public_key_here

# Guild ID (Server ID) for guild-specific commands (optional)
DISCORD_GUILD=your_guild_id_here

# Optional: Discord API version (defaults to 10)
DISCORD_API_VERSION=10
```

### Step 4: Publish Configuration

```shell
php artisan vendor:publish --tag=discord-interactions-config
```

This creates `config/discord_interactions.php` where you can define your commands and customize settings.

### Uninstall
```shell
composer remove revolution/discord-manager
```

- Delete `config/discord_interactions.php`
- Delete `app/Discord/` and other files.
- Delete `DISCORD_*` in `.env`

## Configuration

### Command Configuration

Edit `config/discord_interactions.php` to define your Discord commands:

```php
return [
    // Guild-specific commands (only available in specified servers)
    'guild' => [
        [
            'name' => 'hello',
            'description' => 'Say hello to a user',
            'type' => CommandType::CHAT_INPUT,
            'guild_id' => env('DISCORD_GUILD'),
            'options' => [
                [
                    'name' => 'user',
                    'description' => 'User to greet',
                    'type' => CommandOptionType::USER,
                    'required' => true,
                ],
            ],
        ],
    ],

    // Global commands (available in all servers)
    'global' => [
        [
            'name' => 'ping',
            'description' => 'Check if the bot is responding',
            'type' => CommandType::CHAT_INPUT,
        ],
    ],

    // Other configuration options
    'commands' => app_path('Discord/Interactions'), // Path to command classes
    'token' => env('DISCORD_BOT_TOKEN'),
    'bot' => env('DISCORD_BOT'),
    'public_key' => env('DISCORD_PUBLIC_KEY'),
    'path' => 'discord/webhook', // Webhook endpoint path
    'route' => 'discord.webhook', // Route name
    'middleware' => 'throttle', // Additional middleware
];
```

### Discord Developer Portal Setup

Set the **Interactions Endpoint URL** in your Discord application:

1. Go to [Discord Developer Portal](https://discord.com/developers/applications)
2. Select your application
3. Navigate to "General Information"
4. Set the Interactions Endpoint URL to: `https://yourdomain.com/discord/webhook`

## Usage

### Quick Start

Here's a complete example to get you started:

#### 1. Create a Command

```shell
php artisan discord:make:interaction HelloCommand
```

This creates `app/Discord/Interactions/HelloCommand.php`:

```php
<?php

namespace App\Discord\Interactions;

use Illuminate\Http\Request;
use Revolution\DiscordManager\Concerns\WithInteraction;

class HelloCommand
{
    use WithInteraction;

    public string $command = 'hello';

    public function __invoke(Request $request): void
    {
        $user = $request->json('member.user.id', $request->json('user.id'));

        $data = [
            'content' => "<@$user> Hello from Laravel!",
            'allowed_mentions' => ['parse' => ['users']],
        ];

        $response = $this->followup(token: $request->json('token'), data: $data);
    }
}
```

#### 2. Add Command to Configuration

The command you created can be used as either a guild-specific or global command. You need to add it to your `config/discord_interactions.php` file to specify where it should be registered.

Edit `config/discord_interactions.php` and add your command to either the `guild` or `global` array:

```php
return [
    // For guild-specific commands (only available in specified servers)
    'guild' => [
        [
            'name' => 'hello',
            'description' => 'Say hello from Laravel',
            'type' => CommandType::CHAT_INPUT,
            'guild_id' => env('DISCORD_GUILD'),
        ],
        // Add more guild commands here...
    ],

    // For global commands (available in all servers where your bot is installed)
    'global' => [
        [
            'name' => 'hello',
            'description' => 'Say hello from Laravel',
            'type' => CommandType::CHAT_INPUT,
        ],
        // Add more global commands here...
    ],

    // ... rest of configuration
];
```

Choose `guild` for testing and development, or `global` for production deployment to all servers.

#### 3. Register Commands

```shell
php artisan discord:interactions:register
```

#### 4. Create Event Listener

The Discord Manager package uses Laravel's event system to handle incoming webhook requests. When Discord sends a webhook request to your application, the package dispatches an `InteractionsWebhook` event. You need to create a listener to handle this event and process the Discord interaction.

```shell
php artisan make:listener InteractionsListener
```

Update `app/Listeners/InteractionsListener.php`:

```php
<?php

namespace App\Listeners;

use Revolution\DiscordManager\Events\InteractionsWebhook;
use Revolution\DiscordManager\Facades\DiscordManager;

class InteractionsListener
{
    public function handle(InteractionsWebhook $event): void
    {
        DiscordManager::interaction($event->request);
    }
}
```

This listener receives the webhook request from Discord and passes it to the DiscordManager, which then routes it to the appropriate command class based on the interaction data.



### Advanced Usage

#### Commands with Options

```php
<?php

namespace App\Discord\Interactions;

use Illuminate\Http\Request;
use Revolution\DiscordManager\Concerns\WithInteraction;

class GreetCommand
{
    use WithInteraction;

    public string $command = 'greet';

    public function __invoke(Request $request): void
    {
        $targetUser = $request->json('data.options.0.value');
        $message = $request->json('data.options.1.value', 'Hello!');
        $user = $request->json('member.user.id', $request->json('user.id'));

        $data = [
            'content' => "<@$targetUser> $message (from <@$user>)",
            'allowed_mentions' => ['parse' => ['users']],
        ];

        $this->followup(token: $request->json('token'), data: $data);
    }
}
```

#### Interactive Components

```php
<?php

namespace App\Discord\Interactions;

use Illuminate\Http\Request;
use Revolution\DiscordManager\Concerns\WithInteraction;
use Revolution\DiscordManager\Support\ComponentType;
use Revolution\DiscordManager\Support\ButtonStyle;

class ButtonCommand
{
    use WithInteraction;

    public string $command = 'button-demo';

    public function __invoke(Request $request): void
    {
        $data = [
            'content' => 'Click a button below:',
            'components' => [
                [
                    'type' => ComponentType::ACTION_ROW->value,
                    'components' => [
                        [
                            'type' => ComponentType::BUTTON->value,
                            'style' => ButtonStyle::PRIMARY->value,
                            'label' => 'Primary',
                            'custom_id' => 'primary_button',
                        ],
                        [
                            'type' => ComponentType::BUTTON->value,
                            'style' => ButtonStyle::SUCCESS->value,
                            'label' => 'Success',
                            'custom_id' => 'success_button',
                        ],
                    ],
                ],
            ],
        ];

        $this->followup(token: $request->json('token'), data: $data);
    }
}
```

#### Handling Button Interactions

```php
<?php

namespace App\Discord\Interactions;

use Illuminate\Http\Request;
use Revolution\DiscordManager\Concerns\WithInteraction;

class PrimaryButtonCommand
{
    use WithInteraction;

    public string $command = 'primary_button';

    public function __invoke(Request $request): void
    {
        $user = $request->json('member.user.id', $request->json('user.id'));

        $data = [
            'content' => "<@$user> You clicked the primary button!",
            'allowed_mentions' => ['parse' => ['users']],
        ];

        $this->followup(token: $request->json('token'), data: $data);
    }
}
```

### Command Registration

After creating or modifying commands, always register them with Discord:

```shell
# Register all commands defined in config
php artisan discord:interactions:register
```

### Troubleshooting

#### Common Issues

1. **Webhook URL not accessible**: Ensure your application is publicly accessible and the webhook URL is correct
2. **Invalid signature**: Verify your `DISCORD_PUBLIC_KEY` is correct
3. **Commands not appearing**: Check that commands are properly registered and the bot has necessary permissions
4. **Permission errors**: Ensure your bot has the necessary permissions in the Discord server

#### Testing Webhook Locally

For local development, we recommend using [expose](https://expose.dev/) to expose your local server. Expose is a popular choice among Laravel/PHP developers for creating secure tunnels to localhost.

First, install expose globally:

```shell
composer global require exposedev/expose
```

Next, create an expose account and set your authentication token:

```shell
expose token YOUR_TOKEN
```

For more information on authentication, see the [expose documentation](https://expose.dev/docs/getting-started/authentication).

To use expose, start your Laravel local server first, then run the `expose share` command:

```shell
php artisan serve
```
```shell
expose share http://127.0.0.1:8000
```

If you are not using `php artisan serve`, please adjust it to suit your environment.

Use the generated expose URL as your webhook endpoint in Discord Developer Portal.

## How It Works

The Discord Manager package follows a secure webhook-based workflow to handle Discord interactions:

### Request Flow

1. **Discord Webhook**: Discord sends a POST request to `https://yourdomain.com/discord/webhook` when users interact with your bot
2. **Signature Validation**: [ValidateSignature](./src/Http/Middleware/ValidateSignature.php) middleware verifies the request authenticity using your public key
3. **Controller Processing**: [InteractionsWebhookController](./src/Http/Controllers/InteractionsWebhookController.php) receives and processes the interaction
4. **Deferred Response**: [DeferredResponse](./src/Http/Response/DeferredResponse.php) immediately acknowledges Discord to prevent timeout
5. **Event Dispatch**: [DispatchInteractionsEvent](./src/Http/Middleware/DispatchInteractionsEvent.php) terminable middleware dispatches the interaction event
6. **Event Handling**: [InteractionsWebhook](./src/Events/InteractionsWebhook.php) event is fired and handled by your listener
7. **Command Execution**: Your `InteractionsListener` calls `DiscordManager::interaction()` which invokes the appropriate command class
8. **Response Delivery**: Commands use the `followup()` method to send responses back to Discord

### Security

- All requests are cryptographically verified using Ed25519 signatures
- Invalid signatures are automatically rejected
- Webhook endpoints are protected against unauthorized access

### Performance

- Deferred responses prevent Discord timeouts (3-second limit)
- Terminable middleware allows background processing
- Automatic command discovery and caching

## Examples and Resources

### Sample Application

For a complete working example, see: https://github.com/kawax/discord-interactions

### Command Types

The package supports various Discord command and component types:

- **Slash Commands**: Traditional `/command` interactions
- **User Commands**: Right-click context menu on users  
- **Message Commands**: Right-click context menu on messages
- **Button Components**: Interactive buttons in messages
- **Select Menus**: Dropdown selection components
- **Modal Forms**: Pop-up forms for user input

### Response Types

- **Immediate Response**: Direct reply to the interaction
- **Deferred Response**: Acknowledge first, respond later (recommended)
- **Followup Messages**: Additional messages after the initial response

### Best Practices

1. **Use Deferred Responses**: Always use `followup()` for responses that take time to process
2. **Handle Errors Gracefully**: Implement proper error handling in your command classes
3. **Validate Input**: Always validate user input from command options
4. **Use Guild Commands for Development**: Guild commands are scoped to specific servers for testing
5. **Implement Logging**: Use Laravel's logging to track interactions and debug issues

## LICENSE
MIT 
