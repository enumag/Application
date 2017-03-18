<?php

namespace Enumag\Application\UI;

use Arachne\EntityLoader\Application\RequestEntityLoader;
use Arachne\EntityLoader\Application\RequestEntityUnloader;
use Nette\Application\BadRequestException;
use Nette\Application\Request;
use Nette\Http\Session;
use Nette\Object;
use Nette\Utils\Random;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RequestStorage extends Object
{

	const REQUEST_KEY = '_rid';

	const SESSION_SECTION = 'Enumag.Application/requests';

	/** @var Session */
	protected $session;

	/** @var RequestEntityLoader|null */
	protected $loader;

	/** @var RequestEntityUnloader|null */
	protected $unloader;

	public function __construct(Session $session, RequestEntityLoader $loader = null, RequestEntityUnloader $unloader = null)
	{
		$this->session = $session;
		$this->loader = $loader;
		$this->unloader = $unloader;
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
		if ($this->unloader) {
			$this->unloader->filterOut($request);
		} elseif ($this->loader) {
			$this->loader->filterOut($request);
		}

		$session = $this->session->getSection(self::SESSION_SECTION);
		do {
			$key = Random::generate(5);
		} while (isset($session[$key]));

		$session[$key] = $request;
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
		if (!isset($session[$key])) {
			return;
		}
		// Cloning is necessary to prevent the stored request from being modified too.
		$request = clone $session[$key];

		if ($this->loader) {
			try {
				$this->loader->filterIn($request);
			} catch (BadRequestException $e) {
				return;
			}
		}

		$request->setFlag(Request::RESTORED, true);
		return $request;
	}

}
