<?php

/**
 * http://github.com/jordanlev/php-file-upload-validation
 * 
 * version 2015-08-28
 * 
 * Credit: a lot of this code was copied from the kohana 2.3 validation library
 */

class FileUploadValidation {
	
	private $file;
	
	public function __construct($field_name) {
		$this->file = array_key_exists($field_name, $_FILES) ? $_FILES[$field_name] : null;
	}
	
	/**
	 * Tests if input data is valid file type, even if no upload is present.
	 * (You should basically always call this on your file fields, even ones
	 * that aren't required -- this ensures there's no funny stuff going on).
	 */
	public function valid() {
		return (is_array($this->file)
			AND isset($this->file['error'])
			AND !is_array($this->file['error'])
			AND isset($this->file['name'])
			AND !is_array($this->file['name'])
			AND isset($this->file['type'])
			AND !is_array($this->file['type'])
			AND isset($this->file['tmp_name'])
			AND !is_array($this->file['tmp_name'])
			AND isset($this->file['size'])
			AND !is_array($this->file['size'])
			AND ((int) $this->file['error'] === UPLOAD_ERR_NO_FILE || (int) $this->file['error'] === UPLOAD_ERR_OK));
	}

	/**
	 * Tests if input data has valid upload data.
	 * 
	 * Note that when we return false, it could mean that no file was chosen to upload
	 * OR it could mean that there was an error of some kind uploading the chosen file
	 * (so write your error messages to the user accordingly).
	 */
	public function exists() {
		return (isset($this->file['tmp_name'])
			AND isset($this->file['error'])
			AND is_uploaded_file($this->file['tmp_name'])
			AND (int) $this->file['error'] === UPLOAD_ERR_OK);
	}

	/**
	 * Validation rule to test if an uploaded file is allowed by extension.
	 *
	 * NOTE: Returns TRUE (valid) if no file was uploaded or if there was some
	 *       non-type-related error with the upload (so it is safe to performe this validation
	 *       on its own, regardless of whether or not it passed the `valid` or `exists` tests).
	 */
	public function type(array $allowed_types) {
		if ((int) $this->file['error'] !== UPLOAD_ERR_OK)
			return TRUE; //some other error occurred or nothing was uploaded, so just return true without checking the type

		// Get the default extension of the file
		$extension = strtolower(substr(strrchr($this->file['name'], '.'), 1));

		// Make sure there is an extension, and that the extension is allowed
		return ( ! empty($extension) AND in_array($extension, $allowed_types) );
	}

	/**
	 * Validation rule to test if an uploaded file is allowed by file size.
	 * File sizes are defined as: SB, where S is the size (1, 15, 300, etc) and
	 * B is the byte modifier: (B)ytes, (K)ilobytes, (M)egabytes, (G)igabytes.
	 * Eg: to limit the size to 1MB or less, you would use "1M".
	 * 
	 * ALSO checks against the php.ini upload_max_filesize / post_max_size settings
	 *  (which means if the $size arg is larger than those, the validation might fail).
	 *  Passing in null for the $size arg means we will only check the php.ini settings.
	 *
	 * NOTE: Returns TRUE (valid) if no file was uploaded or if there was some
	 *       non-size-related error with the upload (so it is safe to performe this validation
	 *       on its own, regardless of whether or not it passed the `valid` or `exists` tests).
	 */
	public function size($size = null) {
		if ((int) $this->file['error'] === UPLOAD_ERR_INI_SIZE || (int) $this->file['error'] === UPLOAD_ERR_FORM_SIZE)
			return FALSE;
		
		if ((int) $this->file['error'] !== UPLOAD_ERR_OK)
			return TRUE; //some other error occurred or nothing was uploaded, so just return true without checking the size

		if (is_null($size))
			return TRUE; //we weren't given a specific size to check, so if execution made it this far then the size is valid
			
		$size = strtoupper($size);

		if ( ! preg_match('/[0-9]++[BKMG]/', $size))
			return FALSE;

		// Make the size into a power of 1024
		switch (substr($size, -1))
		{
			case 'G': $size = intval($size) * pow(1024, 3); break;
			case 'M': $size = intval($size) * pow(1024, 2); break;
			case 'K': $size = intval($size) * pow(1024, 1); break;
			default:  $size = intval($size);                break;
		}

		// Test that the file is under or equal to the max size
		return ($this->file['size'] <= $size);
	}
}