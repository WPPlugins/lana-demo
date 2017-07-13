<?php
/**
 * Plugin Name: Lana Demo
 * Plugin URI: http://wp.lanaprojekt.hu/blog/wordpress-plugins/lana-demo/
 * Description: Demo user with editable roles and dashboard widgets.
 * Version: 1.0.6
 * Author: Lana Design
 * Author URI: http://wp.lanaprojekt.hu/blog/
 */

defined( 'ABSPATH' ) or die();
define( 'LANA_DEMO_VERSION', '1.0.6' );

/**
 * Language
 * load
 */
load_plugin_textdomain( 'lana-demo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

/**
 * Plugin Settings link
 *
 * @param $links
 *
 * @return mixed
 */
function lana_demo_plugin_settings_link( $links ) {
	$settings_link = '<a href="options-general.php?page=lana-demo-settings.php">' . __( 'Settings', 'lana-demo' ) . '</a>';
	array_unshift( $links, $settings_link );

	return $links;
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'lana_demo_plugin_settings_link' );

/**
 * Init
 * filter pre_update_option
 */
function lana_demo_init() {
	add_filter( 'pre_update_option_lana_demo_username', 'lana_demo_update_option_username', 10, 2 );
	add_filter( 'pre_update_option_lana_demo_password', 'lana_demo_update_option_password', 10, 2 );
}

add_action( 'init', 'lana_demo_init' );

/**
 * Lana Demo - add role
 */
function lana_demo_add_role() {

	add_role( 'lana_demo', __( 'Lana Demo', 'lana-demo' ), array(
		'read'         => true,
		'edit_posts'   => filter_var( get_option( 'lana_demo_role_edit_posts', '0' ), FILTER_VALIDATE_BOOLEAN ),
		'edit_pages'   => filter_var( get_option( 'lana_demo_role_edit_pages', '0' ), FILTER_VALIDATE_BOOLEAN ),
		'upload_files' => filter_var( get_option( 'lana_demo_role_upload_files', '0' ), FILTER_VALIDATE_BOOLEAN )
	) );
}

register_activation_hook( __FILE__, 'lana_demo_add_role' );

/**
 * Lana Demo - reload role for update option
 *
 * @param $option
 */
function lana_demo_update_option_reload_role( $option ) {

	$role_update_options = array(
		'lana_demo_role_edit_posts',
		'lana_demo_role_edit_pages',
		'lana_demo_role_upload_files'
	);

	if ( ! in_array( $option, $role_update_options ) ) {
		return;
	}

	remove_role( 'lana_demo' );
	lana_demo_add_role();
}

add_action( 'added_option', 'lana_demo_update_option_reload_role', 10, 1 );
add_action( 'updated_option', 'lana_demo_update_option_reload_role', 10, 1 );

/**
 * Update Option - username
 *
 * @param $new_username
 * @param $old_username
 *
 * @return mixed
 */
function lana_demo_update_option_username( $new_username, $old_username ) {

	$user_id = get_option( 'lana_demo_user_id', false );

	/**
	 * Add new user
	 */
	if ( ! $user_id ) {
		$userdata = array(
			'user_login' => $new_username,
			'user_pass'  => get_option( 'lana_demo_password', null ),
			'role'       => 'lana_demo'
		);

		$user_id = wp_insert_user( $userdata );

		if ( ! is_wp_error( $user_id ) ) {
			add_option( 'lana_demo_user_id', $user_id );

			return $new_username;
		}
	}

	return $old_username;
}

/**
 * Update Option - password
 *
 * @param $new_password
 * @param $old_password
 *
 * @return mixed
 */
function lana_demo_update_option_password( $new_password, $old_password ) {

	$user_id = get_option( 'lana_demo_user_id', false );

	/**
	 * Edit password
	 */
	if ( $user_id ) {

		$userdata = array(
			'ID'        => $user_id,
			'user_pass' => $new_password
		);

		$user_id = wp_update_user( $userdata );

		if ( ! is_wp_error( $user_id ) ) {
			return $new_password;
		}
	}

	return $old_password;
}

/**
 * Add Lana Demo Settings page
 * in Options page
 */
function lana_demo_settings_menu() {
	add_options_page( __( 'Lana Demo Settings', 'lana-demo' ), __( 'Lana Demo', 'lana-demo' ), 'manage_options', 'lana-demo-settings.php', 'lana_demo_settings' );

	/** call register settings function */
	add_action( 'admin_init', 'lana_demo_register_settings' );
}

add_action( 'admin_menu', 'lana_demo_settings_menu' );

/**
 * Register settings
 */
function lana_demo_register_settings() {
	register_setting( 'lana-demo-settings-group', 'lana_demo_username' );
	register_setting( 'lana-demo-settings-group', 'lana_demo_password' );
	register_setting( 'lana-demo-settings-group', 'lana_demo_role_edit_posts' );
	register_setting( 'lana-demo-settings-group', 'lana_demo_role_edit_pages' );
	register_setting( 'lana-demo-settings-group', 'lana_demo_role_upload_files' );
	register_setting( 'lana-demo-settings-group', 'lana_demo_first_widget_title' );
	register_setting( 'lana-demo-settings-group', 'lana_demo_first_widget_content' );
	register_setting( 'lana-demo-settings-group', 'lana_demo_second_widget_title' );
	register_setting( 'lana-demo-settings-group', 'lana_demo_second_widget_content' );
}

/**
 * Lana Demo Settings page
 */
function lana_demo_settings() {
	?>
    <div class="wrap">
        <h2><?php _e( 'Lana Demo Settings', 'lana-demo' ); ?></h2>

        <form method="post" action="options.php">
			<?php settings_fields( 'lana-demo-settings-group' ); ?>

            <h2 class="title"><?php _e( 'User Settings', 'lana-demo' ); ?></h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="lana_demo_username">
							<?php _e( 'Username', 'lana-demo' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" name="lana_demo_username" id="lana_demo_username"
                               value="<?php echo esc_attr( get_option( 'lana_demo_username', '' ) ); ?>" <?php disabled( get_option( 'lana_demo_user_id', false ) ); ?>>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="lana_demo_password">
							<?php _e( 'Password', 'lana-demo' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" name="lana_demo_password" id="lana_demo_password"
                               value="<?php echo esc_attr( get_option( 'lana_demo_password', '' ) ); ?>">
                    </td>
                </tr>
            </table>

            <h2 class="title"><?php _e( 'Role Settings', 'lana-demo' ); ?></h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="lana_demo_role_edit_posts">
							<?php _e( 'Edit Posts', 'lana-demo' ); ?>
                        </label>
                    </th>
                    <td>
                        <select name="lana_demo_role_edit_posts" id="lana_demo_role_edit_posts">
                            <option value="0"
								<?php selected( get_option( 'lana_demo_role_edit_posts', '0' ), '0' ); ?>>
								<?php _e( 'Disabled', 'lana-demo' ); ?>
                            </option>
                            <option value="1"
								<?php selected( get_option( 'lana_demo_role_edit_posts', '0' ), '1' ); ?>>
								<?php _e( 'Enabled', 'lana-demo' ); ?>
                            </option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="lana_demo_role_edit_pages">
							<?php _e( 'Edit Pages', 'lana-demo' ); ?>
                        </label>
                    </th>
                    <td>
                        <select name="lana_demo_role_edit_pages" id="lana_demo_role_edit_pages">
                            <option value="0"
								<?php selected( get_option( 'lana_demo_role_edit_pages', '0' ), '0' ); ?>>
								<?php _e( 'Disabled', 'lana-demo' ); ?>
                            </option>
                            <option value="1"
								<?php selected( get_option( 'lana_demo_role_edit_pages', '0' ), '1' ); ?>>
								<?php _e( 'Enabled', 'lana-demo' ); ?>
                            </option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="lana_demo_role_upload_files">
							<?php _e( 'Upload Files', 'lana-demo' ); ?>
                        </label>
                    </th>
                    <td>
                        <select name="lana_demo_role_upload_files" id="lana_demo_role_upload_files">
                            <option value="0"
								<?php selected( get_option( 'lana_demo_role_upload_files', '0' ), '0' ); ?>>
								<?php _e( 'Disabled', 'lana-demo' ); ?>
                            </option>
                            <option value="1"
								<?php selected( get_option( 'lana_demo_role_upload_files', '0' ), '1' ); ?>>
								<?php _e( 'Enabled', 'lana-demo' ); ?>
                            </option>
                        </select>
                    </td>
                </tr>
            </table>

            <h2 class="title"><?php _e( 'Widget Settings', 'lana-demo' ); ?></h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="lana_demo_first_widget_title">
							<?php _e( 'First Widget Title', 'lana-demo' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" name="lana_demo_first_widget_title" id="lana_demo_first_widget_title"
                               value="<?php echo esc_attr( get_option( 'lana_demo_first_widget_title', '' ) ); ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="lana_demo_first_widget_content">
							<?php _e( 'First Widget Content', 'lana-demo' ); ?>
                        </label>
                    </th>
                    <td>
						<?php
						wp_editor( get_option( 'lana_demo_first_widget_content', '' ), 'lana_demo_first_widget_content', array(
							'textarea_name' => 'lana_demo_first_widget_content',
							'textarea_rows' => 10
						) );
						?>
                    </td>
                </tr>
            </table>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="lana_demo_second_widget_title">
							<?php _e( 'Second Widget Title', 'lana-demo' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" name="lana_demo_second_widget_title" id="lana_demo_second_widget_title"
                               value="<?php echo esc_attr( get_option( 'lana_demo_second_widget_title', '' ) ); ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="lana_demo_second_widget_content">
							<?php _e( 'First Widget Content', 'lana-demo' ); ?>
                        </label>
                    </th>
                    <td>
						<?php
						wp_editor( get_option( 'lana_demo_second_widget_content', '' ), 'lana_demo_second_widget_content', array(
							'textarea_name' => 'lana_demo_second_widget_content',
							'textarea_rows' => 10
						) );
						?>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" class="button-primary"
                       value="<?php esc_attr_e( 'Save Changes', 'lana-demo' ); ?>"/>
            </p>

        </form>
    </div>
	<?php
}

/**
 * Returns the translated role of the current user.
 * If that user has no role for the current blog, it returns false.
 * @return string The name of the current role
 **/
function lana_demo_get_current_user_role() {
	global $wp_roles;

	$current_user = wp_get_current_user();
	$roles        = $current_user->roles;
	$role         = array_shift( $roles );

	if ( isset( $wp_roles->role_names[ $role ] ) ) {
		return $role;
	}

	return false;
}

/**
 * Login styles
 */
function lana_demo_login_styles() {
	wp_register_style( 'lana-demo-login', plugin_dir_url( __FILE__ ) . '/assets/css/login.css', array(), LANA_DEMO_VERSION );
	wp_enqueue_style( 'lana-demo-login' );
}

add_action( 'login_enqueue_scripts', 'lana_demo_login_styles' );

/**
 * Login message
 * welcome message, username and password
 */
function lana_demo_login_message() {

	if ( ! get_option( 'lana_demo_user_id', false ) ) {
		return;
	}

	if ( ! get_option( 'lana_demo_username', false ) || empty( get_option( 'lana_demo_username' ) ) ) {
		return;
	}

	if ( ! get_option( 'lana_demo_password', false ) || empty( get_option( 'lana_demo_password' ) ) ) {
		return;
	}

	?>
    <div class="demo-login-message">
        <p>
            <strong>
				<?php echo sprintf( __( 'Welcome to %s demo.', 'lana-demo' ), get_bloginfo( 'name' ) ); ?>
            </strong>
        </p>

        <p>
			<?php echo __( 'Username', 'lana-demo' ); ?>: <?php echo get_option( 'lana_demo_username', '' ); ?>
            <br>
			<?php echo __( 'Password', 'lana-demo' ); ?>: <?php echo get_option( 'lana_demo_username', '' ); ?>
        </p>
    </div>
	<?php
}

add_filter( 'login_message', 'lana_demo_login_message' );

/**
 * Add a widget to the dashboard
 */
function lana_demo_add_dashboard_widgets() {

	if ( lana_demo_get_current_user_role() == 'lana_demo' ) {

		if ( get_option( 'lana_demo_first_widget_content', false ) ) {
			wp_add_dashboard_widget( 'lana_demo_first_widget', get_option( 'lana_demo_first_widget_title', __( 'Lana Demo Widget' ) ), 'lana_demo_first_widget_function' );
		}

		if ( get_option( 'lana_demo_second_widget_content', false ) ) {
			wp_add_dashboard_widget( 'lana_demo_second_widget', get_option( 'lana_demo_second_widget_title', __( 'Lana Demo Widget' ) ), 'lana_demo_second_widget_function' );
		}
	}
}

add_action( 'wp_dashboard_setup', 'lana_demo_add_dashboard_widgets' );

/**
 * Create the function to output the contents of our First Widget
 */
function lana_demo_first_widget_function() {
	echo wpautop( get_option( 'lana_demo_first_widget_content', '' ) );
}

/**
 * Create the function to output the contents of our Second Widget
 */
function lana_demo_second_widget_function() {
	echo wpautop( get_option( 'lana_demo_second_widget_content', '' ) );
}

/**
 * Remove dashboard elements in demo user
 */
function lana_demo_remove_dashboard_meta() {

	if ( lana_demo_get_current_user_role() == 'lana_demo' ) {

		remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );

		if ( get_option( 'lana_demo_first_widget_content', false ) ) {
			add_meta_box( 'lana_demo_first_widget', get_option( 'lana_demo_first_widget_title', __( 'Lana Demo Widget' ) ), 'lana_demo_first_widget_function', 'dashboard', 'normal', 'high' );
		}

		if ( get_option( 'lana_demo_second_widget_content', false ) ) {
			add_meta_box( 'lana_demo_second_widget', get_option( 'lana_demo_second_widget_title', __( 'Lana Demo Widget' ) ), 'lana_demo_second_widget_function', 'dashboard', 'side', 'high' );
		}
	}
}

add_action( 'admin_init', 'lana_demo_remove_dashboard_meta' );

/**
 * Remove menus in demo user
 */
function lana_demo_remove_menus() {

	if ( lana_demo_get_current_user_role() == 'lana_demo' ) {
		remove_menu_page( 'profile.php' );
		remove_menu_page( 'tools.php' );
		remove_menu_page( 'edit-comments.php' );
	}
}

add_action( 'admin_menu', 'lana_demo_remove_menus' );

/**
 * Disable user profile for demo user
 */
function lana_demo_disable_user_profile() {
	global $pagenow;

	if ( ( $pagenow == 'profile.php' || $pagenow == 'user-edit.php' ) && lana_demo_get_current_user_role() == 'lana_demo' ) {
		wp_redirect( admin_url() );
		exit;
	}
}

add_action( 'admin_init', 'lana_demo_disable_user_profile' );