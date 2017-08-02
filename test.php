<?php
include('referrals.class.php');
include('settings.php');
$test= new referral(56165,42972,20120103,$mysqli,$months_apart,$percentage);
print_r($test);
$test->make_voucher();

?>
