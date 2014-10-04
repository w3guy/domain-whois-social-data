<?php

/*
Plugin Name: Domain Whois and Social Data
Plugin URI: http://sitepoint.com
Description: Display whois and social data of a Domain.
Version: 1.0
Author: Agbonghama Collins
Author URI: http://w3guy.com
License: GPL2
*/


class Domain_Whois_Social_Data extends WP_Widget {


	function __construct() {
		parent::__construct(
			'whois_social_widget', // Base ID
			__( 'Domain Whois and Social Data', 'dwsd' ), // Name
			array( 'description' => __( 'Display whois and social data of a Domain.', 'dwsd' ), ) // Description
		);
	}


	/**
	 * Retrieve the response body of the API GET request and convert it to an object
	 *
	 * @param $domain
	 * @param $api_key
	 *
	 * @return object|mixed
	 */
	public function json_whois_api( $domain, $api_key ) {

		$url = 'http://jsonwhois.com/api/whois/?apiKey=' . $api_key . '&domain=' . $domain;

		$request = wp_remote_get( $url );

		$response_body = wp_remote_retrieve_body( $request );

		$decode_json_to_object = json_decode( $response_body );

		return $decode_json_to_object;

	}


	/**
	 * Get the domain Alexa Rank
	 *
	 * @param object $response_data Json decoded response body
	 *
	 * @return integer
	 */
	public function alexa_rank( $response_data ) {

		return $response_data->alexa->rank;

	}

	/**
	 * Number of times domain have been tweeted
	 *
	 * @param object $response_data Json decoded response body
	 *
	 * @return integer
	 */
	public function twitter_tweets( $response_data ) {

		return $response_data->social->twitter->count;

	}


	/**
	 * Number of times domain have been shared on Facebook
	 *
	 * @param object $response_data Json decoded response body
	 *
	 * @return integer
	 */
	public function facebook_share_count( $response_data ) {

		return $response_data->social->facebook->data[0]->share_count;

	}


	/**
	 * Number of times domain have been liked on Facebook
	 *
	 * @param object $response_data Json decoded response body
	 *
	 * @return mixed
	 */
	public function facebook_like_count( $response_data ) {

		return $response_data->social->facebook->data[0]->like_count;

	}


	/**
	 * Number of times domain have been shared to LinkedIn
	 *
	 * @param object $response_data Json decoded response body
	 *
	 * @return integer
	 */
	public function linkedin_share( $response_data ) {

		return $response_data->social->linkedIn;

	}


	/**
	 * Number of times domain have been shared on Google+
	 *
	 * @param object $response_data Json decoded response body
	 *
	 * @return integer
	 */
	public function google_share( $response_data ) {

		return $response_data->social->google;

	}


	/**
	 * Google Pagerank of Domain
	 *
	 * @param object $response_data Json decoded response body
	 *
	 * @return integer
	 */
	public function google_page_rank( $response_data ) {

		return $response_data->google->rank;

	}


	/**
	 *Domain nameservers
	 *
	 * @param object $response_data Json decoded response body
	 *
	 * @return string
	 */
	public function domain_nameservers( $response_data ) {

		$name_servers = $response_data->whois->domain->nserver;

		return $name_servers->{0} . ' ' . $name_servers->{1};

	}


	/**
	 * Date domain was created
	 *
	 * @param object $response_data Json decoded response body
	 *
	 * @return mixed
	 */
	public function date_created( $response_data ) {

		return $response_data->whois->domain->created;
	}


	/**
	 * Domain expiration date
	 *
	 * @param object $response_data Json decoded response body
	 *
	 * @return mixed
	 */
	public function expiration_date( $response_data ) {

		return $response_data->whois->domain->expires;
	}


	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		// Retrieve the saved widget API key
		$api_key = ! empty( $instance['api_key'] ) ? $instance['api_key'] : '54183ad8c433fac10b6f5d7c';

		// Get the Domain name saved in the widget
		$domain_name = ! empty( $instance['domain_name'] ) ? $instance['domain_name'] : '';

		// JsonWhois API response
		$api_response = $this->json_whois_api( $domain_name, $api_key );

		// Display the widget Title
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}


		echo '<ol>';
		echo '<li> <strong>Alexa Rank:</strong> ', $this->alexa_rank( $api_response ), '</li>';
		echo '<li> <strong>Google Page Rank:</strong> ', $this->google_page_rank( $api_response ), '</li>';
		echo '<li> <strong>Facebook shares:</strong> ', $this->facebook_share_count( $api_response ), '</li>';
		echo '<li> <strong>Facebook likes:</strong> ', $this->facebook_like_count( $api_response ), '</li>';
		echo '<li> <strong>Twitter Tweets:</strong> ', $this->twitter_tweets( $api_response ), '</li>';
		echo '<li> <strong>Google +1:</strong> ', $this->google_share( $api_response ), '</li>';
		echo '<li> <strong>LinkedIn shares:</strong> ', $this->linkedin_share( $api_response ), '</li>';
		echo '<li> <strong>Date created:</strong> ', $this->date_created( $api_response ), '</li>';
		echo '<li> <strong>Expiration date:</strong> ', $this->expiration_date( $api_response ), '</li>';
		echo '<li> <strong>Nameservers:</strong> ', $this->domain_nameservers( $api_response ), '</li>';

		echo '</ol>';


		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @return string
	 */
	public function form( $instance ) {
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = __( 'Domain Whois & Social Data', 'dwsd' );
		}

		$domain_name = isset( $instance['domain_name'] ) ? $instance['domain_name'] : '';

		$api_key = isset( $instance['api_key'] ) ? $instance['api_key'] : '54183ad8c433fac10b6f5d7c';

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
			       name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
			       value="<?php echo esc_attr( $title ); ?>">
		</p>

		<p>
			<label
				for="<?php echo $this->get_field_id( 'domain_name' ); ?>"><?php _e( 'Domain name (without http://)' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'domain_name' ); ?>"
			       name="<?php echo $this->get_field_name( 'domain_name' ); ?>" type="text"
			       value="<?php echo esc_attr( $domain_name ); ?>">
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'api_key' ); ?>"><?php _e( 'API Key)' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'api_key' ); ?>"
			       name="<?php echo $this->get_field_name( 'api_key' ); ?>" type="text"
			       value="<?php echo esc_attr( $api_key ); ?>">
		</p>
	<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                = array();
		$instance['title']       = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['domain_name'] = ( ! empty( $new_instance['domain_name'] ) ) ? strip_tags( $new_instance['domain_name'] ) : '';

		return $instance;
	}

}

// register Domain_Whois_Social_Data widget
function register_whois_social_widget() {
	register_widget( 'Domain_Whois_Social_Data' );
}

add_action( 'widgets_init', 'register_whois_social_widget' );