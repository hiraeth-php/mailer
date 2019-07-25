<?php

namespace Hiraeth\Mailer;

use Hiraeth;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Delegates are responsible for constructing dependencies for the dependency injector.
 *
 * Each delegate operates on a single concrete class and provides the class that it is capable
 * of building so that it can be registered easily with the application.
 */
class PHPMailerDelegate implements Hiraeth\Delegate
{
	/**
	 * Get the class for which the delegate operates.
	 *
	 * @static
	 * @access public
	 * @return string The class for which the delegate operates
	 */
	static public function getClass(): string
	{
		return PHPMailer::class;
	}


	/**
	 * Get the instance of the class for which the delegate operates.
	 *
	 * @access public
	 * @param Hiraeth\Application $app The application instance for which the delegate operates
	 * @return object The instance of the class for which the delegate operates
	 */
	public function __invoke(Hiraeth\Application $app): object
	{
		$mailer   = new PHPMailer();
		$settings = [
			'host' => $app->getEnvironment('SMTP_HOST', NULL),
			'user' => $app->getEnvironment('SMTP_USER', NULL),
			'pass' => $app->getEnvironment('SMTP_PASS', NULL),
			'port' => $app->getEnvironment('SMTP_PORT', NULL),
			'tls'  => $app->getEnvironment('SMTP_TLS',  NULL),
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
