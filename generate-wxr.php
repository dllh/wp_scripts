<?php 

$w = new WXR_Generator();
$w->dump();

class WXR_Generator {

	function __construct() {
		$this->post_count = 10;
		$this->comments_per_post = 5;
		$this->tag_count = 5;
		$this->cat_count = 5;
		// For setting minimum tag and category ids (not essential most of the time).
		$this->tag_min_id = 1;
		$this->cat_min_id = 1;
		$this->author_count = 1;
		$this->term_prefixes = array();

		$this->authors = $this->categories = $this->tags = array();
	}


	function generate_authors() {
		for ( $i = $this->author_count; $i > 0; $i-- ) {
				$this->authors[] = 'author' . $i;
			?>
				<wp:author><wp:author_id><?php echo $i; ?></wp:author_id><wp:author_login>author<?php echo $i; ?></wp:author_login><wp:author_email>author<?php echo $i; ?>@automattic.com</wp:author_email><wp:author_display_name><![CDATA[Author<?php echo $i; ?>]]></wp:author_display_name><wp:author_first_name><![CDATA[]]></wp:author_first_name><wp:author_last_name><![CDATA[]]></wp:author_last_name></wp:author>
			<?php
		}	
	}

	function generate_categories() {
		for ( $i = $this->cat_min_id; $i < ( $this->cat_min_id + $this->cat_count ); $i++ ) {
				$this->categories[] = $i;
			?>
				<wp:category><wp:term_id><?php echo $i; ?></wp:term_id><wp:category_nicename><?php echo $this->get_term_slug( 'category', $i ); ?></wp:category_nicename><wp:category_parent></wp:category_parent><wp:cat_name><![CDATA[<?php echo $this->get_term_name( 'category', $i ); ?>]]></wp:cat_name></wp:category>
			<?php
		}
	}

	function generate_tags() {
		for ( $i = $this->tag_min_id; $i < ( $this->tag_min_id + $this->tag_count ); $i++ ) {
				$this->tags[] = $i;
			?>
				<wp:term><wp:term_id><?php echo $i; ?></wp:term_id><wp:term_taxonomy>post_tag</wp:term_taxonomy><wp:term_slug><?php echo $this->get_term_slug( 'tag', $i ); ?></wp:term_slug><wp:term_name><![CDATA[<?php echo $this->get_term_name( 'tag', $i ); ?>]]></wp:term_name></wp:term>
			<?php
		}
	}

	// Generate a randomish string for inclusion in tag and category slugs and names.
	function get_random_term_prefix( $taxonomy, $id ) {
		if ( ! array_key_exists( $taxonomy . '-' . $id, $this->term_prefixes ) ) {
			$randomish_string = $taxonomy . $id . mt_rand();
			$randomish_string = md5( $randomish_string );
			$randomish_string = str_shuffle( $randomish_string );
			$randomish_string = substr( $randomish_string, 0, 10 );

			$this->term_prefixes[ $taxonomy . '-' . $id ] = $randomish_string;
		}

		return $this->term_prefixes[ $taxonomy . '-' . $id ];

	}

	// Helper function because we want to be consistent in this in both the category listing near the top of the wxr and in the per-post categories.
	function get_term_name( $taxonomy, $id ) {
		$prefix = $this->get_random_term_prefix( $taxonomy, $id );
		return ucfirst( $taxonomy ) . ' ' . $prefix . ' ' . $id;
	}

	// Helper function because we want to be consistent in this in both the category listing near the top of the wxr and in the per-post categories.
	function get_term_slug( $taxonomy, $id ) {
		$prefix = $this->get_random_term_prefix( $taxonomy, $id );
		return $taxonomy . '-' . $prefix . '-' . $id;
	}

	function generate_posts() {

		$running_comment_count = 0;

		for ( $i = 1; $i <= $this->post_count; $i++ ) {
			$now = time();
			$timestamp = rand( $now - ( 60 * 86400 ), $now );
			$slug_date = @date( 'Y/m', $timestamp );

			?>
			<item>
				<title>Post Number <?php echo $i; ?></title>
				<link>http://oddbird.org/wptrunk/<?php echo $slug_date; ?>/post-number-<?php echo $i; ?>/</link>
				<pubDate><?php echo @date( 'r', $timestamp ); ?></pubDate>
				<dc:creator><?php echo $this->authors[ array_rand( $this->authors ) ]; ?></dc:creator>
				<guid isPermaLink="false">http://oddbird.org/wptrunk/?p=<?php echo $i; ?></guid>
				<description></description>
				<content:encoded><![CDATA[<?php echo $this->get_random_text(); ?>]]></content:encoded>
				<excerpt:encoded><![CDATA[]]></excerpt:encoded>
				<wp:post_id><?php echo $i; ?></wp:post_id>
				<wp:post_date><?php echo @date( 'Y-m-d H:i:s', $timestamp ); ?></wp:post_date>
				<wp:post_date_gmt><?php echo gmdate( 'Y-m-d H:i:s', $timestamp ); ?></wp:post_date_gmt>
				<wp:comment_status>open</wp:comment_status>
				<wp:ping_status>open</wp:ping_status>
				<wp:post_name>post-number-<?php echo $i; ?></wp:post_name>
				<wp:status>publish</wp:status>
				<wp:post_parent>0</wp:post_parent>
				<wp:menu_order>0</wp:menu_order>
				<wp:post_type>post</wp:post_type>
				<wp:post_password></wp:post_password>
				<wp:is_sticky>0</wp:is_sticky>

				<?php
					$category_keys = array_rand( $this->categories, 5 );
					foreach ( $category_keys as $category_key => $category_id ) {
				?>
						<category domain="category" nicename="<?php echo $this->get_term_slug( 'category', $category_id ); ?>"><![CDATA[<?php echo $this->get_term_name( 'category', $category_id ); ?>]]></category>
				
				<?php	
					}
				?>

				<?php
					$tag_keys = array_rand( $this->tags, 5 );
					foreach ( $tag_keys as $tag_key => $tag_id ) {
				?>
						<category domain="post_tag" nicename="<?php echo $this->get_term_slug( 'tag', $tag_id ); ?>"><![CDATA[<?php echo $this->get_term_name( 'tag', $tag_id ); ?>]]></category>
				
				<?php	
					}
				?>

				<?php
					for ( $j = $this->comments_per_post; $j > 0; $j-- ) {
						$comment_timestamp = rand( $timestamp, $now );
						$running_comment_count++;
				?>
						<wp:comment>
							<wp:comment_id><?php echo $running_comment_count; ?></wp:comment_id>
							<wp:comment_author><![CDATA[<?php echo $this->authors[ array_rand( $this->authors ) ]; ?>]]></wp:comment_author>
							<wp:comment_author_email></wp:comment_author_email>
							<wp:comment_author_url>http://oddbird.org/</wp:comment_author_url>
							<wp:comment_author_IP></wp:comment_author_IP>
							<wp:comment_date><?php echo @date( 'Y-m-d H:i:s', $comment_timestamp ); ?></wp:comment_date>
							<wp:comment_date_gmt><?php echo gmdate( 'Y-m-d H:i:s', $comment_timestamp ); ?></wp:comment_date_gmt>
							<wp:comment_content><![CDATA[<?php echo $this->get_random_text(); ?>]]></wp:comment_content>
							<wp:comment_approved>1</wp:comment_approved>
							<wp:comment_type></wp:comment_type>
							<wp:comment_parent>0</wp:comment_parent>
							<wp:comment_user_id>0</wp:comment_user_id>
						</wp:comment>
				<?php } ?>

			</item>
			<?php

		}
	}

	function get_random_text() {
		$words = 'lorem ipsum dolor sit amet consectetur adipisicing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur excepteur sint occaecat cupidatat non proident sunt in culpa qui officia deserunt mollit anim id est laborum';
		$words = explode( ' ', $words );

		$out = array();

		$sentence_count = rand( 1, 7 );

		for ( $i = 1; $i <= $sentence_count; $i++ ) {
			shuffle( $words );
			$words_in_sentence = array_slice( $words, 0, rand( 5, count( $words ) ) );
			$out[] = ucfirst( array_pop( $words_in_sentence ) ) . ' ' . implode( ' ', $words_in_sentence ) . '.';
		}
		return implode( "\n\n", $out );

	}

	function dump() {
		echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n"; 

		?>
		<!-- generator="WordPress/3.4-alpha-19643" created="2012-01-11 18:25" -->
		<rss version="2.0"
			xmlns:excerpt="http://wordpress.org/export/1.1/excerpt/"
			xmlns:content="http://purl.org/rss/1.0/modules/content/"
			xmlns:wfw="http://wellformedweb.org/CommentAPI/"
			xmlns:dc="http://purl.org/dc/elements/1.1/"
			xmlns:wp="http://wordpress.org/export/1.1/"
		>

		<channel>
			<title>WP Trunk</title>
			<link>http://oddbird.org/wptrunk</link>
			<description>Just another WordPress site</description>
			<pubDate>Wed, 11 Jan 2012 18:25:58 +0000</pubDate>
			<language>en</language>
			<wp:wxr_version>1.1</wp:wxr_version>
			<wp:base_site_url>http://oddbird.org/wptrunk</wp:base_site_url>
			<wp:base_blog_url>http://oddbird.org/wptrunk</wp:base_blog_url>

			<?php $this->generate_authors(); ?>

			<?php $this->generate_categories(); ?>

			<?php $this->generate_tags(); ?>

			<generator>http://wordpress.org/?v=3.4-alpha-19643</generator>

			<?php $this->generate_posts(); ?>

		</channel>
		</rss>
<?php
	}
}
