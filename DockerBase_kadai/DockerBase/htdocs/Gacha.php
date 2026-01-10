// --- ① 抽選の準備：rate を取得 ---
$stmt = $pdo_master->query("SELECT id, name, rate FROM cards"); // rateに変更
$all_cards = $stmt->fetchAll();

// --- ② 重みの合計（total_rate）を出す ---
$total_rate = 0;
foreach ($all_cards as $card) {
    $total_rate += $card['rate']; // rateを使用
}

// --- ③ 抽選 ---
$random_number = mt_rand(1, $total_rate);

// --- ④ 判定 ---
$current_sum = 0;
$picked_card = null;
foreach ($all_cards as $card) {
    $current_sum += $card['rate']; // rateを使用
    if ($random_number <= $current_sum) {
        $picked_card = $card;
        break;
    }
}