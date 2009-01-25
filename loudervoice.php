<?php
/*
Plugin Name: LouderVoice
Description: Allows you to easily insert correctly formatted LouderVoice reviews into your blog posts. [&nbsp;<a href="options-general.php?page=loudervoice">Settings</a>&nbsp;&bull;&nbsp;<a href="../wp-content/plugins/loudervoice/readme.txt">Readme</a>&nbsp;]
Version:     2.1
Author:      LouderVoice
Plugin URI:  http://www.loudervoice.com/extras/
Author URI:  http://www.loudervoice.com/
Copyright:   2007-2009 Argolon Solutions Limited t/a LouderVoice

	Built for LouderVoice by John Blackbourn [johnblackbourn.com]

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

*/

class LouderVoice {

	var $plugin = array();
	var $form;
	var $checkboxes;

	function LouderVoice() {
		$this->plugin = array(
			'path'    => '' . WP_PLUGIN_URL . '/' . basename( dirname( __FILE__ ) ),
			'version' => '2.1'
		);

		if ( $this->is( 'post.php' ) or
			 $this->is( 'post-new.php' ) ) {
			add_action( 'admin_menu',   array( &$this, 'load_js' ) );
			add_action( 'admin_head',   array( &$this, 'build_review_form' ) );
			add_action( 'admin_footer', array( &$this, 'flush_review_form' ) );
		}

		add_action( 'admin_head',       array( &$this, 'load_css' ) );
		add_action( 'save_post',        array( &$this, 'save_meta' ), 10, 2 );
		add_action( 'save_post',        array( &$this, 'ping_loudervoice' ), 10, 2 );
		add_action( 'admin_menu',       array( &$this, 'options_menu' ) );
		add_filter( 'the_content',      array( &$this, 'filter_content' ) );
		add_filter( 'admin_init',       array( &$this, 'save_username' ) );
	}

	function save_username() {
		global $user_ID;

		if ( !$this->is( 'options.php' ) )
			return;
		if ( !isset( $_POST['action'] ) or !isset( $_POST['loudervoice_username'] ) )
			return;
		if ( 'update' != $_POST['action'] )
			return;

		$usernames = (array) get_option( 'loudervoice_usernames' );

		$usernames[$user_ID] = stripslashes( $_POST['loudervoice_username'] );
		ksort( $usernames );
		update_option( 'loudervoice_usernames', $usernames );

	}

	function filter_content( $content ) {
		global $post;

		$meta = get_post_custom( $post->ID );

		if ( !isset( $meta['review-status'][0] ) or ( '' == $meta['review-status'][0] ) )
			return $content;

		$usernames = get_option( 'loudervoice_usernames' );

		$review = array(
			'status'   => $meta['review-status'][0],
			'vcard'    => $meta['review-vcard'][0],
			'rating'   => (int) $meta['review-rating'][0],
			'name'     => wptexturize( $meta['review-name'][0] ),
			'url'      => $meta['review-url'][0],
			'tags'     => $meta['review-tags'][0],
			'street'   => wptexturize( $meta['review-street'][0] ),
			'town'     => wptexturize( $meta['review-town'][0] ),
			'region'   => wptexturize( $meta['review-region'][0] ),
			'postcode' => wptexturize( $meta['review-postcode'][0] ),
			'country'  => wptexturize( $meta['review-country'][0] ),
			'date'     => get_the_time( 'M j Y' ),
			'author'   => get_the_author(),
			'username' => attribute_escape( stripslashes( $usernames[$post->post_author] ) )
		);

		$vcard = ( $review['vcard'] ) ? ' vcard' : '';

		$lv_lang = ( '' != WPLANG ) ? substr( WPLANG, 0, 2 ) : 'en';

		$hreview  = "<div lang='$lv_lang' class='hreview'>\n";
		$hreview .= "<div class='item$vcard'>\n";
		$hreview .= ( $vcard ) ? "<span class='fn org'>" : "<span class='fn'>";
		$hreview .= ( $review['url'] ) ? "<a href='{$review['url']}' class='url'>{$review['name']}</a>" : $review['name'];
		$hreview .= '</span>';

		if ( $vcard ) {
			$loc = array();
			if ( $review['street'] )
				$loc[] = "<div><span class='street-address'>{$review['street']}</span>";
			if ( $review['town'] )
				$loc[] = "<div><span class='locality'>{$review['town']}</span>";
			if ( $review['region'] )
				$loc[] = "<div><span class='region'>{$review['region']}</span>";
			if ( $review['postcode'] )
				$loc[] = "<div><span class='postal-code'>{$review['postcode']}</span>";
			if ( $review['country'] )
				$loc[] = "<div><span class='country-name'>{$review['country']}</span>";
			$hreview .= ",\n<div class='adr'>\n" . implode( ",</div>\n", $loc ) . "</div>\n</div>";
		}

		$hreview .= "\n</div>\n\n<div class='stars' title='{$review['rating']}/5'><img src='" . $this->plugin['path'] . "/images/{$review['rating']}outof5.gif' alt='{$review['rating']}/5' /></div>\n\n";

		$hreview = wptexturize( $hreview );
		$hreview .= "<div class='description'>$content</div>\n\n";

		$hreview .= '<div>' . sprintf( __( 'Rated %1$s/5 on %2$s', 'lv' ), "<span class='rating'>{$review['rating']}</span>", "<span class='dtreviewed'>{$review['date']}</span>" ) . "</div>\n";

		$hreview .= wptexturize( '<div>' . sprintf( __( 'Vote on %1$s\'s reviews at %2$s', 'lv' ), "<span class='reviewer vcard'><span class='fn'>{$review['author']}</span></span>", "<a href='http://www.loudervoice.com/people/{$review['username']}/'>LouderVoice</a>" ) . "</div>\n\n" );

		if ( !get_option( 'loudervoice_dontusetags' ) ) {
			$tags = wptexturize( $this->clean_tags( $review['tags'], true ) );
			$hreview .= "<div class='review_tags'>" . sprintf( __( '%1$s review tags: %2$s', 'lv' ), 'LouderVoice', $tags ) . "</div>\n\n";
		}

		$hreview .= "\n</div>";
		
		return $hreview;
	}

	function options_menu() {
		add_options_page( __( 'LouderVoice', 'lv' ), __( 'LouderVoice', 'lv' ), 'manage_options', 'loudervoice', array( &$this, 'options_page' ) );
	}

	function options_page() {
		global $user_ID;

		$usernames = (array) get_option( 'loudervoice_usernames' );

		$user = new WP_User( $user_ID );

		if ( isset( $usernames[$user_ID] ) )
			$lv_username = $usernames[$user_ID];
		else
			$lv_username = '';

		?>
<div class="wrap">
<h2><img src="<?php echo $this->plugin['path']; ?>/images/loudervoice.png" alt="LouderVoice logo" /> <?php _e( 'LouderVoice Settings', 'lv' ); ?></h2>
<form method="post" action="options.php">
<?php if ( function_exists( 'wp_nonce_field' ) )
	wp_nonce_field( 'update-options' ); ?>
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="loudervoice_dontusetags" />
<table class="optiontable form-table">
<tr>
	<th scope="row" valign="top"><?php _e( 'Tagging', 'lv' ); ?></th>
	<td><label><input type="checkbox" name="loudervoice_dontusetags" <?php checked( get_option( 'loudervoice_dontusetags' ), true ); ?> /> <?php _e( 'Don\'t use LouderVoice tags on this blog', 'lv' ); ?></label><br />
	<span class="setting-description"><?php _e( 'If this checkbox is checked, you won\'t see the tags field when writing a review. LouderVoice will instead use the tags from whatever tagging system you use with WordPress and apply those to your review.', 'lv' ); ?></span></td>
</tr>
<tr>
	<th scope="row" valign="top"><?php _e( 'My LouderVoice Username', 'lv' ); ?></th>
	<td><input type="text" name="loudervoice_username" value="<?php echo attribute_escape( $lv_username ); ?>" size="30" class="regular-text" /><br />
	<span class="setting-description"><?php _e( 'This is your LouderVoice username and only applies to reviews that you write. Other users of this blog will need to set their own LouderVoice username in order to write reviews.', 'lv' ); ?>
	<?php if ( '' == $lv_username ) {
		echo '<strong>';
		printf( __( 'If you don\'t have a LouderVoice account <a href="%s" target="_blank">get one here!</a>', 'lv' ), 'http://www.loudervoice.com/register/create?email=' . $user->user_email );
		echo '</strong>';
	} ?>
	</span></td>
</tr>
</table>
<p class="submit"><input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" /></p>
</form>
</div>
		<?php
	}

	function save_meta( $post_ID, $post = false ) {
		global $user_ID;

		if ( !isset( $_POST['review'] ) )
			return;

		$review = (array) $_POST['review'];
		$post_ID = (int) $post_ID;

		foreach ( $review as $key => $value ) {
			if ( function_exists( 'wp_filter_nohtml_kses' ) )
				$value = stripslashes( wp_filter_nohtml_kses( $value ) );
			else
				$value = stripslashes( strip_tags( $value ) );
			if ( '' != $value ) {
				if ( 'url' == $key )
					$value = clean_url( $value );
				if ( 'rating' == $key )
					$value = (int) $value;
				if ( 'tags' == $key )
					$value = $this->clean_tags( $value );
				if ( !update_post_meta( $post_ID, "review-$key", $value ) )
					add_post_meta( $post_ID, "review-$key", $value );
			} else
				delete_post_meta( $post_ID, "review-$key" );
		}

		if ( !isset( $review['status'] ) ) {
			delete_post_meta( $post_ID, 'review-status' );
			delete_post_meta( $post_ID, 'review-rating' );
		}

		if ( !isset( $review['vcard'] ) )
			delete_post_meta( $post_ID, 'review-vcard' );

	}

	function clean_tags( $tags = '', $linkify = false ) {
		$tags = explode( ',', $tags );

		foreach ( $tags as $tag )
			$taglist[] = trim( $tag );

		unset( $tags );

		if ( !$linkify )
			return implode( ', ', $taglist );

		for ( $i = 0; $i < count( $taglist ); $i++ ) {
			$tag = strtolower( $taglist[$i] );
			$tag = preg_replace( '/^"([^"]+)"$/', "$1", $tag );
			$tag = urlencode( $tag );
			$tag = str_replace( '%20', '+', $tag );
			$tag = '<a href="http://www.loudervoice.com/tags/' . $tag . '" rel="tag">' . $taglist[$i] . '</a>';
			$taglist[$i] = $tag;
		}

		return implode( ', ', $taglist );
	}

	function insert_review_form( $content ) {
		$content = str_replace( '<fieldset id="titlediv', $this->checkboxes . '<fieldset id="titlediv', $content );
		$content = str_replace( '<fieldset id="postdiv', $this->form . '<fieldset id="postdiv', $content );
		$content = str_replace( '<div id="titlediv', $this->checkboxes . '<div id="titlediv', $content );
		$content = str_replace( '<div id="postdiv', $this->form . '<div id="postdiv', $content );
		return $content;
	}

	function flush_review_form() {
		ob_end_flush();
	}

	function review_form() {
		global $user_ID;
		ob_start();
		include( 'reviewform.php' );
		$rtn = ob_get_contents();
		ob_end_clean();
		return $rtn;
	}

	function review_checkboxes() {
		if ( isset( $_GET['post'] ) ) {
			$post = (int) $_GET['post'];
			$is_review = get_post_meta( $post, 'review-status', true );
			$has_vcard = get_post_meta( $post, 'review-vcard', true );
		}
		ob_start();
		?>
	<fieldset id="hreview_checkbox" <?php if ( function_exists( 'add_meta_box' ) ) echo 'class="freshbox"'; ?>>
	<label><input id="lv_is_review" name="review[status]" type="checkbox" <?php checked( $is_review, true ); ?> /> <?php _e('This is a review', 'lv'); ?></label>
	</fieldset>

	<fieldset id="vcard_checkbox" <?php if ( function_exists( 'add_meta_box' ) ) echo 'class="freshbox"'; ?> <?php if ( !$is_review ) echo 'style="display:none;"'; ?>>
		<label><input id="lv_has_vcard" name="review[vcard]" type="checkbox" <?php checked( $has_vcard, true ); ?> /> <?php _e('Include location information', 'lv'); ?></label>
	</fieldset>

	<br style="clear:left" />
		<?php
		$rtn = ob_get_contents();
		ob_end_clean();
		return $rtn;
	}

	function build_review_form() {
		$this->form = $this->review_form();
		$this->checkboxes = $this->review_checkboxes();
		ob_start( array( &$this, 'insert_review_form' ) );
	}

	function ping_loudervoice( $post_ID, $post = false ) {
		if ( !get_post_meta( $post_ID, 'review-status', true ) )
			return;
		if ( !$post )
			$post = get_post( $post_ID );
		if ( 'publish' != $post->post_status )
			return;
		weblog_ping( 'http://www.loudervoice.com/rpc' );
	}

	function is( $filename ) {
		return strpos( $_SERVER['REQUEST_URI'], $filename );
	}

	function load_css() {
		?><link rel="stylesheet" type="text/css" href="<?php echo $this->plugin['path']; ?>/loudervoice.css?ver=<?php echo $this->plugin['version']; ?>" /><?php
	}

	function load_js() {
		$dep = array( 'jquery' );
		wp_enqueue_script( 'loudervoice', $this->plugin['path'] . '/loudervoice.js', $dep, $this->plugin['version'] );
	}

}

if ( !defined( 'WP_CONTENT_URL' ) )
	define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( !defined( 'WP_CONTENT_DIR' ) )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( !defined( 'WP_PLUGIN_URL' ) )
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( !defined( 'WP_PLUGIN_DIR' ) )
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

$loudervoice = new LouderVoice();

if ( function_exists( 'load_plugin_textdomain' ) )
	load_plugin_textdomain( 'lv', $loudervoice->plugin['path'] );

?>