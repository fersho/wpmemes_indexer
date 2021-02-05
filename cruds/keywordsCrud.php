<?php

function addAdminPageKeyword() {
  add_submenu_page('memes', 'Memes', 'Memes Keywords', 'manage_options', "meme_keywords", 'crudAdminKeywordPage');
}

add_action('admin_menu', 'addAdminPageKeyword');

function crudAdminKeywordPage() {
  global $wpdb;
  $keywordsTableName = $wpdb->prefix . 'mein_keywords';

  if (isset($_POST['newsubmit'])) {
    $keyword = $_POST['newkeyword'];
    $wpdb->query("INSERT INTO $keywordsTableName(keyword) VALUES('$keyword')");
    echo "<script>location.replace('admin.php?page=keywords');</script>";
  }
  if (isset($_POST['uptsubmit'])) {
    $id = $_POST['uptid'];
    $keyword = $_POST['uptkeyword'];
    $wpdb->query("UPDATE $keywordsTableName SET keyword='$keyword' WHERE id='$id'");
    echo "<script>location.replace('admin.php?page=keywords');</script>";
  }
  if (isset($_GET['del'])) {
    $del_id = $_GET['del'];
    $wpdb->query("DELETE FROM $keywordsTableName WHERE id='$del_id'");
    echo "<script>location.replace('admin.php?page=keywords');</script>";
  }
  ?>
  <div class="wrap">
    <h2>Keywords</h2>
    <table class="wp-list-table widefat striped">
      <thead>
        <tr>
          <th width="25%">Keyword ID</th>
          <th width="25%">keyword</th>
          <th width="25%">Actions</th>
        </tr>
      </thead>
      <tbody>
        <form action="" method="post">
          <tr>
            <td><input type="text" value="AUTO_GENERATED" disabled></td>
            <td><input type="text" id="newkeyword" name="newkeyword"></td>
            <td><button id="newsubmit" name="newsubmit" type="submit">INSERT</button></td>
          </tr>
        </form>
        <?php
          $result = $wpdb->get_results("SELECT * FROM $keywordsTableName");
          foreach ($result as $print) {
            echo "
              <tr>
                <td width='25%'>$print->id</td>
                <td width='25%'>$print->keyword</td>
                <td width='25%'><a href='admin.php?page=keywords&upt=$print->id'><button type='button'>UPDATE</button></a> <a href='admin.php?page=keywords&del=$print->id'><button type='button'>DELETE</button></a></td>
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
        $result = $wpdb->get_results("SELECT * FROM $keywordsTableName WHERE id='$upt_id'");
        foreach($result as $print) {
          $keyword = $print->keyword;
        }
        echo "
        <table class='wp-list-table widefat striped'>
          <thead>
            <tr>
              <th width='25%'>Keyword ID</th>
              <th width='25%'>keyword</th>
              <th width='25%'>Actions</th>
            </tr>
          </thead>
          <tbody>
            <form action='' method='post'>
              <tr>
                <td width='25%'>$print->id <input type='hidden' id='uptid' name='uptid' value='$print->id'></td>
                <td width='25%'><input type='text' id='uptkeyword' name='uptkeyword' value='$print->keyword'></td>
                <td width='25%'><button id='uptsubmit' name='uptsubmit' type='submit'>UPDATE</button> <a href='admin.php?page=keywords'><button type='button'>CANCEL</button></a></td>
              </tr>
            </form>
          </tbody>
        </table>";
      }
    ?>
  </div>
  <?php

}

