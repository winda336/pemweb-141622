<?php require 'config.php';
$s=$pdo->prepare('SELECT b.*,u.name un FROM bookings b JOIN units u ON u.id=b.unit_id WHERE booking_code=?');$s->execute([$_GET['kode']??'']);$b=$s->fetch();if(!$b){http_response_code(404);exit('Booking tidak ditemukan');}
if($_SERVER['REQUEST_METHOD']==='POST'&&$b['status']==='pending'){
 $pdo->prepare("UPDATE payments SET status='paid',method=?,reference=?,paid_at=NOW() WHERE booking_id=?")->execute([$_POST['method'],'DUMMY-'.strtoupper(bin2hex(random_bytes(4))),$b['id']]);
 $pdo->prepare("UPDATE bookings SET status='confirmed' WHERE id=?")->execute([$b['id']]);header('Location: sukses.php?kode='.$b['booking_code']);exit;}
$title='Pembayaran';include 'header.php';?>
<section><div class="wrap"><div class="box"><h2>Pembayaran (Dummy)</h2><p><b><?=e($b['un'])?></b> ×<?=$b['qty_units']?><br><?=e($b['check_in'])?> s/d <?=e($b['check_out'])?> (<?=$b['nights']?> malam)<br>Asuransi: <?=$b['has_insurance']?'Ya ('.rp($b['insurance_fee']).')':'Tidak'?></p><p class="harga">Total: <?=rp($b['total_amount'])?></p>
<?php if($b['status']==='pending'):?><form method="post"><label>Metode<select name="method"><option value="dummy_transfer">Transfer (dummy)</option><option value="dummy_qris">QRIS (dummy)</option></select></label><br><button class="btn">Bayar Sekarang</button></form><?php else:?><div class="ok">Booking sudah <?=e($b['status'])?>.</div><?php endif;?></div></div></section><?php include 'footer.php';
