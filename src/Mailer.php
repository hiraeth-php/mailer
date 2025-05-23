<?php

namespace Hiraeth\Mailer;

use Hiraeth\Templates;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

/**
 *
 */
class Mailer
{


	/**
	 * The delay(in microseconds) before retrying to send.
	 *
	 * @var int
	 */
	protected $attemptsDelay = 250000;


	/**
	 * Number of retries for the mailer.
	 *
	 * @var int
	 */
	protected $attemptsMax = 3;


	/**
	 * @var null|array{name: string, email: string}
	 */
	protected $debugRecipient = NULL;


	/**
	 * @var PHPMailer
	 */
	protected $mail;


	/**
	 * @var null|array{name: string, email: string}
	 */
	protected $sender = NULL;


	/**
	 * @var Templates\Template
	 */
	protected $template;


	/**
	 * @var Templates\Manager
	 */
	protected $templates;


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
	 * @param array<string, mixed> $data
	 */
	public function send(callable $addresser, $data): string
	{
		if (!$this->template instanceof Templates\Template) {
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

		if (count($content) != 2) {
			$content[1] = $content[0];

			if (preg_match('#<title>(.*)</title>#', $content[1], $matches)) {
				$content[0] = $matches[1];
			} else {
				throw new RuntimeException('Could not determine e-mail subject');
			}
		}

		$message->Subject = strip_tags($content[0]);
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
	 */
	public function setAttemptsDelay(int $delay): Mailer
	{
		$this->attemptsDelay = $delay;

		return $this;
	}


	/**
	 * Set the max number of retry attempts.
	 *
	 * @param integer $attempts
	 */
	public function setAttemptsMax(int $attempts): Mailer
	{
		$this->attemptsMax = $attempts;

		return $this;
	}


	/**
	 *
	 */
	public function setDebugRecipient(string $email, string $name): Mailer
	{
		$this->debugRecipient = [
			'name'  => $name,
			'email' => $email
		];

		return $this;
	}


	/**
	 *
	 */
	public function setSender(string $email, string $name): Mailer
	{
		$this->sender = [
			'name'  => $name,
			'email' => $email
		];

		return $this;
	}


	/**
	 *
	 */
	protected function getMailType(): string
	{
		return $this->template->getExtension();
	}


	/**
	 * @param array<string, mixed> $data
	 */
	protected function render(array $data): string
	{
		return $this->template->setAll($data)->render();
	}
}
