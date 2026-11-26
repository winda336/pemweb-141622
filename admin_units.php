<?php
require 'config.php';
need(['staff', 'admin']);
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['hapus'])) {
        try {
            $pdo->prepare('DELETE FROM units WHERE id=?')->execute([$_POST['hapus']]);
            $msg = 'Unit dihapus.';
        } catch (PDOException $x) {
            $msg = 'Unit sudah punya booking, nonaktifkan saja.';
        }
    } else {
        $d = [
            $_POST['name'],
            $_POST['category'],
            $_POST['description'],
            (int)$_POST['capacity'],
            (int)$_POST['total_units'],
            (float)$_POST['price'],
            isset($_POST['aktif']) ? 1 : 0
        ];

        if ($_POST['id']) {
            $pdo->prepare('UPDATE units SET name=?,category=?,description=?,capacity=?,total_units=?,price_per_night=?,is_active=? WHERE id=?')->execute([...$d, $_POST['id']]);
        } else {
            $pdo->prepare('INSERT INTO units(name,category,description,capacity,total_units,price_per_night,is_active) VALUES(?,?,?,?,?,?,?)')->execute($d);
        }

        $msg = 'Tersimpan.';
    }
}

$ed = [
    'id' => '',
    'name' => '',
    'category' => 'dome',
    'description' => '',
    'capacity' => 2,
    'total_units' => 1,
    'price_per_night' => 0,
    'is_active' => 1
];

if (isset($_GET['edit'])) {
    $s = $pdo->prepare('SELECT * FROM units WHERE id=?');
    $s->execute([$_GET['edit']]);
    $ed = $s->fetch() ?: $ed;
}

$us = $pdo->query('SELECT * FROM units ORDER BY id')->fetchAll();
$title = 'Kelola Unit';
include 'header.php';
?>

<section>
    <div class="wrap">
        <h2>Kelola Unit</h2>

        <?php if ($msg): ?>
            <div class="ok"><?= e($msg) ?></div>
        <?php endif; ?>

        <div style="overflow-x:auto">
            <table class="tbl">
                <tr>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Tarif</th>
                    <th>Aktif</th>
                    <th></th>
                </tr>

                <?php foreach ($us as $x): ?>
                    <tr>
                        <td><?= e($x['name']) ?></td>
                        <td><?= e($x['category']) ?></td>
                        <td><?= $x['total_units'] ?></td>
                        <td><?= rp($x['price_per_night']) ?></td>
                        <td><?= $x['is_active'] ? 'Ya' : 'Tidak' ?></td>
                        <td>
                            <a href="?edit=<?= $x['id'] ?>">Ubah</a>
                            <form method="post" style="display:inline" onsubmit="return confirm('Hapus?')">
                                <button name="hapus" value="<?= $x['id'] ?>">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <br>

        <div class="box">
            <h3><?= $ed['id'] ? 'Ubah' : 'Tambah' ?> Unit</h3>

            <form method="post">
                <input type="hidden" name="id" value="<?= e($ed['id']) ?>">

                <label>Nama
                    <input name="name" value="<?= e($ed['name']) ?>" required>
                </label>

                <label>Kategori
                    <input name="category" value="<?= e($ed['category']) ?>">
                </label>

                <label>Deskripsi
                    <textarea name="description"><?= e($ed['description']) ?></textarea>
                </label>

                <label>Kapasitas
                    <input type="number" name="capacity" value="<?= e($ed['capacity']) ?>">
                </label>

                <label>Stok unit
                    <input type="number" name="total_units" value="<?= e($ed['total_units']) ?>">
                </label>

                <label>Tarif / malam
                    <input type="number" name="price" value="<?= e($ed['price_per_night']) ?>">
                </label>

                <label>
                    <input type="checkbox" name="aktif" <?= $ed['is_active'] ? 'checked' : '' ?>>
                    Tampil di katalog
                </label>

                <br>
                <button class="btn">Simpan</button>
            </form>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
