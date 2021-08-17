<?php

define( 'TK_WXR_VERSION', '1.2' );

/**
 * This is an object version of wordpresses export_wp function
 * Which is crap because you cannot use it to generate multiple
 * export files. That code seriously needs to be refactored,
 * until then we will use this.
 *
 *
 * Version number for the export format.
 * Bump this when something changes that might affect compatibility.
 * @since 2.5.0
 */
class Tk_Export
{
    /**
     * @var wpdb|null
     */
    protected $wpdb = null;

    /**
     * @var array|WP_Post|null
     */
    protected $post = null;

    /**
     * @var array
     */
    protected $defaults = array(
        'content' => 'all',
        'author' => false,
        'category' => false,
        'start_date' => false,
        'end_date' => false,
        'status' => false,
    );

    /**
     * @var array
     */
    protected $args = array();


    /**
     * Tk_Export constructor.
     */
    public function __construct()
    {
        global $wpdb, $post;
        $this->wpdb = $wpdb;
        $this->post = $post;

        add_filter('wxr_export_skip_postmeta', array($this, 'wxr_filter_postmeta'), 10, 2);
    }


    /**
     * Generates the WXR export file for download.
     *
     * Default behavior is to export all content, however, note that post content will only
     * be exported for post types with the `can_export` argument enabled. Any posts with the
     * 'auto-draft' status will be skipped.
     *
     * @param array $args {
     *     Optional. Arguments for generating the WXR export file for download. Default empty array.
     *
     * @type string $content Type of content to export. If set, only the post content of this post type
     *                                  will be exported. Accepts 'all', 'post', 'page', 'attachment', or a defined
     *                                  custom post. If an invalid custom post type is supplied, every post type for
     *                                  which `can_export` is enabled will be exported instead. If a valid custom post
     *                                  type is supplied but `can_export` is disabled, then 'posts' will be exported
     *                                  instead. When 'all' is supplied, only post types with `can_export` enabled will
     *                                  be exported. Default 'all'.
     * @type string $author Author to export content for. Only used when `$content` is 'post', 'page', or
     *                                  'attachment'. Accepts false (all) or a specific author ID. Default false (all).
     * @type string $category Category (slug) to export content for. Used only when `$content` is 'post'. If
     *                                  set, only post content assigned to `$category` will be exported. Accepts false
     *                                  or a specific category slug. Default is false (all categories).
     * @type string $start_date Start date to export content from. Expected date format is 'Y-m-d'. Used only
     *                                  when `$content` is 'post', 'page' or 'attachment'. Default false (since the
     *                                  beginning of time).
     * @type string $end_date End date to export content to. Expected date format is 'Y-m-d'. Used only when
     *                                  `$content` is 'post', 'page' or 'attachment'. Default false (latest publish date).
     * @type string $status Post status to export posts for. Used only when `$content` is 'post' or 'page'.
     *                                  Accepts false (all statuses except 'auto-draft'), or a specific status, i.e.
     *                                  'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', or
     *                                  'trash'. Default false (all statuses except 'auto-draft').
     * }
     * @global wpdb $wpdb WordPress database abstraction object.
     * @global WP_Post $post Global `$post`.
     *
     * @since 2.1.0
     * @return string
     */
    public function export($args = array(), $download = false)
    {
        $this->args = wp_parse_args($args, $this->defaults);

        /**
         * Fires at the beginning of an export, before any headers are sent.
         * @param array $args An array of export arguments.
         * @since 2.3.0
         */
        do_action('export_wp', $this->args);

        if ($download) {
            $sitename = sanitize_key(get_bloginfo('name'));
            if (!empty($sitename)) {
                $sitename .= '.';
            }
            $date = date('Y-m-d');
            $wp_filename = $sitename . 'WordPress.' . $date . '.xml';

            /**
             * Filters the export filename.
             * @param string $wp_filename The name of the file for download.
             * @param string $sitename The site name.
             * @param string $date Today's date, formatted.
             * @since 4.4.0
             */
            $filename = apply_filters('export_wp_filename', $wp_filename, $sitename, $date);

            header('Content-Description: File Transfer');
            header('Content-Disposition: attachment; filename=' . $filename);
            header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
        }

        if ('all' != $this->args['content'] && post_type_exists($this->args['content'])) {
            $ptype = get_post_type_object($this->args['content']);
            if (!$ptype->can_export) {
                $this->args['content'] = 'post';
            }
            //$where = $this->wpdb->prepare("{$this->wpdb->posts}.post_type = %s", 'listings');
            //$where = $this->wpdb->prepare("{$this->wpdb->posts}.post_type = %s AND {$this->wpdb->posts}.post_status = %s ", $this->args['content'], 'publish');
            $where = $this->wpdb->prepare("({$this->wpdb->posts}.post_type = %s OR {$this->wpdb->posts}.post_type = 'attachment')", $this->args['content']);

//            $where = $this->wpdb->prepare("({$this->wpdb->posts}.post_type = %s OR ( {$this->wpdb->posts}.post_type = 'attachment' AND {$this->wpdb->posts}.post_parent IN (
//    SELECT ID FROM {$this->wpdb->posts} WHERE 1
//    )) )", $this->args['content']);

        } else {
            $post_types = get_post_types(array('can_export' => true));
            $esses = array_fill(0, count($post_types), '%s');
            $where = $this->wpdb->prepare("{$this->wpdb->posts}.post_type IN (" . implode(',', $esses) . ')', $post_types);
        }

        if ($this->args['status'] && ('listings' == $this->args['content'] || 'post' == $this->args['content'] || 'page' == $this->args['content'])) {
            $where .= $this->wpdb->prepare(" AND {$this->wpdb->posts}.post_status = %s", $this->args['status']);
        } else {
            $where .= " AND {$this->wpdb->posts}.post_status != 'auto-draft'";
            //$where .= " AND ({$this->wpdb->posts}.post_status != 'auto-draft' AND {$this->wpdb->posts}.post_status != 'draft')";
        }

        $join = '';
        if ($this->args['category'] && 'post' == $this->args['content']) {
            if ($term = term_exists($this->args['category'], 'category')) {
                $join = "INNER JOIN {$this->wpdb->term_relationships} ON ({$this->wpdb->posts}.ID = {$this->wpdb->term_relationships}.object_id)";
                $where .= $this->wpdb->prepare(" AND {$this->wpdb->term_relationships}.term_taxonomy_id = %d", $term['term_taxonomy_id']);
            }
        }

        if ('post' == $this->args['content'] || 'page' == $this->args['content'] || 'listings' == $this->args['content'] || 'attachment' == $this->args['content']) {
            if ($this->args['author']) {
                $where .= $this->wpdb->prepare(" AND {$this->wpdb->posts}.post_author = %d", $this->args['author']);
            }

            if ($this->args['start_date']) {
                $where .= $this->wpdb->prepare(" AND {$this->wpdb->posts}.post_date >= %s", date('Y-m-d', strtotime($this->args['start_date'])));
            }

            if ($this->args['end_date']) {
                $where .= $this->wpdb->prepare(" AND {$this->wpdb->posts}.post_date < %s", date('Y-m-d', strtotime('+1 month', strtotime($this->args['end_date']))));
            }
        }

        $limit = '';
        if (isset($this->args['limit'])) {
            $limit = 'Limit ' . $this->args['limit'];
            if (isset($this->args['offset'])) {
                $limit = 'Limit ' . $this->args['offset'] . ', ' . $this->args['limit'];;
            }
        }

        // Grab a snapshot of post IDs, just in case it changes during the export.
        $sql = "SELECT ID FROM {$this->wpdb->posts} $join WHERE $where $limit";
//        error_log(print_r($this->args, true));
//        error_log($sql);
        $post_ids = $this->wpdb->get_col($sql);

        // Get all attachments
        $sql = "SELECT p.* 
FROM {$this->wpdb->posts} p
WHERE post_type='attachment'
-- AND post_mime_type LIKE 'image%'
AND post_parent IN (
    SELECT ID FROM {$this->wpdb->posts} $join WHERE $where
)
";
        $post_ids = array_merge($post_ids, $this->wpdb->get_col($sql));

        /*
         * Get the requested terms ready, empty unless posts filtered by category
         * or all content.
         */
        $cats = $tags = $terms = array();
        if (isset($term) && $term) {
            $cat = get_term($term['term_id'], 'category');
            $cats = array($cat->term_id => $cat);
            unset($term, $cat);
        } elseif ('all' == $args['content']) {
            $categories = (array)get_categories(array('get' => 'all'));
            $tags = (array)get_tags(array('get' => 'all'));

            $custom_taxonomies = get_taxonomies(array('_builtin' => false));
            $custom_terms = (array)get_terms($custom_taxonomies, array('get' => 'all'));

            // Put categories in order with no child going before its parent.
            while ($cat = array_shift($categories)) {
                if ($cat->parent == 0 || isset($cats[$cat->parent])) {
                    $cats[$cat->term_id] = $cat;
                } else {
                    $categories[] = $cat;
                }
            }

            // Put terms in order with no child going before its parent.
            while ($t = array_shift($custom_terms)) {
                if ($t->parent == 0 || isset($terms[$t->parent])) {
                    $terms[$t->term_id] = $t;
                } else {
                    $custom_terms[] = $t;
                }
            }

            unset($categories, $custom_taxonomies, $custom_terms);
        }

        $xml = '<?xml version="1.0" encoding="' . get_bloginfo('charset') . "\" ?>\n";
        /*
          This is a WordPress eXtended RSS file generated by WordPress as an export of your site.
          It contains information about your site's posts, pages, comments, categories, and other content.
          You may use this file to transfer that content from one site to another.
          This file is not intended to serve as a complete backup of your site.

          To import this information into a WordPress site follow these steps:
          1. Log in to that site as an administrator.
          2. Go to Tools: Import in the WordPress admin panel.
          3. Install the "WordPress" importer from the list.
          4. Activate & Run Importer.
          5. Upload this file using the form provided on that page.
          6. You will first be asked to map the authors in this export file to users
             on the site. For each author, you may choose to map to an
             existing user on the site or to create a new user.
          7. WordPress will then import each of the posts, pages, comments, categories, etc.
             contained in this file into your site.
        */

        // TODO: check this as I am not sure what it does???
        ob_start();
        the_generator('export');
        $xml .= ob_get_contents();
        ob_end_clean();

        $wxrVer = TK_WXR_VERSION;
        $date = date('D, d M Y H:i:s +0000');
        $xml .= <<<XML
<rss version="2.0"
  xmlns:excerpt="http://wordpress.org/export/$wxrVer/excerpt/"
  xmlns:content="http://purl.org/rss/1.0/modules/content/"
  xmlns:wfw="http://wellformedweb.org/CommentAPI/"
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xmlns:wp="http://wordpress.org/export/$wxrVer/"
>
<channel>
  <title>{$this->bloginfo_rss('name')}</title>
  <link>{$this->bloginfo_rss('url')}</link>
  <description>{$this->bloginfo_rss('description')}</description>
  <pubDate>$date</pubDate>
  <language>{$this->bloginfo_rss('language')}</language>
  <wp:wxr_version>$wxrVer</wp:wxr_version>
  <wp:base_site_url>{$this->wxr_site_url()}</wp:base_site_url>
  <wp:base_blog_url>{$this->bloginfo_rss('url')}</wp:base_blog_url>

XML;
        $xml .= $this->wxr_authors_list($post_ids);

        foreach ($cats as $c) {
            $xml .= <<<XML
  <wp:category>
    <wp:term_id>{intval($c->term_id)}</wp:term_id>
    <wp:category_nicename>{$this->wxr_cdata($c->slug)}</wp:category_nicename>
    <wp:category_parent>{$this->wxr_cdata($c->parent ? $cats[$c->parent]->slug : '')}</wp:category_parent>
    {$this->wxr_cat_name($c)}
    {$this->wxr_category_description($c)}
    {$this->wxr_term_meta($c)}
    ?>
  </wp:category>
XML;
        }

        foreach ($tags as $t) {
            $xml .= <<<XML
    <wp:tag>
    <wp:term_id>{intval($t->term_id)}</wp:term_id>
    <wp:tag_slug>{$this->wxr_cdata($t->slug)}</wp:tag_slug>
    {$this->wxr_tag_name($t)}
    {$this->wxr_tag_description($t)}
    {$this->wxr_term_meta($t)}
  </wp:tag>
XML;
        }

        foreach ($terms as $t) {
            $xml .= <<<XML
    <wp:term>
    <wp:term_id>{$this->wxr_cdata($t->term_id)}</wp:term_id>
    <wp:term_taxonomy>{$this->wxr_cdata($t->taxonomy)}</wp:term_taxonomy>
    <wp:term_slug>{$this->wxr_cdata($t->slug)}</wp:term_slug>
    <wp:term_parent>{$this->wxr_cdata($t->parent ? $terms[$t->parent]->slug : '')}</wp:term_parent>
    {$this->wxr_term_name($t)}
    {$this->wxr_term_description($t)}
    {$this->wxr_term_meta($t)}
  </wp:term>
XML;
        }
        if ('all' == $args['content']) {
            $xml .= $this->wxr_nav_menu_terms();
        }

        // TODO: check this as I am not sure what it does???
        ob_start();
        /** This action is documented in wp-includes/feed-rss2.php */
        do_action('rss2_head');
        $xml .= ob_get_contents();
        ob_end_clean();

        if ($post_ids) {
            /**
             * @global WP_Query $wp_query
             */
            global $wp_query;
            // Fake being in the loop.
            $wp_query->in_the_loop = true;

            // Fetch 20 posts at a time rather than loading the entire table into memory.
            while ($next_posts = array_splice($post_ids, 0, 20)) {
                $where = 'WHERE ID IN (' . join(',', $next_posts) . ')';
                $posts = $this->wpdb->get_results("SELECT * FROM {$this->wpdb->posts} $where");

                // Begin Loop.
                foreach ($posts as $post) {
                    setup_postdata($post);

                    /** This filter is documented in wp-includes/feed.php */
                    $title = apply_filters('the_title_rss', $post->post_title);

                    /**
                     * Filters the post content used for WXR exports.
                     *
                     * @param string $post_content Content of the current post.
                     * @since 2.5.0
                     *
                     */
                    $content = $this->wxr_cdata(apply_filters('the_content_export', $post->post_content));

                    /**
                     * Filters the post excerpt used for WXR exports.
                     *
                     * @param string $post_excerpt Excerpt for the current post.
                     * @since 2.6.0
                     *
                     */
                    $excerpt = $this->wxr_cdata(apply_filters('the_excerpt_export', $post->post_excerpt));

                    $is_sticky = is_sticky($post->ID) ? 1 : 0;

                    // Rss permalink
                    ob_start();
                    the_permalink_rss();
                    $rssPermalink = ob_get_contents();
                    ob_end_clean();

                    //
                    $mysqlDate = mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false);


                    ob_start();
                    the_guid();
                    $guid = ob_get_contents();
                    ob_end_clean();

                    $xml .= <<<XML
  <item>
    <title>$title</title>
    <link>$rssPermalink</link>
    <pubDate>$mysqlDate</pubDate>
    <dc:creator>{$this->wxr_cdata(get_the_author_meta('login'))}</dc:creator>
    <guid isPermaLink="false">$guid</guid>
    <description></description>
    <content:encoded>{$content}</content:encoded>
    <excerpt:encoded>{$excerpt}</excerpt:encoded>
    <wp:post_id>{$this->intval($post->ID)}</wp:post_id>
    <wp:post_date>{$this->wxr_cdata($post->post_date)}</wp:post_date>
    <wp:post_date_gmt>{$this->wxr_cdata($post->post_date_gmt)}</wp:post_date_gmt>
    <wp:comment_status>{$this->wxr_cdata($post->comment_status)}</wp:comment_status>
    <wp:ping_status>{$this->wxr_cdata($post->ping_status)}</wp:ping_status>
    <wp:post_name>{$this->wxr_cdata($post->post_name)}</wp:post_name>
    <wp:status>{$this->wxr_cdata($post->post_status)}</wp:status>
    <wp:post_parent>{$this->intval($post->post_parent)}</wp:post_parent>
    <wp:menu_order>{$this->intval($post->menu_order)}</wp:menu_order>
    <wp:post_type>{$this->wxr_cdata($post->post_type)}</wp:post_type>
    <wp:post_password>{$this->wxr_cdata($post->post_password)}</wp:post_password>
    <wp:is_sticky>{$this->intval($is_sticky)}</wp:is_sticky>\n
XML;
                    if ($post->post_type == 'attachment') {
                        $xml .= <<<XML
    <wp:attachment_url>{$this->wxr_cdata(wp_get_attachment_url($post->ID))}</wp:attachment_url>\n
XML;
                    }

                    $xml .= $this->wxr_post_taxonomy($post);

                    $postmeta = $this->wpdb->get_results($this->wpdb->prepare("SELECT * FROM {$this->wpdb->postmeta} WHERE post_id = %d", $post->ID));
                    foreach ($postmeta as $meta) {
                        /**
                         * Filters whether to selectively skip post meta used for WXR exports.
                         *
                         * Returning a truthy value to the filter will skip the current meta
                         * object from being exported.
                         *
                         * @param bool $skip Whether to skip the current post meta. Default false.
                         * @param string $meta_key Current meta key.
                         * @param object $meta Current meta object.
                         * @since 3.3.0
                         *
                         */
                        if (apply_filters('wxr_export_skip_postmeta', false, $meta->meta_key, $meta)) {
                            continue;
                        }
                        $xml .= <<<XML
    <wp:postmeta>
      <wp:meta_key>{$this->wxr_cdata($meta->meta_key)}</wp:meta_key>
      <wp:meta_value>{$this->wxr_cdata($meta->meta_value)}</wp:meta_value>
    </wp:postmeta>\n
XML;
                    }


                    $_comments = $this->wpdb->get_results($this->wpdb->prepare("SELECT * FROM {$this->wpdb->comments} WHERE comment_post_ID = %d AND comment_approved <> 'spam'", $post->ID));
                    $comments = array_map('get_comment', $_comments);
                    foreach ($comments as $c) {
                        $xml .= <<<XML
    <wp:comment>
      <wp:comment_id>{$this->intval($c->comment_ID)}</wp:comment_id>
      <wp:comment_author>{$this->wxr_cdata($c->comment_author)}</wp:comment_author>
      <wp:comment_author_email>{$this->wxr_cdata($c->comment_author_email)}</wp:comment_author_email>
      <wp:comment_author_url>{$this->esc_url_raw($c->comment_author_url)}</wp:comment_author_url>
      <wp:comment_author_IP>{$this->wxr_cdata($c->comment_author_IP)}</wp:comment_author_IP>
      <wp:comment_date>{$this->wxr_cdata($c->comment_date)}</wp:comment_date>
      <wp:comment_date_gmt>{$this->wxr_cdata($c->comment_date_gmt)}</wp:comment_date_gmt>
      <wp:comment_content>{$this->wxr_cdata($c->comment_content)}</wp:comment_content>
      <wp:comment_approved>{$this->wxr_cdata($c->comment_approved)}</wp:comment_approved>
      <wp:comment_type>{$this->wxr_cdata( $c->comment_type )}</wp:comment_type>
      <wp:comment_parent>{$this->intval( $c->comment_parent )}</wp:comment_parent>
      <wp:comment_user_id>{$this->intval( $c->user_id )}</wp:comment_user_id>\n
XML;

                        $c_meta = $this->wpdb->get_results($this->wpdb->prepare("SELECT * FROM {$this->wpdb->commentmeta} WHERE comment_id = %d", $c->comment_ID));
                        foreach ($c_meta as $meta) {
                            /**
                             * Filters whether to selectively skip comment meta used for WXR exports.
                             *
                             * Returning a truthy value to the filter will skip the current meta
                             * object from being exported.
                             *
                             * @param bool $skip Whether to skip the current comment meta. Default false.
                             * @param string $meta_key Current meta key.
                             * @param object $meta Current meta object.
                             * @since 4.0.0
                             *
                             */
                            if (apply_filters('wxr_export_skip_commentmeta', false, $meta->meta_key, $meta)) {
                                continue;
                            }

                            $xml .= <<<XML
        <wp:commentmeta>
          <wp:meta_key>{$this->wxr_cdata($meta->meta_key)}</wp:meta_key>
          <wp:meta_value>{$this->wxr_cdata($meta->meta_value )}</wp:meta_value>
        </wp:commentmeta>\n
XML;
                        }
                        $xml .= <<<XML
      </wp:comment>\n
XML;
                    }
                    $xml .= <<<XML
    </item>\n
XML;

                }
            }


        }

        $xml .= <<<XML
  </channel>
</rss>
XML;

        if ($download)
            echo $xml;

        return $xml;
    }  // end export()


    /**
     * @param string $show
     */
    protected function bloginfo_rss($show = '')
    {
        return get_bloginfo_rss($show);
    }

    /**
     * @param $i
     * @return int
     */
    protected function intval($i)
    {
        return intval($i);
    }

    /**
     * @param $url
     * @return string
     */
    protected function esc_url_raw($url)
    {
        return esc_url_raw($url);
    }

    /**
     * Wrap given string in XML CDATA tag.
     * @param string $str String to wrap in XML CDATA tag.
     * @return string
     * @since 2.1.0
     */
    protected function wxr_cdata($str)
    {
        if (!seems_utf8($str)) {
            $str = utf8_encode($str);
        }
        // $str = ent2ncr(esc_html($str));
        $str = '<![CDATA[' . str_replace(']]>', ']]]]><![CDATA[>', $str) . ']]>';
        return $str;
    }

    /**
     * Return the URL of the site
     * @return string Site URL.
     * @since 2.5.0
     */
    protected function wxr_site_url()
    {
        if (is_multisite()) {
            // Multisite: the base URL.
            return network_home_url();
        } else {
            // WordPress (single site): the blog URL.
            return get_bloginfo_rss('url');
        }
    }

    /**
     * Output a cat_name XML tag from a given category object
     * @param object $category Category Object
     * @return string
     * @since 2.1.0
     */
    protected function wxr_cat_name($category)
    {
        if (empty($category->name)) {
            return '';
        }
        return '<wp:cat_name>' . $this->wxr_cdata($category->name) . "</wp:cat_name>\n";
    }

    /**
     * Output a category_description XML tag from a given category object
     * @param object $category Category Object
     * @return string
     * @since 2.1.0
     */
    protected function wxr_category_description($category)
    {
        if (empty($category->description)) {
            return '';
        }
        return '<wp:category_description>' . $this->wxr_cdata($category->description) . "</wp:category_description>\n";
    }

    /**
     * Output a tag_name XML tag from a given tag object
     * @param object $tag Tag Object
     * @return string
     * @since 2.3.0
     */
    protected function wxr_tag_name($tag)
    {
        if (empty($tag->name)) {
            return '';
        }
        return '<wp:tag_name>' . $this->wxr_cdata($tag->name) . "</wp:tag_name>\n";
    }

    /**
     * Output a tag_description XML tag from a given tag object
     * @param object $tag Tag Object
     * @return string
     * @since 2.3.0
     */
    protected function wxr_tag_description($tag)
    {
        if (empty($tag->description)) {
            return '';
        }
        return '<wp:tag_description>' . $this->wxr_cdata($tag->description) . "</wp:tag_description>\n";
    }

    /**
     * Output a term_name XML tag from a given term object
     * @param object $term Term Object
     * @return string
     * @since 2.9.0
     */
    protected function wxr_term_name($term)
    {
        if (empty($term->name)) {
            return '';
        }
        return '<wp:term_name>' . $this->wxr_cdata($term->name) . "</wp:term_name>\n";
    }

    /**
     * Output a term_description XML tag from a given term object
     * @param object $term Term Object
     * @return string
     * @since 2.9.0
     */
    protected function wxr_term_description($term)
    {
        if (empty($term->description)) {
            return '';
        }
        return "<wp:term_description>" . $this->wxr_cdata($term->description) . "</wp:term_description>\n";
    }

    /**
     * Output term meta XML tags for a given term object.
     * @param WP_Term $term Term object.
     * @return string
     * @since 4.6.0
     */
    protected function wxr_term_meta($term)
    {
        $termmeta = $this->wpdb->get_results($this->wpdb->prepare("SELECT * FROM $this->wpdb->termmeta WHERE term_id = %d", $term->term_id));
        $str = '';
        foreach ($termmeta as $meta) {
            /**
             * Filters whether to selectively skip term meta used f
             * $str = '';or WXR exports.
             * Returning a truthy value to the filter will skip the current meta
             * object from being exported.
             *
             * @param bool $skip Whether to skip the current piece of term meta. Default false.
             * @param string $meta_key Current meta key.
             * @param object $meta Current meta object.
             * @since 4.6.0
             */
            if (!apply_filters('wxr_export_skip_termmeta', false, $meta->meta_key, $meta)) {
                $str .= sprintf("    <wp:termmeta>\n        <wp:meta_key>%s</wp:meta_key>\n        <wp:meta_value>%s</wp:meta_value>\n        </wp:termmeta>\n", $this->wxr_cdata($meta->meta_key), $this->wxr_cdata($meta->meta_value));
            }
        }
        return $str;
    }

    /**
     * Output list of authors with posts
     * @param int[] $post_ids Optional. Array of post IDs to filter the query by.
     * @return string
     * @since 3.1.0
     * @global wpdb $wpdb WordPress database abstraction object.
     */
    protected function wxr_authors_list(array $post_ids = null)
    {
        if (!empty($post_ids)) {
            $post_ids = array_map('absint', $post_ids);
            $and = 'AND ID IN ( ' . implode(', ', $post_ids) . ')';
        } else {
            $and = '';
        }

        $authors = array();
        $results = $this->wpdb->get_results("SELECT DISTINCT post_author FROM {$this->wpdb->posts} WHERE post_status != 'auto-draft' $and");
        foreach ((array)$results as $result) {
            $authors[] = get_userdata($result->post_author);
        }
        $authors = array_filter($authors);

        $str = '';
        foreach ($authors as $author) {
            $str .= "  <wp:author>";
            $str .= '    <wp:author_id>' . intval($author->ID) . '</wp:author_id>';
            $str .= '    <wp:author_login>' . $this->wxr_cdata($author->user_login) . '</wp:author_login>';
            $str .= '    <wp:author_email>' . $this->wxr_cdata($author->user_email) . '</wp:author_email>';
            $str .= '    <wp:author_display_name>' . $this->wxr_cdata($author->display_name) . '</wp:author_display_name>';
            $str .= '    <wp:author_first_name>' . $this->wxr_cdata($author->first_name) . '</wp:author_first_name>';
            $str .= '    <wp:author_last_name>' . $this->wxr_cdata($author->last_name) . '</wp:author_last_name>';
            $str .= "  </wp:author>\n";
        }
        return $str;
    }

    /**
     * Output all navigation menu terms
     *
     * @return string
     * @since 3.1.0
     */
    protected function wxr_nav_menu_terms()
    {
        $nav_menus = wp_get_nav_menus();
        if (empty($nav_menus) || !is_array($nav_menus)) {
            return '';
        }

        $str = '';
        foreach ($nav_menus as $menu) {
            $str .= "  <wp:term>";
            $str .= '    <wp:term_id>' . intval($menu->term_id) . '</wp:term_id>';
            $str .= '    <wp:term_taxonomy>nav_menu</wp:term_taxonomy>';
            $str .= '    <wp:term_slug>' . $this->wxr_cdata($menu->slug) . '</wp:term_slug>';
            $str .= $this->wxr_term_name($menu);
            $str .= "  </wp:term>\n";
        }
        return $str;
    }

    /**
     * Output list of taxonomy terms, in XML tag format, associated with a post
     *
     * @return string
     * @since 2.3.0
     */
    protected function wxr_post_taxonomy($post)
    {
        //$post = get_post();
        $taxonomies = get_object_taxonomies($post->post_type);
        if (empty($taxonomies)) {
            return '';
        }
        $terms = wp_get_object_terms($post->ID, $taxonomies);
        $str = '';
        foreach ((array)$terms as $term) {
            $str .= "    <category domain=\"{$term->taxonomy}\" nicename=\"{$term->slug}\">" . $this->wxr_cdata($term->name) . "</category>\n";
        }
        return $str;
    }

    /**
     * For filter 'wxr_export_skip_postmeta'
     *
     * @param bool $return_me
     * @param string $meta_key
     * @return bool
     */
    public function wxr_filter_postmeta($return_me, $meta_key)
    {
        if ('_edit_lock' == $meta_key) {
            $return_me = true;
        }
        return $return_me;
    }


}
