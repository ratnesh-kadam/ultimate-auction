<?php
//auction listing page - pagination
$page_num = get_option('wdm_auc_num_per_page');
$page_num = (!empty($page_num) && $page_num > 0) ? $page_num : 20;

function auction_pagination($pages = '', $range = 2, $paged) {
    $showitems = ($range * 2) + 1;

    if (empty($paged))
        $paged = 1;

    if ($pages == '') {
        global $wp_query;
        $pages = $wp_query->max_num_pages;

        if (!$pages) {
            $pages = 1;
        }
    }

    if (1 != $pages) {
        echo "<div class='pagination'>";
        printf('<span>' . __('Page %1$s of %2$s', 'wdm-ultimate-auction') . '</span>', $paged, $pages);
        if ($paged > 2 && $paged > $range + 1 && $showitems < $pages)
            echo "<a href='" . get_pagenum_link(1) . "'>&laquo;</a>";
        if ($paged > 1 && $showitems < $pages)
            echo "<a href='" . get_pagenum_link($paged - 1) . "'>&lsaquo;</a>";

        for ($i = 1; $i <= $pages; $i++) {
            if (1 != $pages && (!($i >= $paged + $range + 1 || $i <= $paged - $range - 1) || $pages <= $showitems )) {
                echo ($paged == $i) ? "<span class='current'>" . $i . "</span>" : "<a href='" . get_pagenum_link($i) . "' class='inactive' >" . $i . "</a>";
            }
        }

        if ($paged < $pages && $showitems < $pages)
            echo "<a href='" . get_pagenum_link($paged + 1) . "'>&rsaquo;</a>";
        if ($paged < $pages - 1 && $paged + $range - 1 < $pages && $showitems < $pages)
            echo "<a href='" . get_pagenum_link($pages) . "'>&raquo;</a>";
        echo "</div>\n";
    }
}

$wdm_auction_array = array();

if (get_query_var('paged')) {
    $paged = get_query_var('paged');
} elseif (get_query_var('page')) {
    $paged = get_query_var('page');
} else {
    $paged = 1;
}

$args = array(
    'posts_per_page' => $page_num,
    'post_type' => 'ultimate-auction',
    'auction-status' => 'live',
    'post_status' => 'publish',
    'paged' => $paged,
    'suppress_filters' => false
);


$arg_data_c = array(
    'posts_per_page' => -1,
    'post_type' => 'ultimate-auction',
    'auction-status' => 'live',
    'post_status' => 'publish',
    'paged' => $paged,
    'suppress_filters' => false
);

do_action('wdm_ua_before_get_auctions');

$wdm_auction_array = get_posts($args);

$count_pages = count(get_posts($arg_data_c));

do_action('wdm_ua_after_get_auctions');

$show_content = '';
$show_content = apply_filters('wdm_ua_before_auctions_listing', $show_content);
echo $show_content;
?>

<div class="wdm-auction-listing-container">
    <ul class="wdm_auctions_list wdm_auction_ListingResults">
        <li class="auction-list-menus">
            <span class="wdm_auction_column_header___headers wdm_auction_column_header___app" data-reactid="417">
                <ul class="wdm_auction_column_header___items" data-reactid="418">
                    <li class="wdm_auction_column_header___type" data-reactid="419">Product</li>
                    <li class="wdm_auction_column_header___bids" data-reactid="420">Bids</li>
                    <li class="wdm_auction_column_header___timeRemaining" data-reactid="421">Time Left</li>
                    <li class="wdm_auction_column_header___buyItNow" data-reactid="422">Buy Now</li>
                    <li class="wdm_auction_column_header___price" data-reactid="423">Price</li>
                </ul>
            </span>
        </li>

        <?php
        //auction listing page container
        foreach ($wdm_auction_array as $wdm_single_auction) {
            global $wpdb;
            $query = "SELECT MAX(bid) FROM " . $wpdb->prefix . "wdm_bidders WHERE auction_id =" . $wdm_single_auction->ID;
            $curr_price = $wpdb->get_var($query);
            ?>
            <li class="wdm_auction_list_result">
                <div class="wdm_auction_highlight_row">
                    <span class="wdm_auction_list_wrapper">
                        <a class="wdm_auction_list_resultLink" href="<?php echo get_permalink() . $set_char . "ult_auc_id=" . $wdm_single_auction->ID; ?>" class="wdm-auction-list-link">

                            <div class="wdm_auction_thumb_wrapper">
                            <?php
                                $vid_arr = array('mpg', 'mpeg', 'avi', 'mov', 'wmv', 'wma', 'mp4', '3gp', 'ogm', 'mkv', 'flv');
                                $auc_thumb = get_post_meta($wdm_single_auction->ID, 'wdm_auction_thumb', true);
                                $imgMime = wdm_get_mime_type($auc_thumb);
                                $img_ext = explode(".", $auc_thumb);
                                $img_ext = end($img_ext);

                                if (strpos($img_ext, '?') !== false)
                                    $img_ext = strtolower(strstr($img_ext, '?', true));

                                if (strstr($imgMime, "video/") || in_array($img_ext, $vid_arr) || strstr($auc_thumb, "youtube.com") || strstr($auc_thumb, "vimeo.com")) {
                                    $auc_thumb = plugins_url('img/film.png', __FILE__);
                                }
                                if (empty($auc_thumb)) {
                                    $auc_thumb = plugins_url('img/no-pic.jpg', __FILE__);
                                }
                                ?>
                                <img src="<?php echo $auc_thumb; ?>" alt="<?php echo $wdm_single_auction->post_title; ?>" class="ListingTile___tile ListingTile___tileImageApp"/>
                            </div>
                            <span class="wdm_auction_list_info_column">
                                <span class="wdm_auction_list_resultRow">
                                    <span class="wdm_auction_listing_title"><?php echo $wdm_single_auction->post_title; ?></span>
                                    <span class="wdm_auction_listing_bidcount wdm_auction_listing_rowelement">
                                        <span>
                                            <?php
                                                $get_bids = "SELECT COUNT(bid) FROM " . $wpdb->prefix . "wdm_bidders WHERE auction_id =" . $wdm_single_auction->ID;
                                                $bids_placed = $wpdb->get_var($get_bids);
                                                if (!empty($bids_placed) || $bids_placed > 0){
                                                    if($bids_placed >1)
                                                        echo $bids_placed . __(' Bids','wdm-ultimate-auction');
                                                    else
                                                        echo $bids_placed . __(' Bid','wdm-ultimate-auction');
                                                }
                                    
                                            ?>
                                        </span>
                                    </span>
                                    <span class="wdm_auction_list_time_remaining wdm_auction_listing_rowelement">
                                        <span>
                                            <?php
                                    $now = time();
                                    $ending_date = strtotime(get_post_meta($wdm_single_auction->ID, 'wdm_listing_ends', true));
                                    $act_trm = wp_get_post_terms($wdm_single_auction->ID, 'auction-status', array("fields" => "names"));

                                    $seconds = $ending_date - $now;

                                    if (in_array('expired', $act_trm)) {
                                        echo "<span class='wdm-mark-red'>" . __('Expired', 'wdm-ultimate-auction') . "</span>";
                                    } elseif ($seconds > 0 && !in_array('expired', $act_trm)) {
                                        $days = floor($seconds / 86400);
                                        $seconds %= 86400;

                                        $hours = floor($seconds / 3600);
                                        $seconds %= 3600;

                                        $minutes = floor($seconds / 60);
                                        $seconds %= 60;

                                        if ($days > 1)
                                            echo $days . " " . __('days', 'wdm-ultimate-auction');
                                        elseif ($days == 1)
                                            echo $days . " " . __('day', 'wdm-ultimate-auction');
                                        elseif ($days < 1) {
                                            if ($hours > 1)
                                                echo "<span class='wdm-mark-red'>".$hours . " " . __('hours', 'wdm-ultimate-auction')."</span>";
                                            elseif ($hours == 1)
                                                echo "<span class='wdm-mark-red'>".$hour . " " . __('hour', 'wdm-ultimate-auction')."</span>";
                                            elseif ($hours < 1) {
                                                if ($minutes > 1)
                                                    echo "<span class='wdm-mark-red'>".$minutes . " " . __('minutes', 'wdm-ultimate-auction')."</span>";
                                                elseif ($minutes == 1)
                                                    echo "<span class='wdm-mark-red'>".$minutes . " " . __('minute', 'wdm-ultimate-auction')."</span>";
                                                elseif ($minutes < 1) {
                                                    if ($seconds > 1)
                                                        "<span class='wdm-mark-red'>".$seconds . " " . __('seconds', 'wdm-ultimate-auction')."</span>";
                                                    elseif ($seconds == 1)
                                                        echo "<span class='wdm-mark-red'>".$seconds . " " . __('second', 'wdm-ultimate-auction')."</span>";
                                                    else
                                                        echo "<span class='wdm-mark-red'>". __('Expired', 'wdm-ultimate-auction')."</span>";
                                                }
                                            }
                                        }
                                    }
                                    else {
                                        echo __('Expired', 'wdm-ultimate-auction');
                                    }
                                    ?>
                                        </span>
                                    </span>
                                    <span class="wdm_auction_list_buynowprice wdm_auction_listing_rowelement">
                                        <?php
                                        $cc = substr(get_option('wdm_currency'), -3);
                                        $ob = get_post_meta($wdm_single_auction->ID, 'wdm_opening_bid', true);
                                        $bnp = get_post_meta($wdm_single_auction->ID, 'wdm_buy_it_now', true);

                                            if(!empty($bnp))
                                                echo $currency_symbol . number_format($bnp, 2, '.', ',') ;
                                        ?>
                                    </span>
                                </span>
                                <span class="wdm_auction_list_resultRow">
                                    <div class="ListingResults___keyAttributes">
                                    <!-- react-text: 441 -->New<!-- /react-text --><!-- react-text: 442 --> <!-- /react-text --><!-- react-text: 443 -->Product<!-- /react-text -->
                                    </div>
                                </span>
                                <span class="wdm_auction_list_resultRow">
                                    <span class="ListingResults___performanceInfo">
                                        <?php echo $wdm_single_auction->post_excerpt; ?>
                                    </span>
                                </span>
                            </span>
                            <span class="wdm_auction_list_ctaColumn">
                                <span class="wdm_auction_list_price wdm_auction_list_currentprice">
                                    <?php
                                        $cc = substr(get_option('wdm_currency'), -3);
                                        $ob = get_post_meta($wdm_single_auction->ID, 'wdm_opening_bid', true);
                                        $bnp = get_post_meta($wdm_single_auction->ID, 'wdm_buy_it_now', true);

                                        
                                        if ((!empty($curr_price) || $curr_price > 0) && !empty($ob))
                                            echo $currency_symbol . number_format($curr_price, 2, '.', ',') ;
                                        elseif (!empty($ob))
                                            echo $currency_symbol . number_format($ob, 2, '.', ',') ;
                                        elseif (empty($ob) && !empty($bnp))
                                            printf(__('Buy at %s%s %s', 'wdm-ultimate-auction'), $currency_symbol, number_format($bnp, 2, '.', ','), $currency_code_display);
                                    ?>
                                </span>
                                <span class="wdm_auction_list_buttonsWrapper">
                                        <span class="wdm_auction_list_bidButtonsWrapper spacing___pad0 spacing___mgnLeftXxs">
                                            <span class="wdm_auction_list_bid_bidButtonsWrapper">
                                                <button class="Button___xs Button___button Button___block BidButton___ctaButton" type="button">
                                                    <?php _e('Bid Now', 'wdm-ultimate-auction'); ?>
                                                </button>
                                            </span>
                                        </span>
                                </span>

                            </span>
                        </a>
                    </span>
                </div>
            </li>
            <?php
        }

//global $wpdb;
//
//$live_posts = array();
//
//$comm_query = "SELECT object_id
//FROM ".$wpdb->prefix."term_relationships
//WHERE term_taxonomy_id = (SELECT term_id
//FROM ".$wpdb->prefix."terms
//WHERE slug = 'live')";
//
//$comm_query = apply_filters('wdm_ua_filtered_auctions', $comm_query);
//
//$live_posts = $wpdb->get_col($comm_query);
//
//if(!empty($live_posts)){
//     $live_posts = implode("," , $live_posts);
//
//     $count_query = "SELECT count(ID)
//     FROM ".$wpdb->prefix."posts
//     WHERE post_type = 'ultimate-auction'
//     AND ID IN($live_posts)
//     AND post_status = 'publish'";
//
//     $count_query = apply_filters('wdm_ua_filtered_counts', $count_query);
//
//     $count_pages = $wpdb->get_var($count_query);

        if (!empty($count_pages)) {
            echo '<input type="hidden" id="wdm_ua_auc_avail" value="' . $count_pages . '" />';

            $c = ceil($count_pages / $page_num);
            auction_pagination($c, 1, $paged);
        }
//}
        ?>
    </ul>
</div>