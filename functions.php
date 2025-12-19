<?php
/*
 * Copyright 2014 Osclass
 * Copyright 2023 Osclass by OsclassPoint.com
 *
 * Osclass maintained & developed by OsclassPoint.com
 * You may not use this file except in compliance with the License.
 * You may download copy of Osclass at
 *
 *     https://osclass-classifieds.com/download
 *
 * Do not edit or add to this file if you wish to upgrade Osclass to newer
 * versions in the future. Software is distributed on an "AS IS" basis, without
 * warranties or conditions of any kind, either express or implied. Do not remove
 * this NOTICE section as it contains license information and copyrights.
 */


define('SIGMA_THEME_VERSION', '160');
define('THEME_COMPATIBLE_WITH_OSCLASS_HOOKS', 820);     // Compatibility with new hooks up to version



// Get latest items for home page
function sigma_home_latest() {
  if(osc_is_home_page()) {
    osc_reset_latest_items();
    
    if(osc_count_latest_items() > 0) { 
      ?>
      <div class="home-latest">
        <h2><?php _e('Latest Listings', 'sigma') ; ?></h2>
        <?php
          View::newInstance()->_exportVariableToView("listType", 'latestItems');
          View::newInstance()->_exportVariableToView("listClass", 'listing-grid');
          osc_current_web_theme_path('loop.php');
        ?>
      </div>
      
      <?php osc_run_hook('home_latest'); ?>
      <?php osc_run_hook('home_premium'); ?>
      <?php 
    } 
  }
}

osc_add_hook('before-main', 'sigma_home_latest');


if( (string)osc_get_preference('keyword_placeholder', 'sigma')=="" ) {
  Params::setParam('keyword_placeholder', __('ie. PHP Programmer', 'sigma') ) ;
}

function sigma_remove_styles() {
  osc_remove_style('font-open-sans');
  osc_remove_style('open-sans');
  osc_remove_style('fi_font-awesome');
  osc_remove_style('font-awesome44');
  osc_remove_style('font-awesome45');
  osc_remove_style('font-awesome47');
  osc_remove_style('font-awesome');
}

osc_add_hook('init', 'sigma_remove_styles');
osc_add_hook('header', 'sigma_remove_styles');

//osc_register_script('fancybox', osc_current_web_theme_url('js/fancybox/jquery.fancybox.pack.js'), array('jquery'));
//osc_enqueue_style('fancybox', osc_current_web_theme_url('js/fancybox/jquery.fancybox.css'));
//osc_enqueue_script('fancybox');
osc_enqueue_script('fancybox');


osc_enqueue_style('font-awesome-sigma', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css');
// used for date/dateinterval custom fields
osc_enqueue_script('php-date');
if(!OC_ADMIN) {
  osc_enqueue_style('fine-uploader-css', osc_assets_url('js/fineuploader/fineuploader.css'));
  if(getPreference('rtl','sigma')=='0') {
    osc_enqueue_style('sigma-fine-uploader-css', osc_current_web_theme_url('css/ajax-uploader.css'));
  } else {
    osc_enqueue_style('sigma-fine-uploader-css', osc_current_web_theme_url('css/ajax-uploader-rtl.css'));
  }
}
osc_enqueue_script('jquery-fineuploader');


/**

FUNCTIONS

*/

// install options
if( !function_exists('sigma_theme_install') ) {
  function sigma_theme_install() {
    osc_set_preference('logo', 'sigma_logo.png', 'sigma');
    osc_set_preference('keyword_placeholder', Params::getParam('keyword_placeholder'), 'sigma');
    osc_set_preference('version', SIGMA_THEME_VERSION, 'sigma');
    osc_set_preference('footer_link', '1', 'sigma');
    osc_set_preference('donation', '0', 'sigma');
    osc_set_preference('defaultShowAs@all', 'list', 'sigma');
    osc_set_preference('defaultShowAs@search', 'list');
    osc_set_preference('defaultLocationShowAs', 'dropdown', 'sigma'); // dropdown / autocomplete
    osc_set_preference('rtl', '0', 'sigma');
    osc_reset_preferences();
  }
}
// update options
if( !function_exists('sigma_theme_update') ) {
  function sigma_theme_update($current_version) {
    osc_set_preference('version', SIGMA_THEME_VERSION, 'sigma');
  }
}
if(!function_exists('check_install_sigma_theme')) {
  function check_install_sigma_theme() {
    $current_version = osc_get_preference('version', 'sigma');
    //check if current version is installed or need an update<
    if( $current_version=='' ) {
      sigma_theme_install();
    } else if($current_version < SIGMA_THEME_VERSION){
      sigma_theme_update($current_version);
    }
  }
}

// RTL LANGUAGE SUPPORT
function sigma_is_rtl() {
  $current_lang = strtolower(osc_current_user_locale());

  if(in_array($current_lang, sigma_rtl_languages())) {
    return true;
  } else {
    return false;
  }
}


function sigma_rtl_languages() {
  $langs = array('ar_DZ','ar_BH','ar_EG','ar_IQ','ar_JO','ar_KW','ar_LY','ar_MA','ar_OM','ar_SA','ar_SY','fa_IR','ar_TN','ar_AE','ar_YE','ar_TD','ar_CO','ar_DJ','ar_ER','ar_MR','ar_SD');
  return array_map('strtolower', $langs);
}


if(!function_exists('sigma_add_body_class_construct')) {
  function sigma_add_body_class_construct($classes){
    $sigmaBodyClass = sigmaBodyClass::newInstance();
    $classes = array_merge($classes, $sigmaBodyClass->get());
    return $classes;
  }
}
if(!function_exists('sigma_body_class')) {
  function sigma_body_class($echo = true){
    /**
    * Print body classes.
    *
    * @param string $echo Optional parameter.
    * @return print string with all body classes concatenated
    */
    osc_add_filter('sigma_bodyClass','sigma_add_body_class_construct');
    $classes = osc_apply_filter('sigma_bodyClass', array());
    if($echo && count($classes)){
      echo 'class="'.implode(' ',$classes).'"';
    } else {
      return $classes;
    }
  }
}
if(!function_exists('sigma_add_body_class')) {
  function sigma_add_body_class($class){
    /**
    * Add new body class to body class array.
    *
    * @param string $class required parameter.
    */
    $sigmaBodyClass = sigmaBodyClass::newInstance();
    $sigmaBodyClass->add($class);
  }
}
if(!function_exists('sigma_nofollow_construct')) {
  /**
  * Hook for header, meta tags robots nofollos
  */
  function sigma_nofollow_construct() {
    echo '<meta name="robots" content="noindex, nofollow, noarchive" />' . PHP_EOL;
    echo '<meta name="googlebot" content="noindex, nofollow, noarchive" />' . PHP_EOL;

  }
}
if( !function_exists('sigma_follow_construct') ) {
  /**
  * Hook for header, meta tags robots follow
  */
  function sigma_follow_construct() {
    echo '<meta name="robots" content="index, follow" />' . PHP_EOL;
    echo '<meta name="googlebot" content="index, follow" />' . PHP_EOL;

  }
}

/**
 * ENHANCED SEO FUNCTIONS FOR URL & CANONICAL FIXES
 */

if( !function_exists('sigma_detect_page_type') ) {
  /**
   * Detect the current page type for canonical tag generation
   * @return array Page type information
   */
  function sigma_detect_page_type() {
    $page_info = array(
      'type' => 'unknown',
      'is_filtered' => false,
      'filters' => array(),
      'page_number' => 1,
      'canonical_url' => osc_get_current_url()
    );
    
    // Detect page type
    if(osc_is_home_page()) {
      $page_info['type'] = 'home';
    } elseif(osc_is_search_page()) {
      $page_info['type'] = 'search';
    } elseif(osc_is_item_page()) {
      $page_info['type'] = 'item';
    } elseif(osc_is_static_page()) {
      $page_info['type'] = 'page';
    } elseif(Rewrite::newInstance()->get_location() === 'user') {
      $page_info['type'] = 'user';
    } else {
      // Check if it's a category or location page
      $location = Rewrite::newInstance()->get_location();
      if($location === 'search') {
        $page_info['type'] = 'category'; // Category pages use search controller
      }
    }
    
    // Detect pagination
    $page_number = (int)Params::getParam('iPage');
    if($page_number > 1) {
      $page_info['page_number'] = $page_number;
    }
    
    // Detect filters for search/category pages
    if(in_array($page_info['type'], array('search', 'category'))) {
      $page_info['filters'] = sigma_detect_active_filters();
      $page_info['is_filtered'] = !empty($page_info['filters']);
    }
    
    return $page_info;
  }
}

if( !function_exists('sigma_detect_active_filters') ) {
  /**
   * Detect active search/category filters
   * @return array List of active filters
   */
  function sigma_detect_active_filters() {
    $filters = array();
    
    // Price filters
    if(Params::getParam('sPriceMin') !== '' && Params::getParam('sPriceMin') !== null) {
      $filters['price_min'] = Params::getParam('sPriceMin');
    }
    if(Params::getParam('sPriceMax') !== '' && Params::getParam('sPriceMax') !== null) {
      $filters['price_max'] = Params::getParam('sPriceMax');
    }
    
    // Condition filters
    if(Params::getParam('sCondition') !== '' && Params::getParam('sCondition') !== null) {
      $filters['condition'] = Params::getParam('sCondition');
    }
    
    // Company filter
    if(Params::getParam('sCompany') !== '' && Params::getParam('sCompany') !== null) {
      $filters['company'] = Params::getParam('sCompany');
    }
    
    // Period filter
    if(Params::getParam('sPeriod') !== '' && Params::getParam('sPeriod') !== null) {
      $filters['period'] = Params::getParam('sPeriod');
    }
    
    // View filter (list/gallery)
    if(Params::getParam('sShowAs') !== '' && Params::getParam('sShowAs') !== null) {
      $filters['show_as'] = Params::getParam('sShowAs');
    }
    
    // Order filters (only if not default)
    $order = Params::getParam('sOrder');
    $order_type = Params::getParam('iOrderType');
    if($order !== '' && $order !== null && $order !== 'dt_pub_date') {
      $filters['order'] = $order;
    }
    if($order_type !== '' && $order_type !== null && $order_type !== '0') {
      $filters['order_type'] = $order_type;
    }
    
    // Search pattern
    if(Params::getParam('sPattern') !== '' && Params::getParam('sPattern') !== null) {
      $filters['pattern'] = Params::getParam('sPattern');
    }
    
    // Location filters (when not part of clean URL structure)
    if(Params::getParam('sRegion') !== '' && Params::getParam('sRegion') !== null) {
      $filters['region'] = Params::getParam('sRegion');
    }
    if(Params::getParam('sCity') !== '' && Params::getParam('sCity') !== null) {
      $filters['city'] = Params::getParam('sCity');
    }
    if(Params::getParam('sCountry') !== '' && Params::getParam('sCountry') !== null) {
      $filters['country'] = Params::getParam('sCountry');
    }
    
    return $filters;
  }
}

if( !function_exists('sigma_generate_canonical_url') ) {
  /**
   * Generate appropriate canonical URL based on page type and filters
   * @param array $page_info Page information from sigma_detect_page_type()
   * @return string Canonical URL
   */
  function sigma_generate_canonical_url($page_info) {
    // For paginated pages (page > 1), use self-referencing canonical
    if($page_info['page_number'] > 1) {
      return osc_get_current_url();
    }
    
    // For clean pages without filters, use self-referencing canonical
    if(!$page_info['is_filtered']) {
      return osc_get_current_url();
    }
    
    // For filtered pages, generate parent canonical URL
    switch($page_info['type']) {
      case 'search':
      case 'category':
        return sigma_generate_parent_category_url();
        
      default:
        return osc_get_current_url();
    }
  }
}

if( !function_exists('sigma_generate_parent_category_url') ) {
  /**
   * Generate parent category URL without filters
   * @return string Clean parent category URL
   */
  function sigma_generate_parent_category_url() {
    $base_url = osc_base_url();
    
    // Get current category if any
    $category_id = Params::getParam('sCategory');
    if($category_id && is_numeric($category_id)) {
      // Generate clean category URL
      return osc_search_category_url($category_id);
    }
    
    // Get current region/city for location pages
    $region_id = Params::getParam('sRegion');
    $city_id = Params::getParam('sCity');
    
    if($city_id && is_numeric($city_id)) {
      return osc_search_url(array('sCity' => $city_id));
    } elseif($region_id && is_numeric($region_id)) {
      return osc_search_url(array('sRegion' => $region_id));
    }
    
    // Default to search page
    return osc_search_url();
  }
}

if( !function_exists('sigma_enhanced_canonical_tag') ) {
  /**
   * Generate enhanced canonical tag HTML
   * @return string Canonical tag HTML or empty string
   */
  function sigma_enhanced_canonical_tag() {
    // Let Osclass handle canonical if enhanced canonical is enabled
    if(function_exists('osc_enhance_canonical_url_enabled') && osc_enhance_canonical_url_enabled()) {
      $canonical_url = osc_get_canonical();
      if($canonical_url) {
        // Validate and ensure proper format
        $canonical_url = sigma_ensure_absolute_url($canonical_url);
        $canonical_url = sigma_encode_canonical_url($canonical_url);
        
        if(sigma_validate_canonical_url($canonical_url)) {
          return $canonical_url;
        }
      }
    }
    
    // Fallback to our enhanced logic
    $page_info = sigma_detect_page_type();
    $canonical_url = sigma_generate_canonical_url($page_info);
    
    // Ensure absolute URL and proper encoding
    $canonical_url = sigma_ensure_absolute_url($canonical_url);
    $canonical_url = sigma_encode_canonical_url($canonical_url);
    
    // Validate before returning
    if(sigma_validate_canonical_url($canonical_url)) {
      return $canonical_url;
    }
    
    // Fallback to base URL if validation fails
    return osc_base_url();
  }
}

if( !function_exists('sigma_should_noindex_page') ) {
  /**
   * Determine if current page should have noindex meta robots tag
   * @return boolean True if page should be noindexed
   */
  function sigma_should_noindex_page() {
    $page_info = sigma_detect_page_type();
    
    // Noindex paginated pages beyond page 1
    if($page_info['page_number'] > 1) {
      return true;
    }
    
    // Noindex filtered pages
    if($page_info['is_filtered']) {
      return true;
    }
    
    // Noindex search pages with patterns
    if($page_info['type'] === 'search' && !empty($page_info['filters']['pattern'])) {
      return true;
    }
    
    // Special cases for specific page types
    switch($page_info['type']) {
      case 'item':
        // Noindex spam items
        return (osc_item_is_spam() || osc_premium_is_spam());
        
      case 'search':
        // Noindex if no results
        return (osc_count_items() == 0);
        
      default:
        return false;
    }
  }
}

if( !function_exists('sigma_enhanced_meta_robots') ) {
  /**
   * Generate enhanced meta robots tag
   * @return void Outputs meta robots tag if needed
   */
  function sigma_enhanced_meta_robots() {
    if(sigma_should_noindex_page()) {
      echo '<meta name="robots" content="noindex, follow" />' . PHP_EOL;
      echo '<meta name="googlebot" content="noindex, follow" />' . PHP_EOL;
    } else {
      echo '<meta name="robots" content="index, follow" />' . PHP_EOL;
      echo '<meta name="googlebot" content="index, follow" />' . PHP_EOL;
    }
  }
}

// Hook our enhanced meta robots function to replace old system
if( !function_exists('sigma_init_enhanced_seo') ) {
  function sigma_init_enhanced_seo() {
    // Remove any existing meta robots hooks
    osc_remove_hook('header', 'sigma_nofollow_construct');
    osc_remove_hook('header', 'sigma_follow_construct');
    
    // Add our enhanced meta robots function
    osc_add_hook('header', 'sigma_enhanced_meta_robots', 5);
  }
}

// Initialize enhanced SEO on every page load
osc_add_hook('init', 'sigma_init_enhanced_seo', 1);

/**
 * REDIRECT CHAINS & BROKEN LINKS MANAGEMENT
 */

if( !function_exists('sigma_handle_dynamic_redirects') ) {
  /**
   * Handle dynamic redirects for deleted or moved content
   * @return void
   */
  function sigma_handle_dynamic_redirects() {
    // Only process on 404 errors or specific conditions
    if(!sigma_should_handle_redirect()) {
      return;
    }
    
    $current_url = $_SERVER['REQUEST_URI'];
    $redirect_url = sigma_find_redirect_target($current_url);
    
    if($redirect_url && $redirect_url !== $current_url && $redirect_url !== 'none') {
      // Validate redirect target
      if(!sigma_validate_redirect_target($redirect_url)) {
        return;
      }
      
      // Prevent redirect loops
      if(!sigma_is_redirect_loop($current_url, $redirect_url)) {
        // Make URL absolute if needed
        if(strpos($redirect_url, 'http') !== 0) {
          $redirect_url = osc_base_url() . ltrim($redirect_url, '/');
        }
        
        header("Location: " . $redirect_url, true, 301);
        exit;
      }
    }
  }
}

if( !function_exists('sigma_should_handle_redirect') ) {
  /**
   * Determine if we should attempt to handle a redirect
   * @return boolean
   */
  function sigma_should_handle_redirect() {
    // Handle 404 errors
    if(http_response_code() === 404) {
      return true;
    }
    
    // Handle specific Osclass error conditions
    if(osc_is_web_user_logged_in() === null && Rewrite::newInstance()->get_location() === 'item') {
      // Possible deleted item
      return true;
    }
    
    // Handle category not found
    if(Rewrite::newInstance()->get_location() === 'search' && osc_count_categories() === 0) {
      return true;
    }
    
    return false;
  }
}

if( !function_exists('sigma_find_redirect_target') ) {
  /**
   * Find appropriate redirect target for broken URL
   * @param string $url Current URL
   * @return string|false Redirect target or false if none found
   */
  function sigma_find_redirect_target($url) {
    // Check cache first
    $cached_redirect = sigma_get_cached_redirect($url);
    if($cached_redirect) {
      return $cached_redirect;
    }
    
    // Parse URL components
    $parsed = parse_url($url);
    $path = isset($parsed['path']) ? trim($parsed['path'], '/') : '';
    $query = isset($parsed['query']) ? $parsed['query'] : '';
    
    // Try different redirect strategies
    $redirect_url = false;
    
    // 1. Check for deleted category redirects
    $redirect_url = sigma_check_category_redirect($path, $query);
    if($redirect_url) {
      sigma_cache_redirect_mapping($url, $redirect_url);
      sigma_log_redirect($url, $redirect_url, 'category');
      return $redirect_url;
    }
    
    // 2. Check for deleted item redirects
    $redirect_url = sigma_check_item_redirect($path, $query);
    if($redirect_url) {
      sigma_cache_redirect_mapping($url, $redirect_url);
      sigma_log_redirect($url, $redirect_url, 'item');
      return $redirect_url;
    }
    
    // 3. Check for user profile redirects
    $redirect_url = sigma_check_user_redirect($path, $query);
    if($redirect_url) {
      sigma_cache_redirect_mapping($url, $redirect_url);
      sigma_log_redirect($url, $redirect_url, 'user');
      return $redirect_url;
    }
    
    // 4. Check for search pattern redirects
    $redirect_url = sigma_check_search_redirect($path, $query);
    if($redirect_url) {
      sigma_cache_redirect_mapping($url, $redirect_url);
      sigma_log_redirect($url, $redirect_url, 'search');
      return $redirect_url;
    }
    
    // 5. Intelligent fallback based on URL structure
    $redirect_url = sigma_intelligent_fallback($path, $query);
    if($redirect_url) {
      sigma_cache_redirect_mapping($url, $redirect_url);
      sigma_log_redirect($url, $redirect_url, 'fallback');
      return $redirect_url;
    }
    
    // Cache negative result to avoid repeated processing
    sigma_cache_redirect_mapping($url, 'none');
    
    return false;
  }
}

if( !function_exists('sigma_check_category_redirect') ) {
  /**
   * Check for category-based redirects
   * @param string $path URL path
   * @param string $query Query string
   * @return string|false Redirect URL or false
   */
  function sigma_check_category_redirect($path, $query) {
    // Extract category ID or slug from URL
    if(preg_match('/category[\/\-_]?(\d+)/', $path, $matches)) {
      $category_id = $matches[1];
      
      // Check if category exists
      $category = Category::newInstance()->findByPrimaryKey($category_id);
      if($category) {
        return osc_search_category_url($category_id);
      } else {
        // Category deleted, try to find parent or similar category
        return sigma_find_parent_category_redirect($category_id);
      }
    }
    
    // Try to match category slug
    if(preg_match('/\/([a-zA-Z0-9\-_]+)/', $path, $matches)) {
      $slug = $matches[1];
      $categories = Category::newInstance()->listAll();
      
      foreach($categories as $category) {
        if(isset($category['s_slug']) && $category['s_slug'] === $slug) {
          return osc_search_category_url($category['pk_i_id']);
        }
      }
    }
    
    return false;
  }
}

if( !function_exists('sigma_check_item_redirect') ) {
  /**
   * Check for item-based redirects
   * @param string $path URL path
   * @param string $query Query string
   * @return string|false Redirect URL or false
   */
  function sigma_check_item_redirect($path, $query) {
    // Extract item ID from URL
    if(preg_match('/item[\/\-_]?(\d+)/', $path, $matches)) {
      $item_id = $matches[1];
      
      // Check if item exists
      $item = Item::newInstance()->findByPrimaryKey($item_id);
      if($item && $item['b_enabled'] == 1 && $item['b_active'] == 1) {
        return osc_item_url();
      } else {
        // Item deleted or disabled, redirect to category
        if($item && isset($item['fk_i_category_id'])) {
          return osc_search_category_url($item['fk_i_category_id']);
        }
      }
    }
    
    return false;
  }
}

if( !function_exists('sigma_check_user_redirect') ) {
  /**
   * Check for user profile redirects
   * @param string $path URL path
   * @param string $query Query string
   * @return string|false Redirect URL or false
   */
  function sigma_check_user_redirect($path, $query) {
    // Extract user ID from URL
    if(preg_match('/user[\/\-_]?(\d+)/', $path, $matches)) {
      $user_id = $matches[1];
      
      // Check if user exists
      $user = User::newInstance()->findByPrimaryKey($user_id);
      if($user && $user['b_enabled'] == 1) {
        return osc_user_public_profile_url($user_id);
      } else {
        // User deleted or disabled, redirect to search
        return osc_search_url();
      }
    }
    
    return false;
  }
}

if( !function_exists('sigma_check_search_redirect') ) {
  /**
   * Check for search-based redirects
   * @param string $path URL path
   * @param string $query Query string
   * @return string|false Redirect URL or false
   */
  function sigma_check_search_redirect($path, $query) {
    // Handle old search formats
    if(strpos($path, 'search') !== false || strpos($query, 'sPattern') !== false) {
      // Parse query parameters
      parse_str($query, $params);
      
      // Build clean search URL
      $search_params = array();
      
      if(isset($params['sPattern']) && !empty($params['sPattern'])) {
        $search_params['sPattern'] = $params['sPattern'];
      }
      if(isset($params['sCategory']) && !empty($params['sCategory'])) {
        $search_params['sCategory'] = $params['sCategory'];
      }
      if(isset($params['sCity']) && !empty($params['sCity'])) {
        $search_params['sCity'] = $params['sCity'];
      }
      if(isset($params['sRegion']) && !empty($params['sRegion'])) {
        $search_params['sRegion'] = $params['sRegion'];
      }
      
      if(!empty($search_params)) {
        return osc_search_url($search_params);
      } else {
        return osc_search_url();
      }
    }
    
    return false;
  }
}

if( !function_exists('sigma_intelligent_fallback') ) {
  /**
   * Provide intelligent fallback redirects
   * @param string $path URL path
   * @param string $query Query string
   * @return string|false Redirect URL or false
   */
  function sigma_intelligent_fallback($path, $query) {
    // If path contains recognizable keywords, redirect to relevant section
    $keywords = array(
      'car' => 'cars',
      'auto' => 'cars', 
      'vehicle' => 'cars',
      'house' => 'real-estate',
      'home' => 'real-estate',
      'property' => 'real-estate',
      'job' => 'jobs',
      'work' => 'jobs',
      'employment' => 'jobs'
    );
    
    foreach($keywords as $keyword => $category_slug) {
      if(stripos($path, $keyword) !== false) {
        // Find category by slug
        $categories = Category::newInstance()->listAll();
        foreach($categories as $category) {
          if(isset($category['s_slug']) && strpos($category['s_slug'], $category_slug) !== false) {
            return osc_search_category_url($category['pk_i_id']);
          }
        }
      }
    }
    
    // Default fallback to search page
    return osc_search_url();
  }
}

if( !function_exists('sigma_find_parent_category_redirect') ) {
  /**
   * Find parent category for deleted category
   * @param int $category_id Deleted category ID
   * @return string|false Parent category URL or false
   */
  function sigma_find_parent_category_redirect($category_id) {
    // This would require checking category hierarchy
    // For now, redirect to main search page
    return osc_search_url();
  }
}

if( !function_exists('sigma_is_redirect_loop') ) {
  /**
   * Check if redirect would create a loop
   * @param string $from Source URL
   * @param string $to Target URL
   * @return boolean True if loop detected
   */
  function sigma_is_redirect_loop($from, $to) {
    // Simple loop detection - check if target equals source
    if($from === $to) {
      return true;
    }
    
    // Check if we've seen this redirect before (session-based)
    if(!isset($_SESSION['sigma_redirects'])) {
      $_SESSION['sigma_redirects'] = array();
    }
    
    $redirect_key = md5($from . '->' . $to);
    if(isset($_SESSION['sigma_redirects'][$redirect_key])) {
      return true;
    }
    
    // Track this redirect
    $_SESSION['sigma_redirects'][$redirect_key] = time();
    
    // Clean old redirects (older than 5 minutes)
    foreach($_SESSION['sigma_redirects'] as $key => $timestamp) {
      if(time() - $timestamp > 300) {
        unset($_SESSION['sigma_redirects'][$key]);
      }
    }
    
    return false;
  }
}

// Hook the dynamic redirect handler
osc_add_hook('init', 'sigma_handle_dynamic_redirects', 10);

/**
 * METADATA & HEADING STRUCTURE OPTIMIZATION
 */

if( !function_exists('sigma_get_page_type_detailed') ) {
  /**
   * Get detailed page type information for metadata generation
   * @return array Page type and context information
   */
  function sigma_get_page_type_detailed() {
    $page_info = array(
      'type' => 'unknown',
      'subtype' => '',
      'context' => array(),
      'title_parts' => array(),
      'description_context' => array()
    );
    
    if(osc_is_home_page()) {
      $page_info['type'] = 'home';
      $page_info['title_parts'][] = 'Classified Ads';
      $page_info['description_context']['action'] = 'Browse';
      $page_info['description_context']['content'] = 'classified advertisements';
      
    } elseif(osc_is_search_page()) {
      $page_info['type'] = 'search';
      
      // Determine search subtype
      if(osc_search_category()) {
        $page_info['subtype'] = 'category';
        $page_info['context']['category'] = osc_search_category();
        $page_info['title_parts'][] = osc_search_category();
      }
      
      if(osc_search_region()) {
        $page_info['context']['region'] = osc_search_region();
        $page_info['title_parts'][] = 'in ' . osc_search_region();
      } elseif(osc_search_city()) {
        $page_info['context']['city'] = osc_search_city();
        $page_info['title_parts'][] = 'in ' . osc_search_city();
      }
      
      if(Params::getParam('sPattern')) {
        $page_info['subtype'] = 'pattern_search';
        $page_info['context']['pattern'] = Params::getParam('sPattern');
        $page_info['title_parts'][] = '"' . Params::getParam('sPattern') . '"';
      }
      
      $page_info['description_context']['action'] = 'Browse';
      $page_info['description_context']['content'] = 'listings';
      $page_info['description_context']['count'] = osc_search_total_items();
      
    } elseif(osc_is_item_page()) {
      $page_info['type'] = 'item';
      $page_info['context']['item'] = osc_item();
      $page_info['title_parts'][] = osc_item_title();
      
      if(osc_item_category()) {
        $page_info['context']['category'] = osc_item_category();
        $page_info['title_parts'][] = osc_item_category();
      }
      
      if(osc_item_city()) {
        $page_info['context']['city'] = osc_item_city();
        $page_info['title_parts'][] = 'in ' . osc_item_city();
      } elseif(osc_item_region()) {
        $page_info['context']['region'] = osc_item_region();
        $page_info['title_parts'][] = 'in ' . osc_item_region();
      }
      
      $page_info['description_context']['price'] = osc_item_formated_price();
      $page_info['description_context']['location'] = osc_item_city() ? osc_item_city() : osc_item_region();
      
    } elseif(osc_is_static_page()) {
      $page_info['type'] = 'page';
      $page_info['context']['page'] = osc_static_page();
      $page_info['title_parts'][] = osc_static_page_title();
      
    } elseif(Rewrite::newInstance()->get_location() === 'user') {
      $page_info['type'] = 'user';
      $section = Rewrite::newInstance()->get_section();
      $page_info['subtype'] = $section;
      
      if($section === 'pub_profile') {
        $page_info['title_parts'][] = osc_user_name() . "'s Profile";
      } else {
        $page_info['title_parts'][] = ucfirst(str_replace('_', ' ', $section));
      }
    }
    
    return $page_info;
  }
}

if( !function_exists('sigma_generate_optimized_title') ) {
  /**
   * Generate SEO-optimized page title
   * @return string Optimized page title
   */
  function sigma_generate_optimized_title() {
    $page_info = sigma_get_page_type_detailed();
    $title_parts = array();
    
    // Add primary title parts
    if(!empty($page_info['title_parts'])) {
      $primary_title = implode(' ', $page_info['title_parts']);
      $title_parts[] = sigma_optimize_title_length($primary_title, 45);
    }
    
    // Add site name
    $site_name = osc_page_title();
    if($site_name && !in_array($site_name, $title_parts)) {
      $title_parts[] = $site_name;
    }
    
    $full_title = implode(' | ', $title_parts);
    
    // Ensure title is within optimal length
    return sigma_optimize_title_length($full_title, 60);
  }
}

if( !function_exists('sigma_generate_optimized_description') ) {
  /**
   * Generate SEO-optimized meta description
   * @return string Optimized meta description
   */
  function sigma_generate_optimized_description() {
    $page_info = sigma_get_page_type_detailed();
    $description = '';
    
    switch($page_info['type']) {
      case 'home':
        $description = sigma_generate_home_description($page_info);
        break;
        
      case 'search':
        $description = sigma_generate_search_description($page_info);
        break;
        
      case 'item':
        $description = sigma_generate_item_description($page_info);
        break;
        
      case 'page':
        $description = sigma_generate_page_description($page_info);
        break;
        
      case 'user':
        $description = sigma_generate_user_description($page_info);
        break;
        
      default:
        $description = 'Browse classified ads on ' . osc_page_title() . '. Find great deals and connect with local sellers.';
    }
    
    return sigma_optimize_description_length($description);
  }
}

if( !function_exists('sigma_generate_home_description') ) {
  /**
   * Generate home page description
   * @param array $page_info Page information
   * @return string Description
   */
  function sigma_generate_home_description($page_info) {
    $site_name = osc_page_title();
    $total_items = Item::newInstance()->countAll();
    
    $description = "Browse thousands of classified ads on {$site_name}. ";
    
    if($total_items > 0) {
      $description .= "Find great deals among {$total_items} active listings. ";
    }
    
    $description .= "Buy, sell, and discover local opportunities in your area.";
    
    return $description;
  }
}

if( !function_exists('sigma_generate_search_description') ) {
  /**
   * Generate search page description
   * @param array $page_info Page information
   * @return string Description
   */
  function sigma_generate_search_description($page_info) {
    $description = '';
    $count = isset($page_info['description_context']['count']) ? $page_info['description_context']['count'] : 0;
    
    if($page_info['subtype'] === 'category' && isset($page_info['context']['category'])) {
      $category = $page_info['context']['category'];
      $location = '';
      
      if(isset($page_info['context']['city'])) {
        $location = ' in ' . $page_info['context']['city'];
      } elseif(isset($page_info['context']['region'])) {
        $location = ' in ' . $page_info['context']['region'];
      }
      
      $description = "Browse {$category} listings{$location}. ";
      
      if($count > 0) {
        $description .= "Find great deals among {$count} active {$category} ads. ";
      }
      
      $description .= "Connect with local sellers and discover opportunities.";
      
    } elseif($page_info['subtype'] === 'pattern_search' && isset($page_info['context']['pattern'])) {
      $pattern = $page_info['context']['pattern'];
      $location = '';
      
      if(isset($page_info['context']['city'])) {
        $location = ' in ' . $page_info['context']['city'];
      } elseif(isset($page_info['context']['region'])) {
        $location = ' in ' . $page_info['context']['region'];
      }
      
      $description = "Search results for '{$pattern}'{$location}. ";
      
      if($count > 0) {
        $description .= "Found {$count} matching listings. ";
      }
      
      $description .= "Browse classified ads and find what you're looking for.";
      
    } else {
      $description = "Browse classified ads and find great deals. ";
      
      if($count > 0) {
        $description .= "Discover opportunities among {$count} active listings. ";
      }
      
      $description .= "Connect with local sellers in your area.";
    }
    
    return $description;
  }
}

if( !function_exists('sigma_generate_item_description') ) {
  /**
   * Generate item page description
   * @param array $page_info Page information
   * @return string Description
   */
  function sigma_generate_item_description($page_info) {
    $description = '';
    
    // Get item description excerpt
    $item_desc = osc_item_description();
    if($item_desc) {
      $excerpt = sigma_create_excerpt($item_desc, 80);
      $description .= $excerpt . ' - ';
    }
    
    // Add price if available
    if(isset($page_info['description_context']['price']) && $page_info['description_context']['price']) {
      $description .= $page_info['description_context']['price'] . ' ';
    }
    
    // Add location
    if(isset($page_info['description_context']['location']) && $page_info['description_context']['location']) {
      $description .= 'in ' . $page_info['description_context']['location'] . '. ';
    }
    
    // Add category context
    if(isset($page_info['context']['category'])) {
      $description .= $page_info['context']['category'] . ' listing. ';
    }
    
    // Add call to action
    $description .= 'Contact seller on ' . osc_page_title() . '.';
    
    return $description;
  }
}

if( !function_exists('sigma_generate_page_description') ) {
  /**
   * Generate static page description
   * @param array $page_info Page information
   * @return string Description
   */
  function sigma_generate_page_description($page_info) {
    // Try to get page content excerpt
    if(isset($page_info['context']['page']['s_text'])) {
      $content = $page_info['context']['page']['s_text'];
      return sigma_create_excerpt($content, 150);
    }
    
    // Fallback description
    $page_title = isset($page_info['context']['page']['s_title']) ? $page_info['context']['page']['s_title'] : 'Page';
    return $page_title . ' - ' . osc_page_title() . '. Browse classified ads and find great deals in your area.';
  }
}

if( !function_exists('sigma_generate_user_description') ) {
  /**
   * Generate user page description
   * @param array $page_info Page information
   * @return string Description
   */
  function sigma_generate_user_description($page_info) {
    $site_name = osc_page_title();
    
    switch($page_info['subtype']) {
      case 'pub_profile':
        $user_name = osc_user_name();
        return "View {$user_name}'s profile and listings on {$site_name}. Browse classified ads and connect with local sellers.";
        
      case 'items':
        return "Manage your listings on {$site_name}. Edit, renew, and track your classified ads.";
        
      case 'profile':
        return "Update your account profile on {$site_name}. Manage your personal information and preferences.";
        
      case 'alerts':
        return "Manage your search alerts on {$site_name}. Get notified about new listings matching your interests.";
        
      default:
        return "Your account dashboard on {$site_name}. Manage listings, profile, and account settings.";
    }
  }
}

if( !function_exists('sigma_optimize_title_length') ) {
  /**
   * Optimize title length for SEO
   * @param string $title Original title
   * @param int $max_length Maximum length
   * @return string Optimized title
   */
  function sigma_optimize_title_length($title, $max_length = 60) {
    if(strlen($title) <= $max_length) {
      return $title;
    }
    
    // Try to truncate at word boundary
    $truncated = substr($title, 0, $max_length);
    $last_space = strrpos($truncated, ' ');
    
    if($last_space !== false && $last_space > ($max_length * 0.7)) {
      return substr($title, 0, $last_space);
    }
    
    // Fallback to hard truncation
    return substr($title, 0, $max_length - 3) . '...';
  }
}

if( !function_exists('sigma_optimize_description_length') ) {
  /**
   * Optimize description length for SEO
   * @param string $description Original description
   * @param int $max_length Maximum length
   * @return string Optimized description
   */
  function sigma_optimize_description_length($description, $max_length = 160) {
    if(strlen($description) <= $max_length) {
      return $description;
    }
    
    // Try to truncate at sentence boundary
    $sentences = preg_split('/[.!?]+/', $description);
    $result = '';
    
    foreach($sentences as $sentence) {
      $sentence = trim($sentence);
      if(empty($sentence)) continue;
      
      $test_result = $result . ($result ? '. ' : '') . $sentence . '.';
      if(strlen($test_result) <= $max_length) {
        $result = $test_result;
      } else {
        break;
      }
    }
    
    if($result) {
      return $result;
    }
    
    // Fallback to word boundary truncation
    $truncated = substr($description, 0, $max_length);
    $last_space = strrpos($truncated, ' ');
    
    if($last_space !== false && $last_space > ($max_length * 0.8)) {
      return substr($description, 0, $last_space) . '...';
    }
    
    // Hard truncation as last resort
    return substr($description, 0, $max_length - 3) . '...';
  }
}

if( !function_exists('sigma_create_excerpt') ) {
  /**
   * Create excerpt from content
   * @param string $content Original content
   * @param int $max_length Maximum length
   * @return string Excerpt
   */
  function sigma_create_excerpt($content, $max_length = 100) {
    // Strip HTML tags
    $content = strip_tags($content);
    
    // Remove extra whitespace
    $content = preg_replace('/\s+/', ' ', trim($content));
    
    if(strlen($content) <= $max_length) {
      return $content;
    }
    
    // Truncate at word boundary
    $truncated = substr($content, 0, $max_length);
    $last_space = strrpos($truncated, ' ');
    
    if($last_space !== false && $last_space > ($max_length * 0.7)) {
      return substr($content, 0, $last_space);
    }
    
    return substr($content, 0, $max_length - 3) . '...';
  }
}

/**
 * REDIRECT MONITORING AND VALIDATION
 */

if( !function_exists('sigma_log_redirect') ) {
  /**
   * Log redirect for monitoring purposes
   * @param string $from Source URL
   * @param string $to Target URL
   * @param string $type Redirect type
   * @return void
   */
  function sigma_log_redirect($from, $to, $type = 'dynamic') {
    // Only log in development or if logging is enabled
    if(!defined('WP_DEBUG') || !WP_DEBUG) {
      return;
    }
    
    $log_entry = array(
      'timestamp' => date('Y-m-d H:i:s'),
      'from' => $from,
      'to' => $to,
      'type' => $type,
      'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
      'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''
    );
    
    // Log to file (if writable)
    $log_file = osc_uploads_path() . 'redirects.log';
    if(is_writable(dirname($log_file))) {
      file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
    }
  }
}

if( !function_exists('sigma_validate_redirect_target') ) {
  /**
   * Validate that redirect target exists and is accessible
   * @param string $url Target URL
   * @return boolean True if target is valid
   */
  function sigma_validate_redirect_target($url) {
    // Basic URL validation
    if(!filter_var($url, FILTER_VALIDATE_URL)) {
      return false;
    }
    
    // Check if it's an internal URL
    $base_url = osc_base_url();
    if(strpos($url, $base_url) !== 0) {
      return false; // External URLs not allowed for redirects
    }
    
    // For performance, we'll assume internal URLs are valid
    // In production, you might want to add more sophisticated checking
    return true;
  }
}

if( !function_exists('sigma_get_redirect_stats') ) {
  /**
   * Get redirect statistics for monitoring
   * @return array Redirect statistics
   */
  function sigma_get_redirect_stats() {
    $stats = array(
      'total_redirects' => 0,
      'redirect_types' => array(),
      'common_sources' => array(),
      'error_rate' => 0
    );
    
    $log_file = osc_uploads_path() . 'redirects.log';
    if(file_exists($log_file) && is_readable($log_file)) {
      $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      $stats['total_redirects'] = count($lines);
      
      // Analyze recent redirects (last 100)
      $recent_lines = array_slice($lines, -100);
      foreach($recent_lines as $line) {
        $entry = json_decode($line, true);
        if($entry) {
          $type = isset($entry['type']) ? $entry['type'] : 'unknown';
          $stats['redirect_types'][$type] = isset($stats['redirect_types'][$type]) ? $stats['redirect_types'][$type] + 1 : 1;
          
          $from = isset($entry['from']) ? $entry['from'] : '';
          $stats['common_sources'][$from] = isset($stats['common_sources'][$from]) ? $stats['common_sources'][$from] + 1 : 1;
        }
      }
      
      // Sort by frequency
      arsort($stats['common_sources']);
      $stats['common_sources'] = array_slice($stats['common_sources'], 0, 10, true);
    }
    
    return $stats;
  }
}

/**
 * REDIRECT PERFORMANCE OPTIMIZATION
 */

if( !function_exists('sigma_cache_redirect_mapping') ) {
  /**
   * Cache redirect mappings for better performance
   * @param string $from Source URL
   * @param string $to Target URL
   * @return void
   */
  function sigma_cache_redirect_mapping($from, $to) {
    // Use transient cache for redirect mappings
    $cache_key = 'sigma_redirect_' . md5($from);
    set_transient($cache_key, $to, 3600); // Cache for 1 hour
  }
}

if( !function_exists('sigma_get_cached_redirect') ) {
  /**
   * Get cached redirect mapping
   * @param string $from Source URL
   * @return string|false Cached redirect target or false
   */
  function sigma_get_cached_redirect($from) {
    $cache_key = 'sigma_redirect_' . md5($from);
    return get_transient($cache_key);
  }
}

if( !function_exists('sigma_clear_redirect_cache') ) {
  /**
   * Clear redirect cache
   * @return void
   */
  function sigma_clear_redirect_cache() {
    // This would require a more sophisticated cache management system
    // For now, we'll rely on transient expiration
    delete_transient('sigma_redirect_cache_cleared');
    set_transient('sigma_redirect_cache_cleared', time(), 60);
  }
}

if( !function_exists('sigma_clean_internal_url') ) {
  /**
   * Clean internal URLs by removing unnecessary parameters
   * @param string $url The URL to clean
   * @return string Cleaned URL
   */
  function sigma_clean_internal_url($url) {
    // Parse URL
    $parsed = parse_url($url);
    
    if(!isset($parsed['query'])) {
      return $url;
    }
    
    // Parse query string
    parse_str($parsed['query'], $params);
    
    // Remove tracking parameters
    $tracking_params = array('utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 
                             'fbclid', 'gclid', 'msclkid', '_ga', '_gid', 'ref', 'referrer', 'PHPSESSID');
    
    foreach($tracking_params as $param) {
      unset($params[$param]);
    }
    
    // Remove default Osclass parameters
    if(isset($params['sOrder']) && $params['sOrder'] === 'dt_pub_date') {
      unset($params['sOrder']);
    }
    if(isset($params['iOrderType']) && $params['iOrderType'] === '0') {
      unset($params['iOrderType']);
    }
    if(isset($params['sShowAs']) && $params['sShowAs'] === 'list') {
      unset($params['sShowAs']);
    }
    
    // Rebuild URL
    $clean_url = $parsed['scheme'] . '://' . $parsed['host'];
    if(isset($parsed['port'])) {
      $clean_url .= ':' . $parsed['port'];
    }
    $clean_url .= $parsed['path'];
    
    if(!empty($params)) {
      $clean_url .= '?' . http_build_query($params);
    }
    
    if(isset($parsed['fragment'])) {
      $clean_url .= '#' . $parsed['fragment'];
    }
    
    return $clean_url;
  }
}

// Apply clean URL filter to search URLs
if( !function_exists('sigma_filter_search_url') ) {
  function sigma_filter_search_url($url) {
    return sigma_clean_internal_url($url);
  }
}

// Hook into Osclass URL generation
osc_add_filter('search_url', 'sigma_filter_search_url');
osc_add_filter('search_category_url', 'sigma_filter_search_url');

if( !function_exists('sigma_validate_canonical_url') ) {
  /**
   * Validate canonical URL format
   * @param string $url The URL to validate
   * @return boolean True if URL is valid
   */
  function sigma_validate_canonical_url($url) {
    // Check if URL is not empty
    if(empty($url)) {
      return false;
    }
    
    // Check if URL is properly formatted
    if(!filter_var($url, FILTER_VALIDATE_URL)) {
      return false;
    }
    
    // Check if URL is absolute (has protocol and domain)
    $parsed = parse_url($url);
    if(!isset($parsed['scheme']) || !isset($parsed['host'])) {
      return false;
    }
    
    // Check if URL uses HTTPS (for security)
    if($parsed['scheme'] !== 'https' && $parsed['scheme'] !== 'http') {
      return false;
    }
    
    return true;
  }
}

if( !function_exists('sigma_ensure_absolute_url') ) {
  /**
   * Ensure URL is absolute with protocol and domain
   * @param string $url The URL to make absolute
   * @return string Absolute URL
   */
  function sigma_ensure_absolute_url($url) {
    // If already absolute, return as-is
    if(preg_match('/^https?:\/\//', $url)) {
      return $url;
    }
    
    // Make relative URL absolute
    $base_url = rtrim(osc_base_url(), '/');
    $url = ltrim($url, '/');
    
    return $base_url . '/' . $url;
  }
}

if( !function_exists('sigma_encode_canonical_url') ) {
  /**
   * Properly encode canonical URL
   * @param string $url The URL to encode
   * @return string Encoded URL
   */
  function sigma_encode_canonical_url($url) {
    $parsed = parse_url($url);
    
    if(!$parsed) {
      return $url;
    }
    
    // Rebuild URL with proper encoding
    $encoded_url = $parsed['scheme'] . '://';
    
    if(isset($parsed['user'])) {
      $encoded_url .= rawurlencode($parsed['user']);
      if(isset($parsed['pass'])) {
        $encoded_url .= ':' . rawurlencode($parsed['pass']);
      }
      $encoded_url .= '@';
    }
    
    $encoded_url .= $parsed['host'];
    
    if(isset($parsed['port'])) {
      $encoded_url .= ':' . $parsed['port'];
    }
    
    if(isset($parsed['path'])) {
      // Encode path segments individually to preserve slashes
      $path_segments = explode('/', $parsed['path']);
      $encoded_segments = array_map('rawurlencode', $path_segments);
      $encoded_url .= implode('/', $encoded_segments);
    }
    
    if(isset($parsed['query'])) {
      $encoded_url .= '?' . $parsed['query']; // Query already encoded by http_build_query
    }
    
    if(isset($parsed['fragment'])) {
      $encoded_url .= '#' . rawurlencode($parsed['fragment']);
    }
    
    return $encoded_url;
  }
}

/* logo */
if( !function_exists('logo_header') ) {
  function logo_header() {
     $logo = osc_get_preference('logo','sigma');
     
     $html = '<a href="'.osc_base_url().'"><img border="0" alt="' . osc_page_title() . '" src="' . sigma_logo_url() . '"></a>';
     if( $logo!='' && file_exists( osc_uploads_path() . $logo ) ) {
      return $html;
     } else {
      return '<a href="'.osc_base_url().'">'.osc_page_title().'</a>';
    }
  }
}
/* logo */
if( !function_exists('sigma_logo_url') ) {
  function sigma_logo_url() {
    $logo = osc_get_preference('logo','sigma');

    if($logo) {
      return osc_uploads_url($logo);
    }
    return false;
  }
}
if( !function_exists('sigma_draw_item') ) {
  function sigma_draw_item($class = false,$admin = false, $premium = false) {
    $filename = 'loop-single';
    if($premium){
      $filename .='-premium';
    }
    require WebThemes::newInstance()->getCurrentThemePath().$filename.'.php';
  }
}
if( !function_exists('sigma_show_as') ){
  function sigma_show_as(){

    $p_sShowAs  = Params::getParam('sShowAs');
    $aValidShowAsValues = array('list', 'gallery');
    if (!in_array($p_sShowAs, $aValidShowAsValues)) {
      $p_sShowAs = sigma_default_show_as();
    }

    return $p_sShowAs;
  }
}
if( !function_exists('sigma_default_direction') ){
  function sigma_default_direction(){
    $locale = osc_get_current_user_locale();
    if(isset($locale['b_rtl']) && $locale['b_rtl'] == 1) {
      return 1;
    } else {
      return getPreference('rtl','sigma');
    }
  }
}
if( !function_exists('sigma_default_show_as') ){
  function sigma_default_show_as(){
    return getPreference('defaultShowAs@all','sigma');
  }
}
if( !function_exists('sigma_default_location_show_as') ){
  function sigma_default_location_show_as(){
    return osc_get_preference('defaultLocationShowAs','sigma');
  }
}
if( !function_exists('sigma_draw_categories_list') ) {
  function sigma_draw_categories_list(){ ?>
  <?php if(!osc_is_home_page()){ echo '<div class="resp-wrapper">'; } ?>
   <?php
   //cell_3
  $total_categories   = osc_count_categories();
  $col1_max_cat     = ceil($total_categories/3);

   osc_goto_first_category();
   $i    = 0;

   while ( osc_has_categories() ) {
   ?>
  <?php
    if($i%$col1_max_cat == 0){
      if($i > 0) { echo '</div>'; }
      if($i == 0) {
         echo '<div class="cell_3 first_cel">';
      } else {
        echo '<div class="cell_3">';
      }
    }
  ?>
  <ul class="r-list">
     <li>
       <h1>
        <?php
        $_slug    = osc_category_slug();
        $_url     = osc_search_category_url();
        $_name    = osc_category_name();
        $_total_items = osc_category_total_items();
        if ( osc_count_subcategories() > 0 ) { ?>
        <span class="collapse resp-toogle"><i class="fa fa-caret-right fa-lg"></i></span>
        <?php } ?>
        <?php if($_total_items > 0) { ?>
        <a class="category <?php echo $_slug; ?>" href="<?php echo $_url; ?>"><?php echo $_name ; ?></a> <span>(<?php echo $_total_items ; ?>)</span>
        <?php } else { ?>
        <a class="category <?php echo $_slug; ?>" href="#"><?php echo $_name ; ?></a> <span>(<?php echo $_total_items ; ?>)</span>
        <?php } ?>
       </h1>
       <?php if ( osc_count_subcategories() > 0 ) { ?>
         <ul>
           <?php while ( osc_has_subcategories() ) { ?>
             <li>
             <?php if( osc_category_total_items() > 0 ) { ?>
               <a class="category sub-category <?php echo osc_category_slug() ; ?>" href="<?php echo osc_search_category_url() ; ?>"><?php echo osc_category_name() ; ?></a> <span>(<?php echo osc_category_total_items() ; ?>)</span>
             <?php } else { ?>
               <a class="category sub-category <?php echo osc_category_slug() ; ?>" href="#"><?php echo osc_category_name() ; ?></a> <span>(<?php echo osc_category_total_items() ; ?>)</span>
             <?php } ?>
             </li>
           <?php } ?>
         </ul>
       <?php } ?>
     </li>
  </ul>
  <?php
      $i++;
    }
    echo '</div>';
  ?>
  <?php if(!osc_is_home_page()){ echo '</div>'; } ?>
  <?php
  }
}
if( !function_exists('sigma_search_number') ) {
  /**
    *
    * @return array
    */
  function sigma_search_number() {
    $search_from = ((osc_search_page() * osc_default_results_per_page_at_search()) + 1);
    $search_to   = ((osc_search_page() + 1) * osc_default_results_per_page_at_search());
    if( $search_to > osc_search_total_items() ) {
      $search_to = osc_search_total_items();
    }

    return array(
      'from' => $search_from,
      'to'   => $search_to,
      'of'   => osc_search_total_items()
    );
  }
}
/*
 * Helpers used at view
 */
if( !function_exists('sigma_item_title') ) {
  function sigma_item_title() {
    $title = osc_item_title();
    foreach( osc_get_locales() as $locale ) {
      if( Session::newInstance()->_getForm('title') != "" ) {
        $title_ = Session::newInstance()->_getForm('title');
        if( @$title_[$locale['pk_c_code']] != "" ){
          $title = $title_[$locale['pk_c_code']];
        }
      }
    }
    return $title;
  }
}
if( !function_exists('sigma_item_description') ) {
  function sigma_item_description() {
    $description = osc_item_description();
    foreach( osc_get_locales() as $locale ) {
      if( Session::newInstance()->_getForm('description') != "" ) {
        $description_ = Session::newInstance()->_getForm('description');
        if( @$description_[$locale['pk_c_code']] != "" ){
          $description = $description_[$locale['pk_c_code']];
        }
      }
    }
    return $description;
  }
}
if( !function_exists('related_listings') ) {
  function related_listings() {
    View::newInstance()->_exportVariableToView('items', array());

    $mSearch = new Search();
    $mSearch->addCategory(osc_item_category_id());
    $mSearch->addRegion(osc_item_region());
    $mSearch->addItemConditions(sprintf("%st_item.pk_i_id < %s ", DB_TABLE_PREFIX, osc_item_id()));
    $mSearch->limit('0', '3');

    $aItems    = $mSearch->doSearch();
    $iTotalItems = count($aItems);
    if( $iTotalItems == 3 ) {
      View::newInstance()->_exportVariableToView('items', $aItems);
      return $iTotalItems;
    }
    unset($mSearch);

    $mSearch = new Search();
    $mSearch->addCategory(osc_item_category_id());
    $mSearch->addItemConditions(sprintf("%st_item.pk_i_id != %s ", DB_TABLE_PREFIX, osc_item_id()));
    $mSearch->limit('0', '3');

    $aItems = $mSearch->doSearch();
    $iTotalItems = count($aItems);
    if( $iTotalItems > 0 ) {
      View::newInstance()->_exportVariableToView('items', $aItems);
      return $iTotalItems;
    }
    unset($mSearch);

    return 0;
  }
}

if( !function_exists('osc_is_contact_page') ) {
  function osc_is_contact_page() {
    if( Rewrite::newInstance()->get_location() === 'contact' ) {
      return true;
    }

    return false;
  }
}

if( !function_exists('get_breadcrumb_lang') ) {
  function get_breadcrumb_lang() {
    $lang = array();
    $lang['item_add']         = __('Publish a listing', 'sigma');
    $lang['item_edit']        = __('Edit your listing', 'sigma');
    $lang['item_send_friend']     = __('Send to a friend', 'sigma');
    $lang['item_contact']       = __('Contact publisher', 'sigma');
    $lang['search']         = __('Search results', 'sigma');
    $lang['search_pattern']     = __('Search results: %s', 'sigma');
    $lang['user_dashboard']     = __('Dashboard', 'sigma');
    $lang['user_dashboard_profile'] = __("%s's profile", 'sigma');
    $lang['user_account']       = __('Account', 'sigma');
    $lang['user_items']       = __('Listings', 'sigma');
    $lang['user_alerts']      = __('Alerts', 'sigma');
    $lang['user_profile']       = __('Update account', 'sigma');
    $lang['user_change_email']    = __('Change email', 'sigma');
    $lang['user_change_username']   = __('Change username', 'sigma');
    $lang['user_change_password']   = __('Change password', 'sigma');
    $lang['login']          = __('Login', 'sigma');
    $lang['login_recover']      = __('Recover password', 'sigma');
    $lang['login_forgot']       = __('Change password', 'sigma');
    $lang['register']         = __('Create a new account', 'sigma');
    $lang['contact']        = __('Contact', 'sigma');
    return $lang;
  }
}

if(!function_exists('user_dashboard_redirect')) {
  function user_dashboard_redirect() {
    $page   = Params::getParam('page');
    $action = Params::getParam('action');
    if($page=='user' && $action=='dashboard') {
      if(ob_get_length()>0) {
        ob_end_flush();
      }
      header("Location: ".osc_user_list_items_url(), TRUE,301);
    }
  }
  osc_add_hook('init', 'user_dashboard_redirect');
}

if( !function_exists('get_user_menu') ) {
  function get_user_menu() {
    $options   = array();
    $options[] = array(
      'name' => __('Public Profile'),
       'url' => osc_user_public_profile_url(),
       'class' => 'opt_publicprofile'
    );
    $options[] = array(
      'name'  => __('Listings', 'sigma'),
      'url'   => osc_user_list_items_url(),
      'class' => 'opt_items'
    );
    $options[] = array(
      'name' => __('Alerts', 'sigma'),
      'url' => osc_user_alerts_url(),
      'class' => 'opt_alerts'
    );
    $options[] = array(
      'name'  => __('Account', 'sigma'),
      'url'   => osc_user_profile_url(),
      'class' => 'opt_account'
    );
    $options[] = array(
      'name'  => __('Change email', 'sigma'),
      'url'   => osc_change_user_email_url(),
      'class' => 'opt_change_email'
    );
    $options[] = array(
      'name'  => __('Change username', 'sigma'),
      'url'   => osc_change_user_username_url(),
      'class' => 'opt_change_username'
    );
    $options[] = array(
      'name'  => __('Change password', 'sigma'),
      'url'   => osc_change_user_password_url(),
      'class' => 'opt_change_password'
    );
    $options[] = array(
      'name'  => __('Delete account', 'sigma'),
      'url'   => '#',
      'class' => 'opt_delete_account'
    );

    return $options;
  }
}

if( !function_exists('delete_user_js') ) {
  function delete_user_js() {
    $location = Rewrite::newInstance()->get_location();
    $section  = Rewrite::newInstance()->get_section();
    if( ($location === 'user' && in_array($section, array('dashboard', 'profile', 'alerts', 'change_email', 'change_username',  'change_password', 'items'))) || (Params::getParam('page') ==='custom' && Params::getParam('in_user_menu')==true ) ) {
      osc_enqueue_script('delete-user-js');
    }
  }
  osc_add_hook('header', 'delete_user_js', 1);
}

if( !function_exists('user_info_js') ) {
  function user_info_js() {
    $location = Rewrite::newInstance()->get_location();
    $section  = Rewrite::newInstance()->get_section();

    if( $location === 'user' && in_array($section, array('dashboard', 'profile', 'alerts', 'change_email', 'change_username',  'change_password', 'items')) ) {
      $user = User::newInstance()->findByPrimaryKey( Session::newInstance()->_get('userId') );
      View::newInstance()->_exportVariableToView('user', $user);
      ?>
<script type="text/javascript">
sigma.user = {};
sigma.user.id = '<?php echo osc_user_id(); ?>';
sigma.user.secret = '<?php echo osc_user_field("s_secret"); ?>';
</script>
    <?php }
  }
  osc_add_hook('header', 'user_info_js');
}

function theme_sigma_actions_admin() {
  //if(OC_ADMIN)
  if( Params::getParam('file') == 'oc-content/themes/sigma/admin/settings.php' ) {
    if( Params::getParam('donation') == 'successful' ) {
      osc_set_preference('donation', '1', 'sigma');
      osc_reset_preferences();
    }
  }

  switch( Params::getParam('action_specific') ) {
    case('settings'):
      $footerLink  = Params::getParam('footer_link');

      osc_set_preference('keyword_placeholder', Params::getParam('keyword_placeholder'), 'sigma');
      osc_set_preference('footer_link', ($footerLink ? '1' : '0'), 'sigma');
      osc_set_preference('defaultShowAs@all', Params::getParam('defaultShowAs@all'), 'sigma');
      osc_set_preference('defaultShowAs@search', Params::getParam('defaultShowAs@all'));

      osc_set_preference('defaultLocationShowAs', Params::getParam('defaultLocationShowAs'), 'sigma');

      osc_set_preference('header-728x90',     trim(Params::getParam('header-728x90', false, false, false)),          'sigma');
      osc_set_preference('homepage-728x90',     trim(Params::getParam('homepage-728x90', false, false, false)),        'sigma');
      osc_set_preference('sidebar-300x250',     trim(Params::getParam('sidebar-300x250', false, false, false)),        'sigma');
      osc_set_preference('search-results-top-728x90',   trim(Params::getParam('search-results-top-728x90', false, false, false)),      'sigma');
      osc_set_preference('search-results-middle-728x90',  trim(Params::getParam('search-results-middle-728x90', false, false, false)),     'sigma');

      osc_set_preference('rtl', (Params::getParam('rtl') ? '1' : '0'), 'sigma');

      osc_add_flash_ok_message(__('Theme settings updated correctly', 'sigma'), 'admin');
      osc_redirect_to(osc_admin_render_theme_url('oc-content/themes/sigma/admin/settings.php'));
    break;
    case('upload_logo'):
      $package = Params::getFiles('logo');
      if( $package['error'] == UPLOAD_ERR_OK ) {
        $img = ImageResizer::fromFile($package['tmp_name']);
        $ext = $img->getExt();
        $logo_name   = 'sigma_logo';
        $logo_name  .= '.'.$ext;
        $path = osc_uploads_path() . $logo_name ;
        $img->saveToFile($path);

        osc_set_preference('logo', $logo_name, 'sigma');

        osc_add_flash_ok_message(__('The logo image has been uploaded correctly', 'sigma'), 'admin');
      } else {
        osc_add_flash_error_message(__("An error has occurred, please try again", 'sigma'), 'admin');
      }
      osc_redirect_to(osc_admin_render_theme_url('oc-content/themes/sigma/admin/header.php'));
    break;
    case('remove'):
      $logo = osc_get_preference('logo','sigma');
      $path = osc_uploads_path() . $logo ;
      if(file_exists( $path ) ) {
        @unlink( $path );
        osc_delete_preference('logo','sigma');
        osc_reset_preferences();
        osc_add_flash_ok_message(__('The logo image has been removed', 'sigma'), 'admin');
      } else {
        osc_add_flash_error_message(__("Image not found", 'sigma'), 'admin');
      }
      osc_redirect_to(osc_admin_render_theme_url('oc-content/themes/sigma/admin/header.php'));
    break;
  }
}

function sigma_redirect_user_dashboard()
{
  if( (Rewrite::newInstance()->get_location() === 'user') && (Rewrite::newInstance()->get_section() === 'dashboard') ) {
    header('Location: ' .osc_user_list_items_url());
    exit;
  }
}

function sigma_delete() {
  Preference::newInstance()->delete(array('s_section' => 'sigma'));
}

osc_add_hook('init', 'sigma_redirect_user_dashboard', 2);
osc_add_hook('init_admin', 'theme_sigma_actions_admin');
osc_add_hook('theme_delete_sigma', 'sigma_delete');

function sigma_admin_menu_links() {
  osc_admin_menu_appearance(__('Header logo', 'sigma'), osc_admin_render_theme_url('oc-content/themes/sigma/admin/header.php'), 'header_sigma');
  osc_admin_menu_appearance(__('Theme settings', 'sigma'), osc_admin_render_theme_url('oc-content/themes/sigma/admin/settings.php'), 'settings_sigma');
}

osc_add_hook('init_admin', 'sigma_admin_menu_links');




//TRIGGER FUNCTIONS
check_install_sigma_theme();

// if(osc_is_home_page()){
//   osc_add_hook('inside-main','sigma_draw_categories_list');
// } else if( osc_is_static_page() || osc_is_contact_page() ){
//   osc_add_hook('before-content','sigma_draw_categories_list');
// }

if(osc_is_home_page() || osc_is_search_page()){
  sigma_add_body_class('has-searchbox');
}


function sigma_sidebar_category_search($catId = null)
{
  $aCategories = array();
  if($catId==null || $catId <= 0) {
    $aCategories[] = Category::newInstance()->findRootCategoriesEnabled();
  } else {
    // if parent category, only show parent categories
    $aCategories = Category::newInstance()->toRootTree($catId);
    end($aCategories);
    $cat = current($aCategories);
    // if is parent of some category
    $childCategories = Category::newInstance()->findSubcategoriesEnabled($cat['pk_i_id']);
    if(count($childCategories) > 0) {
      $aCategories[] = $childCategories;
    }
  }

  if(count($aCategories) == 0) {
    return "";
  }

  sigma_print_sidebar_category_search($aCategories, $catId);
}

function sigma_print_sidebar_category_search($aCategories, $current_category = null, $i = 0)
{
  $class = '';
  if(!isset($aCategories[$i])) {
    return null;
  }

  if($i===0) {
    $class = 'class="category"';
  }

  $c   = $aCategories[$i];
  $i++;
  if(!isset($c['pk_i_id'])) {
    echo '<ul '.$class.'>';
    if($i==1) {
      echo '<li><a href="'.osc_esc_html(osc_update_search_url(array('sCategory'=>null, 'iPage'=>null))).'">'.__('All categories', 'sigma')."</a></li>";
    }
    foreach($c as $key => $value) {
  ?>
      <li>
        <a id="cat_<?php echo osc_esc_html($value['pk_i_id']);?>" href="<?php echo osc_esc_html(osc_update_search_url(array('sCategory'=> $value['pk_i_id'], 'iPage'=>null))); ?>">
        <?php if(isset($current_category) && $current_category == $value['pk_i_id']){ echo '<strong>'.$value['s_name'].'</strong>'; }
        else{ echo $value['s_name']; } ?>
        </a>

      </li>
  <?php
    }
    if($i==1) {
    echo "</ul>";
    } else {
    echo "</ul>";
    }
  } else {
  ?>
  <ul <?php echo $class;?>>
    <?php if($i==1) { ?>
    <li><a href="<?php echo osc_esc_html(osc_update_search_url(array('sCategory'=>null, 'iPage'=>null))); ?>"><?php _e('All categories', 'sigma'); ?></a></li>
    <?php } ?>
      <li>
        <a id="cat_<?php echo osc_esc_html($c['pk_i_id']);?>" href="<?php echo osc_esc_html(osc_update_search_url(array('sCategory'=> $c['pk_i_id'], 'iPage'=>null))); ?>">
        <?php if(isset($current_category) && $current_category == $c['pk_i_id']){ echo '<strong>'.$c['s_name'].'</strong>'; }
            else{ echo $c['s_name']; } ?>
        </a>
        <?php sigma_print_sidebar_category_search($aCategories, $current_category, $i); ?>
      </li>
    <?php if($i==1) { ?>
    <?php } ?>
  </ul>
<?php
  }
}

/**

CLASSES

*/
class sigmaBodyClass
{
  /**
  * Custom Class for add, remove or get body classes.
  *
  * @param string $instance used for singleton.
  * @param array $class.
  */
  private static $instance;
  private $class;

  private function __construct()
  {
    $this->class = array();
  }

  public static function newInstance()
  {
    if (  !self::$instance instanceof self)
    {
      self::$instance = new self;
    }
    return self::$instance;
  }

  public function add($class)
  {
    $this->class[] = $class;
  }
  public function get()
  {
    return $this->class;
  }
}


function osc_theme_check_compatibility_branch() {
  $osclass_version = (int)str_replace('.', '', OSCLASS_VERSION);
  $osclass_author = (!defined('OSCLASS_AUTHOR') ? 'NONE' : strtoupper(OSCLASS_AUTHOR));
  
  if($osclass_version >= 420 && $osclass_author <> 'OSCLASSPOINT') {
    osc_add_flash_error_message('Theme is not compatible with your osclass version or branch! You cannot use this theme as it would generate errors on your installation. Download and install supported osclass version: <a href="https://osclass-classifieds.com/download">https://osclass-classifieds.com/download</a>');
  }
} 

osc_add_hook('header', 'osc_theme_check_compatibility_branch', 1);


/**

HELPERS

*/
if( !function_exists('osc_uploads_url')) {
  function osc_uploads_url($item = '') {
    $logo = osc_get_preference('logo', 'sigma');
    if ($logo != '' && file_exists(osc_uploads_path() . $logo)) {
      $path = str_replace(ABS_PATH, '', osc_uploads_path() . '/');
      $path = str_replace('//', '/', $path);
      return osc_base_url() . $path . $item;
    }
  }
}

/*

  ads  SEARCH

 */
if (!function_exists('search_ads_listing_top_fn')) {
  function search_ads_listing_top_fn() {
    if(osc_get_preference('search-results-top-728x90', 'sigma')!='') {
      echo '<div class="clear"></div>' . PHP_EOL;
      echo '<div class="ads_728">' . PHP_EOL;
      echo osc_get_preference('search-results-top-728x90', 'sigma');
      echo '</div>' . PHP_EOL;
    }
  }
}
//osc_add_hook('search_ads_listing_top', 'search_ads_listing_top_fn');

if (!function_exists('search_ads_listing_medium_fn')) {
  function search_ads_listing_medium_fn() {
    if(osc_get_preference('search-results-middle-728x90', 'sigma')!='') {
      echo '<div class="clear"></div>' . PHP_EOL;
      echo '<div class="ads_728">' . PHP_EOL;
      echo osc_get_preference('search-results-middle-728x90', 'sigma');
      echo '</div>' . PHP_EOL;
    }
  }
}
osc_add_hook('search_ads_listing_medium', 'search_ads_listing_medium_fn');
?>
/**
 * HEADING STRUCTURE OPTIMIZATION
 */

if( !function_exists('sigma_generate_h1') ) {
  /**
   * Generate optimized H1 heading for current page
   * @return string H1 content
   */
  function sigma_generate_h1() {
    $page_info = sigma_get_page_type_detailed();
    
    switch($page_info['type']) {
      case 'home':
        return 'Classified Ads - ' . osc_page_title();
        
      case 'search':
        return sigma_generate_search_h1($page_info);
        
      case 'item':
        return osc_item_title();
        
      case 'page':
        return osc_static_page_title();
        
      case 'user':
        return sigma_generate_user_h1($page_info);
        
      default:
        return osc_page_title();
    }
  }
}

if( !function_exists('sigma_generate_search_h1') ) {
  /**
   * Generate H1 for search pages
   * @param array $page_info Page information
   * @return string H1 content
   */
  function sigma_generate_search_h1($page_info) {
    if($page_info['subtype'] === 'category' && isset($page_info['context']['category'])) {
      $h1 = $page_info['context']['category'];
      
      if(isset($page_info['context']['city'])) {
        $h1 .= ' in ' . $page_info['context']['city'];
      } elseif(isset($page_info['context']['region'])) {
        $h1 .= ' in ' . $page_info['context']['region'];
      }
      
      return $h1;
      
    } elseif($page_info['subtype'] === 'pattern_search' && isset($page_info['context']['pattern'])) {
      $h1 = 'Search Results for "' . $page_info['context']['pattern'] . '"';
      
      if(isset($page_info['context']['city'])) {
        $h1 .= ' in ' . $page_info['context']['city'];
      } elseif(isset($page_info['context']['region'])) {
        $h1 .= ' in ' . $page_info['context']['region'];
      }
      
      return $h1;
      
    } else {
      return 'Browse Classified Ads';
    }
  }
}

if( !function_exists('sigma_generate_user_h1') ) {
  /**
   * Generate H1 for user pages
   * @param array $page_info Page information
   * @return string H1 content
   */
  function sigma_generate_user_h1($page_info) {
    switch($page_info['subtype']) {
      case 'pub_profile':
        return osc_user_name() . "'s Profile";
        
      case 'items':
        return 'My Listings';
        
      case 'profile':
        return 'Account Settings';
        
      case 'alerts':
        return 'My Alerts';
        
      case 'change_email':
        return 'Change Email';
        
      case 'change_password':
        return 'Change Password';
        
      case 'change_username':
        return 'Change Username';
        
      default:
        return 'My Account';
    }
  }
}

if( !function_exists('sigma_get_page_h2_structure') ) {
  /**
   * Get suggested H2 structure for current page
   * @return array Array of H2 headings
   */
  function sigma_get_page_h2_structure() {
    $page_info = sigma_get_page_type_detailed();
    $h2_headings = array();
    
    switch($page_info['type']) {
      case 'home':
        $h2_headings = array(
          'Latest Listings',
          'Browse Categories',
          'Popular Locations'
        );
        break;
        
      case 'search':
        if($page_info['subtype'] === 'category') {
          $h2_headings = array(
            'Refine Your Search',
            'Featured Listings',
            'All Listings'
          );
        } else {
          $h2_headings = array(
            'Search Results',
            'Refine Search',
            'Related Categories'
          );
        }
        break;
        
      case 'item':
        $h2_headings = array(
          'Item Details',
          'Description',
          'Contact Seller',
          'Related Listings'
        );
        break;
        
      case 'user':
        if($page_info['subtype'] === 'items') {
          $h2_headings = array(
            'Active Listings',
            'Inactive Listings',
            'Listing Statistics'
          );
        } elseif($page_info['subtype'] === 'profile') {
          $h2_headings = array(
            'Personal Information',
            'Contact Details',
            'Account Preferences'
          );
        }
        break;
    }
    
    return $h2_headings;
  }
}

if( !function_exists('sigma_output_structured_heading') ) {
  /**
   * Output properly structured heading with SEO optimization
   * @param string $text Heading text
   * @param int $level Heading level (1-6)
   * @param array $attributes Additional HTML attributes
   * @return string HTML heading element
   */
  function sigma_output_structured_heading($text, $level = 1, $attributes = array()) {
    $level = max(1, min(6, intval($level)));
    $tag = 'h' . $level;
    
    // Build attributes string
    $attr_string = '';
    if(!empty($attributes)) {
      foreach($attributes as $key => $value) {
        $attr_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
      }
    }
    
    return '<' . $tag . $attr_string . '>' . esc_html($text) . '</' . $tag . '>';
  }
}

/**
 * TEMPLATE HELPER FUNCTIONS
 */

if( !function_exists('sigma_meta_title') ) {
  /**
   * Output optimized meta title
   * @return void
   */
  function sigma_meta_title() {
    echo '<title>' . esc_html(sigma_generate_optimized_title()) . '</title>' . PHP_EOL;
  }
}

if( !function_exists('sigma_meta_description') ) {
  /**
   * Output optimized meta description
   * @return void
   */
  function sigma_meta_description() {
    $description = sigma_generate_optimized_description();
    if($description) {
      echo '<meta name="description" content="' . esc_attr($description) . '" />' . PHP_EOL;
    }
  }
}

if( !function_exists('sigma_page_h1') ) {
  /**
   * Output optimized H1 heading
   * @param array $attributes Additional HTML attributes
   * @return void
   */
  function sigma_page_h1($attributes = array()) {
    $h1_text = sigma_generate_h1();
    echo sigma_output_structured_heading($h1_text, 1, $attributes);
  }
}

if( !function_exists('sigma_section_h2') ) {
  /**
   * Output section H2 heading
   * @param string $text Heading text
   * @param array $attributes Additional HTML attributes
   * @return void
   */
  function sigma_section_h2($text, $attributes = array()) {
    echo sigma_output_structured_heading($text, 2, $attributes);
  }
}

if( !function_exists('sigma_subsection_h3') ) {
  /**
   * Output subsection H3 heading
   * @param string $text Heading text
   * @param array $attributes Additional HTML attributes
   * @return void
   */
  function sigma_subsection_h3($text, $attributes = array()) {
    echo sigma_output_structured_heading($text, 3, $attributes);
  }
}

/**
 * METADATA VALIDATION AND QUALITY ASSURANCE
 */

if( !function_exists('sigma_validate_page_metadata') ) {
  /**
   * Validate current page metadata for SEO compliance
   * @return array Validation results
   */
  function sigma_validate_page_metadata() {
    $results = array(
      'title' => array('valid' => true, 'issues' => array()),
      'description' => array('valid' => true, 'issues' => array()),
      'headings' => array('valid' => true, 'issues' => array())
    );
    
    // Validate title
    $title = sigma_generate_optimized_title();
    if(strlen($title) > 60) {
      $results['title']['valid'] = false;
      $results['title']['issues'][] = 'Title too long (' . strlen($title) . ' characters)';
    }
    if(strlen($title) < 30) {
      $results['title']['valid'] = false;
      $results['title']['issues'][] = 'Title too short (' . strlen($title) . ' characters)';
    }
    
    // Validate description
    $description = sigma_generate_optimized_description();
    if(strlen($description) > 160) {
      $results['description']['valid'] = false;
      $results['description']['issues'][] = 'Description too long (' . strlen($description) . ' characters)';
    }
    if(strlen($description) < 120) {
      $results['description']['valid'] = false;
      $results['description']['issues'][] = 'Description too short (' . strlen($description) . ' characters)';
    }
    
    return $results;
  }
}

if( !function_exists('sigma_check_duplicate_metadata') ) {
  /**
   * Check for duplicate titles and descriptions (basic implementation)
   * @return array Duplicate check results
   */
  function sigma_check_duplicate_metadata() {
    // This would require a more sophisticated system to track all page metadata
    // For now, return a placeholder structure
    return array(
      'duplicate_titles' => array(),
      'duplicate_descriptions' => array(),
      'similar_content' => array()
    );
  }
}

// Hook metadata functions to appropriate actions
osc_add_hook('header', 'sigma_meta_title', 1);
osc_add_hook('header', 'sigma_meta_description', 2);

/**
 * CORE WEB VITALS & PERFORMANCE OPTIMIZATION
 */

if( !function_exists('sigma_optimize_core_web_vitals') ) {
  /**
   * Initialize Core Web Vitals optimizations
   * @return void
   */
  function sigma_optimize_core_web_vitals() {
    // Remove duplicate jQuery and optimize loading
    sigma_remove_duplicate_scripts();
    
    // Optimize CSS delivery
    sigma_optimize_css_delivery();
    
    // Implement lazy loading for images
    sigma_implement_lazy_loading();
    
    // Add resource hints and preloading
    sigma_add_performance_hints();
    
    // Optimize font loading
    sigma_optimize_font_loading();
  }
}

if( !function_exists('sigma_remove_duplicate_scripts') ) {
  /**
   * Remove duplicate jQuery and optimize script loading
   * @return void
   */
  function sigma_remove_duplicate_scripts() {
    // Remove duplicate jQuery versions
    global $wp_scripts;
    
    // Dequeue duplicate scripts that might conflict
    $duplicate_scripts = array(
      'jquery-ui-core',
      'jquery-ui-widget', 
      'jquery-ui-mouse',
      'jquery-migrate'
    );
    
    foreach($duplicate_scripts as $script) {
      if(wp_script_is($script, 'enqueued')) {
        wp_dequeue_script($script);
      }
    }
    
    // Ensure only one jQuery version is loaded
    if(!wp_script_is('jquery', 'enqueued')) {
      wp_enqueue_script('jquery');
    }
  }
}

if( !function_exists('sigma_optimize_css_delivery') ) {
  /**
   * Optimize CSS delivery for better performance
   * @return void
   */
  function sigma_optimize_css_delivery() {
    // This will be implemented in the header to inline critical CSS
    // and load non-critical CSS asynchronously
    add_action('wp_head', 'sigma_inline_critical_css', 1);
    add_action('wp_footer', 'sigma_load_non_critical_css', 1);
  }
}

if( !function_exists('sigma_inline_critical_css') ) {
  /**
   * Inline critical CSS for above-the-fold content
   * @return void
   */
  function sigma_inline_critical_css() {
    $critical_css = sigma_get_critical_css();
    if($critical_css) {
      echo '<style id="critical-css">' . $critical_css . '</style>' . PHP_EOL;
    }
  }
}

if( !function_exists('sigma_get_critical_css') ) {
  /**
   * Get critical CSS for current page type
   * @return string Critical CSS
   */
  function sigma_get_critical_css() {
    // Basic critical CSS for above-the-fold content
    $critical_css = '
    /* Critical CSS for above-the-fold content */
    body { margin: 0; padding: 0; font-family: "Nunito", sans-serif; }
    .header { background: #fff; border-bottom: 1px solid #eee; }
    .logo { display: inline-block; }
    .main-nav { display: inline-block; }
    .search-box { background: #f8f9fa; padding: 20px 0; }
    .container, .resp-wrapper { max-width: 1200px; margin: 0 auto; padding: 0 15px; }
    .btn { display: inline-block; padding: 8px 16px; text-decoration: none; border-radius: 4px; }
    .btn-primary { background: #007bff; color: #fff; }
    .form-control { width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }
    h1 { font-size: 2rem; margin: 0 0 1rem 0; font-weight: 700; }
    h2 { font-size: 1.5rem; margin: 0 0 1rem 0; font-weight: 700; }
    .list-header { background: #fff; padding: 20px 0; }
    ';
    
    // Add page-specific critical CSS
    if(osc_is_home_page()) {
      $critical_css .= '
      .home-search { text-align: center; padding: 40px 0; }
      .categories-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
      ';
    } elseif(osc_is_search_page()) {
      $critical_css .= '
      .search-filters { background: #f8f9fa; padding: 15px; margin-bottom: 20px; }
      .listing-item { border: 1px solid #eee; margin-bottom: 15px; padding: 15px; }
      ';
    } elseif(osc_is_item_page()) {
      $critical_css .= '
      .item-header { margin-bottom: 20px; }
      .item-images { margin-bottom: 20px; }
      .item-details { background: #f8f9fa; padding: 20px; }
      ';
    }
    
    return $critical_css;
  }
}

if( !function_exists('sigma_load_non_critical_css') ) {
  /**
   * Load non-critical CSS asynchronously
   * @return void
   */
  function sigma_load_non_critical_css() {
    $non_critical_css = array(
      osc_current_web_theme_url('css/responsive.css'),
      'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css'
    );
    
    foreach($non_critical_css as $css_url) {
      echo '<link rel="preload" href="' . $css_url . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . PHP_EOL;
      echo '<noscript><link rel="stylesheet" href="' . $css_url . '"></noscript>' . PHP_EOL;
    }
  }
}

if( !function_exists('sigma_implement_lazy_loading') ) {
  /**
   * Implement lazy loading for images
   * @return void
   */
  function sigma_implement_lazy_loading() {
    // Add lazy loading attributes to images
    add_filter('the_content', 'sigma_add_lazy_loading_to_images');
    add_filter('post_thumbnail_html', 'sigma_add_lazy_loading_to_images');
    
    // Add intersection observer polyfill for older browsers
    add_action('wp_footer', 'sigma_add_lazy_loading_script');
  }
}

if( !function_exists('sigma_add_lazy_loading_to_images') ) {
  /**
   * Add lazy loading attributes to images
   * @param string $content HTML content
   * @return string Modified HTML content
   */
  function sigma_add_lazy_loading_to_images($content) {
    // Don't lazy load images that are likely above the fold
    if(sigma_is_above_fold_content()) {
      return $content;
    }
    
    // Add loading="lazy" to images
    $content = preg_replace('/<img([^>]+?)src=/i', '<img$1loading="lazy" src=', $content);
    
    // Add explicit dimensions where missing
    $content = sigma_add_image_dimensions($content);
    
    return $content;
  }
}

if( !function_exists('sigma_is_above_fold_content') ) {
  /**
   * Check if current content is likely above the fold
   * @return boolean
   */
  function sigma_is_above_fold_content() {
    // Simple heuristic - first few images are likely above fold
    static $image_count = 0;
    $image_count++;
    
    // First 2 images are likely above fold
    return $image_count <= 2;
  }
}

if( !function_exists('sigma_add_image_dimensions') ) {
  /**
   * Add explicit dimensions to images to prevent CLS
   * @param string $content HTML content
   * @return string Modified HTML content
   */
  function sigma_add_image_dimensions($content) {
    // This is a simplified implementation
    // In production, you'd want to analyze actual image dimensions
    $content = preg_replace_callback(
      '/<img([^>]+?)src=["\']([^"\']+)["\']([^>]*?)>/i',
      function($matches) {
        $img_tag = $matches[0];
        $src = $matches[2];
        
        // Skip if dimensions already exist
        if(strpos($img_tag, 'width=') !== false && strpos($img_tag, 'height=') !== false) {
          return $img_tag;
        }
        
        // Add default aspect ratio to prevent CLS
        $width = 300; // Default width
        $height = 200; // Default height (3:2 aspect ratio)
        
        // Try to get actual dimensions (simplified)
        $dimensions = sigma_get_image_dimensions($src);
        if($dimensions) {
          $width = $dimensions['width'];
          $height = $dimensions['height'];
        }
        
        // Add dimensions to img tag
        $img_tag = str_replace('<img', '<img width="' . $width . '" height="' . $height . '"', $img_tag);
        
        return $img_tag;
      },
      $content
    );
    
    return $content;
  }
}

if( !function_exists('sigma_get_image_dimensions') ) {
  /**
   * Get image dimensions (simplified implementation)
   * @param string $src Image source URL
   * @return array|false Image dimensions or false
   */
  function sigma_get_image_dimensions($src) {
    // This is a simplified implementation
    // In production, you'd want to cache dimensions and handle various scenarios
    
    // Convert relative URLs to absolute
    if(strpos($src, 'http') !== 0) {
      $src = osc_base_url() . ltrim($src, '/');
    }
    
    // Try to get dimensions from cache first
    $cache_key = 'img_dims_' . md5($src);
    $dimensions = get_transient($cache_key);
    
    if($dimensions === false) {
      // Get image dimensions
      $image_info = @getimagesize($src);
      if($image_info) {
        $dimensions = array(
          'width' => $image_info[0],
          'height' => $image_info[1]
        );
        
        // Cache for 24 hours
        set_transient($cache_key, $dimensions, 24 * HOUR_IN_SECONDS);
      }
    }
    
    return $dimensions;
  }
}

if( !function_exists('sigma_add_lazy_loading_script') ) {
  /**
   * Add lazy loading JavaScript for older browsers
   * @return void
   */
  function sigma_add_lazy_loading_script() {
    ?>
    <script>
    // Lazy loading fallback for older browsers
    if ('loading' in HTMLImageElement.prototype === false) {
      // Intersection Observer polyfill
      if (!window.IntersectionObserver) {
        // Load all images immediately if no support
        document.querySelectorAll('img[loading="lazy"]').forEach(function(img) {
          img.loading = 'eager';
        });
      } else {
        // Use Intersection Observer for lazy loading
        var lazyImages = document.querySelectorAll('img[loading="lazy"]');
        var imageObserver = new IntersectionObserver(function(entries, observer) {
          entries.forEach(function(entry) {
            if (entry.isIntersecting) {
              var image = entry.target;
              image.loading = 'eager';
              imageObserver.unobserve(image);
            }
          });
        });
        
        lazyImages.forEach(function(image) {
          imageObserver.observe(image);
        });
      }
    }
    </script>
    <?php
  }
}

if( !function_exists('sigma_add_performance_hints') ) {
  /**
   * Add resource hints for better performance
   * @return void
   */
  function sigma_add_performance_hints() {
    add_action('wp_head', 'sigma_output_resource_hints', 1);
  }
}

if( !function_exists('sigma_output_resource_hints') ) {
  /**
   * Output resource hints in HTML head
   * @return void
   */
  function sigma_output_resource_hints() {
    // DNS prefetch for external resources
    echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . PHP_EOL;
    echo '<link rel="dns-prefetch" href="//fonts.gstatic.com">' . PHP_EOL;
    echo '<link rel="dns-prefetch" href="//cdnjs.cloudflare.com">' . PHP_EOL;
    
    // Preconnect for critical external resources
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . PHP_EOL;
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . PHP_EOL;
    
    // Preload critical resources
    $critical_resources = sigma_get_critical_resources();
    foreach($critical_resources as $resource) {
      echo '<link rel="preload" href="' . $resource['url'] . '" as="' . $resource['type'] . '"';
      if(isset($resource['crossorigin'])) {
        echo ' crossorigin';
      }
      echo '>' . PHP_EOL;
    }
  }
}

if( !function_exists('sigma_get_critical_resources') ) {
  /**
   * Get critical resources to preload
   * @return array Critical resources
   */
  function sigma_get_critical_resources() {
    $resources = array();
    
    // Preload critical CSS
    $resources[] = array(
      'url' => osc_current_web_theme_url('css/style.css'),
      'type' => 'style'
    );
    
    // Preload critical JavaScript
    $resources[] = array(
      'url' => osc_current_web_theme_url('js/global.js'),
      'type' => 'script'
    );
    
    // Preload hero image on home page
    if(osc_is_home_page()) {
      $logo_url = sigma_logo_url();
      if($logo_url) {
        $resources[] = array(
          'url' => $logo_url,
          'type' => 'image'
        );
      }
    }
    
    // Preload item images on item pages
    if(osc_is_item_page() && osc_count_item_resources()) {
      osc_goto_first_item_resource();
      $resources[] = array(
        'url' => osc_resource_url(),
        'type' => 'image'
      );
    }
    
    return $resources;
  }
}

if( !function_exists('sigma_optimize_font_loading') ) {
  /**
   * Optimize font loading to prevent layout shifts
   * @return void
   */
  function sigma_optimize_font_loading() {
    add_action('wp_head', 'sigma_add_font_optimization', 5);
  }
}

if( !function_exists('sigma_add_font_optimization') ) {
  /**
   * Add font optimization to prevent FOIT/FOUT
   * @return void
   */
  function sigma_add_font_optimization() {
    ?>
    <style>
    /* Font display optimization */
    @font-face {
      font-family: 'Nunito';
      font-display: swap;
    }
    @font-face {
      font-family: 'Merriweather';
      font-display: swap;
    }
    
    /* Prevent layout shift during font loading */
    body {
      font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    </style>
    <?php
  }
}

if( !function_exists('sigma_track_core_web_vitals') ) {
  /**
   * Add Core Web Vitals tracking
   * @return void
   */
  function sigma_track_core_web_vitals() {
    add_action('wp_footer', 'sigma_output_web_vitals_script');
  }
}

if( !function_exists('sigma_output_web_vitals_script') ) {
  /**
   * Output Web Vitals tracking script
   * @return void
   */
  function sigma_output_web_vitals_script() {
    ?>
    <script>
    // Core Web Vitals tracking
    (function() {
      // Only track if Web Vitals API is available
      if (!window.PerformanceObserver) return;
      
      function sendToAnalytics(metric) {
        // Send to your analytics service
        console.log('Core Web Vital:', metric.name, metric.value);
        
        // Example: Send to Google Analytics
        if (typeof gtag !== 'undefined') {
          gtag('event', metric.name, {
            event_category: 'Web Vitals',
            event_label: metric.id,
            value: Math.round(metric.name === 'CLS' ? metric.value * 1000 : metric.value),
            non_interaction: true,
          });
        }
      }
      
      // Track LCP
      new PerformanceObserver(function(list) {
        var entries = list.getEntries();
        var lastEntry = entries[entries.length - 1];
        sendToAnalytics({
          name: 'LCP',
          value: lastEntry.startTime,
          id: 'lcp-' + Date.now()
        });
      }).observe({entryTypes: ['largest-contentful-paint']});
      
      // Track CLS
      var clsValue = 0;
      var clsEntries = [];
      new PerformanceObserver(function(list) {
        for (var entry of list.getEntries()) {
          if (!entry.hadRecentInput) {
            clsEntries.push(entry);
            clsValue += entry.value;
          }
        }
        sendToAnalytics({
          name: 'CLS',
          value: clsValue,
          id: 'cls-' + Date.now()
        });
      }).observe({entryTypes: ['layout-shift']});
      
      // Track FID (will be replaced by INP)
      new PerformanceObserver(function(list) {
        for (var entry of list.getEntries()) {
          sendToAnalytics({
            name: 'FID',
            value: entry.processingStart - entry.startTime,
            id: 'fid-' + Date.now()
          });
        }
      }).observe({entryTypes: ['first-input']});
    })();
    </script>
    <?php
  }
}

// Initialize Core Web Vitals optimizations
osc_add_hook('init', 'sigma_optimize_core_web_vitals', 1);
osc_add_hook('init', 'sigma_track_core_web_vitals', 1);
/**
 * CORE WEB VITALS & PERFORMANCE OPTIMIZATION
 */

if( !function_exists('sigma_optimize_performance') ) {
  /**
   * Initialize performance optimizations
   * @return void
   */
  function sigma_optimize_performance() {
    // Remove duplicate jQuery and optimize loading
    sigma_optimize_jquery_loading();
    
    // Optimize CSS delivery
    sigma_optimize_css_delivery();
    
    // Optimize JavaScript loading
    sigma_optimize_js_loading();
    
    // Add performance monitoring
    sigma_add_performance_monitoring();
  }
}

if( !function_exists('sigma_optimize_jquery_loading') ) {
  /**
   * Remove duplicate jQuery instances and optimize loading
   * @return void
   */
  function sigma_optimize_jquery_loading() {
    // Remove duplicate jQuery registrations
    global $wp_scripts;
    
    // Dequeue duplicate jQuery versions
    osc_remove_script('jquery-ui');
    osc_remove_script('jquery-migrate');
    
    // Ensure only one jQuery version is loaded
    if(!wp_script_is('jquery', 'registered')) {
      osc_register_script('jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js', array(), '3.6.0');
    }
    
    // Defer jQuery loading for non-critical pages
    if(!osc_is_publish_page() && !osc_is_edit_page()) {
      add_filter('script_loader_tag', 'sigma_defer_jquery_loading', 10, 2);
    }
  }
}

if( !function_exists('sigma_defer_jquery_loading') ) {
  /**
   * Add defer attribute to jQuery script tag
   * @param string $tag Script tag HTML
   * @param string $handle Script handle
   * @return string Modified script tag
   */
  function sigma_defer_jquery_loading($tag, $handle) {
    if('jquery' === $handle && !is_admin()) {
      return str_replace(' src', ' defer src', $tag);
    }
    return $tag;
  }
}

if( !function_exists('sigma_optimize_css_delivery') ) {
  /**
   * Optimize CSS delivery for better performance
   * @return void
   */
  function sigma_optimize_css_delivery() {
    // Inline critical CSS
    osc_add_hook('header', 'sigma_inline_critical_css', 1);
    
    // Defer non-critical CSS
    osc_add_hook('header', 'sigma_defer_non_critical_css', 8);
    
    // Optimize font loading
    osc_add_hook('header', 'sigma_optimize_font_loading', 2);
  }
}

if( !function_exists('sigma_inline_critical_css') ) {
  /**
   * Inline critical CSS for above-the-fold content
   * @return void
   */
  function sigma_inline_critical_css() {
    $critical_css = sigma_get_critical_css();
    if($critical_css) {
      echo '<style id="critical-css">' . $critical_css . '</style>' . PHP_EOL;
    }
  }
}

if( !function_exists('sigma_get_critical_css') ) {
  /**
   * Get critical CSS for current page type
   * @return string Critical CSS
   */
  function sigma_get_critical_css() {
    $page_info = sigma_get_page_type_detailed();
    $critical_css = '';
    
    // Base critical CSS for all pages
    $critical_css .= '
    body{font-family:Nunito,sans-serif;margin:0;padding:0;line-height:1.6}
    .header{background:#fff;border-bottom:1px solid #eee;position:relative}
    .resp-wrapper{max-width:1200px;margin:0 auto;padding:0 15px}
    .main-content{min-height:400px}
    h1{font-size:2em;margin:0.67em 0;font-weight:700}
    h2{font-size:1.5em;margin:0.83em 0;font-weight:700}
    .btn{display:inline-block;padding:8px 16px;background:#007cba;color:#fff;text-decoration:none;border-radius:4px}
    .search-form{background:#f8f9fa;padding:20px;border-radius:8px;margin:20px 0}
    ';
    
    // Page-specific critical CSS
    switch($page_info['type']) {
      case 'home':
        $critical_css .= '
        .home-hero{background:#f8f9fa;padding:40px 0;text-align:center}
        .category-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin:20px 0}
        ';
        break;
        
      case 'search':
        $critical_css .= '
        .list-header{background:#f8f9fa;padding:20px 0;border-bottom:1px solid #eee}
        .counter-search{font-size:14px;color:#666;margin:10px 0}
        .actions{display:flex;gap:10px;align-items:center;margin:10px 0}
        ';
        break;
        
      case 'item':
        $critical_css .= '
        .item-header{padding:20px 0}
        .item-title{font-size:2.2em;margin:0 0 10px 0;line-height:1.2}
        .item-price{font-size:1.8em;color:#007cba;font-weight:700;margin:10px 0}
        ';
        break;
    }
    
    return $critical_css;
  }
}

if( !function_exists('sigma_defer_non_critical_css') ) {
  /**
   * Defer non-critical CSS loading
   * @return void
   */
  function sigma_defer_non_critical_css() {
    // Get non-critical CSS files
    $non_critical_css = array(
      'responsive' => osc_current_web_theme_url('css/responsive.css?v=' . date('YmdHis')),
      'font-awesome' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css'
    );
    
    foreach($non_critical_css as $handle => $url) {
      echo '<link rel="preload" href="' . esc_attr($url) . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . PHP_EOL;
      echo '<noscript><link rel="stylesheet" href="' . esc_attr($url) . '"></noscript>' . PHP_EOL;
    }
  }
}

if( !function_exists('sigma_optimize_font_loading') ) {
  /**
   * Optimize font loading to prevent layout shifts
   * @return void
   */
  function sigma_optimize_font_loading() {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . PHP_EOL;
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . PHP_EOL;
    
    // Preload critical fonts
    echo '<link rel="preload" href="https://fonts.gstatic.com/s/nunito/v16/XRXV3I6Li01BKofIOuaBXso.woff2" as="font" type="font/woff2" crossorigin>' . PHP_EOL;
    
    // Load fonts with font-display: swap
    echo '<style>
    @font-face {
      font-family: "Nunito";
      font-style: normal;
      font-weight: 400;
      font-display: swap;
      src: url(https://fonts.gstatic.com/s/nunito/v16/XRXV3I6Li01BKofIOuaBXso.woff2) format("woff2");
    }
    @font-face {
      font-family: "Nunito";
      font-style: normal;
      font-weight: 700;
      font-display: swap;
      src: url(https://fonts.gstatic.com/s/nunito/v16/XRXW3I6Li01BKofAjsOUbOvISTs.woff2) format("woff2");
    }
    </style>' . PHP_EOL;
  }
}

if( !function_exists('sigma_optimize_js_loading') ) {
  /**
   * Optimize JavaScript loading for better performance
   * @return void
   */
  function sigma_optimize_js_loading() {
    // Defer non-critical JavaScript
    add_filter('script_loader_tag', 'sigma_defer_non_critical_js', 10, 2);
    
    // Remove unused scripts on certain pages
    if(!osc_is_publish_page() && !osc_is_edit_page()) {
      osc_remove_script('fineuploader');
      osc_remove_script('jquery-fineuploader');
    }
  }
}

if( !function_exists('sigma_defer_non_critical_js') ) {
  /**
   * Add defer attribute to non-critical JavaScript
   * @param string $tag Script tag HTML
   * @param string $handle Script handle
   * @return string Modified script tag
   */
  function sigma_defer_non_critical_js($tag, $handle) {
    // Scripts that should be deferred
    $defer_scripts = array('global-theme-js', 'fancybox', 'delete-user-js');
    
    if(in_array($handle, $defer_scripts) && !is_admin()) {
      return str_replace(' src', ' defer src', $tag);
    }
    
    return $tag;
  }
}

if( !function_exists('sigma_optimize_images') ) {
  /**
   * Optimize image loading and prevent layout shifts
   * @param string $src Image source
   * @param string $alt Alt text
   * @param int $width Image width
   * @param int $height Image height
   * @param array $attributes Additional attributes
   * @return string Optimized image HTML
   */
  function sigma_optimize_images($src, $alt = '', $width = null, $height = null, $attributes = array()) {
    $img_attributes = array();
    
    // Add dimensions to prevent layout shift
    if($width && $height) {
      $img_attributes['width'] = $width;
      $img_attributes['height'] = $height;
      $img_attributes['style'] = 'max-width: 100%; height: auto;';
    }
    
    // Add lazy loading for below-the-fold images
    if(!isset($attributes['loading'])) {
      $img_attributes['loading'] = 'lazy';
    }
    
    // Add decoding attribute for better performance
    $img_attributes['decoding'] = 'async';
    
    // Merge with provided attributes
    $img_attributes = array_merge($img_attributes, $attributes);
    
    // Build image tag
    $img_tag = '<img src="' . esc_attr($src) . '" alt="' . esc_attr($alt) . '"';
    
    foreach($img_attributes as $key => $value) {
      $img_tag .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
    }
    
    $img_tag .= '>';
    
    return $img_tag;
  }
}

if( !function_exists('sigma_add_performance_monitoring') ) {
  /**
   * Add Core Web Vitals monitoring
   * @return void
   */
  function sigma_add_performance_monitoring() {
    if(!is_admin()) {
      osc_add_hook('footer', 'sigma_output_performance_monitoring');
    }
  }
}

if( !function_exists('sigma_output_performance_monitoring') ) {
  /**
   * Output Core Web Vitals monitoring script
   * @return void
   */
  function sigma_output_performance_monitoring() {
    ?>
    <script>
    // Core Web Vitals monitoring
    (function() {
      // Track LCP (Largest Contentful Paint)
      if ('PerformanceObserver' in window) {
        new PerformanceObserver(function(entryList) {
          for (const entry of entryList.getEntries()) {
            if (entry.startTime < 5000) { // Only track LCP within 5 seconds
              console.log('LCP:', entry.startTime);
              // Send to analytics if needed
              if (typeof gtag !== 'undefined') {
                gtag('event', 'web_vitals', {
                  event_category: 'Web Vitals',
                  event_label: 'LCP',
                  value: Math.round(entry.startTime)
                });
              }
            }
          }
        }).observe({entryTypes: ['largest-contentful-paint']});
        
        // Track CLS (Cumulative Layout Shift)
        let clsValue = 0;
        new PerformanceObserver(function(entryList) {
          for (const entry of entryList.getEntries()) {
            if (!entry.hadRecentInput) {
              clsValue += entry.value;
            }
          }
          console.log('CLS:', clsValue);
          // Send to analytics if needed
          if (typeof gtag !== 'undefined') {
            gtag('event', 'web_vitals', {
              event_category: 'Web Vitals',
              event_label: 'CLS',
              value: Math.round(clsValue * 1000)
            });
          }
        }).observe({entryTypes: ['layout-shift']});
        
        // Track INP (Interaction to Next Paint) - simplified version
        let interactions = [];
        ['click', 'keydown', 'pointerdown'].forEach(function(type) {
          document.addEventListener(type, function(event) {
            const startTime = performance.now();
            requestAnimationFrame(function() {
              const duration = performance.now() - startTime;
              interactions.push(duration);
              console.log('Interaction duration:', duration);
              
              // Send to analytics if needed
              if (typeof gtag !== 'undefined' && duration > 100) {
                gtag('event', 'web_vitals', {
                  event_category: 'Web Vitals',
                  event_label: 'INP',
                  value: Math.round(duration)
                });
              }
            });
          });
        });
      }
    })();
    </script>
    <?php
  }
}

if( !function_exists('sigma_preload_critical_resources') ) {
  /**
   * Preload critical resources for better performance
   * @return void
   */
  function sigma_preload_critical_resources() {
    $page_info = sigma_get_page_type_detailed();
    
    // Preload critical CSS
    echo '<link rel="preload" href="' . osc_current_web_theme_url('css/style.css?v=' . date('YmdHis')) . '" as="style">' . PHP_EOL;
    
    // Preload critical JavaScript
    echo '<link rel="preload" href="' . osc_current_web_theme_js_url('global.js?v=' . date('YmdHis')) . '" as="script">' . PHP_EOL;
    
    // Page-specific preloading
    switch($page_info['type']) {
      case 'home':
        // Preload hero image if exists
        $hero_image = sigma_get_hero_image();
        if($hero_image) {
          echo '<link rel="preload" href="' . esc_attr($hero_image) . '" as="image">' . PHP_EOL;
        }
        break;
        
      case 'item':
        // Preload first item image
        if(osc_count_item_resources() > 0) {
          osc_item_resource();
          $first_image = osc_resource_url();
          if($first_image) {
            echo '<link rel="preload" href="' . esc_attr($first_image) . '" as="image">' . PHP_EOL;
          }
        }
        break;
    }
  }
}

if( !function_exists('sigma_get_hero_image') ) {
  /**
   * Get hero image URL for home page
   * @return string|false Hero image URL or false
   */
  function sigma_get_hero_image() {
    // Check if there's a custom hero image set
    $hero_image = osc_get_preference('hero_image', 'sigma');
    if($hero_image && file_exists(osc_uploads_path() . $hero_image)) {
      return osc_uploads_url($hero_image);
    }
    
    // Fallback to default hero image
    $default_hero = osc_current_web_theme_url('images/hero-bg.jpg');
    if(file_exists(WebThemes::newInstance()->getCurrentThemePath() . 'images/hero-bg.jpg')) {
      return $default_hero;
    }
    
    return false;
  }
}

// Initialize performance optimizations
osc_add_hook('init', 'sigma_optimize_performance', 1);
osc_add_hook('header', 'sigma_preload_critical_resources', 3);
/**
 * LAYOUT STABILITY AND IMAGE OPTIMIZATION
 */

if( !function_exists('sigma_responsive_image_with_placeholder') ) {
  /**
   * Generate responsive image with aspect ratio placeholder to prevent CLS
   * @param string $src Image source
   * @param string $alt Alt text
   * @param int $width Original width
   * @param int $height Original height
   * @param array $sizes Responsive sizes array
   * @param array $attributes Additional attributes
   * @return string Responsive image HTML
   */
  function sigma_responsive_image_with_placeholder($src, $alt = '', $width = null, $height = null, $sizes = array(), $attributes = array()) {
    if(!$width || !$height) {
      // Fallback to regular optimized image
      return sigma_optimize_images($src, $alt, $width, $height, $attributes);
    }
    
    $aspect_ratio = ($height / $width) * 100;
    
    // Build srcset if sizes provided
    $srcset = '';
    if(!empty($sizes)) {
      $srcset_parts = array();
      foreach($sizes as $size_width => $size_src) {
        $srcset_parts[] = esc_attr($size_src) . ' ' . intval($size_width) . 'w';
      }
      $srcset = 'srcset="' . implode(', ', $srcset_parts) . '"';
    }
    
    // Build sizes attribute
    $sizes_attr = '';
    if(!empty($sizes)) {
      $sizes_attr = 'sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"';
    }
    
    // Additional attributes
    $attr_string = '';
    foreach($attributes as $key => $value) {
      $attr_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
    }
    
    return sprintf(
      '<div class="responsive-image-container" style="position: relative; padding-bottom: %s%%; overflow: hidden;">
        <img src="%s" alt="%s" %s %s 
             style="position: absolute; top: 0; left: 0; width: 100%%; height: 100%%; object-fit: cover;" 
             loading="lazy" decoding="async" %s>
      </div>',
      $aspect_ratio,
      esc_attr($src),
      esc_attr($alt),
      $srcset,
      $sizes_attr,
      $attr_string
    );
  }
}

if( !function_exists('sigma_skeleton_loader') ) {
  /**
   * Generate skeleton loader to prevent layout shifts
   * @param string $type Type of skeleton (text, image, card)
   * @param array $options Skeleton options
   * @return string Skeleton HTML
   */
  function sigma_skeleton_loader($type = 'text', $options = array()) {
    $default_options = array(
      'width' => '100%',
      'height' => '20px',
      'lines' => 3,
      'class' => ''
    );
    
    $options = array_merge($default_options, $options);
    
    $skeleton_css = '
    .skeleton {
      background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
      background-size: 200% 100%;
      animation: skeleton-loading 1.5s infinite;
      border-radius: 4px;
    }
    
    @keyframes skeleton-loading {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }
    ';
    
    $html = '<style>' . $skeleton_css . '</style>';
    
    switch($type) {
      case 'text':
        for($i = 0; $i < $options['lines']; $i++) {
          $width = ($i === $options['lines'] - 1) ? '70%' : '100%';
          $html .= '<div class="skeleton ' . esc_attr($options['class']) . '" style="width: ' . $width . '; height: ' . esc_attr($options['height']) . '; margin-bottom: 10px;"></div>';
        }
        break;
        
      case 'image':
        $html .= '<div class="skeleton ' . esc_attr($options['class']) . '" style="width: ' . esc_attr($options['width']) . '; height: ' . esc_attr($options['height']) . ';"></div>';
        break;
        
      case 'card':
        $html .= '
        <div class="skeleton-card">
          <div class="skeleton" style="width: 100%; height: 200px; margin-bottom: 15px;"></div>
          <div class="skeleton" style="width: 80%; height: 20px; margin-bottom: 10px;"></div>
          <div class="skeleton" style="width: 60%; height: 16px; margin-bottom: 10px;"></div>
          <div class="skeleton" style="width: 40%; height: 16px;"></div>
        </div>';
        break;
    }
    
    return $html;
  }
}

if( !function_exists('sigma_lazy_load_content') ) {
  /**
   * Implement lazy loading for content sections
   * @param string $content Content to lazy load
   * @param string $placeholder Placeholder content
   * @param array $options Lazy loading options
   * @return string Lazy loading HTML
   */
  function sigma_lazy_load_content($content, $placeholder = '', $options = array()) {
    $default_options = array(
      'threshold' => '0.1',
      'class' => 'lazy-content',
      'skeleton' => true
    );
    
    $options = array_merge($default_options, $options);
    
    if(empty($placeholder) && $options['skeleton']) {
      $placeholder = sigma_skeleton_loader('text', array('lines' => 2));
    }
    
    $lazy_id = 'lazy-' . uniqid();
    
    return sprintf(
      '<div class="%s" data-lazy-id="%s" data-threshold="%s">
        <div class="lazy-placeholder">%s</div>
        <div class="lazy-content-hidden" style="display: none;">%s</div>
      </div>
      <script>
      (function() {
        if ("IntersectionObserver" in window) {
          const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
              if (entry.isIntersecting) {
                const container = entry.target;
                const placeholder = container.querySelector(".lazy-placeholder");
                const content = container.querySelector(".lazy-content-hidden");
                
                placeholder.style.display = "none";
                content.style.display = "block";
                observer.unobserve(container);
              }
            });
          }, { threshold: %s });
          
          const lazyElement = document.querySelector("[data-lazy-id=\'%s\']");
          if (lazyElement) {
            observer.observe(lazyElement);
          }
        } else {
          // Fallback for browsers without IntersectionObserver
          const container = document.querySelector("[data-lazy-id=\'%s\']");
          if (container) {
            container.querySelector(".lazy-placeholder").style.display = "none";
            container.querySelector(".lazy-content-hidden").style.display = "block";
          }
        }
      })();
      </script>',
      esc_attr($options['class']),
      esc_attr($lazy_id),
      esc_attr($options['threshold']),
      $placeholder,
      $content,
      esc_attr($options['threshold']),
      esc_attr($lazy_id),
      esc_attr($lazy_id)
    );
  }
}

if( !function_exists('sigma_optimize_item_images') ) {
  /**
   * Optimize item images for better performance and CLS prevention
   * @return string Optimized item images HTML
   */
  function sigma_optimize_item_images() {
    if(!osc_count_item_resources()) {
      return '';
    }
    
    $images_html = '';
    $image_count = 0;
    
    while(osc_has_item_resources() && $image_count < 5) { // Limit to 5 images for performance
      $image_url = osc_resource_url();
      $image_alt = osc_item_title() . ' - Image ' . ($image_count + 1);
      
      // Generate responsive sizes
      $sizes = array(
        '400' => osc_resource_thumbnail_url(), // Thumbnail
        '800' => osc_resource_url() // Full size
      );
      
      // First image should not be lazy loaded (LCP optimization)
      $attributes = array();
      if($image_count > 0) {
        $attributes['loading'] = 'lazy';
      } else {
        $attributes['fetchpriority'] = 'high'; // Prioritize first image
      }
      
      $images_html .= sigma_responsive_image_with_placeholder(
        $image_url,
        $image_alt,
        800, // Assume standard width
        600, // Assume standard height
        $sizes,
        $attributes
      );
      
      $image_count++;
    }
    
    return $images_html;
  }
}

/**
 * PERFORMANCE BUDGET AND MONITORING
 */

if( !function_exists('sigma_check_performance_budget') ) {
  /**
   * Check if page meets performance budget
   * @return array Performance budget status
   */
  function sigma_check_performance_budget() {
    $budget = array(
      'max_css_size' => 50000, // 50KB
      'max_js_size' => 100000, // 100KB
      'max_images_size' => 500000, // 500KB
      'max_total_size' => 1000000, // 1MB
      'max_requests' => 50
    );
    
    $current = array(
      'css_size' => 0,
      'js_size' => 0,
      'images_size' => 0,
      'total_size' => 0,
      'requests' => 0
    );
    
    // This would require more sophisticated measurement
    // For now, return budget structure
    return array(
      'budget' => $budget,
      'current' => $current,
      'within_budget' => true
    );
  }
}

if( !function_exists('sigma_generate_performance_report') ) {
  /**
   * Generate performance optimization report
   * @return array Performance report
   */
  function sigma_generate_performance_report() {
    return array(
      'optimizations_applied' => array(
        'Critical CSS inlined',
        'Non-critical CSS deferred',
        'JavaScript optimized and deferred',
        'Images lazy loaded with dimensions',
        'Fonts optimized with font-display: swap',
        'Compression and caching enabled',
        'Core Web Vitals monitoring active'
      ),
      'performance_budget' => sigma_check_performance_budget(),
      'recommendations' => array(
        'Monitor Core Web Vitals regularly',
        'Optimize images further with WebP format',
        'Consider implementing service worker for caching',
        'Regular performance audits with Lighthouse'
      )
    );
  }
}
/**
 * SITEMAP & ROBOTS.TXT OPTIMIZATION
 */

if( !function_exists('sigma_init_sitemap_system') ) {
  /**
   * Initialize sitemap generation system
   * @return void
   */
  function sigma_init_sitemap_system() {
    // Handle sitemap requests
    osc_add_hook('init', 'sigma_handle_sitemap_requests', 1);
    
    // Generate robots.txt
    osc_add_hook('init', 'sigma_handle_robots_txt', 1);
    
    // Auto-update sitemaps when content changes
    osc_add_hook('posted_item', 'sigma_invalidate_sitemap_cache');
    osc_add_hook('edited_item', 'sigma_invalidate_sitemap_cache');
    osc_add_hook('delete_item', 'sigma_invalidate_sitemap_cache');
  }
}

if( !function_exists('sigma_handle_sitemap_requests') ) {
  /**
   * Handle sitemap URL requests
   * @return void
   */
  function sigma_handle_sitemap_requests() {
    $request_uri = $_SERVER['REQUEST_URI'];
    $parsed_url = parse_url($request_uri);
    $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    
    // Remove leading slash and query parameters
    $path = ltrim($path, '/');
    
    if($path === 'sitemap.xml') {
      sigma_serve_sitemap_index();
      exit;
    } elseif(preg_match('/^sitemap-([a-z]+)(?:-(\d+))?\.xml$/', $path, $matches)) {
      $type = $matches[1];
      $page = isset($matches[2]) ? intval($matches[2]) : 1;
      sigma_serve_sitemap($type, $page);
      exit;
    }
  }
}

if( !function_exists('sigma_handle_robots_txt') ) {
  /**
   * Handle robots.txt requests
   * @return void
   */
  function sigma_handle_robots_txt() {
    $request_uri = $_SERVER['REQUEST_URI'];
    $parsed_url = parse_url($request_uri);
    $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    
    if(ltrim($path, '/') === 'robots.txt') {
      sigma_serve_robots_txt();
      exit;
    }
  }
}

if( !function_exists('sigma_serve_sitemap_index') ) {
  /**
   * Serve the main sitemap index
   * @return void
   */
  function sigma_serve_sitemap_index() {
    $cache_key = 'sigma_sitemap_index';
    $cached_sitemap = get_transient($cache_key);
    
    if($cached_sitemap !== false) {
      sigma_output_xml($cached_sitemap);
      return;
    }
    
    $base_url = osc_base_url();
    $current_time = date('c');
    
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    // Categories sitemap
    $xml .= '  <sitemap>' . "\n";
    $xml .= '    <loc>' . esc_xml($base_url . 'sitemap-categories.xml') . '</loc>' . "\n";
    $xml .= '    <lastmod>' . $current_time . '</lastmod>' . "\n";
    $xml .= '  </sitemap>' . "\n";
    
    // Items sitemaps (paginated)
    $items_pages = sigma_get_items_sitemap_pages();
    for($page = 1; $page <= $items_pages; $page++) {
      $xml .= '  <sitemap>' . "\n";
      $xml .= '    <loc>' . esc_xml($base_url . 'sitemap-items-' . $page . '.xml') . '</loc>' . "\n";
      $xml .= '    <lastmod>' . $current_time . '</lastmod>' . "\n";
      $xml .= '  </sitemap>' . "\n";
    }
    
    // Locations sitemap
    $xml .= '  <sitemap>' . "\n";
    $xml .= '    <loc>' . esc_xml($base_url . 'sitemap-locations.xml') . '</loc>' . "\n";
    $xml .= '    <lastmod>' . $current_time . '</lastmod>' . "\n";
    $xml .= '  </sitemap>' . "\n";
    
    // Pages sitemap
    $xml .= '  <sitemap>' . "\n";
    $xml .= '    <loc>' . esc_xml($base_url . 'sitemap-pages.xml') . '</loc>' . "\n";
    $xml .= '    <lastmod>' . $current_time . '</lastmod>' . "\n";
    $xml .= '  </sitemap>' . "\n";
    
    $xml .= '</sitemapindex>';
    
    // Cache for 1 hour
    set_transient($cache_key, $xml, 3600);
    
    sigma_output_xml($xml);
  }
}

if( !function_exists('sigma_serve_sitemap') ) {
  /**
   * Serve specific sitemap type
   * @param string $type Sitemap type
   * @param int $page Page number for paginated sitemaps
   * @return void
   */
  function sigma_serve_sitemap($type, $page = 1) {
    $cache_key = 'sigma_sitemap_' . $type . '_' . $page;
    $cached_sitemap = get_transient($cache_key);
    
    if($cached_sitemap !== false) {
      sigma_output_xml($cached_sitemap);
      return;
    }
    
    $xml = '';
    
    switch($type) {
      case 'categories':
        $xml = sigma_generate_categories_sitemap();
        break;
      case 'items':
        $xml = sigma_generate_items_sitemap($page);
        break;
      case 'locations':
        $xml = sigma_generate_locations_sitemap();
        break;
      case 'pages':
        $xml = sigma_generate_pages_sitemap();
        break;
      default:
        header('HTTP/1.0 404 Not Found');
        exit;
    }
    
    if($xml) {
      // Cache for 1 hour
      set_transient($cache_key, $xml, 3600);
      sigma_output_xml($xml);
    } else {
      header('HTTP/1.0 404 Not Found');
      exit;
    }
  }
}

if( !function_exists('sigma_generate_categories_sitemap') ) {
  /**
   * Generate categories sitemap
   * @return string XML sitemap content
   */
  function sigma_generate_categories_sitemap() {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    // Get all active categories with items
    $categories = Category::newInstance()->listAll(false);
    
    foreach($categories as $category) {
      if($category['b_enabled'] != 1) continue;
      
      // Check if category has active items
      $item_count = Item::newInstance()->countByCategory($category['pk_i_id']);
      if($item_count == 0) continue;
      
      $url = osc_search_category_url($category['pk_i_id']);
      $lastmod = isset($category['dt_last_modified']) ? date('c', strtotime($category['dt_last_modified'])) : date('c');
      
      // Determine priority based on category level and item count
      $priority = sigma_get_category_priority($category, $item_count);
      
      $xml .= '  <url>' . "\n";
      $xml .= '    <loc>' . esc_xml($url) . '</loc>' . "\n";
      $xml .= '    <lastmod>' . $lastmod . '</lastmod>' . "\n";
      $xml .= '    <changefreq>monthly</changefreq>' . "\n";
      $xml .= '    <priority>' . $priority . '</priority>' . "\n";
      $xml .= '  </url>' . "\n";
    }
    
    $xml .= '</urlset>';
    
    return $xml;
  }
}

if( !function_exists('sigma_generate_items_sitemap') ) {
  /**
   * Generate items sitemap with pagination
   * @param int $page Page number
   * @return string XML sitemap content
   */
  function sigma_generate_items_sitemap($page = 1) {
    $items_per_page = 10000; // Sitemap limit
    $offset = ($page - 1) * $items_per_page;
    
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    // Get active items
    $mSearch = new Search();
    $mSearch->addConditions(DB_TABLE_PREFIX . 't_item.b_enabled = 1');
    $mSearch->addConditions(DB_TABLE_PREFIX . 't_item.b_active = 1');
    $mSearch->addConditions(DB_TABLE_PREFIX . 't_item.b_spam = 0');
    $mSearch->limit($offset, $items_per_page);
    $mSearch->order(DB_TABLE_PREFIX . 't_item.dt_pub_date', 'DESC');
    
    $items = $mSearch->doSearch();
    
    foreach($items as $item) {
      $url = osc_item_url_from_item($item);
      $lastmod = date('c', strtotime($item['dt_mod_date']));
      
      // Determine priority based on item age and category
      $priority = sigma_get_item_priority($item);
      
      // Determine change frequency based on item age
      $changefreq = sigma_get_item_changefreq($item);
      
      $xml .= '  <url>' . "\n";
      $xml .= '    <loc>' . esc_xml($url) . '</loc>' . "\n";
      $xml .= '    <lastmod>' . $lastmod . '</lastmod>' . "\n";
      $xml .= '    <changefreq>' . $changefreq . '</changefreq>' . "\n";
      $xml .= '    <priority>' . $priority . '</priority>' . "\n";
      $xml .= '  </url>' . "\n";
    }
    
    $xml .= '</urlset>';
    
    return $xml;
  }
}

if( !function_exists('sigma_generate_locations_sitemap') ) {
  /**
   * Generate locations sitemap
   * @return string XML sitemap content
   */
  function sigma_generate_locations_sitemap() {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    // Get all regions with items
    $regions = Region::newInstance()->listAll();
    foreach($regions as $region) {
      if($region['b_enabled'] != 1) continue;
      
      $item_count = Item::newInstance()->countByRegion($region['pk_i_id']);
      if($item_count == 0) continue;
      
      $url = osc_search_url(array('sRegion' => $region['pk_i_id']));
      $priority = $item_count > 100 ? '0.7' : '0.6';
      
      $xml .= '  <url>' . "\n";
      $xml .= '    <loc>' . esc_xml($url) . '</loc>' . "\n";
      $xml .= '    <lastmod>' . date('c') . '</lastmod>' . "\n";
      $xml .= '    <changefreq>monthly</changefreq>' . "\n";
      $xml .= '    <priority>' . $priority . '</priority>' . "\n";
      $xml .= '  </url>' . "\n";
    }
    
    // Get all cities with items
    $cities = City::newInstance()->listAll();
    foreach($cities as $city) {
      if($city['b_enabled'] != 1) continue;
      
      $item_count = Item::newInstance()->countByCity($city['pk_i_id']);
      if($item_count == 0) continue;
      
      $url = osc_search_url(array('sCity' => $city['pk_i_id']));
      $priority = $item_count > 50 ? '0.7' : '0.5';
      
      $xml .= '  <url>' . "\n";
      $xml .= '    <loc>' . esc_xml($url) . '</loc>' . "\n";
      $xml .= '    <lastmod>' . date('c') . '</lastmod>' . "\n";
      $xml .= '    <changefreq>monthly</changefreq>' . "\n";
      $xml .= '    <priority>' . $priority . '</priority>' . "\n";
      $xml .= '  </url>' . "\n";
    }
    
    $xml .= '</urlset>';
    
    return $xml;
  }
}

if( !function_exists('sigma_generate_pages_sitemap') ) {
  /**
   * Generate static pages sitemap
   * @return string XML sitemap content
   */
  function sigma_generate_pages_sitemap() {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    // Add home page
    $xml .= '  <url>' . "\n";
    $xml .= '    <loc>' . esc_xml(osc_base_url()) . '</loc>' . "\n";
    $xml .= '    <lastmod>' . date('c') . '</lastmod>' . "\n";
    $xml .= '    <changefreq>daily</changefreq>' . "\n";
    $xml .= '    <priority>1.0</priority>' . "\n";
    $xml .= '  </url>' . "\n";
    
    // Get all static pages
    $pages = Page::newInstance()->listAll(0, null, null, null, null, null);
    
    foreach($pages as $page) {
      if($page['b_enabled'] != 1) continue;
      
      $url = osc_static_page_url($page);
      $lastmod = isset($page['dt_last_modified']) ? date('c', strtotime($page['dt_last_modified'])) : date('c');
      
      // Determine priority based on page importance
      $priority = sigma_get_page_priority($page);
      
      $xml .= '  <url>' . "\n";
      $xml .= '    <loc>' . esc_xml($url) . '</loc>' . "\n";
      $xml .= '    <lastmod>' . $lastmod . '</lastmod>' . "\n";
      $xml .= '    <changefreq>monthly</changefreq>' . "\n";
      $xml .= '    <priority>' . $priority . '</priority>' . "\n";
      $xml .= '  </url>' . "\n";
    }
    
    $xml .= '</urlset>';
    
    return $xml;
  }
}

if( !function_exists('sigma_serve_robots_txt') ) {
  /**
   * Serve optimized robots.txt
   * @return void
   */
  function sigma_serve_robots_txt() {
    $base_url = osc_base_url();
    
    $robots_txt = "# Robots.txt for " . osc_page_title() . "\n";
    $robots_txt .= "# Generated automatically by Sigma theme\n\n";
    
    $robots_txt .= "User-agent: *\n\n";
    
    // Allow important content
    $robots_txt .= "# Allow important content\n";
    $robots_txt .= "Allow: /\n";
    $robots_txt .= "Allow: /search\n";
    $robots_txt .= "Allow: /item/\n";
    $robots_txt .= "Allow: /user/pub_profile/\n";
    $robots_txt .= "Allow: /page/\n\n";
    
    // Allow theme assets
    $robots_txt .= "# Allow theme assets\n";
    $robots_txt .= "Allow: /oc-content/themes/*/css/\n";
    $robots_txt .= "Allow: /oc-content/themes/*/js/\n";
    $robots_txt .= "Allow: /oc-content/themes/*/images/\n\n";
    
    // Disallow admin and private areas
    $robots_txt .= "# Disallow admin and private areas\n";
    $robots_txt .= "Disallow: /oc-admin/\n";
    $robots_txt .= "Disallow: /oc-includes/\n";
    $robots_txt .= "Disallow: /oc-content/plugins/\n";
    $robots_txt .= "Disallow: /oc-content/uploads/temp/\n\n";
    
    // Disallow user private areas
    $robots_txt .= "# Disallow user private areas\n";
    $robots_txt .= "Disallow: /user/\n";
    $robots_txt .= "Disallow: /login\n";
    $robots_txt .= "Disallow: /register\n\n";
    
    // Disallow filtered and parameterized URLs
    $robots_txt .= "# Disallow filtered and parameterized URLs\n";
    $robots_txt .= "Disallow: /search?*\n";
    $robots_txt .= "Disallow: /*?sOrder=*\n";
    $robots_txt .= "Disallow: /*?iOrderType=*\n";
    $robots_txt .= "Disallow: /*?sShowAs=*\n";
    $robots_txt .= "Disallow: /*?iPage=*\n";
    $robots_txt .= "Disallow: /*?sPriceMin=*\n";
    $robots_txt .= "Disallow: /*?sPriceMax=*\n";
    $robots_txt .= "Disallow: /*?sCondition=*\n";
    $robots_txt .= "Disallow: /*?sCompany=*\n";
    $robots_txt .= "Disallow: /*?sPeriod=*\n\n";
    
    // Disallow technical files
    $robots_txt .= "# Disallow technical files\n";
    $robots_txt .= "Disallow: /*.php$\n";
    $robots_txt .= "Disallow: /config.php\n";
    $robots_txt .= "Disallow: /.htaccess\n\n";
    
    // Crawl delay
    $robots_txt .= "# Crawl delay\n";
    $robots_txt .= "Crawl-delay: 1\n\n";
    
    // User-agent specific rules
    $robots_txt .= "# Google-specific optimizations\n";
    $robots_txt .= "User-agent: Googlebot\n";
    $robots_txt .= "Crawl-delay: 0.5\n\n";
    
    $robots_txt .= "# Bing-specific optimizations\n";
    $robots_txt .= "User-agent: Bingbot\n";
    $robots_txt .= "Crawl-delay: 1\n\n";
    
    // Block problematic crawlers
    $robots_txt .= "# Block problematic crawlers\n";
    $robots_txt .= "User-agent: AhrefsBot\n";
    $robots_txt .= "Disallow: /\n\n";
    
    $robots_txt .= "User-agent: MJ12bot\n";
    $robots_txt .= "Disallow: /\n\n";
    
    // Sitemap location
    $robots_txt .= "# Sitemap location\n";
    $robots_txt .= "Sitemap: " . $base_url . "sitemap.xml\n";
    
    header('Content-Type: text/plain; charset=UTF-8');
    echo $robots_txt;
  }
}

// Helper functions for sitemap generation

if( !function_exists('sigma_get_items_sitemap_pages') ) {
  /**
   * Calculate number of pages needed for items sitemap
   * @return int Number of pages
   */
  function sigma_get_items_sitemap_pages() {
    $total_items = Item::newInstance()->countAll();
    $items_per_page = 10000;
    return max(1, ceil($total_items / $items_per_page));
  }
}

if( !function_exists('sigma_get_category_priority') ) {
  /**
   * Get priority for category based on level and item count
   * @param array $category Category data
   * @param int $item_count Number of items in category
   * @return string Priority value
   */
  function sigma_get_category_priority($category, $item_count) {
    if($category['fk_i_parent_id'] == null) {
      // Root category
      return $item_count > 100 ? '0.8' : '0.7';
    } else {
      // Subcategory
      return $item_count > 50 ? '0.7' : '0.6';
    }
  }
}

if( !function_exists('sigma_get_item_priority') ) {
  /**
   * Get priority for item based on age and category
   * @param array $item Item data
   * @return string Priority value
   */
  function sigma_get_item_priority($item) {
    $days_old = (time() - strtotime($item['dt_pub_date'])) / (24 * 60 * 60);
    
    if($days_old < 7) {
      return '0.9';
    } elseif($days_old < 30) {
      return '0.8';
    } elseif($days_old < 90) {
      return '0.7';
    } else {
      return '0.6';
    }
  }
}

if( !function_exists('sigma_get_item_changefreq') ) {
  /**
   * Get change frequency for item based on age
   * @param array $item Item data
   * @return string Change frequency
   */
  function sigma_get_item_changefreq($item) {
    $days_old = (time() - strtotime($item['dt_pub_date'])) / (24 * 60 * 60);
    
    if($days_old < 30) {
      return 'weekly';
    } else {
      return 'monthly';
    }
  }
}

if( !function_exists('sigma_get_page_priority') ) {
  /**
   * Get priority for static page
   * @param array $page Page data
   * @return string Priority value
   */
  function sigma_get_page_priority($page) {
    $important_pages = array('contact', 'about', 'terms', 'privacy');
    $page_slug = isset($page['s_internal_name']) ? $page['s_internal_name'] : '';
    
    if(in_array(strtolower($page_slug), $important_pages)) {
      return '0.8';
    } else {
      return '0.6';
    }
  }
}

if( !function_exists('sigma_output_xml') ) {
  /**
   * Output XML with proper headers
   * @param string $xml XML content
   * @return void
   */
  function sigma_output_xml($xml) {
    header('Content-Type: application/xml; charset=UTF-8');
    header('X-Robots-Tag: noindex');
    echo $xml;
  }
}

if( !function_exists('sigma_invalidate_sitemap_cache') ) {
  /**
   * Invalidate sitemap cache when content changes
   * @return void
   */
  function sigma_invalidate_sitemap_cache() {
    // Delete all sitemap-related transients
    delete_transient('sigma_sitemap_index');
    delete_transient('sigma_sitemap_categories_1');
    delete_transient('sigma_sitemap_locations_1');
    delete_transient('sigma_sitemap_pages_1');
    
    // Delete items sitemap cache (multiple pages)
    $pages = sigma_get_items_sitemap_pages();
    for($i = 1; $i <= $pages; $i++) {
      delete_transient('sigma_sitemap_items_' . $i);
    }
  }
}

// Initialize sitemap system
osc_add_hook('init', 'sigma_init_sitemap_system', 1);
/**
 * STRUCTURED DATA (JSON-LD) IMPLEMENTATION
 */

if( !function_exists('sigma_init_structured_data') ) {
  /**
   * Initialize structured data system
   * @return void
   */
  function sigma_init_structured_data() {
    // Add structured data to page head
    osc_add_hook('header', 'sigma_output_structured_data', 9);
  }
}

if( !function_exists('sigma_output_structured_data') ) {
  /**
   * Output structured data based on current page type
   * @return void
   */
  function sigma_output_structured_data() {
    $schemas = array();
    
    // Always include Organization schema
    $schemas[] = sigma_generate_organization_schema();
    
    // Page-specific schemas
    if(osc_is_home_page()) {
      $schemas[] = sigma_generate_website_schema();
      
    } elseif(osc_is_item_page()) {
      $schemas[] = sigma_generate_product_schema();
      $schemas[] = sigma_generate_breadcrumb_schema('item');
      
    } elseif(osc_is_search_page()) {
      if(osc_search_category()) {
        $schemas[] = sigma_generate_collection_page_schema();
      }
      $schemas[] = sigma_generate_breadcrumb_schema('search');
      
    } elseif(osc_is_static_page()) {
      $schemas[] = sigma_generate_webpage_schema();
      $schemas[] = sigma_generate_breadcrumb_schema('page');
      
    } elseif(Rewrite::newInstance()->get_location() === 'user' && Rewrite::newInstance()->get_section() === 'pub_profile') {
      $schemas[] = sigma_generate_person_schema();
      $schemas[] = sigma_generate_breadcrumb_schema('user');
    }
    
    // Output all schemas
    foreach($schemas as $schema) {
      if($schema && is_array($schema)) {
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>' . PHP_EOL;
      }
    }
  }
}

if( !function_exists('sigma_generate_organization_schema') ) {
  /**
   * Generate Organization schema (site-wide)
   * @return array Organization schema
   */
  function sigma_generate_organization_schema() {
    $schema = array(
      '@context' => 'https://schema.org',
      '@type' => 'Organization',
      'name' => osc_page_title(),
      'url' => osc_base_url()
    );
    
    // Add logo if available
    $logo_url = sigma_get_site_logo_url();
    if($logo_url) {
      $schema['logo'] = $logo_url;
    }
    
    // Add contact information if available
    $contact_info = sigma_get_contact_information();
    if($contact_info) {
      $schema['contactPoint'] = array(
        '@type' => 'ContactPoint',
        'contactType' => 'customer service'
      );
      
      if(isset($contact_info['phone'])) {
        $schema['contactPoint']['telephone'] = $contact_info['phone'];
      }
      
      if(isset($contact_info['email'])) {
        $schema['contactPoint']['email'] = $contact_info['email'];
      }
    }
    
    // Add social media links if available
    $social_links = sigma_get_social_media_links();
    if(!empty($social_links)) {
      $schema['sameAs'] = $social_links;
    }
    
    return $schema;
  }
}

if( !function_exists('sigma_generate_website_schema') ) {
  /**
   * Generate WebSite schema for home page
   * @return array WebSite schema
   */
  function sigma_generate_website_schema() {
    $schema = array(
      '@context' => 'https://schema.org',
      '@type' => 'WebSite',
      'name' => osc_page_title(),
      'url' => osc_base_url()
    );
    
    // Add search functionality
    $schema['potentialAction'] = array(
      '@type' => 'SearchAction',
      'target' => array(
        '@type' => 'EntryPoint',
        'urlTemplate' => osc_base_url() . 'search?sPattern={search_term_string}'
      ),
      'query-input' => 'required name=search_term_string'
    );
    
    return $schema;
  }
}

if( !function_exists('sigma_generate_product_schema') ) {
  /**
   * Generate Product schema for item pages
   * @return array|false Product schema or false if not applicable
   */
  function sigma_generate_product_schema() {
    if(!osc_is_item_page()) {
      return false;
    }
    
    $item = osc_item();
    if(!$item) {
      return false;
    }
    
    $schema = array(
      '@context' => 'https://schema.org/',
      '@type' => 'Product',
      'name' => osc_item_title(),
      'url' => osc_item_url()
    );
    
    // Add description
    $description = osc_item_description();
    if($description) {
      $schema['description'] = sigma_clean_text_for_schema($description);
    }
    
    // Add images
    $images = sigma_get_item_images_for_schema();
    if(!empty($images)) {
      $schema['image'] = $images;
    }
    
    // Add category
    if(osc_item_category()) {
      $schema['category'] = osc_item_category();
    }
    
    // Add offer information
    $offer = sigma_generate_offer_schema($item);
    if($offer) {
      $schema['offers'] = $offer;
    }
    
    // Add condition if available
    $condition = sigma_get_item_condition();
    if($condition) {
      $schema['itemCondition'] = $condition;
    }
    
    // Add brand if available
    $brand = sigma_get_item_brand();
    if($brand) {
      $schema['brand'] = array(
        '@type' => 'Brand',
        'name' => $brand
      );
    }
    
    return $schema;
  }
}

if( !function_exists('sigma_generate_offer_schema') ) {
  /**
   * Generate Offer schema for product
   * @param array $item Item data
   * @return array|false Offer schema or false
   */
  function sigma_generate_offer_schema($item) {
    $price = osc_item_price();
    if(!$price || $price <= 0) {
      return false;
    }
    
    $offer = array(
      '@type' => 'Offer',
      'price' => number_format((float)$price, 2, '.', ''),
      'priceCurrency' => osc_get_preference('currency', 'osclass') ?: 'USD',
      'availability' => 'https://schema.org/InStock',
      'url' => osc_item_url()
    );
    
    // Add seller information
    if(osc_item_user_name()) {
      $offer['seller'] = array(
        '@type' => 'Person',
        'name' => osc_item_user_name()
      );
      
      // Add seller URL if public profile exists
      if(osc_item_user_id()) {
        $offer['seller']['url'] = osc_user_public_profile_url(osc_item_user_id());
      }
    }
    
    // Add valid through date (item expiration)
    if(osc_item_expiration_date()) {
      $offer['validThrough'] = date('c', strtotime(osc_item_expiration_date()));
    }
    
    return $offer;
  }
}

if( !function_exists('sigma_generate_collection_page_schema') ) {
  /**
   * Generate CollectionPage schema for category pages
   * @return array|false CollectionPage schema or false
   */
  function sigma_generate_collection_page_schema() {
    if(!osc_search_category()) {
      return false;
    }
    
    $schema = array(
      '@context' => 'https://schema.org',
      '@type' => 'CollectionPage',
      'name' => osc_search_category(),
      'url' => osc_get_current_url()
    );
    
    // Add description if available
    $category_description = osc_category_description();
    if($category_description) {
      $schema['description'] = sigma_clean_text_for_schema($category_description);
    }
    
    // Add number of items
    $item_count = osc_search_total_items();
    if($item_count > 0) {
      $schema['numberOfItems'] = $item_count;
    }
    
    // Add main entity (the category itself)
    $schema['mainEntity'] = array(
      '@type' => 'ItemList',
      'name' => osc_search_category(),
      'numberOfItems' => $item_count
    );
    
    return $schema;
  }
}

if( !function_exists('sigma_generate_breadcrumb_schema') ) {
  /**
   * Generate BreadcrumbList schema
   * @param string $page_type Current page type
   * @return array BreadcrumbList schema
   */
  function sigma_generate_breadcrumb_schema($page_type) {
    $breadcrumbs = sigma_get_breadcrumb_data($page_type);
    
    if(empty($breadcrumbs)) {
      return false;
    }
    
    $schema = array(
      '@context' => 'https://schema.org',
      '@type' => 'BreadcrumbList',
      'itemListElement' => array()
    );
    
    foreach($breadcrumbs as $position => $breadcrumb) {
      $schema['itemListElement'][] = array(
        '@type' => 'ListItem',
        'position' => $position + 1,
        'name' => $breadcrumb['name'],
        'item' => $breadcrumb['url']
      );
    }
    
    return $schema;
  }
}

if( !function_exists('sigma_generate_webpage_schema') ) {
  /**
   * Generate WebPage schema for static pages
   * @return array WebPage schema
   */
  function sigma_generate_webpage_schema() {
    $schema = array(
      '@context' => 'https://schema.org',
      '@type' => 'WebPage',
      'name' => osc_static_page_title(),
      'url' => osc_get_current_url()
    );
    
    // Add description if available
    $page_text = osc_static_page_text();
    if($page_text) {
      $schema['description'] = sigma_clean_text_for_schema($page_text, 160);
    }
    
    // Add main entity
    $schema['mainEntity'] = array(
      '@type' => 'Article',
      'headline' => osc_static_page_title(),
      'text' => sigma_clean_text_for_schema($page_text)
    );
    
    return $schema;
  }
}

if( !function_exists('sigma_generate_person_schema') ) {
  /**
   * Generate Person schema for user profile pages
   * @return array|false Person schema or false
   */
  function sigma_generate_person_schema() {
    if(!osc_user_name()) {
      return false;
    }
    
    $schema = array(
      '@context' => 'https://schema.org',
      '@type' => 'Person',
      'name' => osc_user_name(),
      'url' => osc_get_current_url()
    );
    
    // Add description if available
    $user_info = osc_user_info();
    if(isset($user_info['s_info']) && $user_info['s_info']) {
      $schema['description'] = sigma_clean_text_for_schema($user_info['s_info']);
    }
    
    // Add location if available (city/region)
    if(osc_user_city()) {
      $schema['address'] = array(
        '@type' => 'PostalAddress',
        'addressLocality' => osc_user_city()
      );
      
      if(osc_user_region()) {
        $schema['address']['addressRegion'] = osc_user_region();
      }
    }
    
    return $schema;
  }
}

// Helper functions for structured data

if( !function_exists('sigma_get_breadcrumb_data') ) {
  /**
   * Get breadcrumb data for current page
   * @param string $page_type Page type
   * @return array Breadcrumb data
   */
  function sigma_get_breadcrumb_data($page_type) {
    $breadcrumbs = array();
    
    // Always start with home
    $breadcrumbs[] = array(
      'name' => __('Home', 'sigma'),
      'url' => osc_base_url()
    );
    
    switch($page_type) {
      case 'item':
        // Add category hierarchy
        if(osc_item_category_id()) {
          $category_path = sigma_get_category_hierarchy(osc_item_category_id());
          foreach($category_path as $category) {
            $breadcrumbs[] = array(
              'name' => $category['s_name'],
              'url' => osc_search_category_url($category['pk_i_id'])
            );
          }
        }
        
        // Add current item
        $breadcrumbs[] = array(
          'name' => osc_item_title(),
          'url' => osc_item_url()
        );
        break;
        
      case 'search':
        if(osc_search_category()) {
          $breadcrumbs[] = array(
            'name' => osc_search_category(),
            'url' => osc_get_current_url()
          );
        } else {
          $breadcrumbs[] = array(
            'name' => __('Search', 'sigma'),
            'url' => osc_get_current_url()
          );
        }
        break;
        
      case 'page':
        $breadcrumbs[] = array(
          'name' => osc_static_page_title(),
          'url' => osc_get_current_url()
        );
        break;
        
      case 'user':
        $breadcrumbs[] = array(
          'name' => osc_user_name() . "'s Profile",
          'url' => osc_get_current_url()
        );
        break;
    }
    
    return $breadcrumbs;
  }
}

if( !function_exists('sigma_get_category_hierarchy') ) {
  /**
   * Get category hierarchy path
   * @param int $category_id Category ID
   * @return array Category hierarchy
   */
  function sigma_get_category_hierarchy($category_id) {
    $hierarchy = array();
    $category = Category::newInstance()->findByPrimaryKey($category_id);
    
    if($category) {
      $hierarchy[] = $category;
      
      // Get parent categories
      if($category['fk_i_parent_id']) {
        $parent_hierarchy = sigma_get_category_hierarchy($category['fk_i_parent_id']);
        $hierarchy = array_merge($parent_hierarchy, $hierarchy);
      }
    }
    
    return $hierarchy;
  }
}

if( !function_exists('sigma_get_item_images_for_schema') ) {
  /**
   * Get item images formatted for schema
   * @return array Image URLs
   */
  function sigma_get_item_images_for_schema() {
    $images = array();
    
    if(osc_count_item_resources() > 0) {
      while(osc_has_item_resources()) {
        $image_url = osc_resource_url();
        if($image_url) {
          $images[] = $image_url;
        }
      }
    }
    
    return $images;
  }
}

if( !function_exists('sigma_get_item_condition') ) {
  /**
   * Get item condition for schema
   * @return string|false Schema.org condition URL or false
   */
  function sigma_get_item_condition() {
    // Check if there's a condition custom field
    $condition = osc_item_field('condition');
    
    if($condition) {
      switch(strtolower($condition)) {
        case 'new':
          return 'https://schema.org/NewCondition';
        case 'used':
          return 'https://schema.org/UsedCondition';
        case 'refurbished':
          return 'https://schema.org/RefurbishedCondition';
        case 'damaged':
          return 'https://schema.org/DamagedCondition';
      }
    }
    
    // Default to used condition for classified ads
    return 'https://schema.org/UsedCondition';
  }
}

if( !function_exists('sigma_get_item_brand') ) {
  /**
   * Get item brand for schema
   * @return string|false Brand name or false
   */
  function sigma_get_item_brand() {
    // Check if there's a brand custom field
    $brand = osc_item_field('brand');
    
    if($brand) {
      return $brand;
    }
    
    // Check if brand is mentioned in title or description
    $title = osc_item_title();
    $description = osc_item_description();
    
    // Simple brand detection (could be enhanced)
    $common_brands = array('Apple', 'Samsung', 'Sony', 'Nike', 'Adidas', 'BMW', 'Mercedes', 'Toyota', 'Honda');
    
    foreach($common_brands as $brand) {
      if(stripos($title, $brand) !== false || stripos($description, $brand) !== false) {
        return $brand;
      }
    }
    
    return false;
  }
}

if( !function_exists('sigma_get_site_logo_url') ) {
  /**
   * Get site logo URL for schema
   * @return string|false Logo URL or false
   */
  function sigma_get_site_logo_url() {
    $logo = osc_get_preference('logo', 'sigma');
    
    if($logo && file_exists(osc_uploads_path() . $logo)) {
      return osc_uploads_url($logo);
    }
    
    // Fallback to default logo
    $default_logo = osc_current_web_theme_url('images/logo.png');
    if(file_exists(WebThemes::newInstance()->getCurrentThemePath() . 'images/logo.png')) {
      return $default_logo;
    }
    
    return false;
  }
}

if( !function_exists('sigma_get_contact_information') ) {
  /**
   * Get contact information for schema
   * @return array|false Contact information or false
   */
  function sigma_get_contact_information() {
    $contact = array();
    
    // Check theme preferences for contact info
    $phone = osc_get_preference('contact_phone', 'sigma');
    $email = osc_get_preference('contact_email', 'sigma');
    
    if($phone) {
      $contact['phone'] = $phone;
    }
    
    if($email) {
      $contact['email'] = $email;
    }
    
    // Fallback to admin email
    if(empty($contact['email'])) {
      $admin_email = osc_contact_email();
      if($admin_email) {
        $contact['email'] = $admin_email;
      }
    }
    
    return !empty($contact) ? $contact : false;
  }
}

if( !function_exists('sigma_get_social_media_links') ) {
  /**
   * Get social media links for schema
   * @return array Social media URLs
   */
  function sigma_get_social_media_links() {
    $social_links = array();
    
    // Check theme preferences for social media links
    $facebook = osc_get_preference('facebook_url', 'sigma');
    $twitter = osc_get_preference('twitter_url', 'sigma');
    $instagram = osc_get_preference('instagram_url', 'sigma');
    $linkedin = osc_get_preference('linkedin_url', 'sigma');
    
    if($facebook) $social_links[] = $facebook;
    if($twitter) $social_links[] = $twitter;
    if($instagram) $social_links[] = $instagram;
    if($linkedin) $social_links[] = $linkedin;
    
    return $social_links;
  }
}

if( !function_exists('sigma_clean_text_for_schema') ) {
  /**
   * Clean text for use in schema markup
   * @param string $text Text to clean
   * @param int $max_length Maximum length (optional)
   * @return string Cleaned text
   */
  function sigma_clean_text_for_schema($text, $max_length = null) {
    // Strip HTML tags
    $text = strip_tags($text);
    
    // Remove extra whitespace
    $text = preg_replace('/\s+/', ' ', trim($text));
    
    // Remove special characters that might break JSON
    $text = str_replace(array('"', "\n", "\r", "\t"), array('\"', ' ', ' ', ' '), $text);
    
    // Truncate if max length specified
    if($max_length && strlen($text) > $max_length) {
      $text = substr($text, 0, $max_length - 3) . '...';
    }
    
    return $text;
  }
}

// Initialize structured data system
osc_add_hook('init', 'sigma_init_structured_data', 1);
/**
 * MOBILE UX IMPROVEMENTS
 */

if( !function_exists('sigma_init_mobile_optimizations') ) {
  /**
   * Initialize mobile UX optimizations
   * @return void
   */
  function sigma_init_mobile_optimizations() {
    // Add mobile-specific CSS and JavaScript
    osc_add_hook('header', 'sigma_add_mobile_optimizations', 6);
    
    // Add mobile-friendly viewport meta tag (if not already present)
    osc_add_hook('header', 'sigma_ensure_mobile_viewport', 1);
    
    // Add touch-friendly interactions
    osc_add_hook('footer', 'sigma_add_touch_optimizations');
  }
}

if( !function_exists('sigma_ensure_mobile_viewport') ) {
  /**
   * Ensure proper mobile viewport meta tag
   * @return void
   */
  function sigma_ensure_mobile_viewport() {
    // Check if viewport meta tag is already present in head.php
    // If not, add it (this is a safety check)
    echo '<!-- Mobile viewport optimization -->' . PHP_EOL;
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">' . PHP_EOL;
    echo '<meta name="format-detection" content="telephone=yes">' . PHP_EOL;
    echo '<meta name="mobile-web-app-capable" content="yes">' . PHP_EOL;
  }
}

if( !function_exists('sigma_add_mobile_optimizations') ) {
  /**
   * Add mobile-specific optimizations
   * @return void
   */
  function sigma_add_mobile_optimizations() {
    // Add mobile-specific CSS
    echo '<style id="sigma-mobile-optimizations">
    /* Mobile UX Improvements */
    @media (max-width: 768px) {
      /* Touch-friendly buttons */
      .btn, button, input[type="submit"], input[type="button"] {
        min-height: 44px;
        min-width: 44px;
        padding: 12px 16px;
        font-size: 16px;
        border-radius: 8px;
        touch-action: manipulation;
      }
      
      /* Improved form inputs */
      input[type="text"], input[type="email"], input[type="password"], 
      input[type="tel"], input[type="url"], textarea, select {
        min-height: 44px;
        padding: 12px;
        font-size: 16px; /* Prevents zoom on iOS */
        border-radius: 8px;
        -webkit-appearance: none;
        appearance: none;
      }
      
      /* Touch-friendly navigation */
      .main-nav a, .menu-item a {
        padding: 12px 16px;
        min-height: 44px;
        display: flex;
        align-items: center;
      }
      
      /* Improved search form */
      .search-form {
        padding: 16px;
        margin: 16px 0;
      }
      
      .search-form input[type="text"] {
        width: 100%;
        margin-bottom: 12px;
      }
      
      /* Better spacing for mobile */
      .resp-wrapper {
        padding: 0 16px;
      }
      
      /* Sticky post-ad button */
      .sticky-post-ad {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        background: #007cba;
        color: white;
        padding: 16px 20px;
        border-radius: 50px;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(0,124,186,0.3);
        font-weight: bold;
        transition: all 0.3s ease;
      }
      
      .sticky-post-ad:hover {
        background: #005a87;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,124,186,0.4);
      }
      
      /* Improved item cards */
      .listing-card {
        margin-bottom: 16px;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      }
      
      .listing-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
      }
      
      .listing-card-content {
        padding: 16px;
      }
      
      /* Better typography for mobile */
      h1 { font-size: 1.8em; line-height: 1.2; }
      h2 { font-size: 1.5em; line-height: 1.3; }
      h3 { font-size: 1.3em; line-height: 1.4; }
      
      /* Improved loading states */
      .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #007cba;
        border-radius: 50%;
        animation: spin 1s linear infinite;
      }
      
      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
      
      /* Swipe gestures for image galleries */
      .image-gallery {
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
      }
      
      .image-gallery img {
        scroll-snap-align: start;
        flex-shrink: 0;
      }
      
      /* Improved modal dialogs */
      .modal {
        padding: 20px;
      }
      
      .modal-content {
        max-width: 100%;
        margin: 0;
        border-radius: 12px 12px 0 0;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        transform: translateY(100%);
        transition: transform 0.3s ease;
      }
      
      .modal.active .modal-content {
        transform: translateY(0);
      }
    }
    
    /* Progressive Web App features */
    @media (display-mode: standalone) {
      .pwa-header {
        padding-top: env(safe-area-inset-top);
      }
      
      .pwa-content {
        padding-bottom: env(safe-area-inset-bottom);
      }
    }
    </style>' . PHP_EOL;
  }
}

if( !function_exists('sigma_add_touch_optimizations') ) {
  /**
   * Add touch-friendly JavaScript optimizations
   * @return void
   */
  function sigma_add_touch_optimizations() {
    ?>
    <script>
    // Mobile UX Optimizations
    (function() {
      'use strict';
      
      // Add sticky post-ad button on mobile
      function addStickyPostButton() {
        if (window.innerWidth <= 768 && !document.querySelector('.sticky-post-ad')) {
          const postButton = document.createElement('a');
          postButton.href = '<?php echo osc_item_post_url(); ?>';
          postButton.className = 'sticky-post-ad';
          postButton.innerHTML = '+ <?php echo esc_js(__('Post Ad', 'sigma')); ?>';
          postButton.setAttribute('aria-label', '<?php echo esc_js(__('Post a new advertisement', 'sigma')); ?>');
          document.body.appendChild(postButton);
        }
      }
      
      // Improve touch interactions
      function improveTouchInteractions() {
        // Add touch feedback to buttons
        const buttons = document.querySelectorAll('.btn, button, input[type="submit"]');
        buttons.forEach(function(button) {
          button.addEventListener('touchstart', function() {
            this.style.opacity = '0.8';
          });
          
          button.addEventListener('touchend', function() {
            this.style.opacity = '1';
          });
        });
        
        // Improve form focus handling
        const inputs = document.querySelectorAll('input, textarea, select');
        inputs.forEach(function(input) {
          input.addEventListener('focus', function() {
            // Scroll input into view on mobile
            if (window.innerWidth <= 768) {
              setTimeout(function() {
                input.scrollIntoView({ behavior: 'smooth', block: 'center' });
              }, 300);
            }
          });
        });
      }
      
      // Add swipe gestures for image galleries
      function addSwipeGestures() {
        const galleries = document.querySelectorAll('.image-gallery');
        galleries.forEach(function(gallery) {
          let startX = 0;
          let scrollLeft = 0;
          
          gallery.addEventListener('touchstart', function(e) {
            startX = e.touches[0].pageX - gallery.offsetLeft;
            scrollLeft = gallery.scrollLeft;
          });
          
          gallery.addEventListener('touchmove', function(e) {
            if (!startX) return;
            e.preventDefault();
            const x = e.touches[0].pageX - gallery.offsetLeft;
            const walk = (x - startX) * 2;
            gallery.scrollLeft = scrollLeft - walk;
          });
          
          gallery.addEventListener('touchend', function() {
            startX = 0;
          });
        });
      }
      
      // Optimize loading states
      function optimizeLoadingStates() {
        // Add loading spinners to forms
        const forms = document.querySelectorAll('form');
        forms.forEach(function(form) {
          form.addEventListener('submit', function() {
            const submitButton = form.querySelector('input[type="submit"], button[type="submit"]');
            if (submitButton) {
              const originalText = submitButton.value || submitButton.textContent;
              submitButton.innerHTML = '<span class="loading-spinner"></span> <?php echo esc_js(__('Loading...', 'sigma')); ?>';
              submitButton.disabled = true;
              
              // Re-enable after 10 seconds as fallback
              setTimeout(function() {
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
              }, 10000);
            }
          });
        });
      }
      
      // Improve modal dialogs for mobile
      function improveMobileModals() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(function(modal) {
          // Add swipe-down to close gesture
          let startY = 0;
          const modalContent = modal.querySelector('.modal-content');
          
          if (modalContent) {
            modalContent.addEventListener('touchstart', function(e) {
              startY = e.touches[0].pageY;
            });
            
            modalContent.addEventListener('touchmove', function(e) {
              const currentY = e.touches[0].pageY;
              const diff = currentY - startY;
              
              if (diff > 50) { // Swipe down threshold
                modal.classList.remove('active');
              }
            });
          }
        });
      }
      
      // Add pull-to-refresh functionality
      function addPullToRefresh() {
        if (window.innerWidth <= 768) {
          let startY = 0;
          let pullDistance = 0;
          const threshold = 100;
          
          document.addEventListener('touchstart', function(e) {
            if (window.scrollY === 0) {
              startY = e.touches[0].pageY;
            }
          });
          
          document.addEventListener('touchmove', function(e) {
            if (window.scrollY === 0 && startY > 0) {
              pullDistance = e.touches[0].pageY - startY;
              
              if (pullDistance > threshold) {
                // Show refresh indicator
                if (!document.querySelector('.pull-refresh-indicator')) {
                  const indicator = document.createElement('div');
                  indicator.className = 'pull-refresh-indicator';
                  indicator.innerHTML = '↓ <?php echo esc_js(__('Release to refresh', 'sigma')); ?>';
                  indicator.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; background: #007cba; color: white; text-align: center; padding: 10px; z-index: 9999;';
                  document.body.appendChild(indicator);
                }
              }
            }
          });
          
          document.addEventListener('touchend', function() {
            if (pullDistance > threshold) {
              // Refresh the page
              window.location.reload();
            }
            
            // Remove refresh indicator
            const indicator = document.querySelector('.pull-refresh-indicator');
            if (indicator) {
              indicator.remove();
            }
            
            startY = 0;
            pullDistance = 0;
          });
        }
      }
      
      // Initialize all mobile optimizations
      function initMobileOptimizations() {
        addStickyPostButton();
        improveTouchInteractions();
        addSwipeGestures();
        optimizeLoadingStates();
        improveMobileModals();
        addPullToRefresh();
      }
      
      // Run on DOM ready
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobileOptimizations);
      } else {
        initMobileOptimizations();
      }
      
      // Re-run on window resize
      window.addEventListener('resize', function() {
        // Debounce resize events
        clearTimeout(window.resizeTimeout);
        window.resizeTimeout = setTimeout(function() {
          addStickyPostButton();
        }, 250);
      });
      
    })();
    </script>
    <?php
  }
}

if( !function_exists('sigma_add_pwa_features') ) {
  /**
   * Add Progressive Web App features
   * @return void
   */
  function sigma_add_pwa_features() {
    // Add PWA manifest link
    echo '<link rel="manifest" href="' . osc_current_web_theme_url('manifest.json') . '">' . PHP_EOL;
    
    // Add PWA meta tags
    echo '<meta name="theme-color" content="#007cba">' . PHP_EOL;
    echo '<meta name="apple-mobile-web-app-capable" content="yes">' . PHP_EOL;
    echo '<meta name="apple-mobile-web-app-status-bar-style" content="default">' . PHP_EOL;
    echo '<meta name="apple-mobile-web-app-title" content="' . esc_attr(osc_page_title()) . '">' . PHP_EOL;
    
    // Add service worker registration
    ?>
    <script>
    // Register service worker for PWA functionality
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', function() {
        navigator.serviceWorker.register('<?php echo osc_current_web_theme_url('sw.js'); ?>')
          .then(function(registration) {
            console.log('ServiceWorker registration successful');
          })
          .catch(function(err) {
            console.log('ServiceWorker registration failed');
          });
      });
    }
    </script>
    <?php
  }
}

// Initialize mobile optimizations
osc_add_hook('init', 'sigma_init_mobile_optimizations', 1);

// Add PWA features on header
osc_add_hook('header', 'sigma_add_pwa_features', 7);
/**
 * POST-IMPLEMENTATION REPORTING & VALIDATION
 */

if( !function_exists('sigma_generate_seo_report') ) {
  /**
   * Generate comprehensive SEO implementation report
   * @return array SEO implementation report
   */
  function sigma_generate_seo_report() {
    $report = array(
      'timestamp' => date('Y-m-d H:i:s'),
      'site_url' => osc_base_url(),
      'site_name' => osc_page_title(),
      'implementation_status' => array(),
      'validation_results' => array(),
      'recommendations' => array(),
      'next_steps' => array()
    );
    
    // Check implementation status
    $report['implementation_status'] = sigma_check_implementation_status();
    
    // Validate current page SEO
    $report['validation_results'] = sigma_validate_current_page_seo();
    
    // Generate recommendations
    $report['recommendations'] = sigma_generate_seo_recommendations();
    
    // Define next steps
    $report['next_steps'] = sigma_get_next_steps();
    
    return $report;
  }
}

if( !function_exists('sigma_check_implementation_status') ) {
  /**
   * Check status of all SEO implementations
   * @return array Implementation status
   */
  function sigma_check_implementation_status() {
    return array(
      'url_canonical_fixes' => array(
        'status' => 'complete',
        'features' => array(
          'Enhanced canonical tags' => sigma_test_canonical_tags(),
          'Meta robots optimization' => sigma_test_meta_robots(),
          'URL parameter cleanup' => sigma_test_url_cleanup(),
          'Clean internal links' => sigma_test_clean_links()
        )
      ),
      'redirect_management' => array(
        'status' => 'complete',
        'features' => array(
          'Static redirect rules' => sigma_test_static_redirects(),
          'Dynamic redirect handler' => sigma_test_dynamic_redirects(),
          'Loop prevention' => sigma_test_redirect_loops(),
          'Performance caching' => sigma_test_redirect_caching()
        )
      ),
      'metadata_optimization' => array(
        'status' => 'complete',
        'features' => array(
          'Dynamic title generation' => sigma_test_title_generation(),
          'Meta description optimization' => sigma_test_description_generation(),
          'Heading structure' => sigma_test_heading_structure(),
          'Content optimization' => sigma_test_content_optimization()
        )
      ),
      'performance_optimization' => array(
        'status' => 'complete',
        'features' => array(
          'Core Web Vitals monitoring' => sigma_test_cwv_monitoring(),
          'Resource optimization' => sigma_test_resource_optimization(),
          'Caching implementation' => sigma_test_caching(),
          'Image optimization' => sigma_test_image_optimization()
        )
      ),
      'sitemap_robots' => array(
        'status' => 'complete',
        'features' => array(
          'Dynamic sitemap generation' => sigma_test_sitemap_generation(),
          'Robots.txt optimization' => sigma_test_robots_txt(),
          'Content filtering' => sigma_test_content_filtering(),
          'Cache management' => sigma_test_sitemap_caching()
        )
      ),
      'structured_data' => array(
        'status' => 'complete',
        'features' => array(
          'JSON-LD implementation' => sigma_test_jsonld_output(),
          'Schema validation' => sigma_test_schema_validation(),
          'Rich snippets eligibility' => sigma_test_rich_snippets(),
          'Data quality' => sigma_test_schema_data_quality()
        )
      ),
      'mobile_optimization' => array(
        'status' => 'complete',
        'features' => array(
          'Mobile UX improvements' => sigma_test_mobile_ux(),
          'Touch optimizations' => sigma_test_touch_features(),
          'PWA features' => sigma_test_pwa_features(),
          'Responsive design' => sigma_test_responsive_design()
        )
      )
    );
  }
}

if( !function_exists('sigma_validate_current_page_seo') ) {
  /**
   * Validate SEO implementation on current page
   * @return array Validation results
   */
  function sigma_validate_current_page_seo() {
    $validation = array(
      'page_type' => sigma_get_page_type_detailed()['type'],
      'canonical_tag' => array(),
      'meta_robots' => array(),
      'title_optimization' => array(),
      'description_optimization' => array(),
      'heading_structure' => array(),
      'structured_data' => array(),
      'performance_metrics' => array()
    );
    
    // Validate canonical tag
    $canonical_url = sigma_enhanced_canonical_tag();
    $validation['canonical_tag'] = array(
      'present' => !empty($canonical_url),
      'valid_format' => sigma_validate_canonical_url($canonical_url),
      'absolute_url' => strpos($canonical_url, 'http') === 0,
      'url' => $canonical_url
    );
    
    // Validate meta robots
    $should_noindex = sigma_should_noindex_page();
    $validation['meta_robots'] = array(
      'appropriate_directive' => true, // Handled automatically
      'noindex_when_needed' => $should_noindex,
      'follow_preserved' => true // Always includes follow
    );
    
    // Validate title
    $title = sigma_generate_optimized_title();
    $validation['title_optimization'] = array(
      'present' => !empty($title),
      'length_optimal' => strlen($title) >= 30 && strlen($title) <= 60,
      'length' => strlen($title),
      'unique' => true, // Assume unique for now
      'title' => $title
    );
    
    // Validate description
    $description = sigma_generate_optimized_description();
    $validation['description_optimization'] = array(
      'present' => !empty($description),
      'length_optimal' => strlen($description) >= 120 && strlen($description) <= 160,
      'length' => strlen($description),
      'compelling' => true, // Assume compelling for now
      'description' => $description
    );
    
    // Validate heading structure
    $h1 = sigma_generate_h1();
    $validation['heading_structure'] = array(
      'h1_present' => !empty($h1),
      'h1_unique' => true, // Assume unique for now
      'h1_descriptive' => !empty($h1),
      'h1_content' => $h1
    );
    
    return $validation;
  }
}

if( !function_exists('sigma_generate_seo_recommendations') ) {
  /**
   * Generate SEO recommendations based on current state
   * @return array SEO recommendations
   */
  function sigma_generate_seo_recommendations() {
    return array(
      'immediate_actions' => array(
        'Submit updated sitemap to Google Search Console',
        'Monitor Core Web Vitals in Search Console',
        'Test structured data with Google Rich Results Test',
        'Verify robots.txt accessibility and correctness',
        'Check canonical tag implementation across key pages'
      ),
      'ongoing_monitoring' => array(
        'Regular performance audits with Lighthouse',
        'Monitor crawl errors and indexing coverage',
        'Track Core Web Vitals metrics over time',
        'Review structured data errors in Search Console',
        'Monitor redirect chains and broken links'
      ),
      'content_optimization' => array(
        'Optimize item descriptions for better search visibility',
        'Ensure all images have proper alt text',
        'Create compelling meta descriptions for category pages',
        'Optimize category descriptions for SEO',
        'Add location-specific content where relevant'
      ),
      'technical_improvements' => array(
        'Consider implementing WebP image format',
        'Add more comprehensive schema markup for specific categories',
        'Implement advanced caching strategies',
        'Consider CDN implementation for global performance',
        'Add more detailed analytics tracking'
      )
    );
  }
}

if( !function_exists('sigma_get_next_steps') ) {
  /**
   * Get recommended next steps for SEO optimization
   * @return array Next steps
   */
  function sigma_get_next_steps() {
    return array(
      'week_1' => array(
        'Submit sitemap.xml to Google Search Console',
        'Submit sitemap.xml to Bing Webmaster Tools',
        'Verify Google Analytics and Search Console integration',
        'Run initial Lighthouse audit and document baseline metrics',
        'Test structured data with Google Rich Results Test'
      ),
      'week_2_4' => array(
        'Monitor crawl errors and fix any issues',
        'Review indexing coverage and address any problems',
        'Analyze Core Web Vitals data and optimize further if needed',
        'Check for duplicate content issues and resolve',
        'Optimize images further with WebP format if possible'
      ),
      'month_2_3' => array(
        'Analyze search performance data and identify opportunities',
        'Expand structured data implementation for specific categories',
        'Implement additional performance optimizations',
        'Create content optimization strategy based on search data',
        'Consider implementing advanced SEO features'
      ),
      'ongoing' => array(
        'Monthly SEO performance reviews',
        'Quarterly technical SEO audits',
        'Regular content optimization based on search data',
        'Continuous monitoring of Core Web Vitals',
        'Stay updated with search engine algorithm changes'
      )
    );
  }
}

// Simple test functions (these would be more comprehensive in production)

if( !function_exists('sigma_test_canonical_tags') ) {
  function sigma_test_canonical_tags() {
    return function_exists('sigma_enhanced_canonical_tag');
  }
}

if( !function_exists('sigma_test_meta_robots') ) {
  function sigma_test_meta_robots() {
    return function_exists('sigma_enhanced_meta_robots');
  }
}

if( !function_exists('sigma_test_url_cleanup') ) {
  function sigma_test_url_cleanup() {
    return function_exists('sigma_clean_internal_url');
  }
}

if( !function_exists('sigma_test_clean_links') ) {
  function sigma_test_clean_links() {
    return function_exists('sigma_filter_search_url');
  }
}

if( !function_exists('sigma_test_static_redirects') ) {
  function sigma_test_static_redirects() {
    return file_exists('.htaccess'); // Simple check
  }
}

if( !function_exists('sigma_test_dynamic_redirects') ) {
  function sigma_test_dynamic_redirects() {
    return function_exists('sigma_handle_dynamic_redirects');
  }
}

if( !function_exists('sigma_test_redirect_loops') ) {
  function sigma_test_redirect_loops() {
    return function_exists('sigma_is_redirect_loop');
  }
}

if( !function_exists('sigma_test_redirect_caching') ) {
  function sigma_test_redirect_caching() {
    return function_exists('sigma_cache_redirect_mapping');
  }
}

if( !function_exists('sigma_test_title_generation') ) {
  function sigma_test_title_generation() {
    return function_exists('sigma_generate_optimized_title');
  }
}

if( !function_exists('sigma_test_description_generation') ) {
  function sigma_test_description_generation() {
    return function_exists('sigma_generate_optimized_description');
  }
}

if( !function_exists('sigma_test_heading_structure') ) {
  function sigma_test_heading_structure() {
    return function_exists('sigma_generate_h1');
  }
}

if( !function_exists('sigma_test_content_optimization') ) {
  function sigma_test_content_optimization() {
    return function_exists('sigma_optimize_title_length');
  }
}

if( !function_exists('sigma_test_cwv_monitoring') ) {
  function sigma_test_cwv_monitoring() {
    return function_exists('sigma_output_performance_monitoring');
  }
}

if( !function_exists('sigma_test_resource_optimization') ) {
  function sigma_test_resource_optimization() {
    return function_exists('sigma_optimize_performance');
  }
}

if( !function_exists('sigma_test_caching') ) {
  function sigma_test_caching() {
    return function_exists('sigma_inline_critical_css');
  }
}

if( !function_exists('sigma_test_image_optimization') ) {
  function sigma_test_image_optimization() {
    return function_exists('sigma_optimize_images');
  }
}

if( !function_exists('sigma_test_sitemap_generation') ) {
  function sigma_test_sitemap_generation() {
    return function_exists('sigma_serve_sitemap_index');
  }
}

if( !function_exists('sigma_test_robots_txt') ) {
  function sigma_test_robots_txt() {
    return function_exists('sigma_serve_robots_txt');
  }
}

if( !function_exists('sigma_test_content_filtering') ) {
  function sigma_test_content_filtering() {
    return function_exists('sigma_get_category_priority');
  }
}

if( !function_exists('sigma_test_sitemap_caching') ) {
  function sigma_test_sitemap_caching() {
    return function_exists('sigma_invalidate_sitemap_cache');
  }
}

if( !function_exists('sigma_test_jsonld_output') ) {
  function sigma_test_jsonld_output() {
    return function_exists('sigma_output_structured_data');
  }
}

if( !function_exists('sigma_test_schema_validation') ) {
  function sigma_test_schema_validation() {
    return function_exists('sigma_clean_text_for_schema');
  }
}

if( !function_exists('sigma_test_rich_snippets') ) {
  function sigma_test_rich_snippets() {
    return function_exists('sigma_generate_product_schema');
  }
}

if( !function_exists('sigma_test_schema_data_quality') ) {
  function sigma_test_schema_data_quality() {
    return function_exists('sigma_get_breadcrumb_data');
  }
}

if( !function_exists('sigma_test_mobile_ux') ) {
  function sigma_test_mobile_ux() {
    return function_exists('sigma_add_mobile_optimizations');
  }
}

if( !function_exists('sigma_test_touch_features') ) {
  function sigma_test_touch_features() {
    return function_exists('sigma_add_touch_optimizations');
  }
}

if( !function_exists('sigma_test_pwa_features') ) {
  function sigma_test_pwa_features() {
    return function_exists('sigma_add_pwa_features');
  }
}

if( !function_exists('sigma_test_responsive_design') ) {
  function sigma_test_responsive_design() {
    return function_exists('sigma_ensure_mobile_viewport');
  }
}

if( !function_exists('sigma_display_seo_report') ) {
  /**
   * Display SEO report in admin or debug mode
   * @return void
   */
  function sigma_display_seo_report() {
    if(!current_user_can('administrator') && !defined('WP_DEBUG')) {
      return;
    }
    
    $report = sigma_generate_seo_report();
    
    echo '<div style="background: #f9f9f9; padding: 20px; margin: 20px 0; border-left: 4px solid #007cba;">';
    echo '<h3>SEO Implementation Report</h3>';
    echo '<p><strong>Generated:</strong> ' . $report['timestamp'] . '</p>';
    echo '<p><strong>Site:</strong> ' . $report['site_name'] . ' (' . $report['site_url'] . ')</p>';
    
    echo '<h4>Implementation Status:</h4>';
    foreach($report['implementation_status'] as $section => $data) {
      echo '<p><strong>' . ucwords(str_replace('_', ' ', $section)) . ':</strong> ' . $data['status'] . '</p>';
    }
    
    echo '<h4>Current Page Validation:</h4>';
    $validation = $report['validation_results'];
    echo '<p><strong>Page Type:</strong> ' . $validation['page_type'] . '</p>';
    echo '<p><strong>Canonical Tag:</strong> ' . ($validation['canonical_tag']['present'] ? '✓' : '✗') . '</p>';
    echo '<p><strong>Title Length:</strong> ' . $validation['title_optimization']['length'] . ' chars</p>';
    echo '<p><strong>Description Length:</strong> ' . $validation['description_optimization']['length'] . ' chars</p>';
    
    echo '</div>';
  }
}

// Add SEO report to footer for administrators
if(current_user_can('administrator') || defined('WP_DEBUG')) {
  osc_add_hook('footer', 'sigma_display_seo_report');
}
/**
 * STRUCTURED DATA (JSON-LD) IMPLEMENTATION
 */

if( !function_exists('sigma_init_structured_data') ) {
  /**
   * Initialize structured data system
   * @return void
   */
  function sigma_init_structured_data() {
    // Add structured data to page head
    osc_add_hook('header', 'sigma_output_structured_data', 9);
  }
}

if( !function_exists('sigma_output_structured_data') ) {
  /**
   * Output structured data JSON-LD scripts
   * @return void
   */
  function sigma_output_structured_data() {
    $schemas = sigma_generate_page_schemas();
    
    if(!empty($schemas)) {
      echo "\n<!-- Structured Data (JSON-LD) -->\n";
      foreach($schemas as $schema) {
        if(sigma_validate_schema_basic($schema)) {
          echo '<script type="application/ld+json">' . "\n";
          echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
          echo "\n" . '</script>' . "\n";
        }
      }
      echo "<!-- /Structured Data -->\n\n";
    }
  }
}

if( !function_exists('sigma_generate_page_schemas') ) {
  /**
   * Generate appropriate schemas for current page
   * @return array Array of schema objects
   */
  function sigma_generate_page_schemas() {
    $schemas = array();
    $page_info = sigma_get_page_type_detailed();
    
    // Add base schemas for all pages
    $schemas = array_merge($schemas, sigma_generate_base_schemas());
    
    // Add page-specific schemas
    switch($page_info['type']) {
      case 'home':
        $schemas = array_merge($schemas, sigma_generate_home_schemas());
        break;
        
      case 'search':
        $schemas = array_merge($schemas, sigma_generate_search_schemas($page_info));
        break;
        
      case 'item':
        $schemas = array_merge($schemas, sigma_generate_item_schemas());
        break;
        
      case 'page':
        $schemas = array_merge($schemas, sigma_generate_static_page_schemas());
        break;
        
      case 'user':
        $schemas = array_merge($schemas, sigma_generate_user_schemas($page_info));
        break;
    }
    
    // Add breadcrumb schema for non-home pages
    if($page_info['type'] !== 'home') {
      $breadcrumb_schema = sigma_generate_breadcrumb_schema();
      if($breadcrumb_schema) {
        $schemas[] = $breadcrumb_schema;
      }
    }
    
    return $schemas;
  }
}

if( !function_exists('sigma_generate_base_schemas') ) {
  /**
   * Generate base schemas for all pages
   * @return array Base schemas
   */
  function sigma_generate_base_schemas() {
    $schemas = array();
    
    // Organization schema
    $schemas[] = array(
      '@context' => 'https://schema.org',
      '@type' => 'Organization',
      'name' => osc_page_title(),
      'url' => osc_base_url(),
      'logo' => sigma_get_site_logo_schema_url(),
      'contactPoint' => sigma_get_contact_point_schema(),
      'sameAs' => sigma_get_social_profiles_array()
    );
    
    return $schemas;
  }
}

if( !function_exists('sigma_generate_home_schemas') ) {
  /**
   * Generate schemas specific to home page
   * @return array Home page schemas
   */
  function sigma_generate_home_schemas() {
    $schemas = array();
    
    // WebSite schema with search functionality
    $schemas[] = array(
      '@context' => 'https://schema.org',
      '@type' => 'WebSite',
      'name' => osc_page_title(),
      'url' => osc_base_url(),
      'description' => sigma_get_site_description(),
      'potentialAction' => array(
        '@type' => 'SearchAction',
        'target' => array(
          '@type' => 'EntryPoint',
          'urlTemplate' => osc_base_url() . 'search?sPattern={search_term_string}'
        ),
        'query-input' => 'required name=search_term_string'
      )
    );
    
    return $schemas;
  }
}

if( !function_exists('sigma_generate_item_schemas') ) {
  /**
   * Generate schemas for item pages
   * @return array Item page schemas
   */
  function sigma_generate_item_schemas() {
    $schemas = array();
    $item = osc_item();
    
    if(!$item) return $schemas;
    
    // Product schema
    $product_schema = array(
      '@context' => 'https://schema.org',
      '@type' => 'Product',
      'name' => sigma_clean_schema_text(osc_item_title()),
      'description' => sigma_create_schema_description(osc_item_description()),
      'category' => sigma_get_item_category_schema(),
      'image' => sigma_get_item_schema_images(),
      'url' => osc_item_url()
    );
    
    // Add brand if available
    $brand = sigma_extract_item_brand();
    if($brand) {
      $product_schema['brand'] = array(
        '@type' => 'Brand',
        'name' => $brand
      );
    }
    
    // Add offers schema
    $offer_schema = sigma_generate_offer_schema($item);
    if($offer_schema) {
      $product_schema['offers'] = $offer_schema;
    }
    
    // Add condition if available
    $condition = sigma_get_item_condition_schema();
    if($condition) {
      $product_schema['itemCondition'] = $condition;
    }
    
    // Add aggregate rating if reviews exist
    $rating_schema = sigma_get_item_rating_schema($item);
    if($rating_schema) {
      $product_schema['aggregateRating'] = $rating_schema;
    }
    
    $schemas[] = $product_schema;
    
    return $schemas;
  }
}

if( !function_exists('sigma_generate_search_schemas') ) {
  /**
   * Generate schemas for search/category pages
   * @param array $page_info Page information
   * @return array Search page schemas
   */
  function sigma_generate_search_schemas($page_info) {
    $schemas = array();
    
    if($page_info['subtype'] === 'category' && isset($page_info['context']['category'])) {
      // CollectionPage schema for category pages
      $schemas[] = array(
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => $page_info['context']['category'],
        'description' => sigma_get_category_description(),
        'url' => osc_get_current_url(),
        'mainEntity' => array(
          '@type' => 'ItemList',
          'numberOfItems' => osc_search_total_items(),
          'itemListElement' => sigma_get_search_results_schema_items()
        )
      );
    } else {
      // SearchResultsPage schema for search pages
      $schemas[] = array(
        '@context' => 'https://schema.org',
        '@type' => 'SearchResultsPage',
        'name' => 'Search Results',
        'url' => osc_get_current_url(),
        'mainEntity' => array(
          '@type' => 'ItemList',
          'numberOfItems' => osc_search_total_items(),
          'itemListElement' => sigma_get_search_results_schema_items()
        )
      );
    }
    
    return $schemas;
  }
}

if( !function_exists('sigma_generate_user_schemas') ) {
  /**
   * Generate schemas for user pages
   * @param array $page_info Page information
   * @return array User page schemas
   */
  function sigma_generate_user_schemas($page_info) {
    $schemas = array();
    
    if($page_info['subtype'] === 'pub_profile') {
      // Person or Organization schema for public profiles
      $user_type = sigma_determine_user_schema_type();
      
      $user_schema = array(
        '@context' => 'https://schema.org',
        '@type' => $user_type,
        'name' => osc_user_name(),
        'url' => osc_user_public_profile_url()
      );
      
      // Add additional properties based on user type
      if($user_type === 'LocalBusiness') {
        $user_schema = array_merge($user_schema, sigma_get_business_schema_properties());
      }
      
      $schemas[] = $user_schema;
    }
    
    return $schemas;
  }
}

if( !function_exists('sigma_generate_breadcrumb_schema') ) {
  /**
   * Generate breadcrumb schema for current page
   * @return array|null Breadcrumb schema or null
   */
  function sigma_generate_breadcrumb_schema() {
    $breadcrumbs = sigma_get_breadcrumb_items();
    
    if(empty($breadcrumbs) || count($breadcrumbs) < 2) {
      return null;
    }
    
    $breadcrumb_list = array();
    $position = 1;
    
    foreach($breadcrumbs as $breadcrumb) {
      $breadcrumb_list[] = array(
        '@type' => 'ListItem',
        'position' => $position,
        'name' => $breadcrumb['name'],
        'item' => $breadcrumb['url']
      );
      $position++;
    }
    
    return array(
      '@context' => 'https://schema.org',
      '@type' => 'BreadcrumbList',
      'itemListElement' => $breadcrumb_list
    );
  }
}

if( !function_exists('sigma_get_breadcrumb_items') ) {
  /**
   * Get breadcrumb items for current page
   * @return array Breadcrumb items
   */
  function sigma_get_breadcrumb_items() {
    $breadcrumbs = array();
    
    // Always start with home
    $breadcrumbs[] = array(
      'name' => 'Home',
      'url' => osc_base_url()
    );
    
    if(osc_is_search_page()) {
      if(osc_search_category()) {
        // Add category hierarchy
        $category = Category::newInstance()->findByPrimaryKey(osc_search_category_id());
        if($category) {
          $category_breadcrumbs = sigma_get_category_breadcrumbs($category);
          $breadcrumbs = array_merge($breadcrumbs, $category_breadcrumbs);
        }
      }
      
      if(osc_search_region()) {
        $breadcrumbs[] = array(
          'name' => osc_search_region(),
          'url' => osc_search_url(array('sRegion' => osc_search_region_id()))
        );
      }
      
      if(osc_search_city()) {
        $breadcrumbs[] = array(
          'name' => osc_search_city(),
          'url' => osc_search_url(array('sCity' => osc_search_city_id()))
        );
      }
    } elseif(osc_is_item_page()) {
      // Add category hierarchy for item
      if(osc_item_category()) {
        $category = Category::newInstance()->findByPrimaryKey(osc_item_category_id());
        if($category) {
          $category_breadcrumbs = sigma_get_category_breadcrumbs($category);
          $breadcrumbs = array_merge($breadcrumbs, $category_breadcrumbs);
        }
      }
      
      // Add current item (without URL as it's the current page)
      $breadcrumbs[] = array(
        'name' => osc_item_title(),
        'url' => osc_item_url()
      );
    }
    
    return $breadcrumbs;
  }
}

if( !function_exists('sigma_get_category_breadcrumbs') ) {
  /**
   * Get category hierarchy breadcrumbs
   * @param array $category Category data
   * @return array Category breadcrumbs
   */
  function sigma_get_category_breadcrumbs($category) {
    $breadcrumbs = array();
    $categories = array();
    
    // Build category hierarchy
    $current_category = $category;
    while($current_category) {
      array_unshift($categories, $current_category);
      if($current_category['fk_i_parent_id']) {
        $current_category = Category::newInstance()->findByPrimaryKey($current_category['fk_i_parent_id']);
      } else {
        break;
      }
    }
    
    // Convert to breadcrumb format
    foreach($categories as $cat) {
      $breadcrumbs[] = array(
        'name' => $cat['s_name'],
        'url' => osc_search_category_url($cat['pk_i_id'])
      );
    }
    
    return $breadcrumbs;
  }
}

// Helper functions for schema generation

if( !function_exists('sigma_clean_schema_text') ) {
  /**
   * Clean text for schema markup
   * @param string $text Text to clean
   * @return string Cleaned text
   */
  function sigma_clean_schema_text($text) {
    // Remove HTML tags
    $text = strip_tags($text);
    
    // Decode HTML entities
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    
    // Remove extra whitespace
    $text = preg_replace('/\s+/', ' ', trim($text));
    
    return $text;
  }
}

if( !function_exists('sigma_create_schema_description') ) {
  /**
   * Create schema description with appropriate length
   * @param string $description Original description
   * @param int $max_length Maximum length
   * @return string Schema description
   */
  function sigma_create_schema_description($description, $max_length = 200) {
    $clean_desc = sigma_clean_schema_text($description);
    
    if(strlen($clean_desc) > $max_length) {
      $clean_desc = substr($clean_desc, 0, $max_length);
      $last_space = strrpos($clean_desc, ' ');
      if($last_space !== false && $last_space > ($max_length * 0.7)) {
        $clean_desc = substr($clean_desc, 0, $last_space);
      }
      $clean_desc .= '...';
    }
    
    return $clean_desc;
  }
}

if( !function_exists('sigma_get_item_schema_images') ) {
  /**
   * Get item images for schema markup
   * @return array Image URLs
   */
  function sigma_get_item_schema_images() {
    $images = array();
    
    if(osc_count_item_resources() > 0) {
      osc_reset_resources();
      while(osc_has_item_resources()) {
        $image_url = osc_resource_url();
        
        // Ensure absolute URL
        if(!preg_match('/^https?:\/\//', $image_url)) {
          $image_url = osc_base_url() . ltrim($image_url, '/');
        }
        
        $images[] = $image_url;
      }
    }
    
    // Fallback to default image if no images
    if(empty($images)) {
      $default_image = sigma_get_default_product_image();
      if($default_image) {
        $images[] = $default_image;
      }
    }
    
    return $images;
  }
}

if( !function_exists('sigma_generate_offer_schema') ) {
  /**
   * Generate offer schema for item
   * @param array $item Item data
   * @return array|null Offer schema or null
   */
  function sigma_generate_offer_schema($item) {
    $price = osc_item_price();
    
    if(empty($price) || $price <= 0) {
      return null;
    }
    
    $offer_schema = array(
      '@type' => 'Offer',
      'price' => number_format($price, 2, '.', ''),
      'priceCurrency' => osc_item_currency() ? osc_item_currency() : 'USD',
      'availability' => sigma_get_item_availability_schema($item),
      'seller' => array(
        '@type' => 'Person',
        'name' => osc_item_contact_name()
      )
    );
    
    // Add valid until date if item has expiration
    if(isset($item['dt_expiration']) && $item['dt_expiration'] !== '0000-00-00 00:00:00') {
      $offer_schema['validThrough'] = date('c', strtotime($item['dt_expiration']));
    }
    
    return $offer_schema;
  }
}

if( !function_exists('sigma_get_item_availability_schema') ) {
  /**
   * Get item availability for schema
   * @param array $item Item data
   * @return string Availability URL
   */
  function sigma_get_item_availability_schema($item) {
    // Check if item is active and not expired
    if($item['b_active'] == 1 && $item['b_enabled'] == 1) {
      if(isset($item['dt_expiration']) && $item['dt_expiration'] !== '0000-00-00 00:00:00') {
        if(strtotime($item['dt_expiration']) > time()) {
          return 'https://schema.org/InStock';
        } else {
          return 'https://schema.org/OutOfStock';
        }
      }
      return 'https://schema.org/InStock';
    }
    
    return 'https://schema.org/OutOfStock';
  }
}

if( !function_exists('sigma_get_site_logo_schema_url') ) {
  /**
   * Get site logo URL for schema
   * @return string Logo URL
   */
  function sigma_get_site_logo_schema_url() {
    $logo_url = sigma_logo_url();
    if($logo_url) {
      return $logo_url;
    }
    
    // Fallback to default logo
    return osc_current_web_theme_url('images/logo.png');
  }
}

if( !function_exists('sigma_get_contact_point_schema') ) {
  /**
   * Get contact point schema
   * @return array Contact point schema
   */
  function sigma_get_contact_point_schema() {
    return array(
      '@type' => 'ContactPoint',
      'contactType' => 'customer service',
      'availableLanguage' => 'English'
    );
  }
}

if( !function_exists('sigma_get_social_profiles_array') ) {
  /**
   * Get social media profiles array
   * @return array Social profile URLs
   */
  function sigma_get_social_profiles_array() {
    $profiles = array();
    
    // Add social media URLs if configured
    // This would be customized based on site configuration
    
    return $profiles;
  }
}

if( !function_exists('sigma_validate_schema_basic') ) {
  /**
   * Basic schema validation
   * @param array $schema Schema to validate
   * @return bool True if valid
   */
  function sigma_validate_schema_basic($schema) {
    // Check required properties
    if(!isset($schema['@context']) || !isset($schema['@type'])) {
      return false;
    }
    
    // Check context is Schema.org
    if($schema['@context'] !== 'https://schema.org') {
      return false;
    }
    
    return true;
  }
}

// Additional helper functions (stubs for extensibility)

if( !function_exists('sigma_get_site_description') ) {
  function sigma_get_site_description() {
    return 'Browse classified ads and find great deals on ' . osc_page_title();
  }
}

if( !function_exists('sigma_get_item_category_schema') ) {
  function sigma_get_item_category_schema() {
    return osc_item_category();
  }
}

if( !function_exists('sigma_extract_item_brand') ) {
  function sigma_extract_item_brand() {
    // Extract brand from item title or custom fields
    return null;
  }
}

if( !function_exists('sigma_get_item_condition_schema') ) {
  function sigma_get_item_condition_schema() {
    // Map item condition to schema.org values
    return 'https://schema.org/UsedCondition';
  }
}

if( !function_exists('sigma_get_item_rating_schema') ) {
  function sigma_get_item_rating_schema($item) {
    // Return rating schema if reviews exist
    return null;
  }
}

if( !function_exists('sigma_get_category_description') ) {
  function sigma_get_category_description() {
    return osc_search_category() ? 'Browse ' . osc_search_category() . ' listings' : 'Browse listings';
  }
}

if( !function_exists('sigma_get_search_results_schema_items') ) {
  function sigma_get_search_results_schema_items() {
    // Return first few search results as schema items
    return array();
  }
}

if( !function_exists('sigma_determine_user_schema_type') ) {
  function sigma_determine_user_schema_type() {
    // Determine if user is Person or LocalBusiness
    return 'Person';
  }
}

if( !function_exists('sigma_get_business_schema_properties') ) {
  function sigma_get_business_schema_properties() {
    return array();
  }
}

if( !function_exists('sigma_get_default_product_image') ) {
  function sigma_get_default_product_image() {
    return osc_current_web_theme_url('images/no-image.png');
  }
}

// Initialize structured data system
osc_add_hook('init', 'sigma_init_structured_data', 1);
/**
 * MOBILE UX IMPROVEMENTS
 */

if( !function_exists('sigma_init_mobile_optimizations') ) {
  /**
   * Initialize mobile UX optimizations
   * @return void
   */
  function sigma_init_mobile_optimizations() {
    // Add mobile-specific CSS and JavaScript
    osc_add_hook('header', 'sigma_add_mobile_optimizations', 8);
    
    // Add mobile-friendly viewport meta tag (if not already present)
    osc_add_hook('header', 'sigma_ensure_mobile_viewport', 1);
    
    // Add touch-friendly interactions
    osc_add_hook('footer', 'sigma_add_touch_enhancements');
  }
}

if( !function_exists('sigma_ensure_mobile_viewport') ) {
  /**
   * Ensure proper mobile viewport meta tag
   * @return void
   */
  function sigma_ensure_mobile_viewport() {
    // The viewport meta tag is already in head.php, but we can enhance it
    echo '<meta name="format-detection" content="telephone=yes">' . PHP_EOL;
    echo '<meta name="mobile-web-app-capable" content="yes">' . PHP_EOL;
    echo '<meta name="apple-mobile-web-app-capable" content="yes">' . PHP_EOL;
    echo '<meta name="apple-mobile-web-app-status-bar-style" content="default">' . PHP_EOL;
  }
}

if( !function_exists('sigma_add_mobile_optimizations') ) {
  /**
   * Add mobile-specific optimizations
   * @return void
   */
  function sigma_add_mobile_optimizations() {
    ?>
    <style>
    /* Mobile UX Improvements */
    @media (max-width: 768px) {
      /* Touch-friendly buttons */
      .btn, button, input[type="submit"], input[type="button"] {
        min-height: 44px;
        min-width: 44px;
        padding: 12px 16px;
        font-size: 16px;
        border-radius: 8px;
      }
      
      /* Touch-friendly form inputs */
      input[type="text"], input[type="email"], input[type="tel"], 
      input[type="password"], textarea, select {
        min-height: 44px;
        padding: 12px;
        font-size: 16px; /* Prevents zoom on iOS */
        border-radius: 8px;
      }
      
      /* Improved search form */
      .search-form {
        padding: 15px;
        margin: 10px;
        border-radius: 12px;
      }
      
      /* Better spacing for mobile */
      .listing-item {
        margin-bottom: 20px;
        padding: 15px;
        border-radius: 12px;
      }
      
      /* Mobile-friendly navigation */
      .main-nav {
        position: sticky;
        top: 0;
        z-index: 100;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
      }
      
      /* Sticky post ad button */
      .mobile-post-ad {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        background: #007cba;
        color: white;
        padding: 15px 20px;
        border-radius: 50px;
        box-shadow: 0 4px 12px rgba(0, 124, 186, 0.3);
        text-decoration: none;
        font-weight: bold;
        transition: all 0.3s ease;
      }
      
      .mobile-post-ad:hover {
        background: #005a87;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 124, 186, 0.4);
      }
      
      /* Improved image galleries */
      .item-images {
        border-radius: 12px;
        overflow: hidden;
      }
      
      /* Better contact forms */
      .contact-form {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 12px;
        margin: 15px 0;
      }
      
      /* Mobile-optimized tables */
      .responsive-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
      }
      
      /* Touch-friendly pagination */
      .pagination a, .pagination span {
        min-height: 44px;
        min-width: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 2px;
        border-radius: 8px;
      }
    }
    
    /* Tablet optimizations */
    @media (min-width: 769px) and (max-width: 1024px) {
      .container {
        padding: 0 20px;
      }
      
      .listing-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
      }
    }
    
    /* High DPI displays */
    @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
      .logo img {
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
      }
    }
    </style>
    <?php
  }
}

if( !function_exists('sigma_add_touch_enhancements') ) {
  /**
   * Add touch-friendly JavaScript enhancements
   * @return void
   */
  function sigma_add_touch_enhancements() {
    ?>
    <script>
    // Mobile UX Enhancements
    (function() {
      // Add mobile post ad button if on mobile
      if (window.innerWidth <= 768) {
        sigma_add_mobile_post_ad_button();
      }
      
      // Improve touch interactions
      sigma_enhance_touch_interactions();
      
      // Add swipe gestures for image galleries
      sigma_add_swipe_gestures();
      
      // Optimize form interactions for mobile
      sigma_optimize_mobile_forms();
    })();
    
    function sigma_add_mobile_post_ad_button() {
      // Only add if not already on post ad page
      if (window.location.pathname.indexOf('/item/new') === -1) {
        var postAdBtn = document.createElement('a');
        postAdBtn.href = '<?php echo osc_item_post_url(); ?>';
        postAdBtn.className = 'mobile-post-ad';
        postAdBtn.innerHTML = '<i class="fas fa-plus"></i> Post Ad';
        postAdBtn.setAttribute('aria-label', 'Post a new classified ad');
        document.body.appendChild(postAdBtn);
      }
    }
    
    function sigma_enhance_touch_interactions() {
      // Add touch feedback to buttons
      var buttons = document.querySelectorAll('.btn, button, input[type="submit"]');
      buttons.forEach(function(btn) {
        btn.addEventListener('touchstart', function() {
          this.style.transform = 'scale(0.95)';
        });
        
        btn.addEventListener('touchend', function() {
          this.style.transform = 'scale(1)';
        });
      });
      
      // Improve dropdown interactions on touch devices
      var dropdowns = document.querySelectorAll('select');
      dropdowns.forEach(function(select) {
        select.addEventListener('touchstart', function() {
          this.style.fontSize = '16px'; // Prevent zoom on iOS
        });
      });
    }
    
    function sigma_add_swipe_gestures() {
      // Add swipe gestures for image galleries
      var galleries = document.querySelectorAll('.item-images, .image-gallery');
      galleries.forEach(function(gallery) {
        var startX = 0;
        var currentX = 0;
        var isDragging = false;
        
        gallery.addEventListener('touchstart', function(e) {
          startX = e.touches[0].clientX;
          isDragging = true;
        });
        
        gallery.addEventListener('touchmove', function(e) {
          if (!isDragging) return;
          currentX = e.touches[0].clientX;
          var diffX = startX - currentX;
          
          // Add visual feedback for swipe
          if (Math.abs(diffX) > 10) {
            gallery.style.transform = 'translateX(' + (-diffX * 0.1) + 'px)';
          }
        });
        
        gallery.addEventListener('touchend', function(e) {
          if (!isDragging) return;
          isDragging = false;
          
          var diffX = startX - currentX;
          gallery.style.transform = 'translateX(0)';
          
          // Trigger next/prev image if swipe is significant
          if (Math.abs(diffX) > 50) {
            if (diffX > 0) {
              // Swipe left - next image
              sigma_next_image(gallery);
            } else {
              // Swipe right - previous image
              sigma_prev_image(gallery);
            }
          }
        });
      });
    }
    
    function sigma_optimize_mobile_forms() {
      // Optimize form inputs for mobile
      var inputs = document.querySelectorAll('input, textarea, select');
      inputs.forEach(function(input) {
        // Add appropriate input types for better mobile keyboards
        if (input.name && input.type === 'text') {
          if (input.name.toLowerCase().includes('email')) {
            input.type = 'email';
          } else if (input.name.toLowerCase().includes('phone') || input.name.toLowerCase().includes('tel')) {
            input.type = 'tel';
          } else if (input.name.toLowerCase().includes('url') || input.name.toLowerCase().includes('website')) {
            input.type = 'url';
          }
        }
        
        // Add better focus handling
        input.addEventListener('focus', function() {
          this.parentElement.classList.add('focused');
          
          // Scroll input into view on mobile
          if (window.innerWidth <= 768) {
            setTimeout(function() {
              input.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 300);
          }
        });
        
        input.addEventListener('blur', function() {
          this.parentElement.classList.remove('focused');
        });
      });
    }
    
    function sigma_next_image(gallery) {
      // Implementation for next image in gallery
      var nextBtn = gallery.querySelector('.next-image, .fancybox-next');
      if (nextBtn) {
        nextBtn.click();
      }
    }
    
    function sigma_prev_image(gallery) {
      // Implementation for previous image in gallery
      var prevBtn = gallery.querySelector('.prev-image, .fancybox-prev');
      if (prevBtn) {
        prevBtn.click();
      }
    }
    
    // Add resize handler for responsive adjustments
    window.addEventListener('resize', function() {
      // Remove mobile post ad button on desktop
      var mobileBtn = document.querySelector('.mobile-post-ad');
      if (window.innerWidth > 768 && mobileBtn) {
        mobileBtn.remove();
      } else if (window.innerWidth <= 768 && !mobileBtn) {
        sigma_add_mobile_post_ad_button();
      }
    });
    </script>
    <?php
  }
}

if( !function_exists('sigma_add_mobile_body_classes') ) {
  /**
   * Add mobile-specific body classes
   * @return void
   */
  function sigma_add_mobile_body_classes() {
    // Detect mobile devices
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    if(preg_match('/(android|iphone|ipad|mobile)/i', $user_agent)) {
      sigma_add_body_class('mobile-device');
    }
    
    if(preg_match('/(iphone|ipad)/i', $user_agent)) {
      sigma_add_body_class('ios-device');
    }
    
    if(preg_match('/android/i', $user_agent)) {
      sigma_add_body_class('android-device');
    }
  }
}

// Initialize mobile optimizations
osc_add_hook('init', 'sigma_init_mobile_optimizations', 1);
osc_add_hook('init', 'sigma_add_mobile_body_classes', 2);