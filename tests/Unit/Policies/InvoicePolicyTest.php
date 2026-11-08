<?php

use App\Enums\Role;
use App\Models\Invoice;
use App\Models\User;
use App\Policies\InvoicePolicy;

it('allows every invoice ability to an admin', function () {
    $policy = new InvoicePolicy;
    $admin = new User;
    $admin->role = Role::Admin;
    $invoice = new Invoice;

    expect($policy->viewAny($admin))->toBeTrue()
        ->and($policy->view($admin, $invoice))->toBeTrue()
        ->and($policy->create($admin))->toBeTrue()
        ->and($policy->update($admin, $invoice))->toBeTrue()
        ->and($policy->delete($admin, $invoice))->toBeTrue()
        ->and($policy->deleteAny($admin))->toBeTrue()
        ->and($policy->restore($admin, $invoice))->toBeTrue()
        ->and($policy->forceDelete($admin, $invoice))->toBeTrue();
});

it('denies every invoice ability to a non admin', function () {
    $policy = new InvoicePolicy;
    $user = new User;
    $user->role = Role::User;
    $invoice = new Invoice;

    expect($policy->viewAny($user))->toBeFalse()
        ->and($policy->view($user, $invoice))->toBeFalse()
        ->and($policy->create($user))->toBeFalse()
        ->and($policy->update($user, $invoice))->toBeFalse()
        ->and($policy->delete($user, $invoice))->toBeFalse()
        ->and($policy->deleteAny($user))->toBeFalse()
        ->and($policy->restore($user, $invoice))->toBeFalse()
        ->and($policy->forceDelete($user, $invoice))->toBeFalse();
});
