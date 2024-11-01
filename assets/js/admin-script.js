/**
 * 
 * @file admin-script.js
 * @description All the script here will be included in admin panel
 * 
 * */

/**
 * 
 * @var GB_SB_BACKEND
 * @description Global variable to hold every element on the backend script
 * 
 * */
GB_SB_BACKEND = {};

(function($){
$(document).ready(function(){

// Scanning backend files
$('#gb-backup-now').on('click', function(){
	
	$('#gb-backup-now').html('Creating Backup. Please don\'t close this window.');
	
	var data = {
		action: 'full_backup',
		_gb_security: _GB_SECURITY[0],
	};
	$.post(
		GB_AJAXURL, 
		data, 
		function(response){
			alert(response);
			window.location.reload();	
		}
	);
});

GB_SB_BACKEND.add_file = function(){
	
	$('#panel-heading').html('Creating Backup File('+ Math.ceil( 100 * ( GB_SB_BACKEND.I / GB_SB_BACKEND.file_info['number-files'] ) ) +'% done)')
	
	output = '<tr><td>'+ GB_SB_BACKEND.file_info['files'][GB_SB_BACKEND.I]['path'] +'</td></tr>';
	
	var data = {
		action: 'full_backup',
		command: 'backup',
		file_path: GB_SB_BACKEND.file_info['files'][GB_SB_BACKEND.I]['path'],
		archive_file_path: GB_SB_BACKEND.file_info['archive-file-path'],
		_gb_security: _GB_SECURITY[0],
	};
	
	$.post(
		GB_AJAXURL, 
		data, 
		function(response){
			
			if(response) $('#gb-backup-statistics-display table').html(output);
			GB_SB_BACKEND.I++;
			if(GB_SB_BACKEND.I < GB_SB_BACKEND.file_info['number-files']) window.setTimeout(GB_SB_BACKEND.add_file, 0);
			
		}
	);
};

/**
 * 
 * @function delete_file
 * @description delete the archive file
 * @param file :: Name of the file to be deleted.
 * @return void
 * 
 * */
GB_SB_BACKEND.delete_file = function(file){
	
	confirm("Are you sure you want to delete file?");
	
	var data = {
		action: 'delete_file',
		file: file,
		_gb_security: _GB_SECURITY[0],
	};
	
	$.post(
		GB_AJAXURL,
		data,
		function(response){
			if(response) window.location.reload();
				else alert("Can\'t Delete file.")
		}
	);
}

/**
 * 
 * @function delete_file
 * @description delete the archive file
 * @param file :: Name of the file to be deleted.
 * @return void
 * 
 * */
GB_SB_BACKEND.restore = function(file){
	
	if(!confirm("Are you sure you want to restore backup?"))
	{console.log('Exited'); return;}
	
	var data = {
		action: 'restore',
		file: file,
		_gb_security: _GB_SECURITY[0],
	};
	
	$.post(
		GB_AJAXURL,
		data,
		function(response){
			console.log(response);
			if(response == true) {
				console.log(response);
				alert("Restored Successfully.");
				window.location.reload();
			}else alert("Can\'t restore file.")
		}
	);
}

})
})(jQuery);
