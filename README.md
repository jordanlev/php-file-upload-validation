Usage example:
    
    $errors = array();
    
    $v = new FileUploadValidation('your_field_name');
    
    //IMPORTANT: always call `valid` and `size` validations
    // (even if the field isn't required or you don't actually have a max size you're checking).
    // 
    // Reason: `valid` makes sure there's no funny business going on with people trying to upload weird things,
    // and `size` will give you a useful error message in case php.ini's `upload_max_filesize`
    // or `post_max_size` settings were exceeded.
    
    if (!$v->valid()) {
    	$errors[] = 'Invalid file upload';
    }
    
    // Size strings are a number followed by "B", "K", "M", or "G" (bytes/kilobytes/megabytes/gigabytes),
    // or don't pass in anything to just check against php.ini's `upload_max_filesize` and `post_max_size` settings.
    if (!$v->size('1M')) {
        $errors[] = 'Uploaded file is too large';
    }
    
    //only call this if this file upload field is required
    if (!$v->exists()) {
    	$errors[] = 'You must upload a file';
    }
    
    //call this if you want to restrict uploaded files to specific extensions
    if (!$v->type(array('png', 'jpg', 'jpeg', 'gif'))) {
    	$errors[] = 'Uploaded file must be an image (.png, .jpg or .gif)';
    }
    
Note that this code does *not* check against actual file types (mimetypes)! Checking mimetypes is notoriously difficult, so this simple library doesn't even bother trying. Only use this code in situations where you trust the user to not upload malicious files (and even in those situations, you should be careful because they may inadvertantly upload a bad file).
