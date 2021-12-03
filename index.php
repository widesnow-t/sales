<?php
require_once __DIR__ . '/functions.php';
$dbh = connect_db();

$sql = 'SELECT * FROM branches';
$stmt = $dbh->prepare($sql);
$stmt->execute();
$branches = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql2 = 'SELECT * FROM staffs';
$stmt = $dbh->prepare($sql2);
$stmt->execute();
$staffs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql3 = 'SELECT * FROM sales';
$stmt = $dbh->prepare($sql3);
$stmt->execute();
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

$year = '';
$branch = '';
$staff = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $year = filter_input(INPUT_GET, 'year');
    $branch = filter_input(INPUT_GET, 'branch');
    $staff = filter_input(INPUT_GET, 'staff');
}
$selected['staff'][$staff ?: ""] = "selected";
$selected['branch'][$branch ?: ""] = "selected";


$sql4 = <<<EOM
SELECT
    q.year,
    q.month,
    p.name staff_id,
    o.name branch_id,
    q.sale
FROM
    branches as o
INNER JOIN
    staffs as p
ON o.id = p.branch_id
INNER JOIN
    sales as q
ON p.id = q.staff_id
WHERE
    q.year = :q.year,
    p.name = :p.name

EOM;
$stmt = $dbh->prepare($sql4);
$stmt->bindParam(':q.year', $year, PDO::PARAM_INT);
$stmt->bindParam(':p.name', $branch, PDO::PARAM_STR);
$stmt->execute();
$bts = $stmt->fetchAll(PDO::FETCH_ASSOC);

var_dump($year, $branch, $staff);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/ress@3.0.0/dist/ress.min.css">
    <link rel="stylesheet" href="style.css">
    <title>売上一覧</title>
</head>

<body>
    <header>
        <h1 class="title">売上一覧</h1>
    </header>
    <div class="wrapper">
        <div class="user-wrapper">
            <div class="form-area">
                <form action="index.php" method="GET">
                    年 <input type="number" name="year" value="<?= $year; ?>">
                    支店 <select name="branch">
                        <option value=""></option>
                        <?php foreach ($branches as $branch) : ?>
                            <option value="<?= $branch['name'] ?>" <?= $selected['branch'][$branch['name']]; ?>><?= $branch['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    従業員 <select name="staff">
                        <option value=""></option>
                        <?php foreach ($staffs as $staff) : ?>
                            <option value="<?= $staff['name'] ?>" <?= $selected['staff'][$staff['name']]; ?>><?= $staff['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
            </div>
            <div>
                <input type="submit" class="btn submit-btn" name="submit" value="検索">
            </div>
            </form>
        </div>
        <div class="incomplete-area">
            <table class="plan-list">
                <thead>
                    <tr>
                        <th class="plan-title">年</th>
                        <th class="plan-title">月</th>
                        <th class="plan-title">支店</th>
                        <th class="plan-title">従業員</th>
                        <th class="plan-title">売上</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bts as $bt) : ?>
                        <tr>
                            <th><?= h($bt['year']) ?></th>
                            <th><?= h($bt['month']) ?></th>
                            <th><?= h($bt['branch_id']) ?></th>
                            <th><?= h($bt['staff_id']) ?></th>
                            <th><?= h($bt['sale']) ?></th>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="total">合計：<p><?= $bt['sale'] ?>万円</p>
            </div>
        </div>

    </div>
</body>

</html>