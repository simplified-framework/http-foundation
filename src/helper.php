<?php

/* http helper functions */

use Simplified\Http\RouteCollection;
use Simplified\Http\ResourceNotFoundException;

function route($name) {
	$routes = RouteCollection::instance();
	if (isset($routes[$name])) {
		$item = $routes[$name]->path;
		if (func_num_args() == 2) {
			$params = func_get_arg(1);
			$keys = array_keys($params);

			foreach ($keys as $key) {
				$item = str_replace("{".$key."?}", $params[$key], $item);
				$item = str_replace("{".$key."}", $params[$key], $item);
			}
		}
		return $item;
	}
	throw new ResourceNotFoundException("No route named $name found");
}

function url($url) {
	return ($url == '/') ? '' : $url;
}

if (class_exists('\\Simplified\\TwigBridge\\TwigRenderer')) {
	class SimplifiedRouteExtension extends \Twig_Extension {
		public function getFunctions() {
			return array(
				'route'  => new \Twig_SimpleFunction('route',
					array($this, 'route'),array('is_safe' => array('html'))
				),
			);
		}

		public function route ($name, $arg = null) {
			return route($name, $arg);
		}

		public function getName() {
			return 'SimplifiedRouteExtension';
		}
	}

	\Simplified\TwigBridge\TwigRenderer::registerExtension(new SimplifiedRouteExtension());
}