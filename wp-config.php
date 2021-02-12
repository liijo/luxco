<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'luxco' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '8R$y!m)|A_]yP``^@HP~>8#)oJsY2}]_72Jf0U4YbN!p2WNOJD{Wxs8p6D1}y- C' );
define( 'SECURE_AUTH_KEY',  '*L_Dd!^%;`xgQS]~Of_1BPDW=p>X51MJ+hm.V?&7;_!^X`cM3IyW@/g`4]Z5a#&`' );
define( 'LOGGED_IN_KEY',    ';hv$Wi; V!XC/1Wc|KM=Y*4=de(6ff-)#d {mQ4rPh`(k2K,z}fkg3T`f8IiGc{:' );
define( 'NONCE_KEY',        ';$&2jB+rqdvK2}t:[G5e~F&/.N)4B>f^,svUK*)Ml>t2_8EtZtj]Bd,Ue* Th(kk' );
define( 'AUTH_SALT',        '`]fN,e6Wyl1kkH}9^O3hx<2Kff?D@T;2zO{fa+1kXncU~oZ_Mpn}y^M ahvoJqa9' );
define( 'SECURE_AUTH_SALT', 'm|V1-YPN_7D!3Pu(Ec03FED3&^=Kd|mVx7G 95IXjzML5I|+d<l0:tk,`x6Cy:Z.' );
define( 'LOGGED_IN_SALT',   '+t46bdv(-)ZkIdoq2SM05t.{CI7u3Kp?~S(P39E70U%3RDe?-|uqX~T]L?U0Cd0(' );
define( 'NONCE_SALT',       's>)v6dKv$4*C9_BRPWUhZK|F_`m/rgg RT/(2!{`zc?b!c;MaJY_R@Xr^(VcU*NB' );

/**#@-*/

/**
 * WordPress Database Table prefix.
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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
