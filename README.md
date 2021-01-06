# google-drive

- [Installation](#installation)
- [Usage](#usage)

## Installation

``` sh
composer require w3lifer/google-drive
```

## Usage

``` php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use w3lifer\google\GoogleDrive;

$googleDrive = new GoogleDrive([
    'pathToCredentials' => __DIR__ . '/credentials.json', // Required
    'pathToToken' => __DIR__ . '/token.json', // Required
]);

$googleDrive->upload(
    __DIR__ . '/test.txt',  // Required
    [
        '<folder id>', // Optional
        '<folder id>', // Optional
    ]
);

$folderId = $googleDrive->createFolder('Folder name');
```
