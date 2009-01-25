<?php

if ( basename(__FILE__) == basename( $_SERVER['SCRIPT_FILENAME'] ) )
	die( 'Please do not load this page directly. Thanks!' );

if ( isset( $_GET['post'] ) ) {
	$post = (int) $_GET['post'];

	$review = array(
		'name'     => get_post_meta( $post, 'review-name', true ),
		'rating'   => get_post_meta( $post, 'review-rating', true ),
		'url'      => get_post_meta( $post, 'review-url', true ),
		'tags'     => get_post_meta( $post, 'review-tags', true ),
		'street'   => get_post_meta( $post, 'review-street', true ),
		'town'     => get_post_meta( $post, 'review-town', true ),
		'region'   => get_post_meta( $post, 'review-region', true ),
		'postcode' => get_post_meta( $post, 'review-postcode', true ),
		'country'  => get_post_meta( $post, 'review-country', true )
	);

	$is_review = get_post_meta( $post, 'review-status', true );
	$has_vcard = get_post_meta( $post, 'review-vcard', true );
}

?>

<div id="lv_review_form" <?php echo ( $is_review ) ? '' : 'style="display:none"'; ?> class="postbox open">

<?php if ( function_exists( 'add_meta_box' ) ) { ?>
<h3 class="hndle"><span><?php _e( 'Review Information', 'lv' ); ?></span></h3>
<?php } ?>

<div class="inside">

<fieldset id="lv_review_rating">
	<legend><?php _e('Rating (*)', 'lv'); ?></legend>
	<div><ul id="review-stars">
	<li><label><input type="radio" name="review[rating]" value="1" <?php checked( '1', $review['rating'] ); ?> /><span> <?php _e('1', 'lv'); ?></span></label></li>
	<li><label><input type="radio" name="review[rating]" value="2" <?php checked( '2', $review['rating'] ); ?> /><span> <?php _e('2', 'lv'); ?></span></label></li>
	<li><label><input type="radio" name="review[rating]" value="3" <?php checked( '3', $review['rating'] ); ?> /><span> <?php _e('3', 'lv'); ?></span></label></li>
	<li><label><input type="radio" name="review[rating]" value="4" <?php checked( '4', $review['rating'] ); ?> /><span> <?php _e('4', 'lv'); ?></span></label></li>
	<li><label><input type="radio" name="review[rating]" value="5" <?php checked( '5', $review['rating'] ); ?> /><span> <?php _e('5', 'lv'); ?></span></label></li>
	</ul></div>
</fieldset>

<?php

$usernames = (array) get_option( 'loudervoice_usernames' );

if ( !isset( $usernames[$user_ID] ) or empty( $usernames[$user_ID] ) ) {
	?><div class="lv_warning"><p><strong>Hold on!</strong> You should set your LouderVoice username in the <a href="options-general.php?page=loudervoice">Settings -> LouderVoice</a> menu before publishing a review to your blog.</p></div><?php
}

?>

<fieldset id="lv_review_name">
	<legend><?php _e('Item Name (*)', 'lv'); ?></legend>
	<div><input type="text" name="review[name]" id="review-name" size="30" value="<?php echo attribute_escape( $review['name'] ); ?>" tabindex="2" /></div>
</fieldset>

<div id="lv_vcard_form" <?php if ( !$has_vcard ) echo 'style="display:none"'; ?>>

<fieldset id="lv_vcard">
	<p id="lv_review_street"><label for="review-street"><?php _e('Street', 'lv'); ?></label>
	<input type="text" tabindex="3" id="review-street" name="review[street]" value="<?php echo attribute_escape( $review['street'] ); ?>" /></p>
	<p id="lv_review_town"><label for="review-town"><?php _e('Town (*)', 'lv'); ?></label>
	<input type="text" tabindex="4" id="review-town" name="review[town]" value="<?php echo attribute_escape( $review['town'] ); ?>" /></p>
	<p id="lv_review_region"><label for="review-region"><?php _e('Region', 'lv'); ?></label>
	<input type="text" tabindex="5" id="review-region" name="review[region]" value="<?php echo attribute_escape( $review['region'] ); ?>" /></p>
	<p id="lv_review_postcode"><label for="review-postcode"><?php _e('Postal Code', 'lv'); ?></label>
	<input type="text" tabindex="6" id="review-postcode" name="review[postcode]" value="<?php echo attribute_escape( $review['postcode'] ); ?>" /></p>
	<p id="lv_review_country"><label for="review-country"><?php _e('Country (*)', 'lv'); ?></label>
	<input type="text" tabindex="7" id="review-country" name="review[country]" value="<?php echo attribute_escape( $review['country'] ); ?>" /></p>
</fieldset>

</div>

<fieldset id="lv_review_url">
	<legend><?php _e('Item URL', 'lv'); ?></legend>
	<div><input type="text" name="review[url]" id="review-url" size="30" value="<?php echo attribute_escape( $review['url'] ); ?>" tabindex="8" /></div>
</fieldset>

<?php if ( !get_option( 'loudervoice_dontusetags' ) ) { ?>
<fieldset id="lv_review_tags">
	<legend><?php _e('LouderVoice Tags (*) (comma separated)', 'lv'); ?></legend>
	<div><input type="text" name="review[tags]" id="review-tags" size="30" value="<?php echo attribute_escape( $review['tags'] ); ?>" tabindex="9" /></div>
</fieldset>
<?php } ?>

<p class="nonessential"><?php _e( 'Fields marked (*) are required', 'lv' ); ?></p>

<script type="text/javascript">
//<![CDATA[

rrating = <?php echo ( $review['rating'] ) ? (int) $review['rating'] : '0'; ?>;
lv_username = <?php echo ( $lv_username ) ? "'" . attribute_escape( $lv_username ) . "'" : 'false'; ?>;
lv_dontusetags = <?php echo ( get_option( 'loudervoice_dontusetags' ) ) ? 'true' : 'false'; ?>;
lv_imgbase = '<?php echo $this->plugin['path']; ?>';
document.getElementById('review-stars').style.background = "url( '" + lv_imgbase + "/images/" + rrating + "outof5.gif' )";

//]]>
</script>

</div>
</div>
