<?php

// The initial setting allows checking the filename extension.
// The controls will be case insensitive (lowercase).
// As the mimetype may not be provided by the html client, or may not be standard,
// the initial setting here doesn't use the mimetypes.

// Note #1:
// It's nevertheless possible to use mimetype to check uploaded file types.
// To do so, fill in the ALLOWED_MIME_TYPE array here after.
// Example : ALLOWED_MIME_TYPE = ["text/plain","image/gif"];

// Note #2:
// If you insert data in both ALLOWED_FILE_EXT and ALLOWED_MIME_TYPE,
// the system will check FIRST the extensions (and stop uploading if file extension doesn't match).
// If you want to use mimetypes only, you can empty the ALLOWED_FILE_EXT array.

// ------
// uploaded files: allowed extensions
// ------
// Note here the extensions allowed (without '.') or use empty array [] to allow all extensions

const ALLOWED_FILE_EXT = [
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
];

// ------
// uploaded files: allowed mimetypes
// ------
// Note here the mimetypes allowed or use empty array [] to allow all mimetypes

const ALLOWED_MIME_TYPE = [];