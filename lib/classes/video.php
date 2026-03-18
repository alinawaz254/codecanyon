<?php
class Video {

    public $id;
    public $title;
    public $description;
    public $video_file;
    public $video_url;


function list_videos(){
    global $db;

    $result = $db->query("SELECT * FROM videos ORDER BY id DESC");

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
        echo '<td colspan="5" class="text-center p-5">';
        echo '<h5 class="text-muted">No Videos Found</h5>';
        echo '<a href="manage_video.php" class="btn btn-primary mt-2">Add First Video</a>';
        echo '</td>';
        echo '</tr>';

    } else {

        while($row = $result->fetch_assoc()){

            echo "<tr>";

            // TITLE
            echo "<td><strong>".htmlspecialchars($row['title'])."</strong></td>";

           // PREVIEW
            echo "<td>";

            if($row['video_file']){

                echo "<video width='120' height='80' controls>
                        <source src='uploads/videos/".$row['video_file']."'>
                    </video>";

            } elseif($row['video_url']){

                $url = $row['video_url'];

                // ================= YOUTUBE =================
                if(strpos($url, 'youtube') !== false || strpos($url, 'youtu.be') !== false){

                    $video_id = '';

                    if(preg_match('/youtu\.be\/([^\?&]+)/', $url, $m)){
                        $video_id = $m[1];
                    }
                    elseif(preg_match('/v=([^\?&]+)/', $url, $m)){
                        $video_id = $m[1];
                    }
                    elseif(preg_match('/shorts\/([^\?&]+)/', $url, $m)){
                        $video_id = $m[1];
                    }

                    if($video_id){
                        echo "<iframe width='280' height='180'
                                src='https://www.youtube.com/embed/".$video_id."'
                                frameborder='0'
                                allowfullscreen>
                            </iframe>";
                    } else {
                        echo "<span class='text-danger'>Invalid YouTube URL</span>";
                    }
                }

                // ================= VIMEO =================
                elseif(strpos($url, 'vimeo.com') !== false){

                    preg_match('/vimeo\.com\/(\d+)/', $url, $m);
                    $video_id = $m[1] ?? '';

                    if($video_id){
                        echo "<iframe width='120' height='80'
                                src='https://player.vimeo.com/video/".$video_id."'
                                frameborder='0'
                                allowfullscreen>
                            </iframe>";
                    } else {
                        echo "<span class='text-danger'>Invalid Vimeo URL</span>";
                    }
                }

                // ================= DIRECT VIDEO =================
                else {

                    echo "<video width='120' height='80' controls>
                            <source src='".$url."'>
                        </video>";
                }

            } else {

                echo "<span class='text-muted'>No Video</span>";
            }

            echo "</td>";

            // DATE
            echo "<td>".date("Y-m-d H:i", strtotime($row['created_at']))."</td>";

            // ACTIONS
            echo "<td class='text-right'>";

            echo "<a href='manage_video.php?edit=".$row['id']."' class='btn btn-sm btn-info mr-1'>
                    Edit
                  </a>";

            echo "<form method='post' style='display:inline' 
                    onsubmit='return confirm(\"Delete this video?\")'>";

            echo "<input type='hidden' name='delete_video' value='".$row['id']."'>";

            echo "<button type='submit' class='btn btn-sm btn-danger'>
                    Delete
                  </button>";

            echo "</form>";

            echo "</td>";

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
    /* =========================
       ADD VIDEO
    ========================= */
    function add_video($title, $description, $file, $url){
        global $db;

        $video_file = NULL;

        if(isset($file['name']) && $file['name'] != ''){
            $target_dir = "uploads/videos/";

            if(!is_dir($target_dir)){
                mkdir($target_dir, 0777, true);
            }

            $filename = time() . "_" . basename($file["name"]);
            $target_file = $target_dir . $filename;

            move_uploaded_file($file["tmp_name"], $target_file);
            $video_file = $filename;
        }

        $stmt = $db->prepare("INSERT INTO videos (title, description, video_file, video_url) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $description, $video_file, $url);

        return $stmt->execute();
    }


    /* =========================
       UPDATE VIDEO
    ========================= */
    function update_video($id, $title, $description, $file, $url){
        global $db;

        // CASE 1: URL MODE
        if(!empty($url)){

            $stmt = $db->prepare("
                UPDATE videos 
                SET title=?, 
                    description=?, 
                    video_url=?, 
                    video_file=NULL 
                WHERE id=?
            ");
            $stmt->bind_param("sssi", $title, $description, $url, $id);

        }

        // CASE 2: FILE UPLOAD MODE
        elseif(isset($file['name']) && $file['name'] != ''){

            $target_dir = "uploads/videos/";
            $filename = time() . "_" . basename($file["name"]);
            $target_file = $target_dir . $filename;

            move_uploaded_file($file["tmp_name"], $target_file);

            $stmt = $db->prepare("
                UPDATE videos 
                SET title=?, 
                    description=?, 
                    video_file=?, 
                    video_url=NULL 
                WHERE id=?
            ");
            $stmt->bind_param("sssi", $title, $description, $filename, $id);

        }

        // CASE 3: ONLY TEXT UPDATE
        else {

            $stmt = $db->prepare("
                UPDATE videos 
                SET title=?, 
                    description=? 
                WHERE id=?
            ");
            $stmt->bind_param("ssi", $title, $description, $id);
        }

        return $stmt->execute();
    }    
    // function update_video($id, $title, $description, $file, $url){
    //     global $db;

    //     if(isset($file['name']) && $file['name'] != ''){

    //         $target_dir = "uploads/videos/";
    //         $filename = time() . "_" . basename($file["name"]);
    //         $target_file = $target_dir . $filename;

    //         move_uploaded_file($file["tmp_name"], $target_file);

    //         $stmt = $db->prepare("UPDATE videos SET title=?, description=?, video_file=?, video_url=? WHERE id=?");
    //         $stmt->bind_param("ssssi", $title, $description, $filename, $url, $id);

    //     } else {

    //         $stmt = $db->prepare("UPDATE videos SET title=?, description=?, video_url=? WHERE id=?");
    //         $stmt->bind_param("sssi", $title, $description, $url, $id);
    //     }

    //     return $stmt->execute();
    // }


    /* =========================
       DELETE VIDEO
    ========================= */
    function delete_video($id){
        global $db;

        $stmt = $db->prepare("DELETE FROM videos WHERE id=?");
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }


    /* =========================
       GET SINGLE VIDEO
    ========================= */
    function get_video($id){
        global $db;

        $stmt = $db->prepare("SELECT * FROM videos WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }


    /* =========================
       GET ALL VIDEOS  ✅ FIXED
    ========================= */
    function get_all_videos(){
        global $db;

        $result = $db->query("SELECT * FROM videos ORDER BY id DESC");

        $videos = [];
        while($row = $result->fetch_assoc()){
            $videos[] = $row;
        }

        return $videos;
    }

    function show_dashboard_videos($limit = 1){
        global $db;

        $stmt = $db->prepare("SELECT * FROM videos ORDER BY id DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows == 0){
            echo "
            <div class='video-box no-video mb-5'>
                <div class='no-video-text'>
                    No Video Available
                </div>
            </div>
            ";
            return;
        }

        $row = $result->fetch_assoc();

        echo "<div class='video-box'>";

        if($row['video_file']){

            echo "<video class='dashboard-video video-hover'
                    autoplay muted loop playsinline>
                    <source src='uploads/videos/".$row['video_file']."'>
                </video>";

        } elseif($row['video_url']){

            $url = $row['video_url'];

            if(strpos($url, 'youtube') !== false || strpos($url, 'youtu.be') !== false){

                $video_id = '';

                // youtu.be/ID
                if(preg_match('/youtu\.be\/([^\?&]+)/', $url, $m)){
                    $video_id = $m[1];
                }
                // youtube.com/watch?v=ID
                elseif(preg_match('/v=([^\?&]+)/', $url, $m)){
                    $video_id = $m[1];
                }
                // youtube.com/shorts/ID ✅ FIXED
                elseif(preg_match('/shorts\/([^\?&]+)/', $url, $m)){
                    $video_id = $m[1];
                }

                if($video_id){
                    echo "<iframe class='dashboard-video'
                            src='https://www.youtube.com/embed/".$video_id."?autoplay=1&mute=1&loop=1&playlist=".$video_id."'
                            frameborder='0'
                            allow='autoplay; encrypted-media'
                            allowfullscreen>
                        </iframe>";
                } else {
                    echo "<div class='no-video-text'>Invalid YouTube URL</div>";
                }
            }
        }

        echo "<div class='video-overlay'></div>";
        echo "</div>";
    }   
}