<?php

namespace Hiraeth\Mailer;

use Hiraeth\Templates;
use PHPMailer\PHPMailer\PHPMailer;

/**
 *
 */
class Mailer
{


	/**
	 * The delay(in microseconds) before retrying to send.
	 */
	protected $attemptDelay = 500000;


	/**
	 * Number of retries for the mailer.
	 */
	protected $attemptsMax = 3;


	/**
	 *
	 */
	protected $debugRecipient = NULL;


	/**
	 *
	 */
	protected $mail = NULL;


	/**
	 *
	 */
	protected $sender = NULL;


	/**
	 *
	 */
	protected $template = NULL;


	/**
	 *
	 */
	protected $templates = NULL;


	/**
	 *
	 */
	public function __construct(PHPMailer $mail, Templates\Manager $templates)
	{
		$this->mail      = $mail;
		$this->templates = $templates;
	}


	/**
	 *
	 */
	public function load(string $template): Mailer
	{
		$this->template = $this->templates->load($template);

		return $this;
	}


	/**
	 *
	 */
	public function send(callable $addresser, $data)
	{
		if (!$this->template) {
			throw new \RuntimeException(sprintf(
				'Cannot send e-mail without a template.  Try calling load().'
			));
		}

		$message  = clone $this->mail;
		$mailtype = $this->getMailType();

		$addresser($message);

		if ($this->debugRecipient) {
			$message->clearAllRecipients();
			$message->addAddress($this->debugRecipient['email'], $this->debugRecipient['name']);
		}

		$content = $this->render($data);
		$content = explode('----', $content, 2);

		$message->Subject = $content[0];
		$message->Body    = $content[1];

		if ($this->sender) {
			$message->setFrom($this->sender['email'], $this->sender['name']);
		} else {
			$message->setFrom('noreply@example.com', 'No Reply');
		}

		if ($mailtype == 'html') {
			$message->isHTML(TRUE);
		}

		if ($mailtype != 'txt') {
			$content = explode('----', $message->Body);

			if (count($content) > 1) {
				$message->Body    = $content[0];
				$message->AltBody = $content[1];
			}
		}

		foreach (range(1, $this->attemptsMax) as $attempt) {
			if (!$message->send()) {
				if ($attempt == $this->attemptsMax) {
					throw new \RuntimeException(sprintf(
						'Could not send e-mail: %s',
						$message->ErrorInfo
					));
				} else {
					usleep($this->attemptsDelay);
				}

			} else {
				break;
			}
		}

		return $message->Body;
	}


	/**
	 * Set the delay(in microseconds) between retries.
	 *
	 * @param integer $delay - in micorseconds
	 * @return void
	 */
	public function setAttemptsDelay(int $delay)
	{
		$this->attemptDelay = $delay;
	}


	/**
	 * Set the max number of retry attempts.
	 *
	 * @param integer $attempts
	 * @return void
	 */
	public function setAttemptsMax(int $attempts)
	{
		$this->attemptsMax = $attempts;
	}


	/**
	 *
	 */
	public function setDebugRecipient($email, $name)
	{
		$this->debugRecipient = [
			'name'  => $name,
			'email' => $email
		];
	}


	/**
	 *
	 */
	public function setSender($email, $name)
	{
		$this->sender = [
			'name'  => $name,
			'email' => $email
		];
	}


	/**
	 *
	 */
	protected function getMailType(): string
	{
		return $this->template->getExtension();
	}


	/**
	 *
	 */
	protected function render($data)
	{
		return $this->template->setAll($data)->render();
	}
}
