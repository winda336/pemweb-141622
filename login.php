<?php
require 'config.php';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s = $pdo->prepare('SELECT * FROM users WHERE email=? AND is_active=1');
    $s->execute([trim($_POST['email'])]);
    $r = $s->fetch();

    if ($r && password_verify($_POST['password'], $r['password_hash'])) {
        session_regenerate_id(true);

        $_SESSION['u'] = [
            'id' => $r['id'],
            'name' => $r['name'],
            'role' => $r['role']
        ];

        header('Location: ' . ($r['role'] === 'user' ? 'index.php' : 'admin_units.php'));
        exit;
    }

    $err = 'Email atau password salah.';
}

$title = 'Masuk';
include 'header.php';
?>

<section>
    <div class="wrap">
        <div class="box">
            <h2>Masuk</h2>

            <?php if (isset($_GET['baru'])): ?>
                <div class="ok">Akun dibuat, silakan masuk.</div>
            <?php endif; ?>

            <?php if ($err): ?>
                <div class="err"><?= e($err) ?></div>
            <?php endif; ?>

            <form method="post">
                <label>Email
                    <input type="email" name="email" required>
                </label>

                <label>Password
                    <input type="password" name="password" required>
                </label>

                <br>

                <button class="btn">Masuk</button>
                <a href="register.php">Belum punya akun?</a>
            </form>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
