<?php
/**
 * PHP version compatibility.
 *
 * @package Bootswatch
 */

add_action( 'init', 'bootswatch_php_version_check', -1 );
function bootswatch_php_version_check() {

	/**
	 * Exit function if PHP version is compatible.
	 */
	if ( PHP_VERSION_ID > BOOTSWATCH_MINIMAL_PHP_VERSION_ID ) {
		return;
	}

	/**
	 * Switch theme.
	 */
	$stylesheet = bootswatch_get_wp_stylesheet();
	if ( $stylesheet ) {
		switch_theme( $stylesheet );
	}

	/**
	 * Let the admin know we switched the theme and explain why.
	 */
	add_action( 'admin_notices', 'bootswatch_php_version_admin_notice' );
}

function bootswatch_get_wp_stylesheet() {

	$stylesheets = array(
		'twentyninteen',
		'twentyeighteen',
		'twentyseventeen',
		'twentysixteen',
		'twentyfifteen',
		'twentyfourteen',
		'twentythirteen',
		'twentytwelve',
		'twentyeleven',
		'twentyten',
	);

	foreach ($stylesheets as $stylesheet) {
		if ( file_exists( WP_CONTENT_DIR . "/themes/$stylesheet/style.css" ) ) {
			return $stylesheet;
		}
	}

	return false;
}

function bootswatch_php_version_admin_notice() {

	$stylesheet = bootswatch_get_wp_stylesheet();
	if ( $stylesheet ) {
		$theme   = wp_get_theme( $stylesheet );
		// Translators: %1$s is the current PHP version and %2$s is a WordPress default theme name.
		$message = sprintf( __( 'Bootswatch requires <code>PHP 5.4</code> or higher but you are using <code>PHP %1$s</code>, as such, Bootswatch cannot be activated, %2$s will be activated instead. We hope you will uprade PHP very soon.', 'bootswatch' ), PHP_VERSION, $theme->get( 'Name' ) );
	} else {
		$message = sprintf( __( 'Bootswatch requires <code>PHP 5.4</code> or higher but you are using <code>PHP %1$s</code>, as such, Bootswatch will not work until you uprade PHP.', 'bootswatch' ), PHP_VERSION );
	}
	bootswatch_admin_notice( $message, 'php-compatibility-error', 'error', false );
	echo '<style>#message2 { display: none; }</style>';
}

function bootswatch_admin_notice( $message, $id, $type = 'success', $is_dismissible = true ) {

	$id = "bootswatch-notice-$id";

	/**
	 * Skip dismissed notices.
	 */
	if ( ! empty( $_COOKIE[ "$id-dismissed" ] ) ) {
		return;
	}

	/**
	 * Classes.
	 */
	$classes = sprintf( 'notice notice-%s %s bootswatch-notice'
		, $type
		, $is_dismissible ? 'is-dismissible' : ''
	);

	/**
	 * Prepare message.
	 *
	 * Add the title if this is the first notice.
	 */
	static $once_html = false;
	$__message = '';
	$__message .= ! $once_html ? ( '<h3>' . __( 'Howdy! Bootswatch here...', 'bootswatch' ) . '</h3>' ) : '';
	$__message .= '<p>' . $message . '</p>';
	printf( '<div id="%s" class="%s">%s</div>', $id, $classes, $__message );
	$once_html = true;

	/**
	 * Output JavaScript common with all dismissible notices.
	 */
	static $once_js   = false;
	if ( $is_dismissible && ! $once_js ) {
		?>
		<script>
			function boostwatchCreateCookie( name, value = 1, days = 7 ) {
				var expiry_date = (new Date((new Date).getTime() + days * 86400000)).toUTCString();
				document.cookie = `${name}=${value}; expires=${expiry_date}; path=/`;
			}
			function boostwatchSetupNotice( noticeID ) {
				jQuery( document ).on( 'click', `#${noticeID} .notice-dismiss`, function() {
					boostwatchCreateCookie( `${noticeID}-dismissed`, 1, 7 );
				} );
			}
		</script>
		<?php
		$once_js = true;
	}

	/**
	 * Outout JS for this notice if it's dismissible
	 */
	if ( $is_dismissible ) {
		?>
		<script>
			jQuery( function($) {
				boostwatchSetupNotice( '<?php echo esc_html( $id ); ?>' );
			} );
		</script>
		<?php
	}
}
