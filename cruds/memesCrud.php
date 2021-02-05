<?php

function addAdminPageContent() {
  add_submenu_page('memes', 'Meme Image', 'Meme Image', 'manage_options', "memes_image", 'crudAdminPage');
}

add_action('admin_menu', 'addAdminPageContent');


function crudAdminPage() {
  global $wpdb;
  $memesTableName = $wpdb->prefix . 'mein_memes';
  $keywordsTableName = $wpdb->prefix . 'mein_keywords';
  $memesKeywordsRelationTableName = $wpdb->prefix . 'mein_memes_keywords';
  createMeme($memesTableName, $keywordsTableName, $memesKeywordsRelationTableName);
  updateMeme($memesTableName, $keywordsTableName, $memesKeywordsRelationTableName);
  deleteMeme($memesTableName, $memesKeywordsRelationTableName);
  ?>
  <div class="wrap">
    <h2>Memes</h2>
    <table class="wp-list-table widefat striped">
      <thead>
        <tr>
          <th width="25%">Meme ID</th>
          <th width="25%">Description</th>
          <th width="25%">Keywords</th>
          <th width="25%">Meme</th>
          <th width="25%">Actions</th>
        </tr>
      </thead>
      <tbody>
        <form action="" method="post" enctype="multipart/form-data">
          <tr>
            <td><input type="text" value="AUTO_GENERATED" disabled></td>
            <td><input type="text" id="newdescription" name="newdescription"></td>
            <td><input type="text" id="newkeywords" name="newkeywords"></td>
            <td><<input type="file" id="newmemeimage" name="newmemeimage"></td>
            <td><button id="newsubmit" name="newsubmit" type="submit">INSERT</button></td>
          </tr>
        </form>
        <?php
          $pageNumber = 0;
          $pageLimit = 5;
          if (isset($_GET['pagenumber'])) {
            $pageNumber = $_GET['pagenumber'];
          }
          $memes = $wpdb->get_results("SELECT * FROM $memesTableName ORDER BY id DESC LIMIT $pageLimit OFFSET ".$pageNumber*$pageLimit);
          foreach ($memes as $meme) {
            $keywords = $wpdb->get_results("SELECT k.* FROM $memesKeywordsRelationTableName mkr JOIN $keywordsTableName k ON k.id = mkr.keyword_id WHERE mkr.meme_id='$meme->id'");
            $keywordsField = "";
            foreach ($keywords as $keyword) {
              $keywordsField .= "$keyword->keyword, ";
            }
            $keywordsField = trim($keywordsField, ", ");
            echo "
              <tr>
                <td width='25%'>$meme->id</td>
                <td width='25%'>$meme->description</td>
                <td width='25%'>$keywordsField</td>
                <td width='25%'><img style=\"width:200px; height:100px; object-fit:contain;\" src='/lamemervaW/$meme->url' /></td>
                <td width='25%'><a href='admin.php?page=memes&upt=$meme->id'><button type='button'>UPDATE</button></a> <a href='admin.php?page=memes&del=$meme->id'><button type='button'>DELETE</button></a></td>
              </tr>
            ";
          }
        ?>
      </tbody>  
    </table>
    <br>
    <br>
    <?php
      if (isset($_GET['upt'])) {
        $upt_id = $_GET['upt'];
        $memes = $wpdb->get_results("SELECT * FROM $memesTableName WHERE id='$upt_id'");
        foreach($memes as $meme) {
          $keywords = $wpdb->get_results("SELECT k.* FROM $memesKeywordsRelationTableName mkr JOIN $keywordsTableName k ON k.id = mkr.keyword_id WHERE mkr.meme_id='$meme->id'");
          $keywordsField = "";
          foreach ($keywords as $keyword) {
            $keywordsField .= "$keyword->keyword, ";
          }
          $keywordsField = trim($keywordsField, ", ");
          echo "
          <table class='wp-list-table widefat striped'>
            <thead>
              <tr>
                <th width='25%'>Meme ID</th>
                <th width='25%'>Description</th>
                <th width='25%'>Keywords</th>
                <th width='25%'>Meme</th>
                <th width='25%'>Actions</th>
              </tr>
            </thead>
            <tbody>
              <form action='' method='post' enctype='multipart/form-data'>
                <tr>
                  <td width='25%'>$meme->id <input type='hidden' id='uptid' name='uptid' value='$meme->id'></td>
                  <td width='25%'><input type='text' id='uptdescription' name='uptdescription' value='$meme->description'></td>
                  <td width='25%'><input type='text' id='uptkeywords' name='uptkeywords' value='$keywordsField'></td>
                  <td width='25%'><input type='file' id='uptmemeimage' name='uptmemeimage'></td>
                  <td width='25%'><button id='uptsubmit' name='uptsubmit' type='submit'>UPDATE</button> <a href='admin.php?page=memes'><button type='button'>CANCEL</button></a></td>
                </tr>
              </form>
            </tbody>
          </table>";
          
        }
      }
      if($pageNumber > 0) {
    ?>
    <a href='admin.php?page=memes&pagenumber=<?php echo $pageNumber-1 ?>'><button id="previouspage" name="previouspage">previous</button></a>
    <?php
      } 
    ?>
    <a href='admin.php?page=memes&pagenumber=<?php echo $pageNumber+1 ?>'><button id="nextpage" name="nextpage">next</button></a>
  </div>
  <?php

}

function deleteMeme($memesTableName, $memesKeywordsRelationTableName) {
  global $wpdb;
  if (isset($_GET['del'])) {
    $del_id = $_GET['del'];
    $wpdb->query("DELETE FROM $memesTableName WHERE id='$del_id'");
    deleteKeywordsRelations($memesKeywordsRelationTableName, $id);
    echo "<script>location.replace('admin.php?page=memes');</script>";
  }
}

function updateMeme($memesTableName, $keywordsTableName, $memesKeywordsRelationTableName) {
  global $wpdb;
  if (isset($_POST['uptsubmit'])) {
    $id = $_POST['uptid'];
    $description = $_POST['uptdescription'];
    $keywords = $_POST['uptkeywords'];
    $updateImageUrl = "";
    if(isset($_FILES["uptmemeimage"])) {
      $imageUrl = uploadImage($_FILES["uptmemeimage"]);
      if($imageUrl != "") {
        $updateImageUrl = ", url='$imageUrl'";
      }
    }
    $wpdb->query("UPDATE $memesTableName SET description='$description'$updateImageUrl WHERE id='$id'");
    deleteKeywordsRelations($memesKeywordsRelationTableName, $id);
    saveKeywordsRelations($id, $keywordsTableName, $memesKeywordsRelationTableName, $keywords);
    echo "<script>location.replace('admin.php?page=memes');</script>";
  }
}

function createMeme($memesTableName, $keywordsTableName, $memesKeywordsRelationTableName) {
  global $wpdb;
  if (isset($_POST['newsubmit'])) {
    $imageUrl = "";
    if(isset($_FILES["newmemeimage"])) {
      $imageUrl = uploadImage($_FILES["newmemeimage"]);
    }
    $description = $_POST['newdescription'];
    $wpdb->query("INSERT INTO $memesTableName(description, url) VALUES('$description', '$imageUrl')");
    $lastMemeId = $wpdb->insert_id;
    saveKeywordsRelations($lastMemeId, $keywordsTableName, $memesKeywordsRelationTableName, $_POST['newkeywords']);
   
    echo "<script>location.replace('admin.php?page=memes');</script>";
  }
}

function deleteKeywordsRelations($memesKeywordsRelationTableName, $lastMemeId) {
  global $wpdb;
  $wpdb->query("DELETE FROM $memesKeywordsRelationTableName WHERE meme_id='$lastMemeId'");
}

function saveKeywordsRelations($lastMemeId, $keywordsTableName, $memesKeywordsRelationTableName, $keywords) {
  global $wpdb;
  meinlog("todos los keywords: ".$keywords);
  $keywordsArray = explode(",", $keywords);

  //saving keywords
  foreach ($keywordsArray as $keyword) {
    meinlog("el keyword:".$keyword);
    $keywordNoSpace = strtolower(trim($keyword));
    $keywordsExist = $wpdb->get_results("SELECT * FROM $keywordsTableName WHERE keyword = '$keywordNoSpace'");
    $lastKeywordId = null;
    if(count($keywordsExist) == 0) {
      meinlog("el keyword no existe");
      if($keywordNoSpace != "") {
        $wpdb->query("INSERT INTO $keywordsTableName(keyword) VALUES('$keywordNoSpace')");
        $lastKeywordId = $wpdb->insert_id;
      }
    }else {
      foreach ($keywordsExist as $keyword) {
        $lastKeywordId = $keyword->id;
        meinlog("el keyword ya existe: ".$lastKeywordId);
      }
    }
    if($lastKeywordId) {
      $wpdb->query("INSERT INTO $memesKeywordsRelationTableName(meme_id, keyword_id) VALUES('$lastMemeId', '$lastKeywordId')");
    }
  }
}

function uploadImage($file) {
  $imageUrl = "";
  if($file["name"] != "") {
    $today = new DateTime("NOW");
    $movefile = wp_handle_upload($file, array('test_form' => FALSE), $today->format("Y-m-d h:i:s"));
      
    if ( $movefile && ! isset( $movefile['error'] ) ) {
      $imageUrl = $movefile["url"];
      $imageUrl = explode("wp-content", $imageUrl);
      if(count($imageUrl)>1) {
        $imageUrl = "/wp-content".$imageUrl[1];
      }
    } else {
        /*
        * Error generated by _wp_handle_upload()
        * @see _wp_handle_upload() in wp-admin/includes/file.php
        */
        meinlog($movefile['error']);
    }
  }
  return $imageUrl;
}