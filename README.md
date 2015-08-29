Usage example:
    
    $errors = array();
    
    $v = new FileUploadValidation('your_field_name');
    
    //always call this (even if the field isn't required)
    if (!$v->valid()) {
    	$errors[] = 'Invalid file upload';
    }
    
    //only call this if this file upload field is required
    if (!$v->exists()) {
    	$errors[] = 'You must upload a file';
    }
    
    //call this if you want to restrict uploaded files to specific extensions
    if (!$v->types(array('png', 'jpg', 'jpeg', 'gif'))) {
    	$errors[] = 'Uploaded file must be an image (.png, .jpg or .gif)';
    }
    
    //You should always call this
    // (in case the `valid` call failed due to php.ini `upload_max_filesize`
    // or `post_max_sizemax` settings, so user knows why it was invalid).
    //Optionally, you can also pass in a size string to further limit the max file size.
    // Size strings are a number followed by "B", "K", "M", or "G" (bytes/kilobytes/megabytes/gigabytes).
	if (!$v->size('1M')) {
		$errors[] = 'Uploaded file is too large';
	}
