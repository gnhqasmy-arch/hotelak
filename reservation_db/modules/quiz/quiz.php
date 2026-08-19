<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
$body_class ='contact-page';
include '../../includes/header.php';

// گرفتن سوالات از دیتابیس
$stmt = $pdo->query("SELECT * FROM quiz_questions ORDER BY sort_order");
$questions = $stmt->fetchAll();
?>
<form method="POST" action="process.php">
    <?php foreach($questions as $q): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5><?= $q['question_text'] ?></h5>
            <?php
            $options = explode(',', $q['options']);
            if($q['question_type'] == 'single_choice'):
                foreach($options as $opt):
            ?>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="q<?= $q['id'] ?>" value="<?= trim($opt) ?>" required>
                    <label class="form-check-label"><?= trim($opt) ?></label>
                </div>
            <?php 
                endforeach;
            elseif($q['question_type'] == 'multi_choice'):
                foreach($options as $opt):
            ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="q<?= $q['id'] ?>[]" value="<?= trim($opt) ?>">
                    <label class="form-check-label"><?= trim($opt) ?></label>
                </div>
            <?php 
                endforeach;
            elseif($q['question_type'] == 'boolean'):
                echo '<select name="q'.$q['id'].'" class="form-control"><option value="بله">بله</option><option value="خیر">خیر</option></select>';
            else:
                echo '<input type="text" name="q'.$q['id'].'" class="form-control">';
            endif;
            ?>
        </div>
    </div>
    <?php endforeach; ?>
    <button type="submit" class="btn btn-primary">دریافت پیشنهادها</button>
</form>
<?php include '../../includes/footer.php'; ?>