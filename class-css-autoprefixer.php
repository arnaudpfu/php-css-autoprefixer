<?php
/**
 * Autoprefixer class
 */

require_once 'class-css-section.php';

if ( ! class_exists( 'CSS_Autoprefixer' ) ) {

	/**
	 * Enable to autoprefix CSS
	 */
	class CSS_Autoprefixer {

		/**
		 * Default unprefixed CSS properties.
		 *
		 * @var array
		 */
		private $full_support = array(
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

		/**
		 * CSS properties webkit prefixed.
		 *
		 * @var array
		 */
		private $webkit_support = array(
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

		/**
		 * CSS properties o prefixed.
		 *
		 * @var array
		 */
		private $o_support = array(
			'transition-duration',
			'transition-delay',
			'transition-timing-function',
		);

		/**
		 * CSS properties moz prefixed.
		 *
		 * @var array
		 */
		private $moz_support = array(
			'column-gap',
		);

		/**
		 * CSS properties ms prefixed.
		 *
		 * @var array
		 */
		private $ms_support = array(
			'transform-origin',
			'flex-direction',
		);

		/**
		 * Get all_support.
		 *
		 * @return array The all_support properties.
		 */
		public function get_all_support() {
			return $this->webkit_support;
		}

		/**
		 * Get webkit_support.
		 *
		 * @return array The webkit_support properties.
		 */
		public function get_webkit_support() {
			return $this->webkit_support;
		}

		/**
		 * Get o_support.
		 *
		 * @return array The o_support properties.
		 */
		public function get_o_support() {
			return $this->webkit_support;
		}

		/**
		 * Get moz_support.
		 *
		 * @return array The moz_support properties.
		 */
		public function get_moz_support() {
			return $this->webkit_support;
		}

		/**
		 * Get ms_support.
		 *
		 * @return array The ms_support properties.
		 */
		public function get_ms_support() {
			return $this->webkit_support;
		}

		/**
		 * Compile CSS
		 *
		 * @param string $unprefixed_css unprefixed CSS code.
		 * @return string minified and prefixed CSS
		 */
		public function compile( $unprefixed_css ) {

			$prefixed_css = '';

			$parsed_css   = $this->parse( $unprefixed_css );
			$parsed_css   = $this->add_prefix( $parsed_css );
			$prefixed_css = $this->gather_css( $parsed_css );

			return $prefixed_css;
		}

		/**
		 * Add css properties to the webkit_support array
		 *
		 * @param array $properties An array of CSS properties in kebab case.
		 * @return void
		 */
		public function add_webkit_support( $properties ) {
			$this->webkit_support = $this->array_push_all_if_not_inside( $this->webkit_support, $properties );
		}

		/**
		 * Add css properties to the o_support array
		 *
		 * @param array $properties An array of CSS properties in kebab case.
		 * @return void
		 */
		public function add_o_support( $properties ) {
			$this->o_support = $this->array_push_all_if_not_inside( $this->o_support, $properties );
		}

		/**
		 * Add css properties to the moz_support array
		 *
		 * @param array $properties An array of CSS properties in kebab case.
		 * @return void
		 */
		public function add_moz_support( $properties ) {
			$this->moz_support = $this->array_push_all_if_not_inside( $this->moz_support, $properties );
		}

		/**
		 * Add css properties to the ms_support array
		 *
		 * @param array $properties An array of CSS properties in kebab case.
		 * @return void
		 */
		public function add_ms_support( $properties ) {
			$this->ms_support = $this->array_push_all_if_not_inside( $this->ms_support, $properties );
		}

		/**
		 * Gather CSS
		 *
		 * @param array $parsed_css array of CSS_Section.
		 * @return string minified and prefixed CSS
		 */
		private function gather_css( $parsed_css ) {

			$gathered_css = '';

			foreach ( $parsed_css as $css_section ) {

				$gathered_css .= $this->gather_css_section( $css_section );
			}

			return $gathered_css;
		}

		/**
		 * Gather CSS section
		 *
		 * @param CSS_Section $css_section prefixed CSS.
		 * @return string minified and prefixed CSS
		 */
		private function gather_css_section( $css_section ) {

			$gathered_section = null === $css_section->selector ? '' : $css_section->selector . '{';

			if ( 'animation' === $css_section->type ) {
				$css_keyframes = $css_section->content;

				foreach ( $css_keyframes as $css_keyframe ) {
					$gathered_section .= $css_keyframe['position'] . '{';
					$gathered_section .= $this->gather_css_properties( $css_keyframe['properties'] );
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
				$gathered_section .= $this->gather_css_properties( $css_section->content );
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
		private function gather_css_properties( $css_properties ) {
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
		private function add_prefix( $css_sections ) {

			$prefixed_css = array();

			foreach ( $css_sections as $css_section ) {
				if ( 'animation' === $css_section->type ) {
					$prefixed_keyframes = array();
					$css_keyframes      = $css_section->content;

					foreach ( $css_keyframes as $css_keyframe ) {
						$css_keyframe['properties'] = $this->prefix_properties( $css_keyframe['properties'] );
						array_push( $prefixed_keyframes, $css_keyframe );
					}
					$css_section->content           = $prefixed_keyframes;
					$css_section->position_prefixes = array( '-webkit-', '' );
				} else {
					$css_section->content = $this->prefix_properties( $css_section->content );
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
		private function prefix_properties( $properties ) {

			$prefixed_properties = array();

			foreach ( $properties as $property ) {
				$prefixed_property = $this->prefix_property( $property );
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
		private function prefix_property( $property ) {

			$prefixed_property = array();

			$prefixed_property['prefixed'] = array();

			array_push( $prefixed_property['prefixed'], $property );

			if ( ! in_array( $property['property'], $this->full_support, true ) ) {
				// webkit.
				$prefixed_property['prefixed'] = $this->check_browser_support( $this->webkit_support, $prefixed_property['prefixed'], '-webkit-' );
				// opera.
				$prefixed_property['prefixed'] = $this->check_browser_support( $this->o_support, $prefixed_property['prefixed'], '-o-' );
				// moz.
				$prefixed_property['prefixed'] = $this->check_browser_support( $this->moz_support, $prefixed_property['prefixed'], '-moz-' );
				// ms.
				$prefixed_property['prefixed'] = $this->check_browser_support( $this->ms_support, $prefixed_property['prefixed'], '-ms-' );

				$prefixed_property['prefixed'] = $this->check_specific_support( $prefixed_property['prefixed'] );
			}

			return $prefixed_property;
		}

		/**
		 * Add more specific prefix
		 *
		 * @param array $prefixed_css_properties already added prefixed properties.
		 * @return array the new $prefixed_css_properties
		 */
		private function check_specific_support( $prefixed_css_properties ) {

			$native_property = $this->get_native_property( $prefixed_css_properties );

			switch ( $native_property['property'] ) {
				case 'align-items':
					array_push(
						$prefixed_css_properties,
						array(
							'property' => '-webkit-box-align',
							'value'    => $this->get_flex_prefixed_value( $native_property['value'] ),
						)
					);
					array_push(
						$prefixed_css_properties,
						array(
							'property' => '-ms-flex-align',
							'value'    => $this->get_flex_prefixed_value( $native_property['value'] ),
						)
					);
					break;

				case 'justify-content':
					array_push(
						$prefixed_css_properties,
						array(
							'property' => '-webkit-box-pack',
							'value'    => $this->get_flex_prefixed_value( $native_property['value'] ),
						)
					);
					array_push(
						$prefixed_css_properties,
						array(
							'property' => '-ms-flex-pack',
							'value'    => $this->get_flex_prefixed_value( $native_property['value'] ),
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
							'value'    => $this->get_flex_prefixed_value( $native_property['value'] ),
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
		private function check_browser_support( $linked_properties, $prefixed_css_properties, $prefix ) {

			$native_property = $this->get_native_property( $prefixed_css_properties );

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
		private function get_native_property( $prefixed_css_properties ) {
			return $prefixed_css_properties[0];
		}

		/**
		 * Get prefixed value of a flex value
		 *
		 * @param string $value css.
		 * @return string
		 */
		private function get_flex_prefixed_value( $value ) {

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
		private function parse( $unprefixed_css ) {

			$parsed_css = array();

			if ( $this->has_selector( $unprefixed_css ) ) {

				$css_text_sections = $this->split_into_css_text_section( $unprefixed_css );

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
		private function has_selector( $css ) {
			return strpos( $css, '}' ) !== false || strpos( $css, '@keyframes ' ) !== false;
		}

		/**
		 * Split CSS code according to the style selectors and animation delcarations
		 *
		 * @param string $css code.
		 * @return array
		 */
		private function split_into_css_text_section( $css ) {
			$css = preg_replace( '/(?<=\}|\{|,|\n|\r)\s*from\s*(?=\{|,)/', '0%', $css );
			$css = preg_replace( '/(?<=\}|\{|,|\n|\r)\s*to\s*(?=\{|,)/', '100%', $css );
			return preg_split( '/(?<=\})(?=[a-zA-Z|#|\s|\.|@]+)/', $css );
		}

		/**
		 * Push all elements of an array inside another one if elements aren't inside.
		 *
		 * @param array $targeted_array The input array.
		 * @param array $pushed_array The pushed array.
		 * @return array The input array with its new value.
		 */
		private function array_push_all_if_not_inside( $targeted_array, $pushed_array ) {
			foreach ( $pushed_array as $pushed_value ) {
				if ( ! in_array( $pushed_value, $targeted_array, true ) ) {
					array_push( $targeted_array, $pushed_value );
				}
			}
			return $targeted_array;
		}
	}
}
