<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        // Clear Laravel caches before each test suite
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
    }
}
