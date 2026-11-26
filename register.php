<?php
require 'config.php';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $n = trim($_POST['name']);
    $m = trim($_POST['email']);
    $p = $_POST['password'];

    if (!$n || !filter_var($m, FILTER_VALIDATE_EMAIL) || strlen($p) < 6) {
        $err = 'Isi nama, email valid, dan password minimal 6 karakter.';
    } else {
        $c = $pdo->prepare('SELECT 1 FROM users WHERE email=?');
        $c->execute([$m]);

        if ($c->fetch()) {
            $err = 'Email sudah terdaftar.';
        } else {
            $pdo->prepare("INSERT INTO users(name,email,phone,password_hash,role,is_active) VALUES(?,?,?,?, 'user',1)")
                ->execute([$n, $m, $_POST['phone'], password_hash($p, PASSWORD_DEFAULT)]);

            header('Location: login.php?baru=1');
            exit;
        }
    }
}

$title = 'Daftar';
include 'header.php';
?>

<section>
    <div class="wrap">
        <div class="box">
            <h2>Daftar Akun</h2>

            <?php if ($err): ?>
                <div class="err"><?= e($err) ?></div>
            <?php endif; ?>

            <form method="post">
                <label>Nama
                    <input name="name" required>
                </label>

                <label>Email
                    <input type="email" name="email" required>
                </label>

                <label>Telepon
                    <input name="phone">
                </label>

                <label>Password
                    <input type="password" name="password" required>
                </label>

                <br>

                <button class="btn">Daftar</button>
            </form>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
