
[ ![Codeship Status for partner-it/b2-php](https://codeship.com/projects/47347300-705f-0133-dfe5-0204a723cae7/status?branch=master)](https://codeship.com/projects/116533) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/partner-it/b2-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/partner-it/b2-php/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/partner-it/b2-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/partner-it/b2-php/?branch=master)

# Backblaze B2 PHP wrapper

## Installation

```
composer require partner-it/b2-php
```

## Setup

Instantiate a new client and get a token:

```php

$client = new \B2\B2Client('accountid', 'applicationKey');
$client->requestToken();

```

### Upload a file

```php

$client->Files->uploadFile('bucketId', '/my/local/path/image.jpg', 'image.jpg', 'image/jpeg');

```

### Dowload a file

Download a file by name:

```php

$data = $b2Client->Files->downloadFileByName('bucketname', 'image.jpg');

```
