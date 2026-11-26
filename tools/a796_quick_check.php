<?php
// A796 offline package check: no DB writes.
$root = dirname(__DIR__);
$manifestFile = $root.'/database/uat/a796_manifest.json';
if (!is_file($manifestFile)) {fwrite(STDERR,"[FAIL] Manifest missing\n");exit(1);}
$m = json_decode(file_get_contents($manifestFile), true, 512, JSON_THROW_ON_ERROR);
foreach ($m['sha256'] as $file=>$hash) {
  $path = $root.'/'.$file;
  if (!is_file($path) || hash_file('sha256', $path)!==$hash) {fwrite(STDERR,"[FAIL] $file checksum\n");exit(1);}
  echo "[PASS] SHA-256 $file\n";
}
$a = file_get_contents($root.'/database/uat/a796_rehearsal.sql');
$b = file_get_contents($root.'/database/uat/a796_commit.sql');
$a = preg_replace("/ROLLBACK;\s*SELECT 'A796_HUMANIZED_EXPANSION_REHEARSAL_ROLLED_BACK' AS result;\s*$/", '', $a);
$b = preg_replace("/COMMIT;\s*SELECT 'A796_HUMANIZED_EXPANSION_COMMITTED' AS result;\s*$/", '', $b);
if ($a===null || $b===null || $a!==$b) {fwrite(STDERR,"[FAIL] Different SQL bodies\n");exit(1);}
echo "[PASS] Rehearsal and commit payload identical\n";
if (($m['adds_total']??0)!==1684 || array_sum($m['additions_per_table']??[])!==1684) {fwrite(STDERR,"[FAIL] Count manifest\n");exit(1);}
echo "[PASS] Manifest totals 1,684 records\n";
foreach (['STOP: A796 only runs','LOCK TABLE','already present. Do not rerun','crypt(gen_random_uuid()','1684 added records'] as $x) {
 if (!str_contains(file_get_contents($root.'/database/uat/a796_commit.sql'), $x)) {fwrite(STDERR,"[FAIL] Safety check: $x\n");exit(1);}
}
echo "[PASS] Local target, lock, repeat guard, random passwords, postchecks\n";
// Regression guard: A794 includes UAT-SN005 with NULL customer_id.
// A fixed %5 modulo inserts only 10 of 12 tickets in that baseline.
$commitSql = file_get_contents($root.'/database/uat/a796_commit.sql');
if (!str_contains($commitSql, 'count(*) OVER () AS eligible_serial_count')
 || !str_contains($commitSql, '(n-1)%s.eligible_serial_count')
 || str_contains($commitSql, '(n-1)%5')
 || !str_contains($commitSql, "('service_tickets','ticket_number LIKE ''DEMO26-T%''',12)")) {
  fwrite(STDERR, "[FAIL] Service ticket eligible serial allocation regression\n"); exit(1);
}
echo "[PASS] Service tickets rotate over customer-linked serials (12/12)\n";
echo "[RESULT] A796_OFFLINE_PACKAGE_READY (PostgreSQL not executed)\n";
