<?php

namespace Enumag\Application;

use Nette\Application\AbortException;
use Nette\Application\Application as BaseApplication;
use Nette\Application\BadRequestException;
use Nette\Application\InvalidPresenterException;
use Nette\Application\IPresenterFactory;
use Nette\Application\IRouter;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Http\Response;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class Application extends BaseApplication
{

	/** @var IPresenterFactory */
	private $presenterFactory;

	/** @var IRouter */
	private $router;

	/** @var IRequest */
	private $httpRequest;

	/** @var IResponse */
	private $httpResponse;

	/** @var Request */
	private $initialRequest;

	public function __construct(IPresenterFactory $presenterFactory, IRouter $router, IRequest $httpRequest, IResponse $httpResponse)
	{
		parent::__construct($presenterFactory, $router, $httpRequest, $httpResponse);
		$this->presenterFactory = $presenterFactory;
		$this->router = $router;
		$this->httpRequest = $httpRequest;
		$this->httpResponse = $httpResponse;
	}

	public function run()
	{
		try {
			$this->onStartup($this);
			$this->processRequest($this->createInitialRequest());
			$this->onShutdown($this);

		} catch (\Exception $e) {
			$this->onError($this, $e);

			// if catchExceptions is NULL, catch only BadRequestException
			if (($this->catchExceptions || ($this->catchExceptions === null && $e instanceof BadRequestException)) && $this->errorPresenter) {
				try {
					$this->processException($e);
					$this->onShutdown($this, $e);
					return;

				} catch (\Exception $e) {
					$this->onError($this, $e);
				}
			}
			$this->onShutdown($this, $e);
			throw $e;
		}
	}

	public function processException(\Exception $e)
	{
		if (!$e instanceof BadRequestException && $this->httpResponse instanceof Response) {
			$this->httpResponse->warnOnBuffer = false;
		}
		if (!$this->httpResponse->isSent()) {
			$this->httpResponse->setCode($e instanceof BadRequestException ? ($e->getCode() ?: 404) : 500);
		}

		$requests = $this->getRequests();
		$request = end($requests) ?: $this->initialRequest;
		$args = array('exception' => $e, 'request' => $request);
		$errorPresenter = $request ? $this->findErrorPresenter($request->getPresenterName()) : $this->errorPresenter;

		if ($this->presenter instanceof Presenter) {
			try {
				$this->presenter->forward(":$errorPresenter:", $args);
			} catch (AbortException $_) {
				$this->processRequest($this->presenter->getLastCreatedRequest());
			}
		} else {
			$this->processRequest(new Request($errorPresenter, Request::FORWARD, $args));
		}
	}

	/**
	 * @return Request
	 */
	public function createInitialRequest()
	{
		$request = $this->router->match($this->httpRequest);

		if (!$request instanceof Request) {
			throw new BadRequestException('No route for HTTP request.');
		}

		$name = ':' . $request->getPresenterName();
		if (strcasecmp(substr($name, - (strlen($this->errorPresenter) + 1)), ':' . $this->errorPresenter) === 0) {
			$this->initialRequest = $request;
			throw new BadRequestException('Invalid request. Presenter is not achievable.');
		}

		try {
			$name = $request->getPresenterName();
			$this->presenterFactory->getPresenterClass($name);

		} catch (InvalidPresenterException $e) {
			$this->initialRequest = $request;
			throw new BadRequestException($e->getMessage(), 0, $e);
		}

		return $request;
	}

	private function findErrorPresenter($module)
	{
		while ($module !== '') {
			$pos = strrpos($module, ':');
			$module = $pos !== false ? substr($module, 0, $pos) : '';
			$errorPresenter = "$module:$this->errorPresenter";
			try {
				$this->presenterFactory->getPresenterClass($errorPresenter);
			} catch (InvalidPresenterException $_) {
				continue;
			}
			return $errorPresenter;
		}
		return $this->errorPresenter;
	}

}
