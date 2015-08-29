<?php

/* version 2015-08-28 */

//You should always call the `valid` test (even if the field isn't required).
//Then you can optionally call the `exists`, `type`, and/or `size` tests as needed.
//Note that the code is structured in such a way that you should always call all desired tests
// (as opposed to only calling `type` or `size` if the `exists` test passes).

//Credit: a lot of this code was copied from the kohana 2.3 validation library

class ValidateFileUpload {

	/**
	 * Tests if input data is valid file type, even if no upload is present.
	 * (You should basically always call this on your file fields, even ones
	 * that aren't required -- this ensures there's no funny stuff going on).
	 *
	 * @param   array  $_FILES item
	 * @return  bool
	 */
	public static function valid($file) {
		return (is_array($file)
			AND isset($file['error'])
			AND !is_array($file['error'])
			AND isset($file['name'])
			AND !is_array($file['name'])
			AND isset($file['type'])
			AND !is_array($file['type'])
			AND isset($file['tmp_name'])
			AND !is_array($file['tmp_name'])
			AND isset($file['size'])
			AND !is_array($file['size'])
			AND ((int) $file['error'] === UPLOAD_ERR_NO_FILE || (int) $file['error'] === UPLOAD_ERR_OK));
	}

	/**
	 * Tests if input data has valid upload data.
	 * 
	 * Note that when we return false, it could mean that no file was chosen to upload
	 * OR it could mean that there was an error of some kind uploading the chosen file
	 * (so write your error messages to the user accordingly).
	 *
	 * @param   array    $_FILES item
	 * @return  bool
	 */
	public static function exists(array $file) {
		return (isset($file['tmp_name'])
			AND isset($file['error'])
			AND is_uploaded_file($file['tmp_name'])
			AND (int) $file['error'] === UPLOAD_ERR_OK);
	}

	/**
	 * Validation rule to test if an uploaded file is allowed by extension.
	 *
	 * NOTE: Returns TRUE (valid) if no file was uploaded or if there was some
	 *       non-type-related error with the upload (so it is safe to performe this validation
	 *       on its own, regardless of whether or not it passed the `valid` or `exists` tests).
	 *
	 * @param   array    $_FILES item
	 * @param   array    allowed file extensions
	 * @return  bool
	 */
	public static function type(array $file, array $allowed_types) {
		if ((int) $file['error'] !== UPLOAD_ERR_OK)
			return TRUE; //some other error occurred or nothing was uploaded, so just return true without checking the type

		// Get the default extension of the file
		$extension = strtolower(substr(strrchr($file['name'], '.'), 1));

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
	 *
	 * @param   array    $_FILES item
	 * @param   string   maximum file size
	 * @return  bool
	 */
	public static function size(array $file, $size = null) {
		if ((int) $file['error'] === UPLOAD_ERR_INI_SIZE || (int) $file['error'] === UPLOAD_ERR_FORM_SIZE)
			return FALSE;
		
		if ((int) $file['error'] !== UPLOAD_ERR_OK)
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
		return ($file['size'] <= $size);
	}
}