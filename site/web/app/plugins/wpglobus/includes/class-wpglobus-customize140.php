<?php
/**
 * Multilingual Customizer
 * @package    WPGlobus
 * @subpackage WPGlobus/Admin
 * @since      1.4.0
 */

if ( ! class_exists( 'WPGlobus_Customize' ) ) :

	/**
	 * Class WPGlobus_Customize
	 */
	class WPGlobus_Customize {

		public static function controller() {
			/**
			 * @see \WP_Customize_Manager::wp_loaded
			 * It calls the `customize_register` action first,
			 * and then - the `customize_preview_init` action
			 */
			/* 
			add_action( 'customize_register', array(
				'WPGlobus_Customize',
				'action__customize_register'
			) ); */
			
			/**
			 * @since 1.5.0
			 */
			if ( WPGlobus_WP::is_pagenow( 'customize.php' ) ) {
				require_once 'admin/wpglobus-customize-filters.php';
			}
			
			add_action( 'customize_preview_init', array(
				'WPGlobus_Customize',
				'action__customize_preview_init'
			) );

			/**
			 * This is called by wp-admin/customize.php
			 */
			add_action( 'customize_controls_enqueue_scripts', array(
				'WPGlobus_Customize',
				'action__customize_controls_enqueue_scripts'
			), 1000 );
			
			if ( WPGlobus_WP::is_admin_doing_ajax() ) {
				add_filter( 'clean_url', array(
					'WPGlobus_Customize',
					'filter__clean_url'
				), 10, 2 );
			}
			
		}

		/**
		 * Filter a string to check translations for URL.
		 * // We build multilingual URLs in customizer using the ':::' delimiter.
		 * We build multilingual URLs in customizer using the '|||' delimiter.
		 * See wpglobus-customize-control.js
		 *
		 * @note  To work correctly, value of $url should begin with URL for default language.
		 * @see   esc_url() - the 'clean_url' filter
		 * @since 1.3.0
		 *
		 * @param string $url          The cleaned URL.
		 * @param string $original_url The URL prior to cleaning.
		 *
		 * @return string
		 */
		public static function filter__clean_url( $url, $original_url ) {

			if ( false !== strpos( $original_url, '|||' ) ) {
				$arr1 = array();
				$arr  = explode( '|||', $original_url );
				foreach ( $arr as $k => $val ) {
					// Note: 'null' is a string, not real `null`.
					if ( 'null' !== $val ) {
						$arr1[ WPGlobus::Config()->enabled_languages[ $k ] ] = $val;
					}
				}
				return WPGlobus_Utils::build_multilingual_string( $arr1 );
			}

			return $url;
		}
		
		/**
		 * Add multilingual controls.
		 * The original controls will be hidden.
		 * @param WP_Customize_Manager $wp_customize
		 */
		public static function action__customize_register( WP_Customize_Manager $wp_customize ) {}
		
		/**
		 * Load Customize Preview JS
		 * Used by hook: 'customize_preview_init'
		 * @see 'customize_preview_init'
		 */
		public static function action__customize_preview_init() {
			wp_enqueue_script(
				'wpglobus-customize-preview',
				WPGlobus::$PLUGIN_DIR_URL . 'includes/js/wpglobus-customize-preview' .
				WPGlobus::SCRIPT_SUFFIX() . '.js',
				array( 'jquery', 'customize-preview' ),
				WPGLOBUS_VERSION,
				true
			);
			wp_localize_script(
				'wpglobus-customize-preview',
				'WPGlobusCustomize',
				array(
					'version'         => WPGLOBUS_VERSION,
					'blogname'        => WPGlobus_Core::text_filter( get_option( 'blogname' ), WPGlobus::Config()->language ),
					'blogdescription' => WPGlobus_Core::text_filter( get_option( 'blogdescription' ), WPGlobus::Config()->language )
				)
			);
		}

		/**
		 * Load Customize Control JS
		 */
		public static function action__customize_controls_enqueue_scripts() {
			
			/** 
			 * @see wp.customize.control elements
			 * for example wp.customize.control('blogname');
			 */
			$disabled_setting_mask = array();
			
			/** navigation menu elements */
			$disabled_setting_mask[] = 'nav_menu_item';
			$disabled_setting_mask[] = 'nav_menu[';
			$disabled_setting_mask[] = 'nav_menu_locations';
			$disabled_setting_mask[] = 'new_menu_name';
			
			/** widgets */
			$disabled_setting_mask[] = 'widgets';

			/** color elements */
			$disabled_setting_mask[] = 'color';

			/** yoast seo */
			$disabled_setting_mask[] = 'wpseo';

			/** css elements */
			$disabled_setting_mask[] = 'css';

			/** social networks elements */
			$disabled_setting_mask[] = 'facebook';
			$disabled_setting_mask[] = 'twitter';
			$disabled_setting_mask[] = 'linkedin';
			$disabled_setting_mask[] = 'behance';
			$disabled_setting_mask[] = 'dribbble';
			$disabled_setting_mask[] = 'instagram';
			/** since 1.4.4 */
			$disabled_setting_mask[] = 'tumblr';
			$disabled_setting_mask[] = 'flickr';
			$disabled_setting_mask[] = 'wordpress';
			$disabled_setting_mask[] = 'youtube';
			$disabled_setting_mask[] = 'pinterest';
			$disabled_setting_mask[] = 'github';
			$disabled_setting_mask[] = 'rss';
			$disabled_setting_mask[] = 'google';
			$disabled_setting_mask[] = 'email';
			
			/**
			 * Filter to disable fields in customizer. 
			 * @see wp.customize.control elements
			 * Returning array.
			 * @since 1.4.0
			 *
			 * @param array $disabled_setting_mask An array of disabled masks.
			 */			
			$disabled_setting_mask = apply_filters( 'wpglobus_customize_disabled_setting_mask', $disabled_setting_mask );
			
			$element_selector = array( 'input[type=text]', 'textarea' );
			
			/**
			 * Filter for element selectors. 
			 * Returning array.
			 * @since 1.4.0
			 *
			 * @param array $element_selector An array of selectors.
			 */			
			$element_selector = apply_filters( 'wpglobus_customize_element_selector', $element_selector );
			
			$set_link_by = array( 'link', 'url' );
			
			/**
			 * Filter of masks to determine links.
			 * @see value data-customize-setting-link of element			 
			 * Returning array.
			 * @since 1.4.0
			 *
			 * @param array $set_link_by An array of masks.
			 */				
			$set_link_by = apply_filters( 'wpglobus_customize_setlinkby', $set_link_by );

			/**
			 * Filter of disabled sections.
			 *
			 * Returning array.
			 * @since 1.5.0
			 *
			 * @param array $disabled_sections An array of sections.
			 */					
			$disabled_sections = array();
			
			$disabled_sections = apply_filters( 'wpglobus_customize_disabled_sections', $disabled_sections );
			
			wp_enqueue_script(
				'wpglobus-customize-control140',
				WPGlobus::$PLUGIN_DIR_URL . 'includes/js/wpglobus-customize-control140' . WPGlobus::SCRIPT_SUFFIX() . '.js',
				array( 'jquery' ),
				WPGLOBUS_VERSION,
				true
			);
			wp_localize_script(
				'wpglobus-customize-control140',
				'WPGlobusCustomize',
				array(
					'version' => WPGLOBUS_VERSION,
					'languageAdmin'			=> WPGlobus::Config()->language,
					'disabledSettingMask' 	=> $disabled_setting_mask,
					'elementSelector'		=> $element_selector,
					'setLinkBy'				=> $set_link_by,
					'disabledSections'		=> $disabled_sections
				)
			);
			
		}

	} // class

endif;
# --- EOF
