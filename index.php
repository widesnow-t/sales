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
    sa.year,
    sa.month,
    st.name staff_id,
    b.name branch_id,
    sa.sale total
FROM
    branches as b
INNER JOIN
    staffs as st
ON b.id = st.branch_id
INNER JOIN
    sales as sa
ON st.id = sa.staff_id
EOM;
if ($year || $branch || $staff) {
    $where = '';
    if ($year) {
        $where = ' sa.year = :year';
    }
    if ($branch) {
        if ($where) {
            $where .= ' AND ';
        }
        $where .= 'b.id = :branch ';
    }
    if ($staff) {
        if ($where) {
        $where .= ' AND ';
        }
        $where .= 'st.id = :staff ';
    }
    $where = ' WHERE ' . $where;
}
$sql4 .= $where;
$sql4 .= ' ORDER BY sa.year ASC, sa.month asc, staff_id ASC, branch_id ASC, total ASC ';
$stmt = $dbh->prepare($sql4);
if ($year){$stmt->bindParam(':year', $year, PDO::PARAM_INT);}
if ($staff){$stmt->bindParam(':staff', $staff, PDO::PARAM_INT);}
if ($branch){$stmt->bindParam(':branch', $branch, PDO::PARAM_INT);}
$stmt->execute();
$bts = $stmt->fetchAll(PDO::FETCH_ASSOC);

//var_dump($year, $branch, $staff);
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
                            <option value="<?= $branch['id'] ?>" <?= $selected['branch'][$branch['id']]; ?>><?= $branch['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    従業員 <select name="staff">
                        <option value=""></option>
                        <?php foreach ($staffs as $staff) : ?>
                            <option value="<?= $staff['id'] ?>" <?= $selected['staff'][$staff['id']]; ?>><?= $staff['name'] ?></option>
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
                        <?php $sum += ($bt['total']); ?>
                        <tr>
                            <th><?= h($bt['year']) ?></th>
                            <th><?= h($bt['month']) ?></th>
                            <th><?= h($bt['branch_id']) ?></th>
                            <th><?= h($bt['staff_id']) ?></th>
                            <th><?= h($bt['total']) ?></th>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="total">合計:<?= number_format($sum) ?>万円</p>
        </div>

    </div>
</body>

</html>