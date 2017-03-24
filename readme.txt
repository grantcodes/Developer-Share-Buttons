=== Developer Share Buttons ===
Contributors: terminalpixel
Tags: share buttons, developer, social, share
Requires at least: 3.5
Tested up to: 4.7.2
Stable tag: 1.2.0

A super lightweight social sharing solution using either the Web Share API or simple sharing links.

== Description ==

A simple, customisable share buttons solution designed specifically for theme developers.

If you would like to help out please contribute to the [GitHub repository](https://github.com/terminalpixel/Developer-Share-Buttons)

== Installation ==
Install the plugin in whatever way you deem fit. Once installed you can set the default settings on the options page.

== Frequently Asked Questions ==
= What functions are available to use? =

There are 4 functions available to use:
1. `get_dev_share_buttons` returns html of share links
2. `the_dev_share_buttons` a wrapper just to echo `get_dev_share_buttons`
3. `get_dev_profile_links` returns an array of the links to social pages that are saved in the admin area
4. `the_dev_profile_links` echos the links as `a` tags in a `div`

`get_dev_share_buttons` and `the_dev_share_buttons` both accept 5 optional parameters:

1. services - An array of service ids eg `array( 'facebook', 'twitter', 'google' )`. Defaults to the options saved on the options page
2. url - The url to share. Defaults to value of `get_the_permalink()`
3. title - The title of the item to share. Defaults to the value of `get_the_title()`
4. text - The text that appears before a service title eg "Share on". Defaults to the value set on the options page.
5. image - The url of an image to go along with the main item, only used by certain services. Defaults to the post thumbnail.

= Are there default styles for this? =

Yes there are! I have added some simple default styles that you can optionally enable:

`add_filter( 'dev_share_buttons_css', '__return_true' );`

= Adding more services =

You can add more services using a filter:

`add_filter( 'dev_share_buttons_services', 'my_new_service' );
function my_new_service( $services ) {
    $services['myserviceid'] = array(
        'id' => 'myserviceid',
        'title' => 'My Service Title',
        'url_structure' => 'http://www.shareurl.com/?url=%1$s&title=%2$s&text=%3$s&image=%4$s',
        'url_after_title' => false
    );
    return $services;
}`

= Adding Icons =

Icons can be added via pure css or the filters provided to add content before or after the button text:

`// Add an svg icon to share buttons.
function my_share_social_icons( $html, $service ) {
    $icon_url = get_stylesheet_directory_uri() . '/svg-icons.svg#' . $service['id'];
    $icon_html = '<svg><use xlink:href="' . $icon_url . '"></use></svg>';
    return $html . $icon_html;
}
add_filter( 'dev_share_buttons_after_share_text', 'my_share_social_icons', 10, 2 );

// Add a png icon to profile links.
function my_profile_social_icons( $html, $service ) {
    $icon_url = get_stylesheet_directory_uri() . '/social-icons/' . $service['id'] . '.png';
    $icon_html = '<img src="' . $icon_url . '" />';
    return $html . $icon_html;
}
add_filter( 'dev_share_buttons_after_profile_text', 'my_profile_social_icons', 10, 2 );`

= Disabling the Web Share API =

The Web Share API is disabled by default but I recommend enabling it as it will display a single share button instead of the multiple buttons if the browser supports it. If you wish to enable this feature you can add a filter:

`add_filter( 'dev_share_buttons_share_api', '__return_true' );`

== Changelog ==

= 1.0.1 =
* Prevent `the_` functions from returning values
* Add spaces between profile links

= 1.0.3 =
* Fix profile link widget

= 1.0.4 =
* Improve support for non post object based pages

= 1.0.5 =
* Add `rel="me"` support for profile links

= 1.0.6 =
* Update to all https links
* Use updated twitter and pinterest urls
* Url encode more things for better character support

= 1.0.7 =
* Add extra filters to add content before and after button text (useful for icons)
* Update coding standard

= 1.0.8 =
* Use `rawurlencode` to improve compatibility with some services

= 1.1.0 =
* Add some optional default styles
* Add Medium, Messenger, Skype, Snapchat, WeChat, WhatsApp and YouTube profile links

= 1.2.0 =
* Add support for the Web Share API
