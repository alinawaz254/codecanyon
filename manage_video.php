<?php
require_once("lib/system_load.php");
authenticate_user('admin');

$video_obj = new Video();
$message = "";

/* =========================
   ADD VIDEO
========================= */
if(isset($_POST['add_video']) && $_POST['add_video'] == '1'){

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $video_url = trim($_POST['video_url']);
    $video_type = $_POST['video_type'] ?? '';
    $file = $_FILES['video_file'];

    if(empty($title)){
        $message = "Title is required";
    } else {

        $allowed = ['mp4','mov','avi','webm'];

        // VALIDATION BASED ON TYPE
        if($video_type == 'upload'){

            if(empty($file['name'])){
                $message = "Please upload a video file";
            } else {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if(!in_array($ext, $allowed)){
                    $message = "Invalid video format";
                }
            }

            // force URL null
            $video_url = NULL;

        } elseif($video_type == 'url'){

            if(empty($video_url)){
                $message = "Please enter video URL";
            }

            // ignore file
            // $file = ['name' => ''];
             $file = null;

        } else {
            $message = "Please select video type";
        }

        // SAVE
        if(empty($message)){
            $video_obj->add_video($title, $description, $file, $video_url);
            header("Location: manage_video.php?added=1");
            exit;
        }
    }
}

/* =========================
   EDIT MODE LOAD
========================= */
$edit_data = null;

if(isset($_GET['edit'])){
    $edit_data = $video_obj->get_video($_GET['edit']);
}


/* =========================
   UPDATE VIDEO
========================= */
if(isset($_POST['update_video']) && $_POST['update_video'] == '1'){

    $id = $_POST['edit_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $video_url = trim($_POST['video_url']);
    $video_type = $_POST['video_type'] ?? '';
    $file = $_FILES['video_file'];

    if(empty($title)){
        $message = "Title is required";
    } else {

        $allowed = ['mp4','mov','avi','webm'];

        if($video_type == 'upload'){

            if(!empty($file['name'])){
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if(!in_array($ext, $allowed)){
                    $message = "Invalid video format";
                }
            }

            $video_url = NULL;

        } elseif($video_type == 'url'){

            if(empty($video_url)){
                $message = "Please enter video URL";
            }

            $file = ['name' => ''];

        }

        if(empty($message)){
            $video_obj->update_video($id, $title, $description, $file, $video_url);
            header("Location: videos.php?updated=1");
            exit;
        }
    }
}

$page_title = "Add New Video";
require_once("lib/includes/header.php");
?>

<!-- SUCCESS MESSAGE -->
<?php if(isset($_GET['added'])): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    Video Added Successfully
    <button type="button" class="close" data-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row flex-row">
    <div class="col-12">

        <!-- FORM -->
        <div class="widget has-shadow">
            <div class="widget-header bordered no-actions d-flex align-items-center">
                <h4>Add New Video</h4>
            </div>

            <div class="widget-body">
                <div class="container">

                    <form method="post" enctype="multipart/form-data">

                        <!-- VIDEO TYPE -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label>Select Video Type *</label><br>

                                <?php 
                                $type = 'upload';
                                if(!empty($edit_data['video_url'])) $type = 'url';
                                ?>

                                <label class="mr-3">
                                    <input type="radio" name="video_type" value="upload"
                                        <?= $type=='upload'?'checked':'' ?>> Upload Video
                                </label>

                                <label>
                                    <input type="radio" name="video_type" value="url" <?= $type=='url'?'checked':'' ?>>
                                    Video URL
                                </label>
                            </div>
                        </div>

                        <div class="row">

                            <!-- TITLE -->
                            <div class="col-md-6 mb-4">
                                <label>Video Title *</label>
                                <input type="text" name="title" class="form-control"
                                    value="<?= $edit_data['title'] ?? '' ?>" required>
                            </div>

                            <!-- FILE -->
                             <div class="col-md-6 mb-4" id="upload_box">
                                 <label>Upload Video</label>
                                 <input type="file" name="video_file" class="form-control">
                                 <small class="text-muted d-block">Allowed: mp4, mov, avi, webm</small>
                                 <small class="text-muted">Recommended: <b>1280 x 720 (16:9)</b> for a perfect fit.</small>
                             </div>

                            <!-- URL -->
                            <div class="col-md-6 mb-4" id="url_box" style="display:none;">
                                <label>Video URL</label>
                                <input type="text" name="video_url" class="form-control"
                                    value="<?= $edit_data['video_url'] ?? '' ?>">
                            </div>

                        </div>
                        <div class="row">
                            <!-- DESCRIPTION -->
                            <div class="col-md-12 mb-4">
                                <label>Description</label>
                                <textarea name="description" class="form-control"
                                    rows="3"><?= $edit_data['description'] ?? '' ?></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-right">

                            <?php if($edit_data): ?>

                            <input type="hidden" name="edit_id" value="<?= $edit_data['id'] ?>">
                            <input type="hidden" name="update_video" value="1">

                            <button type="submit" class="btn btn-golden">
                                Update Video
                            </button>
                            <a href="videos.php" class="btn btn-outline-dark">
                                View All
                            </a>
                            <?php else: ?>

                            <input type="hidden" name="add_video" value="1">

                            <button type="submit" class="btn btn-golden">
                                Add Video
                            </button>
                            <a href="videos.php" class="btn btn-outline-dark">
                                View All
                            </a>
                            <?php endif; ?>

                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>

    </div>
</div>

<!-- TOGGLE SCRIPT -->
<script>
document.addEventListener("DOMContentLoaded", function() {

    const uploadBox = document.getElementById("upload_box");
    const urlBox = document.getElementById("url_box");

    const fileInput = document.querySelector("input[name='video_file']");
    const urlInput = document.querySelector("input[name='video_url']");

    const radios = document.querySelectorAll("input[name='video_type']");

    radios.forEach(radio => {
        radio.addEventListener("change", function() {

            if (this.value === "upload") {
                uploadBox.style.display = "block";
                urlBox.style.display = "none";

                fileInput.disabled = false;
                urlInput.disabled = true;

            } else {
                uploadBox.style.display = "none";
                urlBox.style.display = "block";

                fileInput.disabled = true;
                urlInput.disabled = false;
            }

        });
    });

});
</script>

<?php require_once("lib/includes/footer.php"); ?>