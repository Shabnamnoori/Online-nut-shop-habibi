<?php

require_once __DIR__ . "/../includes/config.php";

$password = password_hash("12345678", PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
    INSERT INTO users
    (
        role_id,
        first_name,
        last_name,
        email,
        phone,
        password,
        status
    )
    VALUES
    (
        1,
        ?,
        ?,
        ?,
        ?,
        ?,
        'active'
    )
");

$stmt->execute([
    "مدیر",
    "حبیبی",
    "admin@habibi.ir",
    "09120000000",
    $password
]);

echo "حساب ادمین با موفقیت ساخته شد.";