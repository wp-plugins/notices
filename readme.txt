=== Notices Ticker ===
Contributors: peterwsterling
Donate link: http://www.sterling-adventures.co.uk/blog/2008/06/01/notices-ticker-plugin/
Author URI: http://www.sterling-adventures.co.uk/
Plugin URI: http://www.sterling-adventures.co.uk/blog/2008/06/01/notices-ticker-plugin/
Tags: notes, notices, message, ticker
Requires at least: 2.5
Tested up to: 2.5
Stable tag: trunk

This plug-in allows short notices to be scrolled - ticker-tape style - on your blog.

== Description ==

This plug-in allows short notices to be scrolled - ticker-tape style - on your blog.  Each message is valid for a set number of days from its creation (or update) date and can also be de-activated to keep it from being displayed until you are ready to let the world see it.

There are two ways to display the ticker notices:
- Using a simple sidebar widget.
- Embed the ticker into one (or more) of your theme’s template files.

== Installation ==

* Put the complete plug-in folder into your WordPress plug-in directory (if this doesn’t make sense it probably isn’t something you should be trying) and activate it.
* Define the text of your notices using the <b>Notices</b> form at <i>Manage &raquo; Notices</i>. Note that HTML is allowed in the text but be careful to avoid the <code>"</code> (double quote) character. Each notice has these attributes:
- <i>Notice</i> text (the notice itself).
- The number of days the notice will be <i>valid</i> for.
- Once created, a checkbox indicating if the notice is <i>active</i>.
- The <i>date</i> is the date the notice is valid from and is the date the notice was created (or last updated).
* Use the Notices widget (<i>Design &raquo; Widgets</i>) to show a sidebar widget that scrolls the chosen number of the most recent notices. The widget has just two options - a repeat (from the main <i>Manage &raquo; Notices</i> page) of the number of notices to show, and a title for the widget.
* Use this <code>&lt;?php put_ticker( [true | false] ); ?&gt;</code> in your theme’s template files. Where <code>true</code> or <code>false</code> determines if the ticker should be hidden when there are no notices to scroll.
For example, <code>&lt;?php put_ticker(false); ?&gt;</code> only shows the ticker when there are notices to scroll, whereas <code>&lt;?php put_ticker(true); ?&gt;</code> always shows the ticker - even an empty one.
* There is a small CSS document (<code>notice.css</code>) in the plug-in folder to help style your notices ticker. The three styles defined are:
- <code>.ticker</code> - style for the text used in the notices.
- <code>.ticker img</code> - style for images (use <code>&lt;img src=... /&gt;</code> in the notice text) in the notices. The example lines images up with the bottom of the notice text.
- <code>.ticker-div</code> - style for the ticker when included in templates files of your theme.
