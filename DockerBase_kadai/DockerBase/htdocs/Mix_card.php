<?php
require_once 'common.php';
require_once 'Mix_process.php';
$uid = $_SESSION['user_id'];
$base_mid = $_POST['base_master_id'] ?? null;
$materials = $_POST['materials'] ?? [];

$result = executeMixing($uid, $base_mid, $materials);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>合成結果</title>
    <style>
        body { text-align: center; padding-top: 50px; font-family: sans-serif; background: #f4f7f6; }
        .res-box { display: inline-block; padding: 40px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); background: white; }
        .success { border: 5px solid #4caf50; }
        .fail { border: 5px solid #f44336; }
        .btn { display: inline-block; margin-top: 25px; padding: 12px 25px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="res-box <?= $result['status'] ?>">
        <?php if ($result['status'] === 'success'): ?>
            <h1 style="color: #2e7d32;">✨ 強化成功！ ✨</h1>
            <p style="font-size: 1.5em;"><strong><?= htmlspecialchars($result['new_name']) ?></strong> を獲得！</p>
        <?php elseif ($result['status'] === 'fail'): ?>
            <h1 style="color: #c62828;">💀 強化失敗...</h1>
            <p>素材は消滅しましたが、ベースカードは無事です。</p>
        <?php else: ?>
            <h1>エラー</h1><p><?= htmlspecialchars($result['message']) ?></p>
        <?php endif; ?>
        <br>
        <a href="My_card.php" class="btn">マイカードへ戻る</a>
    </div>
</body>
</html>