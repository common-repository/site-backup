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
//auto::  echo sanitize_file_name('gb_auto_1489902748_site_backup.zip');
//auto::  pr(_get_cron_array());
?>

<div class='container'>
	
	<div class='row wpb-header'><?php include 'partials/header.php'?></div>
	
	<div class='row wpb-body'>
		
		<div class='col-md-8 wpb-content'>
			
			<div class='row'>
				<div class='col-md-12'>
					<div class="jumbotron text-center">
						<button id="gb-backup-now" class="btn btn-primary btn-lg" href="#" role="button">Backup Now</button>
					</div>
				</div>
								
				<div class='col-md-12 gb-show-backup-statistics'>
					
					<div class="panel panel-default">
						<!-- Default panel contents -->
						<div id="panel-heading" class="panel-heading">Scanning Statistics</div>
						
						<div class="panel-body" id="gb-backup-statistics-display">
							
						</div>
						
					</div>
					
				</div>
				
				<div class='col-md-12'>
					<table class='table'>
						<tr>
							<th>name</th>
							<th>Created</th>
							<th>Action</th>
						</tr>
						<?php
						
							$backup_files = scandir($gb_site_backup->upload_path);
							
						?>
						<?php foreach($backup_files as $file): if($file != '.' && $file != '..'): 
							$re = '/.sql$/';
							$str = $file;
							preg_match_all($re, $str, $matches);
							if(!empty($matches[0])) continue;

						?>
						<tr>
							<td><?php echo $file; ?></td>
							<td><?php $data = explode('_', $file); echo date('d/m/Y', $data[2]); ?></td>
							<td>
								<button type="button" onClick='GB_SB_BACKEND.restore("<?php echo $file; ?>");' class="btn btn-success btn-sm"><span class='glyphicon glyphicon-retweet'></span> Restore</button>
								<button type="button" onClick='GB_SB_BACKEND.delete_file("<?php echo $file; ?>");' class="btn btn-danger btn-sm"><span class='glyphicon glyphicon-trash'></span> Delete</button>
							</td>
						</tr>
						<?php endif; endforeach; ?>
					</table>
				</div>
				
			</div>
			
		</div>
		
		<div class='col-md-4 wpb-sidebar'><?php include 'partials/sidebar.php'?></div>
	
	</div>
	
	<div class='row wpb-footer'><?php include 'partials/footer.php'?></div>

</div>
