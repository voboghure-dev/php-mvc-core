<?php
namespace voboghure\phpmvc;

use voboghure\phpmvc\db\Database;

class Application {
	const EVENT_BEFORE_REQUEST = 'beforeRequest';
	const EVENT_AFTER_REQUEST  = 'afterRequest';
	protected $eventListeners  = [];

	public static string $ROOT_PATH;
	public Request $request;
	public Response $response;
	public Session $session;
	public Database $db;
	public Router $router;
	public static Application $app;
	public  ? Controller $controller = null;
	public  ? UserModel $user;
	public View $view;
	public  ? string $userClass = null;
	public string $layout       = 'main';

	public function __construct( $rootPath, array $config ) {
		self::$ROOT_PATH = $rootPath;
		self::$app       = $this;
		$this->request   = new Request();
		$this->response  = new Response();
		$this->session   = new Session();
		$this->router    = new Router( $this->request, $this->response );
		$this->db        = new Database( $config['db'] );
		$this->view      = new View();
		$this->userClass = $config['userClass'] ?? null;

		$primaryValue = $this->session->get( 'user' );
		if ( $primaryValue ) {
			$primaryKey = $this->userClass::primaryKey();
			$this->user = $this->userClass::findOne( [$primaryKey => $primaryValue] );
		} else {
			$this->user = null;
		}

	}

	public function run() {
		$this->triggerEvent( self::EVENT_BEFORE_REQUEST );

		try {
			echo $this->router->resolve();
		} catch ( \Exception $e ) {
			$this->response->setStatusCode( $e->getCode() );
			echo $this->view->renderView( '_error', [
				'exception' => $e,
			] );
		}

		$this->triggerEvent( self::EVENT_AFTER_REQUEST );
	}

	public function getController() {
		return $this->controller;
	}

	public function setController( Controller $controller ) {
		$this->controller = $controller;
	}

	public static function isGuest() {
		return  ! self::$app->user;
	}

	public function login( UserModel $user ) {
		$this->user   = $user;
		$primaryKey   = $user->primaryKey();
		$primaryValue = $user->{$primaryKey};
		$this->session->set( 'user', $primaryValue );

		return true;
	}

	public function logout() {
		$this->user = null;
		$this->session->remove( 'user' );
	}

	public function triggerEvent( $eventName ) {
		$callbacks = $this->eventListeners[$eventName] ?? [];
		foreach ( $callbacks as $callback ) {
			call_user_func( $callback );
		}
	}

	public function on( $eventName, $callback ) {
		$this->eventListeners[$eventName][] = $callback;
	}
}