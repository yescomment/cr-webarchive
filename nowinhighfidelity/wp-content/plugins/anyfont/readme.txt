=== AnyFont ===
Contributors: choon
Donate link: http://2amlife.com
Tags: images, styles, fonts, admin, theme, enhancement, truetype, opentype, typography, ttf, font, plugin
Requires at least: 2.5
Tested up to: 2.8.1
Stable tag: 0.7.3

AnyFont allows you to use any truetype or opentype font to replace plain text anywhere you want in your theme design. NEW IN 0.7: OpenType Support!

== Description ==

AnyFont allows you to use any truetype or opentype font for post titles, menu items or anywhere else you want to use a custom font in your theme design.

Supports both PHP4 & GD or PHP5 & Imagick or GD.

Version 0.7 introduces an additional two new features, Firstly you can now use any OpenType or TrueType font with AnyFont, and the second great feature which was contributed by [Rik Ward](http://shinypixel.co.uk/), is the ability to have a width limit for your text. ie: Multi-line text. Note: this feature is only available on NEWLY created styles. You cannot edit your current styles and add multiline support at this time.

**Features:**

* Font Manager to easily upload new fonts to wordpress
* Style Management which allows an unlimited number of different styles to be created.
* Font shadow options within the style manager(Requires PHP5 & ImageMagick).
* Image Cache for generated images plus browser caching is enabled for images to reduce page load times.
* Cache Management.
* Easy text replacement options for post titles, page titles, widget titles, blog name and blog description.
* Image replacements are SEO compatible.


== Changelog ==

= 0.7.3 =

* Bugfix: Widget Title replacements will retain the original $before_title and $after_title variables, which really improves compatiblity with highly customised themes. (thanks Gavin!)
* Bugfix: Text should now be left aligned if the style has a character limit

= 0.7.2 =

* Bugfix: Correctly escaped some unescaped characters in a regular expression in the template class (Thanks Joe!)
* Bugfix: Checkboxes for newly created styles should function correctly without needing to refresh the page
* Bugfix: Fonts are listed correctly in the dropdown after saving a new style

= 0.7.1 =

* Feature: New options to replace plain text version for tag titles and category titles.
* Enhancements: Style interface has been cleaned up by hiding any unused options.
* Bugfix: width limit option displaying as off even when enabled.
* Bugfix: correct font was not selected for created styles in style manager (php4 & gd version)
* Bugfix: width limit options are now available for styles created before version 0.7.0

Full changelog is available [here](http://2amlife.com/projects/anyfont/changelog)


== Screenshots ==

1. Style Manager
2. Font Manager
3. General Settings


== Frequently Asked Questions ==

= After enabling the post title replace option on the settings page, all my post titles start with a "&#62; why?? =

This is quite a common error in themes, but quick and easy to fix, open up your theme's index.php file, look for a line that looks similar to the following:

>&#60;h2  class="posttitle"&#62;&#60;a href="&#60;?php the\_permalink() ?&#62;" rel="bookmark" title="Permanent Link to &#60;?php the\_title(); ?&#62;"&#62;&#60;?php the\_title(); ?&#62;&#60;/a&#62;&#60;/h2&#62;

The problem lies in the title attribute, it should read:

>"Permanent Link to &#60;?php the\_title\_*attribute*(); ?&#62;"

= My Hosting Provider says ImageMagick is already installed, but AnyFont doesn't see it? =

If you want to use ImageMagick over GD you need to ensure that the Imagick PHP extension to ImageMagick is also installed. [Click here for more info on Imagick](http://www.php.net/manual/en/book.imagick.php)

= Its just not working for me! HELP! =

Dont worry!! I am here to help where possible! Just leave a message explaining the problem you having on my [blog](http://2amlife.com) or send me a [tweet](http://twitter.com/ch00n) and I will get back to you asap.


== Usage ==

After you have uploaded some truetype fonts and created a new font style, you can include dynamic images in your theme with the following code snippet:

>"&#60;img src='/images/your-style-name/the text to be generated.png' alt='the text to be generated' /&#62;"

**Example:**

Replacing the text in the menu with an image:

>"&#60;a href="&#60;?php bloginfo('home') ?&#62;" rel="home"&#62;Home&#60;/a&#62;"

becomes

>"&#60;a href="&#60;?php bloginfo('home') ?&#62;" rel="home"&#62;&#60;img src="/images/menu/Home.png" alt="Home" /&#62;&#60;/a&#62;"


== Installation ==

*Server Requirements:* PHP4 or PHP5 and either the ImageMagick(imagick module 2.1.1-rc1 and up) or GD image module installed.

Upload the AnyFont plugin to your blog and activate it!