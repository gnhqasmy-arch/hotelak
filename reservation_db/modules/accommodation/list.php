<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../../config/database.php';
require_once '../../config/functions.php';
$body_class ='contact-page';
include '../../includes/header.php';

// دریافت همه اقامتگاه‌های فعال (بدون فیلتر)
$stmt = $pdo->prepare("
    SELECT a.*, c.name as city_name 
    FROM accommodations a
    JOIN cities c ON a.city_id = c.id
    WHERE a.is_active = 1
    ORDER BY a.price_per_night ASC
");
$stmt->execute();
$accommodations = $stmt->fetchAll();
?>



<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="fw-bold">🏠 همه اقامتگاه‌ها</h1>
            <p class="text-muted"><?= count($accommodations) ?> اقامتگاه موجود است</p>
        </div>
    </div>

    <div class="row">
        <?php if (count($accommodations) == 0): ?>
            <div class="col-12">
                <div class="alert alert-warning text-center py-5">
                    هیچ اقامتگاهی یافت نشد.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($accommodations as $acc): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card accommodation-card h-100">
                        <?php 
                        $imgStmt = $pdo->prepare("SELECT image_url FROM accommodation_gallery WHERE accommodation_id = ? AND is_cover = 1 LIMIT 1");
                        $imgStmt->execute([$acc['id']]);
                        $cover = $imgStmt->fetch();
                        $imgSrc = $cover ? BASE_URL . $cover['image_url'] : BASE_URL . '/uploads/default.jpg';
                        ?>
                        <img src="<?= $imgSrc ?>" class="card-img-top" height="200" style="object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($acc['title']) ?></h5>
                            <p class="card-text text-muted"><i class="fas fa-map-marker-alt"></i> <?= $acc['city_name'] ?></p>
                            <p class="card-text"><i class="fas fa-users"></i> ظرفیت: <?= $acc['max_guests'] ?> نفر</p>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="price-badge"><?= number_format($acc['price_per_night']) ?> تومان/شب</span>
                                <a href="<?= BASE_URL ?>/modules/accommodation/single.php?id=<?= $acc['id'] ?>" class="btn btn-sm btn-outline-primary">رزرو <i class="fas fa-arrow-left"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>