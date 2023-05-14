<?php
namespace voboghure\phpmvc;

use voboghure\phpmvc\exceptions\NotFoundException;

class Router {
	public Request $request;
	public Response $response;
	protected array $routes = [];

	public function __construct( Request $request, Response $response ) {
		$this->request  = $request;
		$this->response = $response;
	}

	public function get( $path, $callback ) {
		$this->routes['get'][$path] = $callback;
	}

	public function post( $path, $callback ) {
		$this->routes['post'][$path] = $callback;
	}

	public function resolve() {
		$args = [ $this->request, $this->response ];
		$path   = $this->request->getPath();
		$params = explode( '/', trim( $path, '/' ) );
		if (  ! empty( $params ) ) {
			$path = '/' . $params[0]; // Append the / in callback
			array_shift( $params ); // remove first element as its the callback
			$args = array_merge($args, $params);
		}
		$method   = $this->request->method();
		$callback = $this->routes[$method][$path] ?? false;
		if ( $callback === false ) {
			throw new NotFoundException();
		}
		if ( is_string( $callback ) ) {
			return Application::$app->view->renderView( $callback );
		}
		if ( is_array( $callback ) ) {
			/** @var Controller $controller */
			$controller                   = new $callback[0]();
			Application::$app->controller = $controller;
			$controller->action           = $callback[1];
			$callback[0]                  = $controller;

			foreach ( $controller->getMiddlewares() as $middleware ) {
				$middleware->execute();
			}
		}

		return call_user_func_array( $callback, $args );
	}

}