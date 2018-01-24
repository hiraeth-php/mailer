<?php

namespace Hiraeth\Mailer;

use Hiraeth\Broker;
use Hiraeth\Delegate;
use Hiraeth\Application;
use Hiraeth\Configuration;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Providers add additional dependencies or configuration for objects of certain interfaces.
 */
class PHPMailerDelegate implements Delegate
{
	/**
	 * Get the class for which the delegate operates.
	 *
	 * @static
	 * @access public
	 * @return string The class for which the delegate operates
	 */
	static public function getClass()
	{
		return 'PHPMailer';
	}


	/**
	 * Get the interfaces for which the provider operates.
	 *
	 * @access public
	 * @return array A list of interfaces for which the provider operates
	 */
	static public function getInterfaces()
	{
		return [];
	}


	/**
	 *
	 */
	public function __construct(Application $app, Configuration $config)
	{
		$this->app = $app;
	}


	/**
	 * Prepare the instance.
	 *
	 * @access public
	 * @return Object The prepared instance
	 */
	public function __invoke(Broker $broker)
	{
		$mailer   = new PHPMailer();
		$settings = [
			'host' => $this->app->getEnvironment('SMTP_HOST', NULL),
			'user' => $this->app->getEnvironment('SMTP_USER', NULL),
			'pass' => $this->app->getEnvironment('SMTP_PASS', NULL),
			'port' => $this->app->getEnvironment('SMTP_PORT', NULL),
			'tls'  => $this->app->getEnvironment('SMTP_TLS',  NULL),
		];

		if (isset($settings['host']) && $settings['host']) {
			$mailer->isSMTP();
			$mailer->Host = $settings['host'];
		}

		if (isset($settings['tls']) && $settings['tls']) {
			$mailer->SMTPSecure = 'tls';
		}

		if (isset($settings['user']) && $settings['user']) {
			$mailer->SMTPAuth = TRUE;
			$mailer->Username = $settings['user'];
			$mailer->Password = $settings['pass'];
		}

		if (isset($settings['port']) && $settings['port']) {
			$mailer->Port = $settings['port'];
		}

		return $mailer;
	}
}
