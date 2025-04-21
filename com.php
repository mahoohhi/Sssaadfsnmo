<?php
$uploadBase = "bots/";
if (!is_dir($uploadBase)) mkdir($uploadBase, 0777, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectName = trim($_POST['project_name']);
    $files = $_FILES['files'];

    if (empty($projectName)) {
        $error = "يرجى إدخال اسم المشروع.";
    } else {
        // منع الأحرف غير الآمنة في اسم المجلد
        $safeName = preg_replace("/[^a-zA-Z0-9-_]/", "_", $projectName);
        $projectFolder = $uploadBase . $safeName . "/";

        if (!is_dir($projectFolder)) {
            mkdir($projectFolder, 0777, true);
        }

        $uploaded = false;
        for ($i = 0; $i < count($files['name']); $i++) {
            $filename = basename($files['name'][$i]);
            $targetPath = $projectFolder . $filename;

            if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                $uploaded = true;
            }
        }

        if ($uploaded) {
            header("Location: index.php?uploaded=1");
            exit;
        } else {
            $error = "فشل في رفع الملفات.";
        }
    }
}

// حذف مشروع
if (isset($_GET['delete'])) {
    $folder = basename($_GET['delete']);
    $path = $uploadBase . $folder;

    if (is_dir($path)) {
        foreach (glob("$path/*") as $file) {
            unlink($file);
        }
        rmdir($path);
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>مدير استضافة البوتات</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white p-5">

<div class="container">
  <h1 class="text-center mb-4">رفع مشروع بوت جديد</h1>

  <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php elseif (isset($_GET['uploaded'])): ?>
    <div class="alert alert-success">تم رفع المشروع بنجاح!</div>
  <?php endif; ?>

  <!-- نموذج رفع مشروع -->
  <form action="" method="POST" enctype="multipart/form-data" class="mb-4">
    <div class="mb-3">
      <label>اسم المشروع:</label>
      <input type="text" name="project_name" class="form-control" placeholder="مثال: mybot123" required>
    </div>
    <div class="mb-3">
      <label>اختر ملفات المشروع:</label>
      <input type="file" name="files[]" class="form-control" multiple required>
    </div>
    <button type="submit" class="btn btn-primary w-100">رفع</button>
  </form>

  <hr class="text-light">

  <h3 class="mb-3">المشاريع المرفوعة:</h3>
  <ul class="list-group">
    <?php
      $projects = array_filter(glob($uploadBase . '*'), 'is_dir');
      foreach ($projects as $dir):
        $folder = basename($dir);
    ?>
      <li class="list-group-item bg-secondary text-white d-flex justify-content-between align-items-center">
        <span><?= $folder ?></span>
        <div>
          <a href="<?= $dir ?>" class="btn btn-sm btn-success" target="_blank">عرض</a>
          <a href="?delete=<?= urlencode($folder) ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل تريد حذف المشروع؟');">حذف</a>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
</div>

</body>
</html>