<?php require 'config.php';$err='';
$us=$pdo->query('SELECT * FROM units WHERE is_active=1')->fetchAll();
$v=$_POST+['unit_id'=>$_GET['unit']??'','check_in'=>'','check_out'=>'','qty'=>1,'name'=>$_SESSION['u']['name']??'','email'=>'','phone'=>''];
if($_SERVER['REQUEST_METHOD']==='POST'){
 $s=$pdo->prepare('SELECT * FROM units WHERE id=? AND is_active=1');$s->execute([$v['unit_id']]);$un=$s->fetch();
 $n=(int)round((strtotime($v['check_out'])-strtotime($v['check_in']))/86400);$q=(int)$v['qty'];
 if(!$un||!trim($v['name'])||!filter_var($v['email'],FILTER_VALIDATE_EMAIL)||!trim($v['phone']))$err='Lengkapi data dengan benar.';
 elseif($n<1||strtotime($v['check_in'])<strtotime('today'))$err='Tanggal tidak valid (check-out harus setelah check-in, tidak boleh lampau).';
 elseif($q<1||$q*1>sisa($pdo,$un['id'],$v['check_in'],$v['check_out']))$err='Unit tidak cukup untuk tanggal tersebut.';
 else{$as=isset($_POST['asuransi'])?1:0;$sub=$q*$n*$un['price_per_night'];$fee=$as?round($sub*0.10):0;
  $kode='GLM-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(2)));
  $pdo->prepare("INSERT INTO bookings(booking_code,user_id,unit_id,check_in,check_out,nights,qty_units,guest_name,contact_email,contact_phone,has_insurance,insurance_fee,total_amount,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,'pending')")
   ->execute([$kode,$_SESSION['u']['id']??null,$un['id'],$v['check_in'],$v['check_out'],$n,$q,$v['name'],$v['email'],$v['phone'],$as,$fee,$sub+$fee]);
  $pdo->prepare("INSERT INTO payments(booking_id,amount,status) VALUES(?,?,'pending')")->execute([$pdo->lastInsertId(),$sub+$fee]);
  header('Location: bayar.php?kode='.$kode);exit;}}
$title='Booking';include 'header.php';?>
<section><div class="wrap"><div class="box"><h2>Form Booking</h2><?php if($err):?><div class="err"><?=e($err)?></div><?php endif;?>
<form method="post"><label>Unit<select name="unit_id" id="u"><?php foreach($us as $x):?><option value="<?=$x['id']?>" data-p="<?=$x['price_per_night']?>" <?=$v['unit_id']==$x['id']?'selected':''?>><?=e($x['name'])?> - <?=rp($x['price_per_night'])?>/malam</option><?php endforeach;?></select></label>
<label>Check-in<input type="date" name="check_in" id="a" value="<?=e($v['check_in'])?>" min="<?=date('Y-m-d')?>" required></label><label>Check-out<input type="date" name="check_out" id="b" value="<?=e($v['check_out'])?>" required></label>
<label>Jumlah unit<input type="number" name="qty" id="q" min="1" value="<?=e($v['qty'])?>"></label><label>Nama<input name="name" value="<?=e($v['name'])?>" required></label><label>Email<input type="email" name="email" value="<?=e($v['email'])?>" required></label><label>Telepon<input name="phone" value="<?=e($v['phone'])?>" required></label>
<label><input type="checkbox" name="asuransi" id="i"> Pakai asuransi (+10%, refund 100% jika batal &gt; 3 hari sebelum check-in, 50% jika ≤ 3 hari)</label>
<p class="harga">Total: <span id="t">Rp 0</span></p><button class="btn">Lanjut Bayar</button></form></div></div></section>
<script>const $=i=>document.getElementById(i);function h(){const n=Math.round((new Date($('b').value)-new Date($('a').value))/864e5),p=$('u').selectedOptions[0].dataset.p;let t=n>0?n*p*(+$('q').value||0):0;if($('i').checked)t=Math.round(t*1.1);$('t').textContent='Rp '+t.toLocaleString('id-ID')}['u','a','b','q','i'].forEach(i=>$(i).addEventListener('input',h));h()</script>
<?php include 'footer.php';
