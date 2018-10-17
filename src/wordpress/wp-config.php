<?php
//Begin Really Simple SSL Load balancing fix
if ((isset($_ENV["HTTPS"]) && ("on" == $_ENV["HTTPS"]))
    || (isset($_SERVER["HTTP_X_FORWARDED_SSL"]) && (strpos($_SERVER["HTTP_X_FORWARDED_SSL"], "1") !== false))
    || (isset($_SERVER["HTTP_X_FORWARDED_SSL"]) && (strpos($_SERVER["HTTP_X_FORWARDED_SSL"], "on") !== false))
    || (isset($_SERVER["HTTP_CF_VISITOR"]) && (strpos($_SERVER["HTTP_CF_VISITOR"], "https") !== false))
    || (isset($_SERVER["HTTP_CLOUDFRONT_FORWARDED_PROTO"]) && (strpos($_SERVER["HTTP_CLOUDFRONT_FORWARDED_PROTO"], "https") !== false))
    || (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && (strpos($_SERVER["HTTP_X_FORWARDED_PROTO"], "https") !== false))
) {
    $_SERVER["HTTPS"] = "on";
}
//END Really Simple SSL

define('DB_HOST', $_ENV['DB_HOST']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASSWORD', $_ENV['DB_PASSWORD']);

define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');


define('AUTH_KEY',         $_ENV['AUTH_KEY']);
define('SECURE_AUTH_KEY',  $_ENV['SECURE_AUTH_KEY']);
define('LOGGED_IN_KEY',    $_ENV['LOGGED_IN_KEY']);
define('NONCE_KEY',        $_ENV['NONCE_KEY']);
define('AUTH_SALT',        $_ENV['AUTH_SALT']);
define('SECURE_AUTH_SALT', $_ENV['SECURE_AUTH_SALT']);
define('LOGGED_IN_SALT',   $_ENV['LOGGED_IN_SALT']);
define('NONCE_SALT',       $_ENV['NONCE_SALT']);


if (isset($_ENV["FLEM_ENV"]) && (("local-dev" == $_ENV["FLEM_ENV"]) || ("test" == $_ENV["FLEM_ENV"]))) {
  define('MAX_CACHE_SECONDS', 1);
} else {
  define('MAX_CACHE_SECONDS', HOUR_IN_SECONDS);
}

if (isset($_ENV["FLEM_ENV"]) && ("local-dev" == $_ENV["FLEM_ENV"])) {
  # Local dev settings
  define('WP_HOME', 'http://localhost:3000');
  define('WP_SITEURL', 'http://localhost:3000');
  if (isset($_ENV["WP_CONTENT_URL"])) {
    define('WP_CONTENT_URL', $_ENV["WP_CONTENT_URL"]);
  }
} elseif (isset($_ENV["FLEM_ENV"]) && ("test" == $_ENV["FLEM_ENV"])) {
  define('WP_HOME', 'http://fleming-test.eu-west-1.elasticbeanstalk.com');
  define('WP_SITEURL', 'http://fleming-test.eu-west-1.elasticbeanstalk.com');
} elseif (isset($_SERVER["HTTP_X_AMZ_CF_ID"])) {
  # From CloudFront
  # CloudFront overwrites the host header with the CF origin.
  # We need to persuade WordPress we're on the right domain or it will redirect us.
  if (isset($_ENV["CLOUDFRONT_HOST"])) {
    $_SERVER["HTTP_HOST"] = $_ENV["CLOUDFRONT_HOST"];
  } else {
    $_SERVER["HTTP_HOST"] = 'www.flemingfund.org';
  }
} elseif (isset($_SERVER["HTTP_HOST"]) && ("origin.flemingfund.org" == $_SERVER["HTTP_HOST"])) {
  # Direct access to origin
  define('WP_HOME', 'https://origin.flemingfund.org/');
  define('WP_SITEURL', 'https://origin.flemingfund.org/');
} elseif (isset($_ENV["FLEM_ENV"]) && ("stage" == $_ENV["FLEM_ENV"])
  && isset($_SERVER["HTTP_HOST"]) && ("fleming-stage.eu-west-1.elasticbeanstalk.com" == $_SERVER["HTTP_HOST"])) {
  # Stage origin
  define('WP_HOME', 'http://fleming-stage.eu-west-1.elasticbeanstalk.com/');
  define('WP_SITEURL', 'http://fleming-stage.eu-west-1.elasticbeanstalk.com/');
}

$table_prefix  = 'wp_';
define('WP_DEBUG', (isset($_ENV["FLEM_ENV"]) && ("local-dev" == $_ENV["FLEM_ENV"])));
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
require_once(ABSPATH . 'wp-settings.php');
