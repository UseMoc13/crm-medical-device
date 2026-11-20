<?php
/** Pure smoke tests; no Laravel bootstrap and no DB access. */
declare(strict_types=1);
require dirname(__DIR__).'/app/Support/CrmAnnouncementExposurePolicy.php';
use App\Support\CrmAnnouncementExposurePolicy as P;
$cases = [
    ['empty database has no current references', 0, 0, false, 'NO_CURRENT_STORED_ASSET_REFERENCES'],
    ['empty database with public link', 0, 0, true, 'NO_CURRENT_STORED_ASSET_REFERENCES'],
    ['published-only content still needs policy', 2, 0, true, 'REVIEW_PUBLIC_ASSET_VISIBILITY_POLICY'],
    ['published-only without link still needs policy', 2, 0, false, 'REVIEW_PUBLIC_ASSET_VISIBILITY_POLICY'],
    ['hidden assets and public link hold', 5, 2, true, 'HOLD_HIDDEN_PUBLIC_DISK_REFERENCES'],
    ['hidden assets without link need review', 5, 2, false, 'REVIEW_HIDDEN_REFERENCES_PUBLIC_SERVING_NOT_VERIFIED'],
    ['all assets hidden and link hold', 2, 2, true, 'HOLD_HIDDEN_PUBLIC_DISK_REFERENCES'],
    ['one hidden reference still matters', 100, 1, true, 'HOLD_HIDDEN_PUBLIC_DISK_REFERENCES'],
];
$passed=0;
foreach($cases as [$label,$all,$hidden,$link,$expected]) {
    if(P::decide($all,$hidden,$link)!==$expected) {
        fwrite(STDERR,"[FAIL] {$label}\n"); exit(1);
    }
    echo "[PASS] {$label}\n";
    $passed++;
}
foreach ([[-1,0],[1,-1],[1,2]] as [$all,$hidden]) {
    try { P::decide($all,$hidden,false); fwrite(STDERR,"[FAIL] invalid counts accepted\n"); exit(1); }
    catch(InvalidArgumentException $e) { echo "[PASS] invalid counts rejected\n"; $passed++; }
}
echo "A7.7.4.3 smoke: {$passed}/11 PASS. No DB access.\n";
