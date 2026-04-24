<?php
require_once("lib/system_load.php");
authenticate_user('admin');

$image_obj = new Images();
$message = "";

/* ADD IMAGE */
if(isset($_POST['add_image']) && $_POST['add_image'] == '1'){
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $file = $_FILES['image_file'];

    if(empty($title)){
        $message = "Title is required";
    } elseif(empty($file['name'])){
        $message = "Please upload an image file";
    } else {
        if(empty($message)){
            $image_obj->add_image($title, $description, $file, null);
            header("Location: images.php?added=1");
            exit;
        }
    }
}

/* EDIT MODE LOAD */
$edit_data = null;
if(isset($_GET['edit'])){
    $edit_data = $image_obj->get_image($_GET['edit']);
}

/* UPDATE IMAGE */
if(isset($_POST['update_image']) && $_POST['update_image'] == '1'){
    $id = $_POST['edit_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $file = $_FILES['image_file'];

    if(empty($title)){
        $message = "Title is required";
    } else {
        if(empty($message)){
            $image_obj->update_image($id, $title, $description, $file, null);
            header("Location: images.php?updated=1");
            exit;
        }
    }
}

$page_title = $edit_data ? "Edit Image" : "Add New Image";
require_once("lib/includes/header.php");
?>

<?php if(isset($_GET['added'])): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    Image Added Successfully
    <button type="button" class="close" data-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if($message != ""): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= $message ?>
    <button type="button" class="close" data-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row flex-row">
    <div class="col-12">
        <div class="widget has-shadow">
            <div class="widget-header bordered no-actions d-flex align-items-center">
                <h4><?= $page_title ?></h4>
            </div>
            <div class="widget-body">
                <div class="container">
                    <form method="post" enctype="multipart/form-data">
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label>Image Title *</label>
                                <input type="text" name="title" class="form-control" value="<?= $edit_data['title'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label>Upload Image *</label>
                                <input type="file" name="image_file" class="form-control" <?= !$edit_data ? 'required' : '' ?>>
                                <small class="text-muted">Recommended Size: <b>1280 x 720 (16:9)</b> for a perfect fit.</small>
                                <?php if($edit_data && $edit_data['image_file']): ?>
                                    <small class="text-muted">Current: <a href="assets/upload/images/<?= $edit_data['image_file'] ?>" target="_blank"><?= $edit_data['image_file'] ?></a></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="3"><?= $edit_data['description'] ?? '' ?></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-right">
                                <?php if($edit_data): ?>
                                    <input type="hidden" name="edit_id" value="<?= $edit_data['id'] ?>">
                                    <input type="hidden" name="update_image" value="1">
                                    <button type="submit" class="btn btn-golden">Update Image</button>
                                <?php else: ?>
                                    <input type="hidden" name="add_image" value="1">
                                    <button type="submit" class="btn btn-golden">Add Image</button>
                                <?php endif; ?>
                                <a href="images.php" class="btn btn-outline-dark">View All</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once("lib/includes/footer.php"); ?>
