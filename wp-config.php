<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'store_db' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'Yya)IqD#v]D4:ugeY7fJ0%rkNxQ001n%>BiF:H#E$<6Vwh^Mi_neOxv<r6e8aPTL' );
define( 'SECURE_AUTH_KEY',  'Wc*4O0?HHn`3ot`<z%~6u@;5z3XefTLau;Rw2PeuG(pH6z18%EQFYG13kq>q@PW5' );
define( 'LOGGED_IN_KEY',    'j:T7+W=.hk`eCDFcokP/8eKw>$o;m%bvMt^3R[}_<2/dv~BgMV9{*fzGxFNA8CO~' );
define( 'NONCE_KEY',        'EOB({b)sZ,!:wW`J~|+Yu}.#6CP$l&0:t*Q G:mP4^G1.XjMY9`n. 9iF6Dm=k0,' );
define( 'AUTH_SALT',        ')9g&*`?NOVg9Eq4d!IY})fc3FE^s3wPJl!jfUl3LGt5#>;#A8Hv:a&T&}2DLpaI ' );
define( 'SECURE_AUTH_SALT', '3)QkD$a2x`~,u^mSg&-3KECK<Y_qVrr|aDk^Ykr_N)wg1CHm,_qb[E`ZvDA1(ll%' );
define( 'LOGGED_IN_SALT',   '_1k*@G##^!T#=H|RW?rq3](3qg4ER5_SHR6??$sj(sRmlbDKdjbM$Q%/zE+;FUv%' );
define( 'NONCE_SALT',       'ORf`A7rC5]C0?AE-V9rx5m_4mWEP`T;_cHHl870@|Qas5Q$!pnVLwaOaM?G8%!RI' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
