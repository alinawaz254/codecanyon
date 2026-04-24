<?php
class Images {

    public $id;
    public $title;
    public $description;
    public $image_file;
    public $image_url;

    function list_images(){
        global $db;

        $result = $db->query("SELECT * FROM images ORDER BY id DESC");

        echo '<div class="col-12">';
        echo '<div class="widget has-shadow">';
        echo '<div class="widget-body">';
        echo '<div class="table-responsive">';
        echo '<table class="table table-hover mb-0">';

        echo '<thead>';
        echo '<tr>';
        echo '<th>Title</th>';
        echo '<th>Preview</th>';
        echo '<th>Date</th>';
        echo '<th class="text-right">Actions</th>';
        echo '</tr>';
        echo '</thead>';

        echo '<tbody>';

        if($result->num_rows == 0){
            echo '<tr>';
            echo '<td colspan="4" class="text-center p-5">';
            echo '<h5 class="text-muted">No Images Found</h5>';
            echo '<a href="manage_image.php" class="btn btn-primary btn-md btn-golden mt-2">Add First Image</a>';
            echo '</td>';
            echo '</tr>';
        } else {
            while($row = $result->fetch_assoc()){
                echo "<tr>";
                echo "<td><strong>".htmlspecialchars($row['title'])."</strong></td>";
                echo "<td>";
                if($row['image_file']){
                    echo "<img src='assets/upload/images/".$row['image_file']."' style='width:120px; height:68px; object-fit:cover; border-radius:4px;'>";
                } elseif($row['image_url']){
                    echo "<img src='".$row['image_url']."' style='width:120px; height:68px; object-fit:cover; border-radius:4px;'>";
                } else {
                    echo "<span class='text-muted'>No Image</span>";
                }
                echo "</td>";
                echo "<td>".date("Y-m-d H:i", strtotime($row['created_at']))."</td>";
                echo "<td><div class='action-btn-container'>";
                echo "<a href='manage_image.php?edit=".$row['id']."' class='btn btn-sm btn-info'>Edit</a>";
                echo "<form method='post' onsubmit='return confirm(\"Delete this image?\")'>";
                echo "<input type='hidden' name='delete_image' value='".$row['id']."'>";
                echo "<button type='submit' class='btn btn-sm btn-danger'>Delete</button>";
                echo "</form>";
                echo "</div></td>";
                echo "</tr>";
            }
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    function add_image($title, $description, $file, $url){
        global $db;
        $image_file = NULL;

        if(isset($file['name']) && $file['name'] != ''){
            $target_dir = "assets/upload/images/";
            if(!is_dir($target_dir)){
                mkdir($target_dir, 0777, true);
            }
            $filename = time() . "_" . basename($file["name"]);
            $target_file = $target_dir . $filename;
            move_uploaded_file($file["tmp_name"], $target_file);
            $image_file = $filename;
        }

        $stmt = $db->prepare("INSERT INTO images (title, description, image_file, image_url) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $description, $image_file, $url);
        return $stmt->execute();
    }

    function update_image($id, $title, $description, $file, $url = null){
        global $db;
        
        if(isset($file['name']) && $file['name'] != ''){
            $target_dir = "assets/upload/images/";
            $filename = time() . "_" . basename($file["name"]);
            $target_file = $target_dir . $filename;
            move_uploaded_file($file["tmp_name"], $target_file);
            
            $stmt = $db->prepare("UPDATE images SET title=?, description=?, image_file=?, image_url=NULL WHERE id=?");
            $stmt->bind_param("sssi", $title, $description, $filename, $id);
        } else {
            $stmt = $db->prepare("UPDATE images SET title=?, description=? WHERE id=?");
            $stmt->bind_param("ssi", $title, $description, $id);
        }
        return $stmt->execute();
    }

    function delete_image($id){
        global $db;
        $stmt = $db->prepare("DELETE FROM images WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    function get_image($id){
        global $db;
        $stmt = $db->prepare("SELECT * FROM images WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    function show_dashboard_images($limit = 1){
        global $db;
        $stmt = $db->prepare("SELECT * FROM images ORDER BY id DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows == 0){
            echo "<div class='video-box no-video mb-5'><div class='no-video-text'>No Image Available</div></div>";
            return;
        }

        $row = $result->fetch_assoc();
        echo "<div class='video-box'>";
        if($row['image_file']){
            echo "<img src='assets/upload/images/".$row['image_file']."' class='dashboard-video' style='object-fit:cover;'>";
        } elseif($row['image_url']){
            echo "<img src='".$row['image_url']."' class='dashboard-video' style='object-fit:cover;'>";
        }
        echo "<div class='video-overlay'></div>";
        echo "</div>";
    }
}
?>
