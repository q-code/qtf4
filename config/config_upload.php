<?php

// The initial setting allows checking the filename extension, (not the mimetype).
// As the mimetype may not be provided by the html client, or may not be standard,
// the initial setting of the application doesn't use the mimetypes.

// Note #1:
// It's nevertheless possible to use mimetype to check uploaded file types.
// To do so, fill in the ALLOWED_MIME_TYPE array here after.
// Example : ALLOWED_MIME_TYPE = array("text/plain","image/gif");

// Note #2:
// If you insert data in both ALLOWED_FILE_EXT and ALLOWED_MIME_TYPE,
// the system will check FIRST the file extensions (and stop uploading if file extension doesn't match).
// If you want to use mimetypes only, you can empty the ALLOWED_FILE_EXT array.

// ------
// uploaded files: allowed extensions.
// ------
// Note here the extensions allowed. Use empty array() to allow all extensions
// Must be lowercase, without dot.

const ALLOWED_FILE_EXT = array(
'csv',
'doc','docx',
'gif',
'htm','html',
'inc',
'jpg','jpeg',
'js',
'log',
'pdf',
'php',
'png',
'pps','ppt','pptx',
'rar','tar',
'rtf','txt','text',
'xls','xlsx',
'xml',
'zip'
);

// ------
// uploaded files: allowed mimetypes.
// ------
// Note here the mimetypes allowed. Use empty array() to allow all mimetypes
// Must be lowercase.

const ALLOWED_MIME_TYPE = [];