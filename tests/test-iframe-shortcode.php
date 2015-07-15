<?php

class Test_Iframe_Shortcode extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		add_filter( 'shortcake_bakery_whitelisted_iframe_domains', function() {
			return array(
				'assets.fusion.net',
				'static.fusion.net',
				'interactive.fusion.net',
			);
		});
	}

	public function test_post_display_valid_domain() {
		$post_id = $this->factory->post->create( array( 'post_content' => '[iframe src="//static.fusion.net/the-ultimate-choice/"]' ) );
		$post = get_post( $post_id );
		$this->assertContains( '<iframe src="//static.fusion.net/the-ultimate-choice/" width="100%" height="600" frameborder="0" scrolling="no" class="shortcake-bakery-responsive"></iframe>', apply_filters( 'the_content', $post->post_content ) );
	}

	public function test_display_invalid_domain() {
		$post_id = $this->factory->post->create( array( 'post_content' => '[iframe src="http://notvalid.fusion.net/leonardo-dicaprio/"]' ) );
		$post = get_post( $post_id );
		$this->assertEmpty( trim( apply_filters( 'the_content', $post->post_content ) ) );
	}

}
