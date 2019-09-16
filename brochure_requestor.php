<?php
/**
 * Plugin Name:    Brochure Requestor
 * Description:    Insert a brochure request form via a shortcode.
 * Version:        0.2
 * Author:         James Boynton
 * Author URI:     #
 * Text Domain:    br
 */

defined ('ABSPATH') or die('Direct access not permitted.');

function br_brochure_requestor_init() {
  define ('BROCHURES_PAGE', '4503');

  if (isset($_POST['brochure_request_submitted']) && $_POST['brochure_request_submitted']) {
    if (br_verify_nonce()) {
      $args = br_build_message_fields();
      $content = br_generate_message_content($args);

      br_send_pending_mail($content);
    }
  }
}
add_action('init', 'br_brochure_requestor_init');

function br_build_message_fields() {
  $request        = (isset($_POST['brochure_request'])) ? $_POST['brochure_request'] : array();
  $title          = (isset($request['title'])) ? $request['title'] : '';
  $name           = (isset($request['name'])) ? $request['name'] : '';
  $email          = (isset($request['email'])) ? $request['email'] : '';
  $company        = (isset($request['company'])) ? $request['company'] : '';
  $address_line_1 = (isset($request['address_line_1'])) ? $request['address_line_1'] : '';
  $address_line_2 = (isset($request['address_line_2'])) ? $request['address_line_2'] : '';
  $city           = (isset($request['city'])) ? $request['city'] : '';
  $state          = (isset($request['state'])) ? $request['state'] : '';
  $zip            = (isset($request['zip'])) ? $request['zip'] : '';
  $country        = (isset($request['country'])) ? $request['country'] : '';

  $fields = array(
    'title' => $title,
    'name' => $name,
    'email' => $email,
    'company' => $company,
    'address_line_1' => $address_line_1,
    'address_line_2' => $address_line_2,
    'city' => $city,
    'state' => $state,
    'zip' => $zip,
    'country' => $country
  );

  return $fields;
}

function br_send_pending_mail($body) {
  $recipient = 'dev@xzito.com,info@hornermillwork.com';
  $subject = 'New Brochure Request';

  wp_mail($recipient, $subject, $body);
  wp_safe_redirect($_SERVER['REQUEST_URI']);

  die();
}

function br_generate_message_content($fields) {
  $message = "New Brochure Request\n";
  $message .= "--------------------\n";
  $message .= "\n";
  $message .= "Brochure:         " .$fields["title"] ."\n";
  $message .= "For:              " .$fields["name"] ."\n";
  $message .= "Email:            " .$fields["email"] ."\n";
  $message .= "Company:          " .$fields["company"] ."\n";
  $message .= "Address Line 1:   " .$fields["address_line_1"] ."\n";
  $message .= "Address Line 2:   " .$fields["address_line_2"] ."\n";
  $message .= "City:             " .$fields["city"] ."\n";
  $message .= "State:            " .$fields["state"] ."\n";
  $message .= "Zip Code:         " .$fields["zip"] ."\n";
  $message .= "Country:          " .$fields["country"] ."\n";
  $message .= "\n";

  return $message;
}

function br_enqueue_assets() {
  if (is_page(BROCHURES_PAGE)) {

    // Bootstrap
    wp_enqueue_script('br_bootstrap_script', plugin_dir_url(__FILE__) .
      'vendor/bootstrap/bootstrap.min.js', array('jquery'));
    wp_enqueue_style('br_bootstrap_style', plugin_dir_url(__FILE__) .
      'vendor/bootstrap/bootstrap.min.css');

    // Plugin style
    wp_enqueue_style('br_request_form_style', plugin_dir_url(__FILE__) .
      'css/request_form.css');

    // Form submission script
    wp_enqueue_script('br_form_helper_script', plugin_dir_url(__FILE__) .
      'js/form_helper.js', array('jquery'));
    wp_localize_script('br_form_helper_script', 'br_requested_brochure', array(
      'ajax_url' => admin_url('admin-ajax.php')
    ));
  }
}
add_action('wp_enqueue_scripts', 'br_enqueue_assets');

function br_get_requested_brochure_data() {
  if (!empty($_POST['brochure_name'])) {
    $brochure_name = $_POST['brochure_name'];
  } else {
    $brochure_name = "This brochure is not available at this time.";
  }

  if (!empty($_POST['brochure_image'])) {
    $brochure_image = $_POST['brochure_image'];
  } else {
    $brochure_image = '';
  }

  $brochure_data = array(
    'name' => $brochure_name,
    'image' => $brochure_image
  );
  $brochure_data = json_encode($brochure_data);

  if (defined ('DOING_AJAX') && DOING_AJAX) {
    echo $brochure_data;
  }

  die();
}
add_action(
  'wp_ajax_nopriv_br_get_requested_brochure_data',
  'br_get_requested_brochure_data'
);
add_action(
  'wp_ajax_br_get_requested_brochure_data',
  'br_get_requested_brochure_data'
);

function br_incremented_id() {
  global $post;

  $counter = get_post_meta($post->ID, 'br_counter', true);
  $counter = isset($counter) ? $counter : 1;

  return $counter;
}

function updateCounter($current_id) {
  global $post;

  update_post_meta($post->ID, 'br_counter', ++$current_id);
}

function br_request_brochure_shortcode() {
  ?>
  <div class="request-brochure-wrapper">
    <a class="request-link" id="request-brochure-<?php echo br_incremented_id(); ?>" href="#" data-toggle="modal" data-target="#brochure-request-form-<?php echo br_incremented_id(); ?>">request</a>

    <!-- Modal -->
    <div class="modal fade" id="brochure-request-form-<?php echo br_incremented_id(); ?>" tabindex="-1" role="dialog" aria-labelledby="brochure request form">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form class="form-horizontal" action="<?php the_permalink(); ?>" method="post">
            <?php wp_nonce_field(basename(__FILE__), 'br_request_brochure_nonce'); ?>
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title">Receive a Brochure By Mail</h4>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <div class="col-sm-12">
                  <p class="form-control-static brochure-title" id="requested-brochure-name-<?php echo br_incremented_id(); ?>"></p>
                  <input hidden type="text" id="requested-brochure-title-<?php echo br_incremented_id(); ?>" name="brochure_request[title]" value="">
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-12">
                  <div class="brochure-image" id="requested-brochure-image-<?php echo br_incremented_id(); ?>" name="brochure_request[image_url]"></div>
                </div>
              </div>
              <div class="form-group">
                <label for="brochure_request[name]" class="col-sm-3 control-label">Name</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="name" name="brochure_request[name]" placeholder="Name">
                </div>
              </div>
              <div class="form-group">
                <label for="brochure_request[email]" class="col-sm-3 control-label">Email Address</label>
                <div class="col-sm-9">
                  <input type="email" class="form-control" id="email" name="brochure_request[email]" placeholder="Email Address">
                </div>
              </div>
              <div class="form-group">
                <label for="brochure_request[company]" class="col-sm-3 control-label">Company</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="company" name="brochure_request[company]" placeholder="Company Name">
                </div>
              </div>
              <div class="form-group">
                <label for="brochure_request[address_line_1]" class="col-sm-3 control-label">Address Line 1</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="address_line_1" name="brochure_request[address_line_1]" placeholder="Address Line 1">
                </div>
              </div>
              <div class="form-group">
                <label for="brochure_request[address_line_2]" class="col-sm-3 control-label">Address Line 2</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="address_line_2" name="brochure_request[address_line_2]" placeholder="Address Line 2">
                </div>
              </div>
              <div class="form-group">
                <label for="brochure_request[city]" class="col-sm-3 control-label">City</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="city" name="brochure_request[city]" placeholder="City">
                </div>
              </div>
              <div class="form-group">
                <label for="brochure_request[zip]" class="col-sm-3 control-label">Zip Code</label>
                <div class="col-sm-5">
                  <input type="text" class="form-control" id="zip" name="brochure_request[zip]" placeholder="Zip Code">
                </div>
              </div>
              <div class="form-group">
                <label for="brochure_request[state]" class="col-sm-3 control-label">State</label>
                <div class="col-sm-5">
                  <input type="text" class="form-control" id="state" name="brochure_request[state]" placeholder="State">
                </div>
              </div>
              <div class="form-group">
                <label for="brochure_request[country]" class="col-sm-3 control-label">Country</label>
                <div class="col-sm-5">
                  <input type="text" class="form-control" id="country" name="brochure_request[country]" placeholder="Country">
                </div>
              </div>
            </div>
            <input hidden type="text" name="brochure_request_submitted" value="1">
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary submitRequest" id="requested-brochure-submit-<?php echo br_incremented_id(); ?>" disabled>Request Brochure</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <?php
  updateCounter(br_incremented_id());
}
add_shortcode('request_brochure', 'br_request_brochure_shortcode');

function br_verify_nonce() {
  if (isset($_POST['br_request_brochure_nonce'])) {
    $is_nonce_set = true;
  }

  if (wp_verify_nonce($_POST['br_request_brochure_nonce'], basename(__FILE__))) {
    $is_nonce_verified = true;
  }

  return ($is_nonce_set && $is_nonce_verified);
}

