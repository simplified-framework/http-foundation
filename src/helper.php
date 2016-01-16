<?php

/* http helper functions */

use Simplified\Http\RouteCollection;
use Simplified\Http\ResourceNotFoundException;

function route($name) {
	$routes = RouteCollection::instance();
	if ($routes->has($name)) {
		$item = $routes->get($name)->path;
		if (func_num_args() == 2) {
			$params = func_get_arg(1);
			$keys = array_keys($params);

			foreach ($keys as $key) {
				$item = str_replace("{".$key."?}", $params[$key], $item);
				$item = str_replace("{".$key."}", $params[$key], $item);
			}
		}

		$item = rtrim($item, '/');
		return $item;
	}

	throw new ResourceNotFoundException("No route named $name found");
}

function url($url) {
	return ($url == '/') ? '' : $url;
}

function redirect($target) {
	if (headers_sent()) {
		print '<html><head><meta http-equiv=refresh content="1; URL='.$target.'"></head>
		<body><script type="text/javascript">window.location.href="'.$target.'";</script></body></html>';
	} else {
		header('Location: ' . $target, 301);
		die();
	}
}

if (class_exists('\\Simplified\\TwigBridge\\TwigRenderer')) {
	class SimplifiedRouteExtension extends \Twig_Extension {
		public function getFunctions() {
			return array(
				'route'  => new \Twig_SimpleFunction('route',
					array($this, 'route'),array('is_safe' => array('html'))
				),
				'url'  => new \Twig_SimpleFunction('url',
					array($this, 'url'),array('is_safe' => array('html'))
				),
				'redirect'  => new \Twig_SimpleFunction('redirect',
					array($this, 'redirect'),array('is_safe' => array('html'))
				),
			);
		}

		public function route ($name, $arg = null) {
			return route($name, $arg);
		}

		public function url($target) {
			return url($target);
		}

		public function redirect($target) {
			redirect($target);
		}

		public function getName() {
			return 'SimplifiedRouteExtension';
		}
	}

	\Simplified\TwigBridge\TwigRenderer::registerExtension(new SimplifiedRouteExtension());
}