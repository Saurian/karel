# Nette Session panel

## Requirements

[Nette Framework 2.2](http://nette.org) or higher and PHP 5.3 or higher.

## Documentation
Simple DebugBar to show contents of session.

## Installation

The best way to install Kdyby/NetteSessionPanel is using [Composer](http://getcomposer.org/):

```sh
$ composer require kdyby/nette-session-panel:@dev
```

To load SessionPanel into the DebugBar insert following code into config.neon.

```yml
extensions:
	debugger.session: Kdyby\SessionPanel\DI\SessionPanelExtension
```
