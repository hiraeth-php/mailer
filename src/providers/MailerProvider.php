<?php

namespace Hiraeth\Mailer;

use Hiraeth;

/**
 * Providers add additional dependencies or configuration for objects of certain interfaces.
 *
 * Each provider operates on one or more interfaces and provides the interfaces that it is capable
 * of providing for so that it can be registered easily with the application.
 */
class MailerProvider implements Hiraeth\Provider
{
	/**
	 * {@inheritDoc}
	 */
	static public function getInterfaces(): array
	{
		return [
			Mailer::class
		];
	}


	/**
	 * {@inheritDoc}
	 *
	 * @param Mailer $instance
	 */
	public function __invoke(object $instance, Hiraeth\Application $app): object
	{
		$instance->setSender(
			$app->getConfig('packages/mailer', 'mailer.contacts.sender.email', 'noreplay@hiraeth.dev'),
			$app->getConfig('packages/mailer', 'mailer.contacts.sender.name',  'No Reply')
		);

		$instance->setAttemptsDelay(
			$app->getConfig('packages/mailer', 'mailer.attempts.delay', 500000)
		);

		$instance->setAttemptsMax(
			$app->getConfig('packages/mailer', 'mailer.attempts.max', 3)
		);

		if ($app->getEnvironment('DEBUG', FALSE)) {
			$instance->setDebugRecipient(
				$app->getConfig('packages/mailer', 'mailer.contacts.debug.email', NULL),
				$app->getConfig('packages/mailer', 'mailer.contacts.debug.name',  NULL)
			);
		}

		return $instance;
	}
}
