<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ui.php';

require_login();

$title = 'Notifications';
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (post('action') === 'save' && $isAdmin) {
            db_execute(
                "INSERT INTO notifications (
                    notification_id,
                    user_id,
                    title,
                    detail,
                    type
                ) VALUES (
                    notification_seq.NEXTVAL,
                    NULL,
                    :p_title,
                    :p_detail,
                    :p_type
                )",
                [
                    'p_title' => post('notification_title'),
                    'p_detail' => post('detail'),
                    'p_type' => post('type')
                ]
            );

            flash('Notification created successfully.');
        } elseif (post('action') === 'delete' && $isAdmin) {
            db_execute(
                "DELETE FROM notifications
                 WHERE notification_id = :p_notification_id",
                [
                    'p_notification_id' => post('notification_id')
                ]
            );

            flash('Notification deleted.');
        }
    } catch (Exception $ex) {
        flash($ex->getMessage(), 'error');
    }

    redirect('notifications.php');
}

/*
  Admin sees all notifications.
  Students/users see public notifications plus notifications assigned to their own user_id.
*/
if ($isAdmin) {
    $rows = db_fetch_all(
        "SELECT
            notification_id,
            user_id,
            title,
            detail,
            type,
            TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI') AS created_at
         FROM notifications
         ORDER BY created_at DESC"
    );
} else {
    $rows = db_fetch_all(
        "SELECT
            notification_id,
            user_id,
            title,
            detail,
            type,
            TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI') AS created_at
         FROM notifications
         WHERE user_id IS NULL
            OR user_id = :p_logged_user_id
         ORDER BY created_at DESC",
        [
            'p_logged_user_id' => $_SESSION['user_id']
        ]
    );
}

include __DIR__ . '/includes/header.php';
?>

<div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-slate-500">
  <span>Common</span>
  <span>/</span>
  <span class="font-medium text-slate-800">Notifications</span>
</div>

<?php section_header('Notifications', 'Stay informed about deadlines, reviews, approvals, and publication activity', ''); ?>

<?php if ($isAdmin): ?>
  <div class="card mb-6 p-5">
    <h3 class="text-lg font-semibold">Create Notification</h3>

    <form method="post" class="mt-5 grid gap-4 md:grid-cols-3">
      <input type="hidden" name="action" value="save">

      <?php input_field('Title', 'notification_title'); ?>
      <?php input_field('Type', 'type', 'review'); ?>

      <label class="md:col-span-3">
        <span class="form-label">Detail</span>
        <textarea name="detail" class="input" rows="3"></textarea>
      </label>

      <div class="md:col-span-3">
        <button class="btn btn-primary">Save Notification</button>
      </div>
    </form>
  </div>
<?php endif; ?>

<div class="grid gap-4">
  <?php foreach ($rows as $r): ?>
    <?php
      $notificationType = strtolower($r['type'] ?? '');
      $icon = '📋';

      if ($notificationType === 'approval') {
          $icon = '✅';
      } elseif ($notificationType === 'deadline') {
          $icon = '⚠️';
      } elseif ($notificationType === 'publication') {
          $icon = '🔖';
      } elseif ($notificationType === 'review') {
          $icon = '📋';
      }
    ?>

    <div class="card p-5">
      <div class="flex gap-4">
        <div class="rounded-2xl bg-sky-50 p-3 text-xl text-sky-700">
          <?= $icon ?>
        </div>

        <div class="min-w-0 flex-1">
          <p class="font-semibold text-slate-900"><?= e($r['title']) ?></p>
          <p class="mt-1 text-slate-500"><?= e($r['detail']) ?></p>
          <p class="mt-2 text-xs text-slate-400">
            <?= e($r['created_at']) ?>
            <?php if ($isAdmin && !empty($r['user_id'])): ?>
              · User: <?= e($r['user_id']) ?>
            <?php elseif ($isAdmin): ?>
              · Public
            <?php endif; ?>
          </p>
        </div>

        <?php if ($isAdmin): ?>
          <form method="post" onsubmit="return confirmDelete('Delete notification?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="notification_id" value="<?= e($r['notification_id']) ?>">
            <button class="btn btn-danger px-3 py-2">🗑</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if (empty($rows)): ?>
    <div class="card p-5 text-center text-slate-500">
      No notifications found.
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
