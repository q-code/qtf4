<?php

// To add a new language, add a subfolder (iso code) with the translations in /language/,
// then add the corresponding key-value pair here to create the menu.

const LANGUAGES = [
'en' => 'EN English',
'fr' => 'FR Français',
'nl' => 'NL Nederlands'
];

// The key (iso-code) must be the /language/ subfolder name.
// The value is used to display the menu, where the first part is the menu label and the rest (after the space) is the help tips.

// Display order in the menu is just the order of the entries in this array.
// Even if you don't use translations, you must have at least: const LANGUAGES = ['en' => 'EN English'];