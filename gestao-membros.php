<?php
/*
Plugin Name: Gestao-Membros
Description: A simple plug-in that allows you to perform create (INSERT), read (SELECT), update and delete operations, made by request of Alfsoft
Version: 1.1.0
Author: Tiago Maia
Author URI: https://link.tiagomaia.dev.br/
*/
register_activation_hook(__FILE__, 'crudOperationsTable');


function crudOperationsTable() {
  
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  $table_name = $wpdb->prefix . 'userstable';

  function checkIfEmailAndPhoneExist($email, $phone) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'userstable';
  
    // Verificar se os campos foram preenchidos
    if (empty($email) || empty($phone)) {
      return false;
    }
  
    // Consultar o banco de dados
    $result = $wpdb->get_results("SELECT * FROM $table_name WHERE email = '$email' OR phone = '$phone'");
  
    // Verificar se o e-mail ou o telefone já existem no banco de dados
    if (count($result) > 0) {
      foreach ($result as $row) {
        if ($row->email == $email) {
          echo '<div class="error"><p>E-mail já cadastrado.</p></div>';
        }
        if ($row->phone == $phone) {
          echo '<div class="error"><p>Telefone já cadastrado.</p></div>';
        }
      }
      return true;
    }
  
    return false;
  }


  
  if (isset($_POST['newsubmit'])) {
    $email = $_POST['newemail'];
    $phone = $_POST['newphone'];
    $email_exists = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE email='$email'");
    $phone_exists = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE Phone='$phone'");

    if ($email_exists) {
      echo '<div class="error"><p>E-mail já cadastrado.</p></div>';
      return;
    }
    if ($phone_exists) {
      echo '<div class="error"><p>Telefone já cadastrado.</p></div>';
      return;
    }
  }

//código para criar tabela, realizar inserções, atualizações e exclusões de registros
  $sql = "CREATE TABLE `$table_name` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name`    varchar(220) DEFAULT NULL,
  `email`   varchar(220) DEFAULT NULL,
  `Phone`   varchar(20) DEFAULT NULL,
  PRIMARY KEY(user_id)
  ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
  ";
  if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }
}

add_action('admin_menu', 'addAdminPageContent');

function addAdminPageContent() {
  add_menu_page('GestaoMembros', 'GestaoMembros', 'manage_options' ,'gestao-membros', 'crudAdminPage', 'dashicons-groups');
}
function crudAdminPage() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'userstable';

  if (isset($_POST['newsubmit'])) {
    $name  = $_POST['newname'];
    $email = $_POST['newemail'];
    $phone = $_POST['newphone'];

        // Validar o endereço de e-mail com expressão regular
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          echo '<div class="error"><p>Endereço de e-mail inválido.</p></div>';
          return;
        }

    $wpdb->query("INSERT INTO $table_name(name,email,phone) VALUES('$name','$email','$phone')");
    echo "<script>location.replace('admin.php?page=gestao-membros');</script>";
  }

// Obter imagem aleatória da API
$image_url  = 'https://app.pixelencounter.com/api/basic/monsters/random';
$image_data = file_get_contents($image_url);
$upload_dir = wp_upload_dir();
$image_path = $upload_dir['path'] . '/' . uniqid() . '.png';

// Salvar imagem em pasta do WordPress
$file = wp_upload_bits(basename($image_path), null, $image_data);
if (!$file['error']) {
  $image_path = $file['file'];
} else {
  echo '<div class="error"><p>Erro ao salvar imagem.</p></div>';
  return;
}

  if (isset($_POST['uptsubmit'])) {
    $id           = $_POST['uptid'];
    $name         = $_POST['uptname'];
    $email        = $_POST['uptemail'];
    $phone        = $_POST['uptphone'];
    $wpdb->query("UPDATE $table_name SET name='$name',email='$email', Phone='$phone' WHERE user_id='$id'");
    echo "<script>location.replace('admin.php?page=gestao-membros');</script>";
  }

  if (isset($_GET['del'])) {
    $del_id = $_GET['del'];
    $wpdb->query("DELETE FROM $table_name WHERE user_id='$del_id'");
    echo "<script>location.replace('admin.php?page=gestao-membros');</script>";
  }
  
  ?>
  <div class="wrap">
    <h2>Gestão de Membros</h2>
    <table class="wp-list-table widefat striped">
      <thead>
        <tr>
          <th width="20%">User ID</th>
          <th width="20%">Name</th>
          <th width="20%">Email Address</th>        
          <th width="30%">Phone Number</th>
          <th width="10%">Actions</th>          
        </tr>
      </thead>
      <tbody>
        <form action="" method="post">
          <tr>
            <td><input type="text" value="AUTO_GENERATED" disabled></td>
            <td><input type="text" id="newname" name="newname"></td>
            <td><input type="text" id="newemail" name="newemail"></td>            
            <td><input type="text" id="newphone" name="newphone"></td>
            <td><button id="newsubmit" name="newsubmit" type="submit">INSERT</button></td>
          </tr>
        </form>
        <?php
          $result = $wpdb->get_results("SELECT * FROM $table_name");
          foreach ($result as $print) {
            echo "
              <tr>
                <td width='20%'>$print->user_id</td>
                <td width='20%'>$print->name</td>
                <td width='20%'>$print->email</td>                
                <td width='30%'>$print->Phone</td>
                <td width='10%'><a href='admin.php?page=gestao-membros&upt=$print->user_id'><button type='button'>UPDATE</button></a> <a href='admin.php?page=gestao-membros&del=$print->user_id'><button type='button'>DELETE</button></a></td>
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
        $result = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id='$upt_id'");
        foreach($result as $print) {
          $name = $print->name;
          $email = $print->email;
          $phone = $print->Phone;
        }
        //Formulario para exibir a lista de cadastro no gerenciador
        echo "
        <table class='wp-list-table widefat striped'>
          <thead>
            <tr>
              <th width='20%'>User ID</th>
              <th width='20%'>Name</th>
              <th width='20%'>Email Address</th>
              <th width='30%'>Phone</th>
              <th width='10%'>Actions</th>
            </tr>
          </thead>
          <tbody>
            <form action='' method='post'>
              <tr>
                <td width='20%'>$print->user_id <input type='hidden' id='uptid' name='uptid' value='$print->user_id'></td>
                <td width='20%'><input type='text' id='uptname' name='uptname' value='$print->name'></td>
                <td width='20%'><input type='text' id='uptemail' name='uptemail' value='$print->email'></td>
                <td width='30%'><input type='text' id='uptephone' name='uptephone' value='$print->Phone'></td>
                <td width='10%'><button id='uptsubmit' name='uptsubmit' type='submit'>UPDATE</button> <a href='admin.php?page=gestao-membros'><button type='button'>CANCEL</button></a></td>
              </tr>
            </form>
          </tbody>
        </table>";
      }
    ?>
  </div>
  <?php
}

//Função para gerar shortcode
function tabela_gestao_membros($atts) {
  global $wpdb;
  $table_name = $wpdb->prefix . 'userstable';
  $Content = <<<EOT
<table class="wp-list-table widefat striped">
    <thead>
      <tr>
        <th width="20%">User ID</th>
        <th width="20%">Name</th>
        <th width="20%">Email Address</th>        
        <th width="30%">Phone Number</th>              
      </tr>
    </thead>
    <tbody>
EOT;
  global $wpdb;
  $table_name = $wpdb->prefix . 'userstable';
  $result = $wpdb->get_results("SELECT * FROM $table_name");
  foreach ($result as $print) {
      $Content .= "
            <tr>
              <td width='20%'>$print->user_id</td>
              <td width='20%'>$print->name</td>
              <td width='20%'>$print->email</td>                
              <td width='30%'>$print->Phone</td>             
              </tr>
          ";
  }
  $Content .= "</tbody>  
  </table>";
  return $Content;
}
// chamar no site [tabela-membros]
add_shortcode('tabela-membros', 'tabela_gestao_membros');