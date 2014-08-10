<?php

namespace Enumag\Application;

use Arachne\EntityLoader\Application\RequestEntityLoader;
use Nette\Application\BadRequestException;
use Nette\Application\Request;
use Nette\Http\Session;
use Nette\Object;
use Nette\Security\User;
use Nette\Utils\Strings;

/**
 * @author Jáchym Toušek
 */
class RequestStorage extends Object
{

	const REQUEST_KEY = '_rid';

	const SESSION_SECTION = 'Enumag.Application/requests';

	/** @var User */
	protected $user;

	/** @var Session */
	protected $session;

	/** @var RequestEntityLoader|null */
	protected $loader;

	public function __construct(User $user, Session $session, RequestEntityLoader $loader = NULL)
	{
		$this->user = $user;
		$this->session = $session;
		$this->loader = $loader;
	}

	/**
	 * Stores request to session.
	 * @param Request $request
	 * @param mixed $expiration
	 * @return string
	 */
	public function storeRequest(Request $request, $expiration = '+ 10 minutes')
	{
		$request = clone $request;
		if ($this->loader) {
			$this->loader->removeEntities($request);
		}

		$session = $this->session->getSection(self::SESSION_SECTION);
		do {
			$key = Strings::random(5);
		} while (isset($session[$key]));

		$session[$key] = [ $this->user->getId(), $request ];
		$session->setExpiration($expiration, $key);
		return $key;
	}

	/**
	 * Loads request from session.
	 * @param string $key
	 * @return Request
	 */
	public function loadRequest($key)
	{
		$session = $this->session->getSection(self::SESSION_SECTION);
		if (!isset($session[$key]) || ($session[$key][0] !== NULL && $session[$key][0] !== $this->user->getId())) {
			return;
		}
		// Cloning is necessary to prevent the stored request from being modified too.
		$request = clone $session[$key][1];

		if ($this->loader) {
			try {
				$this->loader->loadEntities($request);
			} catch (BadRequestException $e) {
				return;
			}
		}

		$request->setFlag(Request::RESTORED, TRUE);
		return $request;
	}

}
