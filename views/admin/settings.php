<?php
/**
 * 
 * @file menu.php
 * @description This page is responsibe for the main menu of the plugin
 * 
 * */

// Security Check
if(!defined('ABSPATH')) die();
global $gb_site_backup;
$backup_frequency = array('houorly', 'twice-daily', 'daily', 'gbsb_weekly', 'gbsb_bi_weekly', 'gbsb_monthly');
if( isset($_POST) && !empty($_POST) ){
	
	if( ! wp_verify_nonce( $_POST['gbsp_settings_nonce'] ,'gbsp_settings_nonce_action') || !current_user_can( 'manage_options' ) ) wp_die();
	
	if( isset( $_POST['gb-backup-database'] ) && $_POST['gb-backup-database'] == 'on' ) {
		$_POST['gb-backup-database'] = $_POST['gb-backup-database'];
		$gb_site_backup->options['gb-backup-database'] = $_POST['gb-backup-database'];
	} else unset($_POST['gb-backup-database']);
	
	if( isset( $_POST['gb-backup-files'] ) && $_POST['gb-backup-files'] == 'on' ) {
		$_POST['gb-backup-files'] = $_POST['gb-backup-files'];
		$gb_site_backup->options['gb-backup-files'] = $_POST['gb-backup-files'];
	} else unset($_POST['gb-backup-files']);
	
	$_POST['gb-backup-frequency'] = intval($_POST['gb-backup-frequency']) && in_array($_POST['gb-backup-frequency'], $backup_frequency) ? $_POST['gb-backup-frequency'] : $backup_frequency[3]; 
	$gb_site_backup->options['gb-backup-frequency'] = $_POST['gb-backup-frequency'];
	
	$_POST['gb-number-of-backups'] = intval($_POST['gb-number-of-backups']) ? $_POST['gb-number-of-backups'] : 5;
	$gb_site_backup->options['gb-number-of-backups'] = $_POST['gb-number-of-backups'];
	
	$_POST['gb-exclude-folders'] = sanitize_text_field( $_POST['gb-exclude-folders'] );
	$gb_site_backup->options['gb-exclude-folders'] = $_POST['gb-exclude-folders'];
	
	$_POST['gb-email-notification'] = sanitize_email( $_POST['gb-email-notification'] );
	$gb_site_backup->options['gb-email-notification'] = $_POST['gb-email-notification'];
	
	wp_clear_scheduled_hook( 'gb_site_backup_action_hook' );
	wp_schedule_event( current_time( 'timestamp' ), $gb_site_backup->options['gb-backup-frequency'], 'gb_site_backup_action_hook');
	
}
//auto::  pr($gb_site_backup->options);
?>

<div class='container'>
	
	<div class='row wpb-header'><?php include 'partials/header.php'?></div>
	
	<div class='row wpb-body'>
		
		<div class='col-md-8 wpb-content'>
			
			<div class='row'>
				<div class='col-md-12'>
					
					<div class="panel panel-default">
						<!-- Default panel contents -->
						<div class="panel-heading">Settings</div>
						<div class="panel-body">
							<form method='post' action=''>
								<?php wp_nonce_field( 'gbsp_settings_nonce_action', 'gbsp_settings_nonce' ); ?>
								<h4>Backup</h4>
								<input id='gb-backup-database_id' type='checkbox' name='gb-backup-database' <?php if( isset($gb_site_backup->options['gb-backup-database']) ) echo "checked"; ?>>
								<label for="gb-backup-database_id">Database</label>
								
								<input id='gb-backup-files_id' type='checkbox' name='gb-backup-files' <?php if( isset($gb_site_backup->options['gb-backup-files']) ) echo "checked"; ?>>
								<label for="gb-backup-files_id">Files & Folders</label>
								<br>
								<small>Buck up database or files or both</small>
								
								<br>
								<br>
								
								<h4>Backup Frequency</h4>
								<select id='gb-backup-frequency_id' name='gb-backup-frequency'>
									<option value='hourly' <?php if( isset($gb_site_backup->options['gb-backup-frequency']) && $gb_site_backup->options['gb-backup-frequency'] == 'hourly' ) echo "selected"; ?>>Hourly</option>
									<option value='twice-daily' <?php if( isset($gb_site_backup->options['gb-backup-frequency']) && $gb_site_backup->options['gb-backup-frequency'] == 'twice-daily' ) echo "selected"; ?>>Twice Daily</option>
									<option value='daily' <?php if( isset($gb_site_backup->options['gb-backup-frequency']) && $gb_site_backup->options['gb-backup-frequency'] == 'daily' ) echo "selected"; ?>>Daily</option>
									<option value='gbsb_weekly' <?php if( isset($gb_site_backup->options['gb-backup-frequency']) && $gb_site_backup->options['gb-backup-frequency'] == 'gbsb_weekly' ) echo "selected"; ?>>Weekly</option>
									<option value='gbsb_bi_weekly' <?php if( isset($gb_site_backup->options['gb-backup-frequency']) && $gb_site_backup->options['gb-backup-frequency'] == 'gbsb_bi_weekly' ) echo "selected"; ?>>Bi-weekly</option>
									<option value='gbsb_monthly' <?php if( isset($gb_site_backup->options['gb-backup-frequency']) && $gb_site_backup->options['gb-backup-frequency'] == 'gbsb_monthly' ) echo "selected"; ?>>Monthly</option>
								</select>
								<br>
								<small>How frequently would you like to backup your website?</small>
								
								<br>
								<br>
								
								<h4>Number of backups</h4>
								<input type='number' name='gb-number-of-backups' value='<?php echo $gb_site_backup->options['gb-number-of-backups']; ?>'>
								<br>
								<small>How many backup you want to store on your website?</small>
								
								<br>
								<br>
								
								<h4>Exclude Folder</h4>
								<input type='text' name='gb-exclude-folders' value='<?php echo $gb_site_backup->options['gb-exclude-folders']; ?>'>
								<br>
								<small>Comma separate the path of folders that you don't want to back up.</small>
								<br>
								<br>
								
								<h4>Receive Email Notification</h4>
								<input type='email' name='gb-email-notification' value='<?php echo $gb_site_backup->options['gb-email-notification']; ?>'>
								<br>
								<small>If email notification is needed to be sent.</small>
								<br>
								<br>
								<input type='submit' class="btn btn-primary btn-lg" value='Save'>
							</form>
							
						</div>
						
					</div>
					
				</div>
				
			</div>
			
		</div>
		
		<div class='col-md-4 wpb-sidebar'><?php include 'partials/sidebar.php'?></div>
	
	</div>
	
	<div class='row wpb-footer text-center'><?php include 'partials/footer.php'?></div>

</div>
