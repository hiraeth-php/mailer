<?php

namespace Hiraeth\Mailer;

use Hiraeth\Broker;
use Hiraeth\Provider;
use Hiraeth\Application;
use Hiraeth\Configuration;

/**
 * Providers add additional dependencies or configuration for objects of certain interfaces.
 */
class MailerProvider implements Provider
{
	/**
	 * Get the interfaces for which the provider operates.
	 *
	 * @access public
	 * @return array A list of interfaces for which the provider operates
	 */
	static public function getInterfaces()
	{
		return ['Hiraeth\Mailer\Mailer'];
	}

	/**
	 *
	 */
	public function __construct(Application $app, Configuration $config)
	{
		$this->app    = $app;
		$this->config = $config;
	}


	/**
	 * Prepare the instance.
	 *
	 * @access public
	 * @return Object The prepared instance
	 */
	public function __invoke($instance, Broker $broker)
	{
		$instance->setSender(
			$this->config->get('mailer', 'contacts.sender.email', NULL),
			$this->config->get('mailer', 'contacts.sender.name',  NULL)
		);

		if ($this->app->getEnvironment('DEBUG', FALSE)) {
			$instance->setDebug(
				$this->config->get('mailer', 'contacts.debug.email', NULL),
				$this->config->get('mailer', 'contacts.debug.name',  NULL)
			);
		}

		return $instance;
	}
}
