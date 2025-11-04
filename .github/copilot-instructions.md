# Onboarding Guide: invokable/discord-manager

## Overview

The `discord-manager` is a Laravel package that simplifies Discord bot development by providing a robust framework for handling Discord Interactions via webhooks. This package serves Laravel developers who want to build Discord bots without dealing with the complexities of Discord's API directly.

**Target Users:** Laravel developers building Discord applications, bots, or integrations

**Key Capabilities:**
- **Webhook Processing**: Automatically receives and validates Discord interaction requests (slash commands, buttons, modals)
- **Command Management**: Provides tools to generate, register, and execute Discord commands with minimal boilerplate
- **Security**: Built-in cryptographic signature validation ensures only authentic Discord requests are processed
- **Async Processing**: Uses Laravel's event system to handle complex command logic without blocking Discord's response requirements
- **Developer Experience**: Includes Artisan commands for scaffolding and deployment, plus a facade for easy API access

**Workflow**: Discord sends interaction webhooks → Package validates and processes → Dispatches to user-defined command handlers → Sends responses back to Discord

## Project Organization

### Core Systems

1. **Webhook Processing Pipeline** (`src/Http/`)
    - Entry point for all Discord interactions
    - Handles signature validation, immediate responses, and event dispatching

2. **Command Management System** (`src/DiscordCommandRegistry.php`, `src/Console/`)
    - Discovers, registers, and executes Discord commands
    - Provides scaffolding tools for developers

3. **HTTP Client Layer** (`src/Facades/`, `src/DiscordCommandRegistry.php`)
    - Manages authenticated requests to Discord API
    - Abstracts Discord API versioning and authentication

4. **Event System** (`src/Events/`, `src/Contracts/`)
    - Decouples webhook responses from command processing
    - Enables asynchronous command execution

### Directory Structure

```
├── src/
│   ├── Concerns/           # Reusable traits (WithInteraction)
│   ├── Console/           # Artisan commands (make:interaction, register)
│   │   └── stubs/         # Code generation templates
│   ├── Contracts/         # Interfaces (Factory, InteractionsEvent, InteractionsResponse)  
│   ├── Events/           # Event classes (InteractionsWebhook)
│   ├── Exceptions/       # Custom exceptions (CommandNotFountException)
│   ├── Facades/          # Laravel facades (DiscordManager)
│   ├── Http/             # Controllers, middleware, responses
│   ├── Providers/        # Service provider (DiscordInteractionsServiceProvider)
│   ├── Support/          # Enums (CommandType, CommandOptionType, ComponentType, ButtonStyle, TextInputStyle)
│   └── DiscordCommandRegistry.php # Core manager class
├── config/               # Configuration templates
├── tests/               # Test suite
└── .github/workflows/   # CI/CD (testing, linting)
```

### Key Entry Points

- **`DiscordInteractionsServiceProvider`**: Bootstraps the entire package
- **`/discord/webhook` route**: Receives all Discord interactions
- **`DiscordManager` facade**: Primary API for developers
- **`discord:make:interaction`**: Generates new command classes
- **`discord:interactions:register`**: Deploys commands to Discord

### Main Classes & Functions

- **`DiscordManager::interaction(Request)`**: Processes incoming Discord interactions
- **`DiscordManager::http()`**: Returns authenticated HTTP client for Discord API
- **`InteractionsWebhookController::__invoke()`**: Handles webhook HTTP requests
- **`ValidateSignature::handle()`**: Validates Discord request signatures
- **`WithInteraction::followup()`**: Sends follow-up messages to Discord

## Glossary of Codebase-Specific Terms

**CommandNotFountException** - Custom exception thrown when a Discord interaction references an unregistered command (`src/Exceptions/CommandNotFountException.php`)

**DispatchInteractionsEvent** - Terminable middleware that fires InteractionsWebhook events after responses are sent (`src/Http/Middleware/DispatchInteractionsEvent.php`)

**Factory** - Contract interface defining the core DiscordManager API (`src/Contracts/Factory.php`, implemented by `DiscordManager`)

**InteractionsEvent** - Contract for event objects that represent Discord interactions (`src/Contracts/InteractionsEvent.php`)

**InteractionsResponse** - Contract for objects that generate responses to Discord interactions (`src/Contracts/InteractionsResponse.php`)

**InteractionsWebhook** - Event class representing a processed Discord interaction webhook (`src/Events/InteractionsWebhook.php`)

**InteractionsWebhookController** - Single-action controller that handles Discord webhook POST requests (`src/Http/Controllers/InteractionsWebhookController.php`)

**MakeInteraction** - Artisan command `discord:make:interaction` that generates new Discord command classes (`src/Console/MakeInteraction.php`)

**RegisterCommand** - Artisan command `discord:interactions:register` that registers commands with Discord API (`src/Console/RegisterCommand.php`)

**ValidateSignature** - Middleware that validates Ed25519 signatures on incoming Discord webhooks (`src/Http/Middleware/ValidateSignature.php`)

**WithInteraction** - Trait providing `followup()` method for sending Discord follow-up messages (`src/Concerns/WithInteraction.php`)

**discord_interactions.php** - Main configuration file defining commands, credentials, and webhook settings (`config/discord_interactions.php`)

**followup()** - Method in WithInteraction trait: `followup(string $token, array $data): Response` for sending Discord responses

**interaction()** - Core method in DiscordManager: `interaction(Request $request): mixed` that processes Discord interactions

**interactions** - Internal array in DiscordManager storing registered command handlers mapped by command name

**guild commands** - Discord commands registered for specific servers, configured in `discord_interactions.guild` array

**global commands** - Discord commands available across all servers, configured in `discord_interactions.global` array

**ChannelMessageResponse** - Response class for sending message responses to Discord interactions (`src/Http/Response/ChannelMessageResponse.php`)

**DeferredResponse** - Response class that immediately acknowledges Discord while processing continues asynchronously (`src/Http/Response/DeferredResponse.php`)

**PongResponse** - Special response class for Discord PING health checks (`src/Http/Response/PongResponse.php`)

**ComponentType** - Enum defining Discord UI component types (BUTTON, TEXT_INPUT, etc.) (`src/Support/ComponentType.php`)

**CommandOptionType** - Enum defining Discord command option types (STRING, INTEGER, USER, etc.) (`src/Support/CommandOptionType.php`)

**CommandType** - Enum defining Discord command types (CHAT_INPUT, USER, MESSAGE) (`src/Support/CommandType.php`)

**ButtonStyle** - Enum defining Discord button styles (PRIMARY, DANGER, etc.) (`src/Support/ButtonStyle.php`)

**TextInputStyle** - Enum defining Discord text input styles (SHORT, PARAGRAPH) (`src/Support/TextInputStyle.php`)

**DiscordInteractionsServiceProvider** - Main service provider that registers all package services and routes (`src/Providers/DiscordInteractionsServiceProvider.php`)

**interaction.stub** - Template file for generating new Discord command classes (`src/Console/stubs/interaction.stub`)

**app/Discord/Interactions** - Default directory where generated Discord command classes are stored

**discord.webhook** - Named route for the Discord webhook endpoint, configurable via `discord_interactions.route`

**CHAT_INPUT** - Discord command type for slash commands, referenced in command definitions
