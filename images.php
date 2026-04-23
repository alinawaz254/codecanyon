<?php
require_once("lib/system_load.php");
authenticate_user('admin');

$image_obj = new Images();

// DELETE
if(isset($_POST['delete_image'])){
    $image_obj->delete_image($_POST['delete_image']);
}

$page_title = "Images";
require_once("lib/includes/header.php");
?>

<div class="row">
    <div class="col-12">

        <!-- ADD BUTTON -->
        <div class="text-right mb-4">
            <a href="manage_image.php" class="btn btn-primary btn-md btn-golden">
                <i class="la la-plus-circle"></i> Add New Image
            </a>
        </div>

        <div class="row">
            <?php $image_obj->list_images(); ?>
        </div>

    </div>
</div>

<?php require_once("lib/includes/footer.php"); ?>
