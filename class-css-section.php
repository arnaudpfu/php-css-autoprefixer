<?php
/**
 * Autoprefixer class
 *
 * Enable to create sorted CSS section.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'CSS_Section' ) ) {

	/**
	 * Enable to autoprefix CSS
	 *
	 * @version 5.8.1
	 */
	class CSS_Section {

		/**
		 * Type of the section: 'style' | 'animation'
		 *
		 * @var string
		 */
		public $type;

		/**
		 * Selector of the section
		 *
		 * Can be a CSS query selector or the name of an animation declaration
		 *
		 * @var string
		 */
		public $selector;

		/**
		 * Type of the section: 'style' | 'animation'
		 *
		 * @var array
		 */
		public $content;

		/**
		 * Position prefixes if it is a section of animation type
		 *
		 * @var array
		 */
		public $position_prefixes;

		/**
		 * Constructor
		 *
		 * @param array|string $css_text_section of the section.
		 */
		public function __construct( $css_text_section ) {

			$selector = $this->extract_selector( $css_text_section );
			$type     = $this->define_type( $selector );

			if ( null === $selector ) {
				$content = $this->sort_properties( $css_text_section );
			} elseif ( 'style' === $type ) {
				$content = $this->sort_properties( $this->extract_content( $css_text_section ) );
			} else {
				$content = $this->sort_positions( $this->extract_content( $css_text_section ) );
			}

			$this->type     = $type;
			$this->selector = $selector;
			$this->content  = $content;
		}

		/**
		 * Extract content of a css text section
		 *
		 * @param string $css_text_section text.
		 * @return string only content
		 */
		public function extract_content( $css_text_section ) {
			return substr( $css_text_section, strpos( $css_text_section, '{' ) + 1, -1 );
		}

		/**
		 * Define the type of a CSS text section
		 *
		 * @param string $selector of the section.
		 * @return string animation | style
		 */
		public function define_type( $selector ) {
			return substr( $selector, 0, strlen( '@keyframes ' ) ) === '@keyframes ' ? 'animation' : 'style';
		}

		/**
		 * Extract selector of a CSS text section
		 *
		 * @param string $css_text_section of the section.
		 * @return string selector
		 */
		public function extract_selector( $css_text_section ) {
			$selector = substr( $css_text_section, 0, strpos( $css_text_section, '{' ) );
			return empty( $selector ) ? null : $selector;
		}

		/**
		 * Sort keyframes of CSS section animation
		 *
		 * @param string $css code.
		 * @return array
		 */
		public function sort_positions( $css ) {

			$sorted_positions   = array();
			$css                = substr( $css, -1 ) !== '}' ? $css . '}' : $css;
			$splitted_positions = $this->split_keyframes( $css );
			$splitted_positions = $this->remove_empty_elements( $splitted_positions );

			foreach ( $splitted_positions as $splitted_position ) {
				array_push(
					$sorted_positions,
					array(
						'position'   => substr( $splitted_position, 0, strpos( $splitted_position, '{' ) ),
						'properties' => $this->sort_properties( substr( $splitted_position, strpos( $splitted_position, '{' ) + 1, -1 ) ),
					)
				);
			}

			return $sorted_positions;
		}

		/**
		 * Split CSS keyframes
		 *
		 * @param string $css keyframes.
		 * @return array
		 */
		public function split_keyframes( $css ) {
			return preg_split( '/(?<=\})(?=\s*[0-9]+\s*%(?:(?:\s*,\s*[0-9]+\s*%)|)\s*\{)/', $css );
		}

		/**
		 * Sort properties of CSS section
		 *
		 * @param string $css code.
		 * @return array
		 */
		public function sort_properties( $css ) {

			$sorted_properties   = array();
			$css                 = substr( $css, -1 ) !== ';' ? $css . ';' : $css;
			$splitted_properties = $this->split_properties( $css );
			$splitted_properties = $this->remove_empty_elements( $splitted_properties );

			foreach ( $splitted_properties as $splitted_property ) {
				array_push(
					$sorted_properties,
					array(
						'property' => substr( $splitted_property, 0, strpos( $splitted_property, ':' ) ),
						'value'    => substr( $splitted_property, strpos( $splitted_property, ':' ) + 1, -1 ),
					)
				);
			}

			return $sorted_properties;
		}

		/**
		 * Split CSS properties into single property
		 *
		 * @param string $css_properties grouped.
		 * @return array
		 */
		public function split_properties( $css_properties ) {
			return preg_split( '/(?<=;)/', $css_properties );
		}

		/**
		 * Remove empty elements of an array
		 *
		 * @param array $array haystack.
		 * @return array
		 */
		public function remove_empty_elements( $array ) {
			return array_filter(
				$array,
				function( $value ) {
					return ! empty( $value ) && '' !== $value;
				}
			);
		}

	}
}
