<?php

namespace Hiraeth\Mailer;

use Twig\Environment as Twig;
use PHPMailer\PHPMailer\PHPMailer;

/**
 *
 */
class Mailer
{
	/**
	 *
	 */
	protected $debugRecipient = NULL;


	/**
	 *
	 */
	protected $sender = NULL;


	/**
	 *
	 */
	public function __construct(PHPMailer $mail, Twig $twig)
	{
		$this->mail = $mail;
		$this->twig = $twig;
	}


	/**
	 *
	 */
	public function load($template)
	{
		$this->template = $this->twig->loadTemplate($template);

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

		if (!$message->send()) {
			throw new \RuntimeException(sprintf(
				'Could not send e-mail: %s',
				$message->ErrorInfo
			));
		}

		return $message->Body;
	}


	/**
	 *
	 */
	public function setDebug($email, $name)
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
	protected function getMailType()
	{
		return pathinfo($this->template->getTemplateName(), PATHINFO_EXTENSION);
	}


	/**
	 *
	 */
	protected function render($data)
	{
		$content = $this->template->render($data);

		if (strpos($content, '----') === FALSE) {
			throw new RuntimeException(sprintf(
				'The loaded template "%s" does not contain a subject/body separator.',
				$this->template->getTemplateName()
			));
		}

		return $content;
	}
}
