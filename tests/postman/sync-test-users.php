<?php

// Explicit, local-only account sync: source is read-only, target is always calcupost_test.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['pulse.enabled' => false]);
$sourceConfig = config('database.connections.mysql');
$sourceConfig['database'] = 'calcupost';
$sourceConfig['url'] = null;
$testConfig = $sourceConfig;
$testConfig['database'] = 'calcupost_test';
config(['database.connections.account_source' => $sourceConfig, 'database.connections.account_test' => $testConfig]);
$source = Illuminate\Support\Facades\DB::connection('account_source');
$target = Illuminate\Support\Facades\DB::connection('account_test');
if ($source->getDatabaseName() !== 'calcupost' || $target->getDatabaseName() !== 'calcupost_test') {
    throw new RuntimeException('Unexpected database: refusing account sync.');
}
$columns = ['name', 'email', 'password', 'is_admin', 'email_verified_at', 'deleted_at'];
$accounts = $source->table('users')->get($columns);
$apply = in_array('--apply', $argv, true);
if ($apply) {
    $target->transaction(function () use ($target, $accounts) {
        foreach ($accounts as $account) {
            $values = (array) $account;
            // Copy the existing hash exactly; never rehash or expose it, and never copy sessions/tokens.
            $values['remember_token'] = null;
            $values['updated_at'] = now();
            $existing = $target->table('users')->where('email', $account->email)->first(['id']);
            if ($existing) {
                $target->table('users')->where('id', $existing->id)->update($values);
            } else {
                $values['created_at'] = now();
                $target->table('users')->insert($values);
            }
        }
    });
}
$summary = [];
foreach ($accounts as $account) {
    $copy = $target->table('users')->where('email', $account->email)->first();
    $same = $copy && hash_equals($account->password, $copy->password)
        && (bool) $account->is_admin === (bool) $copy->is_admin
        && $account->deleted_at === $copy->deleted_at;
    if ($apply && !$same) {
        throw new RuntimeException('Account verification failed.');
    }
    $summary[] = ['email' => $account->email, 'admin' => (bool) $account->is_admin,
        'active' => $account->deleted_at === null, 'exists_in_test' => (bool) $copy,
        'credentials_match' => (bool) $same,
        // Only check the password already supplied by the user, for their known admin account.
        'known_admin_password_matches' => $account->email === 'admin@admin.test'
            ? Illuminate\Support\Facades\Hash::check('admin', $account->password) : null];
}
echo json_encode(['mode' => $apply ? 'synced' : 'read-only', 'accounts' => $summary],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
