<?php
/* Template Name: quote page */
get_header();
?>
<?php
$api_url = 'https://api.kanye.rest/'; // API endpoint URL
$quotes_count = 5; // Number of quotes to display

for ($i = 0; $i < $quotes_count; $i++) {
    // Make the API request
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        echo 'Unable to fetch quotes.';
    } else {
        // Get the response body
        $body = wp_remote_retrieve_body($response);

        // Decode the JSON response
        $data = json_decode($body);

        if ($data && isset($data->quote)) {
            $quote = $data->quote;
            echo '<p>' . esc_html($quote) . '</p>';
        } else {
            echo 'No quotes available.';
        }
    }
}
?>

<?php
get_footer();
