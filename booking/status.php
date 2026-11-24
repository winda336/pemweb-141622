<?php require 'config.php';$b=null;$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){$k=trim($_POST['kode']);$c=trim($_POST['kontak']);
 $s=$pdo->prepare('SELECT b.*,u.name un FROM bookings b JOIN units u ON u.id=b.unit_id WHERE booking_code=? AND (contact_email=? OR contact_phone=?)');$s->execute([$k,$c,$c]);$b=$s->fetch();if(!$b)$err='Data tidak ditemukan. Periksa kode dan email/telepon.';}
$title='Cek Status';include 'header.php';?>
<section><div class="wrap"><div class="box"><h2>Cek Status Booking</h2><?php if($err):?><div class="err"><?=e($err)?></div><?php endif;?>
<form method="post"><label>Kode booking<input name="kode" required></label><label>Email atau telepon<input name="kontak" required></label><br><button class="btn">Cek</button></form>
<?php if($b):?><hr><p><b><?=e($b['booking_code'])?></b><br><?=e($b['un'])?> ×<?=$b['qty_units']?><br><?=e($b['check_in'])?> s/d <?=e($b['check_out'])?><br>Total: <?=rp($b['total_amount'])?><br>Status: <b><?=e($b['status'])?></b></p><?php endif;?></div></div></section><?php include 'footer.php';
