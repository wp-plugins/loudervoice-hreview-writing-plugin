
var rrating = 0;

lv_set_rating = function( x ) {
	rrating = x;
};

lv_show_rating = function( x ) {
	jQuery('#review-stars').css('backgroundImage','url(' + lv_imgbase + '/images/' + x + 'outof5.gif)');
};

validate_review = function() {
	if ( !jQuery('#lv_is_review').is(':checked') )
		return true;
	if ( !jQuery('#review-name').val() ) {
		alert( "Hold on, you haven't entered the item name for your review!" );
		jQuery('#lv_review_form').attr('class','postbox open');
		jQuery('#review-name').focus();
		return false;
	}
	if ( !rrating ) {
		alert( "Hold on, you haven't chosen a rating for your review!" );
		jQuery('#lv_review_form').css('class','postbox open');
		jQuery('#review-name').focus();
		return false;
	}
	if ( jQuery('#lv_has_vcard').is(':checked') ) {
		if ( !jQuery('#review-town').val() ) {
			alert( "Hold on, you haven't entered a town for your review!" );
			jQuery('#lv_review_form').css('class','postbox open');
			jQuery('#review-town').focus();
			return false;
		}
		if ( !jQuery('#review-country').val() ) {
			alert( "Hold on, you haven't entered a country for your review!" );
			jQuery('#lv_review_form').css('class','postbox open');
			jQuery('#review-country').focus();
			return false;
		}
	}
	if ( !lv_dontusetags ) {
		if ( !jQuery('#review-tags').val() ) {
			alert( "Hold on, you haven't entered any LouderVoice tags for your review!" );
			jQuery('#lv_review_form').css('class','postbox open');
			jQuery('#review-tags').focus();
			return false;
		}
	}
	return true;
};

lv_preload = function() {
	if ( !lv_imgbase )
		return;
	lv0 = new Image();
	lv0.src = lv_imgbase + '/images/0outof5.gif';
	lv1 = new Image();
	lv1.src = lv_imgbase + '/images/1outof5.gif';
	lv2 = new Image();
	lv2.src = lv_imgbase + '/images/2outof5.gif';
	lv3 = new Image();
	lv3.src = lv_imgbase + '/images/3outof5.gif';
	lv4 = new Image();
	lv4.src = lv_imgbase + '/images/4outof5.gif';
	lv5 = new Image();
	lv5.src = lv_imgbase + '/images/5outof5.gif';
}

if ( window.jQuery ) {

	jQuery(document).ready(function() {

		jQuery('#review-stars label').each(function(i) {
			var j = ( i + 1 );
			jQuery(this).hover(function(){
				lv_show_rating(j);
			},function(){
				lv_show_rating(rrating);
			}).click(function(){
				lv_set_rating(j);
			});
		});

		jQuery('#lv_is_review').click(function(){
			jQuery('#lv_review_form').slideToggle('normal',function(){
				jQuery(this).addClass('open').removeClass('closed');
				jQuery('#vcard_checkbox').toggle();
			});
		});

		jQuery('#lv_has_vcard').click(function(){
			jQuery('#lv_review_form').addClass('open').removeClass('closed');
			jQuery('#lv_vcard_form').slideToggle('normal');
		});

		jQuery('#post').submit(function(){
			return validate_review();
		});

	});

}
