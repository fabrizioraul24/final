<?php

$chromeCandidates = [
    sprintf('%s\\Google\\Chrome\\Application\\chrome.exe', env('PROGRAMFILES', 'C:\\Program Files')),
    sprintf('%s\\Google\\Chrome\\Application\\chrome.exe', env('PROGRAMFILES(X86)', 'C:\\Program Files (x86)')),
    sprintf('%s\\Opera\\opera.exe', env('PROGRAMFILES', 'C:\\Program Files')),
    sprintf('%s\\Opera\\opera.exe', env('PROGRAMFILES(X86)', 'C:\\Program Files (x86)')),
    sprintf('C:\\Users\\%s\\AppData\\Local\\Programs\\Opera\\opera.exe', env('USERNAME')),
];

$defaultChromePath = collect($chromeCandidates)->first(function ($path) {
    return $path && file_exists($path);
});

return [
    /*
     * Here you can configure default Browsershot settings that will be applied
     * to all PDF generation. These settings can still be overridden using the
     * withBrowsershot() method on individual PDF instances.
     */
    'browsershot' => [
        /*
         * Configure the paths to Node.js, npm, Chrome, and other binaries.
         * Leave null to use system defaults or Browsershot's auto-detection.
         */
        'node_binary' => env('LARAVEL_PDF_NODE_BINARY'),
        'npm_binary' => env('LARAVEL_PDF_NPM_BINARY'),
        'include_path' => env('LARAVEL_PDF_INCLUDE_PATH'),
        'chrome_path' => env('LARAVEL_PDF_CHROME_PATH', $defaultChromePath),
        'node_modules_path' => env('LARAVEL_PDF_NODE_MODULES_PATH'),
        'bin_path' => env('LARAVEL_PDF_BIN_PATH'),
        'temp_path' => env('LARAVEL_PDF_TEMP_PATH'),

        /*
         * Other Browsershot configuration options.
         */
        'write_options_to_file' => env('LARAVEL_PDF_WRITE_OPTIONS_TO_FILE', false),
        'no_sandbox' => env('LARAVEL_PDF_NO_SANDBOX', false),
    ],
];
