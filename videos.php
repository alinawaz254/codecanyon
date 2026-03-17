<?php
require_once("lib/system_load.php");
authenticate_user('admin');

$video_obj = new Video();

// DELETE
if(isset($_POST['delete_video'])){
    $video_obj->delete_video($_POST['delete_video']);
}

$page_title = "Videos";
require_once("lib/includes/header.php");
?>

<div class="row">
    <div class="col-12">

        <!-- ADD BUTTON -->
        <div class="text-right mb-4">
            <a href="manage_video.php" class="btn btn-primary btn-md btn-golden">
                <i class="la la-plus-circle"></i> Add New Video
            </a>
        </div>

        <div class="row">
            <?php $video_obj->list_videos(); ?>
        </div>

    </div>
</div>

<?php require_once("lib/includes/footer.php"); ?>