<?php
/*
	Plugin Name:	Notices
	Plugin URI:		http://www.sterling-adventures.co.uk/blog/2008/06/01/notices-ticker-plugin/
	Description:	A plugin which adds a widget with a scrolling "ticker" of notices.
	Author:			Peter Sterling
	Version:		0.2
	Changes:		0.1 - Initial version.
					0.2 - Ticker's "scrollamount" option set, thanks to Klaus.
	Author URI:		http://www.sterling-adventures.co.uk/
*/

// Default options...
$notices_options = get_option('notices_widget');
if(!is_array($notices_options)) {
	// Options do not exist or have not yet been loaded so we define standard options...
	$notices_options = array(
		'title' => 'Notices',
		'credit' => 'on',
		'speed' => '4',
		'pause' => 'on',
		'limit' => '3'
	);
	update_option('notices_widget', $notices_options);
}


// Once only at plugin activation.
function activate_notices()
{
	global $wpdb, $table_prefix;

	// Add administration capability...
	$role = get_role('administrator');
	$role->add_cap('manage_notices');

	// Create MySQL database table...
	include_once(ABSPATH . '/wp-admin/upgrade-functions.php');
	$ddl = "create table " . $table_prefix . "notices (notice_ID bigint(20) NOT NULL auto_increment, active varchar(1) NOT NULL default 'Y', notice_date datetime NOT NULL, notice varchar(500) default NULL, valid smallint(2) default 3, PRIMARY KEY (notice_ID), KEY notice_date (notice_date))";
	return maybe_create_table($table_prefix . 'notices', $ddl);
}


// Help function to generate ticker output...
function get_ticker_content($limit = '')
{
	global $wpdb, $table_prefix;

	$options = get_option('notices_widget');

	if(empty($limit)) $limit = $options['limit'];

	$output = '';
	$notices = $wpdb->get_results("select notice from {$table_prefix}notices where active = 'Y' and (adddate(notice_date, valid) > now() or valid = 0) order by notice_date DESC limit {$limit}");
	if($notices) {
		$output = '<marquee class="ticker" scrollamount="' . $options['speed'] . '"' . ($options['pause'] == 'on' ? ' onmouseover="this.stop()" onmouseout="this.start()">' : '>');
		$dots = false;
		foreach($notices as $notice) {
			if($dots) $output .= ' &nbsp;&nbsp;&nbsp; ... &nbsp;&nbsp;&nbsp; ';
			$dots = true;
			$output .= '&laquo; ' . $notice->notice . ' &raquo;';
		}
		$output .= '</marquee>';
	}
	return $output;
}


// Notice widget...
function notices_widget_init()
{
	// Check widgets are activated.
	if(!function_exists('register_sidebar_widget')) return;

	// Notice widget.
	function notices_widget($args)
	{
		extract($args);

		// Get the widget control value.
		$options = get_option('notices_widget');

		echo $before_widget, $before_title, $options['title'], $after_title;
		echo get_ticker_content($options['limit']);
		echo $after_widget;
	}

	// Control for notices widget.
	function notices_widget_control()
	{
		$options = $newoptions = get_option('notices_widget');

		// This is for handing the control form submission.
		if($_POST['notices-submit']) {
			$newoptions['title'] = strip_tags(stripslashes($_POST['notices-title']));
			$newoptions['limit'] = strip_tags(stripslashes($_POST['notices-limit']));
			if($options != $newoptions) {
				update_option('notices_widget', $newoptions);
				$options = $newoptions;
			}
		}

		// Control form HTML for editing options. ?>
		<label for="notices-title" style="line-height: 35px; display: block;">Title <input type="text" name="notices-title" value="<?php echo $options['title']; ?>" /></label>
		<label for="notices-limit" style="line-height: 35px; display: block;">Limit <input type="text" name="notices-limit" value="<?php echo $options['limit']; ?>" /></label>
		<input type="hidden" name="notices-submit" value="1" />
	<?php }

	wp_register_sidebar_widget('notices', 'Notices', notices_widget, array('classname' => 'noticees_widget', 'description' => "Display a scrolling ticker of notices"));
	wp_register_widget_control('notices', 'Notices', 'notices_widget_control', array('width' => 200));
}


// Add management menu to administration interface...
function manage_notices()
{
	if(!current_user_can('manage_notices')) wp_die(__('Cheatin&#8217; uh?'));

	global $wpdb, $table_prefix;

	$msg = '';

	if(isset($_POST['submit'])) {
		$msg = 'Notice added';
		$wpdb->query("insert into {$table_prefix}notices (notice_date, notice, valid) values (now(), '" . $_POST['notice'] . "', '" . $_POST['valid'] . "')");
	}

	if(!empty($_GET['act'])) {
		switch($_GET['act']) {
		case 'update':
			$msg = "Notice {$_GET['id']} updated";
			$wpdb->query("update {$table_prefix}notices set notice_date = now(), notice = '" . $_GET['notice'] . "', active = '" . ($_GET['active'] == 'true' ? 'Y' : 'N') . "', valid = '" . $_GET['valid'] . "' where notice_ID = '{$_GET['id']}'");
			break;

		case 'delete':
			$msg = "Notice {$_GET['id']} deleted";
			$wpdb->query("delete from {$table_prefix}notices where notice_ID = '{$_GET['id']}'");
			break;
		}
	}

	// Output message.
	if(!empty($msg)) echo "<div id='message' class='updated fade'><p>{$msg}.</p></div>";
?>
	<script language="Javascript">
		function set_input_values(num)
		{
			var h = document.getElementById('href-' + num);
			h.href = h.href + '&notice=' + document.getElementById('notice-' + num).value + '&active=' + document.getElementById('active-' + num).checked + '&valid=' + document.getElementById('valid-' + num).value;
		}
	</script>

	<div class="wrap">
		<h2>Notices</h2>
		Please visit the author's site, <a href='http://www.sterling-adventures.co.uk/' title='Sterling Adventures'>Sterling Adventures</a>, and say "Hi"...

		<h3>Add Notice</h3>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=manage-notices&updated=true">
			<table class='form-table'>
				<tr>
					<td>Notice:</td>
					<td><input type="text" name="notice" size='75' maxlength="500" /></td>
					<td>Valid Days:</td>
					<td><input type="text" name="valid" size='3' value='3' /></td>
					<td><input type="submit" name="submit" value="Add Notice" class="button-secondary" style="float: right;"/></td>
				</tr>
			</table>
		</form>

		<h3>Manage Notices</h3>
		<table class='widefat'>
			<thead>
				<tr><th>ID</th><th>Notice</th><th style="text-align: center;">Active</th><th>Date</th><th>Valid</th><th colspan=2 style="text-align: center;">Action</th></tr>
			</thead>
			<tbody><?php
				$notices = $wpdb->get_results("select notice_ID ID, notice, notice_date, active, valid from {$table_prefix}notices order by notice_date DESC");
				$i = 0;
				foreach($notices as $notice) {
					printf('<tr%s>', ($i % 2 == 0 ? " class='alternate'" : ""));
					printf('<td>%s.</td>', $notice->ID);
					printf('<td><input type="text" value="%s" id="notice-%s" size="58" maxlength="500" /></td>', $notice->notice, $i);
					printf('<td style="text-align: center;"><input type="checkbox" id="active-%s" %s /></td>', $i, ($notice->active == 'Y' ? 'checked' : ''));
					printf('<td>%s</td>', mysql2date(get_option('date_format'), $notice->notice_date));
					printf('<td><input type="text" value="%s" id="valid-%s" size="3" maxlength="2" /> days</td>', $notice->valid, $i);
					printf('<td><a href="?page=manage-notices&act=update&id=%s" class="edit" onclick="set_input_values(%2$s);" id="href-%2$s">Update</a></td>', $notice->ID, $i);
					printf('<td><a href="?page=manage-notices&act=delete&id=%s" class="delete">Delete</a></td>', $notice->ID);
					printf("</tr>\n");
					$i++;
				}
			?></tbody>
		</table>

		<h3>Notices Usage</h3>
		<ul>
			<li>Define notice text above. Note, HTML is allowed but be careful to avoid <code>"</code> (double quote characters).</li>
			<li>Use the <em>Notices</em> widget (<em>Design &raquo; Widgets</em>) to show a sidebar widget that scrolls a chosen number of the most recent notices.</li>
			<li>Or use this <code>&lt;?php put_ticker( [<u>true</u> | false] ); ?&gt;</code> in your template files.  Where <code>true</code> or <code>false</code> determines if the ticker should be hidden when there are no notices to scroll.  For example, <code>&lt;?php put_ticker(false); ?&gt;</code> only shows the ticker when there are notices to scroll, whereas <code>&lt;?php put_ticker(true); ?&gt;</code> always shows the ticker - even an empty one.</li>
		</ul>
	</div>
<?php
}


// Manage options.
function notices_options_page()
{
	if(isset($_POST['option-submit'])) {
		$options_update = array (
			'credit' => ($_POST['credit'] == 'on' ? 'on' : 'off'),
			'speed' => $_POST['speed'],
			'pause' => $_POST['pause'],
			'limit' => $_POST['limit']
		);
		update_option('notices_widget', $options_update);
	}
	$options = get_option('notices_widget');
?>
	<div class="wrap">
		<h2>Notices Options</h2>
		Control the behaviour of the Notices ticker.<br />
		Please visit the author's site, <a href='http://www.sterling-adventures.co.uk/' title='Sterling Adventures'>Sterling Adventures</a>, and say "Hi"...

		<h3>Notice Options</h3>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?page=' . basename(__FILE__); ?>&updated=true">
			<table class='form-table'>
				<tr>
					<td>Limit:</td>
					<td><input type='text' name='limit' value='<?php echo $options['limit']; ?>' size='3' /></td>
					<td><small>The maximum number of most recently updated notices to show.</small></td>
				</tr>
				<tr>
					<td>Speed:</td>
					<td><input type='text' name='speed' value='<?php echo $options['speed']; ?>' size='3' /></td>
					<td><small>The speed of the ticker tape, smaller = slower.</small></td>
				</tr>
				<tr>
					<td>Pause:</td>
					<td><input type="checkbox" name="pause" <?php echo $options['pause'] == 'on' ? 'checked' : ''; ?> /></td>
					<td><small>Pause the ticker's scrolling on <code>mouseover</code>.</small></td>
				</tr>
				<tr>
					<td>Credit:</td>
					<td><input type="checkbox" name="credit" <?php echo $options['credit'] == 'on' ? 'checked' : ''; ?> /></td>
					<td><small>Includes an invisible credit to <a href='http://www.sterling-adventures.co.uk/' title='Sterling Adventures'>Sterling Adventures</a></small></td>
				</tr>
			</table>
			<p class="submit"><input type="submit" name="option-submit" value="Update Notice Options" /></p>
		</form>
	</div>
<?php
}


// Add credit.
function notices_footer()
{
	$options = get_option('notices_widget');
	if($options['credit'] == 'on') echo '<div id="notices_footer" style="display: none;"><a href="http://www.sterling-adventures.co.uk/blog/">Adventures</a></div>';
}


// Add management menu to administration interface...
function manage_notices_menu()
{
	if(function_exists('add_submenu_page')) {
		add_management_page('Manage Notices', 'Notices', 2, 'manage-notices', 'manage_notices');
	}
	if(function_exists('add_options_page')) {
		add_options_page('Notice Options', 'Notices', 8, basename(__FILE__), 'notices_options_page');
	}
}


// Output CSS styles for notices in the header...
function add_notice_styles()
{
	printf("<link rel='stylesheet' media='screen' type='text/css' href='%s/wp-content/plugins/notices/notices.css' />\n", get_settings('home'));
}


// Output the ticker out for use within template files.
function put_ticker($show = true)
{
	$ticker = get_ticker_content();
	if((!$show && !empty($ticker)) || $show) {
		print("<div class='ticker-div'>");
		echo $ticker;
		print("</div>");
	}
}


register_activation_hook(__FILE__, 'activate_notices');
add_action('admin_menu', 'manage_notices_menu');
add_action('plugins_loaded', 'notices_widget_init');
add_action('wp_head', 'add_notice_styles');
add_action('wp_footer', 'notices_footer');
?>
