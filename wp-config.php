<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link http://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('REVISR_GIT_PATH', ''); // Added by Revisr
define('DB_NAME', 'gmagigol_wp960');

/** MySQL database username */
define('DB_USER', 'gmagigol_wp960');

/** MySQL database password */
define('DB_PASSWORD', 'vP4.5@8f2S');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '7k6imtvjcrmcy1gal6fzmlr75m5yj9btapzsdypseajbiwijv0ftkeu7jaudrsq1');
define('SECURE_AUTH_KEY',  'rcu6il0brojbaamoj27sshr3jt7xpng1sekjhomczo36hcetznlzn0a2n929hozm');
define('LOGGED_IN_KEY',    'bng2khuuzzrqqcaxeo1upmwl3b1b88vdj0y5zqpcygbsts0orajkr4stjlfpwcfu');
define('NONCE_KEY',        'hjftmvufx4pz1erqxsn18r0u8hlejtkvfbyx9pk6lvaniyvximmwyak3qb1te16s');
define('AUTH_SALT',        'i9kd13xzvfk6bhrcb2udqt2qs7w6zu1o6pcogjnyngelpcwlm17yqvsjzsgb4gam');
define('SECURE_AUTH_SALT', 'ixhhjohqqkqsvjvjs2fkfddfjnkcvscgwzf9seiltnnz6adr4lqzn6l5vo57tmpg');
define('LOGGED_IN_SALT',   '612yfo6ntpajbfzcn9ywrxs8pcneefokkqo1noi9ddqvzibmjuq7cbrtpqs0ltro');
define('NONCE_SALT',       'qdzhwftfm7uxqygvk2kbmt5bj0izyc7eznuctkvol4cocbdj5dfwdqxlk5rolnkx');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
