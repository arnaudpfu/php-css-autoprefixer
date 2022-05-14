<?php
/**
 * Autoprefixer class
 */

require_once 'class-css-section.php';

if ( ! class_exists( 'CSS_Autoprefixer' ) ) {

	/**
	 * Enable to autoprefix CSS
	 *
	 */
	class CSS_Autoprefixer {

		const FULL_SUPPORT = array(
			'row-gap',
			'background-color',
			'width',
			'height',
			'min-width',
			'min-height',
			'max-width',
			'max-height',
			'overflow',
			'opacity',
			'position',
			'top',
			'bottom',
			'right',
			'left',
			'border-width',
			'border-radius',
			'border-top-style',
			'border-bottom-style',
			'border-left-style',
			'border-right-style',
			'border-color',
			'z-index',
			'border-top-left-radius',
			'border-top-right-radius',
			'border-bottom-right-radius',
			'border-bottom-left-radius',
			'fill',
			'stroke',
			'stroke-width',
			'stroke-dasharray',
			'stroke-dashoffset',
		);

		const WEBKIT_SUPPORT = array(
			'transition-duration',
			'transition-delay',
			'transition-timing-function',
			'animation-duration',
			'animation-timing-function',
			'column-gap',
			'transform',
			'box-shadow',
			'clip-path',
			'perspective',
			'transform-origin',
		);

		const O_SUPPORT = array(
			'transition-duration',
			'transition-delay',
			'transition-timing-function',
		);

		const MOZ_SUPPORT = array(
			'column-gap',
		);

		const MS_SUPPORT = array(
			'transform-origin',
			'flex-direction',
		);

		/**
		 * Compile CSS
		 *
		 * @param string $unprefixed_css unprefixed CSS code.
		 * @return string minified and prefixed CSS
		 */
		public static function compile( $unprefixed_css ) {

			$prefixed_css = '';

			$parsed_css   = self::parse( $unprefixed_css );
			$parsed_css   = self::add_prefix( $parsed_css );
			$prefixed_css = self::gather_css( $parsed_css );

			return $prefixed_css;

		}

		/**
		 * Gather CSS
		 *
		 * @param array $parsed_css array of CSS_Section.
		 * @return string minified and prefixed CSS
		 */
		public static function gather_css( $parsed_css ) {

			$gathered_css = '';

			foreach ( $parsed_css as $css_section ) {

				$gathered_css .= self::gather_css_section( $css_section );
			}

			return $gathered_css;
		}

		/**
		 * Gather CSS section
		 *
		 * @param CSS_Section $css_section prefixed CSS.
		 * @return string minified and prefixed CSS
		 */
		public static function gather_css_section( $css_section ) {

			$gathered_section = null === $css_section->selector ? '' : $css_section->selector . '{';

			if ( 'animation' === $css_section->type ) {
				$css_keyframes = $css_section->content;

				foreach ( $css_keyframes as $css_keyframe ) {
					$gathered_section .= $css_keyframe['position'] . '{';
					$gathered_section .= self::gather_css_properties( $css_keyframe['properties'] );
					$gathered_section .= '}';
				}
				$gathered_section .= '}';

				$gathered_animation = $gathered_section;
				$gathered_section   = '';
				foreach ( $css_section->position_prefixes as $position_prefix ) {
					if ( '' === $position_prefix ) {
						$gathered_section .= $gathered_animation;
					} else {
						$gathered_section .= '@' . $position_prefix . substr( $gathered_animation, strpos( $gathered_animation, '@' ) + 1 );
					}
				}
			} else {
				$gathered_section .= self::gather_css_properties( $css_section->content );
				$gathered_section .= null === $css_section->selector ? '' : '}';
			}

			return $gathered_section;
		}

		/**
		 * Gather CSS properties
		 *
		 * @param array $css_properties prefixed CSS.
		 * @return string minified and prefixed CSS
		 */
		public static function gather_css_properties( $css_properties ) {
			$gathered_properties = '';

			foreach ( $css_properties as $prefixed_properties ) {
				foreach ( $prefixed_properties['prefixed'] as $prefixed_property ) {
					$gathered_properties .= $prefixed_property['property'] . ':' . $prefixed_property['value'] . ';';
				}
			}

			return $gathered_properties;
		}

		/**
		 * Prefix CSS
		 *
		 * @param array $css_sections array of CSS_Section.
		 * @return array prefixed CSS_Section
		 */
		public static function add_prefix( $css_sections ) {

			$prefixed_css = array();

			foreach ( $css_sections as $css_section ) {
				if ( 'animation' === $css_section->type ) {
					$prefixed_keyframes = array();
					$css_keyframes      = $css_section->content;

					foreach ( $css_keyframes as $css_keyframe ) {
						$css_keyframe['properties'] = self::prefix_properties( $css_keyframe['properties'] );
						array_push( $prefixed_keyframes, $css_keyframe );
					}
					$css_section->content           = $prefixed_keyframes;
					$css_section->position_prefixes = array( '-webkit-', '' );
				} else {
					$css_section->content = self::prefix_properties( $css_section->content );
				}
				array_push( $prefixed_css, $css_section );
			}

			return $prefixed_css;
		}

		/**
		 * Prefix CSS properties
		 *
		 * @param array $properties parsed CSS properties.
		 * @return array prefixed CSS properties
		 */
		public static function prefix_properties( $properties ) {

			$prefixed_properties = array();

			foreach ( $properties as $property ) {
				$prefixed_property = self::prefix_property( $property );
				array_push( $prefixed_properties, $prefixed_property );
			}

			return $prefixed_properties;
		}

		/**
		 * Prefix a CSS property
		 *
		 * @param array $property parsed CSS property.
		 * @return array prefixed CSS property
		 */
		public static function prefix_property( $property ) {

			$prefixed_property = array();

			$prefixed_property['prefixed'] = array();

			array_push( $prefixed_property['prefixed'], $property );

			if ( ! in_array( $property['property'], self::FULL_SUPPORT, true ) ) {
				// webkit.
				$prefixed_property['prefixed'] = self::check_browser_support( self::WEBKIT_SUPPORT, $prefixed_property['prefixed'], '-webkit-' );
				// opera.
				$prefixed_property['prefixed'] = self::check_browser_support( self::O_SUPPORT, $prefixed_property['prefixed'], '-o-' );
				// moz.
				$prefixed_property['prefixed'] = self::check_browser_support( self::MOZ_SUPPORT, $prefixed_property['prefixed'], '-moz-' );
				// ms.
				$prefixed_property['prefixed'] = self::check_browser_support( self::MS_SUPPORT, $prefixed_property['prefixed'], '-ms-' );

				$prefixed_property['prefixed'] = self::check_specific_support( $prefixed_property['prefixed'] );
			}

			return $prefixed_property;
		}

		/**
		 * Add more specific prefix
		 *
		 * @param array $prefixed_css_properties already added prefixed properties.
		 * @return array the new $prefixed_css_properties
		 */
		public static function check_specific_support( $prefixed_css_properties ) {

			$native_property = self::get_native_property( $prefixed_css_properties );

			switch ( $native_property['property'] ) {
				case 'align-items':
					array_push(
						$prefixed_css_properties,
						array(
							'property' => '-webkit-box-align',
							'value'    => self::get_flex_prefixed_value( $native_property['value'] ),
						)
					);
					array_push(
						$prefixed_css_properties,
						array(
							'property' => '-ms-flex-align',
							'value'    => self::get_flex_prefixed_value( $native_property['value'] ),
						)
					);
					break;

				case 'justify-content':
					array_push(
						$prefixed_css_properties,
						array(
							'property' => '-webkit-box-pack',
							'value'    => self::get_flex_prefixed_value( $native_property['value'] ),
						)
					);
					array_push(
						$prefixed_css_properties,
						array(
							'property' => '-ms-flex-pack',
							'value'    => self::get_flex_prefixed_value( $native_property['value'] ),
						)
					);
					break;

				case 'background-image':
					if ( strpos( $native_property['value'], 'linear-gradient' ) !== false ) {
						array_push(
							$prefixed_css_properties,
							array(
								'property' => $native_property['property'],
								'value'    => '-o-' . $native_property['value'],
							)
						);
					}
					break;

				case 'flex-direction':
					array_push(
						$prefixed_css_properties,
						array(
							'property' => '-webkit-box-orient',
							'value'    => self::get_flex_prefixed_value( $native_property['value'] ),
						)
					);
					array_push(
						$prefixed_css_properties,
						array(
							'property' => '-webkit-box-direction',
							'value'    => 'normal',
						)
					);
					break;
			}

			return $prefixed_css_properties;
		}

		/**
		 * Check if an accurate property need prefix and prefix it
		 *
		 * @param array  $linked_properties list of property using this prefix.
		 * @param array  $prefixed_css_properties already added prefixed properties.
		 * @param string $prefix added.
		 * @return array the new $prefixed_css_properties
		 */
		public static function check_browser_support( $linked_properties, $prefixed_css_properties, $prefix ) {

			$native_property = self::get_native_property( $prefixed_css_properties );

			if ( in_array( $native_property['property'], $linked_properties, true ) ) {
				$prefixed_property = array(
					'property' => $prefix . $native_property['property'],
					'value'    => $native_property['value'],
				);
				array_push( $prefixed_css_properties, $prefixed_property );
			}

			return $prefixed_css_properties;
		}

		/**
		 * Get the native property of a list of prefixed properties
		 *
		 * It is the first element of the array because it was the first one which was added
		 *
		 * @param array $prefixed_css_properties all the prefixed properties.
		 * @return array
		 */
		public static function get_native_property( $prefixed_css_properties ) {
			return $prefixed_css_properties[0];
		}

		/**
		 * Get prefixed value of a flex value
		 *
		 * @param string $value css.
		 * @return string
		 */
		public static function get_flex_prefixed_value( $value ) {

			switch ( $value ) {
				case 'flex-start':
					$value = 'start';
					break;
				case 'flex-end':
					$value = 'end';
					break;
				case 'row':
					$value = 'horizontal';
					break;
				case 'column':
					$value = 'vertical';
					break;
			}

			return $value;
		}

		/**
		 * Parse CSS in a sorted array
		 *
		 * @param string $unprefixed_css unprefixed and unminified CSS.
		 * @return array
		 */
		public static function parse( $unprefixed_css ) {

			$parsed_css = array();

			if ( self::has_selector( $unprefixed_css ) ) {

				$css_text_sections = self::split_into_css_text_section( $unprefixed_css );

				foreach ( $css_text_sections as $css_text_section ) {

					array_push( $parsed_css, new CSS_Section( $css_text_section ) );

				}
			} else {
				$parsed_css = array( new CSS_Section( $unprefixed_css ) );
			}

			return $parsed_css;
		}

		/**
		 * Tell if CSS has selectors or animation declarations
		 *
		 * @param string $css CSS code.
		 * @return boolean true if CSS has at least one selector
		 */
		public static function has_selector( $css ) {
			return strpos( $css, '}' ) !== false || strpos( $css, '@keyframes ' ) !== false;
		}

		/**
		 * Split CSS code according to the style selectors and animation delcarations
		 *
		 * @param string $css code.
		 * @return array
		 */
		public static function split_into_css_text_section( $css ) {
			$css = preg_replace( '/(?<=\}|\{|,|\n|\r)\s*from\s*(?=\{|,)/', '0%', $css );
			$css = preg_replace( '/(?<=\}|\{|,|\n|\r)\s*to\s*(?=\{|,)/', '100%', $css );
			return preg_split( '/(?<=\})(?=[a-zA-Z|#|\s|\.|@]+)/', $css );
		}
	}
}
