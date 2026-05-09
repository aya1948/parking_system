<?php
// includes/header.php

// --- استدعاء ملفات التهيئة الأساسية (الجلسات وقاعدة البيانات) ---
require_once __DIR__ . '/../config/session.php';  // يحتوي على تعريفات BASE_URL ودوال المستخدم
require_once __DIR__ . '/../config/db.php';
$user  = currentUser();     // جلب بيانات المستخدم الحالي
$flash = getFlash();        // جلب رسائل التنبيه (نجاح/خطأ) المخزنة
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- عنوان الصفحة يتغير حسب المحتوى، والافتراضي هو Rakna -->
    <title><?= htmlspecialchars($pageTitle ?? 'Rakna') ?></title>

    <!-- روابط Bootstrap الأساسية والأيقونات -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* ========== تعريف المتغيرات العامة (قابلة للتعديل) ========== */
        :root {
            --primary: #480959;       /* اللون الأساسي للأزرار والروابط (بنفسجي غامق) */
            --dark: #480959;          /* لون خلفية الشريط العلوي (Navbar) */
            --sidebar-bg: #480959;    /* لون خلفية القائمة الجانبية */
        }

        /* ---------- تنسيق الخلفية العامة للصفحات ---------- */
        body  { background: #f4f6fb; }

        /* ---------- تنسيق الشريط العلوي (Navbar) ---------- */
        .navbar { background: var(--dark) !important; }                     /* خلفية الشريط */
        .navbar-brand { color: #fff !important; font-weight: 700; font-size: 1.4rem; } /* اسم المشروع */
        .nav-link { color: rgb(255, 255, 255) !important; }              /* الروابط */
        .nav-link:hover, .nav-link.active { color: #fff !important; }      /* الروابط عند التمرير */

        /* ---------- تنسيق القائمة الجانبية (Sidebar) ---------- */
        .sidebar {
            min-height: 100vh;                          /* يجعلها ممتدة كامل ارتفاع الصفحة */
            background: var(--sidebar-bg);              /* لون الخلفية — يمكنك تغييره هنا */
            color: #fff;                                /* لون النص العام */
            border-right: 1px solid #480959; /* خط فاصل خفيف */
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.85);              /* لون الروابط (أبيض شفاف) */
            border-radius: 8px;                         /* زوايا دائرية */
            margin: 2px 8px;                            /* مسافات خارجية */
            padding: 10px 14px;                         /* مسافات داخلية */
            transition: 0.2s;                           /* تأثير حركي سلس */
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.15);         /* خلفية خفيفة عند التمرير */
            color: #fff;                                /* نص أبيض كامل */
            border-left: 3px solid #ffffff;      /* حدود يسارية باللون الأبيض */
        }
        .sidebar .nav-link i { width: 22px; color: #d1b3ff; }  /* الأيقونات بلون فاتح */
        .sidebar hr { background-color: rgba(255,255,255,0.2); } /* الخط الفاصل شفاف */

        /* ---------- أزرار أساسية ---------- */
        .btn-primary { background: var(--primary); border-color: var(--primary); }

        /* ---------- بطاقات الإحصائيات (لها حدود ملونة) ---------- */
        .stat-card { border-left: 4px solid var(--primary); }

        /* ---------- شارات الدور (Driver / Owner / Admin) ---------- */
        .badge-role-driver { background: #480959; color: #fff; }  /* سائق */
        .badge-role-owner  { background: #480959; color: #fff; }  /* مالك موقف (تم تصحيح لون النص) */
        .badge-role-admin  { background: #480959; color: #fff; }  /* مدير */

        /* ---------- تأثير حركي عند المرور على بطاقات المواقف ---------- */
        .spot-card:hover   { transform: translateY(-2px); transition: .2s; box-shadow: 0 6px 20px rgba(0,0,0,.1); }
        /* إزالة أي هوامش بين المحتوى والفوتر */
.row.g-0 { margin: 0; }
.col-md-2, .col-md-10 { padding: 0; }
footer { margin-top: 0 !important; }
    </style>
</head>
<body>

<!-- ========== الشريط العلوي (Navbar) ========== -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid px-4">
    <!-- اسم المشروع مع أيقونة -->
    <a class="navbar-brand" href="/parking_system/index.php">
      <i class="bi bi-p-circle-fill me-2"></i>Rakna
    </a>
    <!-- زر القائمة للشاشات الصغيرة -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <!-- محتوى الشريط (روابط وأزرار) -->
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        <?php if ($user): // إذا كان المستخدم مسجل الدخول ?>
          <!-- شارة الدور (Driver, Owner, Admin) -->
          <li class="nav-item">
            <span class="badge badge-role-<?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span>
          </li>
          <!-- أيقونة الإشعارات مع عدد غير المقروء -->
          <li class="nav-item">
            <?php
            // جلب عدد الإشعارات غير المقروءة من قاعدة البيانات
            if (isLoggedIn()) {
                try {
                    $__db   = getDB();
                    $__stmt = $__db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
                    $__stmt->execute([$_SESSION['user_id']]);
                    $__unread = (int)$__stmt->fetchColumn();
                } catch(Exception $e) { $__unread = 0; }
            } else { $__unread = 0; }
            ?>
            <a class="nav-link position-relative" href="/parking_system/index.php?action=notifications">
              <i class="bi bi-bell fs-5"></i>
              <?php if ($__unread > 0): // إذا كان هناك إشعارات غير مقروءة ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                    style="font-size:10px;">
                <?= $__unread > 9 ? '9+' : $__unread ?>
              </span>
              <?php endif; ?>
            </a>
          </li>
          <!-- قائمة المستخدم المنسدلة -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['full_name']) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="/parking_system/index.php?action=profile"><i class="bi bi-person me-2"></i>View Profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="/parking_system/index.php?action=logout"><i class="bi bi-box-arrow-right me-2"></i>Log Out</a></li>
            </ul>
          </li>
        <?php else: // إذا لم يكن مسجل الدخول ?>
          <li class="nav-item"><a class="nav-link" href="/parking_system/index.php?action=login">Log In</a></li>
          <li class="nav-item"><a class="btn btn-primary btn-sm" href="/parking_system/index.php?action=register">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- ========== رسائل التنبيه (Flash Messages) ========== -->
<?php if ($flash): ?>
<div class="container-fluid px-4 mt-3">
  <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'error' ? 'danger' : 'info') ?> alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($flash['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
</div>
<?php endif; ?>