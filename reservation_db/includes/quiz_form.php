<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../../config/database.php';
require_once '../../config/functions.php';


include '../../includes/header.php';

$stmt = $pdo->query("SELECT * FROM quiz_questions ORDER BY sort_order");
$questions = $stmt->fetchAll();

if (count($questions) == 0) {
    echo '<div class="alert alert-danger">هیچ سوالی در دیتابیس وجود ندارد.</div>';
    include '../../includes/footer.php';
    exit;
}

// شناسه سوال نوع اقامتگاه (عدد را با ID واقعی جایگزین کنید)
$type_question_id = 16; // ← این عدد را با ID واقعی جایگزین کنید
?>

<div class="container py-4">

    <h2 class="text-center mb-4">✨ دستیار سفر هوشمند</h2>
    <p class="text-center text-muted mb-4">با پاسخ به سوالات زیر، بهترین اقامتگاه را به شما پیشنهاد می‌دهیم.</p>
    
    <form action="process.php" method="POST" class="quiz-form" autocomplete="off">
        <?php foreach ($questions as $q): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($q['question_text']) ?></h5>
                    
                    <?php
                    // ====== سوال نوع اقامتگاه (با ID دقیق) ======
                    if ($q['id'] == $type_question_id):
                    ?>
                       <div class="form-check">
    <input class="form-check-input" type="radio" name="q<?= $q['id'] ?>" value="ویلا" required>
    <label class="form-check-label">ویلا</label>
</div>
<div class="form-check">
    <input class="form-check-input" type="radio" name="q<?= $q['id'] ?>" value="سوئیت">
    <label class="form-check-label">سوئیت</label>
</div>
<div class="form-check">
    <input class="form-check-input" type="radio" name="q<?= $q['id'] ?>" value="کلبه">
    <label class="form-check-label">کلبه</label>
</div>
<div class="form-check">
    <input class="form-check-input" type="radio" name="q<?= $q['id'] ?>" value="خانه">
    <label class="form-check-label">خانه</label>
</div>
<div class="form-check">
    <input class="form-check-input" type="radio" name="q<?= $q['id'] ?>" value="آپارتمان">
    <label class="form-check-label">آپارتمان</label>
</div>
<div class="form-check">
    <input class="form-check-input" type="radio" name="q<?= $q['id'] ?>" value="">
    <label class="form-check-label">همه موارد</label>
</div>
                    <?php
                    else:
                        // سایر سوالات با گزینه‌های دیتابیس
                        $options = explode(',', $q['options']);
                        if ($q['question_type'] == 'single_choice'):
                            foreach ($options as $opt):
                    ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q<?= $q['id'] ?>" value="<?= trim($opt) ?>" required>
                            <label class="form-check-label"><?= trim($opt) ?></label>
                        </div>
                    <?php 
                            endforeach;
                        elseif ($q['question_type'] == 'multi_choice'):
                            foreach ($options as $opt):
                    ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="q<?= $q['id'] ?>[]" value="<?= trim($opt) ?>">
                            <label class="form-check-label"><?= trim($opt) ?></label>
                        </div>
<?php 
                            endforeach;
                        elseif ($q['question_type'] == 'boolean'):
                    ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q<?= $q['id'] ?>" value="بله" required>
                            <label class="form-check-label">بله</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q<?= $q['id'] ?>" value="خیر">
                            <label class="form-check-label">خیر</label>
                        </div>
                    <?php
                        elseif ($q['question_type'] == 'text'):
                    ?>
                        <input type="text" name="q<?= $q['id'] ?>" class="form-control" placeholder="پاسخ خود را وارد کنید...">
                    <?php
                        elseif ($q['question_type'] == 'range'):
                    ?>
                        <input type="number" name="q<?= $q['id'] ?>" class="form-control" placeholder="مقدار را وارد کنید...">
                    <?php
                        elseif ($q['question_type'] == 'date'):
                    ?>
                        <input type="text" name="q<?= $q['id'] ?>" class="form-control datepicker" placeholder="تاریخ را انتخاب کنید...">
                    <?php
                        endif;
                    endif;
                    ?>
                </div>
            </div>
            
        <?php endforeach; ?>
        
        <button type="submit" class="btn btn-primary btn-lg w-100">دریافت پیشنهادها</button>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>