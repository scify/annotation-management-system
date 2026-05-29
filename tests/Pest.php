<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Integration');

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Browser');

$browserConfig = pest()->browser()->timeout(10000);

if (getenv('BROWSER_HEADLESS') === 'false') {
    $browserConfig->headed();
}

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', fn () => $this->toBe(1));

/*
|--------------------------------------------------------------------------
| Browser helpers
|--------------------------------------------------------------------------
*/

/**
 * Logs in via the login form and returns the resulting page.
 *
 * The Altcha captcha is disabled server-side in APP_ENV=testing (only 'required' applies).
 * We bypass the frontend JavaScript guard by dispatching a synthetic statechange event.
 */
function loginViaForm(string $username, string $password = 'password'): mixed {
    $page = visit('/login');

    // Fail early if JS errors prevent the page from loading correctly.
    $page->assertNoJavascriptErrors();

    $page->type('#username', $username)
        ->type('#password', $password);

    // Debug: confirm typing worked — if this fails, the input isn't receiving text.
    $usernameValue = $page->script("document.getElementById('username')?.value ?? 'NOT FOUND'");
    expect($usernameValue)->toBe($username, 'username input was not filled — check if React mounted and controlled inputs are wired');

    // script() returns the JS evaluation result, not $this — call it separately.
    // wait(0.1) lets React flush the captcha state update before pressing.
    $page->script("
        const widget = document.getElementById('altcha-widget');
        if (widget) {
            widget.dispatchEvent(new CustomEvent('statechange', {
                detail: { payload: 'test-token', state: 'verified' },
                bubbles: true,
            }));
        }
    ");

    return $page->wait(0.1)->press('Log in');
}
