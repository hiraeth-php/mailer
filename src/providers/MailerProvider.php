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
	 * Get the interfaces for which the provider operates.
	 *
	 * @access public
	 * @return array A list of interfaces for which the provider operates
	 */
	static public function getInterfaces(): array
	{
		return [
			Hiraeth\Mailer\Mailer::class
		];
	}


	/**
	 * Prepare the instance.
	 *
	 * @access public
	 * @var object $instance The unprepared instance of the object
	 * @param Hiraeth\Application $app The application instance for which the provider operates
	 * @return object The prepared instance
	 */
	public function __invoke(object $instance, Hiraeth\Application $app): object
	{
		$instance->setSender(
			$app->getConfig('mailer', 'mailer.contacts.sender.email', NULL),
			$app->getConfig('mailer', 'mailer.contacts.sender.name',  NULL)
		);

		if ($app->getEnvironment('DEBUG', FALSE)) {
			$instance->setDebugRecipient(
				$app->getConfig('mailer', 'mailer.contacts.debug.email', NULL),
				$app->getConfig('mailer', 'mailer.contacts.debug.name',  NULL)
			);
		}

		return $instance;
	}
}
