<?php

/**
 * Generate manage referrals navigation content.
 */
if (!function_exists('custom_manage_referral_nav_content')) {
    function custom_manage_referral_nav_content()
    {

        // Initialize variables to store selected dates
        $start_date_sent = '';
        $end_date_sent = '';
        $start_date_received = '';
        $end_date_received = '';
        $unique_chapters = [];

        // Check if the form is submitted for Referral Sent
        if (isset($_POST["submit_sent"])) {
            $start_date_sent = $_POST["start_date_sent"];
            $end_date_sent = $_POST["end_date_sent"];
        }

        // Check if the form is submitted for Referral Received
        if (isset($_POST["submit_received"])) {
            $start_date_received = $_POST["start_date_received"];
            $end_date_received = $_POST["end_date_received"];
        }

        // Retrieve referral sent data if form is submitted for Referral Sent
        if (isset($_POST["submit_sent"])) {
            $none_specified = NULL;
            $referrals_sent = get_referrals_sent_by_date_range($start_date_sent, $end_date_sent, $none_specified );
        }

        // Retrieve referral received data if form is submitted for Referral Received
        if (isset($_POST["submit_received"])) {
            $referrals_received = get_referrals_received_by_date_range($start_date_received, $end_date_received);
        }
?>

        <!-- HTML for Date Range Selection - Referral Sent -->
        <div class="referrals-sent-table">
            <h2>Referrals Sent</h2>
            <div class="ref-date">
                <div class="date-text">Select Date Range: </div>
                <div>
                    <form id="sent-referral-form" method="post">
                        <input type="date" id="start_date_sent" name="start_date_sent"> -
                        <input type="date" id="end_date_sent" name="end_date_sent">

                            <label for="sent_selected_chapter">Select Chapter: </label>

                            <select id="sent_selected_chapter" name="sent_selected_chapter">

                                <?php
                                // Get list of all chapters and pull out those with active referrals
                                // Build filter for chapter selection using only the active chapters. 
                                // Set the default selection to be the current displayed user's chapter. 
                                    $our_chapters = get_active_chapter_list( $unique_chapters );
                                    $cnt = count($our_chapters);
                                    for ($i = 1; $i < $cnt; $i++) { 
                                        if  ( $our_chapters[0] === $our_chapters[$i] ) {
                                            echo '<option value="' . $our_chapters[$i] . '" selected>' . $our_chapters[$i] . '</option><br>';
                                        } else {
                                            echo '<option value="' . $our_chapters[$i] . '">' . $our_chapters[$i] . '</option><br>';
                                        }
                                    }
                                ?>

                            </select>
                            <input type="submit" name="submit_sent" value="Filter">
                    </form>
                </div>
            </div>
            <div class="responsive-table" id="referral-sent-content"></div>
        </div>
        <hr>

        <!-- HTML for Date Range Selection - Referral Received -->
        <div class="referrals-received-table">
            <h2>Referrals Received</h2>
            <div class="ref-date">
                <div class="date-text">Select Date Range: </div>
                <div>
                    <form id="received-referral-form" method="post">
                        <input type="date" class="date-filter" id="start_date_received" name="start_date_received"> -
                        <input type="date" class="date-filter" id="end_date_received" name="end_date_received">

                        <label for="recv_selected_chapter">Select Chapter: </label>
                        <select id="recv_selected_chapter" name="recv_selected_chapter">

                            <?php
                            // Get list of all chapters and pull out those with active referrals
                            // Build filter for chapter selection using only the active chapters. 
                            // Set the default selection to be the current displayed user's chapter. 
                                $our_chapters = get_active_chapter_list( $unique_chapters );
                                $cnt = count($our_chapters);
                                for ($i = 1; $i < $cnt; $i++) { 
                                    if  ( $our_chapters[0] === $our_chapters[$i] ) {
                                        echo '<option value="' . $our_chapters[$i] . '" selected>' . $our_chapters[$i] . '</option><br>';
                                    } else {
                                        echo '<option value="' . $our_chapters[$i] . '">' . $our_chapters[$i] . '</option><br>';
                                    }
                                }
                            ?>

                        </select>



                        <input type="submit" name="submit_received" value="Filter">
                    </form>
                </div>
            </div>
            <div class="responsive-table" id="referral-received-content"></div>
        </div>

        <script>
            jQuery(document).ready(function($) {
                // Click event handler for "Filter" button for sent referrals
                $('input[name="submit_sent"]').on('click', function(event) {
                    event.preventDefault(); // Prevent default form submission
                    var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
                    var startDateSent = $('#start_date_sent').val();
                    var endDateSent = $('#end_date_sent').val();
                    var sentSelectedChapter = $('#sent_selected_chapter').val();

                    // Perform AJAX request
                    $.ajax({
                        url: ajaxurl,
                        type: 'post',
                        dataType: 'json',
                        data: {
                            action: 'filter_referrals',
                            start_date_sent: startDateSent,
                            end_date_sent: endDateSent,
                            sent_selected_chapter: sentSelectedChapter
                        },
                        success: function(response) {
                            $('#referral-sent-content').html(response.sent);
                        }
                    })
                });

                // Click event handler for "Filter" button for received referrals
                $('input[name="submit_received"]').on('click', function(event) {
                    event.preventDefault(); // Prevent default form submission
                    var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
                    var startDateReceived = $('#start_date_received').val();
                    var endDateReceived = $('#end_date_received').val();
                    var recvSelectedChapter = $('#recv_selected_chapter').val();

                    // Perform AJAX request
                    $.ajax({
                        url: ajaxurl,
                        type: 'post',
                        dataType: 'json',
                        data: {
                            action: 'filter_referrals',
                            start_date_received: startDateReceived,
                            end_date_received: endDateReceived,
                            recv_selected_chapter: recvSelectedChapter
                        },
                        success: function(response) {
                            $('#referral-received-content').html(response.received);
                        }
                    });
                });
            });
        </script>
    <?php
    }
}

/**
 * Retrieve referral data for sent referrals within the specified date range for the specific chapter.
 *
 * @param string $start_date The start date of the date range.
 * @param string $end_date   The end date of the date range. 
 * @param string $sent_selected_chapter Chapter name to return referral data for.
 * @return array             An array of referral data for sent referrals within the date range.
 */
function get_referrals_sent_by_date_range($start_date, $end_date, $sent_selected_chapter)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'show_referrals';

    // Processing filter for current user. 
    $current_user_id = bp_displayed_user_id();
    if ( is_null( $sent_selected_chapter ) ) {
        $referrals_all = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE sender_id = %d",
                $current_user_id
            ),
            ARRAY_A
        );
    } else {

        // Get list of all users in selected chapter
        $our_chapter_users = userids_in_chapter( $sent_selected_chapter );

        // Build SQL statement to retrieve all referrals with sender is in our chapter
        $where = 'WHERE sender_id IN ( ';
        for ($i=0; $i < count($our_chapter_users); $i++) { 
            $where .= $our_chapter_users[$i]. ', ';
        }
        $where = substr( $where, 0, -2 );
        $where .= ' )';
        $referrals_all = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM $table_name $where" ),
            ARRAY_A
        );
    }

    // Filter referrals based on the date range
    $referrals_sent = array_filter($referrals_all, function ($referral) use ($start_date, $end_date) {
            $sent_date = date('Y-m-d', strtotime($referral['sent_date']));
        return ($sent_date >= $start_date && $sent_date <= $end_date);
    });
    return $referrals_sent;
}

/**
 * Retrieve referral data for received referrals within the specified date range for the specified chapter.
 *
 * @param string $start_date The start date of the date range.
 * @param string $end_date   The end date of the date range.
 * @param string $recv_selected_chapter Chapter name to return referral data for.
 * @return array             An array of referral data for received referrals within the date range.
 */
function get_referrals_received_by_date_range($start_date, $end_date, $recv_selected_chapter)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'show_referrals';

    // Processing filter for current user. 
    $current_user_id = bp_displayed_user_id();
    if ( is_null( $recv_selected_chapter ) ) {
        $referrals_all = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE recipient_id = %d",
                $current_user_id
            ),
            ARRAY_A
        );
    } else {
        // Get list of all users in selected chapter
        $our_chapter_users = userids_in_chapter( $recv_selected_chapter );

        // Build SQL statement to retrieve all referrals with sender is in our chapter
        $where = 'WHERE recipient_id IN ( ';
        
        for ($i=0; $i < count($our_chapter_users); $i++) { 
            $where .= $our_chapter_users[$i]. ', ';
        }
        $where = substr( $where, 0, -2 );
        $where .= ' )';
        $referrals_all = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM $table_name $where" ),
            ARRAY_A
        );



    }

    // Filter referrals based on the date range
    $referrals_received = array_filter($referrals_all, function ($referral) use ($start_date, $end_date) {
        $received_date = date('Y-m-d', strtotime($referral['received_date']));
        return ($received_date >= $start_date && $received_date <= $end_date);
    });

    return $referrals_received;
}


/**
 * AJAX handler for filtering referrals.
 */
add_action('wp_ajax_filter_referrals', 'filter_referrals_ajax');
add_action('wp_ajax_nopriv_filter_referrals', 'filter_referrals_ajax');

function filter_referrals_ajax()
{
    $sent_html = $received_html = '';

    // referral sent date
    $start_date_sent = isset($_POST["start_date_sent"]) ? sanitize_text_field($_POST["start_date_sent"]) : '';
    $end_date_sent = isset($_POST["end_date_sent"]) ? sanitize_text_field($_POST["end_date_sent"]) : '';

    // referral received date
    $start_date_received = isset($_POST["start_date_received"]) ? sanitize_text_field($_POST["start_date_received"]) : '';
    $end_date_received = isset($_POST["end_date_received"]) ? sanitize_text_field($_POST["end_date_received"]) : '';

    $sender_id = $_POST['sender_id'];
    $recipient_id = $_POST['recipient_id'];
    // Get Selected Chapter from Manage Referrals filter form. This is only available for users with role = 'President'.

    $sent_selected_chapter = isset($_POST["sent_selected_chapter"]) ? sanitize_text_field( $_POST['sent_selected_chapter']) : '';
    $recv_selected_chapter = isset($_POST["recv_selected_chapter"]) ? sanitize_text_field( $_POST['recv_selected_chapter']) : '';

    // Chapter affiliation for sender and receiver are referenced by field 11 'Chapter Member'
    $sender_chapter = bp_get_profile_field_data(array('field' => 11, 'user_id' => $sender_id));
    $recipient_chapter = bp_get_profile_field_data(array('field' => 11, 'user_id' => $recipient_id));
    
    // If the sender or the receiver of the referral is in the selected Chapter, process it. 
        $referrals_sent     = get_referrals_sent_by_date_range($start_date_sent, $end_date_sent, $sent_selected_chapter );
        $referrals_received = get_referrals_received_by_date_range($start_date_received, $end_date_received, $recv_selected_chapter);

        // Process sent referrals
        if (!empty($referrals_sent)) {
            $referrals_sent_table = [];
            foreach ($referrals_sent as $referral) {
                
                $referral_count = 0;
                $sender_id = $referral['sender_id'];
                $sender_name = get_userdata($sender_id)->display_name ? get_userdata($sender_id)->display_name : get_userdata($sender_id)->user_login;

                if (isset($referrals_sent_table[$referral['sender_id']][$referral['type_of_referral']])) {
                    $referral_count = $referrals_sent_table[$referral['sender_id']][$referral['type_of_referral']] + 1;
                } else {
                    $referral_count = 1;
                }
                $referrals_sent_table[$referral['sender_id']]['name'] =  $sender_name;
                $referrals_sent_table[$referral['sender_id']][$referral['type_of_referral']] = $referral_count;
            }
        }  

        // Generate HTML for sent referrals
        ob_start();
    ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Referrals</th>
                    <th>Networking</th>
                    <th>Payments</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($referrals_sent_table)) :
                    $count_referral = $count_networking = $count_payments = 0;
                    foreach ($referrals_sent_table as $key => $referral) :
                        
                        $type_referral = $type_networking = $type_payments = 0;

                        $type_referral = isset($referral['Referral']) ? $type_referral + $referral['Referral'] : $type_referral;
                        $type_networking = isset($referral['Networking']) ? $type_networking + $referral['Networking'] : $type_networking;
                        $type_payments = isset($referral['Payment']) ? $type_payments + $referral['Payment'] : $type_payments;

                        $count_referral += $type_referral;
                        $count_networking += $type_networking;
                        $count_payments += $type_payments;

                        $count = $type_referral + $type_networking + $type_payments;

                ?>
                        <tr>
                            <td>
                                <?php echo $referral['name']; ?>
                            </td>
                            <td>
                                <?php echo $type_referral; ?>
                            </td>
                            <td>
                                <?php echo $type_networking; ?>
                            </td>
                            <td>
                                <?php echo $type_payments; ?>
                            </td>
                            <td>
                                <?php echo $count; ?>
                            </td>
                        </tr>
                    <?php
                    endforeach; ?>
                    <tr>
                        <td><strong>Total</strong></td>
                        <td><?php echo $count_referral; ?></td>
                        <td><?php echo $count_networking; ?></td>
                        <td><?php echo $count_payments; ?></td>
                        <td><?php echo ($count_referral + $count_networking + $count_payments); ?></td>
                    </tr>
                <?php else : ?>
                    <tr>
                        <td colspan="5">No referrals sent data found within selected date range.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
        $sent_html = ob_get_clean();


        // Process received referrals
        if (!empty($referrals_received)) {
            $referrals_received_table = [];
            foreach ($referrals_received as $referral) {

                $referral_count = 0;
                $recipient_id = $referral['recipient_id'];
                $recipient_name = get_userdata($recipient_id)->display_name ? get_userdata($recipient_id)->display_name : get_userdata($recipient_id)->user_login;

                if (isset($referrals_received_table[$referral['recipient_id']][$referral['type_of_referral']])) {
                    $referral_count = $referrals_received_table[$referral['recipient_id']][$referral['type_of_referral']] + 1;
                } else {
                    $referral_count = 1;
                }

                $referrals_received_table[$referral['recipient_id']]['name'] = $recipient_name;
                $referrals_received_table[$referral['recipient_id']][$referral['type_of_referral']] = $referral_count;
            }
        } 
        
        ob_start(); // Generate HTML for received referrals
        ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Referrals</th>
                    <th>Networking</th>
                    <th>Payments</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($referrals_received_table)) :

                    $count_referral = $count_networking = $count_payments = 0;
                    $type_referral = $type_networking = $type_payments = 0;

                    foreach ($referrals_received_table as $key =>  $referral) :

                        $type_referral = isset($referral['Referral']) ? $type_referral + $referral['Referral'] : $type_referral;
                        $type_networking = isset($referral['Networking']) ? $type_networking + $referral['Networking'] : $type_networking;
                        $type_payments = isset($referral['Payment']) ? $type_payments + $referral['Payment'] : $type_payments;

                        $count_referral += $type_referral;
                        $count_networking += $type_networking;
                        $count_payments += $type_payments;

                        $count = $type_referral + $type_networking + $type_payments;

                ?>
                        <tr>
                            <td>
                                <?php echo $referral['name']; ?>
                            </td>
                            <td>
                                <?php echo $type_referral; ?>
                            </td>
                            <td>
                                <?php echo $type_networking; ?>
                            </td>
                            <td>
                                <?php echo $type_payments; ?>
                            </td>
                            <td>
                                <?php echo $count; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td><strong>Total</strong></td>
                        <td><?php echo $count_referral; ?></td>
                        <td><?php echo $count_networking; ?></td>
                        <td><?php echo $count_payments; ?></td>
                        <td><?php echo ($count_referral + $count_networking + $count_payments); ?></td>
                    </tr>
                <?php else : ?>
                    <tr>
                        <td colspan="5">No referrals received found within selected date range.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
<?php
        $received_html = ob_get_clean();

    if ( empty($referrals_received) && empty($referrals_sent) ) {
        $sent_html = '<p>There is no <strong>sent</strong> referral data available for <strong>' . $sent_selected_chapter .'</strong> in the selected period.</p>';
        $received_html = '<p>There is no <strong>received</strong> referral data available for <strong>' . $recv_selected_chapter . '</strong> in the selected period.</p>';
    }
    echo json_encode(array('sent' => $sent_html, 'received' => $received_html));
    wp_die();
}


/**
 * Retrieve referral data for sent referrals within the specified date range.
 *
 * @param string $start_date The start date of the date range.
 * @param string $end_date   The end date of the date range. 
 * @return array             An array of referral data for sent referrals within the date range.
 */
function get_all_referrals_sent_within_date_range( $start_date, $end_date ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'show_referrals';

    $referrals_all = $wpdb->get_results(
        $wpdb->prepare( "SELECT * FROM $table_name" ), 
        ARRAY_A
    );
        
    // Filter referrals based on the date range
    $referrals_sent = array_filter($referrals_all, function ($referral) use ($start_date, $end_date) {
        $sent_date = date('Y-m-d', strtotime($referral['sent_date']));
        return ($sent_date >= $start_date && $sent_date <= $end_date);
    } );
}