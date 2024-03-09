<?php
/**
 * Main class.
 *
 * @package VHC_WP_Show_Ids
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Main VHC_WP_Show_Ids Class.
 *
 * @class VHC_WP_Show_Ids
 */
class VHC_WP_Show_Ids {

	/**
	 * Admin notices to add.
	 *
	 * @since 1.0.0
	 * @var array Array of admin notices.
	 */
	private $notices = array();

	/**
	 * VHC_WP_Show_Ids Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		register_shutdown_function( array( $this, 'log_errors' ) );

		$this->define_constants();
		$this->init_hooks();
	}

	/**
	 * Cloning instances is forbidden due to singleton pattern.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, sprintf( 'You cannot clone instances of %s.', esc_html( get_class( $this ) ) ), '1.0.0' );
	}

	/**
	 * Unserializing instances is forbidden due to singleton pattern.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, sprintf( 'You cannot unserialize instances of %s.', esc_html( get_class( $this ) ) ), '1.0.0' );
	}

	/**
	 * Ensures fatal errors are logged so they can be picked up in the status report.
	 *
	 * @since 1.0.0
	 */
	public function log_errors() {
		$error = error_get_last();
		if ( $error && in_array( $error['type'], array( E_ERROR, E_PARSE, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR ), true ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
				/* translators: 1: Error Message 2: File Name and Path 3: Line Number */
				$error_message = sprintf( __( '%1$s in %2$s on line %3$s', 'vhc-wp-show-ids' ), $error['message'], $error['file'], $error['line'] ) . PHP_EOL;
				// phpcs:disable WordPress.PHP.DevelopmentFunctions
				error_log( $error_message );
				// phpcs:enable WordPress.PHP.DevelopmentFunctions
			}
		}
	}

	/**
	 * Define WC Constants.
	 *
	 * @since 1.0.0
	 */
	private function define_constants() {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_data = get_plugin_data( VHC_WP_SHOW_IDS_PLUGIN_FILE );

		$this->define( 'VHC_WP_SHOW_IDS_ABSPATH', dirname( VHC_WP_SHOW_IDS_PLUGIN_FILE ) . '/' );
		$this->define( 'VHC_WP_SHOW_IDS_PLUGIN_BASENAME', plugin_basename( VHC_WP_SHOW_IDS_PLUGIN_FILE ) );
		$this->define( 'VHC_WP_SHOW_IDS_PLUGIN_NAME', $plugin_data['Name'] );
		$this->define( 'VHC_WP_SHOW_IDS_VERSION', $plugin_data['Version'] );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @since 1.0.0
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/vhc-wp-show-ids/vhc-wp-show-ids-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/vhc-wp-show-ids-LOCALE.mo
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		if ( function_exists( 'determine_locale' ) ) {
			$locale = determine_locale();
		} else {
			$locale = is_admin() ? get_user_locale() : get_locale();
		}

		$locale = apply_filters( 'plugin_locale', $locale, 'vhc-wp-show-ids' );

		unload_textdomain( 'vhc-wp-show-ids' );
		load_textdomain( 'vhc-wp-show-ids', WP_LANG_DIR . '/vhc-wp-show-ids/vhc-wp-show-ids-' . $locale . '.mo' );
		load_plugin_textdomain( 'vhc-wp-show-ids', false, plugin_basename( dirname( VHC_WP_SHOW_IDS_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 */
	public function init_hooks() {
		// Set up localisation.
		$this->load_plugin_textdomain();

		if ( apply_filters( 'vhc_show_ids_enable_copy', true ) === true ) {
			// Enqueue scripts and styles.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		}

		// Hook the action with admin init.
		add_action( 'admin_init', array( $this, 'add_ids_to_row_actions' ) );
	}

	/**
	 * Get the plugin url.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', VHC_WP_SHOW_IDS_PLUGIN_FILE ) );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Register admin styles.
		wp_enqueue_style( 'vhc-wp-show-ids-admin', $this->plugin_url() . '/assets/css/admin' . $suffix . '.css', array(), VHC_WP_SHOW_IDS_VERSION );

		// Register scripts.
		wp_enqueue_script( 'vhc-wp-show-ids-admin', $this->plugin_url() . '/assets/js/admin' . $suffix . '.js', array( 'clipboard', 'jquery' ), VHC_WP_SHOW_IDS_VERSION, true );
	}

	/**
	 * Add IDs to row actions.
	 *
	 * @since 1.0.0
	 */
	public function add_ids_to_row_actions() {
		// Show ids in posts.
		if ( apply_filters( 'vhc_show_ids_enable_for_posts', true ) === true ) {
			add_filter( 'post_row_actions', array( __CLASS__, 'show_post_id' ), 99, 2 );
		}

		// Show ids in pages.
		if ( apply_filters( 'vhc_show_ids_enable_for_pages', true ) === true ) {
			add_filter( 'page_row_actions', array( __CLASS__, 'show_post_id' ), 99, 2 );
		}

		// Show ids in media.
		if ( apply_filters( 'vhc_show_ids_enable_for_medias', true ) === true ) {
			add_filter( 'media_row_actions', array( __CLASS__, 'show_media_id' ), 99, 2 );
		}

		// Show ids in tags.
		if ( apply_filters( 'vhc_show_ids_enable_for_terms', true ) === true ) {
			add_filter( 'tag_row_actions', array( __CLASS__, 'show_term_id' ), 99, 2 );
		}

		// Show ids in comments.
		if ( apply_filters( 'vhc_show_ids_enable_for_comments', true ) === true ) {
			add_filter( 'comment_row_actions', array( __CLASS__, 'show_comment_id' ), 99, 2 );
		}

		// Show ids in users.
		if ( apply_filters( 'vhc_show_ids_enable_for_users', true ) === true ) {
			add_filter( 'user_row_actions', array( __CLASS__, 'show_user_id' ), 99, 2 );
		}
	}

	/**
	 * Display ID is posts, and custom post types.
	 *
	 * @since 1.0.0
	 * @param array   $actions  Row actions.
	 * @param WP_Post $post     Post object.
	 * @return array            Modified row actions.
	 */
	public static function show_post_id( $actions, $post ) {
		if ( apply_filters( 'vhc_show_ids_enable_for_post_' . $post->post_type, true ) === true ) {
			return self::prepend_to_row_actions( $actions, $post->ID );
		}

		return $actions;
	}

	/**
	 * Display ID is media lists.
	 *
	 * @since 1.0.0
	 * @param array   $actions  Row actions.
	 * @param WP_Post $media    Media post object.
	 * @return array            Modified row actions.
	 */
	public static function show_media_id( $actions, $media ) {
		return self::prepend_to_row_actions( $actions, $media->ID );
	}

	/**
	 * Display ID is categories, tags and custom taxonomies term.
	 *
	 * @param array   $actions  Row actions.
	 * @param WP_Term $term     Term object.
	 * @return array            Modified row actions.
	 */
	public static function show_term_id( $actions, $term ) {
		if ( apply_filters( 'vhc_show_ids_enable_for_taxonomy_' . $term->taxonomy, true ) === true ) {
			return self::prepend_to_row_actions( $actions, $term->term_id );
		}

		return $actions;
	}

	/**
	 * Display ID is comments row actions.
	 *
	 * @since 1.0.0
	 * @param array      $actions  Row actions.
	 * @param WP_Comment $comment  Comment object.
	 * @return array               Modified row actions.
	 */
	public static function show_comment_id( $actions, $comment ) {
		return self::prepend_to_row_actions( $actions, $comment->comment_ID );
	}

	/**
	 * Display ID is users row actions.
	 *
	 * @since 1.0.0
	 * @param array   $actions  Row actions.
	 * @param WP_User $user     User object.
	 * @return array            Modified row actions.
	 */
	public static function show_user_id( $actions, $user ) {
		return self::prepend_to_row_actions( $actions, $user->ID );
	}

	/**
	 * Prepend ID in row actions.
	 *
	 * @since 1.0.0
	 * @param array $actions    Row actions.
	 * @param int   $id         Post ID | Term ID | User ID | Comment ID.
	 * @return array            Modified row actions.
	 */
	private static function prepend_to_row_actions( $actions, $id ) {
		// Check if actions is empty.
		if ( ! empty( $actions ) ) {
			// Check if id key already exists.
			// Some plugins add id in row actions by default so we override it.
			if ( isset( $actions['id'] ) ) {
				unset( $actions['id'] );
			}

			// Check if id key doesn't exists in action anymore.
			if ( ! isset( $actions['id'] ) ) {
				// Prepare display text.
				$id_text = sprintf(
					/* translators: %s Object ID */
					__( 'ID: %s', 'vhc-wp-show-ids' ),
					esc_html( $id )
				);

				$classes   = array( 'vhc-column-id' );
				$classes[] = apply_filters( 'vhc_show_ids_enable_copy', true ) ? 'vhc-has-copy' : '';
				$classes   = array_filter( $classes );

				// Prepare the action array.
				$id_action = array(
					'id' => sprintf(
						/* translators: 1: Object ID. 2: Object ID with label */
						'<span class="' . esc_attr( join( ' ', $classes ) ) . '" data-clipboard-text="%1$s" aria-label="' . esc_attr__( 'Click to copy', 'vhc-wp-show-ids' ) . '" data-success-text="' . esc_attr__( 'Copied!', 'vhc-wp-show-ids' ) . '">%2$s</span>',
						esc_attr( $id ),
						esc_html( $id_text )
					),
				);

				// Merge the $actions with $id_action so that ID will be at first.
				$actions = array_merge( $id_action, $actions );
			}
		}

		return $actions;
	}
}
