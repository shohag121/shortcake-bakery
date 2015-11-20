<?php

namespace Shortcake_Bakery\Shortcodes;

class GoogleDocs extends Shortcode {

	private static $valid_hosts = array( 'docs.google.com', 'www.google.com' );

	public static function get_shortcode_ui_args() {
		return array(
			'label'          => esc_html__( 'Google Docs', 'shortcake-bakery' ),
			'listItemImage'  => '<img src="' . esc_url( SHORTCAKE_BAKERY_URL_ROOT . 'assets/images/svg/icon-googledocs.svg' ) . '" />',
			'attrs'          => array(
				array(
					'label'        => esc_html__( 'Document Type', 'shortcake-bakery' ),
					'attr'         => 'type',
					'type'         => 'select',
					'options'      => array(
						'document'      => esc_html__( 'Document', 'shortcake-bakery' ),
						'spreadsheet'   => esc_html__( 'Spreadsheet', 'shortcake-bakery' ),
						'presentation'  => esc_html__( 'Presentation', 'shortcake-bakery' ),
						'form'          => esc_html__( 'Form', 'shortcake-bakery' ),
						'map'           => esc_html__( 'Map', 'shortcake-bakery' ),
					),
					'description'  => esc_html__( 'Type of document to embed', 'shortcake-bakery' ),
				),
				array(
					'label'        => esc_html__( 'URL', 'shortcake-bakery' ),
					'attr'         => 'url',
					'type'         => 'text',
					'description'  => esc_html__( 'Full document URL', 'shortcake-bakery' ),
				),

				array(
					'label'        => esc_html__( 'Height', 'shortcake-bakery' ),
					'attr'         => 'height',
					'type'         => 'number',
					'description'  => esc_html__( 'Pixel height of the iframe.', 'shortcake-bakery' ),
				),
				array(
					'label'        => esc_html__( 'Width', 'shortcake-bakery' ),
					'attr'         => 'width',
					'type'         => 'number',
					'description'  => esc_html__( 'Pixel width of the iframe.', 'shortcake-bakery' ),
				),
				array(
					'label'        => esc_html__( 'Disable Responsiveness', 'shortcake-bakery' ),
					'attr'         => 'disableresponsiveness',
					'type'         => 'checkbox',
					'description'  => esc_html__( 'By default, height/width ratio of the embedded document will be maintained regardless of container width. Check this to keep constant height/width.', 'shortcake-bakery' ),
				),

				/* Options specific to "spreadsheet" document type */
				array(
					'label'        => esc_html__( 'Display spreadsheet header rows?', 'shortcake-bakery' ),
					'attr'         => 'headers',
					'type'         => 'checkbox',
				),

				/* Options specific to "presentation" document type */
				array(
					'label'        => esc_html__( 'Autostart?', 'shortcake-bakery' ),
					'attr'         => 'start',
					'type'         => 'checkbox',
				),
				array(
					'label'        => esc_html__( 'Loop?', 'shortcake-bakery' ),
					'attr'         => 'loop',
					'type'         => 'checkbox',
				),
				array(
					'label'        => esc_html__( 'Delay between slides (ms)', 'shortcake-bakery' ),
					'attr'         => 'delayms',
					'type'         => 'number',
					'default'      => 3000,
				),
				array(
					'label'        => esc_html__( 'Allow fullscreen mode?', 'shortcake-bakery' ),
					'attr'         => 'allowfullscreen',
					'type'         => 'checkbox',
				),

			),
		);
	}

	public static function reversal( $content ) {

		if ( $iframes = self::parse_iframes( $content ) ) {
			$replacements = array();
			foreach ( $iframes as $iframe ) {
				if ( ! in_array( self::parse_url( $iframe->attrs['src'], PHP_URL_HOST ), self::$valid_hosts ) ) {
					continue;
				}
				if ( preg_match( '#(docs|www)\.google\.com/(\w*)/d/(.*)/(\w*)\?([^/?]+)$#', $iframe->attrs['src'], $matches ) ) {
					list( $url, $subdomain, $doc_type, $embed_id, $view_name, $query_string ) = $matches;
				} else {
					continue;
				}

				switch ( $doc_type ) {
					case 'document':
						$replacement_url = 'https://docs.google.com/document/d/' . $embed_id;
						$replacements[ $iframe->original ] = '[' . self::get_shortcode_tag() . ' type="document" ' .
							'url="' . esc_url_raw( $replacement_url ) . '"' .
							( ! empty( $iframe->attrs['height'] ) ? ' height=' . intval( $iframe->attrs['height'] ) : '' ) .
							( ! empty( $iframe->attrs['width'] ) ? ' width=' . intval( $iframe->attrs['width'] ) : '' ) .
							']';
						break;
					case 'spreadsheet':
					case 'spreadsheets':
						parse_str( html_entity_decode( $query_string ), $query_vars );
						$replacement_url = 'https://docs.google.com/spreadsheets/d/' . $embed_id;
						$replacements[ $iframe->original ] = '[' . self::get_shortcode_tag() . ' type="spreadsheet" ' .
							'url="' . esc_url_raw( $replacement_url ) . '"' .
							( ! empty( $iframe->attrs['height'] ) ? ' height=' . intval( $iframe->attrs['height'] ) : '' ) .
							( ! empty( $iframe->attrs['width'] ) ? ' width=' . intval( $iframe->attrs['width'] ) : '' ) .
							( ! empty( $query_vars['headers'] ) && 'false' !== $query_vars['headers'] ? ' headers="true"' : '' ) .
							']';
						break;
					case 'presentation':
						parse_str( html_entity_decode( $query_string ), $query_vars );
						$replacement_url = 'https://docs.google.com/presentation/d/' . $embed_id;
						$replacements[ $iframe->original ] = '[' . self::get_shortcode_tag() . ' type="presentation" ' .
							'url="' . esc_url_raw( $replacement_url ) . '"' .
							( ! empty( $iframe->attrs['height'] ) ? ' height=' . intval( $iframe->attrs['height'] ) : '' ) .
							( ! empty( $iframe->attrs['width'] ) ? ' width=' . intval( $iframe->attrs['width'] ) : '' ) .
							( ! empty( $query_vars['start'] ) && 'false' !== $query_vars['start'] ? ' start="true"' : '' ) .
							( ! empty( $query_vars['loop'] ) && 'false' !== $query_vars['loop'] ? ' loop="true"' : '' ) .
							( ! empty( $query_vars['delayms'] ) ? ' delayms=' . intval( $query_vars['delayms'] ) : '' ) .
							( ! empty( $iframe->attrs['allowfullscreen'] ) ? ' allowfullscreen="true"' : '' ) .
							']';
						break;
					case 'form':
					case 'forms':
						$replacement_url = 'https://docs.google.com/forms/d/' . $embed_id;
						$replacements[ $iframe->original ] = '[' . self::get_shortcode_tag() . ' type="form" ' .
							'url="' . esc_url_raw( $replacement_url ) . '"' .
							( ! empty( $iframe->attrs['height'] ) ? ' height=' . intval( $iframe->attrs['height'] ) : '' ) .
							( ! empty( $iframe->attrs['width'] ) ? ' width=' . intval( $iframe->attrs['width'] ) : '' ) .
							']';
						break;
					case 'map':
					case 'maps':
						parse_str( html_entity_decode( $query_string ), $query_vars );
						if ( empty( $query_vars['mid'] ) ) {
							return;
						}
						$replacement_url = add_query_arg(
							array(
								'mid' => $query_vars['mid'],
							),
							'https://www.google.com/maps/d/embed'
						);
						$replacements[ $iframe->original ] = '[' . self::get_shortcode_tag() . ' type="map" ' .
							'url="' . esc_url_raw( $replacement_url ) . '"' .
							( ! empty( $iframe->attrs['height'] ) ? ' height=' . intval( $iframe->attrs['height'] ) : '' ) .
							( ! empty( $iframe->attrs['width'] ) ? ' width=' . intval( $iframe->attrs['width'] ) : '' ) .
							']';
						break;
				}
			}
			$content = self::make_replacements_to_content( $content, $replacements );
		}

		return $content;
	}

	public static function callback( $attrs, $content = '' ) {

		$host = self::parse_url( $attrs['url'], PHP_URL_HOST );
		if ( empty( $attrs['type'] ) || empty( $attrs['url'] ) || ! in_array( $host, self::$valid_hosts ) ) {
			return '';
		}

		$iframe_classes = "shortcake-bakery-googledocs-{$attrs['type']}" .
			( empty( $attrs['disableresponsiveness'] ) ? ' shortcake-bakery-responsive' : '' );

		$width_attr  = ! empty( $attrs['width'] )  ? 'width  = "' . intval( $attrs['width'] ) . '" ' : '';
		$height_attr = ! empty( $attrs['height'] ) ? 'height = "' . intval( $attrs['height'] ) . '" ' : '';

		$iframe_attrs = ' frameborder="0" marginheight="0" marginwidth="0"';

		switch ( $attrs['type'] ) {
			case 'document':
				return sprintf( '<iframe class="%s" src="%s/pub?embedded=true"%s%s frameborder="0" marginheight="0" marginwidth="0"></iframe>',
					esc_attr( $iframe_classes ),
					esc_url_raw( $attrs['url'] ),
					wp_kses_one_attr( $width_attr, 'img' ),
					wp_kses_one_attr( $height_attr, 'img' )
				);
			case 'spreadsheet':
				$url = add_query_arg(
					array(
						'widget' => 'true',
						'headers' => ! empty( $attrs['headers'] ) ? 'true' : 'false',
					),
					$attrs['url'] . '/pubhtml'
				);
				return sprintf( '<iframe class="%s" src="%s" %s%sframeborder="0" marginheight="0" marginwidth="0"></iframe>',
					esc_attr( $iframe_classes ),
					esc_url( $url ),
					wp_kses_one_attr( $width_attr, 'img' ),
					wp_kses_one_attr( $height_attr, 'img' )
				);
			case 'presentation':
				$url = add_query_arg(
					array(
						'start' => ! empty( $attrs['start'] ) ? 'true' : 'false',
						'loop' => ! empty( $attrs['loop'] ) ? 'true' : 'false',
						'delayms' => ! empty( $attrs['delayms'] ) ? intval( $attrs['delayms'] ) : '3000',
					),
					$attrs['url'] . '/embed'
				);
				return sprintf( '<iframe class="%s" src="%s" %s%sframeborder="0" marginheight="0" marginwidth="0"%s></iframe>',
					esc_attr( $iframe_classes ),
					esc_url_raw( $url ),
					wp_kses_one_attr( $width_attr, 'img' ),
					wp_kses_one_attr( $height_attr, 'img' ),
					! empty( $attrs['allowfullscreen'] ) ? ' allowfullscreen="true" mozallowfullscreen="true" webkitallowfullscreen="true"' : ''
				);
			case 'form':
				$url = add_query_arg(
					array(
						'embedded' => 'true',
					),
					$attrs['url'] . '/viewform'
				);
				return sprintf( '<iframe class="%s" src="%s" %s%sframeborder="0" marginheight="0" marginwidth="0">%s</iframe>',
					esc_attr( $iframe_classes ),
					esc_url_raw( $url ),
					wp_kses_one_attr( $width_attr, 'img' ),
					wp_kses_one_attr( $height_attr, 'img' ),
					esc_html__( 'Loading...', 'shortcake-bakery' )
				);
			case 'map':
				return sprintf( '<iframe class="%s" src="%s" %s%sframeborder="0" marginheight="0" marginwidth="0"></iframe>',
					esc_attr( $iframe_classes ),
					esc_url_raw( $attrs['url'] ),
					wp_kses_one_attr( $width_attr, 'img' ),
					wp_kses_one_attr( $height_attr, 'img' )
				);
		}
	}
}
