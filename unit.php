<?php require 'config.php';$title='Daftar Unit';include 'header.php';
$us=$pdo->query('SELECT * FROM units WHERE is_active=1 ORDER BY price_per_night')->fetchAll();?>
<section><div class="wrap"><h2>Daftar Unit</h2><p class="sub">Pilih unit lalu tentukan tanggal menginap.</p><div class="grid">
<?php foreach($us as $x):?><div class="card"><div class="img"></div><div class="body"><h3><?=e($x['name'])?></h3><p class="meta">Kategori: <?=e($x['category'])?> • <?=e($x['capacity'])?> tamu</p><p><?=e($x['description'])?></p><p class="harga"><?=rp($x['price_per_night'])?> / malam</p><a class="btn" href="booking.php?unit=<?=$x['id']?>">Pesan</a></div></div><?php endforeach;?>
<?php if(!$us):?><p>Belum ada unit.</p><?php endif;?></div></div></section>
<?php include 'footer.php';
