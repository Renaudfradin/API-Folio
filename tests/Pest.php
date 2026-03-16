<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test case is the time where you may
| register any services or resolve any dependencies that your tests
| may need. For example, you may need to load some middleware.
|
*/

uses(TestCase::class)->in('Feature');
uses(RefreshDatabase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that certain values
| meet specific conditions. The "expect()" function gives you access
| to a set of matchers that help you make these assertions.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code
| that you don't want to repeat in every test. Here you can also expose
| helpers as global functions to help you to reduce the number of lines
| in your test files.
|
*/

function something()
{
    // ..
}
