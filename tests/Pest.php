<?php

declare(strict_types=1);

use Pest\Expectation;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Exporter\Exporter;

/*
|--------------------------------------------------------------------------
| Groups of Tests
|--------------------------------------------------------------------------
*/

uses()->group('unit')->in('Unit');
uses()->group('integration')->in('Integration');
